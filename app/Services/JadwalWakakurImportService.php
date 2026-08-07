<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalWakakurImportService
{
    /**
     * Membaca format jadwal Wakakur: kode GTK+mapel pada satu sheet dan
     * jadwal kelas pada sheet terpisah. Data belum menyentuh database.
     */
    public function preview(string $path): array
    {
        $reader = IOFactory::createReader(IOFactory::identify($path));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $codeSheet = $this->findSheet($spreadsheet->getAllSheets(), 'kodegtkmapel');
        $scheduleSheet = $this->findSheet($spreadsheet->getAllSheets(), 'jadwal');
        if (! $codeSheet || ! $scheduleSheet) {
            throw new \DomainException('Template harus memiliki sheet Kode_GTK_mapel dan jadwal.');
        }

        $references = $this->readReferences($codeSheet);
        $schedule = $this->readSchedule($scheduleSheet, $references);

        return array_merge($references, $schedule, [
            'sheet_kode' => $codeSheet->getTitle(),
            'sheet_jadwal' => $scheduleSheet->getTitle(),
        ]);
    }

    public function classKey(string $name): string
    {
        $key = Str::upper(preg_replace('/[^A-Z0-9]+/i', '', $name) ?: '');

        // Template Wakakur memakai 12-A1, sedangkan master kelas 2026/2027
        // menggunakan XII-A1. Keduanya adalah kelas yang sama.
        return preg_replace('/^12(?=[A-Z0-9])/', 'XII', $key) ?: $key;
    }

    private function findSheet(array $sheets, string $needle): ?Worksheet
    {
        $needle = $this->normalize($needle);

        foreach ($sheets as $sheet) {
            if (str_contains($this->normalize($sheet->getTitle()), $needle)) {
                return $sheet;
            }
        }

        return null;
    }

    private function readReferences(Worksheet $sheet): array
    {
        $gtk = [];
        $mapel = [];

        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            foreach ([['A', 'B'], ['D', 'E']] as [$codeColumn, $nameColumn]) {
                $code = trim((string) $sheet->getCell($codeColumn.$row)->getFormattedValue());
                $name = trim((string) $sheet->getCell($nameColumn.$row)->getFormattedValue());
                if (preg_match('/^\d{1,3}$/', $code) && $name !== '') {
                    $gtk[$code] = $name;
                }
            }

            $code = Str::upper(trim((string) $sheet->getCell('G'.$row)->getFormattedValue()));
            $name = trim((string) $sheet->getCell('H'.$row)->getFormattedValue());
            if (preg_match('/^[A-Z]$/', $code) && $name !== '') {
                $mapel[$code] = $name;
            }
        }

        if (! $gtk || ! $mapel) {
            throw new \DomainException('Kode GTK atau kode mata pelajaran pada template tidak ditemukan.');
        }

        return ['gtk_references' => $gtk, 'mapel_references' => $mapel];
    }

    private function readSchedule(Worksheet $sheet, array $references): array
    {
        $headerRow = null;
        for ($row = 1; $row <= min(20, $sheet->getHighestRow()); $row++) {
            if ($this->normalize((string) $sheet->getCell('A'.$row)->getFormattedValue()) === 'hari'
                && $this->normalize((string) $sheet->getCell('B'.$row)->getFormattedValue()) === 'jam') {
                $headerRow = $row;
                break;
            }
        }
        if (! $headerRow) {
            throw new \DomainException('Header HARI dan JAM pada sheet jadwal tidak ditemukan.');
        }

        $columns = $this->readClassColumns($sheet, $headerRow, $headerRow + 1);
        $hari = null;
        $slots = [];
        $dayMaxJam = [];
        $ignored = 0;
        $warnings = [];

        for ($row = $headerRow + 2; $row <= $sheet->getHighestRow(); $row++) {
            $dayLabel = $this->normalize((string) $sheet->getCell('A'.$row)->getFormattedValue());
            $hari = $this->dayValue($dayLabel) ?? $hari;
            $jam = trim((string) $sheet->getCell('B'.$row)->getFormattedValue());
            if (! $hari || ! ctype_digit($jam) || (int) $jam < 1) {
                continue;
            }
            $dayMaxJam[$hari] = max($dayMaxJam[$hari] ?? 0, (int) $jam);

            foreach ($columns as $column => $class) {
                $raw = Str::upper(trim((string) $sheet->getCell($column.$row)->getFormattedValue()));
                if ($raw === '' || $raw === 'UPACARA') {
                    continue;
                }
                if (! $class['kelas']) {
                    $ignored++;
                    continue;
                }
                if (! preg_match('/^(\d{1,3})([A-Z])$/', $raw, $matches)) {
                    $warnings[] = "{$sheet->getTitle()}!{$column}{$row}: kode {$raw} tidak memakai format nomor GTK + huruf mapel.";
                    continue;
                }

                $slots[] = [
                    'sheet_row' => $row,
                    'sheet_column' => $column,
                    'hari' => $hari,
                    'jam_ke' => (int) $jam,
                    'kelas_nama' => $class['kelas'],
                    'kelas_key' => $this->classKey($class['kelas']),
                    'kode_gtk' => $matches[1],
                    'kode_mapel' => $matches[2],
                    'gtk_excel' => $references['gtk_references'][$matches[1]] ?? null,
                    'mapel_excel' => $references['mapel_references'][$matches[2]] ?? null,
                ];
            }
        }

        if (! $slots) {
            throw new \DomainException('Tidak ada slot jadwal kelas yang dapat dibaca dari template.');
        }

        return compact('slots', 'dayMaxJam', 'ignored', 'warnings');
    }

    private function readClassColumns(Worksheet $sheet, int $groupRow, int $numberRow): array
    {
        $columns = [];
        $group = '';
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

        for ($index = 3; $index <= $lastColumn; $index++) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
            $label = trim((string) $sheet->getCell($column.$groupRow)->getFormattedValue());
            if ($label !== '') {
                $group = $label;
            }

            $number = trim((string) $sheet->getCell($column.$numberRow)->getFormattedValue());
            if ($group === '' || $number === '') {
                continue;
            }

            $normalizedGroup = $this->normalize($group);
            if (str_contains($normalizedGroup, 'bk')) {
                $columns[$column] = ['kelas' => null];
                continue;
            }

            $group = preg_replace('/^kelas\s*/i', '', $group) ?: $group;
            $group = trim(preg_replace('/\s+/', ' ', $group));
            // Penamaan kelas aktif SIMANSA memakai 12-A1, sementara template
            // Wakakur menulis Kelas XII A.
            $group = preg_replace('/^XII\b/i', '12', $group) ?: $group;
            $kelas = strcasecmp($group, 'X') === 0
                ? 'X-'.$number
                : str_replace(' ', '-', $group).$number;
            $columns[$column] = ['kelas' => $kelas];
        }

        return $columns;
    }

    private function dayValue(string $value): ?string
    {
        return [
            'senin' => 'senin',
            'selasa' => 'selasa',
            'rabu' => 'rabu',
            'kamis' => 'kamis',
            'jumat' => 'jumat',
            'sabtu' => 'sabtu',
        ][$value] ?? null;
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(Str::ascii($value));

        return trim(preg_replace('/[^a-z0-9]+/', '', $value) ?: '');
    }
}
