@extends('adminlte::page')

@section('title', 'Rekap Absensi — Kelas Saya')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-chart-bar"></i> Kelas Saya</div>
            <h1 class="simansa-hero__title">Rekap Absensi</h1>
            <p class="simansa-hero__subtitle">{{ $kelas->nama_kelas }} · {{ $label }} · {{ $hariAktif }} hari tercatat</p>
        </div>
        <div class="simansa-hero__side">
            <a href="{{ route('admin.gtk.wali.absensi.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-secondary">
                <i class="fas fa-clipboard-check"></i> Input Absensi
            </a>
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

    <div class="card simansa-management-card">
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
@stop
