<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #1f2937; font-family: DejaVu Sans, Arial, sans-serif; font-size: 8.7pt; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { vertical-align: top; }
        .kop { height: 52px; text-align: center; }
        .kop td { vertical-align: middle; }
        .kop-logo { width: 40px; height: 45px; object-fit: contain; }
        .kop-left, .kop-right { width: 18%; }
        .kop-right { text-align: right; }
        .kop-center { width: 64%; text-align: center; line-height: 1.05; }
        .kop-kemenag { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; }
        .kop-school { margin: 1px 0; font-size: 11pt; font-weight: bold; text-transform: uppercase; white-space: nowrap; }
        .kop-address { font-size: 6.1pt; }
        .divider { height: 4px; margin: 2px 0 5px; border-top: 3px solid #064e3b; border-bottom: 1px solid #064e3b; }
        .title { margin: 0 0 4px; text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .overview { margin-bottom: 5px; border: 1px solid #cbd5e1; background: #f1f5f9; border-radius: 5px; }
        .overview td { padding: 3px 5px; }
        .overview > tbody > tr > td:first-child { width: 75%; }
        .overview-data { vertical-align: middle; }
        .overview-details { width: 100%; table-layout: fixed; }
        .overview-details td { padding: 1px 0; border: 0; vertical-align: middle; }
        .overview-details .overview-label { width: 27%; font-weight: bold; white-space: nowrap; }
        .overview-details .overview-colon { width: 3%; text-align: center; white-space: nowrap; }
        .overview-details .overview-value { width: 70%; }
        .status-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; background: #dcfce7; color: #166534; font-weight: bold; }
        .photo-cell { width: 25%; text-align: right; vertical-align: middle !important; }
        .photo { display: inline-block; width: 30mm; height: 40mm; object-fit: cover; border: 1px solid #94a3b8; padding: 1px; }
        .photo-empty { display: inline-block; width: 30mm; height: 40mm; padding-top: 14mm; border: 1px solid #94a3b8; color: #64748b; text-align: center; font-size: 7pt; }
        .two-col-grid { margin-bottom: 4px; }
        .two-col-grid > tbody > tr > td { width: 50%; }
        .two-col-grid > tbody > tr > td:first-child { padding-right: 3px; }
        .two-col-grid > tbody > tr > td:last-child { padding-left: 3px; }
        .section { margin-bottom: 4px; }
        .section-title { padding: 3px 6px; border: 1px solid #047857; border-bottom: 0; background: #047857; color: #fff; font-size: 8.4pt; font-weight: bold; text-transform: uppercase; }
        .data-table { table-layout: fixed; }
        .data-table td { padding: 3px 5px; border: 1px solid #cbd5e1; vertical-align: top; overflow-wrap: normal; word-break: normal; }
        .data-table .label { width: 34%; background: #f8fafc; font-weight: bold; white-space: normal; }
        .value { overflow-wrap: normal; }
        .nowrap { white-space: nowrap; }
        .parent-table { margin-top: 2px; }
        .parent-table th, .parent-table td { padding: 3px 5px; border: 1px solid #cbd5e1; vertical-align: top; }
        .parent-table th { background: #ecfdf5; color: #065f46; text-align: left; font-size: 8.2pt; }
        .parent-table th:first-child, .parent-table td:first-child { width: 24%; }
        .parent-table th:nth-child(2), .parent-table td:nth-child(2), .parent-table th:nth-child(3), .parent-table td:nth-child(3) { width: 38%; }
        .parent-table td:first-child { background: #f8fafc; font-weight: bold; }
        .parent-table .nowrap { white-space: nowrap; }
        .full-section { margin-bottom: 4px; }
        .education-table .label { width: 18%; }
        .education-table td:nth-child(2) { width: 32%; }
        .education-table td:nth-child(3) { width: 50%; }
        .document-list { margin: 0; padding: 0; list-style: none; }
        .document-list li { margin: 0 0 1px; }
        .doc-list { margin: 0; padding: 0; list-style: none; }
        .doc-list li { margin: 0; }
        .signature { margin-top: 4px; table-layout: fixed; }
        .signature td { width: 50%; padding: 0 5px; text-align: center; font-size: 8.2pt; }
        .signature-space { height: 35px; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 3px; padding-top: 3px; border-top: 1px solid #cbd5e1; color: #64748b; text-align: center; font-size: 6.3pt; font-style: italic; white-space: nowrap; }
    </style>
</head>
<body>
@php
    $dash = '-';
    $ortu = $siswa->ortu;
    $kelas = $kelasAktif?->nama_lengkap ?? $kelasAktif?->nama_kelas ?? $dash;
    $schoolName = $setting?->nama_sekolah ?: 'MAN 1 Metro';
    $formatText = fn ($value) => filled($value) ? \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim((string) $value))) : $dash;
    $value = fn ($field) => filled($siswa->{$field}) ? $siswa->{$field} : $dash;
    $parentValue = fn ($field) => $ortu && filled($ortu->{$field}) ? $ortu->{$field} : $dash;
    $parentText = fn ($field) => $formatText($ortu?->{$field});
    $alamat = $siswa->alamat_siswa ?: $ortu?->alamat_ortu;
    $rt = $siswa->rt_siswa ?: $ortu?->rt_ortu;
    $rw = $siswa->rw_siswa ?: $ortu?->rw_ortu;
    $kelurahan = $siswa->kelurahanSiswa?->name ?? $ortu?->kelurahan?->name;
    $kecamatan = $siswa->kecamatanSiswa?->name ?? $ortu?->kecamatan?->name;
    $kabupaten = $siswa->kabupatenSiswa?->name ?? $ortu?->kabupaten?->name;
    $provinsi = $siswa->provinsiSiswa?->name ?? $ortu?->provinsi?->name;
    $kodePos = $siswa->kodepos_siswa ?: $ortu?->kodepos;
    $kepalaName = $kepalaMadrasah?->name ?: '........................................';
    $kepalaNip = $kepalaMadrasah?->gtk?->nip ?: '........................................';
@endphp

<table class="kop"><tr>
    <td class="kop-left">@if($logoKemenagBase64)<img class="kop-logo" src="{{ $logoKemenagBase64 }}" alt="Logo Kemenag">@endif</td>
    <td class="kop-center">
        <div class="kop-kemenag">Kementerian Agama Republik Indonesia</div>
        <div class="kop-kemenag">Kantor Kementerian Agama Kota Metro</div>
        <div class="kop-school">{{ $schoolName }}</div>
        <div class="kop-address">{{ $setting?->alamat_lengkap ?: $setting?->alamat ?: 'Alamat madrasah belum diatur' }}{{ $setting?->kode_pos ? ' - Kode Pos '.$setting->kode_pos : '' }}<br>Website: {{ $setting?->website ?: '-' }} | Email: {{ $setting?->email ?: '-' }}</div>
    </td>
    <td class="kop-right">@if($logoBase64)<img class="kop-logo" src="{{ $logoBase64 }}" alt="Logo Madrasah">@endif</td>
</tr></table>
<div class="divider"></div>
<div class="title">Biodata Peserta Didik</div>

<table class="overview"><tr>
    <td class="overview-data">
        <table class="overview-details">
            <tr><td class="overview-label">Nama Lengkap</td><td class="overview-colon">:</td><td class="overview-value"><strong style="font-size: 10.5pt;">{{ $formatText($siswa->nama_lengkap) }}</strong></td></tr>
            <tr><td class="overview-label">NISN</td><td class="overview-colon">:</td><td class="overview-value nowrap">{{ $value('nisn') }}</td></tr>
            <tr><td class="overview-label">NIS Lokal</td><td class="overview-colon">:</td><td class="overview-value nowrap">{{ $value('nis_lokal') }}</td></tr>
            <tr><td class="overview-label">Kelas Aktif</td><td class="overview-colon">:</td><td class="overview-value">{{ $kelas }}</td></tr>
            <tr><td class="overview-label">Status</td><td class="overview-colon">:</td><td class="overview-value"><span class="status-badge">{{ $formatText($siswa->status_siswa ?: 'aktif') }}</span></td></tr>
        </table>
    </td>
    <td class="photo-cell">@if($fotoBase64)<img class="photo" src="{{ $fotoBase64 }}" alt="Foto siswa">@else<div class="photo-empty">FOTO 3 x 4</div>@endif</td>
</tr></table>

<!-- BARIS 1: data pribadi dan alamat berdampingan -->
<table class="two-col-grid"><tr>
    <td>
        <div class="section"><div class="section-title">A. Data Pribadi</div><table class="data-table">
            <tr><td class="label">NIK</td><td class="value nowrap">{{ $value('nik') }}</td></tr>
            <tr><td class="label">Tempat, Tgl Lahir</td><td class="value">{{ $formatText($siswa->tempat_lahir) }}, {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr>
            <tr><td class="label">Agama</td><td class="value">{{ $formatText($siswa->agama) }}</td></tr>
            <tr><td class="label">No. HP</td><td class="value nowrap">{{ $value('nomor_hp') }}</td></tr>
            <tr><td class="label">Tahun Masuk</td><td class="value">{{ $siswa->tahun_masuk ?: $dash }}</td></tr>
            <tr><td class="label">Anak Ke / Saudara</td><td class="value">{{ $siswa->anak_ke ?? $dash }} / {{ $siswa->jumlah_saudara ?? $dash }}</td></tr>
            <tr><td class="label">Hobi / Cita-cita</td><td class="value">{{ $formatText($siswa->hobi) }} / {{ $formatText($siswa->cita_cita) }}</td></tr>
            <tr><td class="label">Email</td><td class="value">{{ $siswa->user?->email ?: $dash }}</td></tr>
        </table></div>
    </td>
    <td>
        <div class="section"><div class="section-title">B. Alamat dan Domisili</div><table class="data-table">
            <tr><td class="label">Alamat Jalan</td><td class="value">{{ $formatText($alamat) }}</td></tr>
            <tr><td class="label">RT / RW</td><td class="value">{{ $rt ?: $dash }} / {{ $rw ?: $dash }}</td></tr>
            <tr><td class="label">Kelurahan / Desa</td><td class="value">{{ $formatText($kelurahan) }}</td></tr>
            <tr><td class="label">Kecamatan</td><td class="value">{{ $formatText($kecamatan) }}</td></tr>
            <tr><td class="label">Kabupaten / Kota</td><td class="value">{{ $formatText($kabupaten) }}</td></tr>
            <tr><td class="label">Provinsi</td><td class="value">{{ $formatText($provinsi) }}</td></tr>
            <tr><td class="label">Kode Pos</td><td class="value nowrap">{{ $kodePos ?: $dash }}</td></tr>
            <tr><td class="label">Tempat Tinggal</td><td class="value">{{ $formatText($siswa->jenis_tempat_tinggal) }}</td></tr>
        </table></div>
    </td>
</tr></table>

<!-- BARIS 2: orang tua full width -->
<div class="full-section"><div class="section-title">C. Data Orang Tua / Wali</div>
    <table class="data-table"><tr><td class="label">No. Kartu Keluarga</td><td class="value nowrap">{{ $parentValue('no_kk') }}</td></tr></table>
    <table class="parent-table">
        <tr><th>Keterangan</th><th>Data Ayah</th><th>Data Ibu</th></tr>
        <tr><td>Nama Lengkap</td><td class="value">{{ $parentText('nama_ayah') }}</td><td class="value">{{ $parentText('nama_ibu') }}</td></tr>
        <tr><td>NIK</td><td class="value nowrap">{{ $parentValue('nik_ayah') }}</td><td class="value nowrap">{{ $parentValue('nik_ibu') }}</td></tr>
        <tr><td>No. HP</td><td class="value nowrap">{{ $parentValue('hp_ayah') }}</td><td class="value nowrap">{{ $parentValue('hp_ibu') }}</td></tr>
        <tr><td>Pekerjaan</td><td class="value">{{ $pekerjaanOptions[$ortu?->pekerjaan_ayah] ?? $formatText($ortu?->pekerjaan_ayah) }}</td><td class="value">{{ $pekerjaanOptions[$ortu?->pekerjaan_ibu] ?? $formatText($ortu?->pekerjaan_ibu) }}</td></tr>
        <tr><td>Penghasilan Bulanan</td><td class="value">{{ $penghasilanOptions[$ortu?->penghasilan_ayah] ?? $formatText($ortu?->penghasilan_ayah) }}</td><td class="value">{{ $penghasilanOptions[$ortu?->penghasilan_ibu] ?? $formatText($ortu?->penghasilan_ibu) }}</td></tr>
    </table>
</div>

<!-- BARIS 3: pendidikan dan dokumen full width -->
<div class="full-section"><div class="section-title">D. Riwayat Pendidikan &amp; Dokumen</div><table class="data-table education-table">
    <tr><td class="label">Sekolah Asal</td><td class="value">{{ $formatText($siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal) }}</td><td rowspan="2" class="value"><strong>Dokumen Tersimpan</strong><ul class="document-list">@forelse($siswa->dokumen as $dokumen)<li>[x] {{ $dokumen->getJenisDokumenLabel() }}</li>@empty<li>- Belum ada dokumen</li>@endforelse</ul></td></tr>
    <tr><td class="label">NPSN</td><td class="value nowrap">{{ $siswa->sekolahAsal?->npsn ?? $siswa->npsn_asal_sekolah ?? $dash }}</td></tr>
</table></div>

<!-- BARIS 4: tanda tangan dan footer -->
<table class="signature"><tr>
    <td>Mengetahui,<br>Orang Tua / Wali<div class="signature-space"></div><span class="signature-name">........................................</span></td>
    <td>Metro, {{ now()->translatedFormat('d F Y') }}<br>Kepala Madrasah<div class="signature-space"></div><span class="signature-name">{{ $kepalaName }}</span><br>NIP. {{ $kepalaNip }}</td>
</tr></table>
<div class="footer">SIMANSA MAN 1 METRO | Dokumen bersifat rahasia | Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB | Operator: {{ $printedBy }}</div>
</body>
</html>
