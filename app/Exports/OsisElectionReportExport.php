<?php

namespace App\Exports;

use App\Models\OsisElection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OsisElectionReportExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(private OsisElection $election, private array $report) {}

    public function array(): array
    {
        $rows = [['LAPORAN HASIL PEMILIHAN OSIM'], [$this->election->title], ['Tahun pelajaran', $this->election->tahunPelajaran?->nama], ['Periode', $this->election->starts_at->format('d/m/Y H:i').' - '.$this->election->ends_at->format('d/m/Y H:i').' WIB'], ['Berkas dicetak langsung dari SIMANSA'], [], ['RINGKASAN', 'JUMLAH'], ['DPT', $this->report['total']], ['Sudah memilih', $this->report['voted']], ['Belum memilih', $this->report['pending']], ['Partisipasi', $this->report['turnout'].'%'], [], ['PEROLEHAN SUARA', 'SUARA', 'PERSENTASE']];
        foreach ($this->report['packages'] as $item) $rows[] = ['Paslon '.$item['package']->number.' - '.($item['package']->name ?: 'Paket '.$item['package']->number), $item['votes'], $item['percentage'].'%'];
        $rows[] = []; $rows[] = ['PARTISIPASI PER ROMBEL', 'DPT', 'SUDAH', 'BELUM', 'PARTISIPASI'];
        foreach ($this->report['participation'] as $item) $rows[] = [$item['class'], $item['total'], $item['voted'], $item['pending'], $item['percentage'].'%'];
        $rows[] = []; $rows[] = ['DAFTAR PEMILIH', 'IDENTITAS', 'JENIS', 'CAKUPAN', 'STATUS', 'WAKTU MEMILIH'];
        foreach ($this->report['voterRows'] as $row) $rows[] = [$row['name'], $row['identity'], $row['type'], $row['scope'], $row['status'], $row['voted_at']];
        return $rows;
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true, 'size' => 15]], 7 => ['font' => ['bold' => true]], 13 => ['font' => ['bold' => true]]]; }
}
