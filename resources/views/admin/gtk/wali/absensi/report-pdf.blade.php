<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi {{ $kelas->nama_kelas }}</title>
    <style>
        @page { margin: 24px 22px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color:#172033; font-size:9px; }
        h1 { margin:0; font-size:18px; letter-spacing:.2px; }
        h2 { margin:3px 0 0; font-size:12px; color:#3656a8; }
        .muted { color:#64748b; }
        .header { border-bottom:2px solid #4568d4; padding-bottom:9px; margin-bottom:10px; }
        .meta { width:100%; margin-top:7px; }
        .meta td { padding:2px 0; }
        .meta td:first-child { width:95px; color:#64748b; }
        .summary { width:100%; border-collapse:collapse; margin:8px 0 12px; }
        .summary td { width:{{ 100 / count($totals) }}%; padding:6px 4px; text-align:center; border:1px solid #dbe4f0; background:#f8fafc; }
        .summary strong { display:block; font-size:13px; color:#172033; }
        .summary span { color:#64748b; text-transform:uppercase; font-size:7px; }
        table.data { width:100%; border-collapse:collapse; }
        .data th { background:#eaf0ff; color:#304a88; font-size:7px; text-transform:uppercase; padding:5px 4px; border:1px solid #cbd7ec; }
        .data td { padding:4px; border:1px solid #dbe4f0; vertical-align:middle; }
        .data tr:nth-child(even) td { background:#fbfdff; }
        .center { text-align:center; }
        .name { font-weight:bold; }
        .status { font-weight:bold; text-align:center; }
        .status-hadir { color:#16803c; background:#ecfdf3 !important; }
        .status-terlambat { color:#a16207; background:#fff8e7 !important; }
        .status-izin,.status-sakit,.status-dispen { color:#2563a9; background:#eff6ff !important; }
        .status-alpa { color:#b42318; background:#fff1f2 !important; }
        .status-keluar_awal { color:#6d28d9; background:#f5f3ff !important; }
        .day { width:21px; padding:3px 1px !important; font-size:7px !important; }
        .weekend { background:#f1f5f9 !important; color:#94a3b8 !important; }
        .note { margin-top:12px; border:1px solid #dbe4f0; padding:7px; }
        .note-title { font-weight:bold; color:#3656a8; margin-bottom:4px; }
        .footer { margin-top:12px; font-size:8px; color:#64748b; }
        .legend { margin-top:8px; color:#64748b; font-size:8px; }
        .legend b { color:#172033; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h2>{{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }} · {{ $periode === 'bulan' ? 'Rekap Detail Bulanan' : 'Laporan Harian' }}</h2>
        <table class="meta">
            <tr><td>Tahun pelajaran</td><td>{{ $tahun?->nama ?? '-' }}</td></tr>
            <tr><td>Wali kelas</td><td>{{ $kelas->waliKelas?->name ?? '-' }}</td></tr>
            <tr><td>Periode</td><td>{{ $periode === 'bulan' ? $start->translatedFormat('F Y') : $start->translatedFormat('l, d F Y') }}</td></tr>
            <tr><td>Dicetak</td><td>{{ now()->translatedFormat('d F Y H:i') }} WIB</td></tr>
        </table>
    </div>

    <table class="summary"><tr>
        @foreach(['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa','dispen'=>'Dispen','keluar_awal'=>'Keluar awal'] as $key => $label)
            <td><strong>{{ $totals[$key] ?? 0 }}</strong><span>{{ $label }}</span></td>
        @endforeach
    </tr></table>

    @if($periode === 'hari')
        @php($dateKey = $start->toDateString())
        <table class="data">
            <thead><tr><th style="width:28px">No</th><th>NISN</th><th>Nama siswa</th><th style="width:90px">Status</th><th>Catatan</th></tr></thead>
            <tbody>
            @foreach($students as $i => $student)
                @php($record = $recordsByDate->get($dateKey.'|'.$student->id))
                <tr><td class="center">{{ $student->pivot->nomor_urut_absen ?? $i + 1 }}</td><td>{{ $student->nisn ?: '-' }}</td><td class="name">{{ $student->nama_lengkap }}</td><td class="status status-{{ $record?->status ?? 'kosong' }}">{{ $record ? ucfirst(str_replace('_',' ',$record->status)) : 'Belum diisi' }}</td><td>{{ $record?->notes ?: '-' }}</td></tr>
            @endforeach
            </tbody>
        </table>
        <div class="footer">Status sesi: <b>{{ $sessionByDate->get($dateKey)?->status ? ucfirst($sessionByDate->get($dateKey)->status) : 'Belum ada sesi' }}</b>. Catatan siswa ditampilkan jika tersedia.</div>
    @else
        <table class="data">
            <thead><tr><th style="width:24px">No</th><th style="width:150px">Nama siswa</th>@foreach($dates as $date)<th class="day {{ $date->isWeekend() ? 'weekend' : '' }}">{{ $date->format('d') }}<br>{{ $date->translatedFormat('D') }}</th>@endforeach<th class="day">H</th><th class="day">T</th><th class="day">I</th><th class="day">S</th><th class="day">A</th><th class="day">D</th><th class="day">K</th></tr></thead>
            <tbody>
            @foreach($students as $i => $student)
                @php($counts = $summary->get($student->id))
                <tr><td class="center">{{ $student->pivot->nomor_urut_absen ?? $i + 1 }}</td><td class="name">{{ $student->nama_lengkap }}<br><span class="muted">{{ $student->nisn ?: '-' }}</span></td>
                    @foreach($dates as $date)
                        @php($record = $recordsByDate->get($date->toDateString().'|'.$student->id))
                        <td class="status status-{{ $record?->status ?? 'kosong' }} {{ $date->isWeekend() ? 'weekend' : '' }}">{{ $record ? strtoupper(substr($record->status === 'keluar_awal' ? 'K' : $record->status,0,1)) : '·' }}</td>
                    @endforeach
                    @foreach(['hadir','terlambat','izin','sakit','alpa','dispen','keluar_awal'] as $status)<td class="center">{{ $counts[$status] ?? 0 }}</td>@endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="legend"><b>Keterangan:</b> H Hadir · T Terlambat · I Izin · S Sakit · A Alpa · D Dispen · K Keluar awal · · Belum ada catatan absensi.</div>
        @php($notes = $recordsByDate->filter(fn($record) => filled($record->notes))->sortByDesc(fn($record) => $record->checked_at))
        @if($notes->isNotEmpty())
            <div class="note"><div class="note-title">Catatan kehadiran</div>@foreach($notes as $record)<div>• {{ $students->firstWhere('id',$record->siswa_id)?->nama_lengkap ?? 'Siswa' }}: {{ $record->notes }}</div>@endforeach</div>
        @endif
        <div class="footer">Total hari dalam periode: {{ $dates->count() }} · Hari dengan sesi tercatat: {{ $sessionByDate->count() }} · Nilai diambil dari absensi harian kelas wali.</div>
    @endif
</body>
</html>
