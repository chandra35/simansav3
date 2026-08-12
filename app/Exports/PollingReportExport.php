<?php

namespace App\Exports;

use App\Models\Polling;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PollingReportExport extends DefaultValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents, WithCustomValueBinder
{
    private const TEXT_COLUMNS = ['D', 'E', 'F'];

    public function __construct(
        private Polling $polling,
        private Collection $rows,
        private array $exportMetadata = []
    ) {}

    public function headings(): array
    {
        return array_merge(
            ['No', 'Jenis Responden', 'Nama', 'Username/NISN', 'No. HP Siswa', 'Email Siswa', 'Tingkat', 'Rombel', 'Status', 'Waktu Mengisi'],
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
                ($row['student_phone'] ?? null) ?: '-',
                ($row['student_email'] ?? null) ?: '-',
                $row['grade'] ?: '-',
                $row['class_name'] ?: '-',
                $row['answered'] ? 'Sudah Mengisi' : 'Belum Mengisi',
                $row['submitted_at']?->format('d/m/Y H:i') ?: '-',
            ], collect($this->polling->questions)->map(
                fn ($question) => $row['answers'][$question->id] ?? '-'
            )->all());
        })->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $lastDataRow = max(1, $this->rows->count() + 1);
                $noticeStartRow = $lastDataRow + 3;
                $noticeEndRow = $noticeStartRow + 4;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastDataRow}");
                $sheet->getRowDimension(1)->setRowHeight(28);
                if ($this->rows->isNotEmpty()) {
                    $sheet->getStyle("D2:F{$lastDataRow}")->getNumberFormat()->setFormatCode('@');
                }

                $notices = [
                    'PERNYATAAN KERAHASIAAN DAN TANGGUNG JAWAB DATA',
                    'Data ini wajib dijaga kerahasiaannya. Setiap penyalahgunaan atau kebocoran data menjadi tanggung jawab pihak penerima/pengguna data.',
                    'Data diekspor langsung dari SIMANSA. Mohon digunakan sebagaimana mestinya dan hanya dengan izin dari pihak sekolah.',
                    'Signature: '.($this->exportMetadata['signature'] ?? 'SIMANSA'),
                    'Diekspor oleh: '.($this->exportMetadata['exported_by'] ?? '-').' • Waktu: '.($this->exportMetadata['exported_at'] ?? '-'),
                ];

                foreach ($notices as $offset => $notice) {
                    $row = $noticeStartRow + $offset;
                    $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
                    $sheet->setCellValue("A{$row}", $notice);
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->getRowDimension($noticeStartRow)->setRowHeight(26);
                $sheet->getRowDimension($noticeStartRow + 1)->setRowHeight(34);
                $sheet->getRowDimension($noticeStartRow + 2)->setRowHeight(34);
                $sheet->getStyle("A{$noticeStartRow}:{$lastColumn}{$noticeEndRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
                    'font' => ['color' => ['rgb' => '7C2D12']],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'F97316']]],
                ]);
                $sheet->getStyle("A{$noticeStartRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A".($noticeStartRow + 3))->getFont()->setBold(true);
            },
        ];
    }
}
