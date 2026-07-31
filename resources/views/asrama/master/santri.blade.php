@extends('adminlte::page')
@section('title', 'Santri Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Santri Asrama';
    $heroDescription='Aktifkan siswa SIMANSA sebagai santri per rombel atau per siswa, tanpa menggandakan identitas.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#assignSantri"><i class="fas fa-user-plus mr-1"></i> Tambah Santri</button>';
@endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end" data-asrama-loading data-loading-title="Memuat data santri">
    <div class="col-md-5"><label>Tahun Pelajaran Rombel</label><select name="tahun_pelajaran_id" class="form-control asrama-select">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}{{ $year->is_active?' · Aktif':'' }}</option>@endforeach</select></div>
    <div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Terapkan</button></div>
</form></div></div>
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>No. Induk</th><th>Santri</th><th>Rombel SIMANSA</th><th>Pengasuh</th><th>Kamar</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($records as $item)
<tr><td><code>{{ $item->nomor_induk_asrama }}</code></td><td><strong>{{ $item->siswa->nama_lengkap }}</strong><br><small>NISN {{ $item->siswa->nisn ?: '-' }}</small></td><td>{{ $item->kelasAktif?->kelas?->kelasReguler?->nama_kelas ?? $item->siswa->kelasTahunAktif->first()?->nama_kelas ?? '-' }}</td><td>{{ $item->kelasAktif?->pengasuhAssignment?->rombelPengasuh?->pengasuh?->gtk?->nama_lengkap ?? 'Belum dibagi' }}</td><td>{{ $item->kamarAktif?->kamar?->nama ?? 'Belum ditempatkan' }}</td><td><span class="asrama-badge {{ $item->status==='aktif'?'asrama-badge--active':'asrama-badge--muted' }}">{{ ucfirst($item->status) }}</span></td><td><button class="btn btn-sm btn-outline-primary asrama-icon-button" data-toggle="modal" data-target="#editSantri{{ $item->id }}" title="Edit santri"><i class="fas fa-pen"></i></button></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-user-graduate"></i>Belum ada santri aktif.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $records->links() }}</div></div>

<div class="modal fade asrama-modal" id="assignSantri"><div class="modal-dialog modal-xl modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Mengaktifkan santri" data-loading-text="Identitas dan akses portal sedang disinkronkan dari SIMANSA.">@csrf
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah Santri Asrama</h5><small class="text-white-50">Gunakan rombel, pilihan individual, atau keduanya sekaligus.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body"><div class="row">
    <div class="col-lg-6"><label class="asrama-choice"><strong><i class="fas fa-users mr-1 text-info"></i> Ambil satu rombel SIMANSA</strong><small>Semua siswa aktif di rombel akan menjadi santri.</small><select name="kelas_id" class="form-control asrama-select mt-3" data-placeholder="Pilih rombel" data-allow-clear="1"><option value=""></option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->nama_kelas }} · {{ $class->siswa_aktif_count }} siswa</option>@endforeach</select></label></div>
    <div class="col-lg-6 mt-3 mt-lg-0"><label class="asrama-choice"><strong><i class="fas fa-user-check mr-1 text-info"></i> Pilih siswa individual</strong><small>Bisa memilih banyak siswa melalui kolom pencarian.</small><select multiple name="siswa_ids[]" class="form-control asrama-select mt-3" data-placeholder="Cari dan pilih siswa">@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->nama_lengkap }} · {{ $student->nis_lokal ?: $student->nisn }}</option>@endforeach</select></label></div>
    <div class="col-md-6 mt-3"><label>Nomor induk khusus</label><input name="nomor_induk_asrama" class="form-control" placeholder="Hanya digunakan bila memilih satu siswa"><small class="text-muted">Jika kosong, sistem memakai NIS lokal/NISN.</small></div>
    <div class="col-md-6 mt-3"><label>Tanggal masuk</label><input type="date" name="tanggal_masuk" class="form-control" value="{{ now()->toDateString() }}"></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-sync-alt mr-1"></i> Aktifkan Santri</button></div>
</form></div></div>
@foreach($records as $item)
<div class="modal fade asrama-modal" id="editSantri{{ $item->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.update',$item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyimpan data santri">@csrf @method('PUT')
<div class="modal-header"><div><h5 class="modal-title">{{ $item->siswa->nama_lengkap }}</h5><small class="text-white-50">Perbarui nomor identitas dan status tinggal.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
<div class="col-md-6"><label>Nomor induk</label><input required name="nomor_induk_asrama" class="form-control" value="{{ $item->nomor_induk_asrama }}"></div><div class="col-md-6"><label>Status</label><select name="status" class="form-control asrama-select">@foreach(['aktif'=>'Aktif','nonaktif'=>'Nonaktif'] as $key=>$label)<option value="{{ $key }}" @selected($item->status===$key)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6 mt-3"><label>Tanggal masuk</label><input type="date" name="tanggal_masuk" class="form-control" value="{{ $item->tanggal_masuk?->toDateString() }}"></div><div class="col-md-6 mt-3"><label>Tanggal keluar</label><input type="date" name="tanggal_keluar" class="form-control" value="{{ $item->tanggal_keluar?->toDateString() }}"></div>
<div class="col-12 mt-3"><label>Catatan</label><textarea name="catatan" class="form-control" rows="3">{{ $item->catatan }}</textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
