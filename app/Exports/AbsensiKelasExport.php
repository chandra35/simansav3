<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AbsensiKelasExport implements WithMultipleSheets
{
    public function __construct(protected Collection $kelasList)
    {
    }

    public function sheets(): array
    {
        return $this->kelasList
            ->map(fn ($kelas) => new AbsensiKelasSheet($kelas))
            ->values()
            ->all();
    }
}
