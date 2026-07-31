@extends('adminlte::page')
@section('title', 'Mapel Asrama')
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Mata Pelajaran Asrama';$heroDescription='Master mapel bilingual Arab–Latin dengan skala dan urutan khusus rapor.';$heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createMapel"><i class="fas fa-plus mr-1"></i> Tambah Mapel</button>'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Urutan</th><th>Kode</th><th>Nama Mapel</th><th>Unit</th><th>Skala</th><th>Minimum</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($records as $item)<tr><td>{{ $item->urutan }}</td><td><code>{{ $item->kode }}</code></td><td><div class="asrama-arab">{{ $item->nama_arab }}</div><small>{{ $item->nama_latin }}</small></td><td>{{ $item->asrama?->nama ?? 'Semua Unit' }}</td><td>{{ $item->skala_maksimum }}</td><td>{{ $item->nilai_minimum ?? '-' }}</td><td><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editMapel{{ $item->id }}">Edit</button></td></tr>
@empty<tr><td colspan="8" class="asrama-empty"><i class="fas fa-book"></i>Belum ada mapel.</td></tr>@endforelse
</tbody></table></div></div>
<div class="modal fade" id="createMapel"><div class="modal-dialog modal-lg"><form method="post" action="{{ route('asrama.mapel.store') }}" class="modal-content asrama-form">@csrf<div class="modal-header"><h5>Tambah Mapel</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>null])</div><div class="modal-footer"><button class="btn btn-info">Simpan</button></div></form></div></div>
@foreach($records as $item)<div class="modal fade" id="editMapel{{ $item->id }}"><div class="modal-dialog modal-lg"><form method="post" action="{{ route('asrama.mapel.update',$item) }}" class="modal-content asrama-form">@csrf @method('PUT')<div class="modal-header"><h5>Edit Mapel</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>$item])</div><div class="modal-footer"><button class="btn btn-info">Simpan</button></div></form></div></div>@endforeach
@stop
@section('css') @include('asrama._styles') @stop
