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
            font-size: 11pt;
            line-height: 1.4;
            padding: 15px;
        }
        
        /* Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .kop-table {
            width: 100%;
            margin-bottom: 5px;
        }
        
        .kop-table td {
            vertical-align: top;
        }
        
        .kop-logo {
            width: 15%;
            text-align: center;
        }
        
        .kop-logo img {
            height: 80px;
            width: auto;
        }
        
        .kop-content {
            width: 70%;
            text-align: center;
        }
        
        .kop-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .kop-subtitle {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .kop-address {
            font-size: 10pt;
            margin-top: 3px;
        }
        
        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin: 20px 0 15px 0;
        }
        
        .doc-title h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        /* Info Kelas */
        .kelas-info {
            margin-bottom: 15px;
        }
        
        .kelas-info table {
            margin-bottom: 10px;
        }
        
        .kelas-info td {
            padding: 2px 5px;
            font-size: 11pt;
        }
        
        .kelas-info .label {
            width: 150px;
            font-weight: bold;
        }
        
        .kelas-info .separator {
            width: 20px;
            text-align: center;
        }
        
        /* Tabel Absensi */
        .table-absensi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        .table-absensi th,
        .table-absensi td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        
        .table-absensi th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .table-absensi .col-no {
            width: 30px;
        }
        
        .table-absensi .col-nis {
            width: 80px;
        }
        
        .table-absensi .col-nama {
            text-align: left;
            padding-left: 8px;
        }
        
        .table-absensi .col-jk {
            width: 25px;
        }
        
        .table-absensi .col-tanggal {
            width: 20px;
            font-size: 7pt;
        }
        
        /* Footer Info */
        .footer-info {
            margin-top: 20px;
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
            margin-bottom: 10px;
        }
        
        .keterangan table td {
            padding: 2px 5px;
            font-size: 10pt;
        }
        
        .keterangan .label {
            width: 150px;
        }
        
        .ttd-section {
            margin-top: 10px;
        }
        
        .ttd-text {
            margin-bottom: 60px;
            font-size: 10pt;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        .ttd-nip {
            font-size: 10pt;
        }
        
        @page {
            margin: 15px;
        }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    <div class="kop-surat">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    @if($setting && $setting->logo_kemenag)
                        <img src="{{ public_path($setting->logo_kemenag) }}" alt="Logo Kemenag">
                    @endif
                </td>
                <td class="kop-content">
                    @if($setting && $setting->kop_surat_config)
                        @foreach($setting->kop_surat_config['elements'] ?? [] as $element)
                            @if($element['type'] === 'text')
                                <div style="
                                    font-size: {{ $element['style']['fontSize'] ?? 14 }}pt;
                                    font-weight: {{ $element['style']['fontWeight'] ?? 'normal' }};
                                    margin-bottom: {{ $element['style']['marginBottom'] ?? 2 }}px;
                                ">
                                    {{ $element['content'] }}
                                </div>
                            @elseif($element['type'] === 'divider')
                                <hr style="
                                    border: none;
                                    border-top-style: {{ $element['style']['borderStyle'] ?? 'solid' }};
                                    border-top-width: {{ $element['style']['borderWidth'] ?? 1 }}px;
                                    border-top-color: {{ $element['style']['borderColor'] ?? '#000000' }};
                                    margin-top: {{ $element['style']['marginTop'] ?? 5 }}px;
                                    margin-bottom: {{ $element['style']['marginBottom'] ?? 5 }}px;
                                ">
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
                    @if($setting && $setting->logo_sekolah)
                        <img src="{{ public_path($setting->logo_sekolah) }}" alt="Logo Sekolah">
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
        <table>
            <tr>
                <td class="label">Nama Kelas</td>
                <td class="separator">:</td>
                <td>{{ $kelas->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">Semester</td>
                <td class="separator">:</td>
                <td>{{ $kelas->tahunPelajaran->semester_aktif ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Ajaran</td>
                <td class="separator">:</td>
                <td>{{ $kelas->tahunPelajaran->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Wali Kelas</td>
                <td class="separator">:</td>
                <td>{{ $kelas->waliKelas->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Tabel Absensi --}}
    <table class="table-absensi">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">No</th>
                <th rowspan="2" class="col-nis">NIS</th>
                <th rowspan="2" class="col-nama">Nama</th>
                <th rowspan="2" class="col-jk">L/P</th>
                <th colspan="{{ $jumlahHari }}" style="font-size: 9pt;">Tanggal</th>
                <th rowspan="2" style="width: 30px;">S</th>
                <th rowspan="2" style="width: 30px;">I</th>
                <th rowspan="2" style="width: 30px;">A</th>
            </tr>
            <tr>
                @for($i = 1; $i <= $jumlahHari; $i++)
                    <th class="col-tanggal">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($kelas->siswas as $index => $siswa)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-nis">{{ $siswa->nis }}</td>
                <td class="col-nama">{{ strtoupper($siswa->nama_lengkap) }}</td>
                <td class="col-jk">{{ $siswa->jenis_kelamin === 'L' ? 'L' : 'P' }}</td>
                @for($i = 1; $i <= $jumlahHari; $i++)
                    <td class="col-tanggal"></td>
                @endfor
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 4 + $jumlahHari + 3 }}" style="text-align: center; padding: 20px;">
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
                    {{ $setting->kelurahan_nama ?? 'Kelurahan' }}, {{ date('d F Y') }}<br>
                    Wali Kelas
                </div>
                <div class="ttd-nama">{{ $kelas->waliKelas->name ?? '___________________' }}</div>
                <div class="ttd-nip">NIP: {{ $kelas->waliKelas->gtk->nip ?? '___________________' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
