@extends('adminlte::page')

@section('title', 'Dashboard Siswa - SIMANSA')

@section('content_header')
    {{-- Header moved to welcome banner inside content --}}
@stop

{{-- CSS is in the second @section('css') below --}}

@section('content')
@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show mx-1 mt-1" role="alert">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@php
    $kelasAktif = $siswa->kelasAktif->first();
    $waliKelasUser = $kelasAktif?->waliKelas;
    $waliKelasGtk = $waliKelasUser?->gtk;
    $waliKelasNama = $waliKelasGtk?->nama_lengkap ?? $waliKelasUser?->name;
    $waliKelasJabatan = $waliKelasGtk?->jabatan ?? $waliKelasGtk?->jenis_ptk;
    $waliKelasNomorHp = $waliKelasGtk?->nomor_hp ?? $waliKelasUser?->phone;
    $waliKelasFoto = $waliKelasGtk?->foto_profile_url
        ?? $waliKelasUser?->avatar_url
        ?? ($waliKelasNama
            ? 'https://ui-avatars.com/api/?name=' . urlencode($waliKelasNama) . '&size=160&background=2563eb&color=ffffff'
            : null);
    $waliKelasStatusMessage = null;

    if (!$kelasAktif) {
        $waliKelasStatusMessage = 'Kelas aktif Anda belum ditentukan. Silakan hubungi admin madrasah untuk penempatan kelas.';
    } elseif (!$waliKelasNama) {
        $waliKelasStatusMessage = 'Wali kelas untuk kelas Anda belum ditetapkan. Silakan cek kembali nanti atau hubungi admin madrasah.';
    }
@endphp
<!-- Page Loading Overlay with Lottie Animation -->
<div class="page-loader" id="pageLoader">
    <div class="loader-content">
        <div class="lottie-container" id="lottieLoader"></div>
        <div class="loading-text">Memuat Dashboard...</div>
        <div class="loading-subtext">Mohon tunggu sebentar</div>
    </div>
</div>

@if($snbpReminder)
<div class="modal fade" id="modalSnbpReminder" tabindex="-1" role="dialog" aria-labelledby="modalSnbpReminderLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="modalSnbpReminderLabel">
                    <i class="fas fa-exclamation-circle mr-2"></i>Lengkapi Nomor Pendaftaran SNBP
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-start">
                    <div class="mr-3 text-warning" style="font-size: 2.4rem; line-height: 1;">
                        <i class="fas fa-id-card-alt"></i>
                    </div>
                    <div>
                        <p class="mb-2">
                            Anda terdaftar sebagai siswa <strong>eligible</strong> untuk <strong>{{ $snbpReminder['menu_name'] }}</strong>.
                        </p>
                        <p class="mb-2">
                            Nomor pendaftaran SNBP Anda belum diisi, sehingga sistem belum bisa menyiapkan checker pengumuman dan relasi ke data lulusan.
                        </p>
                        @if($snbpReminder['tahun_pelajaran'])
                            <p class="mb-0 text-muted small">
                                Tahun pelajaran: <strong>{{ $snbpReminder['tahun_pelajaran'] }}</strong>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="text-muted small">Silakan lengkapi sekarang agar proses SNBP Anda tercatat dengan benar.</span>
                <a href="{{ $snbpReminder['route'] }}" class="btn btn-warning font-weight-bold">
                    <i class="fas fa-arrow-right mr-1"></i> Isi Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Welcome Banner -->
