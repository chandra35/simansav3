@extends('adminlte::page')
@section('title', 'Rombel Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Rombel Asrama';
    $heroDescription='Rombel Asrama memakai kelas SIMANSA yang sama. Aktifkan rombel, sinkronkan santri, lalu tetapkan satu atau beberapa pengasuh.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createKelas"><i class="fas fa-link mr-1"></i> Aktifkan Rombel</button>';
@endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end" data-asrama-loading data-loading-title="Memuat rombel Asrama">
<div class="col-md-5"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control asrama-select">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}{{ $year->is_active?' · Aktif':'' }}</option>@endforeach</select></div><div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Tampilkan</button></div>
</form></div></div>
<div class="row">
@forelse($records as $item)
<div class="col-xl-4 col-md-6 mb-3"><div class="asrama-panel h-100 mb-0"><div class="asrama-panel__body">
<div class="d-flex justify-content-between align-items-start"><div><span class="asrama-pill mb-2"><i class="fas fa-link"></i> Rombel SIMANSA</span><h4 class="mb-0">{{ $item->kelasReguler?->nama_kelas ?? $item->nama_kelas }}</h4><div class="asrama-arab text-muted">{{ $item->nama_arab }}</div></div><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></div>
<hr><div class="row text-center"><div class="col-4"><small>Santri</small><strong class="d-block">{{ $item->anggota_aktif_count }}</strong></div><div class="col-4"><small>Pengasuh</small><strong class="d-block">{{ $item->pengasuhRombel->count() }}</strong></div><div class="col-4"><small>Ketua</small><strong class="d-block text-truncate" title="{{ $item->ketua?->santri?->siswa?->nama_lengkap }}">{{ $item->ketua?->santri?->siswa?->nama_lengkap ?? '-' }}</strong></div></div>
<div class="mt-3">@forelse($item->pengasuhRombel as $caregiver)<span class="asrama-pill mb-1"><i class="fas fa-user-tie"></i> {{ $caregiver->pengasuh->gtk->nama_lengkap }}{{ $caregiver->is_primary?' · Utama':'' }}</span>@empty<small class="text-warning"><i class="fas fa-exclamation-circle"></i> Pengasuh belum ditetapkan</small>@endforelse</div>
<a href="{{ route('asrama.kelas.show',$item) }}" class="btn btn-outline-info btn-block mt-3">Kelola Rombel <i class="fas fa-arrow-right ml-1"></i></a>
</div></div></div>
@empty<div class="col-12"><div class="asrama-panel"><div class="asrama-empty"><i class="fas fa-school"></i>Belum ada rombel SIMANSA yang diaktifkan untuk Asrama.</div></div></div>@endforelse
</div>
<div class="modal fade asrama-modal" id="createKelas"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kelas.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Mengaktifkan rombel Asrama" data-loading-text="Rombel dan santri SIMANSA sedang disinkronkan.">@csrf
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-link mr-2"></i>Aktifkan Rombel SIMANSA</h5><small class="text-white-50">Tidak membuat kelas baru; data tetap mengikuti master SIMANSA.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
<div class="col-12"><label>Rombel SIMANSA</label><select required name="kelas_id" class="form-control asrama-select" data-placeholder="Cari nama rombel"><option value=""></option>@foreach($regularClasses as $class)<option value="{{ $class->id }}">{{ $class->nama_kelas }} · {{ $class->siswa_aktif_count }} siswa · Tingkat {{ $class->tingkat }}</option>@endforeach</select></div>
<div class="col-md-6 mt-3"><label>Nama Arab (opsional)</label><input dir="rtl" name="nama_arab" class="form-control asrama-arab" placeholder="الفصل"></div><div class="col-md-6 mt-3"><label>Kelompok</label><select required name="jenis" class="form-control asrama-select"><option value="putra">Putra</option><option value="putri">Putri</option><option value="campuran">Campuran</option></select></div>
<div class="col-12 mt-3"><label class="asrama-choice"><input type="checkbox" name="sync_students" value="1" checked> <strong class="d-inline ml-1">Langsung aktifkan seluruh siswa rombel sebagai santri</strong><small class="d-block mt-1">Pilihan ini dapat disinkronkan kembali kapan saja dari detail rombel.</small></label></div>
<div class="col-12 mt-3"><label>Catatan</label><textarea name="deskripsi" class="form-control" rows="3"></textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-sync-alt mr-1"></i> Aktifkan & Sinkronkan</button></div></form></div></div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
