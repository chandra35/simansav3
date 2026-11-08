<?php

namespace App\Exports;

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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SiswaYatimExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithColumnFormatting, WithCustomValueBinder
{
    private const TEXT_COLUMNS = [
        'C',  // NISN
        'D',  // NIK Siswa
        'K',  // NIK Ayah
        'N',  // HP Ayah
        'P',  // NIK Ibu
        'S',  // HP Ibu
        'U',  // No. KK
        'V',  // No. KIP
        'W',  // No. PKH
    ];

    protected Collection $rows;
    protected int $counter = 0;
    protected string $sheetTitle;

    public function __construct(Collection $rows, string $sheetTitle = 'Siswa Yatim Piatu')
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

    public function columnFormats(): array
    {
        return [];
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
            'NIK',
            'Jenis Kelamin',
            'Status Siswa',
            'Kelas / Rombel',
            'Kategori',
            'Nama Ayah',
            'Status Ayah',
            'NIK Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'No. HP Ayah',
            'Nama Ibu',
            'Status Ibu',
            'NIK Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'No. HP Ibu',
            'No. KK',
            'No. KIP',
            'No. PKH',
            'Alamat Orang Tua',
        ];
    }

    public function map($siswa): array
    {
        $this->counter++;
        $ortu  = $siswa->ortu;
        $kelas = $siswa->kelasTahunAktif->first();

        $statusAyah = $ortu?->status_ayah ?? '';
        $statusIbu  = $ortu?->status_ibu ?? '';

        $kategori = match (true) {
            $statusAyah === 'meninggal' && $statusIbu === 'meninggal' => 'Yatim Piatu',
            $statusAyah === 'meninggal' => 'Yatim',
            $statusIbu === 'meninggal'  => 'Piatu',
            default => 'Lainnya',
        };

        $formatStatus = fn(?string $val) => match ($val) {
            'meninggal'   => 'Meninggal',
            'masih_hidup' => 'Masih Hidup',
            default       => $val ?: '-',
        };

        $statusSiswaLabel = match ($siswa->status_siswa) {
            'aktif'         => 'Aktif',
            'lulus'         => 'Lulus',
            'mutasi_keluar' => 'Mutasi Keluar',
            default         => ucfirst((string) $siswa->status_siswa),
        };

        return [
            $this->counter,
            $siswa->nama_lengkap ?? '',
            $siswa->nisn ?? '',
            $siswa->nik ?? '',
            $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : ''),
            $statusSiswaLabel,
            $kelas?->nama_kelas ?: 'Tanpa Rombel',
            $kategori,
            $ortu?->nama_ayah ?? '',
            $formatStatus($statusAyah),
            $ortu?->nik_ayah ?? '',
            $ortu?->pekerjaan_ayah ?? '',
            $ortu?->penghasilan_ayah ?? '',
            $ortu?->hp_ayah ?? '',
            $ortu?->nama_ibu ?? '',
            $formatStatus($statusIbu),
            $ortu?->nik_ibu ?? '',
            $ortu?->pekerjaan_ibu ?? '',
            $ortu?->penghasilan_ibu ?? '',
            $ortu?->hp_ibu ?? '',
            $ortu?->no_kk ?? '',
            $siswa->nomor_kip ?? '',
            $siswa->nomor_pkh ?? '',
            $ortu?->alamat_ortu ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 30,  // Nama Lengkap
            'C' => 16,  // NISN
            'D' => 20,  // NIK
            'E' => 14,  // JK
            'F' => 14,  // Status Siswa
            'G' => 18,  // Kelas
            'H' => 16,  // Kategori
            'I' => 26,  // Nama Ayah
            'J' => 16,  // Status Ayah
            'K' => 20,  // NIK Ayah
            'L' => 22,  // Pekerjaan Ayah
            'M' => 20,  // Penghasilan Ayah
            'N' => 16,  // HP Ayah
            'O' => 26,  // Nama Ibu
            'P' => 16,  // Status Ibu
            'Q' => 20,  // NIK Ibu
            'R' => 22,  // Pekerjaan Ibu
            'S' => 20,  // Penghasilan Ibu
            'T' => 16,  // HP Ibu
            'U' => 20,  // No. KK
            'V' => 18,  // No. KIP
            'W' => 18,  // No. PKH
            'X' => 35,  // Alamat
        ];
    }
}
