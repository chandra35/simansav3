<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Statistik Lulusan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1, h2, h3 { margin: 0 0 8px; }
        .muted { color: #6b7280; }
        .section { margin-top: 18px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-grid td { width: 25%; border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        .summary-label { font-size: 9px; color: #6b7280; }
        .summary-value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
        .two-col { width: 100%; border-collapse: separate; border-spacing: 12px 0; }
        .two-col td { width: 50%; vertical-align: top; padding: 0; border: none; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <h1>Laporan Statistik Lulusan</h1>
    <div class="muted">
        Tahun Pelajaran: {{ $selectedTahun->nama }} |
        Diexport: {{ $generated_at->format('d-m-Y H:i:s') }}
    </div>

    <div class="section">
        <h3>Filter Aktif</h3>
        <table>
            <tbody>
                @foreach($filters as $label => $value)
                    <tr>
                        <th style="width: 220px;">{{ $label }}</th>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Ringkasan Utama</h3>
        <table class="summary-grid">
            <tr>
                <td><div class="summary-label">Total Siswa Kelas 12</div><div class="summary-value">{{ $summary['total'] }}</div></td>
                <td><div class="summary-label">Sudah Mengisi</div><div class="summary-value">{{ $summary['sudah_isi'] }}</div></td>
                <td><div class="summary-label">Belum Mengisi</div><div class="summary-value">{{ $summary['belum_isi'] }}</div></td>
                <td><div class="summary-label">Universitas Tujuan</div><div class="summary-value">{{ $summary['total_universitas'] }}</div></td>
            </tr>
            <tr>
                <td><div class="summary-label">Eligible SNBP</div><div class="summary-value">{{ $summary['eligible_total'] }}</div></td>
                <td><div class="summary-label">Sudah Isi Nomor SNBP</div><div class="summary-value">{{ $summary['eligible_sudah_isi_nomor'] }}</div></td>
                <td><div class="summary-label">Lulus SNBP</div><div class="summary-value">{{ $summary['eligible_lulus'] }}</div></td>
                <td><div class="summary-label">PTN Diterima dari SNBP</div><div class="summary-value">{{ $summary['total_ptn_diterima'] }}</div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="two-col">
            <tr>
                <td>
                    <h3>Statistik Per Jalur</h3>
                    <table>
                        <thead>
                            <tr><th>Jalur</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @foreach($per_jalur as $jalur => $jumlah)
                                <tr><td>{{ $jalur }}</td><td>{{ $jumlah }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td>
                    <h3>Status Checker SNBP</h3>
                    <table>
                        <thead>
                            <tr><th>Status</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Belum Dicek</td><td>{{ $checker_status['belum_dicek'] }}</td></tr>
                            <tr><td>Lulus</td><td>{{ $checker_status['lulus'] }}</td></tr>
                            <tr><td>Tidak Lulus</td><td>{{ $checker_status['tidak_lulus'] }}</td></tr>
                            <tr><td>Gagal Cek</td><td>{{ $checker_status['gagal_cek'] }}</td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="two-col">
            <tr>
                <td>
                    <h3>Top Universitas Tujuan</h3>
                    <table>
                        <thead>
                            <tr><th>Universitas</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse($top_universitas as $item)
                                <tr><td>{{ $item['label'] }}</td><td>{{ $item['jumlah'] }}</td></tr>
                            @empty
                                <tr><td colspan="2">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
                <td>
                    <h3>Top PTN Diterima SNBP</h3>
                    <table>
                        <thead>
                            <tr><th>Perguruan Tinggi</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse($top_ptn_snbp as $item)
                                <tr><td>{{ $item['label'] }}</td><td>{{ $item['jumlah'] }}</td></tr>
                            @empty
                                <tr><td colspan="2">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Top Program Studi Diterima SNBP</h3>
        <table>
            <thead>
                <tr><th>Program Studi</th><th style="width: 80px;">Jumlah</th></tr>
            </thead>
            <tbody>
                @forelse($top_prodi_snbp as $item)
                    <tr><td>{{ $item['label'] }}</td><td>{{ $item['jumlah'] }}</td></tr>
                @empty
                    <tr><td colspan="2">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Matriks Per Kelas</h3>
        <table>
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Eligible</th>
                    <th>Lulus SNBP</th>
                    <th>Sudah Isi</th>
                    <th>Belum Isi</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($per_kelas as $item)
                    <tr>
                        <td>{{ $item['kelas_nama'] }}</td>
                        <td>{{ $item['eligible'] }}</td>
                        <td>{{ $item['eligible_lulus'] }}</td>
                        <td>{{ $item['sudah_isi'] }}</td>
                        <td>{{ $item['belum_isi'] }}</td>
                        <td>{{ $item['total'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <h3>Daftar Lulusan</h3>
        <table>
            <thead>
                <tr>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Jalur</th>
                    <th>Checker SNBP</th>
                    <th>Universitas</th>
                    <th>Program Studi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->nisn }}</td>
                        <td>{{ $row->nama_lengkap }}</td>
                        <td>{{ $row->kelas_nama }}</td>
                        <td>{{ $row->is_filled ? 'Sudah Isi' : 'Belum Isi' }}</td>
                        <td>{{ $row->jalur_masuk ?: '-' }}</td>
                        <td>{{ $row->has_snbp_number ? (match($row->snbp_check_status){ 'lulus' => 'Lulus', 'tidak_lulus' => 'Tidak Lulus', 'gagal_cek' => 'Gagal Cek', default => 'Belum Dicek'}) : '-' }}</td>
                        <td>{{ $row->nama_universitas ?: '-' }}</td>
                        <td>{{ $row->program_studi ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Monitoring Siswa Eligible SNBP</h3>
        <table>
            <thead>
                <tr>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Kelas</th>
                    <th>No. SNBP</th>
                    <th>Status Checker</th>
                    <th>PTN</th>
                    <th>Program Studi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eligible_rows as $row)
                    <tr>
                        <td>{{ $row->nisn }}</td>
                        <td>{{ $row->nama_lengkap }}</td>
                        <td>{{ $row->tanggal_lahir ? \Carbon\Carbon::parse($row->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $row->kelas_nama ?: '-' }}</td>
                        <td>{{ $row->nomor_pendaftaran ?: '-' }}</td>
                        <td>{{ match($row->check_status){ 'lulus' => 'Lulus', 'tidak_lulus' => 'Tidak Lulus', 'gagal_cek' => 'Gagal Cek', default => 'Belum Dicek'} }}</td>
                        <td>{{ $row->nama_universitas ?: '-' }}</td>
                        <td>{{ $row->program_studi ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada data eligible.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
