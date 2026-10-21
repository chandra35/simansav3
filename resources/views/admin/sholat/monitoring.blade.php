@extends('adminlte::page')

@section('title', 'Monitoring Presensi Sholat')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-desktop text-primary mr-2"></i>Monitoring Presensi Sholat & Mutaba'ah</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Monitoring Sholat</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <!-- Filter Card -->
    <div class="card card-default shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('admin.sholat.monitoring') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-md-4 mb-2">
                    <label class="font-weight-bold"><i class="far fa-calendar-alt mr-1 text-primary"></i> Tanggal Pemantauan</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" required>
                </div>
                <div class="form-group col-md-5 mb-2">
                    <label class="font-weight-bold"><i class="fas fa-mosque mr-1 text-success"></i> Sholat / Agenda</label>
                    <select name="sholat_setting_id" class="form-control" required>
                        @foreach($activeSettings as $s)
                            <option value="{{ $s->id }}" {{ $selectedSetting && $selectedSetting->id === $s->id ? 'selected' : '' }}>
                                {{ $s->nama_sholat }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sync-alt mr-1"></i> Perbarui Tampilan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSetting)
    <!-- Metric Overview Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['kelas_sudah'] }} <sup style="font-size: 20px">/ {{ $stats['total_kelas'] }}</sup></h3>
                    <p>Kelas Sudah Terdata</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="small-box-footer font-weight-bold">
                    {{ $stats['total_kelas'] > 0 ? round(($stats['kelas_sudah'] / $stats['total_kelas']) * 100, 1) : 0 }}% Keterisian
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ number_format($stats['siswa_berjamaah']) }}</h3>
                    <p>Siswa Berjamaah Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="small-box-footer font-weight-bold">
                    Tercatat Hadir Utama
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ number_format($stats['siswa_masbuq'] + $stats['siswa_munfarid']) }}</h3>
                    <p>Masbuq / Munfarid</p>
                </div>
                <div class="icon">
                    <i class="fas fa-running"></i>
                </div>
                <div class="small-box-footer font-weight-bold">
                    Toleransi Keterlambatan
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ number_format($stats['siswa_alpa']) }}</h3>
                    <p>Alpa (Tanpa Keterangan)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="small-box-footer font-weight-bold">
                    Perlu Tindak Lanjut
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Progres Input Per Rombel -->
        <div class="col-lg-7">
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-tasks mr-1 text-primary"></i> Status Input Per Rombel: {{ $selectedSetting->nama_sholat }}
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 520px;">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Kelas</th>
                                <th>Wali Kelas</th>
                                <th class="text-center" style="width: 140px;">Status Input</th>
                                <th class="text-center" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelasProgress as $idx => $kp)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><strong class="text-dark">{{ $kp['kelas']->nama_kelas }}</strong></td>
                                <td>
                                    <div class="small text-muted">{{ $kp['kelas']->waliKelas->nama_lengkap ?? $kp['kelas']->waliKelas->nama ?? '-' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($kp['is_submitted'])
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Sudah Diinput</span>
                                        <div class="small text-muted mt-1">{{ $kp['petugas'] ?? 'Petugas' }}</div>
                                    @else
                                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Belum Diinput</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.sholat.input', ['tanggal' => $tanggal, 'sholat_setting_id' => $selectedSetting->id, 'kelas_id' => $kp['kelas']->id]) }}" class="btn btn-xs btn-primary font-weight-bold">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Peringatan BK & Wali Kelas: Siswa Butuh Pembinaan -->
        <div class="col-lg-5">
            <div class="card card-outline card-danger shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-bell mr-1"></i> Peringatan BK (Alpa &ge; 3 Kali Bulan Ini)
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 520px;">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center" style="width: 80px;">Alpa</th>
                                <th class="text-center" style="width: 80px;">Kepatuhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warningList as $w)
                            <tr>
                                <td>
                                    <strong class="text-dark">{{ $w['siswa']->nama_lengkap }}</strong>
                                    <div class="small text-muted">{{ $w['siswa']->nisn ?? $w['siswa']->nis_lokal ?? '-' }}</div>
                                </td>
                                <td><span class="badge badge-light border">{{ $w['kelas']->nama_kelas ?? '-' }}</span></td>
                                <td class="text-center font-weight-bold text-danger">
                                    <span class="badge badge-danger px-2">{{ $w['alpa_count'] }}x</span>
                                </td>
                                <td class="text-center">
                                    <span class="small font-weight-bold text-danger">{{ $w['persentase'] }}%</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-success">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <div>Alhamdulillah, tidak ada siswa dengan alpa sholat &ge; 3 kali bulan ini.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light small text-muted">
                    Data di atas diperbarui secara otomatis berdasarkan akumulasi ketidakhadiran sholat tanpa keterangan dalam 1 bulan kalender berjalan.
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop
