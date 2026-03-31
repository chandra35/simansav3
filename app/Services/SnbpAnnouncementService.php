<?php

namespace App\Services;

use App\Models\ReferensiPerguruanTinggi;
use App\Models\ReferensiProgramStudi;
use App\Models\SnbpRegistration;
use App\Models\SiswaLulusan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SnbpAnnouncementService
{
    private const CONFIG_URL = 'https://pengumuman-snbp.snpmb.id/config.json';
    private const CONFIG_CACHE_KEY = 'snbp_announcement_config';
    private const CONFIG_CACHE_SECONDS = 1800;

    public function checkRegistration(SnbpRegistration $registration): array
    {
        $registrationNumber = trim((string) $registration->nomor_pendaftaran);
        $birthDate = optional($registration->siswa)->tanggal_lahir;

        if ($registrationNumber === '') {
            return $this->persistResult($registration, [
                'status' => 'gagal_cek',
                'message' => 'Nomor pendaftaran SNBP belum diisi.',
                'payload' => null,
                'source_url' => null,
            ]);
        }

        if (!$birthDate) {
            return $this->persistResult($registration, [
                'status' => 'gagal_cek',
                'message' => 'Tanggal lahir siswa belum tersedia.',
                'payload' => null,
                'source_url' => null,
            ]);
        }

        $config = $this->getConfig();
        $birthDate = Carbon::parse($birthDate);

        if (now()->timestamp < (int) ($config['opening_time'] ?? 0)) {
            return $this->persistResult($registration, [
                'status' => 'belum_dicek',
                'message' => 'Portal pengumuman SNBP belum dibuka.',
                'payload' => [
                    'opening_time' => $config['opening_time'] ?? null,
                ],
                'source_url' => null,
            ]);
        }

        $lookup = $registrationNumber . $birthDate->format('Ymd');
        $hashTail = substr(md5($lookup), -6);
        $sourceUrl = rtrim((string) $config['authoritative'], '/') . '/'
            . $hashTail . '-' . $config['key'] . '-' . $lookup . '.json';

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->acceptJson()
            ->get($sourceUrl);

        if ($response->status() === 404) {
            return $this->persistResult($registration, [
                'status' => 'gagal_cek',
                'message' => 'Nomor pendaftaran atau tanggal lahir tidak ditemukan di portal SNBP.',
                'payload' => [
                    'lookup' => $lookup,
                    'hash_tail' => $hashTail,
                ],
                'source_url' => $sourceUrl,
            ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('Portal SNBP sedang tidak merespons dengan baik.');
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new RuntimeException('Format respons portal SNBP tidak dikenali.');
        }

        $accepted = data_get($payload, 'ac');
        $status = is_array($accepted) ? 'lulus' : 'tidak_lulus';

        $message = $status === 'lulus'
            ? sprintf(
                'Lulus SNBP di %s - %s.',
                data_get($accepted, 'pt', '-'),
                data_get($accepted, 'pr', '-')
            )
            : 'Tidak lulus seleksi SNBP.';

        if ($status === 'lulus') {
            $this->syncLulusan($registration, $payload);
        } elseif ($status === 'tidak_lulus') {
            $this->unlinkRejectedLulusan($registration);
        }

        return $this->persistResult($registration, [
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
            'source_url' => $sourceUrl,
        ]);
    }

    public function getConfig(): array
    {
        return Cache::remember(self::CONFIG_CACHE_KEY, self::CONFIG_CACHE_SECONDS, function () {
            $response = Http::timeout(15)
                ->retry(1, 250)
                ->acceptJson()
                ->get(self::CONFIG_URL);

            if ($response->failed()) {
                throw new RuntimeException('Gagal mengambil konfigurasi portal SNBP.');
            }

            $config = $response->json();
            if (!is_array($config) || empty($config['authoritative']) || empty($config['key'])) {
                throw new RuntimeException('Konfigurasi portal SNBP tidak valid.');
            }

            return $config;
        });
    }

    private function persistResult(SnbpRegistration $registration, array $result): array
    {
        $payload = $result['payload'];
        if (is_array($payload)) {
            $payload['_meta'] = [
                'source_url' => $result['source_url'],
                'checked_at' => now()->toIso8601String(),
            ];
        }

        $registration->forceFill([
            'check_status' => $result['status'],
            'last_checked_at' => now(),
            'last_check_message' => $result['message'],
            'last_check_payload' => $payload,
        ])->save();

        return [
            'status' => $result['status'],
            'status_label' => $registration->fresh()->check_status_label,
            'message' => $result['message'],
            'checked_at' => $registration->last_checked_at?->format('d-m-Y H:i:s'),
            'source_url' => $result['source_url'],
            'payload' => $payload,
        ];
    }

    private function syncLulusan(SnbpRegistration $registration, array $payload): void
    {
        $accepted = data_get($payload, 'ac');
        if (!is_array($accepted)) {
            return;
        }

        $campusName = trim((string) data_get($accepted, 'pt', ''));
        $programName = trim((string) data_get($accepted, 'pr', ''));
        $reRegistrationUrl = trim((string) data_get($accepted, 'ur', ''));

        if ($campusName === '' || $programName === '') {
            return;
        }

        [$referensiPerguruanTinggi, $referensiProgramStudi] = $this->matchReferences($campusName, $programName);

        $existing = SiswaLulusan::query()
            ->where('siswa_id', $registration->siswa_id)
            ->where('tahun_pelajaran_id', $registration->tahun_pelajaran_id)
            ->first();

        $payloadLulusan = [
            'snbp_registration_id' => $registration->id,
            'referensi_perguruan_tinggi_id' => $referensiPerguruanTinggi?->id,
            'referensi_program_studi_id' => $referensiProgramStudi?->id,
            'jalur_masuk' => 'SNBP',
            'nama_universitas' => $referensiPerguruanTinggi?->nama ?? $campusName,
            'nama_universitas_manual' => $referensiPerguruanTinggi ? null : $campusName,
            'jurusan_fakultas' => $referensiProgramStudi?->fakultas ?? $existing?->jurusan_fakultas,
            'program_studi' => $referensiProgramStudi
                ? trim($referensiProgramStudi->jenjang . ' ' . $referensiProgramStudi->nama)
                : $programName,
            'program_studi_manual' => $referensiProgramStudi ? null : $programName,
            'keterangan' => $this->mergeKeterangan($existing?->keterangan, $reRegistrationUrl),
        ];

        SiswaLulusan::updateOrCreate(
            [
                'siswa_id' => $registration->siswa_id,
                'tahun_pelajaran_id' => $registration->tahun_pelajaran_id,
            ],
            $payloadLulusan
        );
    }

    private function unlinkRejectedLulusan(SnbpRegistration $registration): void
    {
        SiswaLulusan::query()
            ->where('siswa_id', $registration->siswa_id)
            ->where('tahun_pelajaran_id', $registration->tahun_pelajaran_id)
            ->where(function ($query) use ($registration) {
                $query->where('snbp_registration_id', $registration->id)
                    ->orWhere('jalur_masuk', 'SNBP');
            })
            ->delete();
    }

    private function matchReferences(string $campusName, string $programName): array
    {
        $referensiPerguruanTinggi = ReferensiPerguruanTinggi::query()
            ->where('is_active', true)
            ->get()
            ->first(function (ReferensiPerguruanTinggi $campus) use ($campusName) {
                return $this->normalize($campus->nama) === $this->normalize($campusName);
            });

        $referensiProgramStudi = null;

        if ($referensiPerguruanTinggi) {
            $programs = ReferensiProgramStudi::query()
                ->where('is_active', true)
                ->where('referensi_perguruan_tinggi_id', $referensiPerguruanTinggi->id)
                ->get();

            $referensiProgramStudi = $programs->first(function (ReferensiProgramStudi $program) use ($programName) {
                $fullName = trim($program->jenjang . ' ' . $program->nama);

                return $this->normalize($fullName) === $this->normalize($programName)
                    || $this->normalize($program->nama) === $this->normalize($programName);
            });
        }

        return [$referensiPerguruanTinggi, $referensiProgramStudi];
    }

    private function mergeKeterangan(?string $existing, string $reRegistrationUrl): ?string
    {
        $existing = trim((string) $existing);

        if ($reRegistrationUrl === '') {
            return $existing !== '' ? $existing : null;
        }

        $note = 'Link daftar ulang SNBP: ' . $reRegistrationUrl;

        if ($existing === '') {
            return $note;
        }

        if (Str::contains($existing, $reRegistrationUrl)) {
            return $existing;
        }

        return trim($existing . PHP_EOL . $note);
    }

    private function normalize(string $value): string
    {
        $value = Str::upper(Str::ascii($value));
        $value = preg_replace('/[^A-Z0-9]+/', '', $value);

        return $value ?? '';
    }
}
