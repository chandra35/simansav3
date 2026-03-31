<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Statistik Lulusan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1, h2, h3 { margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .section { margin-top: 16px; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 8px -8px 0; }
        .cards td { width: 25%; border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        .label { font-size: 9px; color: #6b7280; }
        .value { font-size: 15px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
        .two-col { width: 100%; border-collapse: separate; border-spacing: 10px 0; }
        .two-col td { width: 50%; vertical-align: top; border: none; padding: 0; }
        .accepted-grid { width: 100%; border-collapse: separate; border-spacing: 10px 10px; margin: 6px -10px 0; }
        .accepted-grid td { width: 33.33%; border: none; padding: 0; vertical-align: top; }
        .accepted-card { border: 1px solid #d1d5db; background: #f8fafc; padding: 10px; min-height: 88px; }
        .accepted-head { width: 100%; border-collapse: collapse; }
        .accepted-head td { border: none; padding: 0; vertical-align: middle; }
        .avatar { width: 34px; height: 34px; border-radius: 17px; background: #1d4ed8; color: #ffffff; text-align: center; font-size: 12px; font-weight: bold; line-height: 34px; }
        .avatar-photo { width: 34px; height: 34px; border-radius: 17px; object-fit: cover; display: block; }
        .student-name { font-size: 11px; font-weight: bold; margin-bottom: 2px; }
        .student-meta { font-size: 9px; color: #6b7280; }
        .accepted-campus { margin-top: 8px; font-size: 10px; font-weight: bold; }
        .accepted-program { margin-top: 2px; font-size: 9px; color: #374151; }
        .note { margin-top: 10px; padding: 8px; border: 1px solid #d1d5db; background: #f9fafb; }
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
        <table class="cards">
            <tr>
                <td><div class="label">Total Siswa Kelas 12</div><div class="value">{{ $summary['total'] }}</div></td>
                <td><div class="label">Sudah Mengisi Lulusan</div><div class="value">{{ $summary['sudah_isi'] }}</div></td>
                <td><div class="label">Belum Mengisi Lulusan</div><div class="value">{{ $summary['belum_isi'] }}</div></td>
                <td><div class="label">Universitas Tujuan</div><div class="value">{{ $summary['total_universitas'] }}</div></td>
            </tr>
            <tr>
                <td><div class="label">Eligible SNBP</div><div class="value">{{ $summary['eligible_total'] }}</div></td>
                <td><div class="label">Sudah Isi Nomor SNBP</div><div class="value">{{ $summary['eligible_sudah_isi_nomor'] }}</div></td>
                <td><div class="label">Lulus SNBP</div><div class="value">{{ $summary['eligible_lulus'] }}</div></td>
                <td><div class="label">PTN Diterima dari SNBP</div><div class="value">{{ $summary['total_ptn_diterima'] }}</div></td>
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
                            <tr><th>Jalur</th><th style="width: 80px;">Jumlah</th></tr>
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
                            <tr><th>Status</th><th style="width: 80px;">Jumlah</th></tr>
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
                            <tr><th>Universitas</th><th style="width: 80px;">Jumlah</th></tr>
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
                            <tr><th>Perguruan Tinggi</th><th style="width: 80px;">Jumlah</th></tr>
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
        <table class="two-col">
            <tr>
                <td>
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
                </td>
                <td>
                    <h3>Matriks Per Kelas</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Kelas</th>
                                <th>Eligible</th>
                                <th>Lulus</th>
                                <th>Sudah Isi</th>
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
                                    <td>{{ $item['total'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Daftar Siswa Lolos SNBP</h3>
        @if(!empty($accepted_students))
            <table class="accepted-grid">
                @foreach(array_chunk($accepted_students, 3) as $row)
                    <tr>
                        @foreach($row as $student)
                            <td>
                                <div class="accepted-card">
                                    <table class="accepted-head">
                                        <tr>
                                            <td style="width: 42px;">
                                                @if(!empty($student['photo_path']))
                                                    <img src="{{ $student['photo_path'] }}" alt="{{ $student['nama_lengkap'] }}" class="avatar-photo">
                                                @else
                                                    <div class="avatar">{{ $student['initials'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="student-name">{{ $student['nama_lengkap'] }}</div>
                                                <div class="student-meta">{{ $student['kelas_nama'] }} | NISN {{ $student['nisn'] }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="accepted-campus">{{ $student['nama_universitas'] }}</div>
                                    <div class="accepted-program">{{ $student['program_studi'] }}</div>
                                </div>
                            </td>
                        @endforeach
                        @for($i = count($row); $i < 3; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        @else
            <table>
                <tbody>
                    <tr>
                        <td>Belum ada siswa dengan status lulus SNBP pada filter yang dipilih.</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>

    <div class="note">
        PDF ini disusun sebagai ringkasan statistik plus daftar siswa yang sudah lulus SNBP agar tetap informatif,
        ringan, dan stabil saat dicetak. Untuk detail lengkap seluruh siswa, gunakan export Excel dari halaman admin lulusan.
    </div>
</body>
</html>
