<?php

namespace App\Services;

use App\Models\CustomMenu;
use App\Models\CustomMenuSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomMenuImportService
{
    protected $menu;

    public function __construct(CustomMenu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Import siswa from Excel file
     */
    public function import($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Get header row
            $headers = array_shift($rows);
            
            // Validate required columns
            if (!in_array('NISN', $headers)) {
                throw new \Exception('Kolom NISN tidak ditemukan di file Excel');
            }

            $nisnIndex = array_search('NISN', $headers);
            $namaIndex = array_search('Nama Lengkap', $headers) ?: array_search('Nama', $headers);
            
            // Get custom field indexes using the model method
            $customFieldIndexes = [];
            $customFields = $this->menu->getCustomFieldsArray();
            
            if ($this->menu->content_type === 'personal' && !empty($customFields)) {
                foreach ($customFields as $key => $field) {
                    $columnName = is_array($field) && isset($field['label']) ? $field['label'] : $key;
                    $index = array_search($columnName, $headers);
                    if ($index !== false) {
                        $customFieldIndexes[$key] = $index;
                    }
                }
            }

            $result = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'duplicate' => 0,
                'errors' => [],
            ];

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $result['total']++;
                
                // Skip empty rows
                if (empty($row[$nisnIndex])) {
                    continue;
                }

                $nisn = trim($row[$nisnIndex]);
                
                // Find siswa by NISN
                $siswa = Siswa::where('nisn', $nisn)->first();

                if (!$siswa) {
                    $result['failed']++;
                    $result['errors'][] = [
                        'row' => $rowIndex + 2, // +2 karena header dan index mulai 0
                        'nisn' => $nisn,
                        'error' => 'NISN tidak ditemukan'
                    ];
                    continue;
                }

                // Check if already assigned
                $existing = CustomMenuSiswa::where('custom_menu_id', $this->menu->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

                if ($existing) {
                    $result['duplicate']++;
                    continue;
                }

                // Collect personal data if content_type is personal
                $personalData = null;
                if ($this->menu->content_type === 'personal' && !empty($customFieldIndexes)) {
                    $personalData = [];
                    $customFields = $this->menu->getCustomFieldsArray();
                    
                    foreach ($customFieldIndexes as $fieldKey => $columnIndex) {
                        $value = $row[$columnIndex] ?? null;
                        
                        // Encrypt password fields
                        $fieldConfig = $customFields[$fieldKey] ?? [];
                        if (is_array($fieldConfig) && isset($fieldConfig['type']) && $fieldConfig['type'] === 'password' && !empty($value)) {
                            $value = encrypt($value);
                        }
                        
                        $personalData[$fieldKey] = $value;
                    }
                }

                // Create assignment
                CustomMenuSiswa::create([
                    'custom_menu_id' => $this->menu->id,
                    'siswa_id' => $siswa->id,
                    'personal_data' => $personalData,
                    'is_read' => false,
                ]);

                $result['success']++;
            }

            DB::commit();

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import error', [
                'menu_id' => $this->menu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate and download Excel template
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers based on content type
            $headers = ['NISN', 'Nama Lengkap'];

            // Get custom fields using the model method
            $customFields = $this->menu->getCustomFieldsArray();

            if ($this->menu->content_type === 'personal' && !empty($customFields)) {
                foreach ($customFields as $fieldKey => $field) {
                    $label = is_array($field) && isset($field['label']) ? $field['label'] : $fieldKey;
                    $headers[] = $label;
                }
            }

            $headers[] = 'Keterangan';

            // Write headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }

            // Add sample data
            $sampleData = ['1234567890', 'Contoh Nama Siswa'];
            
            if ($this->menu->content_type === 'personal' && !empty($customFields)) {
                foreach ($customFields as $fieldKey => $field) {
                    $type = is_array($field) && isset($field['type']) ? $field['type'] : 'text';
                    $label = is_array($field) && isset($field['label']) ? $field['label'] : $fieldKey;
                    
                    switch ($type) {
                        case 'password':
                            $sampleData[] = 'Password123';
                            break;
                        case 'email':
                            $sampleData[] = 'email@example.com';
                            break;
                        default:
                            $sampleData[] = 'Contoh ' . $label;
                    }
                }
            }
            
            $sampleData[] = 'Keterangan tambahan (opsional)';

            $col = 'A';
            foreach ($sampleData as $value) {
                $sheet->setCellValue($col . '2', $value);
                $col++;
            }

            // Create writer and download
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Template_' . str_replace(' ', '_', $this->menu->judul) . '_' . date('Ymd') . '.xlsx';
            
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error generating Excel template', [
                'menu_id' => $this->menu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
