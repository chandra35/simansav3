<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Sekolah;
use App\Services\KemendikbudApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class NpsnSiswaImport implements ToCollection, WithHeadingRow
{
    protected $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
        'warnings' => []
    ];

    protected $apiService;

    public function __construct()
    {
        $this->apiService = new KemendikbudApiService();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena row 1 adalah header, dan index dimulai dari 0
            
            try {
                // Validasi NISN wajib
                if (empty($row['nisn'])) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'nisn' => '-',
                        'nama' => $row['nama'] ?? '-',
                        'error' => 'NISN wajib diisi'
                    ];
                    continue;
                }

                // Validasi NPSN wajib
                if (empty($row['npsn'])) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'nisn' => $row['nisn'],
                        'nama' => $row['nama'] ?? '-',
                        'error' => 'NPSN wajib diisi'
                    ];
                    continue;
                }

                // Validasi NPSN format (8 digit)
                $npsn = trim($row['npsn']);
                if (!preg_match('/^\d{8}$/', $npsn)) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'nisn' => $row['nisn'],
                        'nama' => $row['nama'] ?? '-',
                        'error' => 'NPSN harus 8 digit angka (saat ini: ' . $npsn . ')'
                    ];
                    continue;
                }

                // Cari siswa berdasarkan NISN
                $siswa = Siswa::where('nisn', trim($row['nisn']))->first();
                
                if (!$siswa) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'nisn' => $row['nisn'],
                        'nama' => $row['nama'] ?? '-',
                        'error' => 'NISN tidak ditemukan di database'
                    ];
                    continue;
                }

                // === INTEGRASI API SERVICE ===
                // Cek/fetch data sekolah (database lokal atau API Kemendikbud)
                $sekolahData = $this->apiService->getSekolah($npsn);
                
                $warningMessage = null;
                $shouldUpdateNpsn = false;
                
                if ($sekolahData['success']) {
                    // Data sekolah ditemukan (dari DB lokal atau API)
                    $sekolah = $sekolahData['data'];
                    $source = $sekolahData['source']; // 'database' atau 'api'
                    
                    Log::info("Import NPSN: Sekolah {$npsn} ditemukan dari {$source}", [
                        'nisn' => $siswa->nisn,
                        'sekolah' => $sekolah->nama
                    ]);
                    
                    // Jika dari API, sudah otomatis tersimpan ke DB oleh service
                    if ($source === 'api') {
                        $warningMessage = 'Data sekolah diambil dari API Kemendikbud dan disimpan ke database';
                    }
                    
                    $shouldUpdateNpsn = true;
                } else {
                    // Data sekolah TIDAK ditemukan (API error atau NPSN tidak valid)
                    // TIDAK bisa update NPSN karena FK constraint!
                    
                    Log::warning("Import NPSN: Sekolah {$npsn} tidak ditemukan", [
                        'nisn' => $siswa->nisn,
                        'message' => $sekolahData['message']
                    ]);
                    
                    $warningMessage = 'NPSN TIDAK DISIMPAN karena data sekolah tidak ditemukan di database lokal maupun API Kemendikbud: ' . 
                                     $sekolahData['message'];
                    
                    $shouldUpdateNpsn = false;
                }

                // Update NPSN siswa HANYA jika sekolah ada di database (karena FK constraint)
                if ($shouldUpdateNpsn) {
                    $siswa->npsn_asal_sekolah = $npsn;
                    $siswa->save();
                    
                    $this->results['success']++;
                    
                    // Log activity
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($siswa)
                        ->withProperties([
                            'nisn' => $siswa->nisn,
                            'nama' => $siswa->nama_lengkap,
                            'npsn_asal' => $npsn,
                            'sekolah_found' => true,
                            'source' => $sekolahData['source']
                        ])
                        ->log('Update NPSN Asal Sekolah via Import');
                } else {
                    // Tidak bisa update karena sekolah tidak ada (FK constraint)
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'nisn' => $siswa->nisn,
                        'nama' => $siswa->nama_lengkap,
                        'error' => 'NPSN ' . $npsn . ' tidak ditemukan di database dan API Kemendikbud'
                    ];
                }
                
                // Jika ada warning, masukkan ke array warnings
                if ($warningMessage && $shouldUpdateNpsn) {
                    $this->results['warnings'][] = [
                        'row' => $rowNumber,
                        'nisn' => $siswa->nisn,
                        'nama' => $siswa->nama_lengkap,
                        'npsn' => $npsn,
                        'message' => $warningMessage
                    ];
                }

            } catch (\Exception $e) {
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row' => $rowNumber,
                    'nisn' => $row['nisn'] ?? '-',
                    'nama' => $row['nama'] ?? '-',
                    'error' => 'Error: ' . $e->getMessage()
                ];
                
                Log::error('Import NPSN error on row ' . $rowNumber, [
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function getResults()
    {
        return $this->results;
    }
}
