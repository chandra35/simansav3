<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Monitoring Jurnal Guru</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7.2pt;
            color: #1e293b;
            line-height: 1.28;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .school-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 6.8pt;
            color: #475569;
        }
        .doc-period {
            font-size: 7pt;
            color: #334155;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 6.8pt;
        }
        .meta-label {
            width: 90px;
            font-weight: 600;
            color: #475569;
        }
        .meta-sep {
            width: 10px;
            text-align: center;
            color: #64748b;
        }
        .meta-val {
            color: #0f172a;
        }
        .summary-table {
            width: 100%;
            margin: 0 0 6px 0;
            border-collapse: collapse;
        }
        .summary-table td {
            border: 1px solid #94a3b8;
            padding: 4px 8px;
            text-align: center;
            background: #f8fafc;
        }
        .summary-num {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            display: block;
        }
        .summary-lbl {
            font-size: 6.2pt;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #94a3b8;
            padding: 4.5px 5px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 6.8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
            vertical-align: middle;
        }
        .data-table td {
            vertical-align: middle;
        }
        .col-no {
            width: 25px;
            text-align: center;
        }
        .col-teacher {
            width: 190px;
        }
        .col-role {
            width: 110px;
            text-align: center;
        }
        .col-stat {
            width: 55px;
            text-align: center;
        }
        .col-hours {
            width: 60px;
            text-align: center;
        }
        .col-att {
            width: 65px;
            text-align: center;
        }
        .col-date {
            width: 75px;
            text-align: center;
        }
        .col-status {
            width: 75px;
            text-align: center;
        }
        .teacher-name {
            font-weight: bold;
            color: #0f172a;
        }
        .teacher-nip {
            font-size: 6.2pt;
            color: #64748b;
        }
        .badge-active {
            font-weight: bold;
            color: #0f172a;
        }
        .badge-empty {
            color: #64748b;
            font-style: italic;
        }
        .footer-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .footer-table td {
            width: 50%;
            vertical-align: top;
            font-size: 7pt;
        }
        .sign-title {
            color: #475569;
            margin-bottom: 2px;
        }
        .sign-role {
            font-weight: 600;
            color: #0f172a;
        }
        .sign-space {
            height: 44px;
        }
        .sign-name {
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .sign-nip {
            color: #475569;
            font-size: 6.5pt;
        }
    </style>
</head>
<body>
    {{-- Header / Kop Surat --}}
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div class="school-name">{{ $setting->nama_madrasah ?? 'MAN 1 KOTA BANDUNG' }}</div>
                <div class="doc-title">Rekapitulasi Monitoring Jurnal Pembelajaran Guru</div>
                <div class="doc-subtitle">Pusat Pengawasan dan Evaluasi Dokumen Mengajar & Tugas Wali Kelas (EKIN / SKP)</div>
                <div class="doc-period">
                    Periode: <strong>{{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</strong>
                </div>
            </td>
            <td style="width: 42%;">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Tahun Pelajaran</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val"><strong>{{ $year->nama ?? '-' }}</strong> (Semester {{ $year->semester_aktif ?? '-' }})</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Total Guru Dipantau</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">{{ $stats['total_teachers'] }} Orang</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tingkat Partisipasi</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val"><strong>{{ $stats['participation_rate'] }}%</strong> ({{ $stats['active_teachers'] }} dari {{ $stats['total_teachers'] }} Guru)</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal Cetak</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Executive Summary KPI --}}
    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-num">{{ $stats['total_teachers'] }}</span>
                <span class="summary-lbl">Total Guru Terdata</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['active_teachers'] }}</span>
                <span class="summary-lbl">Guru Sudah Mengisi</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['inactive_teachers'] }}</span>
                <span class="summary-lbl">Guru Belum Mengisi</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['total_entries'] }}</span>
                <span class="summary-lbl">Total Sesi Jurnal</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['total_teaching'] }} / {{ $stats['total_homeroom'] }}</span>
                <span class="summary-lbl">Mengajar / Wali Kelas</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['total_hours'] }} Jam</span>
                <span class="summary-lbl">Total Jam Kegiatan</span>
            </td>
            <td>
                <span class="summary-num">{{ $stats['overall_avg_attendance'] ? $stats['overall_avg_attendance'] . '%' : '-' }}</span>
                <span class="summary-lbl">Rerata Presensi Siswa</span>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-teacher">Nama Guru & NIP</th>
                <th class="col-role">Jabatan / Mapel</th>
                <th class="col-stat">Mengajar</th>
                <th class="col-stat">Wali Kelas</th>
                <th class="col-stat">Total Sesi</th>
                <th class="col-hours">Total Jam</th>
                <th class="col-att">Rerata Hadir</th>
                <th class="col-date">Jurnal Terakhir</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-teacher">
                        <div class="teacher-name">{{ $r['nama'] }}</div>
                        <div class="teacher-nip">NIP: {{ $r['nip'] }}</div>
                    </td>
                    <td class="col-role">
                        <div>{{ $r['jabatan'] }}</div>
                        @if($r['subjects']->isNotEmpty())
                            <div class="teacher-nip">{{ $r['subjects']->take(2)->join(', ') }}</div>
                        @endif
                    </td>
                    <td class="col-stat">{{ $r['teaching_count'] }}</td>
                    <td class="col-stat">{{ $r['homeroom_count'] }}</td>
                    <td class="col-stat" style="font-weight: bold;">{{ $r['total_journals'] }}</td>
                    <td class="col-hours">{{ $r['total_hours'] }} Jam</td>
                    <td class="col-att">
                        {{ $r['avg_attendance'] ? $r['avg_attendance'] . '%' : '-' }}
                    </td>
                    <td class="col-date">
                        {{ $r['last_journal_date'] ? \Carbon\Carbon::parse($r['last_journal_date'])->format('d/m/Y') : '-' }}
                    </td>
                    <td class="col-status">
                        @if($r['has_journals'])
                            <span class="badge-active">Sudah Mengisi</span>
                        @else
                            <span class="badge-empty">Belum Mengisi</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 12px; color: #64748b;">
                        Tidak ada data guru yang memenuhi kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Official Signatures --}}
    <table class="footer-table">
        <tr>
            <td style="padding-left: 20px;">
                <div class="sign-title">Diperiksa & Dicetak oleh:</div>
                <div class="sign-role">Petugas Monitoring / Administrator</div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ auth()->user()->name ?? 'Administrator SIMANSA' }}</div>
                <div class="sign-nip">NIP: {{ auth()->user()->gtk?->nip ?? '-' }}</div>
            </td>
            <td style="text-align: right; padding-right: 20px;">
                <div class="sign-title">{{ $setting->kabupaten ?? 'Bandung' }}, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="sign-role">Mengetahui, Kepala Madrasah</div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $kepalaMadrasah?->nama_lengkap ?? ($setting->nama_kepala_madrasah ?? 'Kepala Madrasah') }}</div>
                <div class="sign-nip">NIP: {{ $kepalaMadrasah?->nip ?? ($setting->nip_kepala_madrasah ?? '-') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
