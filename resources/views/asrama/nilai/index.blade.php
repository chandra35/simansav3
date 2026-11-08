@extends('adminlte::page')
@section('title', 'Input Nilai Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Input Nilai Asrama';
    $heroDescription = 'Penugasan yang tampil disesuaikan dengan asatidz pengampu atau pengasuh rombel.';
    $heroAction = '';
    if (!empty($canManageKomponen)) {
        $heroAction = '<a href="' . route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $selectedYear?->id]) . '" class="btn btn-warning text-dark font-weight-bold">'
            . '<i class="fas fa-sliders-h mr-1"></i> Pengaturan Komponen Nilai'
            . '</a>';
    }
@endphp
@include('asrama._hero')

{{-- Filter Card Tahun Pelajaran & Info Komponen --}}
<div class="asrama-panel">
    <div class="asrama-panel__header">
        <div>
            <h3><i class="fas fa-filter mr-1"></i> Filter Tahun Pelajaran & Semester</h3>
            <p>Secara default terpusat pada tahun pelajaran yang sedang aktif di SIMANSA.</p>
        </div>
    </div>
    <div class="asrama-panel__body asrama-form">
        <form method="get" class="row align-items-end">
            <div class="col-md-5 mb-2">
                <label>Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-control asrama-select" onchange="this.form.submit()">
                    @foreach($years as $yr)
                        <option value="{{ $yr->id }}" @selected(($selectedYearId ?? ($selectedYear?->id ?? null)) == $yr->id)>
                            {{ $yr->nama }}
                            @if($activeYear && $activeYear->id === $yr->id)
                                ★ [Aktif di SIMANSA]
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label>Semester</label>
                <select name="semester" class="form-control asrama-select" onchange="this.form.submit()">
                    <option value="">-- Semua Semester --</option>
                    <option value="Ganjil" @selected(request('semester') === 'Ganjil')>Semester Ganjil</option>
                    <option value="Genap" @selected(request('semester') === 'Genap')>Semester Genap</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                @if(request()->hasAny(['tahun_pelajaran_id', 'semester']))
                    <a href="{{ route('asrama.nilai.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-undo mr-1"></i> Reset Filter
                    </a>
                @endif
            </div>
        </form>

        {{-- Indikator Komponen Penilaian --}}
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    @php
                        $kList = $komponenList ?? ($components ?? collect());
                        $tBobot = $totalBobot ?? (float) $kList->sum('bobot');
                    @endphp
                    <span class="font-weight-bold mr-2"><i class="fas fa-layer-group text-info mr-1"></i> Mode Penilaian {{ $selectedYear?->nama }}:</span>
                    @if($kList->isNotEmpty())
                        <span class="badge badge-success px-2 py-1 mr-2"><i class="fas fa-check-circle mr-1"></i>Dinamis ({{ $kList->count() }} Komponen)</span>
                        @foreach($kList as $kmp)
                            <span class="badge badge-light border mr-1 px-2 py-1">
                                <strong>{{ $kmp->kode }}</strong> ({{ number_format($kmp->bobot, 1) }}%)
                            </span>
                        @endforeach
                        <span class="badge {{ abs($tBobot - 100) < 0.01 ? 'badge-info' : 'badge-warning text-dark' }} ml-1">
                            Total Bobot: {{ number_format($tBobot, 1) }}%
                        </span>
                    @else
                        <span class="badge badge-secondary px-2 py-1 mr-2">Standar (Nilai Tunggal Flat 0–100)</span>
                        <small class="text-muted">Belum ada rincian komponen nilai yang diatur untuk tahun ini.</small>
                    @endif
                </div>
                @if(!empty($canManageKomponen))
                    <div class="mt-2 mt-md-0">
                        <a href="{{ route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $selectedYear?->id]) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-cog mr-1"></i> Kelola Komponen
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
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
