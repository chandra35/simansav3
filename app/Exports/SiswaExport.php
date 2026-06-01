<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $rows;
    protected int $counter = 0;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NISN',
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
            'Tanggal Daftar',
        ];
    }

    public function map($siswa): array
    {
        $this->counter++;
        $ortu  = $siswa->ortu;
        $kelas = $siswa->kelasAktif->first();
        $user  = $siswa->user;

        return [
            $this->counter,
            $siswa->nama_lengkap,
            $siswa->nisn,
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
            'D' => 18,  // Username
            'E' => 26,  // Password
            'F' => 20,  // NIK
            'G' => 14,  // JK
            'H' => 18,  // Tempat Lahir
            'I' => 14,  // Tgl Lahir
            'J' => 12,  // Agama
            'K' => 16,  // HP
            'L' => 12,  // Kelas
            'M' => 12,  // Tahun Masuk
            'N' => 16,  // NPSN
            'O' => 28,  // Email
            'P' => 26,  // Nama Ayah
            'Q' => 20,  // NIK Ayah
            'R' => 22,  // Pekerjaan Ayah
            'S' => 20,  // Penghasilan Ayah
            'T' => 16,  // HP Ayah
            'U' => 26,  // Nama Ibu
            'V' => 20,  // NIK Ibu
            'W' => 22,  // Pekerjaan Ibu
            'X' => 20,  // Penghasilan Ibu
            'Y' => 16,  // HP Ibu
            'Z' => 20,  // No KK
            'AA' => 16, // Status Diri
            'AB' => 16, // Status Ortu
            'AC' => 14, // Verval
            'AD' => 14, // Tgl Daftar
        ];
    }
}
