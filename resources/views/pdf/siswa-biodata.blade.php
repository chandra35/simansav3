<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 16mm 15mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 9px; line-height: 1.4; }
        .watermark { position: fixed; top: 235px; left: 175px; width: 240px; text-align: center; opacity: .045; z-index: -1000; }
        .watermark img { width: 230px; height: 230px; object-fit: contain; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 9px; }
        .header-table, .profile-table, .two-col, .data-table { width: 100%; border-collapse: collapse; }
        .header-table td, .profile-table td, .two-col td { vertical-align: middle; }
        .logo { width: 55px; height: 55px; object-fit: contain; }
        .school-name { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #102a67; }
        .document-title { margin: 2px 0; font-size: 17px; font-weight: bold; color: #111827; }
        .document-meta { color: #64748b; font-size: 8px; }
        .print-meta { text-align: right; color: #64748b; font-size: 7.5px; }
        .profile { margin-top: 12px; border: 1px solid #cbd5e1; }
        .profile-photo { width: 86px; height: 106px; object-fit: cover; border: 1px solid #cbd5e1; padding: 2px; }
        .photo-empty { width: 86px; height: 106px; border: 1px solid #cbd5e1; text-align: center; padding-top: 43px; color: #64748b; font-size: 7px; }
        .profile-table td { padding: 10px; }
        .student-name { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .profile-line { margin: 2px 0; }
        .section { margin-top: 14px; }
        .section-title { background: #eaf0fb; border-left: 4px solid #1e3a8a; color: #102a67; font-weight: bold; padding: 5px 7px; text-transform: uppercase; font-size: 9px; }
        .two-col td { width: 50%; vertical-align: top; }
        .two-col td:first-child { padding-right: 4px; }
        .two-col td:last-child { padding-left: 4px; }
        .data-table { margin-top: 5px; margin-bottom: 5px; }
        .data-table td { border: 1px solid #dbe3ee; padding: 4px 6px; vertical-align: top; }
        .data-table .label { width: 35%; background: #f8fafc; color: #475569; font-weight: bold; }
        .parent-heading { background: #f8fafc; border: 1px solid #dbe3ee; border-bottom: 0; padding: 5px 6px; font-weight: bold; color: #334155; }
        .footer { position: fixed; bottom: -9mm; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; color: #64748b; font-size: 7px; }
        .footer-right { float: right; }
        .document-list { margin: 0; padding-left: 14px; }
        .document-list li { margin-bottom: 2px; }
    </style>
</head>
<body>
@php
    $dash = '—';
    $ortu = $siswa->ortu;
    $kelas = $kelasAktif?->nama_lengkap ?? $kelasAktif?->nama_kelas ?? $dash;
    $schoolName = $setting?->nama_sekolah ?: 'MAN 1 Metro';
    $value = fn ($field) => filled($siswa->{$field}) ? $siswa->{$field} : $dash;
    $parentValue = fn ($field) => $ortu && filled($ortu->{$field}) ? $ortu->{$field} : $dash;
    $ayahPekerjaan = $ortu?->pekerjaan_ayah;
    $ayahPenghasilan = $ortu?->penghasilan_ayah;
    $ibuPekerjaan = $ortu?->pekerjaan_ibu;
    $ibuPenghasilan = $ortu?->penghasilan_ibu;
    $alamatDomisili = $siswa->alamat_siswa ?: $ortu?->alamat_ortu;
    $rtDomisili = $siswa->rt_siswa ?: $ortu?->rt_ortu;
    $rwDomisili = $siswa->rw_siswa ?: $ortu?->rw_ortu;
    $kelurahanDomisili = $siswa->kelurahanSiswa?->name ?? $ortu?->kelurahan?->name;
    $kecamatanDomisili = $siswa->kecamatanSiswa?->name ?? $ortu?->kecamatan?->name;
    $kabupatenDomisili = $siswa->kabupatenSiswa?->name ?? $ortu?->kabupaten?->name;
    $provinsiDomisili = $siswa->provinsiSiswa?->name ?? $ortu?->provinsi?->name;
    $kodePosDomisili = $siswa->kodepos_siswa ?: $ortu?->kodepos;
@endphp

@if($logoBase64)<div class="watermark"><img src="{{ $logoBase64 }}" alt=""></div>@endif

<div class="header">
    <table class="header-table"><tr>
        <td style="width:68px">@if($logoBase64)<img class="logo" src="{{ $logoBase64 }}" alt="Logo">@endif</td>
        <td><div class="school-name">{{ $schoolName }}</div><div class="document-title">BIODATA PESERTA DIDIK</div><div class="document-meta">Ringkasan data yang tercatat pada Sistem Informasi MAN 1 Metro</div></td>
        <td class="print-meta" style="width:150px">DICETAK PADA<br><strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong><br>NISN: {{ $value('nisn') }}</td>
    </tr></table>
</div>

<div class="profile">
    <table class="profile-table"><tr>
        <td style="width:105px">@if($fotoBase64)<img class="profile-photo" src="{{ $fotoBase64 }}" alt="Foto siswa">@else<div class="photo-empty">FOTO BELUM TERSEDIA</div>@endif</td>
        <td><div class="student-name">{{ $siswa->nama_lengkap }}</div><div class="profile-line"><strong>NISN:</strong> {{ $value('nisn') }}</div><div class="profile-line"><strong>NIS Lokal:</strong> {{ $value('nis_lokal') }}</div><div class="profile-line"><strong>Jenis Kelamin:</strong> {{ $siswa->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}</div><div class="profile-line"><strong>Kelas Aktif:</strong> {{ $kelas }}</div><div class="profile-line"><strong>Status:</strong> {{ $siswa->status_siswa ? \Illuminate\Support\Str::headline($siswa->status_siswa) : 'Aktif' }}</div></td>
    </tr></table>
</div>

<div class="section"><div class="section-title">A. DATA PRIBADI</div><table class="two-col"><tr><td><table class="data-table">
    <tr><td class="label">NIK</td><td>{{ $value('nik') }}</td></tr><tr><td class="label">Tempat, tanggal lahir</td><td>{{ $siswa->tempat_lahir ?: $dash }}, {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr><tr><td class="label">Agama</td><td>{{ $value('agama') }}</td></tr><tr><td class="label">No. HP</td><td>{{ $value('nomor_hp') }}</td></tr>
</table></td><td><table class="data-table">
    <tr><td class="label">Tahun masuk</td><td>{{ $siswa->tahun_masuk ?: $dash }}</td></tr><tr><td class="label">Anak ke / jumlah saudara</td><td>{{ $siswa->anak_ke ?? $dash }} / {{ $siswa->jumlah_saudara ?? $dash }}</td></tr><tr><td class="label">Hobi / cita-cita</td><td>{{ $siswa->hobi ?: $dash }} / {{ $siswa->cita_cita ?: $dash }}</td></tr><tr><td class="label">Email akun</td><td>{{ $siswa->user?->email ?: $dash }}</td></tr>
</table></td></tr></table></div>

<div class="section"><div class="section-title">B. ALAMAT DAN DOMISILI</div><table class="data-table"><tr><td class="label">Alamat domisili</td><td>{{ $alamatDomisili ?: $dash }}</td></tr><tr><td class="label">RT / RW</td><td>{{ $rtDomisili ?: $dash }} / {{ $rwDomisili ?: $dash }}</td></tr><tr><td class="label">Kelurahan / Desa</td><td>{{ $kelurahanDomisili ?: $dash }}</td></tr><tr><td class="label">Kecamatan</td><td>{{ $kecamatanDomisili ?: $dash }}</td></tr><tr><td class="label">Kabupaten / Kota</td><td>{{ $kabupatenDomisili ?: $dash }}</td></tr><tr><td class="label">Provinsi</td><td>{{ $provinsiDomisili ?: $dash }}</td></tr><tr><td class="label">Kode Pos</td><td>{{ $kodePosDomisili ?: $dash }}</td></tr><tr><td class="label">Jenis tempat tinggal</td><td>{{ $value('jenis_tempat_tinggal') }}</td></tr></table></div>

<div class="section"><div class="section-title">C. DATA ORANG TUA</div><table class="two-col"><tr><td><div class="parent-heading">DATA AYAH</div><table class="data-table">
    <tr><td class="label">Nama</td><td>{{ $parentValue('nama_ayah') }}</td></tr><tr><td class="label">NIK / No. HP</td><td>{{ $parentValue('nik_ayah') }} / {{ $parentValue('hp_ayah') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ayahPekerjaan] ?? $parentValue('pekerjaan_ayah') }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ayahPenghasilan] ?? $parentValue('penghasilan_ayah') }}</td></tr>
</table></td><td><div class="parent-heading">DATA IBU</div><table class="data-table">
    <tr><td class="label">Nama</td><td>{{ $parentValue('nama_ibu') }}</td></tr><tr><td class="label">NIK / No. HP</td><td>{{ $parentValue('nik_ibu') }} / {{ $parentValue('hp_ibu') }}</td></tr><tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ibuPekerjaan] ?? $parentValue('pekerjaan_ibu') }}</td></tr><tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ibuPenghasilan] ?? $parentValue('penghasilan_ibu') }}</td></tr>
</table></td></tr></table><table class="data-table"><tr><td class="label">No. Kartu Keluarga</td><td>{{ $parentValue('no_kk') }}</td></tr></table></div>

<div class="section"><div class="section-title">D. RIWAYAT PENDIDIKAN DAN DOKUMEN</div><table class="two-col"><tr><td><table class="data-table"><tr><td class="label">Sekolah asal</td><td>{{ $siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal ?? $dash }}</td></tr><tr><td class="label">NPSN sekolah asal</td><td>{{ $siswa->sekolahAsal?->npsn ?? $siswa->npsn_asal_sekolah ?? $dash }}</td></tr></table></td><td><table class="data-table"><tr><td class="label">Dokumen tercatat</td><td><ul class="document-list">@forelse($siswa->dokumen as $dokumen)<li>{{ $dokumen->getJenisDokumenLabel() }}</li>@empty<li>Belum ada dokumen tercatat.</li>@endforelse</ul></td></tr></table></td></tr></table></div>

<div class="footer">SIMANSA · {{ $schoolName }} · Dokumen bersifat rahasia<span class="footer-right">Biodata Peserta Didik</span></div>
</body>
</html>
