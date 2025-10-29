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
                        <b><i class="fas fa-birthday-cake mr-1"></i> Umur</b>
                        <span class="float-right text-muted">
                            {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->age }} tahun
                        </span>
                    </li>
                    @endif
                    @php
                        $kelasAktif = $siswa->kelasAktif->first();
                    @endphp
                    @if($kelasAktif)
                    <li class="list-group-item">
                        <b><i class="fas fa-school mr-1"></i> Kelas</b>
                        <span class="float-right">
                            <span class="badge badge-success">{{ $kelasAktif->nama_lengkap }}</span>
                        </span>
                    </li>
                    @if($kelasAktif->waliKelas)
                    <li class="list-group-item">
                        <b><i class="fas fa-chalkboard-teacher mr-1"></i> Wali Kelas</b>
                        <span class="float-right text-muted">
                            {{ $kelasAktif->waliKelas->nama }}
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
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="50%">Nama Lengkap</th>
                                    <th width="20%">NISN</th>
                                    <th width="15%">JK</th>
                                    <th width="10%" class="text-center">Profil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($temanSekelas as $index => $teman)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-user-circle text-primary mr-1"></i>
                                        <strong>{{ $teman->nama_lengkap }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $teman->nisn }}</span>
                                    </td>
                                    <td>
                                        @if($teman->jenis_kelamin == 'L')
                                            <i class="fas fa-mars text-primary"></i> Laki-laki
                                        @else
                                            <i class="fas fa-venus text-danger"></i> Perempuan
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($teman->foto_profile)
                                            <img src="{{ Storage::url($teman->foto_profile) }}" 
                                                 class="img-circle elevation-2" 
                                                 width="30" height="30"
                                                 alt="{{ $teman->nama_lengkap }}"
                                                 style="object-fit: cover;">
                                        @else
                                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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