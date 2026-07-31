@extends('adminlte::page')
@section('title', 'Mapel Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Mata Pelajaran Asrama';$heroDescription='Master mapel bilingual Arab–Latin dengan skala dan urutan khusus rapor.';$heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createMapel"><i class="fas fa-plus mr-1"></i> Tambah Mapel</button>'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Urutan</th><th>Kode</th><th>Nama Mapel</th><th>Skala</th><th>Minimum</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($records as $item)<tr><td>{{ $item->urutan }}</td><td><code>{{ $item->kode }}</code></td><td><div class="asrama-arab">{{ $item->nama_arab }}</div><small>{{ $item->nama_latin }}</small></td><td>{{ $item->skala_maksimum }}</td><td>{{ $item->nilai_minimum ?? '-' }}</td><td><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editMapel{{ $item->id }}">Edit</button></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-book"></i>Belum ada mapel.</td></tr>@endforelse
</tbody></table></div></div>
<div class="modal fade asrama-modal" id="createMapel"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyimpan mata pelajaran">@csrf<div class="modal-header"><h5 class="modal-title">Tambah Mata Pelajaran</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>null])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info">Simpan</button></div></form></div></div>
@foreach($records as $item)<div class="modal fade asrama-modal" id="editMapel{{ $item->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.update',$item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui mata pelajaran">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">Edit {{ $item->nama_latin }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>$item])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info">Simpan</button></div></form></div></div>@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
