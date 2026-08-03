<?php

namespace App\Exports;

use App\Models\Polling;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PollingReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private Polling $polling, private Collection $rows) {}

    public function headings(): array
    {
        return array_merge(
            ['No', 'Jenis Responden', 'Nama', 'Username/NISN', 'Tingkat', 'Rombel', 'Status', 'Waktu Mengisi'],
            $this->polling->questions->pluck('prompt')->all()
        );
    }

    public function array(): array
    {
        return $this->rows->values()->map(function ($row, $index) {
            return array_merge([
                $index + 1,
                strtoupper($row['type']),
                $row['name'],
                $row['username'],
                $row['grade'] ?: '-',
                $row['class_name'] ?: '-',
                $row['answered'] ? 'Sudah Mengisi' : 'Belum Mengisi',
                $row['submitted_at']?->format('d/m/Y H:i') ?: '-',
            ], collect($this->polling->questions)->map(
                fn ($question) => $row['answers'][$question->id] ?: '-'
            )->all());
        })->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
            ],
        ];
    }
}
