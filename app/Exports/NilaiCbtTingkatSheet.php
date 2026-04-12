<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class NilaiCbtTingkatSheet implements FromArray, WithTitle, WithEvents, WithColumnWidths
{
    protected $tingkat;
    protected $tktRows;
    protected $tktMapel;
    protected $smartq;
    protected $tktMapelWajib = [];
    protected $mapelCount;
    protected $totalCols;
    protected $dataStartRow;
    protected $totalDataRows = 0;
    protected $kelasRanges = []; // track kelas header rows for styling
    protected $tidakHadirRows = []; // track absent student rows

    public function __construct($tingkat, $tktRows, $tktMapel, $smartq)
    {
        $this->tingkat = $tingkat;
        $this->tktRows = $tktRows;
        $this->tktMapel = $tktMapel;
        $this->smartq = $smartq;
        $this->mapelCount = $tktMapel->count();

        // Detect wajib mapel
        $tktTotal = $tktRows->count();
        foreach ($tktMapel as $m) {
            $attempts = $tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $m['quiz_id'])->where('normalized_100', '>', 0)->count();
            if ($attempts > ($tktTotal * 0.5)) {
                $this->tktMapelWajib[] = $m['quiz_id'];
            }
        }

        // Columns: No, Nama, NISN, Kelas + mapel columns + Rata-rata (if >1 mapel) + Kehadiran
        $this->totalCols = 4 + $this->mapelCount + ($this->mapelCount > 1 ? 1 : 0) + 1;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Nama
            'C' => 14,  // NISN
            'D' => 15,  // Kelas
        ];
    }

    public function array(): array
    {
        $rows = [];
        $tktLabel = $this->tingkat ? 'Tingkat ' . $this->tingkat : 'Lainnya';
        $tktTotal = $this->tktRows->count();
        $tktHadir = $this->tktRows->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
        $tktAvg = $this->tktRows->where('has_attempt', true)->avg('normalized_100');

        // ── Title block ──
        $rows[] = ['Laporan Nilai CBT Moodle — ' . $this->smartq->nama];
        $rows[] = [$tktLabel . ' | ' . $tktTotal . ' siswa | ' . $tktHadir . ' hadir | ' . ($tktTotal - $tktHadir) . ' tidak hadir | Rata²: ' . round($tktAvg ?? 0, 1)];
        $rows[] = ['Discan: ' . ($this->smartq->last_scan_at?->format('d M Y H:i') ?? '-') . ' | ' . $this->smartq->moodle_base_url];
        $rows[] = []; // empty row

        // ── Ringkasan Mapel ──
        $rows[] = ['RINGKASAN MAPEL ' . strtoupper($tktLabel)];
        $ringkasanHeader = ['No', 'Mata Pelajaran', 'Tipe', 'Mengerjakan', 'Rata-rata', 'Tertinggi', 'Terendah', 'Keterangan'];
        $rows[] = $ringkasanHeader;

        $num = 0;
        foreach ($this->tktMapel as $m) {
            $num++;
            $qid = $m['quiz_id'];
            $scores = $this->tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $qid)->where('normalized_100', '>', 0);
            $isWajib = in_array($qid, $this->tktMapelWajib);
            $avg = $scores->count() > 0 ? round($scores->avg('normalized_100'), 1) : 0;
            $max = $scores->count() > 0 ? round($scores->max('normalized_100'), 1) : 0;
            $min = $scores->count() > 0 ? round($scores->min('normalized_100'), 1) : 0;

            $ket = '';
            if ($avg >= 80) $ket = 'Sangat Baik';
            elseif ($avg >= 60) $ket = 'Cukup Baik';
            elseif ($avg >= 40) $ket = 'Perlu Perhatian';
            elseif ($scores->count() > 0) $ket = 'Kritis';
            else $ket = 'Belum ada yang mengerjakan';

            $rows[] = [
                $num,
                $m['quiz_name'],
                $isWajib ? 'Wajib' : 'Pilihan',
                $scores->count() . '/' . $tktTotal,
                $avg,
                $max,
                $min,
                $ket,
            ];
        }

        $rows[] = []; // separator
        $rows[] = []; // separator

        // ── Data header ──
        $dataHeader = ['No', 'Nama Siswa', 'NISN', 'Kelas'];
        foreach ($this->tktMapel as $m) {
            $label = $m['quiz_name'];
            if (!in_array($m['quiz_id'], $this->tktMapelWajib)) $label .= ' *';
            $dataHeader[] = $label;
        }
        if ($this->mapelCount > 1) $dataHeader[] = 'Rata-rata';
        $dataHeader[] = 'Kehadiran';
        $rows[] = $dataHeader;

        $this->dataStartRow = count($rows) + 1; // 1-indexed for Excel

        // ── Student data grouped by kelas ──
        $byKelas = $this->tktRows->groupBy(fn($r) => $r['siswa_kelas'] ?? $r['moodle_lastname'] ?? 'Tanpa Kelas')->sortKeys();
        $globalNum = 0;

        foreach ($byKelas as $kelasName => $kelasRowsRaw) {
            $kelasRows = collect($kelasRowsRaw);
            $kelasHadir = $kelasRows->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
            $kelasAvg = $kelasRows->where('has_attempt', true)->avg('normalized_100');

            // Kelas sub-header
            $kelasHeaderRow = [
                '',
                $kelasName . ' (' . $kelasRows->count() . ' siswa, ' . $kelasHadir . ' hadir' .
                    ($kelasAvg ? ', rata²: ' . round($kelasAvg, 1) : '') . ')',
            ];
            $rows[] = $kelasHeaderRow;
            $this->kelasRanges[] = count($rows); // 1-indexed row number
            $this->totalDataRows++;

            foreach ($kelasRows->sortByDesc('normalized_100') as $row) {
                $globalNum++;
                $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                $isHadir = $row['has_attempt'] ?? false;

                $r = [
                    $globalNum,
                    ($row['siswa_nama'] ?? '') ?: (($row['moodle_firstname'] ?? '') ?: ($row['moodle_fullname'] ?? '-')),
                    "'" . ($row['siswa_nisn'] ?? $row['moodle_username'] ?? '-'), // prefix ' to keep as text
                    $row['siswa_kelas'] ?? ($row['moodle_lastname'] ?? '-'),
                ];

                $scoreValues = [];
                foreach ($this->tktMapel as $m) {
                    $score = $rowScores->get($m['quiz_id']);
                    if ($score) {
                        $r[] = $score['normalized_100'];
                        $scoreValues[] = $score['normalized_100'];
                    } else {
                        $r[] = null; // empty cell
                    }
                }

                if ($this->mapelCount > 1) {
                    $r[] = count($scoreValues) > 0 ? round(array_sum($scoreValues) / count($scoreValues), 1) : null;
                }
                $r[] = $isHadir ? 'HADIR' : 'TIDAK HADIR';

                $rows[] = $r;
                $this->totalDataRows++;

                if (!$isHadir) {
                    $this->tidakHadirRows[] = count($rows);
                }
            }

            // Kelas footer row — averages
            $footerRow = ['', 'Rata-rata ' . $kelasName, '', ''];
            foreach ($this->tktMapel as $m) {
                $kelasMapelScores = $kelasRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $m['quiz_id'])->where('normalized_100', '>', 0);
                $footerRow[] = $kelasMapelScores->count() > 0 ? round($kelasMapelScores->avg('normalized_100'), 1) : '';
            }
            if ($this->mapelCount > 1) {
                $footerRow[] = $kelasAvg ? round($kelasAvg, 1) : '';
            }
            $footerRow[] = $kelasHadir . '/' . $kelasRows->count();
            $rows[] = $footerRow;
            $this->kelasRanges[] = count($rows); // also style footer
            $this->totalDataRows++;
        }

        // ── Legend ──
        $rows[] = [];
        $rows[] = ['Keterangan:'];
        $rows[] = ['', '≥80 = Sangat Baik (hijau)', '', '', '60-79 = Baik (biru)', '', '', '40-59 = Cukup (kuning)', '', '', '<40 = Kurang (merah)'];
        $rows[] = ['', '* = Mapel Pilihan', '', '', 'Kosong = Tidak mengambil / tidak mengerjakan'];

        return $rows;
    }

    public function title(): string
    {
        return $this->tingkat ? 'Tingkat ' . $this->tingkat : 'Lainnya';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->totalCols);

                // ── Title styling ──
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2:A3')->getFont()->setItalic(true)->setSize(10);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");

                // ── Ringkasan header (row 5) ──
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                ]);

                // Ringkasan table header (row 6)
                $sheet->getStyle('A6:H6')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Ringkasan data rows
                $ringkasanEndRow = 6 + $this->mapelCount;
                if ($this->mapelCount > 0) {
                    $sheet->getStyle("A7:H{$ringkasanEndRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("C7:C{$ringkasanEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D7:G{$ringkasanEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Color-code ringkasan averages
                    for ($row = 7; $row <= $ringkasanEndRow; $row++) {
                        $val = $sheet->getCell("E{$row}")->getValue();
                        if (is_numeric($val)) {
                            $color = $val >= 80 ? '70AD47' : ($val >= 60 ? '4472C4' : ($val >= 40 ? 'FFC000' : 'FF4444'));
                            $sheet->getStyle("E{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                            ]);
                        }
                    }
                }

                // ── Data table header ──
                $headerRow = $this->dataStartRow - 1; // the header is one row before dataStartRow
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(30);

                // ── Data area borders ──
                $dataEndRow = $headerRow + $this->totalDataRows;
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataEndRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Center-align score columns (E onwards to last-1)
                $firstMapelCol = 'E';
                $lastMapelCol = Coordinate::stringFromColumnIndex(4 + $this->mapelCount + ($this->mapelCount > 1 ? 1 : 0));
                $lastStatusCol = $lastCol;
                for ($row = $headerRow + 1; $row <= $dataEndRow; $row++) {
                    $sheet->getStyle("{$firstMapelCol}{$row}:{$lastStatusCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ── Color-code each score cell ──
                for ($row = $headerRow + 1; $row <= $dataEndRow; $row++) {
                    // Skip kelas header/footer rows
                    if (in_array($row, $this->kelasRanges)) continue;

                    for ($col = 5; $col <= 4 + $this->mapelCount; $col++) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $val = $sheet->getCell("{$colLetter}{$row}")->getValue();
                        if (is_numeric($val) && $val > 0) {
                            if ($val >= 80) {
                                $bg = 'C6EFCE'; $fg = '006100';
                            } elseif ($val >= 60) {
                                $bg = 'D6E4F0'; $fg = '1F4E79';
                            } elseif ($val >= 40) {
                                $bg = 'FFF2CC'; $fg = '7F6000';
                            } else {
                                $bg = 'FFC7CE'; $fg = '9C0006';
                            }
                            $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $fg]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                            ]);
                        }
                    }

                    // Rata-rata column
                    if ($this->mapelCount > 1) {
                        $avgCol = Coordinate::stringFromColumnIndex(4 + $this->mapelCount + 1);
                        $val = $sheet->getCell("{$avgCol}{$row}")->getValue();
                        if (is_numeric($val) && $val > 0) {
                            $sheet->getStyle("{$avgCol}{$row}")->getFont()->setBold(true);
                        }
                    }
                }

                // ── Kelas sub-header styling ──
                foreach ($this->kelasRanges as $kelasRow) {
                    $sheet->getStyle("A{$kelasRow}:{$lastCol}{$kelasRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    ]);
                    $sheet->mergeCells("B{$kelasRow}:D{$kelasRow}");
                }

                // ── Tidak hadir rows — red background ──
                foreach ($this->tidakHadirRows as $absentRow) {
                    $sheet->getStyle("A{$absentRow}:{$lastCol}{$absentRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']],
                    ]);
                    // Kehadiran cell — red text
                    $sheet->getStyle("{$lastCol}{$absentRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'CC0000']],
                    ]);
                }

                // ── Freeze panes at data header ──
                $freezeRow = $headerRow + 1;
                $sheet->freezePane("E{$freezeRow}");

                // ── Auto-filter on data header ──
                $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");

                // ── Column widths for mapel ──
                for ($col = 5; $col <= 4 + $this->mapelCount; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colLetter)->setWidth(12);
                }
                if ($this->mapelCount > 1) {
                    $avgCol = Coordinate::stringFromColumnIndex(4 + $this->mapelCount + 1);
                    $sheet->getColumnDimension($avgCol)->setWidth(10);
                }
                $sheet->getColumnDimension($lastCol)->setWidth(14);

                // ── Print settings ──
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);
            }
        ];
    }
}
