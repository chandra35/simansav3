<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GtkImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class GtkImportController extends Controller
{
    /**
     * Show import form
     */
    public function index()
    {
        return view('admin.gtk.import');
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_GTK.xlsx';
        $filePath = storage_path('app/templates/' . $fileName);

        if (!file_exists($filePath)) {
            $this->createTemplate();
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * Process import
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:2048'
        ], [
            'file.required' => 'File Excel wajib dipilih',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
            'file.max' => 'Ukuran file maksimal 2MB'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $file = $request->file('file');
            
            $import = new GtkImport();
            Excel::import($import, $file);
            
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => 'Import selesai',
                'data' => [
                    'success_count' => $results['success'],
                    'failed_count' => $results['failed'],
                    'total' => $results['success'] + $results['failed'],
                    'errors' => $results['errors']
                ]
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'nik' => $failure->values()['nik'] ?? '-',
                    'nama' => $failure->values()['nama_lengkap'] ?? '-',
                    'error' => implode(', ', $failure->errors())
                ];
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => [
                    'success_count' => 0,
                    'failed_count' => count($errors),
                    'total' => count($errors),
                    'errors' => $errors
                ]
            ], 422);

        } catch (\Exception $e) {
            Log::error('GTK Import error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => [
                    'success_count' => 0,
                    'failed_count' => 0,
                    'total' => 0,
                    'errors' => []
                ]
            ], 500);
        }
    }

    /**
     * Create template Excel
     */
    protected function createTemplate()
    {
        $templatePath = storage_path('app/templates');
        
        if (!file_exists($templatePath)) {
            mkdir($templatePath, 0755, true);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers - kolom wajib dan tidak wajib
        $headers = [
            'Nama Lengkap',  // Wajib
            'NIK',           // Wajib
            'Jenis Kelamin', // Wajib
            'NUPTK',         // Tidak wajib
            'NIP',           // Tidak wajib
            'Tempat Lahir',  // Tidak wajib
            'Tanggal Lahir'  // Tidak wajib (format: YYYY-MM-DD, contoh: 1982-05-29)
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Sample data dengan keterangan
        $sampleData = [
            ['Budi Santoso', '1234567890123456', 'L', '1234567890123456', '198001012005011001', 'Jakarta', '1982-05-29'],
            ['Siti Nurhaliza', '9876543210987654', 'P', '9876543210987654', '', 'Bandung', '1990-12-15'],
            ['Ahmad Wijaya', '1111222233334444', 'L', '', '', 'Surabaya', ''],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Add notes
        $sheet->setCellValue('A5', 'KETERANGAN:');
        $sheet->setCellValue('A6', '1. Kolom Nama Lengkap, NIK, dan Jenis Kelamin WAJIB diisi');
        $sheet->setCellValue('A7', '2. NIK harus 16 digit angka');
        $sheet->setCellValue('A8', '3. NUPTK harus 16 digit angka (jika diisi)');
        $sheet->setCellValue('A9', '4. NIP maksimal 18 digit (jika diisi)');
        $sheet->setCellValue('A10', '5. Jenis Kelamin: L/P atau Laki-laki/Perempuan');
        $sheet->setCellValue('A11', '6. Tanggal Lahir format: YYYY-MM-DD (contoh: 1982-05-29)');
        $sheet->setCellValue('A12', '7. Username akan dibuat otomatis dari NIK');
        $sheet->setCellValue('A13', '8. Password default adalah NIK');

        // Bold notes
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A6:A13')->getFont()->setItalic(true);

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save(storage_path('app/templates/Template_Import_GTK.xlsx'));
    }
}
