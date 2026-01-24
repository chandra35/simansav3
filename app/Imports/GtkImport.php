<?php

namespace App\Imports;

use App\Models\Gtk;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class GtkImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    protected $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public function collection(Collection $rows)
    {
        $dataRowNumber = 1;
        
        foreach ($rows as $index => $row) {
            try {
                DB::beginTransaction();
                
                // Clean numeric fields
                $row['nik'] = $this->cleanNumericField($row['nik'] ?? '');
                if (!empty($row['nuptk'])) {
                    $row['nuptk'] = $this->cleanNumericField($row['nuptk']);
                }
                if (!empty($row['nip'])) {
                    $row['nip'] = $this->cleanNumericField($row['nip']);
                }
                
                Log::info('GTK Import row data', ['row' => $row->toArray(), 'data_row' => $dataRowNumber]);

                // Validasi data wajib
                $this->validateRequiredFields($row, $dataRowNumber);

                // Check if NIK already exists
                if (Gtk::where('nik', $row['nik'])->exists()) {
                    throw new \Exception("NIK {$row['nik']} sudah terdaftar di sistem");
                }

                // Check if NUPTK already exists (if provided)
                if (!empty($row['nuptk']) && Gtk::where('nuptk', $row['nuptk'])->exists()) {
                    throw new \Exception("NUPTK {$row['nuptk']} sudah terdaftar di sistem");
                }

                // Check if NIP already exists (if provided)
                if (!empty($row['nip']) && Gtk::where('nip', $row['nip'])->exists()) {
                    throw new \Exception("NIP {$row['nip']} sudah terdaftar di sistem");
                }

                // Create User account
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'username' => $row['nik'], // NIK sebagai username
                    'email' => $this->generateEmail($row['nik']),
                    'password' => Hash::make($row['nik']), // Password = NIK
                    'is_active' => true,
                    'is_first_login' => true,
                ]);

                // Assign role GTK (default)
                $user->assignRole('GTK');

                // Normalize jenis kelamin
                $jenisKelamin = $this->normalizeJenisKelamin($row['jenis_kelamin']);

                // Parse tanggal lahir if provided
                $tanggalLahir = null;
                if (!empty($row['tanggal_lahir'])) {
                    $tanggalLahir = $this->parseDate($row['tanggal_lahir']);
                }

                // Create GTK
                $gtk = Gtk::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $row['nama_lengkap'],
                    'nik' => $row['nik'],
                    'nuptk' => !empty($row['nuptk']) ? $row['nuptk'] : null,
                    'nip' => !empty($row['nip']) ? $row['nip'] : null,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => !empty($row['tempat_lahir']) ? $row['tempat_lahir'] : null,
                    'tanggal_lahir' => $tanggalLahir,
                    'data_diri_completed' => false,
                    'data_kepegawaian_completed' => false,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();
                $this->results['success']++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row' => $dataRowNumber,
                    'nik' => $row['nik'] ?? '-',
                    'nama' => $row['nama_lengkap'] ?? '-',
                    'error' => $e->getMessage()
                ];
            }
            
            $dataRowNumber++;
        }
    }

    protected function validateRequiredFields($row, $rowNumber)
    {
        $rowArray = $row->toArray();
        
        $requiredFields = [
            'nama_lengkap' => 'Nama Lengkap',
            'nik' => 'NIK',
            'jenis_kelamin' => 'Jenis Kelamin'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($rowArray[$field])) {
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

        // Validasi NIK (harus 16 digit)
        if (!preg_match('/^\d{16}$/', $row['nik'])) {
            throw new \Exception("NIK harus 16 digit angka");
        }

        // Validasi NUPTK jika diisi (harus 16 digit)
        if (!empty($row['nuptk']) && !preg_match('/^\d{16}$/', $row['nuptk'])) {
            throw new \Exception("NUPTK harus 16 digit angka");
        }

        // Validasi NIP jika diisi (maksimal 18 digit)
        if (!empty($row['nip']) && strlen($row['nip']) > 18) {
            throw new \Exception("NIP maksimal 18 digit");
        }
    }

    protected function normalizeJenisKelamin($value)
    {
        $jk = strtoupper($value);
        if (in_array($jk, ['LAKI-LAKI', 'LAKI', 'L'])) {
            return 'L';
        } elseif (in_array($jk, ['PEREMPUAN', 'P'])) {
            return 'P';
        }
        return $jk;
    }

    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Try different date formats
            if (is_numeric($value)) {
                // Excel date serial number
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            // Try common formats
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $value);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$value}");
        }

        return null;
    }

    protected function generateEmail($nik)
    {
        return strtolower($nik) . '@gtk.simansa.sch.id';
    }

    protected function cleanNumericField($value)
    {
        if (empty($value)) {
            return '';
        }
        
        $value = (string) $value;
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        return $cleaned;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|digits:16',
            'nuptk' => 'nullable|digits:16',
            'nip' => 'nullable|max:18',
            'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan,LAKI-LAKI,PEREMPUAN',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required' => 'Nama Lengkap wajib diisi',
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus 16 digit',
            'nuptk.digits' => 'NUPTK harus 16 digit',
            'nip.max' => 'NIP maksimal 18 digit',
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
