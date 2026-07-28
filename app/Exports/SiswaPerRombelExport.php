<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SiswaPerRombelExport implements WithMultipleSheets
{
    public function __construct(
        protected Collection $rows,
        protected string $emptyTitle = 'Data Siswa',
        protected ?int $tingkat = null
    ) {
    }

    public function sheets(): array
    {
        if ($this->rows->isEmpty()) {
            return [new SiswaExport(collect(), 'ALL')];
        }

        $sheets = $this->rows
            ->groupBy(fn ($siswa) => $siswa->kelasTahunAktif->first()?->nama_kelas ?: 'Tanpa Rombel')
            ->sortKeysUsing(fn ($a, $b) => strnatcasecmp($this->classSortKey($a), $this->classSortKey($b)))
            ->map(fn (Collection $rows, string $kelas) => new SiswaExport(
                $rows->sortBy('nama_lengkap')->values(),
                $this->safeSheetTitle($kelas)
            ))
            ->values()
            ->all();

        $sheets[] = new SiswaExport($this->allRows(), 'ALL');

        return $sheets;
    }

    private function allRows(): Collection
    {
        return $this->rows
            ->sort(fn ($a, $b) => strnatcasecmp($this->classSortKey($this->className($a)), $this->classSortKey($this->className($b)))
                ?: strnatcasecmp($a->nama_lengkap ?? '', $b->nama_lengkap ?? ''))
            ->values();
    }

    private function className($siswa): string
    {
        return $siswa->kelasTahunAktif->first()?->nama_kelas ?: 'Tanpa Rombel';
    }

    private function classSortKey(string $kelas): string
    {
        return $kelas === 'Tanpa Rombel' ? 'ZZZ Tanpa Rombel' : $kelas;
    }

    private function safeSheetTitle(string $title): string
    {
        $title = trim(preg_replace('/[\[\]\*\/\\\\\?\:]+/', ' ', $title));
        $title = preg_replace('/\s+/', ' ', $title) ?: 'Rombel';

        return mb_substr($title, 0, 31);
    }
}
