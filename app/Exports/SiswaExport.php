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
            'D' => 18,  // Nomor Tes
            'E' => 18,  // Username
            'F' => 26,  // Password
            'G' => 20,  // NIK
            'H' => 14,  // JK
            'I' => 18,  // Tempat Lahir
            'J' => 14,  // Tgl Lahir
            'K' => 12,  // Agama
            'L' => 16,  // HP
            'M' => 12,  // Kelas
            'N' => 12,  // Tahun Masuk
            'O' => 16,  // NPSN
            'P' => 28,  // Email
            'Q' => 26,  // Nama Ayah
            'R' => 20,  // NIK Ayah
            'S' => 22,  // Pekerjaan Ayah
            'T' => 20,  // Penghasilan Ayah
            'U' => 16,  // HP Ayah
            'V' => 26,  // Nama Ibu
            'W' => 20,  // NIK Ibu
            'X' => 22,  // Pekerjaan Ibu
            'Y' => 20,  // Penghasilan Ibu
            'Z' => 16,  // HP Ibu
            'AA' => 20, // No KK
            'AB' => 16, // Status Diri
            'AC' => 16, // Status Ortu
            'AD' => 14, // Verval
            'AE' => 14, // Tgl Daftar
        ];
    }
}
