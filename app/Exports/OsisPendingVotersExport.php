<?php

namespace App\Exports;

use App\Models\OsisElection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OsisPendingVotersExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(private OsisElection $election, private Collection $rows) {}

    public function array(): array
    {
        $rows = [['LAPORAN SISWA BELUM MEMILIH'], [$this->election->title], ['Dicetak', now()->format('d/m/Y H:i').' WIB'], ['Berkas dicetak langsung dari SIMANSA'], [], ['NO', 'NISN', 'NAMA LENGKAP', 'ROMBEL', 'SEKOLAH ASAL', 'ALAMAT']];
        foreach ($this->rows as $row) $rows[] = [$row['no'], $row['nisn'], $row['name'], $row['class'], $row['school'], $row['address']];
        return $rows;
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true, 'size' => 15]], 6 => ['font' => ['bold' => true]]]; }
}
