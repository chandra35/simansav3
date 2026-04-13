<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmartqKelulusanTemplateExport implements WithMultipleSheets
{
    protected $pesertas;
    protected $mapelPilihan;

    public function __construct($pesertas, $mapelPilihan)
    {
        $this->pesertas = $pesertas;
        $this->mapelPilihan = $mapelPilihan;
    }

    public function sheets(): array
    {
        return [
            new SmartqKelulusanDataSheet($this->pesertas, $this->mapelPilihan),
            new SmartqKelulusanDaftarMapelSheet($this->mapelPilihan),
        ];
    }
}

class SmartqKelulusanDataSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $pesertas;
    protected $mapelPilihan;

    public function __construct($pesertas, $mapelPilihan)
    {
        $this->pesertas = $pesertas;
        $this->mapelPilihan = $mapelPilihan;
    }

    public function title(): string
    {
        return 'Data Kelulusan';
    }

    public function headings(): array
    {
        return ['NAMA', 'NISN', 'PERINGKAT MAPEL', 'PERINGKAT UMUM', 'MAPEL', 'STATUS'];
    }

    public function array(): array
    {
        return $this->pesertas->map(fn($p) => [
            $p->siswa?->nama_lengkap ?? '-',
            $p->siswa?->nisn ?? '-',
            '', // admin fills
            '', // admin fills
            '', // admin fills
            '', // admin fills
        ])->toArray();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 16,
            'C' => 18,
            'D' => 18,
            'E' => 28,
            'F' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->pesertas->count() + 1;

        // Header styling
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3a5f']],
            ],
        ];

        // Light yellow background for editable columns (C-F)
        if ($lastRow > 1) {
            $sheet->getStyle("C2:F{$lastRow}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFDE7']],
            ]);
        }

        // Add data validation (dropdown) for MAPEL column
        $mapelNames = $this->mapelPilihan->pluck('nama_mapel')->implode(',');
        for ($row = 2; $row <= $lastRow; $row++) {
            $validation = $sheet->getCell("E{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . $mapelNames . '"');
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Mapel tidak valid');
            $validation->setError('Pilih mapel dari daftar yang tersedia.');
        }

        // Add data validation (dropdown) for STATUS column
        for ($row = 2; $row <= $lastRow; $row++) {
            $validation = $sheet->getCell("F{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"diterima,cadangan"');
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Status tidak valid');
            $validation->setError('Gunakan: diterima atau cadangan');
        }

        return $styles;
    }
}

class SmartqKelulusanDaftarMapelSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $mapelPilihan;

    public function __construct($mapelPilihan)
    {
        $this->mapelPilihan = $mapelPilihan;
    }

    public function title(): string
    {
        return 'Daftar Mapel Pilihan';
    }

    public function headings(): array
    {
        return ['Nama Mapel', 'Kode'];
    }

    public function array(): array
    {
        return $this->mapelPilihan->map(fn($m) => [$m->nama_mapel, $m->kode_mapel])->toArray();
    }

    public function columnWidths(): array
    {
        return ['A' => 35, 'B' => 15];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3a5f']],
            ],
        ];
    }
}
