@extends('adminlte::page')
@section('title', 'Kelas Asrama')
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Kelas Asrama';$heroDescription='Rombel asrama mandiri dengan wali, ketua kelas, anggota, dan pengampu.';$heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createKelas"><i class="fas fa-plus mr-1"></i> Buat Kelas</button>'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end"><div class="col-md-5"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}{{ $year->is_active?' · Aktif':'' }}</option>@endforeach</select></div><div class="col-md-2 mt-2"><button class="btn btn-info btn-block">Tampilkan</button></div></form></div></div>
<div class="row">
@forelse($records as $item)<div class="col-lg-4 col-md-6 mb-3"><div class="asrama-panel h-100"><div class="asrama-panel__body">
<div class="d-flex justify-content-between align-items-start"><div><small class="text-muted">{{ $item->asrama->nama }}</small><h4 class="mt-1 mb-0">{{ $item->nama_kelas }}</h4><div class="asrama-arab text-muted">{{ $item->nama_arab }}</div></div><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></div>
<hr><div class="row text-center"><div class="col-4"><small>Santri</small><strong class="d-block">{{ $item->anggota_aktif_count }}/{{ $item->kapasitas }}</strong></div><div class="col-4"><small>Wali</small><strong class="d-block text-truncate" title="{{ $item->wali?->gtk?->nama_lengkap }}">{{ $item->wali?->gtk?->nama_lengkap??'-' }}</strong></div><div class="col-4"><small>Ketua</small><strong class="d-block text-truncate" title="{{ $item->ketua?->santri?->siswa?->nama_lengkap }}">{{ $item->ketua?->santri?->siswa?->nama_lengkap??'-' }}</strong></div></div>
<a href="{{ route('asrama.kelas.show',$item) }}" class="btn btn-outline-info btn-block mt-3">Kelola Kelas</a>
</div></div></div>
@empty<div class="col-12"><div class="asrama-panel"><div class="asrama-empty"><i class="fas fa-school"></i>Belum ada kelas asrama pada tahun ini.</div></div></div>@endforelse
</div>
<div class="modal fade" id="createKelas"><div class="modal-dialog modal-lg"><form method="post" action="{{ route('asrama.kelas.store') }}" class="modal-content asrama-form">@csrf<div class="modal-header"><h5>Buat Kelas Asrama</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
<div class="col-md-6"><div class="form-group"><label>Unit Asrama</label><select required name="asrama_id" class="form-control">@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->nama }}</option>@endforeach</select></div></div><div class="col-md-6"><div class="form-group"><label>Tahun Pelajaran</label><select required name="tahun_pelajaran_id" class="form-control">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}</option>@endforeach</select></div></div>
<div class="col-md-6"><div class="form-group"><label>Nama Kelas</label><input required name="nama_kelas" class="form-control"></div></div><div class="col-md-6"><div class="form-group"><label>Nama Arab / الفصل</label><input dir="rtl" name="nama_arab" class="form-control asrama-arab"></div></div>
<div class="col-md-3"><div class="form-group"><label>Tingkat</label><input type="number" min="1" max="12" name="tingkat" class="form-control"></div></div><div class="col-md-3"><div class="form-group"><label>Jenis</label><select name="jenis" class="form-control"><option value="putra">Putra</option><option value="putri">Putri</option><option value="campuran">Campuran</option></select></div></div><div class="col-md-3"><div class="form-group"><label>Kapasitas</label><input required type="number" name="kapasitas" value="40" class="form-control"></div></div><div class="col-md-3"><div class="form-group"><label>Ruang</label><input name="ruang" class="form-control"></div></div>
<div class="col-12"><div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"></textarea></div></div>
</div></div><div class="modal-footer"><button class="btn btn-info">Buat Kelas</button></div></form></div></div>
@stop
@section('css') @include('asrama._styles') @stop
