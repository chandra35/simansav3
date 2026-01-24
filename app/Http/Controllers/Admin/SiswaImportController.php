<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SiswaImportController extends Controller
{
    /**
     * Show import form
     */
    public function index()
    {
        return view('admin.siswa.import');
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_Siswa.xlsx';
        $filePath = storage_path('app/templates/' . $fileName);

        if (!file_exists($filePath)) {
            // Create template if not exists
            $this->createTemplate();
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * Process import
     */
    public function import(Request $request)
    {
        // Validasi file
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
            
            // Import data
            $import = new SiswaImport();
            Excel::import($import, $file);
            
            // Get results
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
                    'nisn' => $failure->values()['nisn'] ?? '-',
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
            // Log error untuk debugging
            Log::error('Import error', [
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

        // Create simple template with headers
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers - urutan sesuai template EMIS
        $headers = ['Nama Lengkap', 'NISN', 'NIK', 'Jenis Kelamin'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Sample data
        $sampleData = [
            ['Ahmad Fauzi', '0123456789', '1234567890123456', 'L'],
            ['Siti Nurhaliza', '0123456790', '1234567890123457', 'P'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add notes
        $sheet->setCellValue('A5', 'CATATAN:');
        $sheet->setCellValue('A6', '- NISN harus 10 digit angka (akan digunakan sebagai username dan password)');
        $sheet->setCellValue('A7', '- NIK harus 16 digit angka');
        $sheet->setCellValue('A8', '- Jenis Kelamin: L (Laki-laki) atau P (Perempuan)');
        $sheet->setCellValue('A9', '- Semua kolom wajib diisi');
        $sheet->setCellValue('A10', '- Password default: sama dengan NISN');
        $sheet->setCellValue('A11', '- Siswa akan melengkapi data orang tua sendiri setelah login');
        $sheet->getStyle('A5:A11')->getFont()->setItalic(true)->setSize(9);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($templatePath . '/Template_Import_Siswa.xlsx');
    }
}
