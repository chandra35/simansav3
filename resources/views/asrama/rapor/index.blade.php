@extends('adminlte::page')
@section('title', 'Rapor Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php $heroTitle='Rapor Asrama';$heroDescription='Lengkapi sikap dan kehadiran, periksa nilai, lalu terbitkan rapor bilingual.'; @endphp
@include('asrama._hero')
<div class="asrama-panel">
    <div class="asrama-panel__header"><div><h3><i class="fas fa-filter mr-1"></i> Pilih Kelas & Semester</h3><p>Daftar santri mengikuti kelas asrama yang dipilih.</p></div></div>
    <div class="asrama-panel__body asrama-form">
        <form method="get" class="row align-items-end">
            <div class="col-md-6 mb-2">
                <label>Kelas Asrama</label>
                <select name="kelas_id" class="form-control asrama-select">
                    @foreach($classes as $item)
                        <option value="{{ $item->id }}" @selected($kelasId===$item->id)>{{ $item->tahunPelajaran->nama }} · {{ $item->asrama->nama }} · {{ $item->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <label>Semester</label>
                <select name="semester" class="form-control">
                    <option @selected($semester==='Ganjil')>Ganjil</option>
                    <option @selected($semester==='Genap')>Genap</option>
                </select>
            </div>
            <div class="col-md-3 col-6 mb-2"><button class="btn btn-info btn-block"><i class="fas fa-search mr-1"></i> Tampilkan</button></div>
        </form>
    </div>
</div>
@php
    $terbitCount = $members->filter(fn ($m) => $m->rapor->first()?->status === 'terbit')->count();
    $draftCount = $members->filter(fn ($m) => $m->rapor->first() && $m->rapor->first()->status !== 'terbit')->count();
@endphp
<div class="row">
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat"><span>Santri Aktif</span><strong>{{ $members->count() }}</strong><small>Pada kelas terpilih</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat"><span>Terbit</span><strong>{{ $terbitCount }}</strong><small>Rapor terkunci</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat" style="border-left-color:var(--asrama-gold);"><span>Draft</span><strong>{{ $draftCount }}</strong><small>Masih dapat diubah</small></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="asrama-stat" style="border-left-color:#8492a6;"><span>Belum Dibuat</span><strong>{{ $members->count() - $terbitCount - $draftCount }}</strong><small>Belum ada rapor {{ $semester }}</small></div></div>
</div>
<div class="asrama-panel">
    <div class="asrama-panel__header"><div><h3><i class="fas fa-file-alt mr-1"></i> Daftar Santri — Semester {{ $semester }}</h3><p>Klik "Kelola" untuk melengkapi sikap, kehadiran, dan menerbitkan rapor.</p></div></div>
    <div class="table-responsive">
        <table class="table asrama-table asrama-datatable" data-order='[[0,"asc"]]'>
            <thead><tr><th>No</th><th>Santri</th><th>Nomor Induk</th><th>Status Rapor</th><th>Terbit</th><th class="no-sort text-right">Aksi</th></tr></thead>
            <tbody>
            @forelse($members as $index=>$member)
                @php($rapor=$member->rapor->first())
                <tr>
                    <td>{{ $member->nomor_urut??$index+1 }}</td>
                    <td><strong>{{ $member->santri->siswa->nama_lengkap }}</strong><br><small class="text-muted">NISN {{ $member->santri->siswa->nisn }}</small></td>
                    <td><code>{{ $member->santri->nomor_induk_asrama }}</code></td>
                    <td>
                        @if($rapor?->status==='terbit')<span class="asrama-badge asrama-badge--active"><i class="fas fa-lock mr-1"></i>Terbit & terkunci</span>
                        @elseif($rapor)<span class="badge badge-warning">Draft</span>
                        @else<span class="asrama-badge asrama-badge--muted">Belum dibuat</span>@endif
                    </td>
                    <td>{{ $rapor?->published_at?->format('d/m/Y H:i')??'-' }}</td>
                    <td class="text-right">
                        <a class="btn btn-sm btn-info" href="{{ route('asrama.rapor.edit',[$member,'semester'=>$semester]) }}"><i class="fas fa-pen mr-1"></i>Kelola</a>
                        @if($rapor)<a target="_blank" class="btn btn-sm btn-secondary" href="{{ route('asrama.rapor.print',$rapor) }}"><i class="fas fa-print mr-1"></i>Cetak</a>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="asrama-empty"><i class="fas fa-file-alt"></i>Pilih kelas yang memiliki santri aktif.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
