<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: legal portrait; margin: 12mm 15mm 15mm; }
        * { box-sizing: border-box; }
        body { color:#111; font-family: Arial, Helvetica, sans-serif; font-size:12pt; margin:0; }
        .kop { border-bottom:3px double #111; height:104px; padding-bottom:5px; position:relative; text-align:center; }
        .kop-logo { height:86px; object-fit:contain; position:absolute; top:0; width:86px; }
        .kop-logo.left { left:0; }
        .kop-logo.right { right:0; }
        .kop-title { line-height:1.08; margin:0 68px; }
        .kop-title div { font-size:14pt; }
        .kop-title .line-3 { font-size:16pt; }
        .kop-meta { font-size:8pt; line-height:1.15; margin:4px 18px 0; }
        .document-title { font-size:16pt; font-weight:bold; margin:25px 0 3px; text-align:center; text-decoration:underline; }
        .document-number { font-size:10pt; margin-bottom:22px; text-align:center; }
        p { line-height:1.4; margin:0 0 12px; text-align:justify; }
        .intro { margin-bottom:8px; }
        table.identity { border-collapse:collapse; margin:0 0 13px; width:100%; }
        table.identity td { padding:2px 0; vertical-align:top; }
        table.identity .number { width:22px; }
        table.identity .label { width:37%; }
        table.identity .colon { text-align:center; width:16px; }
        table.identity .value { width:auto; }
        .section-title { margin:9px 0 3px; }
        .closing { margin-top:13px; }
        .signature { margin-left:58%; margin-top:28px; width:42%; }
        .signature .city-date { margin-bottom:2px; }
        .signature .role { margin-bottom:40px; }
        .signature .name { font-weight:bold; text-decoration:underline; }
        .signature .nip { margin-top:1px; }
    </style>
</head>
<body>
@php
    $siswa = $mutasiSiswa->siswa;
    $ortu = $siswa?->ortu;
    $kepalaUser = $kepala['user'] ?? null;
    $kepalaGtk = $kepalaUser?->gtk;
    $bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $formatDate = function ($date) use ($bulan) {
        return $date ? $date->format('j').' '.$bulan[(int) $date->format('n')].' '.$date->format('Y') : '-';
    };
    $jenisKelamin = $siswa?->jenis_kelamin === 'L' ? 'Laki-Laki' : ($siswa?->jenis_kelamin === 'P' ? 'Perempuan' : '-');
    $kelas = $siswa?->kelasSaatIni?->nama_lengkap ?: ($mutasiSiswa->kelas_asal ?: '-');
    $alamat = method_exists($siswa, 'getAlamatLengkapSiswa') ? $siswa->getAlamatLengkapSiswa() : ($siswa?->alamat_siswa ?: '-');
    $nomor = $mutasiSiswa->nomor_surat_mutasi ?: '-';
    $tanggalSurat = $formatDate($mutasiSiswa->tanggal_mutasi);
    $namaKepala = $kepalaUser?->name ?: 'Kepala Madrasah';
    $nipKepala = $kepalaGtk?->nip ?: '-';
    $kotaCetak = $setting->kota?->name ?: 'Metro';
@endphp

<div class="kop">
    @if($logoKemenagDataUri)<img class="kop-logo left" src="{{ $logoKemenagDataUri }}" alt="Logo Kementerian Agama">@endif
    @if($logoSekolahDataUri)<img class="kop-logo right" src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah">@endif
    @php($kopLines = $setting->kop_header_lines)
    <div class="kop-title">
        <div>{{ $kopLines[0] }}</div>
        <div>{{ $kopLines[1] }}</div>
        <div class="line-3">{{ $kopLines[2] }}</div>
    </div>
    <div class="kop-meta">
        {{ $setting->kop_identitas }}<br>
        {{ $setting->kop_alamat }}<br>
        <em>Website : <u>{{ $setting->website ?: '-' }}</u>&nbsp;&nbsp; E-mail: {{ $setting->email ?: '-' }}</em>
    </div>
</div>

<div class="document-title">SURAT IZIN MUTASI / PINDAH SISWA</div>
<div class="document-number">Nomor : {{ $nomor }}</div>

<p class="intro">Yang bertanda tangan di bawah ini Kepala Madrasah Aliyah Negeri (MAN) 1 Metro Provinsi Lampung menerangkan dengan sesungguhnya bahwa :</p>

<table class="identity">
    <tr><td class="number">1.</td><td class="label">Nama siswa</td><td class="colon">:</td><td class="value">{{ $siswa?->nama_lengkap ?: '-' }}</td></tr>
    <tr><td class="number">2.</td><td class="label">Tempat tanggal lahir</td><td class="colon">:</td><td class="value">{{ $siswa?->tempat_lahir ?: '-' }}, {{ $formatDate($siswa?->tanggal_lahir) }}</td></tr>
    <tr><td class="number">3.</td><td class="label">Nomor pokok sekolah nasional (NPSN)</td><td class="colon">:</td><td class="value">{{ $siswa?->npsn_asal_sekolah ?: $setting->npsn ?: '-' }}</td></tr>
    <tr><td class="number">4.</td><td class="label">Nomor induk siswa nasional (NISN)</td><td class="colon">:</td><td class="value">{{ $siswa?->nisn ?: '-' }}</td></tr>
    <tr><td class="number">5.</td><td class="label">Nomor induk siswa (NIS)</td><td class="colon">:</td><td class="value">{{ $siswa?->nis ?: '-' }}</td></tr>
    <tr><td class="number">6.</td><td class="label">Jenis kelamin</td><td class="colon">:</td><td class="value">{{ $jenisKelamin }}</td></tr>
    <tr><td class="number">7.</td><td class="label">Kelas</td><td class="colon">:</td><td class="value">{{ $kelas }}</td></tr>
    <tr><td class="number">8.</td><td class="label">Alamat siswa</td><td class="colon">:</td><td class="value">{{ $alamat }}</td></tr>
    <tr><td class="number">9.</td><td class="label">Agama</td><td class="colon">:</td><td class="value">{{ $siswa?->agama ?: '-' }}</td></tr>
    <tr><td class="number">10.</td><td class="label">Nama ayah kandung</td><td class="colon">:</td><td class="value">{{ $ortu?->nama_ayah ?: '-' }}</td></tr>
    <tr><td class="number">11.</td><td class="label">Nama ibu kandung</td><td class="colon">:</td><td class="value">{{ $ortu?->nama_ibu ?: '-' }}</td></tr>
</table>

@if($mutasiSiswa->isMutasiKeluar())
    <p>Telah mengajukan permohonan mutasi / pindah siswa atas permintaan orang tua / wali murid yang bersangkutan dengan alasan {{ $mutasiSiswa->alasan_mutasi_keluar ?: 'pindah sekolah' }}.</p>
    <p>Pada dasarnya kami menyetujui usulan pindah siswa tersebut dan yang bersangkutan tidak diperkenankan masuk kembali ke {{ $setting->nama_sekolah ?: 'madrasah ini' }}.</p>
    <p class="section-title">Tujuan pindah ke :</p>
    <table class="identity">
        <tr><td class="number">1.</td><td class="label">Nama sekolah</td><td class="colon">:</td><td class="value">{{ $mutasiSiswa->sekolah_tujuan ?: '-' }}</td></tr>
        <tr><td class="number">2.</td><td class="label">Kabupaten / Kota</td><td class="colon">:</td><td class="value">{{ $mutasiSiswa->alamat_sekolah_tujuan ?: '-' }}</td></tr>
        <tr><td class="number">3.</td><td class="label">Provinsi</td><td class="colon">:</td><td class="value">-</td></tr>
    </table>
@else
    <p>Telah diterima sebagai peserta didik pindahan di {{ $setting->nama_sekolah ?: 'madrasah ini' }} berdasarkan data mutasi dari {{ $mutasiSiswa->sekolah_asal ?: '-' }}.</p>
    <p>Surat ini dibuat sebagai dokumen administrasi perpindahan peserta didik sesuai data yang tercatat pada sistem.</p>
@endif

<p class="closing">Demikian Surat Izin Mutasi / Pindah Siswa ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

<div class="signature">
    <div class="city-date">{{ $kotaCetak }}, {{ $tanggalSurat }}</div>
    <div class="role">Kepala Madrasah,</div>
    <div class="name">{{ $namaKepala }}</div>
    <div class="nip">NIP. {{ $nipKepala }}</div>
</div>
</body>
</html>
