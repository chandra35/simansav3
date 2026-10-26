<?php

namespace App\Exports;

use App\Models\TkaPeserta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TkaPesertaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithCustomValueBinder
{
    private const TEXT_COLUMNS = ['C', 'D', 'F', 'G', 'H'];

    protected Collection $rows;
    protected int $counter = 0;
    protected int $year;

    public function __construct(Collection $rows, int $year = 2026)
    {
        $this->rows = $rows;
        $this->year = $year;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Akun TKA ' . $this->year;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Username',
            'Password',
            'Jenis Kelamin',
            'Nama Ruang',
            'Gelombang',
            'Sesi',
        ];
    }

    public function map($p): array
    {
        $this->counter++;

        $namaLengkap = $p->siswa?->nama_lengkap ?: $p->nama_peserta;

        $jk = $p->siswa?->jenis_kelamin;
        if ($jk) {
            $val = strtoupper(trim((string) $jk));
            if ($val === 'L' || str_starts_with($val, 'LAKI')) {
                $jkFormatted = 'Laki-Laki';
            } elseif ($val === 'P' || str_starts_with($val, 'PEREMPUAN')) {
                $jkFormatted = 'Perempuan';
            } else {
                $jkFormatted = $jk;
            }
        } else {
            $jkFormatted = '-';
        }

        return [
            $this->counter,
            $namaLengkap,
            (string) $p->username,
            (string) $p->password,
            $jkFormatted,
            (string) ($p->ruang ?: '-'),
            (string) ($p->gelombang ?: '-'),
            (string) ($p->sesi ?: '-'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        // Header style
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1A6FC4');
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows style
        $lastRow = $this->counter + 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("C2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("F2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Borders
            $sheet->getStyle("A1:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD0D0D0');
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 38,  // Nama Lengkap
            'C' => 20,  // Username
            'D' => 20,  // Password
            'E' => 16,  // Jenis Kelamin
            'F' => 22,  // Nama Ruang
            'G' => 14,  // Gelombang
            'H' => 12,  // Sesi
        ];
    }
}
