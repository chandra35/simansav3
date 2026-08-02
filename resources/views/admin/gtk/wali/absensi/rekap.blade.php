@extends('adminlte::page')

@section('title', 'Rekap Absensi — Kelas Saya')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-bar text-primary"></i> Rekap Absensi</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.wali.absensi.index', ['kelas_id' => $kelas->id]) }}">Absensi</a></li>
                <li class="breadcrumb-item active">Rekap</li>
            </ol>
        </div>
    </div>
@stop

@php
    $badgeMap = [
        'hadir' => 'success', 'terlambat' => 'warning', 'izin' => 'info',
        'sakit' => 'info', 'alpa' => 'danger', 'dispen' => 'secondary', 'keluar_awal' => 'secondary',
    ];
@endphp

@section('content')
<div class="gtk-wali-rekap-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-chart-bar mr-1"></i> Ringkasan Kehadiran</h3>
                    <p class="mb-2 text-white-50">{{ $kelas->nama_kelas }} · {{ $label }}</p>
                    <p class="mb-0">Bandingkan status kehadiran setiap siswa pada periode yang dipilih.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-center">
                    <div class="text-white-50 small text-uppercase font-weight-bold">Hari Tercatat</div>
                    <h3 class="mb-2 text-white">{{ $hariAktif }}</h3>
                    <a href="{{ route('admin.gtk.wali.absensi.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-clipboard-check mr-1"></i> Input Absensi
                    </a>
                </div>
            </div>
        </div>
    </div>

    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.absensi.rekap', 'extraQuery' => ['periode' => $periode, 'tanggal' => $tanggal]])

    <div class="card simansa-filter-panel mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.gtk.wali.absensi.rekap') }}" class="form-inline">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <label class="mr-2 mb-0 font-weight-600"><i class="fas fa-filter mr-1"></i> Periode:</label>
                <select name="periode" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="hari" {{ $periode === 'hari' ? 'selected' : '' }}>Harian</option>
                    <option value="minggu" {{ $periode === 'minggu' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulan" {{ $periode === 'bulan' ? 'selected' : '' }}>Bulanan</option>
                </select>
                <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ date('Y-m-d') }}" class="form-control mr-2" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <div class="row">
        @foreach($statuses as $st)
            <div class="col-6 col-md-3 col-xl">
                <div class="small-box bg-{{ $badgeMap[$st] ?? 'secondary' }}">
                    <div class="inner">
                        <h3>{{ $totals[$st] ?? 0 }}</h3>
                        <p class="text-capitalize">{{ $st }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-table"></i> Rekap per Siswa</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:48px">No</th>
                        <th>Nama Siswa</th>
                        @foreach($statuses as $st)
                            <th class="text-center text-capitalize" style="width:70px">{{ substr($st, 0, 3) }}</th>
                        @endforeach
                        <th class="text-center" style="width:70px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $s)
                        @php $sum = $perSiswa->get($s->id); @endphp
                        <tr>
                            <td class="text-center">{{ $s->pivot->nomor_urut_absen ?? ($i + 1) }}</td>
                            <td>{{ $s->nama_lengkap }}</td>
                            @foreach($statuses as $st)
                                <td class="text-center">{{ $sum[$st] ?? 0 }}</td>
                            @endforeach
                            <td class="text-center font-weight-600">{{ $sum['total'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .gtk-wali-rekap-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-wali-rekap-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .gtk-wali-rekap-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; }
    @media (max-width:575.98px) {
        .gtk-wali-rekap-page > .bg-gradient-primary .card-body { padding:1rem; }
        .gtk-wali-rekap-page > .bg-gradient-primary h3 { font-size:1.1rem; }
        .gtk-wali-rekap-page .form-inline label,
        .gtk-wali-rekap-page .form-inline .form-control { width:100%; margin-right:0 !important; margin-bottom:.5rem !important; }
    }
</style>
@stop
