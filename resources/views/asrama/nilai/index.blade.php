@extends('adminlte::page')
@section('title', 'Input Nilai Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Input Nilai Asrama';$heroDescription='Penugasan yang tampil disesuaikan dengan asatidz pengampu atau wali kelas.'; @endphp
@include('asrama._hero')
<div class="row">
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat"><span>Penugasan</span><strong>{{ $assignments->count() }}</strong><small>Yang dapat Anda akses</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat"><span>Rombel</span><strong>{{ $assignments->pluck('asrama_kelas_id')->unique()->count() }}</strong><small>Rombel asrama terlibat</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat" style="border-left-color:var(--asrama-gold);"><span>Mapel</span><strong>{{ $assignments->pluck('asrama_mapel_id')->unique()->count() }}</strong><small>Mata pelajaran diampu</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat" style="border-left-color:#3b82f6;"><span>Pengampu</span><strong>{{ $assignments->pluck('asrama_asatidz_id')->unique()->count() }}</strong><small>Asatidz bertugas</small></div></div>
</div>
<div class="asrama-panel">
    <div class="asrama-panel__header"><div><h3><i class="fas fa-pen mr-1"></i> Daftar Penugasan</h3><p>Klik "Input Nilai" untuk mengisi nilai santri per mapel.</p></div></div>
    <div class="table-responsive">
        <table class="table asrama-table asrama-datatable">
            <thead><tr><th>Tahun</th><th>Kelas</th><th>Mapel</th><th>Semester</th><th>Pengampu</th><th class="no-sort text-right">Aksi</th></tr></thead>
            <tbody>
            @forelse($assignments as $item)
                <tr>
                    <td>{{ $item->kelas->tahunPelajaran->nama }}</td>
                    <td><strong>{{ $item->kelas->nama_kelas }}</strong><br><small class="text-muted">{{ $item->kelas->asrama->nama }}</small></td>
                    <td><div class="asrama-arab">{{ $item->mapel->nama_arab }}</div><small>{{ $item->mapel->nama_latin }}</small></td>
                    <td><span class="asrama-pill">{{ $item->semester }}</span></td>
                    <td>{{ $item->asatidz->gtk->nama_lengkap }}</td>
                    <td class="text-right"><a class="btn btn-sm btn-info" href="{{ route('asrama.nilai.edit',$item) }}"><i class="fas fa-pen mr-1"></i> Input Nilai</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="asrama-empty"><i class="fas fa-pen"></i>Belum ada penugasan yang dapat Anda akses.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
