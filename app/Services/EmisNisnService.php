<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class EmisNisnService
{
    protected $apiUrl;
    protected $bearerToken;
    protected $institutionLookupToken;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.emis.api_url', 'https://api-emis.kemenag.go.id/v1');
        
        // Prefer emis_institusi_token (operator lembaga), fallback ke emis_api_token, lalu config
        $institutionToken = DB::table('api_tokens')->where('name', 'emis_institusi_token')->whereNotNull('token')->first();
        $centralToken = DB::table('api_tokens')->where('name', 'emis_api_token')->whereNotNull('token')->first();

        $this->bearerToken = $institutionToken?->token
            ?: $centralToken?->token
            ?: config('services.emis.bearer_token');

        // Endpoint referensi institusi membutuhkan cakupan token Admin Pusat.
        // Token Lembaga tetap menjadi token utama untuk data siswa lembaga.
        $this->institutionLookupToken = $centralToken?->token ?: $this->bearerToken;
        
        $this->timeout = 15; // 15 seconds timeout (reduce spinner wait time)
    }

    /**
     * Cek data NISN dari API EMIS Kemenag (Both Kemdikbud & Kemenag)
     *
     * @param string $nisn
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
                    'Authorization' => 'Bearer ' . $this->bearerToken,
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
                $response1 = $http->get($this->apiUrl . "/students/pusdatin/{$nisn}/0");
                
                Log::info('EmisNisnService: Kemdikbud API Response', [
                    'status' => $response1->status(),
                    'body' => $response1->body()
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
                $response2 = $http->get($this->apiUrl . "/students/student-ppdb-search?fnisn={$nisn}");
                
                Log::info('EmisNisnService: Kemenag API Response', [
                    'status' => $response2->status(),
                    'body' => $response2->body()
                ]);

                if ($response2->status() === 401) {
                    $unauthorized = true;
                } elseif ($response2->successful()) {
                    $data = $response2->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results']) && !empty($data['results'])) {
                        $kemenagData = $data['results'][0]; // Get first result from array

                        // Coba fetch parents via student numeric id endpoint
                        $studentNumId = $kemenagData['id'] ?? null;
                        if ($studentNumId) {
                            try {
                                $rParents = $http->get($this->apiUrl . "/students/students/{$studentNumId}/parents");
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
                        'kemenag' => $kemenagData
                    ]
                ];
            }

            if ($unauthorized) {
                Log::error('EmisNisnService: Unauthorized - Token mungkin expired atau invalid');

                return [
                    'success' => false,
                    'message' => 'Token API EMIS expired atau invalid. Silakan perbarui token EMIS terlebih dahulu.',
                    'data' => null
                ];
            }

            // No data found from both sources
            return [
                'success' => false,
                'message' => 'NISN tidak ditemukan dalam database EMIS (Kemdikbud & Kemenag)',
                'data' => null
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('EmisNisnService: Connection error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server API EMIS. Periksa koneksi internet Anda.',
                'data' => null
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('EmisNisnService: Request error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Request timeout atau ditolak. Silakan coba lagi.',
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('EmisNisnService: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function lookupInstitutionByNpsn(string $npsn): array
    {
        $npsn = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $npsn));

        if (!preg_match('/^[A-Z0-9]{8}$/', $npsn)) {
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
                    'Authorization' => 'Bearer ' . $this->institutionLookupToken,
                ]);

            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get($this->apiUrl . '/institutions/list', [
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

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi API institusi EMIS. Status: ' . $response->status(),
                    'data' => null,
                ];
            }

            $rows = collect($response->json('results', []));
            $institution = $rows->first(function ($row) use ($npsn) {
                return strtoupper((string) ($row['npsn'] ?? '')) === $npsn;
            }) ?: $rows->first();

            if (!$institution) {
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
                'message' => 'Terjadi kesalahan saat mengecek NSM: ' . $exception->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Validate NISN format
     *
     * @param string $nisn
     * @return bool
     */
    public function validateNisnFormat($nisn)
    {
        // NISN harus numeric dan tepat 10 digit
        return is_numeric($nisn) && strlen($nisn) === 10;
    }
}
