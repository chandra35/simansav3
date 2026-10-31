<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jurnal Guru - {{ $user->name }}</title>
    <style>
        @page {
            margin: 22px 26px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .school-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 2px 0 1px;
        }
        .doc-subtitle {
            font-size: 7.5px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-top: 6px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 8px;
        }
        .meta-label {
            color: #64748b;
            width: 90px;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 500;
        }
        .stats-table {
            width: 100%;
            margin: 8px 0;
            border-collapse: separate;
            border-spacing: 4px;
        }
        .stats-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 7px;
            text-align: center;
        }
        .stats-num {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            display: block;
        }
        .stats-desc {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
        }
        table.journal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.journal-table th {
            background: #eef2ff;
            color: #1e3a8a;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
            font-size: 8px;
        }
        table.journal-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 8px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; font-size: 7.5px; }
        .badge-type {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-mengajar { background: #e0f2fe; color: #0369a1; }
        .badge-wali { background: #fef3c7; color: #92400e; }
        .att-summary-box {
            background: #f8fafc;
            border-left: 2px solid #3b82f6;
            padding: 3px 5px;
            margin-top: 3px;
            border-radius: 2px;
            font-size: 7.5px;
        }
        .att-rate {
            font-weight: bold;
            color: #0f172a;
        }
        .att-perfect {
            color: #15803d;
            font-weight: bold;
        }
        .att-absent {
            color: #b91c1c;
            margin-top: 1px;
        }
        .signature-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 8px;
            line-height: 1.4;
        }
        .signature-space {
            height: 48px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="school-title">{{ $setting?->nama_sekolah ?: 'MAN 1 METRO' }}</div>
    <div class="doc-title">LAPORAN JURNAL KEGIATAN & PRESENSI PEMBELAJARAN GURU</div>
    <div class="doc-subtitle">Dokumen Bukti Kinerja Guru (EKIN / SKP) · Sistem Informasi Manajemen Madrasah</div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-val">: {{ $gtk?->nama_lengkap ?? $user->name }}</td>
            <td class="meta-label">Tahun Pelajaran</td>
            <td class="meta-val">: {{ $year?->nama ?? '-' }} (Semester {{ ucfirst($year?->semester_aktif ?? 'Ganjil') }})</td>
        </tr>
        <tr>
            <td class="meta-label">NIP / NPK</td>
            <td class="meta-val">: {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}</td>
            <td class="meta-label">Periode Laporan</td>
            <td class="meta-val">: {{ $dari->translatedFormat('d F Y') }} s.d. {{ $sampai->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jabatan / PTK</td>
            <td class="meta-val">: {{ $gtk?->jenis_ptk ?: ($gtk?->jabatan ?: 'Guru Mata Pelajaran') }}</td>
            <td class="meta-label">Waktu Cetak</td>
            <td class="meta-val">: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>
</div>

<table class="stats-table">
    <tr>
        <td class="stats-box" width="25%">
            <span class="stats-num">{{ $stats['total_entries'] }}</span>
            <span class="stats-desc">Total Entri Kegiatan</span>
        </td>
        <td class="stats-box" width="25%">
            <span class="stats-num">{{ $stats['total_hours'] }} Jam</span>
            <span class="stats-desc">Akumulasi Durasi Tatap Muka</span>
        </td>
        <td class="stats-box" width="25%">
            <span class="stats-num">{{ $stats['teaching_count'] }} Mengajar · {{ $stats['homeroom_count'] }} Wali</span>
            <span class="stats-desc">Rincian Peran</span>
        </td>
        <td class="stats-box" width="25%">
            <span class="stats-num">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '-' }}</span>
            <span class="stats-desc">Rerata Kehadiran Siswa</span>
        </td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr>
            <th width="20" class="text-center">No</th>
            <th width="56">Tanggal</th>
            <th width="65">Jenis / Durasi</th>
            <th width="105">Kelas & Mapel</th>
            <th>Uraian Topik & Kegiatan</th>
            <th width="115">Presensi Siswa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($journals as $index => $journal)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <span class="font-bold">{{ $journal->tanggal->format('d/m/Y') }}</span><br>
                    <span class="text-muted">{{ $journal->tanggal->translatedFormat('l') }}</span>
                </td>
                <td>
                    @if($journal->jenis === 'mengajar')
                        <span class="badge-type badge-mengajar">Mengajar</span>
                    @else
                        <span class="badge-type badge-wali">Wali Kelas</span>
                    @endif
                    @if($journal->durasi_menit)
                        <br><span class="text-muted">{{ $journal->durasi_menit }} menit</span>
                    @endif
                </td>
                <td>
                    <span class="font-bold">{{ $journal->kelas_snapshot ?: '-' }}</span>
                    @if($journal->mapel_snapshot)
                        <br><span class="text-muted">{{ $journal->mapel_snapshot }}</span>
                    @endif
                </td>
                <td>
                    <span class="font-bold">{{ $journal->topik }}</span><br>
                    {{ $journal->kegiatan }}
                    @if($journal->asesmen_tindak_lanjut)
                        <br><span class="text-muted"><strong>Asesmen/Tindak Lanjut:</strong> {{ $journal->asesmen_tindak_lanjut }}</span>
                    @endif
                    @if($journal->catatan)
                        <br><span class="text-muted"><em>Catatan: {{ $journal->catatan }}</em></span>
                    @endif
                </td>
                <td>
                    @if(!empty($journal->attendance_summary))
                        @php $att = $journal->attendance_summary; @endphp
                        <div class="att-summary-box">
                            <div class="att-rate">
                                Hadir: {{ $att['hadir'] ?? 0 }}/{{ $att['total'] ?? 0 }} ({{ $att['persentase'] ?? 100 }}%)
                            </div>
                            @if(!empty($att['absent_students']))
                                <div class="att-absent">
                                    <strong>Tidak Hadir ({{ count($att['absent_students']) }}):</strong><br>
                                    @foreach($att['absent_students'] as $absent)
                                        • {{ $absent['nama'] }} ({{ ucfirst($absent['status']) }}){{ !$loop->last ? '<br>' : '' }}
                                    @endforeach
                                </div>
                            @else
                                <div class="att-perfect">✓ Semua Hadir</div>
                            @endif
                        </div>
                    @elseif($journal->jenis === 'mengajar')
                        <span class="text-muted">Tidak tercatat</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 18px 0; color: #64748b;">
                    Tidak ada catatan jurnal kegiatan pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td>
            Mengetahui,<br>
            Kepala Madrasah
            <div class="signature-space"></div>
            <span class="signature-name">{{ $kepalaMadrasah?->nama_lengkap ?? 'H. Gunawan Susanto, S.Pd., M.Pd.' }}</span><br>
            NIP. {{ $kepalaMadrasah?->nip ?? '197702222005011004' }}
        </td>
        <td>
            {{ config('app.city', 'Metro') }}, {{ $sampai->translatedFormat('d F Y') }}<br>
            Guru Pengampu / Wali Kelas
            <div class="signature-space"></div>
            <span class="signature-name">{{ $gtk?->nama_lengkap ?? $user->name }}</span><br>
            NIP. {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}
        </td>
    </tr>
</table>

</body>
</html>