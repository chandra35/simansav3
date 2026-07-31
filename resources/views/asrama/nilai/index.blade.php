@extends('adminlte::page')
@section('title', 'Input Nilai Asrama')
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Input Nilai Asrama';$heroDescription='Penugasan yang tampil disesuaikan dengan asatidz pengampu atau wali kelas.'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Tahun</th><th>Kelas</th><th>Mapel</th><th>Semester</th><th>Pengampu</th><th></th></tr></thead><tbody>
@forelse($assignments as $item)<tr><td>{{ $item->kelas->tahunPelajaran->nama }}</td><td><strong>{{ $item->kelas->nama_kelas }}</strong><br><small>{{ $item->kelas->asrama->nama }}</small></td><td><div class="asrama-arab">{{ $item->mapel->nama_arab }}</div><small>{{ $item->mapel->nama_latin }}</small></td><td>{{ $item->semester }}</td><td>{{ $item->asatidz->gtk->nama_lengkap }}</td><td class="text-right"><a class="btn btn-sm btn-info" href="{{ route('asrama.nilai.edit',$item) }}"><i class="fas fa-pen mr-1"></i> Input Nilai</a></td></tr>
@empty<tr><td colspan="6" class="asrama-empty"><i class="fas fa-pen"></i>Belum ada penugasan yang dapat Anda akses.</td></tr>@endforelse
</tbody></table></div></div>
@stop
@section('css') @include('asrama._styles') @stop
