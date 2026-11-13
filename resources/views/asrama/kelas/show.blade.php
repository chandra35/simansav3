@extends('adminlte::page')
@section('title', $kelas->nama_kelas.' · Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content')
@include('asrama._alerts')
@php
    $isCustom=$kelas->kelas_id === null;
    $heroTitle=$kelas->kelasReguler?->nama_kelas ?? $kelas->nama_kelas;
    $heroDescription=($isCustom ? 'Rombel Mandiri' : 'Rombel SIMANSA (Kampus 2)').' · '.$kelas->tahunPelajaran->nama.' · Tingkat '.$kelas->tingkat.' · '.ucfirst($kelas->jenis).($kelas->is_active ? '' : ' · Nonaktif');
    $heroAction='<a class="btn btn-light" href="'.route('asrama.kelas.index',['tahun_pelajaran_id'=>$kelas->tahun_pelajaran_id]).'"><i class="fas fa-arrow-left mr-1"></i> Daftar Rombel</a>';
    $siswaReguler=$kelas->kelasReguler?->siswaAktif()->count() ?? 0;
    $totalSantri=$kelas->anggotaAktif->count();
    $punyaKamar=$kelas->anggotaAktif->filter(fn($a)=>$a->santri->kamarAktif)->count();
    $pengasuhUtama=$kelas->pengasuhRombel->firstWhere('is_primary',true)?->pengasuh?->gtk?->nama_lengkap;
    $pengampuGanjil=$kelas->pengampu->where('semester','Ganjil')->count();
    $pengampuGenap=$kelas->pengampu->where('semester','Genap')->count();
