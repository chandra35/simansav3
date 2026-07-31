@extends('adminlte::page')
@section('title', 'Rombel Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Rombel Asrama';
    $heroDescription='Rombel Asrama otomatis mengikuti rombel SIMANSA yang bertanda "Rombel Asrama" pada Manajemen Data → Kelas. Di sini tinggal kelola pengasuh, anggota, dan ketua.';
    $heroAction='';
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
@empty<div class="col-12"><div class="asrama-panel"><div class="asrama-empty"><i class="fas fa-school"></i>Belum ada rombel bertanda Asrama. Centang "Rombel Asrama (Kampus 2)" pada rombel di Manajemen Data → Kelas.</div></div></div>@endforelse
</div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
