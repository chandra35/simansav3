@extends('adminlte::page')

@section('title', 'Detail Menu SNBP')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap"></i> Detail Menu SNBP</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <!-- Info Card -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> {{ $snbpMenu->nama_menu }}
            </h3>
            <div class="card-tools">
                @if($snbpMenu->isEditable())
                    <a href="{{ route('admin.snbp-menu.edit', $snbpMenu) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @else
                    <span class="badge badge-warning"><i class="fas fa-lock"></i> Readonly</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Tahun Pelajaran:</strong><br>
                    {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}
                    @if($snbpMenu->tahunPelajaran && $snbpMenu->tahunPelajaran->is_active)
                        <span class="badge badge-primary">Aktif</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Status Menu:</strong><br>
                    @if($snbpMenu->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">Non-Aktif</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Siswa Eligible:</strong><br>
                    <span class="badge badge-success badge-lg">{{ $summary['eligible_total'] }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Siswa Tidak Eligible:</strong><br>
                    <span class="badge badge-danger badge-lg">{{ $snbpMenu->notEligibleSiswa->count() }}</span>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong><i class="fas fa-calendar-alt"></i> Periode Tampil:</strong><br>
                    @if($snbpMenu->tanggal_mulai)
                        <i class="fas fa-play text-success"></i> Mulai: <strong>{{ $snbpMenu->tanggal_mulai->format('d F Y, H:i') }}</strong><br>
                    @else
                        <i class="fas fa-play text-muted"></i> Mulai: <em class="text-muted">Tidak ada batas mulai</em><br>
                    @endif
                    @if($snbpMenu->tanggal_berakhir)
                        <i class="fas fa-stop text-danger"></i> Berakhir: <strong>{{ $snbpMenu->tanggal_berakhir->format('d F Y, H:i') }}</strong>
                    @else
                        <i class="fas fa-stop text-muted"></i> Berakhir: <em class="text-muted">Tidak ada batas akhir</em>
                    @endif
                </div>
                <div class="col-md-6">
                    @php
                        $now = now();
                        $isWithinPeriod = $snbpMenu->isWithinPeriod();
                    @endphp
                    <strong><i class="fas fa-clock"></i> Status Periode:</strong><br>
                    @if(!$snbpMenu->tanggal_mulai && !$snbpMenu->tanggal_berakhir)
                        <span class="badge badge-info">Selalu Tampil</span>
                    @elseif($snbpMenu->tanggal_mulai && $now->lt($snbpMenu->tanggal_mulai))
                        <span class="badge badge-warning">Belum Dimulai</span>
                    @elseif($snbpMenu->tanggal_berakhir && $now->gt($snbpMenu->tanggal_berakhir))
                        <span class="badge badge-secondary">Telah Berakhir</span>
                    @else
                        <span class="badge badge-success">Sedang Aktif</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($snbpMenu->isEditable())
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('admin.snbp-menu.assign-eligible', $snbpMenu) }}" class="btn btn-success btn-block">
                <i class="fas fa-user-check"></i> Assign Siswa Eligible
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.snbp-menu.assign-not-eligible', $snbpMenu) }}" class="btn btn-secondary btn-block">
                <i class="fas fa-user-times"></i> Assign Siswa Tidak Eligible
            </a>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Konten Eligible -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Konten Eligible
                    </h3>
                </div>
                <div class="card-body">
                    {!! $snbpMenu->konten_eligible ?: '<em class="text-muted">Belum ada konten</em>' !!}
                </div>
            </div>
        </div>

        <!-- Konten Tidak Eligible -->
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-times-circle"></i> Konten Tidak Eligible
                    </h3>
                </div>
                <div class="card-body">
                    {!! $snbpMenu->konten_not_eligible ?: '<em class="text-muted">Belum ada konten</em>' !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['sudah_isi'] }}</h3>
                    <p>Siswa eligible sudah isi nomor SNBP</p>
                </div>
                <div class="icon">
                    <i class="fas fa-id-card"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['terhubung_lulusan'] }}</h3>
                    <p>Data SNBP sudah terhubung ke lulusan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-link"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Eligible Students List -->
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Daftar Siswa Eligible ({{ $summary['eligible_total'] }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($eligibleSiswa->count() > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-success text-white" style="position: sticky; top: 0;">
                                <tr>
                                    <th>#</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Nomor SNBP</th>
                                    <th>Lulusan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibleSiswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><code>{{ $siswa->nisn }}</code></td>
                                    <td>{{ $siswa->nama_lengkap }}</td>
                                    <td>
                                        @if(filled(optional($siswa->snbpRegistration)->nomor_pendaftaran))
                                            <code>{{ $siswa->snbpRegistration->nomor_pendaftaran }}</code>
                                        @else
                                            <span class="text-muted">Belum isi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(optional($siswa->snbpRegistration)->lulusan)
                                            <span class="badge badge-success">Terhubung</span>
                                        @else
                                            <span class="badge badge-secondary">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada siswa eligible</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Not Eligible Students List -->
        <div class="col-md-6">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Daftar Siswa Tidak Eligible ({{ $snbpMenu->notEligibleSiswa->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($snbpMenu->notEligibleSiswa->count() > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-danger text-white" style="position: sticky; top: 0;">
                                <tr>
                                    <th>#</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snbpMenu->notEligibleSiswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><code>{{ $siswa->nisn }}</code></td>
                                    <td>{{ $siswa->nama_lengkap }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada siswa tidak eligible</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.snbp-menu.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-lg {
        font-size: 1.2rem;
        padding: 0.5em 0.75em;
    }
</style>
@stop
