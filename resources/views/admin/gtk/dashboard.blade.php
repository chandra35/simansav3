@extends('adminlte::page')

@section('title', 'Dashboard GTK')

@section('content_header')
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard GTK</h1>
@stop

@section('content')
    {{-- Welcome Card with Complete Info --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3"><i class="fas fa-user-tie"></i> Selamat Datang, {{ $gtk->nama_lengkap }}!</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="35%"><strong>NIK</strong></td>
                                            <td>: {{ $gtk->nik }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NUPTK</strong></td>
                                            <td>: {{ $gtk->nuptk ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NIP</strong></td>
                                            <td>: {{ $gtk->nip ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="35%"><strong>Status</strong></td>
                                            <td>: {{ $gtk->status_kepegawaian ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jabatan</strong></td>
                                            <td>: {{ $gtk->jabatan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kategori</strong></td>
                                            <td>: 
                                                @if($gtk->kategori_ptk || $gtk->jenis_ptk)
                                                    <span class="badge badge-info">{{ $gtk->kategori_ptk ?? '-' }}</span>
                                                    <span class="badge badge-success">{{ $gtk->jenis_ptk ?? '-' }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center d-flex align-items-center justify-content-center">
                            <div>
                                <a href="{{ route('admin.gtk.profile') }}" class="btn btn-info btn-lg mb-2 d-block">
                                    <i class="fas fa-user-edit"></i> Edit Profil
                                </a>
                                <a href="{{ route('admin.gtk.profile.password') }}" class="btn btn-warning btn-lg d-block">
                                    <i class="fas fa-key"></i> Ganti Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Completion Alert (if needed) --}}
    @if($needsCompletion)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Profil Belum Lengkap!</h5>
                    <p class="mb-2">Silakan lengkapi profil Anda untuk mengakses semua fitur sistem:</p>
                    <ul class="mb-2">
                        @if(!$stats['data_diri_completed'])
                            <li>Data Diri (NIK, Tempat/Tanggal Lahir, Alamat Lengkap)</li>
                        @endif
                        @if(!$stats['data_kepeg_completed'])
                            <li>Data Kepegawaian (Status Kepegawaian, Jabatan)</li>
                        @endif
                    </ul>
                    <a href="{{ route('admin.gtk.profile') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Lengkapi Profil Sekarang
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Jadwal Mengajar Hari Ini --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-day"></i> Jadwal Mengajar Hari Ini</h3>
                    <div class="card-tools">
                        <span class="badge badge-light">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Placeholder untuk jadwal mengajar --}}
                    <div class="callout callout-info m-3">
                        <h5><i class="fas fa-info-circle"></i> Informasi</h5>
                        <p class="mb-0">Fitur jadwal mengajar akan segera tersedia. Hubungi admin untuk informasi jadwal.</p>
                    </div>
                    {{-- Contoh tampilan jadwal (akan diganti dengan data real) --}}
                    {{-- 
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-md-2">
                                    <span class="badge badge-primary">07:30 - 08:10</span>
                                </div>
                                <div class="col-md-10">
                                    <strong>Matematika</strong> - Kelas VII A
                                    <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> Ruang 101</small>
                                </div>
                            </div>
                        </li>
                    </ul>
                    --}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-borderless td {
            padding: 0.3rem 0.5rem;
        }
    </style>
@stop

