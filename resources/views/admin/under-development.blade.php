@extends('adminlte::page')

@section('title', 'Fitur Dalam Pengembangan')

@section('content_header')
    <h1><i class="fas fa-hammer text-warning"></i> Fitur Dalam Pengembangan</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-warning card-outline">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-tools fa-5x text-warning"></i>
                    </div>
                    <h2 class="text-warning">Fitur Sedang Dikembangkan</h2>
                    <p class="lead text-muted">
                        Maaf, fitur ini masih dalam tahap pengembangan dan akan segera tersedia.
                    </p>
                    
                    <div class="alert alert-info mt-4">
                        <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi</h5>
                        <p class="mb-0">
                            Fitur ini sedang dalam proses development dan akan dirilis dalam waktu dekat. 
                            Terima kasih atas kesabarannya.
                        </p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                    </div>
                </div>
            </div>

            {{-- Progress Info Card --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks"></i> Status Implementasi Fitur</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-success"><i class="fas fa-check-circle"></i> Fitur yang Sudah Tersedia:</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Dashboard</li>
                                <li><i class="fas fa-check text-success"></i> Manajemen Siswa</li>
                                <li><i class="fas fa-check text-success"></i> Tahun Pelajaran</li>
                                <li><i class="fas fa-check text-success"></i> Kurikulum</li>
                                <li><i class="fas fa-check text-success"></i> Manajemen Kelas</li>
                                <li><i class="fas fa-check text-success"></i> Profile Management</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-warning"><i class="fas fa-hammer"></i> Dalam Pengembangan:</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock text-warning"></i> Mutasi Siswa</li>
                                <li><i class="fas fa-clock text-warning"></i> Activity Log</li>
                                <li><i class="fas fa-clock text-warning"></i> Manajemen GTK</li>
                                <li><i class="fas fa-clock text-warning"></i> Nilai & Rapor</li>
                                <li><i class="fas fa-clock text-warning"></i> Absensi</li>
                                <li><i class="fas fa-clock text-warning"></i> Laporan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Development Timeline --}}
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Roadmap Pengembangan</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Phase 1 -->
                        <div class="time-label">
                            <span class="bg-success">Phase 1 - Selesai</span>
                        </div>
                        <div>
                            <i class="fas fa-check bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Akademik Dasar</h3>
                                <div class="timeline-body">
                                    Setup database, RBAC, Siswa, Tahun Pelajaran, Kurikulum, Kelas
                                </div>
                            </div>
                        </div>

                        <!-- Phase 2 -->
                        <div class="time-label">
                            <span class="bg-warning">Phase 2 - Sedang Berjalan</span>
                        </div>
                        <div>
                            <i class="fas fa-hammer bg-warning"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Mutasi & GTK</h3>
                                <div class="timeline-body">
                                    Mutasi Siswa, Manajemen Guru & Tenaga Kependidikan
                                </div>
                            </div>
                        </div>

                        <!-- Phase 3 -->
                        <div class="time-label">
                            <span class="bg-gray">Phase 3 - Akan Datang</span>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Nilai & Rapor</h3>
                                <div class="timeline-body">
                                    Input nilai, generate rapor, leger nilai
                                </div>
                            </div>
                        </div>

                        <!-- Phase 4 -->
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Absensi & Laporan</h3>
                                <div class="timeline-body">
                                    Absensi siswa, rekap kehadiran, laporan lengkap
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
        }
        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ddd;
            left: 31px;
            margin: 0;
            border-radius: 2px;
        }
        .timeline > div {
            margin-right: 0;
            margin-bottom: 15px;
            position: relative;
        }
        .timeline > div:before,
        .timeline > div:after {
            content: " ";
            display: table;
        }
        .timeline > div:after {
            clear: both;
        }
        .timeline > div > .timeline-item {
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #495057;
            margin-left: 60px;
            margin-right: 15px;
            padding: 10px;
            position: relative;
        }
        .timeline > div > .timeline-item > .timeline-header {
            margin: 0;
            color: #495057;
            border-bottom: 1px solid #f4f4f4;
            padding: 5px;
            font-size: 16px;
            line-height: 1.1;
        }
        .timeline > div > .timeline-item > .timeline-body {
            padding: 10px;
        }
        .timeline > div > .fas,
        .timeline > div > .far {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #666;
            background: #d2d6de;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
        }
        .timeline > .time-label > span {
            font-weight: 600;
            padding: 5px;
            display: inline-block;
            background-color: #fff;
            border-radius: 4px;
        }
    </style>
@stop
