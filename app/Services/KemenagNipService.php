<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class KemenagNipService
{
    protected $apiUrl;
    protected $bearerToken;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.kemenag.api_url', 'https://be-pintar.kemenag.go.id/api/v1');
        $this->bearerToken = config('services.kemenag.bearer_token');
        $this->timeout = 30; // 30 seconds timeout
    }

    /**
     * Cek data NIP dari API Kemenag BE-PINTAR
     *
     * @param string $nip
     * @return array
     */
    public function cekNip($nip)
    {
        try {
            Log::info('KemenagNipService: Checking NIP', ['nip' => $nip]);

            // Validate token exists
            if (empty($this->bearerToken)) {
                throw new Exception('Bearer token tidak dikonfigurasi. Silakan set KEMENAG_BEARER_TOKEN di file .env');
            }

            // Hit API Kemenag
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->bearerToken,
                    'Origin' => 'https://pintar.kemenag.go.id',
                    'Referer' => 'https://pintar.kemenag.go.id/',
                ]);

            // Skip SSL verification untuk development (Windows SSL issue)
            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($this->apiUrl . '/cek_nip', [
                'nip' => $nip
            ]);

            // Log response for debugging
            Log::info('KemenagNipService: API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Check if request was successful
            if ($response->successful()) {
                $data = $response->json();

                // Check response structure
                if (isset($data['code']) && $data['code'] == 200) {
                    if (isset($data['data']) && !empty($data['data'])) {
                        return [
                            'success' => true,
                            'message' => $data['message'] ?? 'Data ditemukan',
                            'data' => $data['data']
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'NIP tidak ditemukan dalam database Kemenag',
                            'data' => null
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Data tidak ditemukan',
                        'data' => null
                    ];
                }
            }

            // Handle HTTP errors
            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'NIP tidak ditemukan dalam database Kemenag',
                    'data' => null
                ];
            }

            if ($response->status() === 401) {
                Log::error('KemenagNipService: Unauthorized - Token mungkin expired atau invalid');
                return [
                    'success' => false,
                    'message' => 'Token API expired atau invalid. Silakan hubungi administrator.',
                    'data' => null
                ];
            }

            if ($response->status() >= 500) {
                Log::error('KemenagNipService: Server error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Server API Kemenag sedang bermasalah. Silakan coba lagi nanti.',
                    'data' => null
                ];
            }

            // Other errors
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek NIP. Status: ' . $response->status(),
                'data' => null
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('KemenagNipService: Connection error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server API Kemenag. Periksa koneksi internet Anda.',
                'data' => null
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('KemenagNipService: Request error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Request timeout atau ditolak. Silakan coba lagi.',
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('KemenagNipService: Unexpected error', [
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
     * Validate NIP format
     *
     * @param string $nip
     * @return bool
     */
    public function validateNipFormat($nip)
    {
        // NIP harus numeric dan tepat 18 digit
        return is_numeric($nip) && strlen($nip) === 18;
    }

    /**
     * Get data lengkap dari API Kemenag untuk sync GTK
     * Menggunakan endpoint YANG SAMA dengan cekNip() tapi dengan parsing lengkap 60+ field
     * 
     * Note: Awalnya dicoba endpoint /api/pintar/satminkal tapi tidak tersedia (404)
     * Jadi menggunakan endpoint /api/v1/cek_nip yang sudah proven working
     *
     * @param string $nip
     * @return array
     */
    public function getSatminkalData($nip)
    {
        try {
            Log::info('KemenagNipService: Getting Satminkal data', ['nip' => $nip]);

            // Validate token exists
            if (empty($this->bearerToken)) {
                throw new Exception('Bearer token tidak dikonfigurasi. Silakan set KEMENAG_BEARER_TOKEN di file .env');
            }

            // Hit API Kemenag - Gunakan endpoint YANG SAMA dengan cekNip (lebih reliable)
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->bearerToken,
                    'Origin' => 'https://pintar.kemenag.go.id',
                    'Referer' => 'https://pintar.kemenag.go.id/',
                ]);

            // Skip SSL verification untuk development
            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            // POST request dengan body JSON (SAMA dengan endpoint cekNip)
            $response = $http->post($this->apiUrl . '/cek_nip', [
                'nip' => $nip
            ]);

            // Log response
            Log::info('KemenagNipService: getSatminkalData API Response', [
                'status' => $response->status(),
                'body_length' => strlen($response->body()),
                'has_data' => isset($response->json()['data'])
            ]);

            // Check if request was successful
            if ($response->successful()) {
                $data = $response->json();

                // Check response structure
                if (isset($data['code']) && $data['code'] == 200) {
                    if (isset($data['data']) && !empty($data['data'])) {
                        // Parse response lengkap
                        $parsedData = $this->parseFullResponse($data['data']);
                        
                        return [
                            'success' => true,
                            'message' => $data['message'] ?? 'Data ditemukan',
                            'data' => $parsedData,
                            'raw_data' => $data['data'] // Keep original untuk backup
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'NIP tidak ditemukan dalam database Kemenag',
                            'data' => null
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Data tidak ditemukan',
                        'data' => null
                    ];
                }
            }

            // Handle HTTP errors
            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'NIP tidak ditemukan dalam database Kemenag',
                    'data' => null
                ];
            }

            if ($response->status() === 401) {
                Log::error('KemenagNipService: Unauthorized - getSatminkalData');
                return [
                    'success' => false,
                    'message' => 'Token API expired atau invalid. Silakan hubungi administrator.',
                    'data' => null
                ];
            }

            if ($response->status() >= 500) {
                Log::error('KemenagNipService: Server error - getSatminkalData', [
                    'status' => $response->status()
                ]);
                return [
                    'success' => false,
                    'message' => 'Server API Kemenag sedang bermasalah. Silakan coba lagi nanti.',
                    'data' => null
                ];
            }

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data. Status: ' . $response->status(),
                'data' => null
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('KemenagNipService: Connection error - getSatminkalData', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server API Kemenag. Periksa koneksi internet Anda.',
                'data' => null
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('KemenagNipService: Request error - getSatminkalData', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Request timeout atau ditolak. Silakan coba lagi.',
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('KemenagNipService: Unexpected error - getSatminkalData', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Parse response lengkap dari API Satminkal
     * Convert semua field ke format yang sesuai dengan database
     *
     * @param array $data
     * @return array
     */
    private function parseFullResponse($data)
    {
        // Convert jenis kelamin: 1 = L, 2 = P
        $jenisKelamin = null;
        if (isset($data['JENIS_KELAMIN'])) {
            $jenisKelamin = $data['JENIS_KELAMIN'] == 1 ? 'L' : 'P';
        }

        // Parse tanggal (format: "1989-09-09 00:00:00" → "1989-09-09")
        $parseDate = function($dateString) {
            if (empty($dateString)) return null;
            try {
                return date('Y-m-d', strtotime($dateString));
            } catch (Exception $e) {
                return null;
            }
        };

        // Return array lengkap dengan mapping field
        return [
            // Data Identitas
            'nip' => $data['NIP'] ?? null,
            'nip_baru' => $data['NIP_BARU'] ?? null,
            'nama' => $data['NAMA'] ?? null,
            'nama_lengkap' => $data['NAMA_LENGKAP'] ?? null,
            'nik' => null, // Tidak ada di response
            'agama' => $data['AGAMA'] ?? null,
            'tempat_lahir' => $data['TEMPAT_LAHIR'] ?? null,
            'tanggal_lahir' => $parseDate($data['TANGGAL_LAHIR'] ?? null),
            'jenis_kelamin' => $jenisKelamin,
            'status_kawin' => $data['STATUS_KAWIN'] ?? null,
            
            // Data Pendidikan
            'pendidikan' => $data['PENDIDIKAN'] ?? null,
            'jenjang_pendidikan' => $data['JENJANG_PENDIDIKAN'] ?? null,
            'kode_bidang_studi' => $data['KODE_BIDANG_STUDI'] ?? null,
            'bidang_studi' => $data['BIDANG_STUDI'] ?? null,
            
            // Data Kontak
            'telepon' => $data['TELEPON'] ?? null,
            'no_hp' => $data['NO_HP'] ?? null,
            'email' => $data['EMAIL'] ?? null,
            'email_dinas' => $data['EMAIL_DINAS'] ?? null,
            
            // Data Alamat
            'alamat_1' => $data['ALAMAT_1'] ?? null,
            'alamat_2' => $data['ALAMAT_2'] ?? null,
            'kab_kota' => $data['KAB_KOTA'] ?? null,
            'provinsi' => $data['PROVINSI'] ?? null,
            'kode_pos' => $data['KODE_POS'] ?? null,
            'kode_lokasi' => $data['KODE_LOKASI'] ?? null,
            'lat' => $data['LAT'] ?? null,
            'lon' => $data['LON'] ?? null,
            
            // Data Kepegawaian
            'status_pegawai' => $data['STATUS_PEGAWAI'] ?? null,
            'kode_pangkat' => $data['KODE_PANGKAT'] ?? null,
            'pangkat' => $data['PANGKAT'] ?? null,
            'gol_ruang' => $data['GOL_RUANG'] ?? null,
            'tmt_cpns' => $parseDate($data['TMT_CPNS'] ?? null),
            'tmt_pangkat' => $parseDate($data['TMT_PANGKAT'] ?? null),
            'tmt_pangkat_yad' => $parseDate($data['tmt_pangkat_yad'] ?? null),
            
            // Data Jabatan
            'tipe_jabatan' => $data['TIPE_JABATAN'] ?? null,
            'kode_jabatan' => $data['KODE_JABATAN'] ?? null,
            'tampil_jabatan' => $data['TAMPIL_JABATAN'] ?? null,
            'kode_level_jabatan' => $data['KODE_LEVEL_JABATAN'] ?? null,
            'level_jabatan' => $data['LEVEL_JABATAN'] ?? null,
            'tmt_jabatan' => $parseDate($data['TMT_JABATAN'] ?? null),
            
            // Data Satuan Kerja
            'kode_satuan_kerja' => $data['KODE_SATUAN_KERJA'] ?? null,
            'satker_1' => $data['SATKER_1'] ?? null,
            'kode_satker_2' => $data['KODE_SATKER_2'] ?? null,
            'satker_2' => $data['SATKER_2'] ?? null,
            'kode_satker_3' => $data['KODE_SATKER_3'] ?? null,
            'satker_3' => $data['SATKER_3'] ?? null,
            'kode_satker_4' => $data['KODE_SATKER_4'] ?? null,
            'satker_4' => $data['SATKER_4'] ?? null,
            'kode_satker_5' => $data['KODE_SATKER_5'] ?? null,
            'satker_5' => $data['SATKER_5'] ?? null,
            'kode_grup_satuan_kerja' => $data['KODE_GRUP_SATUAN_KERJA'] ?? null,
            'grup_satuan_kerja' => $data['GRUP_SATUAN_KERJA'] ?? null,
            'keterangan_satuan_kerja' => $data['KETERANGAN_SATUAN_KERJA'] ?? null,
            'satker_kelola' => $data['SATKER_KELOLA'] ?? null,
            
            // Data Masa Kerja
            'mk_tahun' => $data['MK_TAHUN'] ?? 0,
            'mk_bulan' => $data['MK_BULAN'] ?? 0,
            'mk_tahun_1' => $data['MK_TAHUN_1'] ?? 0,
            'mk_bulan_1' => $data['MK_BULAN_1'] ?? 0,
            
            // Data Gaji
            'gaji_pokok' => $data['Gaji_Pokok'] ?? 0,
            'tmt_kgb_yad' => $parseDate($data['tmt_kgb_yad'] ?? null),
            
            // Data Pensiun
            'usia_pensiun' => $data['USIA_PENSIUN'] ?? 58,
            'tmt_pensiun' => $parseDate($data['TMT_PENSIUN'] ?? null),
            
            // Data Madrasah
            'nsm' => $data['NSM'] ?? null,
            'npsn' => $data['NPSN'] ?? null,
            'kode_kua' => $data['KODE_KUA'] ?? null,
            'hari_kerja' => $data['HARI_KERJA'] ?? 5,
            
            // Data Tambahan
            'iso' => $data['ISO'] ?? null,
            'keterangan' => $data['KETERANGAN'] ?? null,
        ];
    }
}
