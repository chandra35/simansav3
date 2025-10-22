@extends('adminlte::page')

@section('title', 'Dashboard Siswa - SIMANSA')

@section('content_header')
    {{-- Header moved to welcome banner inside content --}}
@stop

@section('content')
<!-- Welcome Banner -->
<div class="row mb-2">
    <div class="col-12">
        <div class="callout callout-info" style="margin-bottom: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-left: 5px solid #fff; color: white;">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <i class="fas fa-hand-peace" style="font-size: 3rem; opacity: 0.9;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: white;"><i class="fas fa-home"></i> Selamat Datang, {{ $siswa->nama_lengkap }}!</h4>
                    <p style="margin: 5px 0 0 0; opacity: 0.95;">
                        Dashboard pribadi Anda di <strong>SIMANSA</strong> - Sistem Informasi MAN 1 Metro
                        @if($tahunPelajaranAktif)
                        <br><small><i class="fas fa-calendar-check"></i> Tahun Pelajaran: <strong>{{ $tahunPelajaranAktif->nama }}</strong> (Semester {{ $tahunPelajaranAktif->semester_aktif }})</small>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards at Top -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $siswa->data_diri_completed ? '100' : '0' }}%</h3>
                <p>Data Diri</p>
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
            <a href="{{ route('siswa.profile.diri') }}" class="small-box-footer">
                {{ $siswa->data_diri_completed ? 'Lihat Detail' : 'Lengkapi Sekarang' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $siswa->data_ortu_completed ? '100' : '0' }}%</h3>
                <p>Data Orangtua</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('siswa.profile.ortu') }}" class="small-box-footer">
                {{ $siswa->data_ortu_completed ? 'Lihat Detail' : 'Lengkapi Sekarang' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 class="text-white">
                    {{ $siswa->foto_profile ? 'Ada' : 'Kosong' }}
                </h3>
                <p class="text-white">Foto Profil</p>
            </div>
            <div class="icon">
                <i class="fas fa-camera"></i>
            </div>
            <a href="{{ route('siswa.profile.diri') }}" class="small-box-footer" style="color: white;">
                {{ $siswa->foto_profile ? 'Ubah Foto' : 'Upload Foto' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        @php
            $totalProgress = 0;
            $completedItems = 0;
            $totalItems = 2;
            
            if($siswa->data_ortu_completed) $completedItems++;
            if($siswa->data_diri_completed) $completedItems++;
            
            $totalProgress = ($completedItems / $totalItems) * 100;
        @endphp
        <div class="small-box {{ $siswa->isDataComplete() ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h3>{{ number_format($totalProgress, 0) }}%</h3>
                <p>Total Kelengkapan</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="#kelengkapan-data" class="small-box-footer">
                Status: {{ $siswa->isDataComplete() ? 'Lengkap' : 'Belum Lengkap' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Profile Card - Left Side -->
    <div class="col-lg-4 col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="{{ $siswa->foto_profile_url }}" 
                         alt="Foto {{ $siswa->nama_lengkap }}"
                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #007bff; cursor: pointer;"
                         id="fotoProfile"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_lengkap) }}&size=300&background={{ $siswa->jenis_kelamin == 'L' ? '007bff' : 'e83e8c' }}&color=fff'">
                    
                    @if(!$siswa->foto_profile)
                        <div class="mt-2">
                            <span class="badge badge-info">
                                <i class="fas fa-magic"></i> Avatar Otomatis
                            </span>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center mt-3">{{ $siswa->nama_lengkap }}</h3>

                <p class="text-muted text-center">
                    <i class="fas fa-id-badge"></i> NISN: {{ $siswa->nisn }}
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</b> 
                        <span class="float-right">
                            @if($siswa->jenis_kelamin == 'L')
                                <span class="badge badge-primary">
                                    <i class="fas fa-mars"></i> Laki-laki
                                </span>
                            @else
                                <span class="badge badge-pink" style="background-color: #e83e8c;">
                                    <i class="fas fa-venus"></i> Perempuan
                                </span>
                            @endif
                        </span>
                    </li>
                    @if($siswa->tempat_lahir && $siswa->tanggal_lahir)
                    <li class="list-group-item">
                        <b><i class="fas fa-birthday-cake mr-1"></i> Lahir</b>
                        <span class="float-right text-muted">
                            {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->age }} tahun
                        </span>
                    </li>
                    @endif
                    @if($siswa->agama)
                    <li class="list-group-item">
                        <b><i class="fas fa-pray mr-1"></i> Agama</b>
                        <span class="float-right">{{ $siswa->agama }}</span>
                    </li>
                    @endif
                </ul>

                <a href="{{ route('siswa.profile.diri') }}" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-edit"></i> <b>Edit Profil</b>
                </a>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-1"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('siswa.profile.diri') }}" class="nav-link">
                            <i class="fas fa-id-card text-info"></i> Data Diri
                            <span class="float-right">
                                @if($siswa->data_diri_completed)
                                    <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i></span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('siswa.profile.ortu') }}" class="nav-link">
                            <i class="fas fa-users text-success"></i> Data Orangtua
                            <span class="float-right">
                                @if($siswa->data_ortu_completed)
                                    <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i></span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('siswa.dokumen') }}" class="nav-link">
                            <i class="fas fa-folder-open text-warning"></i> Dokumen Saya
                            <span class="float-right">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('siswa.profile.password') }}" class="nav-link">
                            <i class="fas fa-lock text-danger"></i> Ubah Password
                            <span class="float-right">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Side Content -->
    <div class="col-lg-8 col-md-7">
        <!-- Data Completion Status -->
        <div class="card card-success" id="kelengkapan-data">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Status Kelengkapan Data Profil
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            <div style="font-size: 3rem; color: {{ $totalProgress == 100 ? '#28a745' : '#ffc107' }};">
                                <i class="fas {{ $totalProgress == 100 ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                            </div>
                            <h2 class="mb-0">
                                <span class="text-bold {{ $totalProgress == 100 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($totalProgress, 0) }}%
                                </span>
                            </h2>
                            <p class="text-muted mb-0">Kelengkapan Total</p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="progress-group">
                            <span class="progress-text">
                                <i class="fas fa-id-card"></i> <strong>Data Diri Siswa</strong>
                            </span>
                            <span class="float-right">
                                @if($siswa->data_diri_completed)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Lengkap</span>
                                @endif
                            </span>
                            <div class="progress progress-sm mt-2 mb-3">
                                <div class="progress-bar {{ $siswa->data_diri_completed ? 'bg-success' : 'bg-warning' }}" 
                                     style="width: {{ $siswa->data_diri_completed ? 100 : 50 }}%"></div>
                            </div>
                        </div>

                        <div class="progress-group">
                            <span class="progress-text">
                                <i class="fas fa-users"></i> <strong>Data Orangtua/Wali</strong>
                            </span>
                            <span class="float-right">
                                @if($siswa->data_ortu_completed)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Lengkap</span>
                                @endif
                            </span>
                            <div class="progress progress-sm mt-2 mb-3">
                                <div class="progress-bar {{ $siswa->data_ortu_completed ? 'bg-success' : 'bg-warning' }}" 
                                     style="width: {{ $siswa->data_ortu_completed ? 100 : 50 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!$siswa->isDataComplete())
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                        <p class="mb-2">Data profil Anda belum lengkap. Segera lengkapi untuk mendapatkan akses penuh ke semua fitur SIMANSA.</p>
                        <hr>
                        <div class="row">
                            @if(!$siswa->data_diri_completed)
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('siswa.profile.diri') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-id-card"></i> Lengkapi Data Diri
                                </a>
                            </div>
                            @endif
                            @if(!$siswa->data_ortu_completed)
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('siswa.profile.ortu') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-users"></i> Lengkapi Data Orangtua
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-success mt-3">
                        <h5><i class="icon fas fa-check-circle"></i> Sempurna!</h5>
                        <p class="mb-0">
                            <i class="fas fa-thumbs-up"></i> Data profil Anda sudah lengkap. Terima kasih atas partisipasinya!
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Information Detail -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i>
                    Informasi Detail Pribadi
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info elevation-1">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tempat Lahir</span>
                                <span class="info-box-number">{{ $siswa->tempat_lahir ?? 'Belum diisi' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success elevation-1">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tanggal Lahir</span>
                                <span class="info-box-number" style="font-size: 15px;">
                                    {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d M Y') : 'Belum diisi' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning elevation-1">
                                <i class="fas fa-phone"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">No. HP</span>
                                <span class="info-box-number">{{ $siswa->no_hp ?? 'Belum diisi' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-danger elevation-1">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Email</span>
                                <span class="info-box-number" style="font-size: 13px;">
                                    {{ $siswa->email ?? 'Belum diisi' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcement Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn mr-1"></i>
                    Informasi & Pengumuman
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <h5><i class="fas fa-graduation-cap"></i> Selamat Datang di SIMANSA!</h5>
                    <p class="mb-0">Sistem Informasi MAN 1 Metro membantu Anda mengelola data dan informasi akademik dengan mudah dan efisien.</p>
                </div>

                <h6 class="mt-3"><i class="fas fa-check-circle text-success"></i> Fitur yang Tersedia:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-1"><i class="fas fa-angle-right text-primary"></i> Kelola profil pribadi</li>
                            <li class="mb-1"><i class="fas fa-angle-right text-primary"></i> Data orangtua/wali</li>
                            <li class="mb-1"><i class="fas fa-angle-right text-primary"></i> Upload dokumen</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-1"><i class="fas fa-angle-right text-primary"></i> Ubah password akun</li>
                            <li class="mb-1"><i class="fas fa-angle-right text-muted"></i> <em>Info akademik (segera)</em></li>
                            <li class="mb-1"><i class="fas fa-angle-right text-muted"></i> <em>Layanan admin (segera)</em></li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    <h6 class="mb-1"><i class="fas fa-lightbulb"></i> <strong>Tips:</strong></h6>
                    <small>Pastikan data yang Anda isi benar dan lengkap. Jika ada kesulitan, jangan ragu untuk menghubungi admin sekolah.</small>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    /* Welcome Banner Gradient */
    .callout {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Profile Photo Hover Effect */
    #fotoProfile {
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    #fotoProfile:hover {
        transform: scale(1.08);
        box-shadow: 0 5px 25px rgba(0, 123, 255, 0.5);
    }

    /* Info Box Hover */
    .info-box {
        min-height: 90px;
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }

    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }

    .info-box-number {
        font-size: 15px;
        font-weight: 600;
    }

    .info-box-icon {
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* Progress Group */
    .progress-group {
        margin-bottom: 15px;
    }

    .progress {
        height: 20px;
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
    }

    /* Small Box Hover Effects */
    .small-box {
        transition: all 0.3s ease;
        border-radius: 5px;
    }

    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .small-box h3 {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .small-box p {
        font-size: 1rem;
    }

    .small-box .icon {
        font-size: 70px;
    }

    /* Card Styling */
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600;
    }

    /* Badge Styling */
    .badge-pink {
        color: #fff;
        background-color: #e83e8c;
    }

    .badge {
        padding: 5px 10px;
        font-size: 0.85rem;
    }

    /* Nav Pills in Quick Actions */
    .nav-pills .nav-link {
        color: #333;
        transition: all 0.3s ease;
        margin-bottom: 5px;
        border-radius: 5px;
    }

    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
        padding-left: 20px;
    }

    .nav-pills .nav-link i {
        width: 25px;
    }

    /* Alert Styling */
    .alert {
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    .alert-success {
        border-left-color: #28a745;
    }

    .alert-info {
        border-left-color: #17a2b8;
    }

    /* List Group */
    .list-group-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    .list-group-item:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
    }

    /* Profile Card */
    .box-profile {
        padding: 20px;
    }

    .profile-username {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
    }

    /* Button Styling */
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .small-box h3 {
            font-size: 2rem;
        }
        
        .small-box .icon {
            font-size: 50px;
        }

        .info-box-number {
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log("Dashboard siswa loaded!");
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Photo click to enlarge
        $('#fotoProfile').on('click', function() {
            var src = $(this).attr('src');
            if (!src.includes('ui-avatars.com')) {
                Swal.fire({
                    imageUrl: src,
                    imageAlt: 'Foto Profil',
                    showConfirmButton: false,
                    showCloseButton: true,
                    imageHeight: 400,
                    background: '#f8f9fa',
                    customClass: {
                        image: 'rounded'
                    }
                });
            }
        });

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if(target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });

        // Auto-dismiss alert after 15 seconds
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 15000);

        // Add animation to info boxes on scroll
        $(window).scroll(function() {
            $('.info-box').each(function() {
                var imagePos = $(this).offset().top;
                var topOfWindow = $(window).scrollTop();
                if (imagePos < topOfWindow + 600) {
                    $(this).addClass('animate__animated animate__fadeInUp');
                }
            });
        });
    });
</script>
@stop