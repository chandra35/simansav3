<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

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
        return ['NAMA', 'NISN', 'PERINGKAT MAPEL', 'PERINGKAT UMUM', 'MAPEL'];
    }

    public function array(): array
    {
        return $this->pesertas->map(fn($p) => [
            $p->siswa?->nama_lengkap ?? '-',
            $p->siswa?->user?->username ?? '-',
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

        // Lock NAMA + NISN columns (A & B), leave C/D/E editable
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('smartq');

        // Unlock columns C, D, E for editing
        for ($row = 2; $row <= $lastRow; $row++) {
            $sheet->getStyle("C{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
            $sheet->getStyle("D{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
            $sheet->getStyle("E{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        // Light yellow background for editable columns
        $sheet->getStyle("C2:E{$lastRow}")->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFDE7']],
        ]);

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
