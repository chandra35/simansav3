<?php

namespace App\Services;

use App\Models\SnbpRegistration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
}
