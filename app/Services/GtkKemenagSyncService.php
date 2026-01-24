<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\GtkKemenagSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GtkKemenagSyncService
{
    protected $kemenagService;

    public function __construct(KemenagNipService $kemenagService)
    {
        $this->kemenagService = $kemenagService;
    }

    /**
     * Sync data GTK dengan API Kemenag
     *
     * @param Gtk $gtk
     * @param string $userId
     * @return array
     */
    public function syncGtkData(Gtk $gtk, $userId)
    {
        // Validasi: GTK harus punya NIP
        if (empty($gtk->nip)) {
            return [
                'success' => false,
                'message' => 'GTK tidak memiliki NIP. Sinkronisasi tidak dapat dilakukan.',
                'data' => null
            ];
        }

        try {
            Log::info('GtkKemenagSyncService: Starting sync', [
                'gtk_id' => $gtk->id,
                'gtk_name' => $gtk->nama_lengkap,
                'nip' => $gtk->nip,
                'user_id' => $userId
            ]);

            // Call API Kemenag melalui KemenagNipService
            $apiResult = $this->kemenagService->getSatminkalData($gtk->nip);

            if (!$apiResult['success']) {
                // API call gagal
                return $this->saveSyncResult(
                    $gtk,
                    null,
                    'failed',
                    $apiResult['message'],
                    $userId,
                    [],
                    []
                );
            }

            // API berhasil, parse data
            $kemenagData = $apiResult['data'];
            $rawData = $apiResult['raw_data'];

            // Compare data lokal dengan data Kemenag
            $differences = $this->compareData($gtk, $kemenagData);

            // Save hasil sync ke database
            return $this->saveSyncResult(
                $gtk,
                $rawData,
                'success',
                'Sinkronisasi berhasil',
                $userId,
                $kemenagData,
                $differences
            );

        } catch (Exception $e) {
            Log::error('GtkKemenagSyncService: Sync error', [
                'gtk_id' => $gtk->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Compare data lokal GTK dengan data Kemenag
     * Hanya membandingkan field yang ADA di database lokal (gtks table)
     *
     * @param Gtk $gtk
     * @param array $kemenagData
     * @return array
     */
    private function compareData(Gtk $gtk, array $kemenagData)
    {
        $differences = [];

        // Mapping field lokal ke field Kemenag untuk perbandingan
        // HANYA field yang ADA di tabel gtks yang akan dibandingkan
        $fieldMapping = [
            'nama_lengkap' => 'nama_lengkap',
            'nip' => 'nip_baru', // NIP lokal vs NIP BARU (18 digit) dari Kemenag
            // NIK tidak di-compare karena tidak ada di response API Kemenag
            // NUPTK tidak di-compare karena tidak ada di response API Kemenag
            'jenis_kelamin' => 'jenis_kelamin',
            // 'tempat_lahir' TIDAK di-compare karena data lokal (dari KK/Akte) lebih akurat
            // Data Kemenag hanya menampilkan kabupaten, bukan kelurahan/desa sebenarnya
            'tanggal_lahir' => 'tanggal_lahir',
            'email' => 'email',
            'nomor_hp' => 'no_hp',
            'status_kepegawaian' => 'status_pegawai',
            'jabatan' => 'tampil_jabatan',
            'tmt_kerja' => 'tmt_pangkat', // Mengambil dari TMT_PANGKAT API Kemenag
        ];

        foreach ($fieldMapping as $localField => $kemenagField) {
            $localValue = $gtk->$localField ?? null;
            $kemenagValue = $kemenagData[$kemenagField] ?? null;

            // Normalize untuk perbandingan
            $localValueNormalized = $this->normalizeValue($localValue);
            $kemenagValueNormalized = $this->normalizeValue($kemenagValue);

            // Jika berbeda, simpan perbedaan
            if ($localValueNormalized !== $kemenagValueNormalized) {
                $differences[$localField] = [
                    'local' => $localValue,
                    'kemenag' => $kemenagValue,
                    'field_label' => $this->getFieldLabel($localField),
                    'is_info_only' => false // Field ini bisa di-apply
                ];
            }
        }

        // ============================================
        // COMPARE ALAMAT (Special handling)
        // ============================================
        // Bandingkan alamat dengan logic khusus karena format berbeda
        // Local: alamat (text) + kelurahan_id (dropdown)
        // Kemenag: alamat_1 + alamat_2 + kode_pos
        
        $localAlamatFull = $this->getFullAlamatLokal($gtk);
        $kemenagAlamatFull = $this->getFullAlamatKemenag($kemenagData);
        
        // Cek apakah alamat berbeda
        if (!$this->isAlamatSame($localAlamatFull, $kemenagAlamatFull)) {
            $differences['alamat_lengkap'] = [
                'local' => $localAlamatFull,
                'kemenag' => $kemenagAlamatFull,
                'field_label' => 'Alamat Lengkap',
                'is_info_only' => false
            ];
        }

        // Field yang ada di Kemenag tapi TIDAK ADA di database lokal
        // Field ini disimpan TERPISAH untuk keperluan display info saja
        // TIDAK dihitung sebagai "perbedaan" dan TIDAK ditampilkan di comparison table
        // Hanya tersedia di accordion "Data Lengkap"
        
        Log::info('GtkKemenagSyncService: Data comparison completed', [
            'gtk_id' => $gtk->id,
            'applicable_differences_count' => count($differences),
            'note' => 'Info-only fields (agama, pendidikan, pangkat, gol_ruang) tidak dihitung sebagai perbedaan'
        ]);

        return $differences;
    }

    /**
     * Get alamat lengkap lokal dengan hierarki wilayah
     */
    private function getFullAlamatLokal(Gtk $gtk)
    {
        $parts = [];
        
        if (!empty($gtk->alamat)) {
            $parts[] = $gtk->alamat;
        }
        
        if (!empty($gtk->rt)) {
            $parts[] = "RT {$gtk->rt}";
        }
        
        if (!empty($gtk->rw)) {
            $parts[] = "RW {$gtk->rw}";
        }
        
        // Get nama wilayah dari relasi
        if ($gtk->kelurahan) {
            $parts[] = $gtk->kelurahan->name;
        }
        
        if ($gtk->kecamatan) {
            $parts[] = $gtk->kecamatan->name;
        }
        
        if ($gtk->kabupaten) {
            $parts[] = $gtk->kabupaten->name;
        }
        
        if ($gtk->provinsi) {
            $parts[] = $gtk->provinsi->name;
        }
        
        if (!empty($gtk->kodepos)) {
            $parts[] = $gtk->kodepos;
        }
        
        return implode(', ', array_filter($parts)) ?: '-';
    }

    /**
     * Get alamat lengkap dari data Kemenag
     */
    private function getFullAlamatKemenag(array $kemenagData)
    {
        $parts = [];
        
        if (!empty($kemenagData['alamat_1'])) {
            $parts[] = $kemenagData['alamat_1'];
        }
        
        if (!empty($kemenagData['alamat_2'])) {
            $parts[] = $kemenagData['alamat_2'];
        }
        
        if (!empty($kemenagData['kab_kota'])) {
            $parts[] = $kemenagData['kab_kota'];
        }
        
        if (!empty($kemenagData['provinsi'])) {
            $parts[] = $kemenagData['provinsi'];
        }
        
        if (!empty($kemenagData['kode_pos'])) {
            $parts[] = $kemenagData['kode_pos'];
        }
        
        return implode(', ', array_filter($parts)) ?: '-';
    }

    /**
     * Check apakah alamat sama (case-insensitive, ignore spacing)
     */
    private function isAlamatSame($alamat1, $alamat2)
    {
        if (empty($alamat1) && empty($alamat2)) {
            return true;
        }
        
        if (empty($alamat1) || empty($alamat2)) {
            return false;
        }
        
        // Normalize untuk perbandingan
        $normalized1 = strtolower(preg_replace('/\s+/', ' ', trim($alamat1)));
        $normalized2 = strtolower(preg_replace('/\s+/', ' ', trim($alamat2)));
        
        // Jika sama persis
        if ($normalized1 === $normalized2) {
            return true;
        }
        
        // Jika similarity > 85%
        similar_text($normalized1, $normalized2, $percent);
        return $percent > 85;
    }

    /**
     * Normalize value untuk perbandingan
     */
    private function normalizeValue($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Convert date to standard format
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        // Trim string
        if (is_string($value)) {
            return trim(strtolower($value));
        }

        return $value;
    }

    /**
     * Get field label untuk display
     */
    private function getFieldLabel($field)
    {
        $labels = [
            'nama_lengkap' => 'Nama Lengkap',
            'nip' => 'NIP (18 digit)',
            'nik' => 'NIK',
            'nuptk' => 'NUPTK',
            'jenis_kelamin' => 'Jenis Kelamin',
            // 'tempat_lahir' tidak ada karena tidak di-compare
            'tanggal_lahir' => 'Tanggal Lahir',
            'email' => 'Email',
            'nomor_hp' => 'Nomor HP',
            'status_kepegawaian' => 'Status Kepegawaian',
            'jabatan' => 'Jabatan',
            'tmt_kerja' => 'TMT Kerja',
            'alamat_lengkap' => 'Alamat Lengkap',
        ];

        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Save hasil sync ke database
     */
    private function saveSyncResult(
        Gtk $gtk,
        $rawResponse,
        $status,
        $message,
        $userId,
        array $parsedData = [],
        array $differences = []
    ) {
        DB::beginTransaction();
        
        try {
            $syncData = [
                'gtk_id' => $gtk->id,
                'raw_response' => $rawResponse,
                'sync_status' => $status,
                'sync_message' => $message,
                'synced_at' => now(),
                'synced_by' => $userId,
                'has_differences' => count($differences) > 0,
                'differences' => $differences,
            ];

            // Merge dengan parsed data jika ada
            if (!empty($parsedData)) {
                $syncData = array_merge($syncData, $parsedData);
            }

            // Update or Create sync record
            $sync = GtkKemenagSync::updateOrCreate(
                ['gtk_id' => $gtk->id],
                $syncData
            );

            DB::commit();

            // Hitung applicable differences (exclude info-only fields)
            $applicableDifferencesCount = 0;
            foreach ($differences as $field => $diff) {
                if (!isset($diff['is_info_only']) || !$diff['is_info_only']) {
                    $applicableDifferencesCount++;
                }
            }

            Log::info('GtkKemenagSyncService: Sync result saved', [
                'sync_id' => $sync->id,
                'gtk_id' => $gtk->id,
                'status' => $status,
                'total_differences' => count($differences),
                'applicable_differences' => $applicableDifferencesCount
            ]);

            return [
                'success' => $status === 'success',
                'message' => $message,
                'data' => $sync,
                'has_differences' => count($differences) > 0,
                'differences_count' => count($differences),
                'applicable_differences_count' => $applicableDifferencesCount,
                'differences' => $differences
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('GtkKemenagSyncService: Failed to save sync result', [
                'gtk_id' => $gtk->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Find wilayah (kelurahan) berdasarkan kode pos dan nama kelurahan dari Kemenag
     * 
     * Priority matching:
     * 1. Kode Pos + Nama Kelurahan (ALAMAT_2)
     * 2. Kode Pos + Nama Kabupaten
     * 3. Kode Pos saja (ambil pertama)
     *
     * @param string $kodePos
     * @param string|null $namaKelurahan - ALAMAT_2 dari Kemenag
     * @param string|null $namaKabupaten - KAB_KOTA dari Kemenag
     * @param string|null $namaProvinsi - PROVINSI dari Kemenag
     * @return object|null
     */
    private function findWilayahByKodePos($kodePos, $namaKelurahan = null, $namaKabupaten = null, $namaProvinsi = null)
    {
        // Query dasar: cari semua kelurahan dengan kode pos yang sama
        $villages = collect();
        
        if (!empty($kodePos)) {
            $villages = DB::table('indonesia_villages')
                ->whereRaw("JSON_EXTRACT(meta, '$.pos') = ?", [$kodePos])
                ->get();

            if ($villages->isNotEmpty()) {
                Log::info('FindWilayah: Villages found by postal code', [
                    'kode_pos' => $kodePos,
                    'count' => $villages->count()
                ]);

                // Jika hanya 1 kelurahan, langsung return
                if ($villages->count() === 1) {
                    Log::info('FindWilayah: Single match by postal code', [
                        'village' => $villages->first()->name
                    ]);
                    return $villages->first();
                }
            } else {
                Log::warning('FindWilayah: No village found for postal code', [
                    'kode_pos' => $kodePos
                ]);
            }
        }

        // ============================================
        // FALLBACK: Jika kode pos tidak ditemukan atau kosong
        // Coba matching dengan nama kelurahan + kabupaten
        // ============================================
        if ($villages->isEmpty() && !empty($namaKelurahan)) {
            Log::info('FindWilayah: Fallback - searching by kelurahan name', [
                'kelurahan' => $namaKelurahan,
                'kabupaten' => $namaKabupaten
            ]);

            // Cari berdasarkan nama kelurahan (case-insensitive, partial match)
            $villages = DB::table('indonesia_villages')
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($namaKelurahan) . '%'])
                ->get();

            Log::info('FindWilayah: Fallback - villages found by name', [
                'count' => $villages->count()
            ]);

            if ($villages->isEmpty()) {
                Log::warning('FindWilayah: Fallback - no village found by name', [
                    'kelurahan' => $namaKelurahan
                ]);
                return null;
            }

            // Jika hanya 1 match, langsung return
            if ($villages->count() === 1) {
                Log::info('FindWilayah: Fallback - single match by name', [
                    'village' => $villages->first()->name
                ]);
                return $villages->first();
            }
        }

        // Jika tidak ada villages ditemukan sama sekali
        if ($villages->isEmpty()) {
            return null;
        }

        // PRIORITY 1: Match dengan nama kelurahan (ALAMAT_2)
        if (!empty($namaKelurahan)) {
            foreach ($villages as $village) {
                if ($this->isSimilarName($village->name, $namaKelurahan, 0.7)) {
                    Log::info('FindWilayah: Match by kelurahan name', [
                        'db_name' => $village->name,
                        'kemenag_name' => $namaKelurahan,
                        'method' => 'ALAMAT_2'
                    ]);
                    return $village;
                }
            }
        }

        // PRIORITY 2: Match dengan kabupaten
        if (!empty($namaKabupaten)) {
            foreach ($villages as $village) {
                // Get kabupaten untuk village ini
                $kecamatan = DB::table('indonesia_districts')
                    ->where('code', $village->district_code)
                    ->first();
                
                if ($kecamatan) {
                    $kabupaten = DB::table('indonesia_cities')
                        ->where('code', substr($kecamatan->code, 0, 4))
                        ->first();
                    
                    if ($kabupaten && $this->isSimilarName($kabupaten->name, $namaKabupaten, 0.7)) {
                        Log::info('FindWilayah: Match by kabupaten name', [
                            'village' => $village->name,
                            'db_kabupaten' => $kabupaten->name,
                            'kemenag_kabupaten' => $namaKabupaten
                        ]);
                        return $village;
                    }
                }
            }
        }

        // PRIORITY 3: Fallback - return yang pertama
        Log::warning('FindWilayah: No specific match, using first village', [
            'village' => $villages->first()->name,
            'kode_pos' => $kodePos
        ]);
        return $villages->first();
    }

    /**
     * Compare similarity antara dua nama wilayah
     * Menghilangkan prefix umum dan case-insensitive
     *
     * @param string $name1
     * @param string $name2
     * @param float $threshold (0-1)
     * @return bool
     */
    private function isSimilarName($name1, $name2, $threshold = 0.8)
    {
        if (empty($name1) || empty($name2)) {
            return false;
        }

        // Normalize: lowercase, trim
        $name1 = strtolower(trim($name1));
        $name2 = strtolower(trim($name2));

        // Remove common prefixes/words
        $removeWords = ['kabupaten', 'kota', 'provinsi', 'kecamatan', 'kelurahan', 'desa'];
        foreach ($removeWords as $word) {
            $name1 = trim(str_replace($word, '', $name1));
            $name2 = trim(str_replace($word, '', $name2));
        }

        // Exact match
        if ($name1 === $name2) {
            return true;
        }

        // Contains match
        if (strpos($name1, $name2) !== false || strpos($name2, $name1) !== false) {
            return true;
        }

        // Similarity percentage
        similar_text($name1, $name2, $percent);
        return $percent >= ($threshold * 100);
    }

    /**
     * Extract RT dan RW dari string alamat
     *
     * @param string|null $alamat
     * @return array ['rt' => string|null, 'rw' => string|null]
     */
    private function extractRtRw($alamat)
    {
        if (empty($alamat)) {
            return ['rt' => null, 'rw' => null];
        }

        $rt = null;
        $rw = null;

        // Pattern untuk RT: RT 02, RT.02, RT02, RT 2
        if (preg_match('/RT[\s\.]?0*(\d+)/i', $alamat, $matches)) {
            $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT); // Pad to 3 digits
        }

        // Pattern untuk RW: RW 05, RW.05, RW05, RW 5
        if (preg_match('/RW[\s\.]?0*(\d+)/i', $alamat, $matches)) {
            $rw = str_pad($matches[1], 3, '0', STR_PAD_LEFT); // Pad to 3 digits
        }

        return ['rt' => $rt, 'rw' => $rw];
    }

    /**
     * Apply data Kemenag ke data lokal GTK
     *
     * @param GtkKemenagSync $sync
     * @param string $userId
     * @param array|null $selectedFields - Field yang dipilih untuk di-apply (null = semua)
     * @return array
     */
    public function applyKemenagDataToLocal(GtkKemenagSync $sync, $userId, $selectedFields = null)
    {
        DB::beginTransaction();
        
        try {
            $gtk = $sync->gtk;

            if (!$gtk) {
                throw new Exception('Data GTK tidak ditemukan');
            }

            Log::info('GtkKemenagSyncService: Applying Kemenag data to local', [
                'sync_id' => $sync->id,
                'gtk_id' => $gtk->id,
                'selected_fields' => $selectedFields
            ]);

            // Prepare data untuk update
            $updateData = [];

            // Mapping: Kemenag Sync Field => Local GTK Field
            // Format: 'field_di_gtk_kemenag_sync_table' => 'field_di_gtks_table'
            $fieldMapping = [
                'nama_lengkap' => 'nama_lengkap',
                'nip_baru' => 'nip', // NIP BARU (18 digit) dari Kemenag → NIP lokal
                // 'nik' tidak di-map karena tidak ada di response API Kemenag
                // 'nuptk' tidak di-map karena tidak ada di response API Kemenag
                'jenis_kelamin' => 'jenis_kelamin',
                // 'tempat_lahir' TIDAK di-apply karena data lokal (dari KK/Akte Kelahiran) lebih akurat
                // Data Kemenag hanya kabupaten, sedangkan lokal adalah kelurahan/desa yang spesifik
                'tanggal_lahir' => 'tanggal_lahir',
                'email' => 'email',
                'no_hp' => 'nomor_hp',        // sync: no_hp → local: nomor_hp
                'status_pegawai' => 'status_kepegawaian',  // sync: status_pegawai → local: status_kepegawaian
                'tampil_jabatan' => 'jabatan', // sync: tampil_jabatan → local: jabatan
                'tmt_pangkat' => 'tmt_kerja', // sync: tmt_pangkat → local: tmt_kerja (TMT Pangkat dari Kemenag)
            ];

            // Jika selected fields kosong, apply semua field lokal
            $fieldsToApply = $selectedFields ?: array_values($fieldMapping);

            foreach ($fieldMapping as $syncField => $localField) {
                // Skip jika field lokal tidak dipilih untuk di-apply
                if (!in_array($localField, $fieldsToApply)) {
                    continue;
                }

                // Get value dari sync data (menggunakan field name dari sync table)
                $value = $sync->$syncField;

                // Only update if value is not null
                if (!is_null($value) && $value !== '') {
                    $updateData[$localField] = $value;
                }
            }

            // ============================================
            // SYNC ALAMAT dengan matching kode pos + kelurahan
            // ============================================
            if (!empty($sync->kode_pos)) {
                Log::info('GtkKemenagSyncService: Attempting address sync', [
                    'kode_pos' => $sync->kode_pos,
                    'alamat_2' => $sync->alamat_2, // Nama kelurahan
                    'kab_kota' => $sync->kab_kota,
                    'provinsi' => $sync->provinsi
                ]);

                // Cari wilayah berdasarkan kode pos + nama kelurahan (ALAMAT_2)
                $wilayah = $this->findWilayahByKodePos(
                    $sync->kode_pos,
                    $sync->alamat_2,      // ✅ ALAMAT_2 = Nama Kelurahan
                    $sync->kab_kota,
                    $sync->provinsi
                );

                if ($wilayah) {
                    // Extract RT/RW dari ALAMAT_1
                    $rtRw = $this->extractRtRw($sync->alamat_1);

                    // Set alamat fields
                    $updateData['alamat'] = $sync->alamat_1; // Jalan + RT/RW
                    $updateData['rt'] = $rtRw['rt'];
                    $updateData['rw'] = $rtRw['rw'];
                    $updateData['kelurahan_id'] = $wilayah->code; // 10 digit
                    $updateData['kecamatan_id'] = $wilayah->district_code; // 6 digit
                    $updateData['kabupaten_id'] = substr($wilayah->district_code, 0, 4); // 4 digit
                    $updateData['provinsi_id'] = substr($wilayah->district_code, 0, 2); // 2 digit
                    $updateData['kodepos'] = $sync->kode_pos;

                    Log::info('GtkKemenagSyncService: Address matched successfully', [
                        'kelurahan' => $wilayah->name,
                        'kelurahan_id' => $wilayah->code,
                        'rt' => $rtRw['rt'],
                        'rw' => $rtRw['rw']
                    ]);
                } else {
                    // Kode pos tidak ditemukan, hanya simpan text alamat
                    $combinedAddress = trim(($sync->alamat_1 ?? '') . ', ' . ($sync->alamat_2 ?? ''));
                    $updateData['alamat'] = $combinedAddress;
                    $updateData['kodepos'] = $sync->kode_pos;

                    Log::warning('GtkKemenagSyncService: Address not matched, saving as text only', [
                        'kode_pos' => $sync->kode_pos,
                        'alamat_2' => $sync->alamat_2,
                        'kab_kota' => $sync->kab_kota,
                        'combined_address' => $combinedAddress
                    ]);
                }
            }

            // Update GTK data
            if (!empty($updateData)) {
                $gtk->update($updateData);
                
                // Hitung logical fields untuk display
                // Field alamat dihitung sebagai 1 logical field, meskipun breakdown jadi banyak field
                $logicalFields = [];
                $addressFields = ['alamat', 'rt', 'rw', 'kelurahan_id', 'kecamatan_id', 'kabupaten_id', 'provinsi_id', 'kodepos'];
                $hasAddressUpdate = false;
                
                foreach (array_keys($updateData) as $field) {
                    if (in_array($field, $addressFields)) {
                        $hasAddressUpdate = true;
                    } else {
                        $logicalFields[] = $field;
                    }
                }
                
                if ($hasAddressUpdate) {
                    $logicalFields[] = 'alamat_lengkap';
                }
                
                Log::info('GtkKemenagSyncService: GTK data updated', [
                    'gtk_id' => $gtk->id,
                    'physical_fields' => count($updateData),
                    'logical_fields' => count($logicalFields),
                    'address_synced' => $hasAddressUpdate
                ]);
            }

            // Re-check data completion status setelah update
            $gtk->refresh(); // Reload data dari database
            
            // Check if data diri is complete (sama seperti logic di GtkController)
            $dataDiriLengkap = !empty($gtk->nik) && 
                              !empty($gtk->nama_lengkap) && 
                              !empty($gtk->jenis_kelamin) && 
                              !empty($gtk->tempat_lahir) && 
                              !empty($gtk->tanggal_lahir);
            
            // Check if data kepegawaian is complete
            $dataKepegLengkap = !empty($gtk->status_kepegawaian) && 
                               !empty($gtk->jabatan) && 
                               !empty($gtk->tmt_kerja);
            
            // Update completion flags jika ada perubahan
            $gtk->update([
                'data_diri_completed' => $dataDiriLengkap,
                'data_kepegawaian_completed' => $dataKepegLengkap
            ]);
            
            Log::info('GtkKemenagSyncService: Completion status updated', [
                'gtk_id' => $gtk->id,
                'data_diri_completed' => $dataDiriLengkap,
                'data_kepegawaian_completed' => $dataKepegLengkap
            ]);

            // Update sync record
            $sync->update([
                'last_applied_at' => now(),
                'applied_by' => $userId,
                'has_differences' => false,
                'differences' => []
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data Kemenag berhasil diterapkan ke data lokal',
                'updated_fields' => $logicalFields ?? [],
                'updated_count' => isset($logicalFields) ? count($logicalFields) : 0,
                'physical_fields_count' => count($updateData), // Untuk debugging
                'data_diri_completed' => $dataDiriLengkap,
                'data_kepegawaian_completed' => $dataKepegLengkap
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('GtkKemenagSyncService: Failed to apply data', [
                'sync_id' => $sync->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menerapkan data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get status sync untuk GTK
     *
     * @param Gtk $gtk
     * @return array
     */
    public function getSyncStatus(Gtk $gtk)
    {
        $sync = $gtk->kemenagSync;

        if (!$sync) {
            return [
                'has_sync' => false,
                'message' => 'Belum pernah disinkronisasi',
                'can_sync' => !empty($gtk->nip)
            ];
        }

        return [
            'has_sync' => true,
            'last_synced_at' => $sync->synced_at,
            'synced_by' => $sync->syncedBy->name ?? 'Unknown',
            'sync_status' => $sync->sync_status,
            'has_differences' => $sync->has_differences,
            'differences_count' => $sync->differences_count,
            'last_applied_at' => $sync->last_applied_at,
            'is_fresh' => $sync->isFresh(),
            'can_sync' => !empty($gtk->nip)
        ];
    }
}
