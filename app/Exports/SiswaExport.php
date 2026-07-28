<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SiswaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithColumnFormatting, WithCustomValueBinder
{
    private const TEXT_COLUMNS = [
        'C',  // NISN
        'D',  // NIS Lokal
        'E',  // Nomor Tes PPDB
        'F',  // Username
        'G',  // Password default
        'H',  // NIK siswa
        'M',  // No. HP siswa
        'P',  // NPSN sekolah asal
        'S',  // NIK ayah
        'V',  // No. HP ayah
        'X',  // NIK ibu
        'AA', // No. HP ibu
        'AB', // No. KK
    ];

    protected Collection $rows;
    protected int $counter = 0;
    protected string $sheetTitle;

    public function __construct(Collection $rows, string $sheetTitle = 'Data Siswa')
    {
        $this->rows = $rows;
        $this->sheetTitle = $sheetTitle;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NISN',
            'NIS Lokal',
            'Nomor Tes PPDB',
            'Username',
            'Password (Default = NISN)',
            'NIK',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'No. HP Siswa',
            'Kelas',
            'Tahun Masuk',
            'Asal Sekolah (NPSN)',
            'Email',
            'Nama Ayah',
            'NIK Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'No. HP Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'No. HP Ibu',
            'No. KK',
            'Status Data Diri',
            'Status Data Ortu',
            'Verval Ijazah',
            'Status EMIS',
            'Tanggal Masuk EMIS',
            'Tanggal Daftar',
        ];
    }

    public function map($siswa): array
    {
        $this->counter++;
        $ortu  = $siswa->ortu;
        $kelas = $siswa->kelasTahunAktif->first();
        $user  = $siswa->user;

        return [
            $this->counter,
            $siswa->nama_lengkap,
            $siswa->nisn,
            $siswa->nis_lokal ?? '',
            $siswa->nomor_tes ?? '',
            $user?->username ?? '',
            $siswa->nisn, // default password = NISN
            $siswa->nik ?? '',
            $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan',
            $siswa->tempat_lahir ?? '',
            $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y') : '',
            $siswa->agama ?? '',
            $siswa->nomor_hp ?? '',
            $kelas?->nama_kelas ?? 'Tanpa Rombel',
            $siswa->tahun_masuk ?? '',
            $siswa->npsn_asal_sekolah ?? '',
            $user?->email ?? '',
            $ortu?->nama_ayah ?? '',
            $ortu?->nik_ayah ?? '',
            $ortu?->pekerjaan_ayah ?? '',
            $ortu?->penghasilan_ayah ?? '',
            $ortu?->hp_ayah ?? '',
            $ortu?->nama_ibu ?? '',
            $ortu?->nik_ibu ?? '',
            $ortu?->pekerjaan_ibu ?? '',
            $ortu?->penghasilan_ibu ?? '',
            $ortu?->hp_ibu ?? '',
            $ortu?->no_kk ?? '',
            $siswa->data_diri_completed ? 'Lengkap' : 'Belum',
            $siswa->data_ortu_completed ? 'Lengkap' : 'Belum',
            $siswa->verval_ijazah ? 'Sudah' : 'Belum',
            $siswa->emis_registered ? 'Sudah Masuk EMIS' : 'Belum Masuk EMIS',
            $siswa->emis_registered_at ? $siswa->emis_registered_at->format('d/m/Y H:i') : '',
            $siswa->created_at ? $siswa->created_at->format('d/m/Y') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a6fc4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Nama
            'C' => 16,  // NISN
            'D' => 22,  // NIS Lokal
            'E' => 18,  // Nomor Tes
            'F' => 18,  // Username
            'G' => 26,  // Password
            'H' => 20,  // NIK
            'I' => 14,  // JK
            'J' => 18,  // Tempat Lahir
            'K' => 14,  // Tgl Lahir
            'L' => 12,  // Agama
            'M' => 16,  // HP
            'N' => 12,  // Kelas
            'O' => 12,  // Tahun Masuk
            'P' => 16,  // NPSN
            'Q' => 28,  // Email
            'R' => 26,  // Nama Ayah
            'S' => 20,  // NIK Ayah
            'T' => 22,  // Pekerjaan Ayah
            'U' => 20,  // Penghasilan Ayah
            'V' => 16,  // HP Ayah
            'W' => 26,  // Nama Ibu
            'X' => 20,  // NIK Ibu
            'Y' => 22,  // Pekerjaan Ibu
            'Z' => 20,  // Penghasilan Ibu
            'AA' => 16, // HP Ibu
            'AB' => 20, // No KK
            'AC' => 16, // Status Diri
            'AD' => 16, // Status Ortu
            'AE' => 14, // Verval
            'AF' => 18, // Status EMIS
            'AG' => 18, // Tgl EMIS
            'AH' => 14, // Tgl Daftar
        ];
    }

    public function columnFormats(): array
    {
        return array_fill_keys(self::TEXT_COLUMNS, NumberFormat::FORMAT_TEXT);
    }
}
