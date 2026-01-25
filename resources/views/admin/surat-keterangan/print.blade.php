<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $suratKeterangan->nomor_surat }}</title>
    <style>
        @page {
            size: A4;
            margin: 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat .nama-instansi {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }
        .kop-surat .nama-sekolah {
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .kop-surat .alamat {
            font-size: 10pt;
            margin: 0;
        }
        .judul-surat {
            text-align: center;
            margin: 30px 0;
        }
        .judul-surat h2 {
            font-size: 14pt;
            text-decoration: underline;
            margin: 0;
        }
        .judul-surat .nomor {
            font-size: 11pt;
            margin: 5px 0 0 0;
        }
        .content {
            text-align: justify;
        }
        .data-siswa {
            margin: 20px 0;
        }
        .data-siswa table {
            margin-left: 40px;
        }
        .data-siswa td {
            padding: 3px 10px;
            vertical-align: top;
        }
        .data-siswa td:first-child {
            width: 150px;
        }
        .tanda-tangan {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .tanda-tangan .tempat-tanggal {
            margin-bottom: 5px;
        }
        .tanda-tangan .jabatan {
            margin-bottom: 70px;
        }
        .tanda-tangan .nama {
            font-weight: bold;
            text-decoration: underline;
        }
        .tanda-tangan .nip {
            font-size: 10pt;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="kop-surat">
        <p class="nama-instansi">KEMENTERIAN AGAMA REPUBLIK INDONESIA</p>
        <p class="nama-sekolah">{{ strtoupper($sekolah->nama ?? 'MADRASAH ALIYAH') }}</p>
        <p class="alamat">
            {{ $sekolah->alamat ?? 'Alamat Sekolah' }}<br>
            Telp: {{ $sekolah->telepon ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}
        </p>
    </div>
    
    <div class="judul-surat">
        <h2>SURAT KETERANGAN</h2>
        <p class="nomor">Nomor: {{ $suratKeterangan->nomor_surat }}</p>
    </div>
    
    <div class="content">
        {!! $suratKeterangan->rendered_content !!}
    </div>
    
    <div class="clearfix">
        <div class="tanda-tangan">
            <p class="tempat-tanggal">
                {{ $sekolah->kota ?? 'Kota' }}, {{ $suratKeterangan->tanggal_surat->translatedFormat('d F Y') }}
            </p>
            <p class="jabatan">Kepala Madrasah,</p>
            <p class="nama">{{ $sekolah->kepala_sekolah ?? 'Nama Kepala Sekolah' }}</p>
            <p class="nip">NIP. {{ $sekolah->nip_kepala ?? '-' }}</p>
        </div>
    </div>
</body>
</html>