<div class="row mb-2">
    <div class="col-12">
        <div class="callout callout-info student-welcome-hero" style="margin-bottom: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-left: 5px solid #fff; color: white;">
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
    <div class="col-lg-3 col-md-6 col-sm-6">
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

    <div class="col-lg-3 col-md-6 col-sm-6">
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

    <div class="col-lg-3 col-md-6 col-sm-6">
        @php
            // Hitung dokumen wajib yang sudah diupload (KK dan Ijazah SMP)
            $dokumenKK = $siswa->dokumen()->where('jenis_dokumen', 'kk')->exists();
            $dokumenIjazah = $siswa->dokumen()->where('jenis_dokumen', 'ijazah_smp')->exists();
            $jumlahDokumenWajib = 0;
            if($dokumenKK) $jumlahDokumenWajib++;
            if($dokumenIjazah) $jumlahDokumenWajib++;
            $dokumenProgress = ($jumlahDokumenWajib / 2) * 100; // 2 dokumen wajib
        @endphp
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 class="text-white">{{ $jumlahDokumenWajib }}/2</h3>
                <p class="text-white">Dokumen Wajib</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="{{ route('siswa.dokumen') }}" class="small-box-footer" style="color: white;">
                {{ $jumlahDokumenWajib == 2 ? 'Lihat Dokumen' : 'Upload Dokumen' }} 
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6">
        @php
            $totalProgress = 0;
            $completedItems = 0;
            $totalItems = 3;
            
            if($siswa->data_ortu_completed) $completedItems++;
            if($siswa->data_diri_completed) $completedItems++;
            if($jumlahDokumenWajib == 2) $completedItems++; // Kedua dokumen wajib sudah diupload
            
            $totalProgress = ($completedItems / $totalItems) * 100;
            $allComplete = $completedItems == $totalItems;
        @endphp
        <div class="small-box {{ $allComplete ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h3>{{ number_format($totalProgress, 0) }}%</h3>
                <p>Total Kelengkapan</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="#kelengkapan-data" class="small-box-footer">
                Status: {{ $allComplete ? 'Lengkap' : 'Belum Lengkap' }} 
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
                    <!-- Enhanced Profile Photo -->
                    <div class="profile-photo-wrapper mx-auto" style="position: relative; width: 160px; height: 160px;">
                        <div class="profile-photo-ring" style="position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; border-radius: 50%; background: linear-gradient(135deg, {{ $siswa->jenis_kelamin == 'L' ? '#007bff, #00d4ff' : '#e83e8c, #ff6b9d' }}); animation: pulse-ring 2s ease-in-out infinite;"></div>
                        <img class="profile-user-img img-fluid" 
                             src="{{ $siswa->foto_profile_url }}" 
                             alt="Foto {{ $siswa->nama_lengkap }}"
                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; position: relative; z-index: 1; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.15);"
                             id="fotoProfile"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_lengkap) }}&size=300&background={{ $siswa->jenis_kelamin == 'L' ? '007bff' : 'e83e8c' }}&color=fff'">
                        <!-- Quick Change Photo Overlay -->
                        <label for="dashboardFotoInput" class="dashboard-foto-overlay" title="Ganti Foto Profil">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="dashboardFotoInput" accept="image/jpeg,image/png" style="display:none;">
                    </div>
                    
                    @if(!$siswa->foto_profile)
                        <div class="mt-2">
                            <span class="badge badge-info" style="animation: pulse 2s infinite;">
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
                        <b><i class="fas fa-birthday-cake mr-1"></i> Umur</b>
                        <span class="float-right text-muted">
                            {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->age }} tahun
                        </span>
                    </li>
                    @endif
                    @if($kelasAktif)
                    <li class="list-group-item">
                        <b><i class="fas fa-school mr-1"></i> Kelas</b>
                        <span class="float-right">
                            <span class="badge badge-success">{{ $kelasAktif->nama_lengkap }}</span>
                        </span>
                    </li>
                    @if($waliKelasNama)
                    <li class="list-group-item">
                        <b><i class="fas fa-chalkboard-teacher mr-1"></i> Wali Kelas</b>
                        <span class="float-right text-muted">
                            {{ $waliKelasNama ?? '-' }}
                        </span>
                    </li>
                    @endif
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

        @if($kelasAktif && $waliKelasNama)
        <div class="card card-info card-outline wali-kelas-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                    Wali Kelas Saya
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="wali-kelas-photo mr-3">
                        <img src="{{ $waliKelasFoto }}"
                             alt="Foto {{ $waliKelasNama }}"
                             class="img-circle"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($waliKelasNama) }}&size=160&background=2563eb&color=ffffff';">
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1 font-weight-bold text-dark">{{ $waliKelasNama }}</h5>
                        <div class="text-muted small mb-2">
                            {{ $waliKelasJabatan ?: 'Wali Kelas ' . $kelasAktif->nama_lengkap }}
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-info px-2 py-1">
                                <i class="fas fa-school mr-1"></i>{{ $kelasAktif->nama_lengkap }}
                            </span>
                            @if($waliKelasGtk?->nip)
                                <span class="badge badge-light border px-2 py-1">
                                    <i class="fas fa-id-card mr-1 text-muted"></i>{{ $waliKelasGtk->nip }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($waliKelasNomorHp)
                <div class="wali-kelas-contact mt-3">
                    <i class="fas fa-phone-alt text-info mr-2"></i>
                    <span>{{ $waliKelasNomorHp }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($waliKelasStatusMessage)
        <div class="card card-warning card-outline wali-kelas-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i>
                    Informasi Wali Kelas
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="mr-3 mt-1 text-warning" style="font-size: 1.6rem;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark mb-1">Informasi wali kelas belum tersedia</div>
                        <div class="text-muted">
                            {{ $waliKelasStatusMessage }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

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
                                    <span class="badge badge-danger"><i class="fas fa-clock"></i> Belum Lengkap</span>
                                @endif
                            </span>
                            <div class="progress progress-sm mt-2 mb-3">
                                <div class="progress-bar {{ $siswa->data_diri_completed ? 'bg-success' : 'bg-danger' }}" 
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
                                    <span class="badge badge-danger"><i class="fas fa-clock"></i> Belum Lengkap</span>
                                @endif
                            </span>
                            <div class="progress progress-sm mt-2 mb-3">
                                <div class="progress-bar {{ $siswa->data_ortu_completed ? 'bg-success' : 'bg-danger' }}" 
                                     style="width: {{ $siswa->data_ortu_completed ? 100 : 50 }}%"></div>
                            </div>
                        </div>

                        <div class="progress-group">
                            <span class="progress-text">
                                <i class="fas fa-file-alt"></i> <strong>Upload Dokumen Wajib</strong>
                            </span>
                            <span class="float-right">
                                @if($jumlahDokumenWajib == 2)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap (2/2)</span>
                                @elseif($jumlahDokumenWajib == 1)
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> 1/2 Dokumen</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Belum Upload</span>
                                @endif
                            </span>
                            <div class="progress progress-sm mt-2 mb-3">
                                <div class="progress-bar {{ $jumlahDokumenWajib == 2 ? 'bg-success' : ($jumlahDokumenWajib == 1 ? 'bg-warning' : 'bg-danger') }}" 
                                     style="width: {{ $dokumenProgress }}%"></div>
                            </div>
                            @if($jumlahDokumenWajib < 2)
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Dokumen wajib: 
                                @if(!$dokumenKK)
                                    <span class="text-danger">Kartu Keluarga</span>{{ !$dokumenIjazah ? ', ' : '' }}
                                @endif
                                @if(!$dokumenIjazah)
                                    <span class="text-danger">Ijazah SMP</span>
                                @endif
                            </small>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$allComplete)
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                        <p class="mb-2">Data profil Anda belum lengkap. Segera lengkapi untuk mendapatkan akses penuh ke semua fitur SIMANSA.</p>
                        <hr>
                        <div class="row">
                            @if(!$siswa->data_diri_completed)
                            <div class="col-md-4 mb-2">
                                <a href="{{ route('siswa.profile.diri') }}" class="btn btn-warning btn-block btn-sm">
                                    <i class="fas fa-id-card"></i> Lengkapi Data Diri
                                </a>
                            </div>
                            @endif
                            @if(!$siswa->data_ortu_completed)
                            <div class="col-md-4 mb-2">
                                <a href="{{ route('siswa.profile.ortu') }}" class="btn btn-warning btn-block btn-sm">
                                    <i class="fas fa-users"></i> Lengkapi Data Orangtua
                                </a>
                            </div>
                            @endif
                            @if($jumlahDokumenWajib < 2)
                            <div class="col-md-4 mb-2">
                                <a href="{{ route('siswa.dokumen') }}" class="btn btn-warning btn-block btn-sm">
                                    <i class="fas fa-file-alt"></i> Upload Dokumen Wajib
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

        <!-- Teman Sekelas Card -->
        @php
            $kelasAktif = $siswa->kelasAktif->first();
            $temanSekelas = $kelasAktif ? $kelasAktif->siswaAktif()->where('siswa_id', '!=', $siswa->id)->orderBy('nama_lengkap', 'asc')->get() : collect();
        @endphp
        
        @if($kelasAktif)
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Teman Sekelas - {{ $kelasAktif->nama_lengkap }}
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">{{ $temanSekelas->count() }} Siswa</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($temanSekelas->count() > 0)
                    <div class="teman-sekelas-grid p-3" style="max-height: 450px; overflow-y: auto;">
                        <div class="row">
                            @foreach($temanSekelas as $teman)
                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                <div class="teman-card text-center p-2" style="background: #f8f9fa; border-radius: 10px; transition: all 0.3s ease;">
                                    <div class="teman-foto mx-auto mb-2" style="width: 70px; height: 70px; border-radius: 50%; overflow: hidden; border: 3px solid {{ $teman->jenis_kelamin == 'L' ? '#007bff' : '#e83e8c' }}; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        <img src="{{ $teman->foto_profile_url }}" 
                                             alt="{{ $teman->nama_lengkap }}"
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($teman->nama_lengkap) }}&size=100&background={{ $teman->jenis_kelamin == 'L' ? '007bff' : 'e83e8c' }}&color=fff'">
                                    </div>
                                    <h6 class="mb-0 small font-weight-bold text-truncate" title="{{ $teman->nama_lengkap }}">
                                        {{ Str::limit($teman->nama_lengkap, 15) }}
                                    </h6>
                                    <small class="text-muted">{{ $teman->nisn }}</small>
                                    <div class="mt-1">
                                        @if($teman->jenis_kelamin == 'L')
                                            <span class="badge badge-primary badge-sm"><i class="fas fa-mars"></i></span>
                                        @else
                                            <span class="badge badge-sm" style="background-color: #e83e8c; color: white;"><i class="fas fa-venus"></i></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="description-block">
                                    <i class="fas fa-mars text-primary fa-2x mb-2"></i>
                                    <h5 class="description-header">{{ $temanSekelas->where('jenis_kelamin', 'L')->count() }}</h5>
                                    <span class="description-text">Laki-laki</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <i class="fas fa-venus text-danger fa-2x mb-2"></i>
                                    <h5 class="description-header">{{ $temanSekelas->where('jenis_kelamin', 'P')->count() }}</h5>
                                    <span class="description-text">Perempuan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada teman sekelas</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

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

