<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Analisis Kuis - {{ $archive->moodle_quiz_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .kop {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .kop td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 55px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            line-height: 1.2;
        }
        .kop-kemenag {
            font-size: 8.5pt;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-school {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0;
        }
        .kop-address {
            font-size: 7.5pt;
            color: #475569;
        }

        .title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 7.8pt;
            vertical-align: top;
        }
        .meta-label {
            width: 18%;
            color: #64748b;
            font-weight: 600;
        }
        .meta-val {
            width: 32%;
            color: #0f172a;
            font-weight: bold;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            border-left: 3px solid #2563eb;
            padding-left: 6px;
            margin: 12px 0 6px 0;
        }

        /* Stat cards grid simulation */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .stat-box {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .stat-val {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
        }
        .stat-lbl {
            font-size: 6.8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* Data table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 7.2pt;
        }
        .data-table th {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            text-align: center;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
        }
        .ttd-space { height: 45px; }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI MADRASAH -->
    <table class="kop">
        <tr>
            <td class="kop-text">
                <div class="kop-kemenag">Kementerian Agama Republik Indonesia</div>
                <div class="kop-school">Madrasah Aliyah Negeri 1 Metro</div>
                <div class="kop-address">Jl. Ki Hajar Dewantara No. 110, Iringmulyo, Kec. Metro Timur, Kota Metro, Lampung 34112</div>
            </td>
        </tr>
    </table>

    <div class="title-block">
        <h1 class="doc-title">Laporan Analisis Kuis CBT & Hasil Penilaian</h1>
        <div class="doc-subtitle">{{ $archive->moodle_quiz_name }}</div>
    </div>

    <!-- META DATA -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-val">: {{ $archive->mataPelajaran->nama ?? ($archive->moodle_course_name ?? '-') }}</td>
            <td class="meta-label">Tahun Pelajaran</td>
            <td class="meta-val">: {{ $archive->tahunPelajaran->nama ?? '-' }} (Semester {{ $archive->semester === 2 ? 'Genap' : 'Ganjil' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Guru Pengampu</td>
            <td class="meta-val">: {{ $archive->gtk->nama_lengkap ?? ($archive->user->name ?? '-') }}</td>
            <td class="meta-label">Kategori / Kursus</td>
            <td class="meta-val">: {{ $archive->moodle_category_name ?? '-' }} / {{ $archive->moodle_course_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kelas / Rombel</td>
            <td class="meta-val">: {{ $archive->kelas->nama_kelas ?? 'Semua Peserta Kuis' }}</td>
            <td class="meta-label">Status Arsip</td>
            <td class="meta-val">: 
                @if($archive->is_locked)
                    <span class="badge badge-success">Terkunci (Final)</span> pada {{ $archive->locked_at?->format('d/m/Y H:i') }}
                @else
                    <span class="badge badge-warning">Draft (Dapat Diperbarui)</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- RINGKASAN PSIKOMETRIK & STATISTIK -->
    <div class="section-title">1. Ringkasan Statistik & Psikometrik Kuis</div>
    <table class="stats-table">
        <tr>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ $stats['attempts_count'] ?? count($scores) }}</div>
                    <div class="stat-lbl">Peserta</div>
                </div>
            </td>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($stats['avg_grade'] ?? $scores->avg('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Rata-rata</div>
                </div>
            </td>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($scores->max('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Tertinggi</div>
                </div>
            </td>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($scores->min('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Terendah</div>
                </div>
            </td>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($stats['standard_deviation'] ?? 0, 2) }}</div>
                    <div class="stat-lbl">Std. Deviasi</div>
                </div>
            </td>
            <td style="width: 16.6%; padding: 2px;">
                <div class="stat-box">
                    <div class="stat-val">{{ isset($stats['cronbach_alpha']) ? $stats['cronbach_alpha'].'%' : '-' }}</div>
                    <div class="stat-lbl">Reliabilitas (Alpha)</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ANALISIS BUTIR SOAL -->
    <div class="section-title">2. Analisis Butir Soal (Item Psychometrics)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama / Indikator Soal</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 10%;">Tingkat Kesukaran (P)</th>
                <th style="width: 12%;">Klasifikasi Kesukaran</th>
                <th style="width: 10%;">Daya Pembeda (D)</th>
                <th style="width: 12%;">Klasifikasi Pembeda</th>
                <th style="width: 15%;">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
                <tr>
                    <td class="text-center font-bold">{{ $it['slot'] ?? ($loop->iteration) }}</td>
                    <td>{{ $it['name'] ?? ('Soal #' . ($it['slot'] ?? $loop->iteration)) }}</td>
                    <td class="text-center">{{ strtoupper($it['type'] ?? 'MC') }}</td>
                    <td class="text-center font-bold">{{ number_format($it['facility_percent'] ?? 0, 1) }}%</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $it['kesukaran_badge'] ?? 'secondary' }}">
                            {{ $it['kesukaran'] ?? '-' }}
                        </span>
                    </td>
                    <td class="text-center font-bold">
                        {{ isset($it['discrimination_index']) ? number_format($it['discrimination_index'], 1) : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $it['pembeda_badge'] ?? 'secondary' }}">
                            {{ $it['pembeda'] ?? '-' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $it['status_badge'] ?? 'secondary' }}">
                            {{ $it['status'] ?? '-' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Belum ada data butir soal yang dianalisis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PAGE BREAK UNTUK DAFTAR NILAI SISWA -->
    <div class="page-break"></div>

    <div class="title-block">
        <h2 class="doc-title" style="font-size: 10pt;">Daftar Hasil Nilai Siswa</h2>
        <div class="doc-subtitle">{{ $archive->moodle_quiz_name }} &bull; Rombel: {{ $archive->kelas->nama_kelas ?? 'Semua Peserta' }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">Rank</th>
                <th style="width: 18%;">NISN / Username</th>
                <th>Nama Lengkap Siswa</th>
                <th style="width: 16%;">Kelas / Rombel</th>
                <th style="width: 12%;">Nilai (100)</th>
                <th style="width: 14%;">Waktu Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scores as $idx => $sc)
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $sc->moodle_username ?? '-' }}</td>
                    <td class="font-bold">{{ $sc->nama_siswa }}</td>
                    <td class="text-center">{{ $sc->kelas_nama ?? '-' }}</td>
                    <td class="text-center font-bold" style="font-size: 8pt; color: {{ $sc->grade >= 75 ? '#166534' : '#991b1b' }};">
                        {{ number_format($sc->grade, 1) }}
                    </td>
                    <td class="text-center">{{ $sc->time_finished ? $sc->time_finished->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada data siswa yang terekam.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN / PENGESAHAN -->
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah
                <div class="ttd-space"></div>
                <strong>H. Sarwo Pramono, M.Pd.</strong><br>
                NIP. 197003181997031002
            </td>
            <td>
                Metro, {{ date('d F Y') }}<br>
                Guru Mata Pelajaran
                <div class="ttd-space"></div>
                <strong>{{ $archive->gtk->nama_lengkap ?? ($archive->user->name ?? '..................................') }}</strong><br>
                NIP. {{ $archive->gtk->nip ?? '..................................' }}
            </td>
        </tr>
    </table>

</body>
</html>
