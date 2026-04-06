<?php

namespace App\Services;

use App\Models\ReferensiPerguruanTinggi;
use App\Models\ReferensiProgramStudi;
use App\Models\SiswaLulusan;
use App\Models\SpanPtkinRegistration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SpanPtkinAnnouncementService
{
    private const PAGE_URL = 'https://pengumuman-span.ptkin.ac.id/page';
    private const CHECK_URL = 'https://pengumuman-span.ptkin.ac.id/lulus/';
    private const PORTAL_STATE_CACHE_KEY = 'span_ptkin_portal_state';
    private const PORTAL_STATE_CACHE_SECONDS = 600;

    public function checkRegistration(SpanPtkinRegistration $registration): array
    {
        $nisn = trim((string) optional($registration->siswa)->nisn);
        $registrationNumber = trim((string) $registration->nomor_pendaftaran);

        if ($registrationNumber === '') {
            return $this->persistResult($registration, [
                'status' => 'gagal_cek',
                'message' => 'Nomor pendaftaran SPAN-PTKIN belum terimport dari sekolah.',
                'payload' => null,
                'source_url' => null,
            ]);
        }

        if ($nisn === '') {
            return $this->persistResult($registration, [
                'status' => 'gagal_cek',
                'message' => 'NISN siswa belum tersedia, sehingga checker SPAN-PTKIN tidak bisa dijalankan.',
                'payload' => null,
                'source_url' => null,
            ]);
        }

        $portalState = $this->getPortalState();
        if (($portalState['is_open'] ?? true) === false) {
            return $this->persistResult($registration, [
                'status' => 'belum_dicek',
                'message' => $portalState['message'] ?? 'Portal pengumuman SPAN-PTKIN belum dibuka.',
                'payload' => [
                    'portal_state' => $portalState,
                ],
                'source_url' => self::PAGE_URL,
            ]);
        }

        $response = Http::timeout(20)
            ->retry(1, 250)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 SIMANSA Checker',
                'Accept' => 'application/json,text/plain,*/*',
            ])
            ->asForm()
            ->post(self::CHECK_URL, [
                'nisn' => $nisn,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Portal SPAN-PTKIN sedang tidak merespons dengan baik.');
        }

        $body = trim((string) $response->body());

        if ($body === '') {
            $this->unlinkRejectedLulusan($registration);

            return $this->persistResult($registration, [
                'status' => 'tidak_lulus',
                'message' => 'Tidak lulus seleksi SPAN-PTKIN atau data tidak ditemukan di portal.',
                'payload' => [
                    'nisn' => $nisn,
                    'nomor_pendaftaran' => $registrationNumber,
                ],
                'source_url' => self::CHECK_URL,
            ]);
        }

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            throw new RuntimeException('Format respons portal SPAN-PTKIN tidak dikenali.');
        }

        $accepted = filled(data_get($payload, 'nm_prodi')) && filled(data_get($payload, 'nm_ptain'));
        $status = $accepted ? 'lulus' : 'tidak_lulus';
        $message = $accepted
            ? sprintf(
                'Lulus SPAN-PTKIN di %s - %s.',
                data_get($payload, 'nm_ptain', '-'),
                data_get($payload, 'nm_prodi', '-')
            )
            : 'Tidak lulus seleksi SPAN-PTKIN.';

        if ($accepted) {
            $this->syncLulusan($registration, $payload);
        } else {
            $this->unlinkRejectedLulusan($registration);
        }

        return $this->persistResult($registration, [
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
            'source_url' => self::CHECK_URL,
        ]);
    }

    public function getPortalState(): array
    {
        return Cache::remember(self::PORTAL_STATE_CACHE_KEY, self::PORTAL_STATE_CACHE_SECONDS, function () {
            $response = Http::timeout(15)
                ->retry(1, 250)
                ->get(self::PAGE_URL);

            if ($response->failed()) {
                throw new RuntimeException('Gagal mengambil halaman portal SPAN-PTKIN.');
            }

            $html = (string) $response->body();
            $isClosed = Str::contains($html, 'Pengumuman dibuka pada hari');

            $message = $isClosed
                ? $this->extractOpeningMessage($html)
                : 'Portal pengumuman SPAN-PTKIN sudah terbuka.';

            return [
                'is_open' => !$isClosed,
                'message' => $message,
            ];
        });
    }

    private function extractOpeningMessage(string $html): string
    {
        if (preg_match('/Pengumuman dibuka pada hari.*?<strong>(.*?)<\/strong>/is', $html, $matches)) {
            $value = trim(strip_tags($matches[1]));
            $value = preg_replace('/\s+/', ' ', $value);

            return 'Portal pengumuman SPAN-PTKIN belum dibuka. Jadwal resmi: ' . $value . '.';
        }

        return 'Portal pengumuman SPAN-PTKIN belum dibuka.';
    }

    private function persistResult(SpanPtkinRegistration $registration, array $result): array
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

    private function syncLulusan(SpanPtkinRegistration $registration, array $payload): void
    {
        $campusName = trim((string) data_get($payload, 'nm_ptain', ''));
        $programName = trim((string) data_get($payload, 'nm_prodi', ''));

        if ($campusName === '' || $programName === '') {
            return;
        }

        [$referensiPerguruanTinggi, $referensiProgramStudi] = $this->matchReferences($campusName, $programName);

        $existing = SiswaLulusan::query()
            ->where('siswa_id', $registration->siswa_id)
            ->where('tahun_pelajaran_id', $registration->tahun_pelajaran_id)
            ->first();

        SiswaLulusan::updateOrCreate(
            [
                'siswa_id' => $registration->siswa_id,
                'tahun_pelajaran_id' => $registration->tahun_pelajaran_id,
            ],
            [
                'span_ptkin_registration_id' => $registration->id,
                'referensi_perguruan_tinggi_id' => $referensiPerguruanTinggi?->id,
                'referensi_program_studi_id' => $referensiProgramStudi?->id,
                'jalur_masuk' => 'SPAN-PTKIN',
                'nama_universitas' => $referensiPerguruanTinggi?->nama ?? $campusName,
                'nama_universitas_manual' => $referensiPerguruanTinggi ? null : $campusName,
                'jurusan_fakultas' => $referensiProgramStudi?->fakultas ?? $existing?->jurusan_fakultas,
                'program_studi' => $referensiProgramStudi
                    ? trim($referensiProgramStudi->jenjang . ' ' . $referensiProgramStudi->nama)
                    : $programName,
                'program_studi_manual' => $referensiProgramStudi ? null : $programName,
                'keterangan' => $this->mergeKeterangan($existing?->keterangan, $registration),
            ]
        );
    }

    private function unlinkRejectedLulusan(SpanPtkinRegistration $registration): void
    {
        SiswaLulusan::query()
            ->where('siswa_id', $registration->siswa_id)
            ->where('tahun_pelajaran_id', $registration->tahun_pelajaran_id)
            ->where(function ($query) use ($registration) {
                $query->where('span_ptkin_registration_id', $registration->id)
                    ->orWhere('jalur_masuk', 'SPAN-PTKIN');
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

    private function mergeKeterangan(?string $existing, SpanPtkinRegistration $registration): ?string
    {
        $existing = trim((string) $existing);
        $note = 'Sinkron otomatis dari checker SPAN-PTKIN. Nomor pendaftaran: ' . $registration->nomor_pendaftaran;

        if ($existing === '') {
            return $note;
        }

        if (Str::contains($existing, $registration->nomor_pendaftaran)) {
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
