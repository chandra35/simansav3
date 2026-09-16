<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 15mm; }
        * { box-sizing: border-box; }
        body { color: #17233b; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.45; }
        .header { border-bottom: 2px solid #304ffe; padding-bottom: 10px; margin-bottom: 14px; }
        .school { color: #304ffe; font-size: 10px; font-weight: bold; letter-spacing: .9px; }
        h1 { margin: 2px 0; font-size: 19px; letter-spacing: .4px; }
        .subtitle { color: #6b778c; font-size: 9px; }
        .profile { width: 100%; border-collapse: collapse; margin-bottom: 13px; }
        .profile td { vertical-align: top; }
        .photo { width: 92px; height: 116px; border: 1px solid #cdd5df; padding: 3px; object-fit: cover; }
        .photo-placeholder { width: 92px; height: 116px; border: 1px dashed #aab4c3; text-align: center; color: #8190a5; padding-top: 49px; }
        .name { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .identity { color: #52627a; margin-top: 3px; }
        .badge { display: inline-block; background: #e9edff; color: #283593; border-radius: 9px; padding: 3px 7px; margin-top: 7px; font-size: 8px; }
        h2 { font-size: 10px; color: #243caa; text-transform: uppercase; letter-spacing: .45px; margin: 14px 0 5px; border-left: 3px solid #5168f2; padding-left: 6px; }
        table.details { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.details td { border: 1px solid #dce2ea; padding: 4px 6px; vertical-align: top; }
        table.details td.label { width: 27%; color: #52627a; background: #f7f9fc; font-weight: bold; }
        .two-col { width: 100%; border-collapse: collapse; }
        .two-col td { width: 50%; vertical-align: top; }
        .two-col td:first-child { padding-right: 5px; }
        .two-col td:last-child { padding-left: 5px; }
        .muted { color: #77849a; }
        .footer { border-top: 1px solid #dce2ea; margin-top: 17px; padding-top: 6px; color: #77849a; font-size: 8px; }
    </style>
</head>
<body>
@php
    $dash = '—';
    $ortu = $siswa->ortu;
    $kelas = $kelasAktif?->nama_lengkap ?? $kelasAktif?->nama_kelas ?? $dash;
    $value = fn ($field) => filled($siswa->{$field}) ? $siswa->{$field} : $dash;
    $parentValue = fn ($field) => $ortu && filled($ortu->{$field}) ? $ortu->{$field} : $dash;
@endphp
<div class="header">
    <div class="school">SIMANSA · MAN 1 METRO</div>
    <h1>BIODATA PESERTA DIDIK</h1>
    <div class="subtitle">Ringkasan data yang tercatat pada sistem · dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
</div>

<table class="profile"><tr>
    <td style="width:105px;">
        @if($fotoBase64)<img src="{{ $fotoBase64 }}" class="photo" alt="Foto siswa">@else<div class="photo-placeholder">Foto belum tersedia</div>@endif
    </td>
    <td>
        <div class="name">{{ $siswa->nama_lengkap }}</div>
        <div class="identity">NISN: <strong>{{ $value('nisn') }}</strong> &nbsp; | &nbsp; NIS Lokal: <strong>{{ $value('nis_lokal') }}</strong></div>
        <div class="identity">Kelas aktif: <strong>{{ $kelas }}</strong></div>
        <span class="badge">{{ $siswa->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }} · {{ $siswa->status_siswa ? \Illuminate\Support\Str::headline($siswa->status_siswa) : 'Aktif' }}</span>
    </td>
</tr></table>

<h2>Data pribadi</h2>
<table class="two-col"><tr><td><table class="details">
    <tr><td class="label">NIK</td><td>{{ $value('nik') }}</td></tr>
    <tr><td class="label">Tempat, tanggal lahir</td><td>{{ $siswa->tempat_lahir ?: $dash }}, {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr>
    <tr><td class="label">Agama</td><td>{{ $value('agama') }}</td></tr>
    <tr><td class="label">Anak ke / jumlah saudara</td><td>{{ $siswa->anak_ke ?? $dash }} / {{ $siswa->jumlah_saudara ?? $dash }}</td></tr>
    <tr><td class="label">No. HP</td><td>{{ $value('nomor_hp') }}</td></tr>
</table></td><td><table class="details">
    <tr><td class="label">Tahun masuk</td><td>{{ $siswa->tahun_masuk ?: $dash }}</td></tr>
    <tr><td class="label">Asal siswa</td><td>{{ $siswa->asal_siswa ? \Illuminate\Support\Str::headline($siswa->asal_siswa) : $dash }}</td></tr>
    <tr><td class="label">Jenis tinggal</td><td>{{ $value('jenis_tempat_tinggal') }}</td></tr>
    <tr><td class="label">Hobi / cita-cita</td><td>{{ $siswa->hobi ?: $dash }} / {{ $siswa->cita_cita ?: $dash }}</td></tr>
    <tr><td class="label">Email akun</td><td>{{ $siswa->user?->email ?: $dash }}</td></tr>
</table></td></tr></table>

<h2>Alamat siswa</h2>
<table class="details"><tr><td class="label">Alamat</td><td>{{ $siswa->getAlamatLengkapSiswa() ?: $dash }}</td></tr><tr><td class="label">RT/RW · Kode Pos</td><td>{{ $siswa->rt_siswa ?: $dash }} / {{ $siswa->rw_siswa ?: $dash }} · {{ $siswa->kodepos_siswa ?: $dash }}</td></tr></table>

<h2>Data orang tua / wali</h2>
<table class="two-col"><tr><td><table class="details">
    <tr><td class="label">Ayah</td><td>{{ $parentValue('nama_ayah') }}</td></tr>
    <tr><td class="label">NIK / No. HP</td><td>{{ $parentValue('nik_ayah') }} / {{ $parentValue('hp_ayah') }}</td></tr>
    <tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ayah] ?? $parentValue('pekerjaan_ayah') }}</td></tr>
    <tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ayah] ?? $parentValue('penghasilan_ayah') }}</td></tr>
</table></td><td><table class="details">
    <tr><td class="label">Ibu</td><td>{{ $parentValue('nama_ibu') }}</td></tr>
    <tr><td class="label">NIK / No. HP</td><td>{{ $parentValue('nik_ibu') }} / {{ $parentValue('hp_ibu') }}</td></tr>
    <tr><td class="label">Pekerjaan</td><td>{{ $pekerjaanOptions[$ortu?->pekerjaan_ibu] ?? $parentValue('pekerjaan_ibu') }}</td></tr>
    <tr><td class="label">Penghasilan</td><td>{{ $penghasilanOptions[$ortu?->penghasilan_ibu] ?? $parentValue('penghasilan_ibu') }}</td></tr>
</table></td></tr></table>
<table class="details"><tr><td class="label">Alamat orang tua</td><td>{{ $ortu?->getAlamatLengkap() ?: $dash }}</td></tr><tr><td class="label">No. KK</td><td>{{ $parentValue('no_kk') }}</td></tr></table>

<h2>Sekolah asal dan dokumen</h2>
<table class="details"><tr><td class="label">Sekolah asal</td><td>{{ $siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal ?? $dash }}</td></tr><tr><td class="label">NPSN</td><td>{{ $siswa->sekolahAsal?->npsn ?? $siswa->npsn_asal_sekolah ?? $dash }}</td></tr><tr><td class="label">Dokumen tercatat</td><td>{{ $siswa->dokumen->isNotEmpty() ? $siswa->dokumen->map(fn ($dokumen) => $dokumen->getJenisDokumenLabel())->implode(', ') : 'Belum ada dokumen' }}</td></tr></table>

<div class="footer">Dokumen ini dihasilkan oleh SIMANSA. Pastikan data diperbarui melalui pihak madrasah bila ditemukan ketidaksesuaian.</div>
</body>
</html>
