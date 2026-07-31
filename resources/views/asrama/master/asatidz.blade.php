@extends('adminlte::page')
@section('title', 'Pengasuh & Pengajar Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Pengasuh & Pengajar';
    $heroDescription='Ambil GTK dari SIMANSA lalu tentukan tugasnya sebagai pengasuh rombel, pengasuh kamar, dan/atau pengampu mata pelajaran.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#assignAsatidz"><i class="fas fa-user-plus mr-1"></i> Tambah GTK Asrama</button>';
@endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>GTK</th><th>Identitas</th><th>Jabatan</th><th>Kewenangan</th><th>Beban Aktif</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($records as $item)
<tr><td><strong>{{ $item->gtk->nama_lengkap }}</strong><br><small>{{ $item->gtk->nip ?: $item->gtk->nuptk }}</small></td><td>{{ $item->nomor_identitas ?: '-' }}</td><td>{{ $item->jabatan }}</td><td>
@if($item->dapat_mengasuh_rombel)<span class="asrama-pill mb-1"><i class="fas fa-users"></i> Rombel</span>@endif
@if($item->dapat_mengasuh_kamar)<span class="asrama-pill mb-1"><i class="fas fa-bed"></i> Kamar</span>@endif
@if($item->dapat_mengampu_mapel)<span class="asrama-pill mb-1"><i class="fas fa-book"></i> Mapel</span>@endif
</td><td><small>{{ $item->rombel_diasuh_count }} rombel · {{ $item->kamar_diasuh_count }} kamar · {{ $item->pengampu_count }} mapel</small></td><td><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td><button class="btn btn-sm btn-outline-primary asrama-icon-button" data-toggle="modal" data-target="#editAsatidz{{ $item->id }}"><i class="fas fa-pen"></i></button></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-chalkboard-teacher"></i>Belum ada GTK yang ditugaskan di Asrama.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $records->links() }}</div></div>

<div class="modal fade asrama-modal" id="assignAsatidz"><div class="modal-dialog modal-xl modal-dialog-centered"><form method="post" action="{{ route('asrama.asatidz.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menambahkan GTK Asrama" data-loading-text="Akses menu dan kewenangan sedang disinkronkan.">@csrf
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah GTK Asrama</h5><small class="text-white-50">Satu GTK dapat memegang beberapa jenis tugas.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body"><div class="row">
<div class="col-md-7"><label>GTK SIMANSA</label><select required name="gtk_id" class="form-control asrama-select" data-placeholder="Cari nama atau NIP"><option value=""></option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}">{{ $gtk->nama_lengkap }}{{ $gtk->nip?' · '.$gtk->nip:'' }}</option>@endforeach</select></div>
<div class="col-md-5"><label>Jabatan</label><input required name="jabatan" value="Pengasuh/Pengajar Asrama" class="form-control"></div>
<div class="col-12 mt-3"><label>Pilih kewenangan</label><div class="row">
@foreach([['dapat_mengasuh_rombel','fa-users','Pengasuh Rombel','Dapat ditugaskan pada satu atau beberapa rombel.'],['dapat_mengasuh_kamar','fa-bed','Pengasuh Kamar','Dapat menangani satu atau beberapa kamar.'],['dapat_mengampu_mapel','fa-book-open','Pengampu Mapel','Dapat diberi akses input nilai mapel.']] as [$name,$icon,$title,$desc])
<div class="col-md-4 mb-2"><label class="asrama-choice"><input type="checkbox" name="{{ $name }}" value="1" {{ $name==='dapat_mengampu_mapel'?'checked':'' }}> <strong class="d-inline ml-1"><i class="fas {{ $icon }} text-info mr-1"></i>{{ $title }}</strong><small class="d-block mt-2">{{ $desc }}</small></label></div>
@endforeach
</div></div>
<div class="col-md-6 mt-3"><label>Nomor identitas Asrama</label><input name="nomor_identitas" class="form-control"></div><div class="col-md-6 mt-3"><label>Tanggal mulai</label><input type="date" name="tanggal_mulai" value="{{ now()->toDateString() }}" class="form-control"></div><div class="col-12 mt-3"><label>Catatan</label><textarea name="catatan" class="form-control" rows="3"></textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-check mr-1"></i> Simpan Penugasan</button></div></form></div></div>

@foreach($records as $item)
<div class="modal fade asrama-modal" id="editAsatidz{{ $item->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.asatidz.update',$item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui tugas GTK">@csrf @method('PUT')
<div class="modal-header"><div><h5 class="modal-title">{{ $item->gtk->nama_lengkap }}</h5><small class="text-white-50">Atur kewenangan dan masa tugas.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
<div class="col-md-6"><label>Jabatan</label><input required name="jabatan" class="form-control" value="{{ $item->jabatan }}"></div><div class="col-md-6"><label>Nomor identitas</label><input name="nomor_identitas" class="form-control" value="{{ $item->nomor_identitas }}"></div>
<div class="col-12 mt-3"><label>Kewenangan</label><div class="d-flex flex-wrap" style="gap:1rem"><label><input type="checkbox" name="dapat_mengasuh_rombel" value="1" @checked($item->dapat_mengasuh_rombel)> Pengasuh Rombel</label><label><input type="checkbox" name="dapat_mengasuh_kamar" value="1" @checked($item->dapat_mengasuh_kamar)> Pengasuh Kamar</label><label><input type="checkbox" name="dapat_mengampu_mapel" value="1" @checked($item->dapat_mengampu_mapel)> Pengampu Mapel</label></div></div>
<div class="col-md-6 mt-3"><label>Mulai</label><input type="date" name="tanggal_mulai" class="form-control" value="{{ $item->tanggal_mulai?->toDateString() }}"></div><div class="col-md-6 mt-3"><label>Selesai</label><input type="date" name="tanggal_selesai" class="form-control" value="{{ $item->tanggal_selesai?->toDateString() }}"></div>
<div class="col-12 mt-3"><label>Catatan</label><textarea name="catatan" class="form-control">{{ $item->catatan }}</textarea></div><div class="col-12 mt-3"><div class="custom-control custom-switch"><input id="asatidzActive{{ $item->id }}" type="checkbox" name="is_active" value="1" class="custom-control-input" @checked($item->is_active)><label for="asatidzActive{{ $item->id }}" class="custom-control-label">Penugasan aktif</label></div></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
