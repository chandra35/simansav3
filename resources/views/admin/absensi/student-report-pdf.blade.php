<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 20px 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #172033; }
        h1 { font-size: 17px; margin: 0; }
        h2 { font-size: 12px; color: #3656a8; margin: 3px 0; }
        .meta { margin: 7px 0 10px; }
        .meta td { padding: 2px 12px 2px 0; }
        .muted { color: #64748b; }
        .summary { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        .summary td { border: 1px solid #dbe4f0; text-align: center; padding: 4px; background: #f8fafc; }
        .summary b { display: block; font-size: 11px; }
        .summary span { font-size: 7px; color: #64748b; }
        .data { width: 100%; border-collapse: collapse; }
        .data th { background: #eaf0ff; color: #304a88; font-size: 7px; text-transform: uppercase; }
        .data th, .data td { border: 1px solid #dbe4f0; padding: 3px; }
        .center { text-align: center; }
        .name { font-weight: bold; }
        .day { width: 19px; text-align: center; padding: 2px 1px !important; }
        .status { text-align: center; font-weight: bold; }
        .hadir { color: #16803c; }
        .terlambat { color: #a16207; }
        .izin, .sakit, .dispen { color: #2563a9; }
        .alpa { color: #b42318; }
        .keluar_awal { color: #6d28d9; }
        .student-risk-red td { background: #fff5f5; }
        .student-risk-orange td { background: #fff8ed; }
        .student-risk-yellow td { background: #fffdea; }
        .count-danger { color: #b42318; background: #fee4e2 !important; font-weight: bold; }
        .count-warning { color: #b54708; background: #ffead5 !important; font-weight: bold; }
        .count-caution { color: #854d0e; background: #fef3c7 !important; font-weight: bold; }
        .notes-section { margin-top: 10px; page-break-inside: avoid; }
        .notes-title { background: #f5f7ff; border-left: 3px solid #4f46e5; color: #263b78; font-size: 9px; font-weight: bold; padding: 4px 6px; }
        .notes-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .notes-table th { background: #f8fafc; color: #64748b; font-size: 7px; text-transform: uppercase; }
        .notes-table th, .notes-table td { border: 1px solid #dbe4f0; padding: 3px 4px; vertical-align: top; }
        .note-type { color: #3656a8; font-weight: bold; white-space: nowrap; }
        .note-empty { color: #64748b; font-style: italic; }
        .footer { margin-top: 7px; color: #64748b; }
        .class-block { page-break-after: always; }
        .class-block:last-child { page-break-after: auto; }
        .legend { margin-top: 6px; color: #64748b; }
    </style>
</head>
<body>
@foreach ($reports as $report)
    <div class="class-block">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <h2>
            {{ $report['kelas']->nama_kelas }}{{ $report['kelas']->asrama_suffix }}
            &middot;
            @if ($periode === 'bulan')
                Rekap Detail Bulanan
            @else
                Laporan Harian
            @endif
        </h2>

        <table class="meta">
            <tr>
                <td class="muted">Tahun pelajaran</td>
                <td>{{ $year->nama }}</td>
                <td class="muted">Wali kelas</td>
                <td>{{ optional($report['kelas']->waliKelas)->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="muted">Periode</td>
                <td colspan="3">
                    @if ($periode === 'bulan')
                        {{ $start->translatedFormat('F Y') }}
                    @else
                        {{ $start->translatedFormat('l, d F Y') }}
                    @endif
                    &middot; Dicetak {{ now()->format('d/m/Y H:i') }} WIB
                </td>
            </tr>
        </table>

        <table class="summary">
            <tr>
            @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa', 'dispen' => 'Dispen', 'keluar_awal' => 'Keluar awal'] as $key => $label)
                <td>
                    <b>{{ $report['totals'][$key] ?? 0 }}</b>
                    <span>{{ $label }}</span>
                </td>
            @endforeach
            </tr>
        </table>

        @if ($periode === 'hari')
            <table class="data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama siswa</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($report['students'] as $i => $student)
                    @php
                        $record = $report['records']->get($start->toDateString() . '|' . $student->id);
                        $recordStatus = $record ? $record->status : null;
                    @endphp
                    <tr>
                        <td class="center">{{ $student->pivot->nomor_urut_absen ?? ($i + 1) }}</td>
                        <td>{{ $student->nisn ?: '-' }}</td>
                        <td class="name">{{ $student->nama_lengkap }}</td>
                        <td class="status {{ $recordStatus }}">
                            @if ($record)
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            @else
                                Belum diisi
                            @endif
                        </td>
                        <td>{{ $record && $record->notes ? $record->notes : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama / NISN</th>
                    @foreach ($dates as $date)
                        <th class="day">{{ $date->format('d') }}<br>{{ $date->translatedFormat('D') }}</th>
                    @endforeach
                        <th>H</th><th>T</th><th>I</th><th>S</th><th>A</th><th>D</th><th>K</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($report['students'] as $i => $student)
                    @php
                        $counts = $report['summary']->get($student->id);
                        $izinAlpa = ($counts['izin'] ?? 0) + ($counts['alpa'] ?? 0);
                        // "Banyak" means three or more occurrences in the selected month.
                        $rowTone = $izinAlpa >= 3 ? 'student-risk-red' : (($counts['sakit'] ?? 0) >= 3 ? 'student-risk-orange' : (($counts['dispen'] ?? 0) >= 3 ? 'student-risk-yellow' : ''));
                    @endphp
                    <tr class="{{ $rowTone }}">
                        <td class="center">{{ $student->pivot->nomor_urut_absen ?? ($i + 1) }}</td>
                        <td class="name">{{ $student->nama_lengkap }}<br><span class="muted">{{ $student->nisn ?: '-' }}</span></td>
                    @foreach ($dates as $date)
                        @php
                            $record = $report['records']->get($date->toDateString() . '|' . $student->id);
                            $recordStatus = $record ? $record->status : null;
                            $recordCode = $record ? ($record->status === 'keluar_awal' ? 'K' : strtoupper(substr($record->status, 0, 1))) : '&middot;';
                        @endphp
                        <td class="status {{ $recordStatus }}">{!! $recordCode !!}</td>
                    @endforeach
                    @foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dispen', 'keluar_awal'] as $status)
                        @php
                            $countClass = match ($status) {
                                'izin', 'alpa' => $izinAlpa >= 3 ? 'count-danger' : '',
                                'sakit' => ($counts[$status] ?? 0) >= 3 ? 'count-warning' : '',
                                'dispen' => ($counts[$status] ?? 0) >= 3 ? 'count-caution' : '',
                                default => '',
                            };
                        @endphp
                        <td class="center {{ $countClass }}">{{ $counts[$status] ?? 0 }}</td>
                    @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="legend">
                <b>Keterangan:</b> H Hadir &middot; T Terlambat &middot; I Izin &middot; S Sakit &middot; A Alpa
                &middot; D Dispen &middot; K Keluar awal &middot; &middot; Belum ada absensi.
                <br><b>Penanda perhatian:</b> 3+ Izin/Alpa <span class="count-danger">merah</span>
                &middot; 3+ Sakit <span class="count-warning">oranye</span>
                &middot; 3+ Dispen <span class="count-caution">kuning</span>.
            </div>
        @endif

        @if ($report['studentNotes']->isNotEmpty())
            <div class="notes-section">
                <div class="notes-title">CATATAN SISWA PADA PERIODE LAPORAN</div>
                <table class="notes-table">
                    <thead>
                        <tr>
                            <th style="width: 4%">No</th>
                            <th style="width: 24%">Siswa</th>
                            <th style="width: 14%">Tanggal / Sumber</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($report['studentNotes'] as $noteIndex => $studentNote)
                        @foreach ($studentNote['items'] as $itemIndex => $item)
                            <tr>
                                @if ($itemIndex === 0)
                                    <td class="center" rowspan="{{ $studentNote['items']->count() }}">{{ $noteIndex + 1 }}</td>
                                    <td rowspan="{{ $studentNote['items']->count() }}"><b>{{ $studentNote['student']->nama_lengkap }}</b><br><span class="muted">{{ $studentNote['student']->nisn ?: '-' }}</span></td>
                                @endif
                                <td><span class="note-type">{{ $item['type'] }}</span><br><span class="muted">{{ $item['date'] ?: '-' }}</span></td>
                                <td>{{ $item['text'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
                <div class="legend">Catatan BK yang dicetak hanya pemberitahuan yang memang dibagikan kepada guru. Rincian konseling rahasia tidak ditampilkan.</div>
            </div>
        @endif

        <div class="footer">Dokumen resmi SIMANSA &middot; Data dibatasi sesuai hak akses laporan admin.</div>
    </div>
@endforeach
</body>
</html>
