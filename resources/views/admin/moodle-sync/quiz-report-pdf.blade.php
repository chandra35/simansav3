<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Analisis Kuis - {{ $archive->moodle_quiz_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 16mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7.6pt;
            color: #1e293b;
            line-height: 1.35;
        }

        /* SIMANSA FOOTER TAG (FIXED ON EVERY PAGE) */
        .footer-tag {
            position: fixed;
            bottom: -11mm;
            left: 0;
            right: 0;
            height: 9mm;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
            font-size: 6.5pt;
            color: #64748b;
        }
        .footer-tag table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .footer-tag td {
            border: none !important;
            padding: 0 !important;
            vertical-align: middle;
        }
        .footer-tag .page-num:before {
            content: "Halaman " counter(page);
        }

        /* KOP SURAT RESMI MADRASAH (DUAL LOGO) */
        .kop {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop td {
            vertical-align: middle;
            border: none !important;
            padding: 0;
        }
        .kop-logo-left {
            width: 70px;
            text-align: left;
            padding-right: 6px !important;
        }
        .kop-logo-right {
            width: 70px;
            text-align: right;
            padding-left: 6px !important;
        }
        .logo-img {
            max-height: 68px;
            max-width: 68px;
            height: auto;
            width: auto;
        }
        .kop-text {
            text-align: center;
            line-height: 1.25;
        }
        .kop-line1 {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-line2 {
            font-size: 8pt;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 1px;
        }
        .kop-school {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .kop-identity {
            font-size: 7.2pt;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.3px;
        }
        .kop-address {
            font-size: 7pt;
            color: #475569;
            margin-top: 1px;
        }
        .kop-contact {
            font-size: 6.8pt;
            color: #64748b;
            font-style: italic;
            margin-top: 1px;
        }
        .kop-divider {
            margin-bottom: 10px;
        }
        .kop-line-thick {
            border-top: 2.2px solid #0f172a;
        }
        .kop-line-thin {
            border-top: 0.8px solid #0f172a;
            margin-top: 1.5px;
        }

        /* TITLE BLOCK */
        .title-block {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 1.5px 8px;
            border-radius: 10px;
            font-size: 6.8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .doc-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 8.2pt;
            color: #475569;
            margin-top: 2px;
            font-weight: 600;
        }

        /* METADATA TABLE */
        .meta-card {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .meta-card td {
            padding: 4px 7px;
            font-size: 7.4pt;
            vertical-align: middle;
            border: none;
        }
        .meta-label {
            width: 16%;
            color: #64748b;
            font-weight: 600;
        }
        .meta-sep {
            width: 2%;
            color: #94a3b8;
            text-align: center;
        }
        .meta-val {
            width: 32%;
            color: #0f172a;
            font-weight: bold;
        }

        /* SECTION HEADER */
        .section-header {
            font-size: 8.6pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-left: 3.5px solid #2563eb;
            padding-left: 6px;
            margin: 10px 0 6px 0;
        }

        /* STATS CARDS */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .stats-table td {
            border: none;
            padding: 0 3px;
        }
        .stat-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: 2.5px solid #2563eb;
            border-radius: 4px;
            padding: 5px 4px;
            text-align: center;
        }
        .stat-val {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-lbl {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
            margin-top: 1px;
        }

        /* DISTRIBUTION SUMMARY TABLE */
        .dist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .dist-table td {
            padding: 4px 6px;
            font-size: 7.2pt;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .dist-header {
            background: #f1f5f9;
            font-weight: bold;
            color: #334155;
            font-size: 6.8pt;
            text-transform: uppercase;
        }

        /* DATA TABLE (ITEM ANALYSIS & SCORES) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 7.1pt;
            vertical-align: middle;
        }
        .data-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            text-align: center;
            font-size: 6.9pt;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .data-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        /* UTILITIES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.4pt;
            font-weight: bold;
            text-align: center;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        /* TANDA TANGAN / PENGESAHAN */
        .ttd-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 7.8pt;
            border: none;
            padding: 0 15px;
        }
        .ttd-space { height: 45px; }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- SIMANSA FOOTER TAG (FIXED ON EVERY PAGE) -->
    <div class="footer-tag">
        <table>
            <tr>
                <td style="text-align: left; width: 80%;">
                    <strong style="color: #0f172a;">SIMANSA CBT ANALYTICS</strong> &bull; Sistem Informasi Madrasah Terpadu MAN 1 Metro
                    <div style="font-size: 5.8pt; color: #94a3b8; margin-top: 1px;">
                        Dokumen resmi yang digenerate otomatis dari arsip kuis e-Learning Moodle &bull; Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
                    </div>
                </td>
                <td style="text-align: right; width: 20%; white-space: nowrap;">
                    <span class="page-num" style="font-weight: bold; color: #334155;"></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- KOP RESMI MADRASAH (DUAL LOGO) -->
    <table class="kop">
        <tr>
            <td class="kop-logo-left">
                @if(!empty($logoKemenagDataUri))
                    <img src="{{ $logoKemenagDataUri }}" alt="Logo Kemenag" class="logo-img">
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-line1">{{ $appSetting?->kop_header_lines[0] ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA' }}</div>
                <div class="kop-line2">{{ $appSetting?->kop_header_lines[1] ?? 'KANTOR KEMENTERIAN AGAMA KOTA METRO' }}</div>
                <div class="kop-school">{{ $appSetting?->kop_header_lines[2] ?? ($appSetting?->nama_sekolah ?? 'MADRASAH ALIYAH NEGERI 1 METRO') }}</div>
                <div class="kop-identity">{{ $appSetting?->kop_identitas ?? 'NSM: 131118720001 NPSN: 10648374 AKREDITAS: A' }}</div>
                <div class="kop-address">
                    {{ $appSetting?->kop_alamat ?? ($appSetting?->alamat_lengkap ?? 'Jl. Ki Hajar Dewantara No. 110, Iringmulyo, Metro Timur, Kota Metro, Lampung 34112') }}
                </div>
                <div class="kop-contact">
                    Website: {{ $appSetting?->website ?? 'https://man1metro.sch.id' }} &bull; E-mail: {{ $appSetting?->email ?? 'man1kotametro@gmail.com' }}
                </div>
            </td>
            <td class="kop-logo-right">
                @if(!empty($logoSekolahDataUri))
                    <img src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <div class="kop-divider">
        <div class="kop-line-thick"></div>
        <div class="kop-line-thin"></div>
    </div>

    <!-- TITLE BLOCK -->
    <div class="title-block">
        <div class="doc-badge">DOKUMEN RESMI ASESMEN CBT</div>
        <h1 class="doc-title">Laporan Analisis Kuis & Rekapitulasi Nilai</h1>
        <div class="doc-subtitle">{{ $archive->moodle_quiz_name }}</div>
    </div>

    <!-- METADATA CARD -->
    <table class="meta-card">
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $archive->mataPelajaran->nama_mapel ?? ($archive->moodle_course_name ?? '-') }}</td>
            <td class="meta-label">Tahun Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $archive->tahunPelajaran->nama ?? ($archive->tahunPelajaran->tahun_ajaran ?? '-') }} (Semester {{ $archive->semester === 2 ? 'Genap' : 'Ganjil' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Guru Pengampu</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $gtk?->nama_lengkap ?? $archive->gtk?->nama_lengkap ?? ($archive->user?->name ?? '-') }}</td>
            <td class="meta-label">Kursus & Kategori</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $archive->moodle_course_name ?? '-' }} ({{ $archive->moodle_category_name ?? '-' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Kelas / Rombel</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $archive->kelas->nama_kelas ?? 'Semua Peserta Kuis' }}</td>
            <td class="meta-label">Status Arsip</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">
                @if($archive->is_locked)
                    <span class="badge badge-success">Terkunci (Final)</span> pada {{ $archive->locked_at?->format('d/m/Y H:i') }}
                @else
                    <span class="badge badge-warning">Draft (Dapat Diperbarui)</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- RINGKASAN PSIKOMETRIK & STATISTIK -->
    <div class="section-header">1. Ringkasan Statistik & Psikometrik Kuis</div>
    <table class="stats-table">
        <tr>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ $stats['attempts_count'] ?? count($scores) }}</div>
                    <div class="stat-lbl">Peserta</div>
                </div>
            </td>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($stats['avg_grade'] ?? $scores->avg('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Rata-rata</div>
                </div>
            </td>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($scores->max('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Tertinggi</div>
                </div>
            </td>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($scores->min('grade') ?? 0, 1) }}</div>
                    <div class="stat-lbl">Terendah</div>
                </div>
            </td>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ number_format($stats['standard_deviation'] ?? 0, 2) }}</div>
                    <div class="stat-lbl">Std. Deviasi</div>
                </div>
            </td>
            <td style="width: 16.6%;">
                <div class="stat-box">
                    <div class="stat-val">{{ isset($stats['cronbach_alpha']) ? $stats['cronbach_alpha'].'%' : '-' }}</div>
                    <div class="stat-lbl">Reliabilitas (&alpha;)</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- DISTRIBUSI HASIL NILAI SISWA -->
    <table class="dist-table">
        <tr class="dist-header">
            <td style="width: 25%;">Sangat Tinggi (&ge; 85)</td>
            <td style="width: 25%;">Tinggi (70 &ndash; 84.9)</td>
            <td style="width: 25%;">Sedang (55 &ndash; 69.9)</td>
            <td style="width: 25%;">Kurang (&lt; 55)</td>
        </tr>
        <tr>
            <td>
                <span class="badge badge-success font-bold" style="font-size: 7.6pt;">{{ $distribusi['sangat_tinggi'] ?? 0 }} Siswa</span>
                <span class="text-muted" style="font-size: 6.4pt;">({{ count($scores) > 0 ? round(($distribusi['sangat_tinggi'] / count($scores)) * 100) : 0 }}%)</span>
            </td>
            <td>
                <span class="badge badge-primary font-bold" style="font-size: 7.6pt;">{{ $distribusi['tinggi'] ?? 0 }} Siswa</span>
                <span class="text-muted" style="font-size: 6.4pt;">({{ count($scores) > 0 ? round(($distribusi['tinggi'] / count($scores)) * 100) : 0 }}%)</span>
            </td>
            <td>
                <span class="badge badge-warning font-bold" style="font-size: 7.6pt;">{{ $distribusi['sedang'] ?? 0 }} Siswa</span>
                <span class="text-muted" style="font-size: 6.4pt;">({{ count($scores) > 0 ? round(($distribusi['sedang'] / count($scores)) * 100) : 0 }}%)</span>
            </td>
            <td>
                <span class="badge badge-danger font-bold" style="font-size: 7.6pt;">{{ $distribusi['kurang'] ?? 0 }} Siswa</span>
                <span class="text-muted" style="font-size: 6.4pt;">({{ count($scores) > 0 ? round(($distribusi['kurang'] / count($scores)) * 100) : 0 }}%)</span>
            </td>
        </tr>
    </table>

    <!-- ANALISIS BUTIR SOAL -->
    <div class="section-header">2. Analisis Butir Soal (Item Psychometrics)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama / Indikator Butir Soal</th>
                <th style="width: 8%;">Tipe</th>
                <th style="width: 12%;">Tingkat Kesukaran (P)</th>
                <th style="width: 13%;">Klasifikasi Kesukaran</th>
                <th style="width: 11%;">Daya Pembeda (D)</th>
                <th style="width: 13%;">Klasifikasi Pembeda</th>
                <th style="width: 13%;">Rekomendasi</th>
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
                    <td colspan="8" class="text-center text-muted" style="padding: 10px;">Belum ada data butir soal yang dianalisis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PAGE BREAK UNTUK DAFTAR NILAI SISWA -->
    <div class="page-break"></div>

    <div class="title-block" style="margin-bottom: 8px;">
        <div class="doc-badge">REKAPITULASI HASIL CBT</div>
        <h2 class="doc-title" style="font-size: 10pt;">Daftar Hasil Nilai Siswa</h2>
        <div class="doc-subtitle">{{ $archive->moodle_quiz_name }} &bull; Rombel: {{ $archive->kelas->nama_kelas ?? 'Semua Peserta' }} ({{ count($scores) }} Siswa)</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">Rank</th>
                <th style="width: 18%;">NISN / Username</th>
                <th>Nama Lengkap Siswa</th>
                <th style="width: 17%;">Kelas / Rombel</th>
                <th style="width: 16%;">Waktu Selesai</th>
                <th style="width: 13%;">Nilai Akhir (100)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scores as $idx => $sc)
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="text-center" style="font-family: monospace; font-size: 7pt;">{{ $sc->moodle_username ?? '-' }}</td>
                    <td class="font-bold">{{ $sc->nama_siswa }}</td>
                    <td class="text-center">{{ $sc->kelas_nama ?? '-' }}</td>
                    <td class="text-center" style="font-size: 6.8pt;">{{ $sc->time_finished ? $sc->time_finished->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center font-bold" style="font-size: 8.2pt; color: {{ $sc->grade >= 75 ? '#166534' : '#991b1b' }};">
                        {{ number_format($sc->grade, 1) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 12px;">Belum ada data siswa yang terekam dalam arsip ini.</td>
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
                <strong>{{ $kepalaSekolah?->name ?? 'GUNAWAN SUSANTO, S.PD, M.Pd.' }}</strong><br>
                <span style="font-size: 7.2pt; color: #475569;">NIP. {{ $kepalaSekolah?->gtk?->nip ?? '197702222005011004' }}</span>
            </td>
            <td>
                Metro, {{ \Carbon\Carbon::parse($archive->created_at ?? now())->locale('id')->translatedFormat('d F Y') }}<br>
                Guru Mata Pelajaran
                <div class="ttd-space"></div>
                <strong>{{ $gtk?->nama_lengkap ?? $archive->gtk?->nama_lengkap ?? ($archive->user?->name ?? '..................................') }}</strong><br>
                <span style="font-size: 7.2pt; color: #475569;">NIP. {{ $gtk?->nip ?? $archive->gtk?->nip ?? '..................................' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
