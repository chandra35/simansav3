<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi {{ $kelas->nama_lengkap }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            padding: 10px;
        }
        
        /* Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .kop-table {
            width: 100%;
            margin-bottom: 3px;
        }
        
        .kop-table td {
            vertical-align: top;
        }
        
        .kop-logo {
            width: 12%;
            text-align: center;
        }
        
        .kop-logo img {
            height: 50px;
            width: auto;
        }
        
        .kop-content {
            width: 76%;
            text-align: center;
        }
        
        .kop-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1px;
            line-height: 1.2;
        }
        
        .kop-subtitle {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1px;
            line-height: 1.2;
        }
        
        .kop-address {
            font-size: 8pt;
            margin-top: 2px;
            line-height: 1.2;
        }
        
        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin: 8px 0 6px 0;
        }
        
        .doc-title h2 {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        /* Info Kelas */
        .kelas-info {
            margin-bottom: 8px;
        }
        
        .kelas-info-table {
            width: 100%;
        }
        
        .kelas-info-table td {
            vertical-align: top;
            width: 50%;
        }
        
        .kelas-info table {
            margin-bottom: 0;
        }
        
        .kelas-info td {
            padding: 1px 3px;
            font-size: 9pt;
        }
        
        .kelas-info .label {
            width: 90px;
            font-weight: bold;
        }
        
        .kelas-info .separator {
            width: 15px;
            text-align: center;
        }
        
        /* Tabel Absensi */
        .table-absensi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        
        .table-absensi th,
        .table-absensi td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
        }
        
        .table-absensi th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 7pt;
        }
        
        .table-absensi .col-no {
            width: 25px;
        }
        
        .table-absensi .col-nisn {
            width: 85px;
            font-size: 7pt;
        }
        
        .table-absensi .col-nama {
            text-align: left;
            padding-left: 5px;
            font-size: 8pt;
        }
        
        .table-absensi .col-jk {
            width: 25px;
        }
        
        .table-absensi .col-hari {
            width: 45px;
            font-size: 7pt;
        }
        
        /* Footer Info */
        .footer-info {
            margin-top: 10px;
            display: table;
            width: 100%;
        }
        
        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .keterangan {
            margin-bottom: 5px;
        }
        
        .keterangan table td {
            padding: 1px 3px;
            font-size: 9pt;
        }
        
        .keterangan .label {
            width: 110px;
        }
        
        .ttd-section {
            margin-top: 5px;
        }
        
        .ttd-text {
            margin-bottom: 45px;
            font-size: 9pt;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
            font-size: 9pt;
        }
        
        .ttd-nip {
            font-size: 8pt;
        }
        
        @page {
            margin: 10mm 12mm;
        }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    <div class="kop-surat">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    @if($logoKemenagBase64)
                        <img src="{{ $logoKemenagBase64 }}" alt="Logo Kemenag">
                    @endif
                </td>
                <td class="kop-content">
                    @if($setting && $setting->kop_surat_config)
                        @foreach($setting->kop_surat_config['elements'] ?? [] as $element)
                            @if($element['type'] === 'text')
                                <div style="
                                    font-size: {{ ($element['style']['fontSize'] ?? 14) * 0.65 }}pt;
                                    font-weight: {{ $element['style']['fontWeight'] ?? 'normal' }};
                                    margin-bottom: {{ ($element['style']['marginBottom'] ?? 2) * 0.7 }}px;
                                    line-height: 1.2;
                                ">
                                    {{ $element['content'] }}
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="kop-title">{{ $setting->nama_sekolah ?? 'NAMA SEKOLAH' }}</div>
                        <div class="kop-subtitle">{{ $setting->nama_madrasah ?? '' }}</div>
                        <div class="kop-address">
                            {{ $setting->alamat ?? '' }}
                        </div>
                    @endif
                </td>
                <td class="kop-logo">
                    @if($logoSekolahBase64)
                        <img src="{{ $logoSekolahBase64 }}" alt="Logo Sekolah">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        <h2>DAFTAR HADIR KELAS</h2>
    </div>

    {{-- Info Kelas --}}
    <div class="kelas-info">
        <table class="kelas-info-table">
            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="label">Nama Kelas</td>
                            <td class="separator">:</td>
                            <td>{{ $kelas->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="label">Semester</td>
                            <td class="separator">:</td>
                            <td>{{ ucfirst($kelas->tahunPelajaran->semester_aktif ?? '-') }} / Tahun Ajaran {{ $kelas->tahunPelajaran->nama ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table>
                        <tr>
                            <td class="label">Wali Kelas</td>
                            <td class="separator">:</td>
                            <td>{{ $kelas->waliKelas->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Minggu Ke</td>
                            <td class="separator">:</td>
                            <td>___________________</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Tabel Absensi --}}
    <table class="table-absensi">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">No</th>
                <th rowspan="2" class="col-nisn">NISN</th>
                <th rowspan="2" class="col-nama">Nama</th>
                <th rowspan="2" class="col-jk">L/P</th>
                <th colspan="6" style="font-size: 8pt;">Hari</th>
                <th rowspan="2" style="width: 22px;">S</th>
                <th rowspan="2" style="width: 22px;">I</th>
                <th rowspan="2" style="width: 22px;">A</th>
            </tr>
            <tr>
                <th class="col-hari">Senin</th>
                <th class="col-hari">Selasa</th>
                <th class="col-hari">Rabu</th>
                <th class="col-hari">Kamis</th>
                <th class="col-hari">Jumat</th>
                <th class="col-hari">Sabtu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelas->siswas as $index => $siswa)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-nisn">{{ $siswa->nisn }}</td>
                <td class="col-nama">{{ strtoupper($siswa->nama_lengkap) }}</td>
                <td class="col-jk">{{ $siswa->jenis_kelamin === 'L' ? 'L' : 'P' }}</td>
                <td class="col-hari"></td>
                <td class="col-hari"></td>
                <td class="col-hari"></td>
                <td class="col-hari"></td>
                <td class="col-hari"></td>
                <td class="col-hari"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align: center; padding: 20px;">
                    Tidak ada siswa di kelas ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Info --}}
    <div class="footer-info">
        <div class="footer-left">
            <div class="keterangan">
                <table>
                    <tr>
                        <td class="label">Jumlah Siswa</td>
                        <td>:</td>
                        <td>{{ $kelas->siswas->count() }} Siswa</td>
                    </tr>
                    <tr>
                        <td class="label">Jumlah Hadir</td>
                        <td>:</td>
                        <td>_______ Siswa</td>
                    </tr>
                    <tr>
                        <td class="label">Jumlah Absen</td>
                        <td>:</td>
                        <td>_______ Siswa</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="footer-right">
            <div class="ttd-section">
                <div class="ttd-text">
                    {{ $setting->kota_nama ?? 'Kota' }}, {{ date('d F Y') }}<br>
                    Wali Kelas
                </div>
                <div class="ttd-nama">{{ $kelas->waliKelas->name ?? '___________________' }}</div>
                <div class="ttd-nip">NIP: {{ $kelas->waliKelas->gtk->nip ?? '___________________' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
