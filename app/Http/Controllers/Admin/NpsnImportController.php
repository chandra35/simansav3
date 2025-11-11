<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\NpsnSiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NpsnImportController extends Controller
{
    /**
     * Show import NPSN form
     */
    public function index()
    {
        return view('admin.siswa.import-npsn');
    }

    /**
     * Download template Excel untuk import NPSN
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_NPSN_Siswa.xlsx';
        $filePath = storage_path('app/templates/' . $fileName);

        // Always create fresh template
        $this->createTemplate($filePath);

        return response()->download($filePath, $fileName);
    }

    /**
     * Process import NPSN
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
            $import = new NpsnSiswaImport();
            Excel::import($import, $file);
            
            // Get results
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => 'Import NPSN selesai',
                'data' => [
                    'success_count' => $results['success'],
                    'failed_count' => $results['failed'],
                    'total' => $results['success'] + $results['failed'],
                    'errors' => $results['errors'],
                    'warnings' => $results['warnings'] ?? []
                ]
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'nisn' => $failure->values()['nisn'] ?? '-',
                    'nama' => $failure->values()['nama'] ?? '-',
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
            Log::error('Import NPSN error', [
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
     * Create template Excel for NPSN import
     */
    protected function createTemplate($filePath)
    {
        $templatePath = dirname($filePath);
        
        if (!file_exists($templatePath)) {
            mkdir($templatePath, 0755, true);
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import NPSN');

        // Headers
        $headers = ['NISN', 'Nama Lengkap', 'NPSN'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Sample data
        $sampleData = [
            ['0123456789', 'Ahmad Fauzi', '10816793'],
            ['0123456790', 'Siti Nurhaliza', '10816793'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto size columns
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);

        // Add notes/instructions
        $sheet->setCellValue('A5', 'CATATAN PENTING:');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FF0000');
        
        $notes = [
            '1. NISN (WAJIB): 10 digit angka, harus sudah ada di database siswa',
            '2. Nama Lengkap (OPSIONAL): Untuk memudahkan identifikasi, tidak akan diupdate',
            '3. NPSN (WAJIB): 8 digit angka, harus ada di database sekolah',
            '4. Hanya NISN dan NPSN yang wajib diisi',
            '5. Data akan diupdate berdasarkan NISN yang ada',
            '6. Pastikan NPSN sekolah sudah ada di database sebelum import',
        ];
        
        $row = 6;
        foreach ($notes as $note) {
            $sheet->setCellValue('A' . $row, $note);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->setSize(9);
            $row++;
        }
        
        // Merge cells for notes
        foreach (range(5, 11) as $r) {
            $sheet->mergeCells('A' . $r . ':C' . $r);
        }

        // Add example section
        $sheet->setCellValue('A13', 'CONTOH DATA:');
        $sheet->getStyle('A13')->getFont()->setBold(true)->setSize(10);
        $sheet->mergeCells('A13:C13');
        
        $sheet->setCellValue('A14', 'NISN: 0123456789');
        $sheet->setCellValue('A15', 'Nama: Ahmad Fauzi (opsional)');
        $sheet->setCellValue('A16', 'NPSN: 10816793 (MTs Negeri 1 Bantul)');
        
        foreach (range(14, 16) as $r) {
            $sheet->mergeCells('A' . $r . ':C' . $r);
            $sheet->getStyle('A' . $r)->getFont()->setSize(9)->setItalic(true);
        }

        // Border for data area
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:C3')->applyFromArray($styleArray);

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
