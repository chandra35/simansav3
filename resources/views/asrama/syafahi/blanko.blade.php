<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blangko Ujian Syafahi - {{ $groupInfo['label'] }} - {{ $selectedKelas->nama_kelas }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Nunito:wght@400;600;700&display=swap">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm 12mm 15mm 12mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-arabic { font-family: 'Amiri', serif; direction: rtl; }

        /* Header */
        .kop {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
        .kop h2 {
            margin: 0;
            font-size: 15pt;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop h1 {
            margin: 3px 0;
            font-size: 17pt;
            font-weight: 700;
        }
        .kop h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: 600;
        }
        .kop-arabic {
            font-size: 18pt;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Meta Information Table */
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* Blanko Table */
        .score-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 15px;
        }
        .score-table th, .score-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
        }
        .score-table th {
            background: #e5e7eb;
            text-align: center;
            font-weight: bold;
        }
        .score-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .aspect-th {
            font-size: 10pt;
        }
        .aspect-arabic {
            font-size: 12pt;
            display: block;
        }

        /* Signatures */
        .sig-section {
            width: 100%;
            margin-top: 25px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            width: 45%;
            vertical-align: top;
        }
        .sig-space {
            height: 60px;
        }

        /* Toolbar */
        .toolbar {
            width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
        }
        .toolbar button, .toolbar a {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .toolbar a.btn-secondary {
            background: #475569;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 10mm 8mm 10mm 8mm;
            }
            .toolbar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div>
            <strong>Blangko Ujian Syafahi (Lisan)</strong> &mdash;
            Mode: <strong>{{ $mode === 'filled' ? 'Hasil Nilai Terisi' : 'Blangko Kosong Penilaian' }}</strong>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ request()->fullUrlWithQuery(['mode' => $mode === 'filled' ? 'empty' : 'filled']) }}" class="btn-secondary">
                {{ $mode === 'filled' ? 'Ganti ke Blangko Kosong' : 'Ganti ke Hasil Terisi' }}
            </a>
            <button onclick="window.print()">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                Cetak Blangko (A4)
            </button>
        </div>
    </div>

    <div class="page">
        <!-- Header -->
        <div class="kop">
            <h2>BLANGKO PENILAIAN UJIAN SYAFAHI (LISAN)</h2>
            <div class="kop-arabic font-arabic">{{ $groupInfo['label_arab'] }}</div>
            <h1>KELOMPOK {{ strtoupper($groupInfo['label']) }}</h1>
            <h3>ASRAMA MAN 1 METRO &mdash; TP. {{ $activeYear->nama ?? '-' }}</h3>
        </div>

        <!-- Meta Table -->
        <table class="meta-table">
            <tr>
                <td style="width: 15%; font-weight: bold;">Kelas / Rombel</td>
                <td style="width: 35%;">: {{ $selectedKelas->nama_kelas }} ({{ $selectedKelas->is_tahfidz ? 'Tahfizh' : 'Reguler' }})</td>
                <td style="width: 15%; font-weight: bold;">Semester</td>
                <td style="width: 35%;">: {{ $semester }} ({{ $semester === 'Ganjil' ? 'I / Satu' : 'II / Dua' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Gedung Asrama</td>
                <td>: {{ $selectedKelas->asrama->nama ?? '-' }}</td>
                <td style="font-weight: bold;">Tanggal Ujian</td>
                <td>: .....................................................</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Penguji / Asatidz</td>
                <td colspan="3">: ........................................................................................................................</td>
            </tr>
        </table>

        <!-- Scores / Assessment Table -->
        <table class="score-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 28px;">No</th>
                    <th rowspan="2" style="width: 80px;">NISN / No. Induk</th>
                    <th rowspan="2" class="text-left" style="min-width: 160px; padding-left: 8px;">Nama Lengkap Santri</th>
                    <th colspan="{{ count($aspectCodes) }}" class="aspect-th">
                        Materi / Aspek Ujian Syafahi (Skala 0 - 10)
                    </th>
                    <th rowspan="2" style="width: 55px;">Rata-rata</th>
                    <th rowspan="2" style="width: 120px;">Catatan / Keterangan</th>
                </tr>
                <tr>
                    @foreach($aspectCodes as $kode)
                        @php
                            $mapel = $mapels->get($kode);
                            $arabName = $mapel?->nama_arab ?? $kode;
                            $latinName = $mapel?->nama_latin ?? $kode;
                            if ($kode === 'ASR-023') {
                                $arabName = $selectedKelas->is_tahfidz ? 'تحسين القرآن' : 'الترجمة';
                                $latinName = $selectedKelas->is_tahfidz ? 'Tahsin Al-Qur\'an' : 'Tarjamah';
                            }
                        @endphp
                        <th style="min-width: 75px;">
                            <span class="aspect-arabic font-arabic">{{ $arabName }}</span>
                            <span style="font-size: 8pt; font-weight: normal; display: block;">{{ $latinName }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($members as $index => $member)
                    @php
                        $memberScores = $scores[$member->id] ?? [];
                        $rowVals = [];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $member->santri->nomor_induk_asrama ?: ($member->santri->siswa->nisn ?: '-') }}</td>
                        <td class="text-left" style="padding-left: 8px;">
                            <div class="font-bold">{{ $member->santri->siswa->nama_lengkap ?? '-' }}</div>
                            @if($member->santri->kamarSantriAktif?->kamar)
                                <div style="font-size: 8pt; color: #666;">Kamar: {{ $member->santri->kamarSantriAktif->kamar->nama }}</div>
                            @endif
                        </td>

                        @foreach($aspectCodes as $kode)
                            @php
                                $val = null;
                                if ($mode === 'filled') {
                                    $pengampu = $pengampuList[$kode] ?? null;
                                    if ($pengampu && isset($memberScores[$pengampu->id])) {
                                        $val = $memberScores[$pengampu->id]->nilai;
                                        if ($val !== null) {
                                            $rowVals[] = (float) $val;
                                        }
                                    }
                                }
                            @endphp
                            <td class="text-center" style="font-weight: bold; font-size: 11pt;">
                                {{ $val !== null ? number_format((float) $val, 1, ',', '.') : '' }}
                            </td>
                        @endforeach

                        @php
                            $rowAvg = count($rowVals) > 0 ? round(array_sum($rowVals) / count($rowVals), 1) : null;
                        @endphp
                        <td class="text-center font-bold" style="font-size: 11pt;">
                            {{ $rowAvg !== null ? number_format($rowAvg, 1, ',', '.') : '' }}
                        </td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($aspectCodes) + 5 }}" class="text-center" style="padding: 20px;">
                            Belum ada anggota santri pada kelas ini.
                        </td>
                    </tr>
                @endforelse

                @if($mode === 'empty' && $members->count() < 10)
                    {{-- Add blank rows for additional students if needed --}}
                    @for($i = $members->count() + 1; $i <= 15; $i++)
                        <tr>
                            <td class="text-center">{{ $i }}</td>
                            <td></td>
                            <td></td>
                            @foreach($aspectCodes as $k)
                                <td></td>
                            @endforeach
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <!-- Signatures Footer -->
        <table class="sig-section">
            <tr>
                <td class="sig-box">
                    Mengetahui,<br>
                    <strong>Koordinator Asrama MAN 1 Metro</strong>
                    <div class="sig-space"></div>
                    <strong><u>{{ $selectedKelas->asrama->pembina->nama ?? '...................................................' }}</u></strong><br>
                    NIP. {{ $selectedKelas->asrama->pembina->nip ?? '-' }}
                </td>
                <td style="width: 10%;"></td>
                <td class="sig-box">
                    Metro, ......................................... 20...<br>
                    <strong>Penguji / Pengampu Ujian Syafahi</strong>
                    <div class="sig-space"></div>
                    <strong><u>...................................................</u></strong><br>
                    NIP. ...............................................
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