@endphp
@include('asrama._hero')
<div class="row">
<div class="col-md-3 col-6 mb-3"><div class="asrama-stat asrama-stat--primary"><span>Santri Aktif</span><strong>{{ $totalSantri }}</strong><small>{{ $isCustom ? 'Kapasitas: '.$kelas->kapasitas.' santri (Mandiri)' : 'dari '.$siswaReguler.' siswa rombel SIMANSA' }}</small></div></div>
<div class="col-md-3 col-6 mb-3"><div class="asrama-stat asrama-stat--info"><span>Pengasuh</span><strong>{{ $kelas->pengasuhRombel->count() }}</strong><small>{{ $pengasuhUtama ? 'Utama: '.$pengasuhUtama : 'Pengasuh utama belum ada' }}</small></div></div>
<div class="col-md-3 col-6 mb-3"><div class="asrama-stat asrama-stat--warning"><span>Pengampu Mapel</span><strong>{{ $kelas->pengampu->count() }}</strong><small>Ganjil {{ $pengampuGanjil }} · Genap {{ $pengampuGenap }}</small></div></div>
<div class="col-md-3 col-6 mb-3"><div class="asrama-stat asrama-stat--success"><span>Sudah Dapat Kamar</span><strong>{{ $punyaKamar }}</strong><small>{{ $totalSantri - $punyaKamar }} santri belum dapat kamar</small></div></div>
</div>
<div class="row">
<div class="col-xl-8">
<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Santri Rombel</h3><p>{{ $isCustom ? 'Santri asrama yang ditempatkan di rombel mandiri ini.' : 'Santri berasal dari anggota aktif rombel SIMANSA ini.' }}</p></div><button class="btn btn-sm btn-info" data-toggle="modal" data-target="#assignStudents"><i class="fas fa-{{ $isCustom ? 'user-plus' : 'sync-alt' }} mr-1"></i> {{ $isCustom ? 'Tambah Santri' : 'Sinkronkan Santri' }}</button></div>
<div class="table-responsive"><table class="table asrama-table asrama-datatable" data-order='[[1,"asc"]]'><thead><tr><th>No</th><th>Santri</th><th>Nomor Induk</th><th>Kamar</th><th>Pengasuh</th><th>Jabatan</th><th class="no-sort"></th></tr></thead><tbody>
@forelse($kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap') as $index=>$item)
<tr><td>{{ $item->nomor_urut ?? $index+1 }}</td><td><strong>{{ $item->santri->siswa->nama_lengkap }}</strong><br><small>{{ $item->santri->siswa->kelasSaatIni ? 'Kelas: '.$item->santri->siswa->kelasSaatIni->nama_kelas.' · ' : '' }}NISN {{ $item->santri->siswa->nisn ?: '-' }}</small></td><td><code>{{ $item->santri->nomor_induk_asrama }}</code></td><td>@if($item->santri->kamarAktif?->kamar)<span class="badge badge-light border"><i class="fas fa-bed mr-1"></i>{{ $item->santri->kamarAktif->kamar->nama }}</span>@else<small class="text-warning"><i class="fas fa-exclamation-circle"></i> Belum dapat kamar</small>@endif</td><td>@if($item->pengasuhAssignment?->rombelPengasuh?->pengasuh?->gtk){{ $item->pengasuhAssignment->rombelPengasuh->pengasuh->gtk->nama_lengkap }}@else<small class="text-warning"><i class="fas fa-exclamation-circle"></i> Belum dibagi</small>@endif</td><td>@if($item->is_ketua_kelas)<span class="badge badge-warning"><i class="fas fa-star mr-1"></i>Ketua</span>@else Santri @endif</td><td class="text-right"><form method="post" action="{{ route('asrama.kelas.santri.destroy',[$kelas,$item]) }}" data-asrama-loading data-confirm="Lepas santri ini dari rombel Asrama?" data-loading-title="Melepas santri">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger asrama-icon-button" title="Lepas santri dari rombel"><i class="fas fa-times"></i></button></form></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-users"></i>Belum ada santri pada rombel ini. Klik "{{ $isCustom ? 'Tambah Santri' : 'Sinkronkan Santri' }}" untuk menambahkan anggota rombel.</td></tr>@endforelse
</tbody></table></div></div>

<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Pengasuh Rombel</h3><p>Satu rombel dapat mempunyai dua atau lebih pengasuh; tiap santri memiliki satu pengasuh utama.</p></div><button class="btn btn-sm btn-info" data-toggle="modal" data-target="#addCaregiver"><i class="fas fa-user-plus mr-1"></i> Tambah Pengasuh</button></div>
<div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Pengasuh</th><th>Status</th><th>Santri Binaan</th><th>Aksi</th></tr></thead><tbody>
@forelse($kelas->pengasuhRombel as $caregiver)
<tr><td><strong>{{ $caregiver->pengasuh->gtk->nama_lengkap }}</strong><br><small>{{ $caregiver->pengasuh->jabatan }}</small></td><td>@if($caregiver->is_primary)<span class="asrama-badge asrama-badge--active">Pengasuh utama</span>@else<span class="asrama-badge asrama-badge--muted">Pendamping</span>@endif</td><td>{{ $caregiver->santriAssignments->where('is_active',true)->count() }} santri</td><td><button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#assignCaregiver{{ $caregiver->id }}"><i class="fas fa-random mr-1"></i> Bagi Santri</button> <form class="d-inline" method="post" action="{{ route('asrama.kelas.pengasuh.destroy',[$kelas,$caregiver]) }}" data-asrama-loading data-confirm="Lepas pengasuh dari rombel ini?" data-loading-title="Melepas pengasuh">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button></form></td></tr>
@empty<tr><td colspan="4" class="asrama-empty"><i class="fas fa-user-tie"></i>Pengasuh rombel belum ditetapkan.</td></tr>@endforelse
</tbody></table></div></div>

<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Pengampu Mata Pelajaran</h3><p>Pengampu memperoleh menu input nilai sesuai penugasannya.</p></div><button class="btn btn-sm btn-info" data-toggle="modal" data-target="#addTeacher"><i class="fas fa-plus mr-1"></i> Tambah Pengampu</button></div>
<div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Mapel</th><th>Semester</th><th>Pengampu</th><th></th></tr></thead><tbody>
@forelse($kelas->pengampu->sortBy('mapel.urutan') as $item)<tr><td><span class="asrama-arab">{{ $item->mapel->nama_arab }}</span><br><small>{{ $item->mapel->nama_latin }}</small></td><td>{{ $item->semester }}</td><td>{{ $item->asatidz->gtk->nama_lengkap }}</td><td class="text-right"><a class="btn btn-sm btn-outline-info" href="{{ route('asrama.nilai.edit',$item) }}"><i class="fas fa-pen"></i></a> <form class="d-inline" method="post" action="{{ route('asrama.kelas.pengampu.destroy',[$kelas,$item]) }}" data-asrama-loading data-confirm="Hapus penugasan mapel ini?" data-loading-title="Menghapus penugasan">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
@empty<tr><td colspan="4" class="asrama-empty"><i class="fas fa-book"></i>Belum ada pengampu.</td></tr>@endforelse
</tbody></table></div></div>
</div>
<div class="col-xl-4">
<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Ketua Rombel</h3><p>Mengikuti kebutuhan Asrama.</p></div></div><div class="asrama-panel__body asrama-form"><form method="post" action="{{ route('asrama.kelas.ketua',$kelas) }}" data-asrama-loading data-loading-title="Menetapkan ketua rombel">@csrf<select name="anggota_id" class="form-control asrama-select mb-3" data-placeholder="Belum ditetapkan" data-allow-clear="1"><option value=""></option>@foreach($kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap') as $item)<option value="{{ $item->id }}" @selected($item->is_ketua_kelas)>{{ $item->santri->siswa->nama_lengkap }}</option>@endforeach</select><button class="btn btn-info btn-block"><i class="fas fa-save mr-1"></i> Simpan Ketua</button></form></div></div>
<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Konfigurasi</h3><p>{{ $isCustom ? 'Rombel Mandiri Asrama' : 'Nama rombel tetap mengikuti SIMANSA.' }}</p></div></div><div class="asrama-panel__body">
<table class="table table-sm table-borderless mb-3" style="font-size:.88rem">
<tr><td class="text-muted" style="width:45%">Tipe Rombel</td><td>@if($isCustom)<span class="badge text-white" style="background:#6f42c1;"><i class="fas fa-feather-alt mr-1"></i>Mandiri / Custom</span>@else<span class="badge badge-info"><i class="fas fa-sync-alt mr-1"></i>Kampus 2</span>@endif</td></tr>
<tr><td class="text-muted">Nama Rombel</td><td><strong>{{ $kelas->kelasReguler?->nama_kelas ?? $kelas->nama_kelas }}</strong></td></tr>
<tr><td class="text-muted">Program</td><td>@if($kelas->is_tahfidz)<span class="badge badge-success"><i class="fas fa-quran mr-1"></i>Tahfizh</span>@else<span class="badge badge-info">Reguler</span>@endif</td></tr>
<tr><td class="text-muted">Nama Arab</td><td class="asrama-arab">{{ $kelas->nama_arab ?: '-' }}</td></tr>
<tr><td class="text-muted">Tahun Pelajaran</td><td>{{ $kelas->tahunPelajaran->nama }}</td></tr>
<tr><td class="text-muted">Tingkat</td><td>{{ $kelas->tingkat }}</td></tr>
<tr><td class="text-muted">Kelompok</td><td>{{ ucfirst($kelas->jenis) }}</td></tr>
<tr><td class="text-muted">Ruang</td><td>{{ $kelas->ruang ?: '-' }}</td></tr>
<tr><td class="text-muted">Kapasitas</td><td>{{ $kelas->kapasitas }} santri</td></tr>
<tr><td class="text-muted">Status</td><td>@if($kelas->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-secondary">Nonaktif</span>@endif</td></tr>
@if($kelas->deskripsi)<tr><td class="text-muted">Catatan</td><td>{{ $kelas->deskripsi }}</td></tr>@endif
</table>
<button class="btn btn-outline-secondary btn-block" data-toggle="modal" data-target="#editClass"><i class="fas fa-cog mr-1"></i> Atur Rombel Asrama</button>
@if($isCustom)
<form method="post" action="{{ route('asrama.kelas.destroy', $kelas) }}" class="mt-2" data-asrama-loading data-confirm="Hapus rombel asrama '{{ $kelas->nama_kelas }}'? Seluruh anggota santri akan dilepas dari rombel ini." data-loading-title="Menghapus rombel asrama">
@csrf @method('DELETE')
<button type="submit" class="btn btn-outline-danger btn-block"><i class="fas fa-trash-alt mr-1"></i> Hapus Rombel Mandiri</button>
</form>
@endif
</div></div>
</div></div>

<div class="modal fade" id="assignStudents"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.santri.store',$kelas) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyinkronkan santri" data-loading-text="Keanggotaan rombel dan akses portal sedang diperbarui.">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-{{ $isCustom ? 'user-plus' : 'sync-alt' }} mr-2 text-info"></i>{{ $isCustom ? 'Tambah Santri ke Rombel' : 'Sinkronkan Santri Rombel' }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">
@if($isCustom)
<div class="callout callout-info py-2 mb-3"><small><i class="fas fa-info-circle mr-1"></i>Pilih siswa yang tinggal di Asrama untuk dimasukkan ke rombel <strong>{{ $kelas->nama_kelas }}</strong>. Siswa yang dipilih otomatis tercatat sebagai santri aktif asrama.</small></div>
@else
<div class="callout callout-info py-2 mb-3"><small>Hanya siswa aktif rombel SIMANSA <strong>{{ $kelas->nama_kelas }}</strong> ({{ $siswaReguler }} siswa) yang dapat dipilih. Siswa yang dipilih otomatis menjadi santri aktif dan anggota rombel ini.</small></div>
<label class="asrama-choice mb-3"><input type="checkbox" name="ambil_semua_rombel" value="1" checked> <strong class="d-inline ml-1">Ambil seluruh siswa aktif rombel</strong><small class="d-block mt-1">Pilihan tercepat untuk rombel yang seluruh siswanya tinggal di Asrama.</small></label>
@endif
<div class="form-group mb-0"><label class="small font-weight-bold text-muted">{{ $isCustom ? 'Cari dan Pilih Siswa / Santri' : 'Atau pilih per siswa' }}</label><select multiple name="siswa_ids[]" class="form-control asrama-select" data-placeholder="Cari nama siswa, kelas, atau NISN">
@foreach($availableStudents as $student)
<option value="{{ $student->id }}">
    {{ $student->nama_lengkap }} ({{ $student->jenis_kelamin == 'L' ? 'L' : 'P' }}) · {{ $student->kelasSaatIni ? $student->kelasSaatIni->nama_kelas : 'Tanpa Kelas' }} · NISN: {{ $student->nisn ?: ($student->nis_lokal ?: '-') }}{{ $student->is_asrama ? ' [Asrama]' : '' }}
</option>
@endforeach
</select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Simpan Santri</button></div></form></div></div>

<div class="modal fade" id="addCaregiver"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.pengasuh.store',$kelas) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menambahkan pengasuh rombel">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-tie mr-2 text-info"></i>Tambah Pengasuh Rombel</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="callout callout-info py-2 mb-3"><small>Pengasuh harus sudah terdaftar dan dicentang "dapat mengasuh rombel" pada menu <strong>Pengasuh &amp; Pengajar</strong>. Satu rombel boleh punya beberapa pengasuh.</small></div><label class="small font-weight-bold text-muted">Pengasuh</label><select required name="asrama_asatidz_id" class="form-control asrama-select" data-placeholder="Cari pengasuh"><option value=""></option>@foreach($caregivers as $item)<option value="{{ $item->id }}">{{ $item->gtk->nama_lengkap }}</option>@endforeach</select><label class="asrama-choice mt-3"><input type="checkbox" name="is_primary" value="1"> <strong class="d-inline ml-1">Jadikan pengasuh utama rombel</strong><small class="d-block mt-1">Pengasuh lain tetap dapat membina kelompok santri di rombel yang sama.</small></label></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambahkan</button></div></form></div></div>

@foreach($kelas->pengasuhRombel as $caregiver)
<div class="modal fade" id="assignCaregiver{{ $caregiver->id }}"><div class="modal-dialog modal-xl modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.pengasuh.santri',[$kelas,$caregiver]) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Membagi santri kepada pengasuh">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-random mr-2 text-info"></i>Santri Binaan · {{ $caregiver->pengasuh->gtk->nama_lengkap }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="callout callout-info py-2 mb-3"><small>Saat ini membina <strong>{{ $caregiver->santriAssignments->where('is_active',true)->count() }} santri</strong>. Assignment lama santri yang dipilih akan berpindah otomatis ke pengasuh ini.</small></div><label class="asrama-choice mb-3"><input type="checkbox" name="semua_santri" value="1"> <strong class="d-inline ml-1">Assign seluruh santri rombel ({{ $totalSantri }} santri)</strong></label><label class="small font-weight-bold text-muted">Pilih santri tertentu</label><select multiple name="anggota_ids[]" class="form-control asrama-select" data-placeholder="Cari dan pilih santri">@foreach($kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap') as $member)<option value="{{ $member->id }}" @selected($member->pengasuhAssignment?->asrama_rombel_pengasuh_id===$caregiver->id)>{{ $member->santri->siswa->nama_lengkap }}{{ $member->pengasuhAssignment?' · saat ini '.$member->pengasuhAssignment->rombelPengasuh->pengasuh->gtk->nama_lengkap:'' }}</option>@endforeach</select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-random mr-1"></i> Simpan Pembagian</button></div></form></div></div>
@endforeach

<div class="modal fade" id="addTeacher"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.pengampu.store',$kelas) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menambahkan pengampu">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-book-open mr-2 text-info"></i>Tambah Pengampu</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="callout callout-info py-2 mb-3"><small>Pengampu memperoleh menu <strong>Input Nilai</strong> sesuai penugasan mapel &amp; semester. Satu mapel hanya boleh satu pengampu per semester.</small></div><div class="row"><div class="col-12"><label class="small font-weight-bold text-muted">Mapel</label><select required name="asrama_mapel_id" class="form-control asrama-select" data-placeholder="Cari mapel"><option value=""></option>@foreach($mapels as $mapel)<option value="{{ $mapel->id }}">{{ $mapel->nama_latin }} · {{ $mapel->nama_arab }}</option>@endforeach</select></div><div class="col-md-8 mt-3"><label class="small font-weight-bold text-muted">Pengampu</label><select required name="asrama_asatidz_id" class="form-control asrama-select" data-placeholder="Cari GTK"><option value=""></option>@foreach($teachers as $item)<option value="{{ $item->id }}">{{ $item->gtk->nama_lengkap }}</option>@endforeach</select></div><div class="col-md-4 mt-3"><label class="small font-weight-bold text-muted">Semester</label><select required name="semester" class="form-control asrama-select"><option>Ganjil</option><option>Genap</option></select></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambahkan</button></div></form></div></div>

<div class="modal fade" id="editClass"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.update',$kelas) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui rombel">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-cog mr-2 text-primary"></i>Konfigurasi {{ $kelas->nama_kelas }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">
@if($isCustom)
<div class="callout callout-info py-2 mb-3"><small><i class="fas fa-feather-alt mr-1"></i>Ini adalah <strong>Rombel Mandiri / Custom</strong>. Anda dapat mengubah nama rombel, tingkat kelas, kapasitas, ruang belajar, dan atribut asrama lainnya.</small></div>
<div class="row">
<div class="col-md-6 form-group">
    <label class="small font-weight-bold text-muted">Nama Rombel Asrama <span class="text-danger">*</span></label>
    <input type="text" name="nama_kelas" class="form-control" value="{{ $kelas->nama_kelas }}" required maxlength="100">
</div>
<div class="col-md-3 form-group">
    <label class="small font-weight-bold text-muted">Tingkat Kelas <span class="text-danger">*</span></label>
    <select name="tingkat" class="form-control asrama-select" required>
        <option value="10" @selected($kelas->tingkat == 10)>Tingkat 10</option>
        <option value="11" @selected($kelas->tingkat == 11)>Tingkat 11</option>
        <option value="12" @selected($kelas->tingkat == 12)>Tingkat 12</option>
    </select>
</div>
<div class="col-md-3 form-group">
    <label class="small font-weight-bold text-muted">Kapasitas</label>
    <input type="number" name="kapasitas" class="form-control" value="{{ $kelas->kapasitas }}" min="1" max="100">
</div>
<div class="col-md-6 form-group">
    <label class="small font-weight-bold text-muted">Ruang / Lokasi Belajar</label>
    <input type="text" name="ruang" class="form-control" value="{{ $kelas->ruang }}">
</div>
<div class="col-md-6 form-group">
    <label class="small font-weight-bold text-muted">Tahun Pelajaran</label>
    <input class="form-control" value="{{ $kelas->tahunPelajaran->nama }}" disabled>
</div>
@else
<div class="callout callout-info py-2 mb-3"><small><i class="fas fa-link mr-1"></i>Nama rombel, tingkat, ruang, dan anggota santri <strong>otomatis mengikuti rombel SIMANSA</strong> dan disinkronkan setiap halaman Rombel Asrama dibuka. Di sini hanya mengatur atribut khusus asrama.</small></div>
<div class="row">
<div class="col-md-4"><label class="small font-weight-bold text-muted">Rombel SIMANSA</label><input class="form-control" value="{{ $kelas->kelasReguler?->nama_kelas ?? $kelas->nama_kelas }}" disabled></div>
<div class="col-md-4"><label class="small font-weight-bold text-muted">Tahun Pelajaran</label><input class="form-control" value="{{ $kelas->tahunPelajaran->nama }}" disabled></div>
<div class="col-md-4"><label class="small font-weight-bold text-muted">Santri Aktif</label><input class="form-control" value="{{ $totalSantri }} dari {{ $siswaReguler }} siswa" disabled></div>
@endif
<div class="col-md-6 mt-2"><label class="small font-weight-bold text-muted">Nama Arab</label><input dir="rtl" name="nama_arab" class="form-control asrama-arab" value="{{ $kelas->nama_arab }}" placeholder="الفصل ..."><small class="form-text text-muted">Tampil pada rapor &amp; dokumen asrama berbahasa Arab.</small></div>
<div class="col-md-6 mt-2"><label class="small font-weight-bold text-muted">Program Kelas Asrama</label><select name="is_tahfidz" class="form-control asrama-select"><option value="0" @selected(!$kelas->is_tahfidz)>Reguler (Non-Tahfiz)</option><option value="1" @selected($kelas->is_tahfidz)>Kelas Tahfizh</option></select><small class="form-text text-muted">Menentukan format rapor (subjek Tahsin vs Tarjamah &amp; kalkulasi nilai).</small></div>
<div class="col-md-6 mt-2"><label class="small font-weight-bold text-muted">Kelompok</label><select name="jenis" class="form-control asrama-select">@foreach(['putra'=>'Putra','putri'=>'Putri','campuran'=>'Campuran'] as $value=>$label)<option value="{{ $value }}" @selected($kelas->jenis===$value)>{{ $label }}</option>@endforeach</select><small class="form-text text-muted">Dipakai saat penempatan kamar agar tidak tertukar putra/putri.</small></div>
<div class="col-12 mt-2"><label class="small font-weight-bold text-muted">Catatan</label><textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan internal asrama (opsional)">{{ $kelas->deskripsi }}</textarea></div>
<div class="col-12 mt-3"><div class="custom-control custom-switch"><input id="classActive" type="checkbox" class="custom-control-input" name="is_active" value="1" @checked($kelas->is_active)><label class="custom-control-label" for="classActive">Rombel Asrama aktif</label></div><small class="form-text text-muted">Nonaktifkan hanya bila rombel tidak lagi dipakai asrama. Rombel nonaktif tersembunyi dari input nilai &amp; rapor, data santri tetap tersimpan.</small></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
<div class="col-md-6 mt-3"><label class="small font-weight-bold text-muted">Nama Arab</label><input dir="rtl" name="nama_arab" class="form-control asrama-arab" value="{{ $kelas->nama_arab }}" placeholder="الفصل ..."><small class="form-text text-muted">Tampil pada rapor &amp; dokumen asrama berbahasa Arab.</small></div>
<div class="col-md-6 mt-3"><label class="small font-weight-bold text-muted">Program Kelas Asrama</label><select name="is_tahfidz" class="form-control asrama-select"><option value="0" @selected(!$kelas->is_tahfidz)>Reguler (Non-Tahfiz)</option><option value="1" @selected($kelas->is_tahfidz)>Kelas Tahfizh</option></select><small class="form-text text-muted">Menentukan format rapor (subjek Tahsin vs Tarjamah &amp; kalkulasi nilai).</small></div>
<div class="col-md-6 mt-3"><label class="small font-weight-bold text-muted">Kelompok</label><select name="jenis" class="form-control asrama-select">@foreach(['putra'=>'Putra','putri'=>'Putri','campuran'=>'Campuran'] as $value=>$label)<option value="{{ $value }}" @selected($kelas->jenis===$value)>{{ $label }}</option>@endforeach</select><small class="form-text text-muted">Dipakai saat penempatan kamar agar tidak tertukar putra/putri.</small></div>
<div class="col-12 mt-3"><label class="small font-weight-bold text-muted">Catatan</label><textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan internal asrama (opsional)">{{ $kelas->deskripsi }}</textarea></div>
<div class="col-12 mt-3"><div class="custom-control custom-switch"><input id="classActive" type="checkbox" class="custom-control-input" name="is_active" value="1" @checked($kelas->is_active)><label class="custom-control-label" for="classActive">Rombel Asrama aktif</label></div><small class="form-text text-muted">Nonaktifkan hanya bila rombel tidak lagi dipakai asrama. Rombel nonaktif tersembunyi dari input nilai &amp; rapor, data santri tetap tersimpan.</small></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
