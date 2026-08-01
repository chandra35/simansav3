@extends('adminlte::page')
@section('title', 'Mapel Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Mata Pelajaran Asrama';$heroDescription='Master mapel bilingual Arab–Latin dengan skala dan urutan khusus rapor.';$heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createMapel"><i class="fas fa-plus mr-1"></i> Tambah Mapel</button>'; @endphp
@include('asrama._hero')
@php
    $totalMapel = $records->count();
    $mapelAktif = $records->where('is_active', true)->count();
@endphp
<div class="row">
    <div class="col-md-4 col-6 mb-3">
        <div class="card card-outline card-primary h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Total Mapel</div>
            <h3 class="text-primary mb-0">{{ number_format($totalMapel) }}</h3>
            <small class="text-muted">Seluruh mata pelajaran asrama</small>
        </div></div>
    </div>
    <div class="col-md-4 col-6 mb-3">
        <div class="card card-outline card-success h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Aktif</div>
            <h3 class="text-success mb-0">{{ number_format($mapelAktif) }}</h3>
            <small class="text-muted">Tampil pada rapor & penugasan</small>
        </div></div>
    </div>
    <div class="col-md-4 col-12 mb-3">
        <div class="card card-outline card-secondary h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Nonaktif</div>
            <h3 class="text-secondary mb-0">{{ number_format($totalMapel - $mapelAktif) }}</h3>
            <small class="text-muted">Diarsipkan, tidak dipakai</small>
        </div></div>
    </div>
</div>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-book mr-2"></i>Daftar Mata Pelajaran</h3></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm asrama-datatable" data-order='[[0,"asc"]]'>
                <thead class="thead-light"><tr><th style="width:80px">Urutan</th><th>Kode</th><th>Nama Mapel</th><th>Skala</th><th>Minimum</th><th>Status</th><th class="no-sort text-right" style="width:96px">Aksi</th></tr></thead>
                <tbody>
                @forelse($records as $item)
                    <tr>
                        <td class="align-middle">{{ $item->urutan }}</td>
                        <td class="align-middle"><code>{{ $item->kode }}</code></td>
                        <td class="align-middle"><div class="asrama-arab">{{ $item->nama_arab }}</div><small class="text-muted">{{ $item->nama_latin }}</small></td>
                        <td class="align-middle">{{ $item->skala_maksimum }}</td>
                        <td class="align-middle">{{ $item->nilai_minimum ?? '-' }}</td>
                        <td class="align-middle"><span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td>
                        <td class="align-middle text-right"><button class="btn btn-sm btn-primary" title="Edit mapel" data-toggle="modal" data-target="#editMapel{{ $item->id }}"><i class="fas fa-pen"></i></button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4 asrama-empty"><i class="fas fa-book mr-1"></i> Belum ada mapel. Klik "Tambah Mapel" untuk memulai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="createMapel" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.store') }}" class="modal-content" data-asrama-loading data-loading-title="Menyimpan mata pelajaran">@csrf<div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Tambah Mata Pelajaran</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>null])</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Simpan</button></div></form></div></div>
@foreach($records as $item)<div class="modal fade" id="editMapel{{ $item->id }}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.update',$item) }}" class="modal-content" data-asrama-loading data-loading-title="Memperbarui mata pelajaran">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title"><i class="fas fa-pen mr-2"></i>Edit {{ $item->nama_latin }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>$item])</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Simpan</button></div></form></div></div>@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
