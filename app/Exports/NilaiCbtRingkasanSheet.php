<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class NilaiCbtRingkasanSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    protected $byTingkat;
    protected $mapelByTingkat;
    protected $smartq;
    protected $tableRows = 0;

    public function __construct($byTingkat, $mapelByTingkat, $smartq)
    {
        $this->byTingkat = $byTingkat;
        $this->mapelByTingkat = $mapelByTingkat;
        $this->smartq = $smartq;
    }

    public function array(): array
    {
        $rows = [];
        $totalAll = $this->byTingkat->flatten(1)->count();
        $hadirAll = $this->byTingkat->flatten(1)->filter(fn($r) => ($r['has_attempt'] ?? false))->count();

        // Title
        $rows[] = ['RINGKASAN NILAI CBT MOODLE'];
        $rows[] = [$this->smartq->nama];
        $rows[] = ['Discan: ' . ($this->smartq->last_scan_at?->format('d M Y H:i') ?? '-')];
        $rows[] = ['Total: ' . $totalAll . ' siswa | Hadir: ' . $hadirAll . ' | Tidak Hadir: ' . ($totalAll - $hadirAll)];
        $rows[] = [];

        // ── Overview per Tingkat ──
        $rows[] = ['RINGKASAN PER TINGKAT'];
        $rows[] = ['Tingkat', 'Total Siswa', 'Hadir', 'Tidak Hadir', '% Kehadiran', 'Rata-rata', 'Tertinggi', 'Terendah', 'Jumlah Mapel'];
        $this->tableRows = 0;

        foreach ($this->byTingkat as $tkt => $tktRows) {
            $tktTotal = $tktRows->count();
            $tktHadir = $tktRows->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
            $tktAvg = $tktRows->where('has_attempt', true)->avg('normalized_100');
            $tktMax = $tktRows->where('has_attempt', true)->max('normalized_100');
            $tktMin = $tktRows->where('has_attempt', true)->min('normalized_100');
            $tktMapelCount = count($this->mapelByTingkat[$tkt] ?? []);

            $rows[] = [
                $tkt ? 'Tingkat ' . $tkt : 'Lainnya',
                $tktTotal,
                $tktHadir,
                $tktTotal - $tktHadir,
                $tktTotal > 0 ? round(($tktHadir / $tktTotal) * 100, 1) . '%' : '0%',
                round($tktAvg ?? 0, 1),
                round($tktMax ?? 0, 1),
                round($tktMin ?? 0, 1),
                $tktMapelCount,
            ];
            $this->tableRows++;
        }

        $rows[] = [];
        $rows[] = [];

        // ── Detail per Tingkat per Mapel ──
        foreach ($this->byTingkat as $tkt => $tktRows) {
            $tktLabel = $tkt ? 'Tingkat ' . $tkt : 'Lainnya';
            $tktMapel = collect($this->mapelByTingkat[$tkt] ?? []);
            $tktTotal = $tktRows->count();

            $tktMapelWajib = [];
            foreach ($tktMapel as $m) {
                $attempts = $tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $m['quiz_id'])->where('normalized_100', '>', 0)->count();
                if ($attempts > ($tktTotal * 0.5)) $tktMapelWajib[] = $m['quiz_id'];
            }

            $rows[] = ['ANALISIS MAPEL — ' . strtoupper($tktLabel)];
            $rows[] = ['No', 'Mata Pelajaran', 'Tipe', 'Mengerjakan', '% Partisipasi', 'Rata-rata', 'Tertinggi', 'Terendah', 'Keterangan'];

            $num = 0;
            foreach ($tktMapel as $m) {
                $num++;
                $qid = $m['quiz_id'];
                $scores = $tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $qid)->where('normalized_100', '>', 0);
                $isWajib = in_array($qid, $tktMapelWajib);
                $avg = $scores->count() > 0 ? round($scores->avg('normalized_100'), 1) : 0;
                $max = $scores->count() > 0 ? round($scores->max('normalized_100'), 1) : 0;
                $min = $scores->count() > 0 ? round($scores->min('normalized_100'), 1) : 0;
                $pct = $tktTotal > 0 ? round(($scores->count() / $tktTotal) * 100, 1) . '%' : '0%';

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
                    $pct,
                    $avg,
                    $max,
                    $min,
                    $ket,
                ];
            }

            $rows[] = [];
        }

        // ── Per-Kelas Summary ──
        $rows[] = ['RINGKASAN PER KELAS'];
        $rows[] = ['No', 'Kelas', 'Tingkat', 'Total Siswa', 'Hadir', 'Tidak Hadir', '% Kehadiran', 'Rata-rata'];

        $num = 0;
        foreach ($this->byTingkat as $tkt => $tktRows) {
            $byKelas = $tktRows->groupBy(fn($r) => $r['siswa_kelas'] ?? $r['moodle_lastname'] ?? 'Tanpa Kelas')->sortKeys();
            foreach ($byKelas as $kelasName => $kelasRows) {
                $num++;
                $kelasCollection = collect($kelasRows);
                $kelasHadir = $kelasCollection->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
                $kelasAvg = $kelasCollection->where('has_attempt', true)->avg('normalized_100');

                $rows[] = [
                    $num,
                    $kelasName,
                    $tkt ? 'Tingkat ' . $tkt : '-',
                    $kelasCollection->count(),
                    $kelasHadir,
                    $kelasCollection->count() - $kelasHadir,
                    $kelasCollection->count() > 0 ? round(($kelasHadir / $kelasCollection->count()) * 100, 1) . '%' : '0%',
                    round($kelasAvg ?? 0, 1),
                ];
            }
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3:A4')->getFont()->setItalic(true);

                // Overview header (row 6-7)
                $sheet->getStyle('A6:I6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                ]);
                $sheet->getStyle('A7:I7')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Overview data
                $overviewEnd = 7 + $this->tableRows;
                if ($this->tableRows > 0) {
                    $sheet->getStyle("A8:I{$overviewEnd}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("B8:I{$overviewEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Find and style all section headers (rows that contain all-caps text in col A)
                $lastRow = $sheet->getHighestRow();
                for ($row = 1; $row <= $lastRow; $row++) {
                    $val = $sheet->getCell("A{$row}")->getValue();
                    if (is_string($val) && (str_starts_with($val, 'ANALISIS MAPEL') || str_starts_with($val, 'RINGKASAN PER KELAS'))) {
                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                        ]);

                        // Style the sub-header row below
                        $subRow = $row + 1;
                        if ($subRow <= $lastRow) {
                            $sheet->getStyle("A{$subRow}:I{$subRow}")->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
                                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);

                            // Data rows below sub-header until empty
                            for ($dr = $subRow + 1; $dr <= $lastRow; $dr++) {
                                $cellVal = $sheet->getCell("A{$dr}")->getValue();
                                if ($cellVal === null || $cellVal === '') break;
                                $sheet->getStyle("A{$dr}:I{$dr}")->applyFromArray([
                                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                                ]);

                                // Color-code avg column (F)
                                $avgVal = $sheet->getCell("F{$dr}")->getValue();
                                if (is_numeric($avgVal) && $avgVal > 0) {
                                    $color = $avgVal >= 80 ? '70AD47' : ($avgVal >= 60 ? '4472C4' : ($avgVal >= 40 ? 'FFC000' : 'FF4444'));
                                    $sheet->getStyle("F{$dr}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Print settings
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
            }
        ];
    }
}
