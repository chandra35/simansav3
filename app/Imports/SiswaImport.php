<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SiswaImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    protected $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public function collection(Collection $rows)
    {
        $dataRowNumber = 1; // Mulai dari 1 untuk data pertama
        
        foreach ($rows as $index => $row) {
            try {
                DB::beginTransaction();
                
                // Clean NISN & NIK: hapus karakter non-angka (termasuk petik tunggal)
                $row['nisn'] = $this->cleanNumericField($row['nisn'] ?? '');
                $row['nik'] = $this->cleanNumericField($row['nik'] ?? '');
                
                // Debug: Log row data untuk melihat key yang digunakan
                Log::info('Row data', ['row' => $row->toArray(), 'data_row' => $dataRowNumber]);

                // Validasi data wajib
                $this->validateRequiredFields($row, $dataRowNumber);

                // Check if NISN already exists
                if (Siswa::where('nisn', $row['nisn'])->exists()) {
                    throw new \Exception("NISN {$row['nisn']} sudah terdaftar di sistem");
                }

                // Check if NIK already exists
                if (Siswa::where('nik', $row['nik'])->exists()) {
                    throw new \Exception("NIK {$row['nik']} sudah terdaftar di sistem");
                }

                // Create User account
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'username' => $row['nisn'], // NISN sebagai username
                    'email' => $this->generateEmail($row['nisn']),
                    'password' => Hash::make($row['nisn']), // Password = NISN (sama dengan create siswa biasa)
                    'is_first_login' => true,
                ]);

                // Assign role siswa
                $user->assignRole('Siswa');

                // Create Siswa
                $siswa = Siswa::create([
                    'user_id' => $user->id,
                    'nisn' => $row['nisn'],
                    'nik' => $row['nik'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin']),
                    'data_diri_completed' => false,
                    'data_ortu_completed' => false,
                ]);

                // Create Ortu (kosong/NULL dulu, nanti siswa yang melengkapi)
                Ortu::create([
                    'siswa_id' => $siswa->id,
                    // Semua field dibiarkan NULL, siswa yang akan melengkapi sendiri
                ]);

                DB::commit();
                $this->results['success']++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row' => $dataRowNumber,
                    'nisn' => $row['nisn'] ?? '-',
                    'nama' => $row['nama_lengkap'] ?? '-',
                    'error' => $e->getMessage()
                ];
            }
            
            $dataRowNumber++; // Increment untuk data berikutnya
        }
    }

    protected function validateRequiredFields($row, $rowNumber)
    {
        // Convert row to array for easier access
        $rowArray = $row->toArray();
        
        $requiredFields = [
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'jenis_kelamin' => 'Jenis Kelamin'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($rowArray[$field])) {
                // Debug: show available keys if field not found
                if (!isset($rowArray[$field])) {
                    $availableKeys = implode(', ', array_keys($rowArray));
                    throw new \Exception("Kolom {$label} tidak ditemukan. Kolom yang tersedia: {$availableKeys}");
                }
                throw new \Exception("Kolom {$label} wajib diisi");
            }
        }

        // Validasi jenis kelamin
        if (!in_array(strtoupper($row['jenis_kelamin']), ['L', 'P', 'LAKI-LAKI', 'PEREMPUAN'])) {
            throw new \Exception("Jenis Kelamin harus 'L', 'P', 'Laki-laki', atau 'Perempuan'");
        }

        // Normalize jenis kelamin
        $jk = strtoupper($row['jenis_kelamin']);
        if ($jk == 'LAKI-LAKI') {
            $row['jenis_kelamin'] = 'L';
        } elseif ($jk == 'PEREMPUAN') {
            $row['jenis_kelamin'] = 'P';
        }

        // Validasi NISN (harus 10 digit)
        if (!preg_match('/^\d{10}$/', $row['nisn'])) {
            throw new \Exception("NISN harus 10 digit angka");
        }

        // Validasi NIK (harus 16 digit)
        if (!preg_match('/^\d{16}$/', $row['nik'])) {
            throw new \Exception("NIK harus 16 digit angka");
        }
    }

    protected function generateEmail($nisn)
    {
        return strtolower($nisn) . '@siswa.simansa.sch.id';
    }

    /**
     * Clean numeric field - hapus semua karakter non-angka
     * Termasuk petik tunggal (') yang sering muncul di Excel
     */
    protected function cleanNumericField($value)
    {
        if (empty($value)) {
            return '';
        }
        
        // Convert to string jika belum
        $value = (string) $value;
        
        // Hapus semua karakter kecuali angka (0-9)
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        return $cleaned;
    }

    public function rules(): array
    {
        return [
            'nisn' => 'required|digits:10',
            'nik' => 'required|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan,LAKI-LAKI,PEREMPUAN',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nisn.required' => 'NISN wajib diisi',
            'nisn.digits' => 'NISN harus 10 digit',
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus 16 digit',
            'nama_lengkap.required' => 'Nama Lengkap wajib diisi',
            'jenis_kelamin.required' => 'Jenis Kelamin wajib diisi',
            'jenis_kelamin.in' => 'Jenis Kelamin harus L/P atau Laki-laki/Perempuan',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getResults()
    {
        return $this->results;
    }
}
