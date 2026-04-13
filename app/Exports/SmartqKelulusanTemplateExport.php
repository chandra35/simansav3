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
    protected $mapelPilihan;

    public function __construct($mapelPilihan)
    {
        $this->mapelPilihan = $mapelPilihan;
    }

    public function sheets(): array
    {
        return [
            new SmartqKelulusanDataSheet($this->mapelPilihan),
            new SmartqKelulusanKodeBidangSheet($this->mapelPilihan),
        ];
    }
}

class SmartqKelulusanDataSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $mapelPilihan;

    public function __construct($mapelPilihan)
    {
        $this->mapelPilihan = $mapelPilihan;
    }

    public function title(): string
    {
        return 'Data Kelulusan';
    }

    public function headings(): array
    {
        return ['NISN', 'Status (diterima/cadangan)', 'Kode Bidang'];
    }

    public function array(): array
    {
        $first = $this->mapelPilihan->first()?->kode_mapel ?? 'M-QH';
        $last = $this->mapelPilihan->last()?->kode_mapel ?? 'M-PP-F';

        return [
            ['0012345678', 'diterima', $first],
            ['0087654321', 'cadangan', $last],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 30, 'C' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

class SmartqKelulusanKodeBidangSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $mapelPilihan;

    public function __construct($mapelPilihan)
    {
        $this->mapelPilihan = $mapelPilihan;
    }

    public function title(): string
    {
        return 'Daftar Kode Bidang';
    }

    public function headings(): array
    {
        return ['Kode Bidang', 'Nama Mapel Pilihan'];
    }

    public function array(): array
    {
        return $this->mapelPilihan->map(fn($m) => [$m->kode_mapel, $m->nama_mapel])->toArray();
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 40];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
