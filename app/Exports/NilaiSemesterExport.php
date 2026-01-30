<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class NilaiSemesterExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    protected $data;
    protected $mapelCodes;
    protected $mapelStats;
    protected $semester;
    protected $tahunNama;
    protected $totalSiswa;

    public function __construct($data, $mapelCodes, $mapelStats, $semester, $tahunNama)
    {
        $this->data = $data;
        $this->mapelCodes = $mapelCodes;
        $this->mapelStats = $mapelStats;
        $this->semester = $semester;
        $this->tahunNama = $tahunNama;
        $this->totalSiswa = count($data);
    }

    public function array(): array
    {
        $rows = [];
        
        // Info rows
        $rows[] = ['Export Nilai Semester ' . $this->semester];
        $rows[] = ['Tahun Pelajaran: ' . $this->tahunNama];
        $rows[] = ['Jumlah Mapel: ' . count($this->mapelCodes)];
        $rows[] = ['Jumlah Siswa: ' . $this->totalSiswa];
        $rows[] = []; // Empty row
        
        // Header row
        $headers = ['NISN', 'Nama'];
        foreach ($this->mapelCodes as $kode) {
            $headers[] = $kode;
        }
        $rows[] = $headers;
        
        // Data rows
        foreach ($this->data as $row) {
            $dataRow = [
                $row['nisn'],
                $row['nama'],
            ];
            foreach ($this->mapelCodes as $kode) {
                $dataRow[] = $row[$kode] ?? '';
            }
            $rows[] = $dataRow;
        }
        
        // Empty row
        $rows[] = [];
        
        // Summary row
        $summaryRow = ['Jumlah Nilai:', ''];
        foreach ($this->mapelCodes as $kode) {
            $summaryRow[] = $this->mapelStats[$kode] ?? 0;
        }
        $rows[] = $summaryRow;
        
        return $rows;
    }

    public function title(): string
    {
        return "Semester {$this->semester}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalCols = count($this->mapelCodes) + 2;
                $lastCol = Coordinate::stringFromColumnIndex($totalCols);
                
                // Style info rows (1-4)
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2:A4')->getFont()->setItalic(true);
                
                // Header row is row 6
                $headerRow = 6;
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2EFDA']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ]
                    ]
                ]);
                
                // Data borders (from header to last data row)
                $dataLastRow = $headerRow + $this->totalSiswa;
                if ($this->totalSiswa > 0) {
                    $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                
                // Summary row style (after empty row)
                $summaryRowNum = $dataLastRow + 2;
                $sheet->getStyle("A{$summaryRowNum}:{$lastCol}{$summaryRowNum}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC']
                    ]
                ]);
            }
        ];
    }
}
