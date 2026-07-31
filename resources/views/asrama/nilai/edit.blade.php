@extends('adminlte::page')
@section('title', 'Nilai '.$pengampu->mapel->nama_latin)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Input Nilai · '.$pengampu->mapel->nama_latin;$heroDescription=$pengampu->kelas->nama_kelas.' · '.$pengampu->kelas->tahunPelajaran->nama.' · Semester '.$pengampu->semester;$heroAction='<a class="btn btn-light" href="'.route('asrama.nilai.index').'"><i class="fas fa-arrow-left mr-1"></i> Penugasan</a>'; @endphp
@include('asrama._hero')
<form method="post" action="{{ route('asrama.nilai.update',$pengampu) }}">@csrf @method('PUT')
<div class="asrama-panel"><div class="asrama-panel__header"><div><h3><span class="asrama-arab">{{ $pengampu->mapel->nama_arab }}</span></h3><p>Skala 0–{{ $pengampu->mapel->skala_maksimum }}. Rapor yang sudah terbit tidak dapat diubah.</p></div><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan Nilai</button></div>
<div class="table-responsive"><table class="table asrama-table"><thead><tr><th>No</th><th>Santri</th><th>Nomor Induk</th><th style="width:150px">Nilai</th><th>Catatan</th></tr></thead><tbody>
@forelse($pengampu->kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap') as $index=>$member)@php($value=$values->get($member->id))<tr><td>{{ $member->nomor_urut??$index+1 }}</td><td><strong>{{ $member->santri->siswa->nama_lengkap }}</strong></td><td><code>{{ $member->santri->nomor_induk_asrama }}</code></td><td><input type="number" step=".01" min="0" max="{{ $pengampu->mapel->skala_maksimum }}" name="nilai[{{ $member->id }}]" value="{{ old('nilai.'.$member->id,$value?->nilai) }}" class="form-control text-center"></td><td><input name="catatan[{{ $member->id }}]" value="{{ old('catatan.'.$member->id,$value?->catatan) }}" class="form-control"></td></tr>
@empty<tr><td colspan="5" class="asrama-empty"><i class="fas fa-users"></i>Belum ada santri aktif di kelas ini.</td></tr>@endforelse
</tbody></table></div></div></form>
@stop
@section('css') @include('asrama._styles') @stop
