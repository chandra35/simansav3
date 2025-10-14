<?php

namespace App\Services;

use App\Models\Sekolah;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KemendikbudApiService
{
    protected $baseUrl = 'https://referensi.data.kemendikdasmen.go.id';
    
    /**
     * Get sekolah data (Database first, then API fallback)
     * 
     * @param string $npsn
     * @return array
     */
    public function getSekolah($npsn)
    {
        // STEP 1: Check database first
        $sekolah = Sekolah::find($npsn);
        
        if ($sekolah) {
            Log::info("Sekolah NPSN {$npsn} found in database");
            
            // Optional: Re-fetch if data is stale (> 6 months)
            if ($sekolah->isStale()) {
                Log::info("Data sekolah {$npsn} is stale, refreshing from API...");
                return $this->refreshSekolahData($sekolah);
            }
            
            return [
                'success' => true,
                'source' => 'database',
                'data' => $sekolah
            ];
        }
        
        // STEP 2: Not in DB, fetch from API
        Log::info("Sekolah NPSN {$npsn} not found in database, fetching from API...");
        return $this->fetchAndSaveFromApi($npsn);
    }
    
    /**
     * Fetch from API and save to database
     * 
     * @param string $npsn
     * @return array
     */
    protected function fetchAndSaveFromApi($npsn)
    {
        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => false]) // Skip SSL verification for testing
                ->get("{$this->baseUrl}/pendidikan/npsn/{$npsn}");
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengakses API Kemendikbud (HTTP ' . $response->status() . ')'
                ];
            }
            
            $data = $this->parseHtmlResponse($response->body(), $npsn);
            
            if (!$data) {
                return [
                    'success' => false,
                    'message' => 'Data sekolah tidak ditemukan atau gagal parsing HTML'
                ];
            }
            
            // Save to database
            $sekolah = Sekolah::create($data);
            
            Log::info("Sekolah NPSN {$npsn} successfully saved to database from API");
            
            return [
                'success' => true,
                'source' => 'api',
                'data' => $sekolah
            ];
            
        } catch (\Exception $e) {
            Log::error("Kemendikbud API Error for NPSN {$npsn}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse HTML response from Kemendikbud
     * 
     * @param string $html
     * @param string $npsn
     * @return array|null
     */
    protected function parseHtmlResponse($html, $npsn)
    {
        try {
            $data = [
                'npsn' => $npsn,
                'nama' => null,
                'status' => null,
                'bentuk_pendidikan' => null,
                'alamat_jalan' => null,
                'desa_kelurahan' => null,
                'kecamatan' => null,
                'kabupaten_kota' => null,
                'provinsi' => null,
                'last_fetched_at' => now(),
            ];
            
            // Parse table rows
            preg_match_all('/<tr>(.*?)<\/tr>/is', $html, $rows);
            
            foreach ($rows[1] as $row) {
                // Clean HTML
                $cleanRow = strip_tags($row);
                $cleanRow = html_entity_decode($cleanRow);
                $cleanRow = preg_replace('/&nbsp;/', '', $cleanRow);
                $cleanRow = preg_replace('/\s+/', ' ', $cleanRow);
                $cleanRow = trim($cleanRow);
                
                // Split by colon
                if (strpos($cleanRow, ':') !== false) {
                    $parts = explode(':', $cleanRow, 2);
                    $label = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    // Map labels to data fields
                    switch (true) {
                        case stripos($label, 'Nama') !== false && !stripos($label, 'Desa'):
                            $data['nama'] = $value;
                            break;
                            
                        case stripos($label, 'Status Sekolah') !== false:
                            $data['status'] = $value;
                            break;
                            
                        case stripos($label, 'Bentuk Pendidikan') !== false:
                            $data['bentuk_pendidikan'] = $value;
                            break;
                            
                        case stripos($label, 'Alamat') !== false && !stripos($label, 'Desa'):
                            $data['alamat_jalan'] = $value;
                            break;
                            
                        case stripos($label, 'Desa') !== false || stripos($label, 'Kelurahan') !== false:
                            $data['desa_kelurahan'] = $value;
                            break;
                            
                        case stripos($label, 'Kecamatan') !== false:
                            $data['kecamatan'] = $value;
                            break;
                            
                        case stripos($label, 'Kab') !== false:
                            $data['kabupaten_kota'] = $value;
                            break;
                            
                        case stripos($label, 'Propinsi') !== false || stripos($label, 'Provinsi') !== false:
                            $data['provinsi'] = $value;
                            break;
                    }
                }
            }
            
            // Validate required fields
            if (empty($data['nama'])) {
                Log::warning("Failed to parse nama sekolah for NPSN {$npsn}");
                return null;
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Log::error("HTML parsing error for NPSN {$npsn}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Refresh stale data from API
     * 
     * @param Sekolah $sekolah
     * @return array
     */
    protected function refreshSekolahData(Sekolah $sekolah)
    {
        $result = $this->fetchAndSaveFromApi($sekolah->npsn);
        
        if ($result['success']) {
            return [
                'success' => true,
                'source' => 'api_refresh',
                'data' => $result['data']
            ];
        }
        
        // If API fails, return existing data
        Log::warning("Failed to refresh data for NPSN {$sekolah->npsn}, using existing data");
        
        return [
            'success' => true,
            'source' => 'database_fallback',
            'data' => $sekolah
        ];
    }
}
