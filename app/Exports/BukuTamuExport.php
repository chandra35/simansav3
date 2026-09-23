<?php

namespace App\Exports;

use App\Models\BukuTamu;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BukuTamuExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    public function __construct(private readonly ?string $start = null, private readonly ?string $end = null)
    {
    }

    public function query(): Builder
    {
        return BukuTamu::query()
            ->with(['provinsi', 'kota', 'kecamatan', 'kelurahan', 'qrToken'])
            ->when($this->start, fn (Builder $query) => $query->whereDate('tanggal_kunjungan', '>=', $this->start))
            ->when($this->end, fn (Builder $query) => $query->whereDate('tanggal_kunjungan', '<=', $this->end))
            ->latest('tanggal_kunjungan')->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Tanggal Kunjungan', 'Waktu Input (WIB)', 'Jenis Pengunjung', 'Nama',
            'Instansi/Lembaga', 'No. HP', 'Alamat Detail', 'Provinsi', 'Kabupaten/Kota',
            'Kecamatan', 'Kelurahan/Desa', 'Keperluan', 'IP Address', 'Device',
            'Platform', 'Browser', 'User-Agent', 'QR Token', 'Dibuat', 'Diperbarui',
        ];
    }

    public function map($item): array
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent((string) $item->user_agent);

        return [
            $item->tanggal_kunjungan?->format('d-m-Y'),
            $item->created_at?->timezone('Asia/Jakarta')->format('H:i:s'),
            ucfirst((string) ($item->jenis_tamu ?: 'individu')),
            $item->nama, $item->alamat_instansi, $item->nomor_hp, $item->alamat,
            $item->provinsi?->name, $item->kota?->name, $item->kecamatan?->name,
            $item->kelurahan?->name, $item->keperluan, $item->ip_address,
            $agent->deviceType(), $agent->platform(), $agent->browser(), $item->user_agent,
            $item->qrToken?->token, $item->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
            $item->updated_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:T1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:T1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F67B1');
        $sheet->getStyle('A1:T1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(32);

        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 16, 'B' => 16, 'C' => 18, 'D' => 24, 'E' => 28, 'F' => 16, 'G' => 32, 'H' => 18, 'I' => 20, 'J' => 18, 'K' => 20, 'L' => 32, 'M' => 18, 'N' => 14, 'O' => 18, 'P' => 18, 'Q' => 42, 'R' => 50, 'S' => 22, 'T' => 22];
    }

    public function title(): string
    {
        return 'Detail Buku Tamu';
    }
}
