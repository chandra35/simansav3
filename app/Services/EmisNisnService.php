<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmisNisnService
{
    protected $apiUrl;

    protected $bearerToken;

    protected $institutionLookupToken;

    protected $timeout;

    public function __construct(?string $bearerToken = null)
    {
        $this->apiUrl = config('services.emis.api_url', 'https://api-emis.kemenag.go.id/v1');

        if ($bearerToken !== null) {
            $this->bearerToken = $bearerToken;
            $this->institutionLookupToken = $bearerToken;
        } else {
            // Prefer emis_institusi_token (operator lembaga), fallback ke emis_api_token, lalu config
            $institutionToken = DB::table('api_tokens')->where('name', 'emis_institusi_token')->whereNotNull('token')->first();
            $centralToken = DB::table('api_tokens')->where('name', 'emis_api_token')->whereNotNull('token')->first();

            $this->bearerToken = $institutionToken?->token
                ?: $centralToken?->token
                ?: config('services.emis.bearer_token');

            // Endpoint referensi institusi membutuhkan cakupan token Admin Pusat.
            // Token Lembaga tetap menjadi token utama untuk data siswa lembaga.
            $this->institutionLookupToken = $centralToken?->token ?: $this->bearerToken;
        }

        $this->timeout = 15; // 15 seconds timeout (reduce spinner wait time)
    }

    public function isConfigured(): bool
    {
        return filled($this->bearerToken);
    }

    /**
     * Periksa riwayat data peserta didik pada layanan SPL EMIS.
     * Token hanya digunakan oleh server dan tidak pernah dikirimkan ke browser.
     */
    public function cekNisnSpl(string $nisn): array
    {
        if (! $this->validateNisnFormat($nisn)) {
            return $this->splFailure('invalid_nisn', 'NISN harus terdiri dari tepat 10 digit angka.');
        }

        if (! $this->isConfigured()) {
            return $this->splFailure('credential_missing', 'Token EMIS belum dikonfigurasi. Perbarui melalui menu Update API Token.');
        }

        try {
            $request = Http::acceptJson()
                ->connectTimeout(8)
                ->timeout($this->timeout)
                ->withToken($this->bearerToken);

            if (! app()->environment('production')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($this->apiUrl.'/students/students/check-spl-student-data', [
                'type' => 'nisn',
                'number' => $nisn,
            ]);

            Log::info('EMIS SPL NISN response', [
                'nisn' => self::maskNisn($nisn),
                'status' => $response->status(),
            ]);

            if (in_array($response->status(), [401, 403, 419], true)) {
                return $this->splFailure('token_expired', 'Token EMIS kedaluwarsa, tidak valid, atau belum memiliki akses SPL.');
            }

            if ($response->status() === 429) {
                return $this->splFailure('rate_limited', 'Permintaan ke EMIS terlalu banyak. Tunggu beberapa saat lalu coba kembali.');
            }

            if ($response->serverError()) {
                return $this->splFailure('upstream_error', 'Layanan EMIS sedang bermasalah. Silakan coba lagi nanti.');
            }

            if (! $response->successful()) {
                return $this->splFailure('request_rejected', 'Permintaan pemeriksaan SPL ditolak oleh EMIS.');
            }

            $payload = $response->json();
            if (! is_array($payload) || ! array_key_exists('success', $payload)) {
                return $this->splFailure('invalid_response', 'Respons EMIS tidak sesuai format yang dikenali.');
            }

            $records = collect($payload['results'] ?? [])
                ->filter(fn ($row) => is_array($row))
                ->map(fn (array $row) => $this->normalizeSplRecord($row))
                ->values()
                ->all();

            if (($payload['success'] ?? false) !== true || $records === []) {
                return $this->splFailure('not_found', 'Tidak ada riwayat SPL yang dapat ditampilkan untuk NISN ini.');
            }

            return [
                'success' => true,
                'code' => 'found',
                'message' => count($records).' riwayat SPL ditemukan.',
                'data' => [
                    'nisn' => $nisn,
                    'records' => $records,
                ],
            ];
        } catch (ConnectionException) {
            return $this->splFailure('connection_error', 'Tidak dapat terhubung ke layanan EMIS. Silakan coba lagi.');
        } catch (Throwable $exception) {
            Log::error('EMIS SPL NISN check failed', [
                'nisn' => self::maskNisn($nisn),
                'exception' => $exception::class,
            ]);

            return $this->splFailure('unexpected_error', 'Terjadi kesalahan saat memeriksa NISN melalui EMIS.');
        }
    }

    public static function maskNisn(string $nisn): string
    {
        return strlen($nisn) === 10 ? substr($nisn, 0, 3).'****'.substr($nisn, -3) : '***';
    }

    private function normalizeSplRecord(array $row): array
    {
        return [
            'nama' => $row['nama'] ?? null,
            'nisn' => $row['nisn'] ?? null,
            'nik' => $row['nik'] ?? null,
            'nama_ibu_kandung' => $row['nama_ibu_kandung'] ?? null,
            'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
            'tanggal_keluar' => $row['tanggal_keluar'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
            'is_disable' => (int) ($row['is_disable'] ?? 0),
            'jenis_keluar_id' => $row['jenis_keluar_id'] ?? null,
            'tingkat_pendidikan_id' => $row['tingkat_pendidikan_id'] ?? null,
            'peserta_didik_id' => $row['peserta_didik_id'] ?? null,
            'sekolah_id' => $row['sekolah_id'] ?? null,
            'sekolah_id_reservasi' => $row['sekolah_id_reservasi'] ?? null,
        ];
    }

    private function splFailure(string $code, string $message): array
    {
        return ['success' => false, 'code' => $code, 'message' => $message, 'data' => null];
    }

    /**
     * Cek data NISN dari API EMIS Kemenag (Both Kemdikbud & Kemenag)
     *
     * @param  string  $nisn
     * @return array
     */
    public function cekNisn($nisn)
    {
        try {
            Log::info('EmisNisnService: Checking NISN from both sources', ['nisn' => $nisn]);

            // Validate token exists
            if (empty($this->bearerToken)) {
                throw new Exception('EMIS Bearer token tidak dikonfigurasi. Silakan set EMIS_BEARER_TOKEN di file .env');
            }

            // Prepare HTTP client
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->bearerToken,
                ]);

            // Skip SSL verification untuk development (Windows SSL issue)
            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            // Initialize data variables
            $kemdikbudData = null;
            $kemenagData = null;
            $unauthorized = false;

            // 1. Fetch Kemdikbud data (Pusdatin endpoint)
            try {
                $response1 = $http->get($this->apiUrl."/students/pusdatin/{$nisn}/0");

                Log::info('EmisNisnService: Kemdikbud API Response', [
                    'status' => $response1->status(),
                    'body' => $response1->body(),
                ]);

                if ($response1->status() === 401) {
                    $unauthorized = true;
                } elseif ($response1->successful()) {
                    $data = $response1->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results'])) {
                        // Check if data is "data tidak ditemukan"
                        if (is_array($data['results']) && isset($data['results']['data']) &&
                            $data['results']['data'] === 'data tidak ditemukan') {
                            // Data not found, keep null
                            $kemdikbudData = null;
                        } else {
                            $kemdikbudData = $data['results'];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('EmisNisnService: Kemdikbud API failed', ['error' => $e->getMessage()]);
            }

            // 2. Fetch Kemenag data (PPDB Search endpoint)
            try {
                $response2 = $http->get($this->apiUrl."/students/student-ppdb-search?fnisn={$nisn}");

                Log::info('EmisNisnService: Kemenag API Response', [
                    'status' => $response2->status(),
                    'body' => $response2->body(),
                ]);

                if ($response2->status() === 401) {
                    $unauthorized = true;
                } elseif ($response2->successful()) {
                    $data = $response2->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results']) && ! empty($data['results'])) {
                        $kemenagData = $data['results'][0]; // Get first result from array

                        // Coba fetch parents via student numeric id endpoint
                        $studentNumId = $kemenagData['id'] ?? null;
                        if ($studentNumId) {
                            try {
                                $rParents = $http->get($this->apiUrl."/students/students/{$studentNumId}/parents");
                                Log::info('EmisNisnService: Parents endpoint', [
                                    'url' => "/students/students/{$studentNumId}/parents",
                                    'status' => $rParents->status(),
                                    'body_preview' => substr($rParents->body(), 0, 200),
                                ]);
                                if ($rParents->successful()) {
                                    $pd = $rParents->json();
                                    $parentsResult = $pd['results'] ?? $pd['data'] ?? null;
                                    if ($parentsResult) {
                                        $kemenagData['parents'] = $parentsResult;
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning('EmisNisnService: parents endpoint failed', ['error' => $e->getMessage()]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('EmisNisnService: Kemenag API failed', ['error' => $e->getMessage()]);
            }

            // Check if at least one data source returned results
            if ($kemdikbudData || $kemenagData) {
                return [
                    'success' => true,
                    'message' => 'Data NISN ditemukan',
                    'data' => [
                        'kemdikbud' => $kemdikbudData,
                        'kemenag' => $kemenagData,
                    ],
                ];
            }

            if ($unauthorized) {
                Log::error('EmisNisnService: Unauthorized - Token mungkin expired atau invalid');

                return [
                    'success' => false,
                    'message' => 'Token API EMIS expired atau invalid. Silakan perbarui token EMIS terlebih dahulu.',
                    'data' => null,
                ];
            }

            // No data found from both sources
            return [
                'success' => false,
                'message' => 'NISN tidak ditemukan dalam database EMIS (Kemdikbud & Kemenag)',
                'data' => null,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('EmisNisnService: Connection error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server API EMIS. Periksa koneksi internet Anda.',
                'data' => null,
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('EmisNisnService: Request error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Request timeout atau ditolak. Silakan coba lagi.',
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('EmisNisnService: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function lookupInstitutionByNpsn(string $npsn): array
    {
        $npsn = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $npsn));

        if (! preg_match('/^[A-Z0-9]{8}$/', $npsn)) {
            return [
                'success' => false,
                'message' => 'Format NPSN tidak valid.',
                'data' => null,
            ];
        }

        if (empty($this->institutionLookupToken)) {
            return [
                'success' => false,
                'message' => 'Token referensi EMIS Admin Pusat belum dikonfigurasi.',
                'data' => null,
            ];
        }

        try {
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->institutionLookupToken,
                ]);

            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get($this->apiUrl.'/institutions/list', [
                'page' => 1,
                'q' => $npsn,
            ]);

            Log::info('EmisNisnService: Institution lookup response', [
                'npsn' => $npsn,
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Token referensi EMIS Admin Pusat expired, invalid, atau tidak memiliki akses institusi.',
                    'data' => null,
                ];
            }

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi API institusi EMIS. Status: '.$response->status(),
                    'data' => null,
                ];
            }

            $rows = collect($response->json('results', []));
            $institution = $rows->first(function ($row) use ($npsn) {
                return strtoupper((string) ($row['npsn'] ?? '')) === $npsn;
            }) ?: $rows->first();

            if (! $institution) {
                return [
                    'success' => false,
                    'message' => 'Data institusi Kemenag tidak ditemukan. Kemungkinan sekolah ini bukan madrasah.',
                    'data' => null,
                ];
            }

            $nsm = $institution['nsm'] ?? ($institution['statistic_num'] ?? null);
            if (blank($nsm)) {
                return [
                    'success' => false,
                    'message' => 'Institusi ditemukan, tetapi NSM masih kosong di EMIS.',
                    'data' => $institution,
                ];
            }

            return [
                'success' => true,
                'message' => 'Data institusi Kemenag ditemukan.',
                'data' => $institution,
            ];
        } catch (\Throwable $exception) {
            Log::error('EmisNisnService: Institution lookup failed', [
                'npsn' => $npsn,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek NSM: '.$exception->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Validate NISN format
     *
     * @param  string  $nisn
     * @return bool
     */
    public function validateNisnFormat($nisn)
    {
        // NISN harus numeric dan tepat 10 digit
        return is_numeric($nisn) && strlen($nisn) === 10;
    }
}
