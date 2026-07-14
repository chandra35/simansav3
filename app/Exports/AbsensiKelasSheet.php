<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiKelasSheet implements FromArray, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    public function __construct(protected $kelas)
    {
    }

    public function array(): array
    {
        $rows = [
            ['DAFTAR HADIR KELAS'],
            ['Kelas', $this->kelas->nama_lengkap],
            ['Semester/Tahun', ucfirst($this->kelas->tahunPelajaran->semester_aktif ?? '-') . ' / ' . ($this->kelas->tahunPelajaran->nama ?? '-')],
            ['Wali Kelas', $this->kelas->waliKelas->name ?? '-'],
            [],
            ['No', 'NISN', 'Nama', 'L/P', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'S', 'I', 'A'],
        ];

        foreach ($this->kelas->siswas as $index => $siswa) {
            $rows[] = [
                $index + 1,
                $siswa->nisn,
                strtoupper($siswa->nama_lengkap),
                $siswa->jenis_kelamin === 'L' ? 'L' : 'P',
                '', '', '', '', '', '', '', '', '',
            ];
        }

        $maleCount = $this->kelas->siswas->where('jenis_kelamin', 'L')->count();
        $femaleCount = $this->kelas->siswas->where('jenis_kelamin', 'P')->count();

        $rows[] = [];
        $rows[] = ['Jumlah Siswa', $this->kelas->siswas->count()];
        $rows[] = ['Laki-laki', $maleCount];
        $rows[] = ['Perempuan', $femaleCount];

        return $rows;
    }

    public function title(): string
    {
        $title = trim(preg_replace('/[\[\]\*\/\\\\\?\:]+/', ' ', (string) $this->kelas->nama_lengkap));
        $title = preg_replace('/\s+/', ' ', $title) ?: 'Kelas';

        return mb_substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastStudentRow = 6 + max($this->kelas->siswas->count(), 1);
        $summaryStart = $lastStudentRow + 2;

        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('A6:M6')->getFont()->setBold(true);
        $sheet->getStyle('A6:M6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A6:M{$lastStudentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A6:A{$lastStudentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D6:M{$lastStudentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$summaryStart}:A" . ($summaryStart + 2))->getFont()->setBold(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->freezePane('A7');
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(34);
                foreach (['E', 'F', 'G', 'H', 'I', 'J'] as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setWidth(11);
                }
            },
        ];
    }
}