<!-- Crop Foto Modal -->
<div class="modal fade" id="dashboardCropModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-crop-alt"></i> Crop Foto Profil</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div style="max-height: 60vh; overflow: hidden;">
                    <img id="dashboardCropImage" src="" style="max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCropRotateLeft" title="Putar Kiri"><i class="fas fa-undo"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCropRotateRight" title="Putar Kanan"><i class="fas fa-redo"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCropFlipH" title="Flip Horizontal"><i class="fas fa-arrows-alt-h"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCropFlipV" title="Flip Vertikal"><i class="fas fa-arrows-alt-v"></i></button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-primary" id="btnCropSave"><i class="fas fa-check"></i> Simpan Foto</button>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
    .student-welcome-hero {
        position: relative;
        overflow: hidden;
        border-left: 0 !important;
        border-radius: 16px;
        padding: 1.2rem 1.3rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.10);
    }

    .student-welcome-hero::after {
        content: "";
        position: absolute;
        right: -70px;
        bottom: -110px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }

    .small-box {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.12);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .small-box .inner {
        padding: 1rem 1rem 1.05rem;
    }

    .small-box .small-box-footer {
        background: rgba(255,255,255,0.08);
        font-weight: 600;
    }

    .card {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #dbe7f4;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.07);
        background: #ffffff;
    }

    .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        border-bottom: 1px solid #dbe7f4;
        color: #0f172a;
    }

    .card-primary:not(.card-outline) .card-header,
    .card-success:not(.card-outline) .card-header,
    .card-info:not(.card-outline) .card-header {
        background: linear-gradient(135deg, #2b5fc7 0%, #1f7a72 100%);
        color: #fff;
    }

    .card-outline {
        border-top-width: 0;
    }

    .nav-pills .nav-link {
        border-radius: 10px;
        padding: 0.8rem 0.95rem;
        font-weight: 600;
    }

    .nav-pills .nav-link:hover {
        background-color: #eef6ff;
        color: #2563eb;
    }

    .list-group-item {
        border-color: rgba(219, 231, 244, 0.9);
        background: transparent;
    }

    .alert {
        border-left-width: 0;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    .progress {
        background: #e8f0fb;
    }

    /* Page Loading Overlay */
    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .page-loader.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    .page-loader .loader-content {
        text-align: center;
        color: white;
    }

    .page-loader .lottie-container {
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .page-loader .loading-text {
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 15px;
        opacity: 0.9;
        animation: pulse-text 1.5s ease-in-out infinite;
    }

    .page-loader .loading-subtext {
        font-size: 0.9rem;
        opacity: 0.7;
        margin-top: 5px;
    }

    @keyframes pulse-text {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

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
    
    /* Pulse Ring Animation for Profile Photo */
    @keyframes pulse-ring {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.7;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    .profile-photo-wrapper {
        margin: 5px auto;
    }

    /* Profile Photo Hover Effect */
    #fotoProfile {
        transition: all 0.3s ease;
    }
    
    #fotoProfile:hover {
        transform: scale(1.05);
    }
    
    /* Teman Sekelas Card Hover */
    .teman-card {
        cursor: default;
    }
    
    .teman-card:hover {
        background: #e9ecef !important;
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .teman-foto img {
        transition: all 0.3s ease;
    }
    
    .teman-card:hover .teman-foto img {
        transform: scale(1.1);
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

    /* Small Box Hover Effects - Smaller Size for Mobile */
    .small-box {
        transition: all 0.3s ease;
        border-radius: 5px;
    }

    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .small-box .inner {
        padding: 12px;
    }

    .small-box h3 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .small-box p {
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .small-box .icon {
        font-size: 55px;
        top: 10px;
        right: 10px;
    }

    .small-box .small-box-footer {
        padding: 4px 0;
        font-size: 0.85rem;
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
    #modalSnbpReminder .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }
    #modalSnbpReminder .modal-header,
    #modalSnbpReminder .modal-footer {
        border: 0;
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

    /* Dashboard Foto Change Overlay */
    .dashboard-foto-overlay {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 123, 255, 0.9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
        border: 3px solid #fff;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }

    .dashboard-foto-overlay:hover {
        background: rgba(0, 86, 179, 1);
        transform: scale(1.1);
    }

    .profile-username {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
    }

    .wali-kelas-card .card-body {
        padding: 1rem 1.1rem;
    }

    .wali-kelas-photo img {
        width: 74px;
        height: 74px;
        object-fit: cover;
        border: 3px solid #e0f2fe;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.16);
    }

    .wali-kelas-contact {
        display: inline-flex;
        align-items: center;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #dbeafe;
        color: #334155;
        font-size: 0.92rem;
    }

    /* Button Styling */
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
    }

    /* Teman Sekelas Table */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    .description-block {
        margin: 10px 0;
    }

    .description-header {
        margin: 10px 0;
        padding: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .description-text {
        text-transform: uppercase;
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .small-box h3 {
            font-size: 1.6rem;
        }
        
        .small-box .icon {
            font-size: 40px;
        }

        .small-box p {
            font-size: 0.8rem;
        }

        .info-box-number {
            font-size: 13px;
        }

        .profile-user-img {
            width: 120px !important;
            height: 120px !important;
        }

        .wali-kelas-photo img {
            width: 64px;
            height: 64px;
        }

        .card-body {
            padding: 12px;
        }
    }

    @media (max-width: 576px) {
        .small-box .inner {
            padding: 10px;
        }

        .small-box h3 {
            font-size: 1.4rem;
        }

        .small-box .icon {
            font-size: 35px;
            top: 8px;
            right: 8px;
        }
    }
</style>
@stop

@section('js')
<!-- Lottie Animation Library -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
<!-- Cropper.js for foto crop -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
    // Initialize Lottie animation for page loader
    var loaderAnimation = lottie.loadAnimation({
        container: document.getElementById('lottieLoader'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'https://lottie.host/2fa0b14e-88bc-4f36-8e63-0b24b8d0c1d2/x6kISxXhXW.json' // Cute student/study loading animation
    });

    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            var loader = document.getElementById('pageLoader');
            loader.classList.add('fade-out');
            // Remove from DOM after animation
            setTimeout(function() {
                loader.style.display = 'none';
            }, 500);
        }, 800); // Show for at least 800ms for smooth UX
    });

    $(document).ready(function() {
        console.log("Dashboard siswa loaded!");
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        @if($snbpReminder)
        $('#modalSnbpReminder').modal('show');
        @endif
        
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

        // === Dashboard Foto Profile Quick Replace ===
        var dashboardCropper = null;

        $('#dashboardFotoInput').on('change', function() {
            var file = this.files[0];
            if (!file) return;

            // Validate file type
            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire('Format Salah', 'Hanya file JPG, JPEG, atau PNG yang diizinkan.', 'error');
                this.value = '';
                return;
            }

            // Validate file size (2MB)
            if (file.size > 2048 * 1024) {
                Swal.fire('File Terlalu Besar', 'Ukuran file maksimal 2MB.', 'error');
                this.value = '';
                return;
            }

            // Read file and open crop modal
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#dashboardCropImage').attr('src', e.target.result);
                $('#dashboardCropModal').modal('show');
            };
            reader.readAsDataURL(file);
        });

        $('#dashboardCropModal').on('shown.bs.modal', function() {
            if (dashboardCropper) {
                dashboardCropper.destroy();
            }
            dashboardCropper = new Cropper(document.getElementById('dashboardCropImage'), {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 0.9,
                responsive: true,
                guides: true,
                highlight: true,
                cropBoxResizable: true,
                cropBoxMovable: true,
            });
        });

        $('#dashboardCropModal').on('hidden.bs.modal', function() {
            if (dashboardCropper) {
                dashboardCropper.destroy();
                dashboardCropper = null;
            }
            $('#dashboardFotoInput').val('');
        });

        // Crop controls
        $('#btnCropRotateLeft').on('click', function() { if (dashboardCropper) dashboardCropper.rotate(-90); });
        $('#btnCropRotateRight').on('click', function() { if (dashboardCropper) dashboardCropper.rotate(90); });
        $('#btnCropFlipH').on('click', function() {
            if (dashboardCropper) {
                var d = dashboardCropper.getData();
                dashboardCropper.scaleX(d.scaleX === -1 ? 1 : -1);
            }
        });
        $('#btnCropFlipV').on('click', function() {
            if (dashboardCropper) {
                var d = dashboardCropper.getData();
                dashboardCropper.scaleY(d.scaleY === -1 ? 1 : -1);
            }
        });

        // Save cropped foto
        $('#btnCropSave').on('click', function() {
            if (!dashboardCropper) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengupload...');

            var canvas = dashboardCropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            var base64 = canvas.toDataURL('image/jpeg', 0.9);

            $.ajax({
                url: '{{ route("siswa.profile.foto.upload") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cropped_image: base64
                },
                success: function(res) {
                    if (res.success) {
                        // Update all foto instances on page
                        $('#fotoProfile').attr('src', res.foto_url);

                        $('#dashboardCropModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal mengupload foto.', 'error');
                    }
                },
                error: function(xhr) {
                    var msg = 'Gagal mengupload foto.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Simpan Foto');
                }
            });
        });
    });
</script>
@stop
