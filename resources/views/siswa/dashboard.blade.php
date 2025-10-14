@extends('adminlte::page')

@section('title', 'Dashboard Siswa - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-home"></i> Dashboard Siswa</h1>
    @if($tahunPelajaranAktif)
    <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
        <i class="fas fa-calendar-alt"></i> Tahun Pelajaran: 
        <strong>{{ $tahunPelajaranAktif->nama }}</strong> 
        (Semester {{ $tahunPelajaranAktif->semester_aktif }})
    </p>
    @endif
@stop

@section('content')
<!-- Profile Card with Photo -->
<div class="row">
    <div class="col-md-4">
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

                <a href="{{ route('siswa.profile.diri') }}" class="btn btn-primary btn-block">
                    <i class="fas fa-user-edit"></i> <b>Edit Profil</b>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Data Completion Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Status Kelengkapan Data
                </h3>
            </div>
            <div class="card-body">
                @php
                    $totalProgress = 0;
                    $completedItems = 0;
                    $totalItems = 2;
                    
                    if($siswa->data_ortu_completed) $completedItems++;
                    if($siswa->data_diri_completed) $completedItems++;
                    
                    $totalProgress = ($completedItems / $totalItems) * 100;
                @endphp

                <div class="text-center mb-4">
                    <h2 class="mb-0">
                        <span class="text-bold {{ $totalProgress == 100 ? 'text-success' : 'text-warning' }}">
                            {{ number_format($totalProgress, 0) }}%
                        </span>
                    </h2>
                    <p class="text-muted">Kelengkapan Data Profil</p>
                </div>

                <div class="progress-group">
                    <span class="progress-text">
                        <i class="fas fa-users"></i> Data Orangtua
                    </span>
                    <span class="float-right">
                        @if($siswa->data_ortu_completed)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Lengkap</span>
                        @endif
                    </span>
                    <div class="progress progress-sm mt-2">
                        <div class="progress-bar {{ $siswa->data_ortu_completed ? 'bg-success' : 'bg-warning' }}" 
                             style="width: {{ $siswa->data_ortu_completed ? 100 : 50 }}%"></div>
                    </div>
                </div>

                <div class="progress-group">
                    <span class="progress-text">
                        <i class="fas fa-id-card"></i> Data Diri
                    </span>
                    <span class="float-right">
                        @if($siswa->data_diri_completed)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Lengkap</span>
                        @endif
                    </span>
                    <div class="progress progress-sm mt-2">
                        <div class="progress-bar {{ $siswa->data_diri_completed ? 'bg-success' : 'bg-warning' }}" 
                             style="width: {{ $siswa->data_diri_completed ? 100 : 50 }}%"></div>
                    </div>
                </div>

                @if(!$siswa->isDataComplete())
                    <div class="callout callout-warning mt-3">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                        <p class="mb-0">
                            Segera lengkapi data profil Anda untuk mendapatkan akses penuh ke semua fitur SIMANSA.
                        </p>
                    </div>
                @else
                    <div class="callout callout-success mt-3">
                        <h5><i class="icon fas fa-check-circle"></i> Sempurna!</h5>
                        <p class="mb-0">
                            Data profil Anda sudah lengkap. Terima kasih atas partisipasinya.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Additional Info Card -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i>
                    Informasi Detail
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tempat Lahir</span>
                                <span class="info-box-number">{{ $siswa->tempat_lahir ?? 'Belum diisi' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tanggal Lahir</span>
                                <span class="info-box-number">
                                    {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d M Y') : 'Belum diisi' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-phone"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">No. HP</span>
                                <span class="info-box-number">{{ $siswa->no_hp ?? 'Belum diisi' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Email</span>
                                <span class="info-box-number" style="font-size: 14px;">
                                    {{ $siswa->email ?? 'Belum diisi' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Announcements -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-1"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center mb-3">
                        <a href="{{ route('siswa.profile.diri') }}" class="btn btn-app bg-info" style="width: 100%;">
                            <i class="fas fa-id-card"></i>
                            Data Diri
                        </a>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <a href="{{ route('siswa.profile.ortu') }}" class="btn btn-app bg-success" style="width: 100%;">
                            <i class="fas fa-users"></i>
                            Data Ortu
                        </a>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <a href="{{ route('siswa.dokumen') }}" class="btn btn-app bg-warning" style="width: 100%;">
                            <i class="fas fa-folder-open"></i>
                            Dokumen
                        </a>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <a href="{{ route('siswa.profile.password') }}" class="btn btn-app bg-danger" style="width: 100%;">
                            <i class="fas fa-lock"></i>
                            Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn mr-1"></i>
                    Pengumuman
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <h5><i class="fas fa-info-circle"></i> Selamat Datang di SIMANSA!</h5>
                    <p>Sistem Informasi MAN 1 Metro membantu Anda mengelola data dan informasi akademik dengan mudah.</p>
                </div>

                <h6 class="mt-3"><i class="fas fa-check-circle text-success"></i> Fitur yang Tersedia:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-angle-right text-primary"></i> Kelola profil pribadi dan orangtua</li>
                    <li><i class="fas fa-angle-right text-primary"></i> Upload dan kelola dokumen</li>
                    <li><i class="fas fa-angle-right text-primary"></i> Ubah password akun</li>
                    <li><i class="fas fa-angle-right text-muted"></i> <em>Informasi akademik (segera hadir)</em></li>
                    <li><i class="fas fa-angle-right text-muted"></i> <em>Layanan administrasi (segera hadir)</em></li>
                </ul>

                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-exclamation-triangle"></i> Penting!</h6>
                    <small>Pastikan data yang Anda isi benar dan lengkap. Jika ada kesulitan, hubungi admin sekolah.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- School Info Statistics -->
<div class="row">
    <div class="col-md-3">
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

    <div class="col-md-3">
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

    <div class="col-md-3">
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

    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $siswa->isDataComplete() ? '100' : number_format((($siswa->data_diri_completed ? 1 : 0) + ($siswa->data_ortu_completed ? 1 : 0)) / 2 * 100, 0) }}%</h3>
                <p>Total Kelengkapan</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="#" class="small-box-footer">
                Status: {{ $siswa->isDataComplete() ? 'Lengkap' : 'Belum Lengkap' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Profile Photo Hover Effect */
    #fotoProfile {
        transition: all 0.3s ease;
    }
    
    #fotoProfile:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(0, 123, 255, 0.5);
    }

    /* Info Box Custom */
    .info-box {
        min-height: 90px;
        transition: all 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .info-box-number {
        font-size: 16px;
    }

    /* Progress Group Spacing */
    .progress-group {
        margin-bottom: 20px;
    }

    /* Quick Action Buttons */
    .btn-app {
        min-width: auto !important;
        height: 100px;
        padding: 15px 5px;
        margin: 0 !important;
    }

    .btn-app i {
        font-size: 30px;
    }

    /* Small Box Hover */
    .small-box {
        transition: all 0.3s ease;
    }

    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    /* Badge Pink for Female */
    .badge-pink {
        color: #fff;
        background-color: #e83e8c;
    }

    /* Callout Animation */
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
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log("Dashboard siswa loaded!");
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Photo click to enlarge (optional)
        $('#fotoProfile').on('click', function() {
            var src = $(this).attr('src');
            if (!src.includes('ui-avatars.com')) {
                // Create modal to show full image
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

        // Auto-dismiss alert after 10 seconds
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 10000);
    });
</script>
@stop