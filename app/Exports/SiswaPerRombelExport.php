<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SiswaPerRombelExport implements WithMultipleSheets
{
    public function __construct(
        protected Collection $rows,
        protected string $emptyTitle = 'Data Siswa'
    ) {
    }

    public function sheets(): array
    {
        if ($this->rows->isEmpty()) {
            return [new SiswaExport(collect(), $this->safeSheetTitle($this->emptyTitle))];
        }

        return $this->rows
            ->groupBy(fn ($siswa) => $siswa->kelasAktif->first()?->nama_kelas ?: 'Tanpa Rombel')
            ->sortKeysUsing('strnatcasecmp')
            ->map(fn (Collection $rows, string $kelas) => new SiswaExport(
                $rows->sortBy('nama_lengkap')->values(),
                $this->safeSheetTitle($kelas)
            ))
            ->values()
            ->all();
    }

    private function safeSheetTitle(string $title): string
    {
        $title = trim(preg_replace('/[\[\]\*\/\\\\\?\:]+/', ' ', $title));
        $title = preg_replace('/\s+/', ' ', $title) ?: 'Rombel';

        return mb_substr($title, 0, 31);
    }
}
