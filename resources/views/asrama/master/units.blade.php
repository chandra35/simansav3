@extends('adminlte::page')
@section('title', 'Unit Asrama')
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Unit Asrama';$heroDescription='Kelola identitas asrama dan kepala asrama dari master GTK SIMANSA.';$heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createUnit"><i class="fas fa-plus mr-1"></i> Tambah Unit</button>'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Daftar Unit</h3><p>Unit putra, putri, atau campuran.</p></div><span class="badge badge-info">{{ $units->count() }} unit</span></div>
<div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Kepala</th><th>Santri Aktif</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($units as $unit)<tr><td><code>{{ $unit->kode }}</code></td><td><strong>{{ $unit->nama }}</strong><br><small>{{ $unit->telepon }}</small></td><td>{{ ucfirst($unit->jenis) }}</td><td>{{ $unit->kepala?->nama_lengkap ?? '-' }}</td><td>{{ $unit->santri_count }}</td><td><span class="asrama-badge {{ $unit->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $unit->is_active?'Aktif':'Nonaktif' }}</span></td><td class="text-right"><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editUnit{{ $unit->id }}">Edit</button></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-building"></i>Belum ada unit asrama.</td></tr>@endforelse
</tbody></table></div></div>

<div class="modal fade" id="createUnit"><div class="modal-dialog modal-lg"><form method="post" action="{{ route('asrama.units.store') }}" class="modal-content asrama-form">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Unit Asrama</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._unit-form',['unit'=>null])</div><div class="modal-footer"><button class="btn btn-info">Simpan Unit</button></div></form></div></div>
@foreach($units as $unit)<div class="modal fade" id="editUnit{{ $unit->id }}"><div class="modal-dialog modal-lg"><form method="post" action="{{ route('asrama.units.update',$unit) }}" class="modal-content asrama-form">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit {{ $unit->nama }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._unit-form',['unit'=>$unit])</div><div class="modal-footer"><button class="btn btn-info">Simpan Perubahan</button></div></form></div></div>@endforeach
@stop
@section('css') @include('asrama._styles') @stop
