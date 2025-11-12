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
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.emis.api_url', 'https://api-emis.kemenag.go.id/v1');
        
        // Get token from database first, fallback to config
        $tokenData = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
        $this->bearerToken = $tokenData ? $tokenData->token : config('services.emis.bearer_token');
        
        $this->timeout = 30; // 30 seconds timeout
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

            // 1. Fetch Kemdikbud data (Pusdatin endpoint)
            try {
                $response1 = $http->get($this->apiUrl . "/students/pusdatin/{$nisn}/0");
                
                Log::info('EmisNisnService: Kemdikbud API Response', [
                    'status' => $response1->status(),
                    'body' => $response1->body()
                ]);

                if ($response1->successful()) {
                    $data = $response1->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results'])) {
                        $kemdikbudData = $data['results'];
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

                if ($response2->successful()) {
                    $data = $response2->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results']) && !empty($data['results'])) {
                        $kemenagData = $data['results'][0]; // Get first result from array
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

            // No data found from both sources
            return [
                'success' => false,
                'message' => 'NISN tidak ditemukan dalam database EMIS (Kemdikbud & Kemenag)',
                'data' => null
            ];

            // Handle HTTP errors
            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'NISN tidak ditemukan dalam database EMIS',
                    'data' => null
                ];
            }

            if ($response->status() === 401) {
                Log::error('EmisNisnService: Unauthorized - Token mungkin expired atau invalid');
                return [
                    'success' => false,
                    'message' => 'Token API expired atau invalid. Silakan hubungi administrator untuk memperbarui token EMIS.',
                    'data' => null
                ];
            }

            if ($response->status() >= 500) {
                Log::error('EmisNisnService: Server error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Server API EMIS sedang bermasalah. Silakan coba lagi nanti.',
                    'data' => null
                ];
            }

            // Other errors
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek NISN. Status: ' . $response->status(),
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
