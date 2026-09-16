<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #111; line-height: 1.35; }
        .kop, .overview, .split, .data-table, .signature { width: 100%; border-collapse: collapse; }
        .kop td, .overview td, .split td, .signature td { vertical-align: middle; }
        .kop-logo { width: 72px; height: 72px; object-fit: contain; }
        .kop-center { text-align: center; line-height: 1.2; }
        .kop-kemenag { font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .kop-school { font-size: 15pt; font-weight: bold; text-transform: uppercase; }
        .kop-address { font-size: 8pt; }
        .divider { border-top: 2px solid #111; border-bottom: 1px solid #111; height: 3px; margin: 5px 0 13px; }
        .title { text-align: center; font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0 0 12px; }
        .overview { page-break-inside: avoid; margin-bottom: 11px; }
        .overview-data { border: 1px solid #333; padding: 8px 10px; }
        .overview-row { margin: 2px 0; }
        .photo { width: 30mm; height: 40mm; object-fit: cover; border: 1px solid #333; padding: 2px; }
        .photo-empty { width: 30mm; height: 40mm; border: 1px solid #333; text-align: center; padding-top: 14mm; font-size: 8pt; color: #555; }
        .section { page-break-inside: avoid; margin-top: 10px; }
        .section-title { background: #ececec; border: 1px solid #333; border-bottom: 0; padding: 5px 7px; font-weight: bold; text-transform: uppercase; }
        .data-table { page-break-inside: avoid; }
        .data-table td { border: 1px solid #777; padding: 5px 7px; vertical-align: top; }
        .data-table .label { width: 30%; background: #f7f7f7; font-weight: bold; }
        .split td { width: 50%; vertical-align: top; }
        .split td:first-child { padding-right: 4px; }
        .split td:last-child { padding-left: 4px; }
        .parent-title { border: 1px solid #777; border-bottom: 0; padding: 5px 7px; font-weight: bold; text-transform: uppercase; background: #f7f7f7; }
        .doc-list { margin: 0; padding-left: 16px; }
        .doc-list li { margin: 2px 0; }
        .signature { page-break-inside: avoid; margin-top: 25px; }
        .signature td { width: 50%; text-align: center; vertical-align: top; }
        .signature-space { height: 52px; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .footer { position: fixed; bottom: -9mm; left: 0; right: 0; border-top: 1px solid #777; padding-top: 4px; font-size: 8pt; font-style: italic; color: #444; text-align: center; }
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
    $kepalaName = $kepalaMadrasah?->name ?: '................................................';
    $kepalaNip = $kepalaMadrasah?->gtk?->nip ?: '................................................';
@endphp

<table class="kop"><tr>
    <td style="width:88px; text-align:left;">@if($logoKemenagBase64)<img class="kop-logo" src="{{ $logoKemenagBase64 }}" alt="Logo Kemenag">@endif</td>
    <td class="kop-center"><div class="kop-kemenag">Kementerian Agama Republik Indonesia</div><div class="kop-kemenag">Kantor Kementerian Agama Kota Metro</div><div class="kop-school">{{ $schoolName }}</div><div class="kop-address">{{ $setting?->alamat_lengkap ?: $setting?->alamat ?: 'Alamat madrasah belum diatur' }}{{ $setting?->kode_pos ? ' · Kode Pos '.$setting->kode_pos : '' }}<br>{{ $setting?->website ?: '' }}{{ $setting?->website && $setting?->email ? ' · ' : '' }}{{ $setting?->email ?: '' }}</div></td>
    <td style="width:88px; text-align:right;">@if($logoBase64)<img class="kop-logo" src="{{ $logoBase64 }}" alt="Logo Madrasah">@endif</td>
</tr></table>
<div class="divider"></div>

<div class="title">Biodata Peserta Didik</div>
<table class="overview"><tr>
    <td class="overview-data"><div class="overview-row"><strong>Nama Lengkap</strong> : {{ $formatText($siswa->nama_lengkap) }}</div><div class="overview-row"><strong>NISN</strong> : {{ $value('nisn') }}</div><div class="overview-row"><strong>NIS Lokal</strong> : {{ $value('nis_lokal') }}</div><div class="overview-row"><strong>Kelas Aktif</strong> : {{ $kelas }}</div><div class="overview-row"><strong>Status</strong> : {{ $formatText($siswa->status_siswa ?: 'aktif') }}</div></td>
    <td style="width:38mm; text-align:right;">@if($fotoBase64)<img class="photo" src="{{ $fotoBase64 }}" alt="Foto siswa">@else<div class="photo-empty">FOTO 3 × 4</div>@endif</td>
</tr></table>

<div class="section"><div class="section-title">A. Data Pribadi</div><table class="split"><tr><td><table class="data-table"><tr><td class="label">NIK</td><td>{{ $value('nik') }}</td></tr><tr><td class="label">Tempat, Tanggal Lahir</td><td>{{ $formatText($siswa->tempat_lahir) }}, {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr><tr><td class="label">Agama</td><td>{{ $formatText($siswa->agama) }}</td></tr><tr><td class="label">No. HP</td><td>{{ $value('nomor_hp') }}</td></tr></table></td><td><table class="data-table"><tr><td class="label">Tahun Masuk</td><td>{{ $siswa->tahun_masuk ?: $dash }}</td></tr><tr><td class="label">Anak Ke / Jml. Saudara</td><td>{{ $siswa->anak_ke ?? $dash }} / {{ $siswa->jumlah_saudara ?? $dash }}</td></tr><tr><td class="label">Hobi / Cita-cita</td><td>{{ $formatText($siswa->hobi) }} / {{ $formatText($siswa->cita_cita) }}</td></tr><tr><td class="label">Email</td><td>{{ $siswa->user?->email ?: $dash }}</td></tr></table></td></tr></table></div>

<div class="section"><div class="section-title">B. Alamat dan Domisili</div><table class="data-table"><tr><td class="label">Alamat Jalan</td><td>{{ $formatText($alamat) }}</td></tr><tr><td class="label">RT / RW</td><td>{{ $rt ?: $dash }} / {{ $rw ?: $dash }}</td></tr><tr><td class="label">Kelurahan / Desa</td><td>{{ $formatText($kelurahan) }}</td></tr><tr><td class="label">Kecamatan</td><td>{{ $formatText($kecamatan) }}</td></tr><tr><td class="label">Kabupaten / Kota</td><td>{{ $formatText($kabupaten) }}</td></tr><tr><td class="label">Provinsi</td><td>{{ $formatText($provinsi) }}</td></tr><tr><td class="label">Kode Pos</td><td>{{ $kodePos ?: $dash }}</td></tr><tr><td class="label">Status Tempat Tinggal</td><td>{{ $formatText($siswa->jenis_tempat_tinggal) }}</td></tr></table></div>

<div class="section"><div class="section-title">C. Data Orang Tua / Wali</div><table class="data-table"><tr><td class="label">No. Kartu Keluarga</td><td>{{ $parentValue('no_kk') }}</td></tr></table><table class="split"><tr><td><div class="parent-title">Data Ayah</div><table class="data-table"><tr><td class="label">Nama</td><td>{{ $formatText($parentValue('nama_ayah')) }}</td></tr><tr><td class="label">NIK</td><td>{{ $parentValue('nik_ayah') }}</td></tr><tr><td class="label">No. HP</td><td>{{ $parentValue('hp_ayah') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ayah] ?? $formatText($ortu?->pekerjaan_ayah) }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ayah] ?? $formatText($ortu?->penghasilan_ayah) }}</td></tr></table></td><td><div class="parent-title">Data Ibu</div><table class="data-table"><tr><td class="label">Nama</td><td>{{ $formatText($parentValue('nama_ibu')) }}</td></tr><tr><td class="label">NIK</td><td>{{ $parentValue('nik_ibu') }}</td></tr><tr><td class="label">No. HP</td><td>{{ $parentValue('hp_ibu') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ibu] ?? $formatText($ortu?->pekerjaan_ibu) }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ibu] ?? $formatText($ortu?->penghasilan_ibu) }}</td></tr></table></td></tr></table></div>

<div class="section"><div class="section-title">D. Riwayat Pendidikan dan Dokumen</div><table class="split"><tr><td><table class="data-table"><tr><td class="label">Sekolah Asal</td><td>{{ $formatText($siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal) }}</td></tr><tr><td class="label">NPSN</td><td>{{ $siswa->sekolahAsal?->npsn ?? $siswa->npsn_asal_sekolah ?? $dash }}</td></tr></table></td><td><table class="data-table"><tr><td class="label">Dokumen Tersimpan</td><td><ul class="doc-list">@forelse($siswa->dokumen as $dokumen)<li>✓ {{ $dokumen->getJenisDokumenLabel() }}</li>@empty<li>- Belum ada dokumen tersimpan</li>@endforelse</ul></td></tr></table></td></tr></table></div>

<table class="signature"><tr><td>Mengetahui,<br>Orang Tua / Wali Siswa<div class="signature-space"></div><div class="signature-name">................................................</div></td><td>Metro, {{ now()->translatedFormat('d F Y') }}<br>Kepala Madrasah<div class="signature-space"></div><div class="signature-name">{{ $kepalaName }}</div>NIP. {{ $kepalaNip }}</td></tr></table>
<div class="footer">SIMANSA MAN 1 METRO &nbsp;|&nbsp; Dokumen bersifat rahasia &nbsp;|&nbsp; Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp; Operator: {{ $printedBy }}</div>
</body>
</html>
