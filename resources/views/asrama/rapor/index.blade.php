@extends('adminlte::page')
@section('title', 'Rapor Asrama')
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Rapor Asrama';$heroDescription='Lengkapi sikap dan kehadiran, periksa nilai, lalu terbitkan rapor bilingual.'; @endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end"><div class="col-md-6"><label>Kelas Asrama</label><select name="kelas_id" class="form-control">@foreach($classes as $item)<option value="{{ $item->id }}" @selected($kelasId===$item->id)>{{ $item->tahunPelajaran->nama }} · {{ $item->asrama->nama }} · {{ $item->nama_kelas }}</option>@endforeach</select></div><div class="col-md-3"><label>Semester</label><select name="semester" class="form-control"><option @selected($semester==='Ganjil')>Ganjil</option><option @selected($semester==='Genap')>Genap</option></select></div><div class="col-md-2 mt-2"><button class="btn btn-info btn-block">Tampilkan</button></div></form></div></div>
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>No</th><th>Santri</th><th>Nomor Induk</th><th>Status Rapor</th><th>Terbit</th><th></th></tr></thead><tbody>
@forelse($members as $index=>$member)@php($rapor=$member->rapor->first())<tr><td>{{ $member->nomor_urut??$index+1 }}</td><td><strong>{{ $member->santri->siswa->nama_lengkap }}</strong><br><small>NISN {{ $member->santri->siswa->nisn }}</small></td><td><code>{{ $member->santri->nomor_induk_asrama }}</code></td><td>@if($rapor?->status==='terbit')<span class="asrama-badge asrama-badge--active">Terbit & terkunci</span>@elseif($rapor)<span class="badge badge-warning">Draft</span>@else<span class="asrama-badge asrama-badge--muted">Belum dibuat</span>@endif</td><td>{{ $rapor?->published_at?->format('d/m/Y H:i')??'-' }}</td><td class="text-right"><a class="btn btn-sm btn-info" href="{{ route('asrama.rapor.edit',[$member,'semester'=>$semester]) }}">Kelola</a>@if($rapor)<a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ route('asrama.rapor.print',$rapor) }}">Cetak</a>@endif</td></tr>
@empty<tr><td colspan="6" class="asrama-empty"><i class="fas fa-file-alt"></i>Pilih kelas yang memiliki santri aktif.</td></tr>@endforelse
</tbody></table></div></div>
@stop
@section('css') @include('asrama._styles') @stop
