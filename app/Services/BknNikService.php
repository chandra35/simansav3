<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BknNikService
{
    protected string $apiUrl = 'https://api-siasn.bkn.go.id/profilasn/api/dukcapil-keluarga';
    protected ?string $bearerToken;
    protected int $timeout = 30;

    public function __construct()
    {
        $tokenData = DB::table('api_tokens')->where('name', 'bkn_siasn_token')->first();
        $this->bearerToken = $tokenData?->token ?? config('services.bkn.bearer_token');
    }

    /**
     * Validasi NIK ke API Dukcapil via BKN SIASN.
     */
    public function cekNik(array $params): array
    {
        try {
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept'          => 'application/json',
                    'Origin'          => 'https://myasn.bkn.go.id',
                    'Referer'         => 'https://myasn.bkn.go.id/',
                    'X-Requested-With' => 'XMLHttpRequest',
                ]);

            if (!empty($this->bearerToken)) {
                $http = $http->withHeaders(['Authorization' => 'Bearer ' . $this->bearerToken]);
            }

            // Skip SSL verify on non-production
            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $query = [
                'id_usulan'     => $params['id_usulan'] ?? (string) Str::uuid(),
                'nik'           => $params['nik'],
                'nokk'          => $params['nokk'],
                'nama'          => $params['nama'],
                'sumber'        => 'MYASN',
                'tgl_lahir'     => $params['tgl_lahir'],
                'agama'         => $params['agama'],
                'jenis_kelamin' => $params['jenis_kelamin'],
            ];

            Log::info('BknNikService: Checking NIK', ['nik' => $params['nik']]);

            $response = $http->get($this->apiUrl, $query);

            Log::info('BknNikService: API Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $isValid = isset($data['is_valid']) && $data['is_valid'] === 'true';

                return [
                    'success'      => true,
                    'is_valid'     => $isValid,
                    'status'       => $data['status'] ?? '-',
                    'message'      => $data['message'] ?? '-',
                    'notification' => $data['notification'] ?? '-',
                    'error'        => $data['error'] ?? false,
                    'raw'          => $data,
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Token tidak valid atau expired. Perbarui token BKN SIASN di menu Update API Token.',
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'Data NIK tidak ditemukan di Dukcapil.',
                ];
            }

            return [
                'success' => false,
                'message' => 'API BKN merespons dengan status ' . $response->status() . '. Coba beberapa saat lagi.',
            ];

        } catch (Exception $e) {
            Log::error('BknNikService: Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server BKN: ' . $e->getMessage(),
            ];
        }
    }
}
