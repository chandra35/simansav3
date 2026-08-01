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
    <div class="col-md-4 col-6 mb-3"><div class="asrama-stat"><span>Total Mapel</span><strong>{{ $totalMapel }}</strong><small>Seluruh mata pelajaran asrama</small></div></div>
    <div class="col-md-4 col-6 mb-3"><div class="asrama-stat"><span>Aktif</span><strong>{{ $mapelAktif }}</strong><small>Tampil pada rapor & penugasan</small></div></div>
    <div class="col-md-4 col-12 mb-3"><div class="asrama-stat" style="border-left-color:#8492a6;"><span>Nonaktif</span><strong>{{ $totalMapel - $mapelAktif }}</strong><small>Diarsipkan, tidak dipakai</small></div></div>
</div>
<div class="asrama-panel">
    <div class="asrama-panel__header"><div><h3><i class="fas fa-book mr-1"></i> Daftar Mata Pelajaran</h3><p>Urutan menentukan posisi mapel pada rapor.</p></div></div>
    <div class="table-responsive">
        <table class="table asrama-table asrama-datatable" data-order='[[0,"asc"]]'>
            <thead><tr><th>Urutan</th><th>Kode</th><th>Nama Mapel</th><th>Skala</th><th>Minimum</th><th>Status</th><th class="no-sort text-right">Aksi</th></tr></thead>
            <tbody>
            @forelse($records as $item)
                <tr>
                    <td>{{ $item->urutan }}</td>
                    <td><code>{{ $item->kode }}</code></td>
                    <td><div class="asrama-arab">{{ $item->nama_arab }}</div><small>{{ $item->nama_latin }}</small></td>
                    <td>{{ $item->skala_maksimum }}</td>
                    <td>{{ $item->nilai_minimum ?? '-' }}</td>
                    <td><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td>
                    <td class="text-right"><button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editMapel{{ $item->id }}"><i class="fas fa-pen mr-1"></i>Edit</button></td>
                </tr>
            @empty
                <tr><td colspan="7" class="asrama-empty"><i class="fas fa-book"></i>Belum ada mapel. Klik "Tambah Mapel" untuk memulai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade asrama-modal" id="createMapel"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyimpan mata pelajaran">@csrf<div class="modal-header"><h5 class="modal-title">Tambah Mata Pelajaran</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>null])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info">Simpan</button></div></form></div></div>
@foreach($records as $item)<div class="modal fade asrama-modal" id="editMapel{{ $item->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.mapel.update',$item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui mata pelajaran">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">Edit {{ $item->nama_latin }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.master._mapel-form',['item'=>$item])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info">Simpan</button></div></form></div></div>@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
