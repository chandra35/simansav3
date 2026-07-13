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
            
            // Save to database (updateOrCreate agar tidak error duplicate key saat refresh data stale)
            $sekolah = $this->saveSchoolPayload($npsn, $data);
            
            Log::info("Sekolah NPSN {$npsn} successfully saved/updated to database from API");
            
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
    public function fetchAndSaveFromReferensi(string $npsn): array
    {
        return $this->fetchAndSaveFromApi($npsn);
    }

    protected function parseHtmlResponse($html, $npsn)
    {
        try {
            $data = [
                'npsn' => $npsn,
                'nama' => null,
                'status' => null,
                'bentuk_pendidikan' => null,
                'jenjang_pendidikan' => null,
                'kementerian_pembina' => null,
                'npyp' => null,
                'no_sk_pendirian' => null,
                'tanggal_sk_pendirian' => null,
                'no_sk_operasional' => null,
                'tanggal_sk_operasional' => null,
                'akreditasi' => null,
                'luas_tanah' => null,
                'akses_internet' => null,
                'sumber_listrik' => null,
                'alamat_jalan' => null,
                'desa_kelurahan' => null,
                'kecamatan' => null,
                'kabupaten_kota' => null,
                'provinsi' => null,
                'telepon' => null,
                'email' => null,
                'website' => null,
                'operator' => null,
                'lintang' => null,
                'bujur' => null,
                'sumber_data_sekolah' => 'referensi-kemendikdasmen',
                'last_fetched_at' => now(),
            ];

            foreach ($this->extractLabelValuePairs($html) as $label => $value) {
                $labelKey = $this->normalizeLabel($label);
                $value = $this->cleanValue($value);

                if ($value === null) {
                    continue;
                }

                switch (true) {
                    case str_contains($labelKey, 'nama') && !str_contains($labelKey, 'desa'):
                        $data['nama'] = $value;
                        break;

                    case str_contains($labelKey, 'status sekolah'):
                        $data['status'] = strtoupper($value);
                        break;

                    case str_contains($labelKey, 'bentuk pendidikan'):
                        $data['bentuk_pendidikan'] = $value;
                        break;

                    case str_contains($labelKey, 'jenjang pendidikan'):
                        $data['jenjang_pendidikan'] = $value;
                        break;

                    case str_contains($labelKey, 'kementerian pembina'):
                        $data['kementerian_pembina'] = $value;
                        break;

                    case $labelKey === 'npyp':
                        $data['npyp'] = $value;
                        break;

                    case str_contains($labelKey, 'no sk pendirian'):
                        $data['no_sk_pendirian'] = $value;
                        break;

                    case str_contains($labelKey, 'tanggal sk pendirian'):
                        $data['tanggal_sk_pendirian'] = $value;
                        break;

                    case str_contains($labelKey, 'nomor sk operasional'):
                        $data['no_sk_operasional'] = $value;
                        break;

                    case str_contains($labelKey, 'tanggal sk operasional'):
                        $data['tanggal_sk_operasional'] = $value;
                        break;

                    case str_contains($labelKey, 'akreditasi'):
                        $data['akreditasi'] = $value;
                        break;

                    case str_contains($labelKey, 'luas tanah'):
                        $data['luas_tanah'] = $value;
                        break;

                    case str_contains($labelKey, 'akses internet'):
                        $data['akses_internet'] = $value;
                        break;

                    case str_contains($labelKey, 'sumber listrik'):
                        $data['sumber_listrik'] = $value;
                        break;

                    case str_contains($labelKey, 'alamat') && !str_contains($labelKey, 'desa'):
                        $data['alamat_jalan'] = $value;
                        break;

                    case str_contains($labelKey, 'desa') || str_contains($labelKey, 'kelurahan'):
                        $data['desa_kelurahan'] = $value;
                        break;

                    case str_contains($labelKey, 'kecamatan'):
                        $data['kecamatan'] = $value;
                        break;

                    case str_contains($labelKey, 'kab') || str_contains($labelKey, 'kota negara'):
                        $data['kabupaten_kota'] = $value;
                        break;

                    case str_contains($labelKey, 'propinsi') || str_contains($labelKey, 'provinsi'):
                        $data['provinsi'] = $value;
                        break;

                    case $labelKey === 'telepon':
                        $data['telepon'] = $value;
                        break;

                    case $labelKey === 'email':
                        $data['email'] = $value;
                        break;

                    case $labelKey === 'website':
                        $data['website'] = $value;
                        break;

                    case $labelKey === 'operator':
                        $data['operator'] = $value;
                        break;

                    case str_contains($labelKey, 'lintang'):
                        $data['lintang'] = $this->normalizeCoordinate($value);
                        break;

                    case str_contains($labelKey, 'bujur'):
                        $data['bujur'] = $this->normalizeCoordinate($value);
                        break;
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

    protected function saveSchoolPayload(string $npsn, array $data): Sekolah
    {
        $payload = collect($data)
            ->reject(fn ($value, $key) => $key === 'npsn' || blank($value))
            ->all();

        return Sekolah::updateOrCreate(['npsn' => $npsn], $payload);
    }

    protected function extractLabelValuePairs(string $html): array
    {
        $html = preg_replace('/<(script|style).*?<\/\1>/is', '', $html);
        $html = preg_replace('/<(br|\/div|\/p|\/tr|\/li|\/h[1-6])\b[^>]*>/i', "\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $lines = collect(explode("\n", $text))
            ->map(fn ($line) => trim(preg_replace('/\s+/u', ' ', $line)))
            ->filter(fn ($line) => $line !== '' && str_contains($line, ':'));

        $pairs = [];
        foreach ($lines as $line) {
            [$label, $value] = array_pad(explode(':', $line, 2), 2, null);
            $label = trim((string) $label);

            if ($label !== '') {
                $pairs[$label] = trim((string) $value);
            }
        }

        return $pairs;
    }

    protected function normalizeLabel(string $label): string
    {
        $label = html_entity_decode($label, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $label = preg_replace('/\s+/u', ' ', $label);

        return strtolower(trim($label));
    }

    protected function cleanValue(?string $value): ?string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        if ($value === '' || $value === '-' || $value === '.000000000000') {
            return null;
        }

        return $value;
    }

    protected function normalizeCoordinate(?string $value): ?float
    {
        $value = $this->cleanValue($value);

        if ($value === null) {
            return null;
        }

        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
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
