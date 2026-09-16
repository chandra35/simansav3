<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; line-height: 1.25; color: #111; }
        table { border-collapse: collapse; }
        .kop, .overview, .main-grid, .split, .data-table, .signature { width: 100%; }
        .kop td, .overview td, .main-grid td, .split td, .signature td { vertical-align: top; }
        .kop { height: 62px; }
        .kop-logo { width: 49px; height: 55px; object-fit: contain; }
        .kop-center { text-align: center; vertical-align: middle !important; line-height: 1.1; }
        .kop-kemenag { font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        .kop-school { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .kop-address { font-size: 6.5pt; }
        .divider { height: 3px; margin: 3px 0 6px; border-top: 2px solid #111; border-bottom: 1px solid #111; }
        .title { margin: 0 0 6px; text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .overview { margin-bottom: 6px; border: 1px solid #333; }
        .overview td { padding: 4px 6px; }
        .overview-data { vertical-align: middle !important; }
        .overview-row { margin: 1px 0; }
        .photo { width: 30mm; height: 40mm; object-fit: cover; border: 1px solid #333; padding: 1px; }
        .photo-empty { width: 30mm; height: 40mm; border: 1px solid #333; padding-top: 14mm; text-align: center; color: #555; font-size: 7pt; }
        .main-grid { table-layout: fixed; }
        .main-grid > tr > td, .main-grid > tbody > tr > td { width: 50%; }
        .main-grid > tr > td:first-child, .main-grid > tbody > tr > td:first-child { padding-right: 3px; }
        .main-grid > tr > td:last-child, .main-grid > tbody > tr > td:last-child { padding-left: 3px; }
        .section { margin-bottom: 5px; }
        .section-title { padding: 3px 5px; border: 1px solid #333; border-bottom: 0; background: #ededed; font-size: 8.5pt; font-weight: bold; text-transform: uppercase; }
        .data-table { table-layout: fixed; }
        .data-table td { padding: 2px 4px; border: 1px solid #777; line-height: 1.22; vertical-align: top; word-wrap: break-word; }
        .data-table .label { width: 34%; background: #f7f7f7; font-weight: bold; }
        .split { table-layout: fixed; }
        .split td { width: 50%; }
        .split td:first-child { padding-right: 2px; }
        .split td:last-child { padding-left: 2px; }
        .parent-title { padding: 3px 4px; border: 1px solid #777; border-bottom: 0; background: #f7f7f7; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        .doc-list { margin: 0; padding-left: 13px; }
        .doc-list li { margin: 0; line-height: 1.2; }
        .signature { margin-top: 5px; table-layout: fixed; }
        .signature td { width: 50%; text-align: center; font-size: 8.5pt; }
        .signature-space { height: 38px; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 4px; padding-top: 3px; border-top: 1px solid #777; text-align: center; font-size: 6.5pt; font-style: italic; white-space: nowrap; }
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
    <td style="width:58px;">@if($logoKemenagBase64)<img class="kop-logo" src="{{ $logoKemenagBase64 }}" alt="Logo Kemenag">@endif</td>
    <td class="kop-center"><div class="kop-kemenag">Kementerian Agama Republik Indonesia</div><div class="kop-kemenag">Kantor Kementerian Agama Kota Metro</div><div class="kop-school">{{ $schoolName }}</div><div class="kop-address">{{ $setting?->alamat_lengkap ?: $setting?->alamat ?: 'Alamat madrasah belum diatur' }}{{ $setting?->kode_pos ? ' - Kode Pos '.$setting->kode_pos : '' }}<br>{{ $setting?->website ?: '' }}{{ $setting?->website && $setting?->email ? ' - ' : '' }}{{ $setting?->email ?: '' }}</div></td>
    <td style="width:58px; text-align:right;">@if($logoBase64)<img class="kop-logo" src="{{ $logoBase64 }}" alt="Logo Madrasah">@endif</td>
</tr></table>
<div class="divider"></div>
<div class="title">Biodata Peserta Didik</div>

<table class="overview"><tr>
    <td class="overview-data"><div class="overview-row"><strong>Nama Lengkap</strong> : {{ $formatText($siswa->nama_lengkap) }}</div><div class="overview-row"><strong>NISN</strong> : {{ $value('nisn') }}</div><div class="overview-row"><strong>NIS Lokal</strong> : {{ $value('nis_lokal') }}</div><div class="overview-row"><strong>Kelas Aktif</strong> : {{ $kelas }}</div><div class="overview-row"><strong>Status</strong> : {{ $formatText($siswa->status_siswa ?: 'aktif') }}</div></td>
    <td style="width:32mm; text-align:right;">@if($fotoBase64)<img class="photo" src="{{ $fotoBase64 }}" alt="Foto siswa">@else<div class="photo-empty">FOTO 3 x 4</div>@endif</td>
</tr></table>

<table class="main-grid"><tr>
    <td>
        <div class="section"><div class="section-title">A. Data Pribadi</div><table class="data-table"><tr><td class="label">NIK</td><td>{{ $value('nik') }}</td></tr><tr><td class="label">Tempat, Tgl Lahir</td><td>{{ $formatText($siswa->tempat_lahir) }}, {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr><tr><td class="label">Agama</td><td>{{ $formatText($siswa->agama) }}</td></tr><tr><td class="label">No. HP</td><td>{{ $value('nomor_hp') }}</td></tr><tr><td class="label">Tahun Masuk</td><td>{{ $siswa->tahun_masuk ?: $dash }}</td></tr><tr><td class="label">Anak Ke / Saudara</td><td>{{ $siswa->anak_ke ?? $dash }} / {{ $siswa->jumlah_saudara ?? $dash }}</td></tr><tr><td class="label">Hobi / Cita-cita</td><td>{{ $formatText($siswa->hobi) }} / {{ $formatText($siswa->cita_cita) }}</td></tr><tr><td class="label">Email</td><td>{{ $siswa->user?->email ?: $dash }}</td></tr></table></div>
        <div class="section"><div class="section-title">B. Alamat dan Domisili</div><table class="data-table"><tr><td class="label">Alamat Jalan</td><td>{{ $formatText($alamat) }}</td></tr><tr><td class="label">RT / RW</td><td>{{ $rt ?: $dash }} / {{ $rw ?: $dash }}</td></tr><tr><td class="label">Kelurahan / Desa</td><td>{{ $formatText($kelurahan) }}</td></tr><tr><td class="label">Kecamatan</td><td>{{ $formatText($kecamatan) }}</td></tr><tr><td class="label">Kabupaten / Kota</td><td>{{ $formatText($kabupaten) }}</td></tr><tr><td class="label">Provinsi</td><td>{{ $formatText($provinsi) }}</td></tr><tr><td class="label">Kode Pos</td><td>{{ $kodePos ?: $dash }}</td></tr><tr><td class="label">Tempat Tinggal</td><td>{{ $formatText($siswa->jenis_tempat_tinggal) }}</td></tr></table></div>
    </td>
    <td>
        <div class="section"><div class="section-title">C. Data Orang Tua / Wali</div><table class="data-table"><tr><td class="label">No. Kartu Keluarga</td><td>{{ $parentValue('no_kk') }}</td></tr></table><table class="split"><tr><td><div class="parent-title">Data Ayah</div><table class="data-table"><tr><td class="label">Nama</td><td>{{ $formatText($parentValue('nama_ayah')) }}</td></tr><tr><td class="label">NIK</td><td>{{ $parentValue('nik_ayah') }}</td></tr><tr><td class="label">No. HP</td><td>{{ $parentValue('hp_ayah') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ayah] ?? $formatText($ortu?->pekerjaan_ayah) }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ayah] ?? $formatText($ortu?->penghasilan_ayah) }}</td></tr></table></td><td><div class="parent-title">Data Ibu</div><table class="data-table"><tr><td class="label">Nama</td><td>{{ $formatText($parentValue('nama_ibu')) }}</td></tr><tr><td class="label">NIK</td><td>{{ $parentValue('nik_ibu') }}</td></tr><tr><td class="label">No. HP</td><td>{{ $parentValue('hp_ibu') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ibu] ?? $formatText($ortu?->pekerjaan_ibu) }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ibu] ?? $formatText($ortu?->penghasilan_ibu) }}</td></tr></table></td></tr></table></div>
        <div class="section"><div class="section-title">D. Riwayat Pendidikan & Dokumen</div><table class="data-table"><tr><td class="label">Sekolah Asal</td><td>{{ $formatText($siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal) }}</td></tr><tr><td class="label">NPSN</td><td>{{ $siswa->sekolahAsal?->npsn ?? $siswa->npsn_asal_sekolah ?? $dash }}</td></tr><tr><td class="label">Dokumen Tersimpan</td><td><ul class="doc-list">@forelse($siswa->dokumen as $dokumen)<li>✓ {{ $dokumen->getJenisDokumenLabel() }}</li>@empty<li>- Belum ada dokumen</li>@endforelse</ul></td></tr></table></div>
    </td>
</tr></table>

<table class="signature"><tr><td>Mengetahui,<br>Orang Tua / Wali Siswa<div class="signature-space"></div><strong>........................................</strong></td><td>Metro, {{ now()->translatedFormat('d F Y') }}<br>Kepala Madrasah<div class="signature-space"></div><strong><u>{{ $kepalaName }}</u></strong><br>NIP. {{ $kepalaNip }}</td></tr></table>
<div class="footer">SIMANSA MAN 1 METRO | Dokumen bersifat rahasia | Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB | Operator: {{ $printedBy }}</div>
</body>
</html>
