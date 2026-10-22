<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $suratKeterangan->nomor_surat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 20mm 15mm 20mm;
        }
        body {
            color: #111;
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            margin: 0;
        }
        
        /* KOP SURAT (Following Disposisi Kemenag Style) */
        .kop {
            border-bottom: 3px double #111;
            height: 85px;
            padding: 0 0 6px;
            position: relative;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
        }
        .kop-logo {
            height: 82px;
            object-fit: contain;
            position: absolute;
            top: 0;
            width: 82px;
        }
        .kop-logo.left {
            left: 0;
            width: 95px;
        }
        .kop-logo.right {
            right: 0;
        }
        .kop-title {
            line-height: 1.15;
            margin: 0 85px;
        }
        .kop-title .line-1 {
            font-size: 13.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-title .line-2 {
            font-size: 13.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-title .line-3 {
            font-size: 14.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-meta {
            font-size: 8.5pt;
            line-height: 1.2;
            margin: 4px 60px 0;
        }

        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            margin: 18px 0 16px;
        }
        .judul-surat .title {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1px;
            margin: 0;
        }
        .judul-surat .nomor {
            font-size: 11pt;
            margin-top: 3px;
        }

        /* ISI SURAT */
        .section-text {
            text-align: justify;
            margin: 10px 0 6px;
        }
        
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 15px;
        }
        .table-data td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .table-data .col-label {
            width: 190px;
        }
        .table-data .col-colon {
            width: 20px;
            text-align: center;
        }
        .table-data .col-value {
            
        }

        .table-subdata {
            width: 100%;
            border-collapse: collapse;
        }
        .table-subdata td {
            padding: 1px 0;
        }
        .table-subdata .col-sublabel {
            width: 60px;
        }
        .table-subdata .col-subcolon {
            width: 18px;
            text-align: center;
        }

        .closing-text {
            text-align: justify;
            margin: 10px 0 0;
        }

        /* TANDA TANGAN */
        .ttd-wrapper {
            margin-top: 25px;
            float: right;
            width: 280px;
            text-align: left;
        }
        .ttd-wrapper .tanggal {
            margin-bottom: 2px;
        }
        .ttd-wrapper .jabatan {
            margin-bottom: 65px;
        }
        .ttd-wrapper .nama {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        .ttd-wrapper .nip {
            margin: 2px 0 0;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    @php
        $kopLines = $setting->kop_header_lines;
        $data = $suratKeterangan->data_tambahan ?? [];
        
        $namaPejabat = $data['nama_pejabat'] ?? $suratKeterangan->penandatangan?->nama_lengkap ?? 'H. Gunawan Susanto, S.Pd., M.Pd.';
        $nipPejabat = $data['nip_pejabat'] ?? $suratKeterangan->penandatangan?->nip ?? '197702222005011004';
        $pangkatGol = $data['pangkat_golongan_pejabat'] ?? 'Pembina Tk I / IV(b)';
        $jabatanPejabat = $data['jabatan_pejabat'] ?? $suratKeterangan->jabatan_penandatangan ?? 'Kepala Madrasah';
        $instansiPejabat = $data['instansi_pejabat'] ?? $setting->nama_sekolah ?: 'MAN 1 Metro';

        $namaSiswa = $data['nama_siswa'] ?? $suratKeterangan->siswa?->nama_lengkap ?? '-';
        $nis = $data['nis'] ?? $suratKeterangan->siswa?->nis ?? '-';
        $nisn = $data['nisn'] ?? $suratKeterangan->siswa?->nisn ?? '-';
        
        $tempatLahir = $data['tempat_lahir'] ?? $suratKeterangan->siswa?->tempat_lahir ?? '';
        $tanggalLahir = $data['tanggal_lahir'] ?? ($suratKeterangan->siswa?->tanggal_lahir ? \Carbon\Carbon::parse($suratKeterangan->siswa->tanggal_lahir)->translatedFormat('d F Y') : '');
        $ttl = trim($tempatLahir . ($tempatLahir && $tanggalLahir ? ', ' : '') . $tanggalLahir);

        $namaAyah = $data['nama_ayah'] ?? $suratKeterangan->siswa?->ortu?->nama_ayah ?? '-';
        $namaIbu = $data['nama_ibu'] ?? $suratKeterangan->siswa?->ortu?->nama_ibu ?? '-';
        $kelas = $data['kelas'] ?? $suratKeterangan->siswa?->kelasTahunAktif->first()?->nama_kelas ?? $suratKeterangan->siswa?->kelasSaatIni?->nama_kelas ?? '-';
        $tapel = $data['tahun_pelajaran'] ?? '2026/2027';
        $keperluan = $suratKeterangan->keperluan ?? $data['keperluan'] ?? '-';
        $tanggalSurat = $suratKeterangan->tanggal_surat ? $suratKeterangan->tanggal_surat->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y');
    @endphp

    <!-- KOP SURAT RESMI -->
    <div class="kop">
        @if($logoKemenagDataUri)
            <img class="kop-logo left" src="{{ $logoKemenagDataUri }}" alt="Logo Kementerian Agama">
        @endif
        @if($logoSekolahDataUri)
            <img class="kop-logo right" src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah">
        @endif
        <div class="kop-title">
            <div class="line-1">{{ $kopLines[0] }}</div>
            <div class="line-2">{{ $kopLines[1] }}</div>
            <div class="line-3">{{ $kopLines[2] }}</div>
        </div>
        <div class="kop-meta">
            {{ $setting->kop_identitas }}<br>
            {{ $setting->kop_alamat }}<br>
            <em>Website : <u>{{ $setting->website ?: '-' }}</u>&nbsp;&nbsp; E-mail: {{ $setting->email ?: '-' }}</em>
        </div>
    </div>

    <!-- JUDUL DAN NOMOR -->
    <div class="judul-surat">
        <div class="title">SURAT KETERANGAN</div>
        <div class="nomor">Nomor : {{ $suratKeterangan->nomor_surat }}</div>
    </div>

    <!-- PARAGRAF PEMBUKA PEJABAT -->
    <div class="section-text">
        Yang bertanda tangan di bawah ini :
    </div>

    <table class="table-data">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-value"><strong>{{ $namaPejabat }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">NIP</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $nipPejabat }}</td>
        </tr>
        <tr>
            <td class="col-label">Pangkat / Golongan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $pangkatGol }}</td>
        </tr>
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $jabatanPejabat }}</td>
        </tr>
        <tr>
            <td class="col-label">Instansi</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $instansiPejabat }}</td>
        </tr>
    </table>

    <!-- KETERANGAN SISWA -->
    <div class="section-text">
        Dengan ini menerangkan bahwa :
    </div>

    <table class="table-data">
        <tr>
            <td class="col-label">Nama Siswa</td>
            <td class="col-colon">:</td>
            <td class="col-value"><strong>{{ strtoupper($namaSiswa) }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">NIS / NISN</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $nis }} / {{ $nisn }}</td>
        </tr>
        <tr>
            <td class="col-label">Tempat / Tanggal Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $ttl ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Nama Orang Tua / Wali</td>
            <td class="col-colon">:</td>
            <td class="col-value">
                <table class="table-subdata">
                    <tr>
                        <td class="col-sublabel">Ayah</td>
                        <td class="col-subcolon">:</td>
                        <td>{{ strtoupper($namaAyah) }}</td>
                    </tr>
                    <tr>
                        <td class="col-sublabel">Ibu</td>
                        <td class="col-subcolon">:</td>
                        <td>{{ strtoupper($namaIbu) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-label">Asal Sekolah</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $instansiPejabat }}</td>
        </tr>
    </table>

    <!-- PERNYATAAN STATUS AKTIF -->
    <div class="section-text" style="text-indent: 30px;">
        Adalah benar yang bersangkutan tercatat aktif sebagai Siswa di {{ $instansiPejabat }} Kelas <strong>{{ $kelas }}</strong> Tahun Pelajaran <strong>{{ $tapel }}</strong>.
    </div>

    <!-- KEPERLUAN -->
    <div class="section-text" style="margin-top: 10px;">
        Surat keterangan ini dibuat untuk dipergunakan sebagai : <strong>{{ $keperluan }}</strong>
    </div>

    <!-- PENUTUP -->
    <div class="closing-text" style="text-indent: 30px; margin-top: 10px;">
        Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN -->
    <div class="clearfix">
        <div class="ttd-wrapper">
            <div class="tanggal">Metro, {{ $tanggalSurat }}</div>
            <div class="jabatan">{{ $jabatanPejabat }},</div>
            <div class="nama">{{ $namaPejabat }}</div>
            <div class="nip">NIP. {{ $nipPejabat }}</div>
        </div>
    </div>
</body>
</html>