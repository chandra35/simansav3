@extends('adminlte::page')

@section('title', 'Data Siswa - SIMANSA')



@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate text-primary"></i> Data Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

<div class="simansa-siswa-management">
<div class="card bg-gradient-primary text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-1"><i class="fas fa-database"></i> Manajemen Peserta Didik</h3>
                <p class="mb-2 text-white-50">
                    Pantau data siswa aktif, kelengkapan biodata, rombel, dan akses akun dari satu halaman operasional.
                </p>
                <p class="mb-0">Gunakan filter dan aksi cepat untuk mengelola data siswa secara rapi, ringkas, dan konsisten.</p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="text-white-50 small text-uppercase font-weight-bold" id="hero-stat-label">Siswa Tahun Aktif</div>
                        <h3 class="mb-0 text-white" id="hero-stat-total">{{ number_format($stats['total_siswa']) }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="text-white-50 small text-uppercase font-weight-bold">Data Lengkap</div>
                        <h3 class="mb-0 text-white" id="hero-stat-lengkap">{{ number_format($stats['data_lengkap']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-primary h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold" id="stat-total-label">Siswa Tahun Aktif</div>
                <h3 class="text-primary mb-1" id="stat-total">{{ number_format($stats['total_siswa']) }}</h3>
                <div class="text-muted">Jumlah siswa sesuai kelompok data dan filter yang sedang dipakai.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-info h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Laki-Laki</div>
                <h3 class="text-info mb-1" id="stat-laki">{{ number_format($stats['laki_laki']) }}</h3>
                <div class="text-muted">Jumlah siswa laki-laki sesuai filter yang sedang aktif.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-danger h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Perempuan</div>
                <h3 class="text-danger mb-1" id="stat-perempuan">{{ number_format($stats['perempuan']) }}</h3>
                <div class="text-muted">Jumlah siswa perempuan sesuai filter yang sedang aktif.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-success h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Data Lengkap</div>
                <h3 class="text-success mb-1" id="stat-lengkap">{{ number_format($stats['data_lengkap']) }}</h3>
                <div class="text-muted">Siswa dengan data diri dan orang tua yang sudah lengkap.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <h3 class="card-title mb-3 mb-lg-0">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Manajemen Data Siswa
                    </h3>
                    <div class="card-tools ml-0 simansa-action-bar">
                        @can('view-statistik-siswa')
                            <a href="{{ route('admin.siswa.statistics') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chart-pie"></i> Statistik Siswa
                            </a>
                        @endcan
                        @can('create-siswa')
                            <a href="{{ route('admin.siswa.import') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-file-excel"></i> Import Data Siswa
                            </a>
                            <a href="{{ route('admin.emis-import.form') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-cloud-download-alt"></i> Import EMIS
                            </a>
                            <a href="{{ route('admin.siswa.import-npsn') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-school"></i> Import NPSN
                            </a>
                            <button type="button" class="btn simansa-btn-strong btn-sm" onclick="addSiswa()">
                                <i class="fas fa-plus"></i> Tambah Siswa
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="simansa-filter-panel">
                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterPopulation" class="simansa-filter-label">
                                <i class="fas fa-users mr-1"></i> Kelompok Data
                            </label>
                            <select id="filterPopulation" class="form-control form-control-sm">
                                <option value="active_year" @selected($population === 'active_year')>
                                    Tahun Aktif {{ $activeYear?->nama ?: '-' }} ({{ number_format($populationCounts['active_year']) }})
                                </option>
                                <option value="unassigned" @selected($population === 'unassigned')>
                                    Aktif Belum Masuk Rombel ({{ number_format($populationCounts['unassigned']) }})
                                </option>
                                <option value="graduated" @selected($population === 'graduated')>
                                    Lulus / Alumni ({{ number_format($populationCounts['graduated']) }})
                                </option>
                                <option value="transferred_out" @selected($population === 'transferred_out')>
                                    Mutasi Keluar ({{ number_format($populationCounts['transferred_out']) }})
                                </option>
                                <option value="all" @selected($population === 'all')>
                                    Semua Riwayat ({{ number_format($populationCounts['all']) }})
                                </option>
                            </select>
                            <small class="text-muted">Default hanya siswa pada rombel tahun aktif.</small>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterJenisKelamin" class="simansa-filter-label">
                                <i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin
                            </label>
                            <select id="filterJenisKelamin" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        @if(!auth()->user()->hasRole('Wali Kelas') || auth()->user()->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah']))
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterTingkat" class="simansa-filter-label">
                                    <i class="fas fa-layer-group mr-1"></i> Tingkat
                                </label>
                                <select id="filterTingkat" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($tingkatOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterKelas" class="simansa-filter-label">
                                    <i class="fas fa-door-open mr-1"></i> Kelas
                                </label>
                                <select id="filterKelas" class="form-control form-control-sm" disabled>
                                    <option value="">Pilih Tingkat Dulu</option>
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterStatus" class="simansa-filter-label">
                                <i class="fas fa-check-circle mr-1"></i> Status Data
                            </label>
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="lengkap">Data Lengkap</option>
                                <option value="belum">Belum Lengkap</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterEmisStatus" class="simansa-filter-label">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Status EMIS
                            </label>
                            <select id="filterEmisStatus" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="sudah">Sudah Masuk EMIS</option>
                                <option value="belum">Belum Masuk EMIS</option>
                            </select>
                        </div>
                    </div>
                    <div class="simansa-filter-actions">
                        <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </button>
                        @can('view-siswa')
                        <a id="btnExportSiswa" href="{{ route('admin.siswa.export') }}" class="btn btn-sm btn-success" data-no-overlay>
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        @endcan
                    </div>
                </div>

                @if(!empty($contextScope))
                    <div class="alert alert-info simansa-stat-context d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div class="mb-2 mb-lg-0">
                            <strong>{{ $contextScope['title'] }}</strong><br>
                            <span class="simansa-context-main">{{ $contextScope['description'] ?: 'Daftar siswa sedang difilter dari halaman statistik.' }}</span>
                            @if(!empty($contextScope['meta']))
                                <div class="simansa-context-meta">
                                    NPSN: {{ $contextScope['meta']['npsn'] ?: '-' }}
                                    <span class="mx-1">|</span>
                                    NSM: {{ $contextScope['meta']['nsm'] ?: '-' }}
                                </div>
                            @endif
                            @if(!empty($contextScope['detail']))
                                <div class="simansa-context-detail">{{ $contextScope['detail'] }}</div>
                            @endif
                        </div>
                        <a href="{{ route('admin.siswa.index') }}" class="btn btn-sm simansa-btn-contrast">
                            <i class="fas fa-times-circle"></i> Hapus Filter Statistik
                        </a>
                    </div>
                @endif

                <div class="simansa-table-note">
                    <p class="mb-0">
                        Gunakan filter untuk memantau siswa per tingkat, kelengkapan biodata, rombel, status EMIS, dan keberadaan fisik.
                        Status EMIS dan Keberadaan hanya dapat diubah oleh Super Admin; pengguna lain melihatnya dalam mode baca saja.
                    </p>
                </div>

                <div class="simansa-table-scroll">
                    <table id="siswa-table" class="table table-hover table-sm simansa-siswa-table">
                        <thead>
                            <tr>
                                <th class="text-center">Foto</th>
                                <th>Nama / NISN</th>
                                <th class="text-center">JK</th>
                                <th>Kelas</th>
                                <th class="text-center">Ortu</th>
                                <th class="text-center">Diri</th>
                                <th class="text-center">Verval</th>
                                <th class="text-center">EMIS</th>
                                <th class="text-center">Keberadaan</th>
                                <th class="text-center">Tgl Masuk</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Foto -->
<div class="modal fade" id="fotoPreviewModal" tabindex="-1" role="dialog" aria-labelledby="fotoPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="fotoPreviewModalLabel">
                    <i class="fas fa-camera-retro"></i> Preview Foto Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="font-weight-bold mb-3" id="fotoPreviewName">-</p>
                <img id="fotoPreviewImage" src="" alt="Preview foto siswa" class="img-fluid rounded shadow-sm border" style="max-height:65vh; object-fit:contain;">
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">Klik download untuk mengambil file foto asli siswa.</small>
                <div>
                    <a href="#" id="fotoPreviewDownload" class="btn btn-success" download>
                        <i class="fas fa-download"></i> Download Foto Asli
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Siswa -->
<div class="modal fade" id="siswaModal" tabindex="-1" role="dialog" aria-labelledby="siswaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siswaModalLabel">Tambah Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="siswaForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nisn">NISN <span class="text-danger">*</span></label>
                        <input type="text" name="nisn" id="nisn" class="form-control" required>
                        <small class="text-muted">NISN akan digunakan sebagai username dan password default</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-info"></i> Informasi</h6>
                        <ul class="mb-0">
                            <li>Username siswa: NISN</li>
                            <li>Password default: NISN</li>
                            <li>Email: NISN@student.man1metro.sch.id</li>
                            <li>Siswa akan diminta mengganti password saat login pertama</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn simansa-btn-strong">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Siswa -->
<div class="modal fade" id="viewSiswaModal" tabindex="-1" role="dialog" aria-labelledby="viewSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="viewSiswaModalLabel">
                    <i class="fas fa-user-graduate"></i> Detail Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="siswaDetailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="data-siswa-tab" data-toggle="tab" href="#data-siswa" role="tab">
                            <i class="fas fa-user"></i> Data Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-diri-tab" data-toggle="tab" href="#data-diri" role="tab">
                            <i class="fas fa-id-card"></i> Data Diri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-ortu-tab" data-toggle="tab" href="#data-ortu" role="tab">
                            <i class="fas fa-users"></i> Data Orang Tua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="sekolah-asal-tab" data-toggle="tab" href="#sekolah-asal" role="tab">
                            <i class="fas fa-school"></i> Sekolah Asal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#dokumen" role="tab">
                            <i class="fas fa-file-alt"></i> Dokumen
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content mt-3" id="siswaDetailTabContent">
                    <div class="tab-pane fade show active" id="data-siswa" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-diri" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-ortu" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="sekolah-asal" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="dokumen" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="viewSiswaFullDetailLink" class="btn btn-primary">
                    <i class="fas fa-history"></i> Lihat Riwayat Perubahan
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar Dokumen -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 py-2 px-3" style="background:#2d2d2d;">
                <h6 class="modal-title text-white mb-0" id="imagePreviewTitle" style="min-width:0; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <i class="fas fa-image mr-1"></i> <span id="imagePreviewTitleText"></span>
                </h6>
                <div class="d-flex align-items-center ml-2" style="gap:4px; flex-shrink:0;">
                    <button class="btn btn-sm btn-outline-light" onclick="imgZoom(-0.25)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                    <button class="btn btn-sm btn-outline-light" onclick="imgZoom(0)" title="Reset Zoom & Rotasi"><i class="fas fa-compress"></i></button>
                    <button class="btn btn-sm btn-outline-light" onclick="imgZoom(0.25)" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                    <button class="btn btn-sm btn-outline-light" onclick="imgRotate(-90)" title="Putar Kiri 90°"><i class="fas fa-undo"></i></button>
                    <button class="btn btn-sm btn-outline-light" onclick="imgRotate(90)" title="Putar Kanan 90°"><i class="fas fa-redo"></i></button>
                    <button class="btn btn-sm btn-secondary" id="btnSelectRegion" onclick="toggleSelectMode()" title="Seleksi area gambar untuk OCR"><i class="fas fa-crop-alt mr-1"></i>Seleksi</button>
                    <button class="btn btn-sm btn-info" id="btnOcrExtract" onclick="startOcr()" title="OCR seluruh gambar"><i class="fas fa-font mr-1"></i>Teks</button>
                    <a id="imagePreviewDownload" href="#" download class="btn btn-sm btn-success" title="Download original"><i class="fas fa-download"></i></a>
                    <a id="imagePreviewDownloadJpg" href="#" class="btn btn-sm btn-warning" title="Download sebagai JPG"><i class="fas fa-file-image"></i> JPG</a>
                    <button type="button" data-dismiss="modal" aria-label="Tutup"
                            style="background:none;border:none;color:#fff;font-size:1.4rem;line-height:1;cursor:pointer;padding:0 4px;opacity:.9;flex-shrink:0;"
                            title="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body p-0 text-center" style="background:#1a1a1a; min-height:400px; overflow:auto; cursor:grab;" id="imagePreviewContainer">
                <div id="imgWrapper" style="position:relative; display:inline-block; max-width:100%; line-height:0;">
                    <img id="imagePreviewImg" src="" alt="" style="max-width:100%; max-height:72vh; object-fit:contain; transition:transform 0.2s; user-select:none; display:block;">
                    <canvas id="ocrSelCanvas" style="position:absolute; top:0; left:0; display:none; cursor:crosshair;"></canvas>
                </div>
            </div>
            <!-- OCR / Smart Text Search Panel -->
            <div id="ocrPanel" style="display:none; background:#1e1e2e; border-top:1px solid #444; padding:12px 16px;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-search text-info mr-2"></i>
                    <input type="text" id="ocrSearchInput" class="form-control form-control-sm"
                           style="max-width:300px; background:#2d2d3d; color:#e0e0e0; border-color:#555; border-radius:4px;"
                           placeholder="Ketik untuk mencari teks...">
                    <span id="ocrSearchCount" class="text-muted ml-2 small"></span>
                    <button class="btn btn-sm btn-outline-secondary ml-auto" onclick="$('#ocrPanel').hide()" title="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="ocrStatus" class="text-muted small mb-2"></div>
                <div id="ocrResultText" class="p-2 rounded"
                     style="background:#111827; color:#d1d5db; font-size:0.82rem; max-height:180px; overflow-y:auto;
                            white-space:pre-wrap; font-family:monospace; line-height:1.7; border:1px solid #374151; user-select:text; cursor:text;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password Siswa -->
<div class="modal fade" id="resetPasswordSiswaModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordSiswaModalLabel">
                    <i class="fas fa-key text-warning mr-2"></i>Reset Password Siswa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Apakah Anda yakin ingin reset password siswa ini?</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Password akan direset ke NISN dan siswa diminta login ulang.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="confirmResetPasswordSiswa">
                    <i class="fas fa-key mr-1"></i> Ya, Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

</div>
@stop

@section('css')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .status-badge {
            font-size: 0.8em;
        }

        .simansa-action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            justify-content: flex-end;
        }
        .simansa-action-bar .btn {
            margin: 0 !important;
            border-radius: .5rem;
            font-size: .78rem;
            font-weight: 700;
            padding: .38rem .62rem;
        }
        .simansa-filter-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .simansa-table-note {
            margin-bottom: .85rem;
            padding: .72rem .85rem;
            border: 1px solid #dbeafe;
            border-radius: .75rem;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: .86rem;
            font-weight: 600;
            line-height: 1.45;
        }

        /* Compact professional table */
        .simansa-siswa-table {
            width: 100% !important;
            min-width: 1050px;
            table-layout: fixed;
        }
        .simansa-siswa-table thead th {
            background: #f1f5f9;
            color: #1e293b;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .045em;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding: .64rem .65rem;
            line-height: 1.2;
            white-space: normal;
        }
        .simansa-siswa-table tbody td {
            font-size: .84rem;
            color: #0f172a;
            vertical-align: middle;
            padding: .55rem .65rem;
            border-bottom: 1px solid #f1f5f9;
            border-top: none;
        }
        .simansa-siswa-table .siswa-col-foto {
            width: 4% !important;
        }
        .simansa-siswa-table .siswa-col-nama {
            width: 19% !important;
            overflow-wrap: anywhere;
            word-break: normal;
        }
        .simansa-siswa-table .siswa-col-jk {
            width: 4% !important;
            white-space: nowrap;
        }
        .simansa-siswa-table .siswa-col-kelas {
            width: 7% !important;
            overflow-wrap: anywhere;
        }
        .simansa-siswa-table .siswa-col-status {
            width: 8% !important;
        }
        .simansa-siswa-table .siswa-col-keberadaan {
            width: 10% !important;
        }
        .simansa-siswa-table .siswa-col-tanggal {
            width: 9% !important;
            white-space: nowrap;
        }
        .simansa-siswa-table .siswa-col-aksi {
            width: 15% !important;
            white-space: normal;
        }
        .simansa-siswa-table tbody tr:hover td {
            background: #f0f7ff;
        }
        .simansa-siswa-table .siswa-col-aksi .btn-group {
            display: inline-flex;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        .simansa-siswa-table .btn-group .btn {
            padding: .2rem .45rem;
            font-size: .78rem;
            line-height: 1.4;
        }
        .simansa-siswa-table .badge {
            font-size: .76rem;
            font-weight: 700;
            border-radius: .45rem;
        }
        /* Table scroll wrapper — fills card, scrolls horizontally when needed */
        .simansa-table-scroll {
            width: 100%;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }
        .simansa-table-scroll > table {
            margin-bottom: 0;
        }
        @media (max-width: 1199.98px) {
            .simansa-siswa-table {
                min-width: 1000px;
            }
            .simansa-siswa-table thead th,
            .simansa-siswa-table tbody td {
                padding-left: .5rem;
                padding-right: .5rem;
            }
        }

        .simansa-btn-strong {
            background: linear-gradient(135deg, #2563eb 0%, #0f766e 100%) !important;
            border: 0 !important;
            color: #fff !important;
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.18);
        }

        .simansa-btn-strong:hover,
        .simansa-btn-strong:focus {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            border-color: #172554;
            color: #fff;
        }

        .simansa-btn-strong:focus {
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.22);
        }

        .simansa-btn-contrast {
            background: #0f172a !important;
            border: 1px solid #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
            box-shadow: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
            text-decoration: none !important;
        }

        .simansa-btn-contrast:hover,
        .simansa-btn-contrast:focus {
            background: #1e293b !important;
            border-color: #1e293b !important;
            color: #ffffff !important;
        }

        .simansa-btn-contrast:focus {
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.18);
        }

        .simansa-btn-contrast i,
        .simansa-btn-contrast span {
            color: inherit !important;
        }

        .simansa-stat-context {
            border: 1px solid #bfd7ff;
            background: linear-gradient(135deg, #eef5ff 0%, #e5efff 100%);
            color: #1e3a8a;
        }

        .simansa-stat-context strong {
            color: #1d4ed8;
        }

        .simansa-context-main {
            color: #1e3a8a;
            font-weight: 700;
        }

        .simansa-context-meta {
            color: #334155;
            font-size: 0.88rem;
            font-weight: 700;
            margin-top: 0.18rem;
        }

        .simansa-context-detail {
            color: #64748b;
            font-size: 0.86rem;
            margin-top: 0.12rem;
        }

        #siswaModal .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        #siswaModal .btn-secondary {
            font-weight: 600;
        }
        .modal-xl {
            max-width: 1200px;
        }
        /* Custom toastr positioning */
        .toast-top-right {
            top: 80px;
            right: 20px;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        /* DataTables length selector styling */
        .dataTables_length select {
            min-width: 80px !important;
            width: auto !important;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
        }
        .dataTables_length {
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0.75rem;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            font-weight: 600;
        }
        .table-detail td {
            padding: 0.5rem;
        }
        .dokumen-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .dokumen-item:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-group-vertical {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .btn-group-vertical .btn {
            border-radius: 0.25rem !important;
        }
        .foto-cell img {
            transition: transform 0.2s ease;
        }
        .foto-cell .js-preview-foto:hover img {
            transform: scale(1.05);
        }

        @media (max-width: 767.98px) {
            .simansa-stat-context .simansa-btn-contrast {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<!-- Toastr JS for toast notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let siswaTable;
let editingId = null;
let resetPasswordSiswaId = null;
let imgScale = 1;
let imgRotation = 0;
let currentPreviewUrl = '';
let currentDownloadJpgUrl = '#';
let ocrText = '';
let selectMode = false;
let selDown = false, selSX = 0, selSY = 0, selEX = 0, selEY = 0;
const statsContextFilters = @json($contextQuery ?? []);
const populationLabels = {
    active_year: 'Siswa Tahun Aktif',
    unassigned: 'Aktif Belum Rombel',
    graduated: 'Lulus / Alumni',
    transferred_out: 'Mutasi Keluar',
    all: 'Semua Riwayat'
};

// Buka gambar di modal preview
function openImagePreview(url, title, downloadUrl, downloadJpgUrl) {
    imgScale = 1;
    imgRotation = 0;
    currentPreviewUrl = url;
    currentDownloadJpgUrl = downloadJpgUrl || '#';
    $('#imagePreviewTitleText').text(title);
    $('#imagePreviewImg').attr('src', url).css('transform', 'scale(1) rotate(0deg)');
    $('#imagePreviewDownload').attr('href', downloadUrl || url);
    $('#imagePreviewDownloadJpg').attr('href', currentDownloadJpgUrl);
    ocrText = '';
    selectMode = false;
    $('#ocrSelCanvas').hide();
    $('#btnSelectRegion').html('<i class="fas fa-crop-alt mr-1"></i>Seleksi').removeClass('btn-danger').addClass('btn-secondary');
    $('#ocrPanel').hide();
    $('#ocrResultText').html('');
    $('#ocrStatus').text('');
    $('#ocrSearchInput').val('');
    $('#ocrSearchCount').text('');
    $('#btnOcrExtract').prop('disabled', false);
    $('#imagePreviewModal').modal('show');
}

// Terapkan transform gabungan scale + rotate
function applyImgTransform() {
    const img = document.getElementById('imagePreviewImg');
    if (!img) return;
    img.style.transform = 'scale(' + imgScale + ') rotate(' + imgRotation + 'deg)';
    img.style.transformOrigin = 'center center';
}

// Zoom
function imgZoom(delta) {
    if (selectMode) return;
    if (delta === 0) { imgScale = 1; imgRotation = 0; }
    else { imgScale = Math.min(Math.max(imgScale + delta, 0.2), 5); }
    applyImgTransform();
}

// Rotate
function imgRotate(deg) {
    if (selectMode) return;
    imgRotation = (imgRotation + deg + 360) % 360;
    applyImgTransform();
}

// ---- Region Selection Mode ----
function toggleSelectMode() {
    selectMode = !selectMode;
    const canvas = document.getElementById('ocrSelCanvas');
    const img = document.getElementById('imagePreviewImg');
    if (selectMode) {
        imgZoom(0); // reset zoom agar koordinat akurat
        canvas.width  = img.offsetWidth;
        canvas.height = img.offsetHeight;
        canvas.style.width  = img.offsetWidth  + 'px';
        canvas.style.height = img.offsetHeight + 'px';
        canvas.style.display = 'block';
        $('#btnSelectRegion').html('<i class="fas fa-times-circle mr-1"></i>Batal').removeClass('btn-secondary').addClass('btn-danger');
        $('#imagePreviewContainer').css('cursor', 'default');
    } else {
        canvas.style.display = 'none';
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        $('#btnSelectRegion').html('<i class="fas fa-crop-alt mr-1"></i>Seleksi').removeClass('btn-danger').addClass('btn-secondary');
        $('#imagePreviewContainer').css('cursor', 'grab');
    }
}

// Canvas mouse events untuk menggambar seleksi
(function() {
    function getCanvasCoords(canvas, e) {
        const r = canvas.getBoundingClientRect();
        const scaleX = canvas.width  / r.width;
        const scaleY = canvas.height / r.height;
        return {
            x: (e.clientX - r.left) * scaleX,
            y: (e.clientY - r.top)  * scaleY
        };
    }
    function drawSelection(canvas, x1, y1, x2, y2) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(0,0,0,0.35)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        const rx = Math.min(x1,x2), ry = Math.min(y1,y2);
        const rw = Math.abs(x2-x1), rh = Math.abs(y2-y1);
        ctx.clearRect(rx, ry, rw, rh);
        ctx.strokeStyle = '#4af';
        ctx.lineWidth = 2;
        ctx.strokeRect(rx, ry, rw, rh);
    }

    document.addEventListener('mousedown', function(e) {
        if (!selectMode) return;
        const canvas = document.getElementById('ocrSelCanvas');
        if (e.target !== canvas) return;
        e.preventDefault();
        selDown = true;
        const c = getCanvasCoords(canvas, e);
        selSX = selEX = c.x; selSY = selEY = c.y;
    });
    document.addEventListener('mousemove', function(e) {
        if (!selectMode || !selDown) return;
        const canvas = document.getElementById('ocrSelCanvas');
        const c = getCanvasCoords(canvas, e);
        selEX = c.x; selEY = c.y;
        drawSelection(canvas, selSX, selSY, selEX, selEY);
    });
    document.addEventListener('mouseup', async function(e) {
        if (!selectMode || !selDown) return;
        selDown = false;
        const canvas = document.getElementById('ocrSelCanvas');
        const c = getCanvasCoords(canvas, e);
        selEX = c.x; selEY = c.y;
        const rw = Math.abs(selEX - selSX), rh = Math.abs(selEY - selSY);
        if (rw < 10 || rh < 10) return; // terlalu kecil
        // Crop dari gambar asli dengan skala natural
        const img = document.getElementById('imagePreviewImg');
        const scaleX = img.naturalWidth  / canvas.width;
        const scaleY = img.naturalHeight / canvas.height;
        const rx = Math.min(selSX, selEX) * scaleX;
        const ry = Math.min(selSY, selEY) * scaleY;
        const cropW = rw * scaleX, cropH = rh * scaleY;
        const cropCanvas = document.createElement('canvas');
        cropCanvas.width  = cropW;
        cropCanvas.height = cropH;
        const cctx = cropCanvas.getContext('2d');
        cctx.drawImage(img, rx, ry, cropW, cropH, 0, 0, cropW, cropH);
        // keluar mode seleksi
        toggleSelectMode();
        // jalankan OCR pada crop
        await runOcrOnCanvas(cropCanvas, 'seleksi area');
    });
})();

// OCR seluruh gambar
async function startOcr() {
    const url = currentPreviewUrl;
    if (!url) return;
    const img = document.getElementById('imagePreviewImg');
    const fullCanvas = document.createElement('canvas');
    fullCanvas.width  = img.naturalWidth;
    fullCanvas.height = img.naturalHeight;
    fullCanvas.getContext('2d').drawImage(img, 0, 0);
    await runOcrOnCanvas(fullCanvas, 'seluruh gambar');
}

// Core OCR runner — menerima canvas element
async function runOcrOnCanvas(canvas, label) {
    $('#ocrPanel').show();
    $('#ocrStatus').html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat mesin OCR...');
    $('#ocrResultText').html('');
    $('#btnOcrExtract').prop('disabled', true);
    try {
        if (typeof Tesseract === 'undefined') {
            await new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
                s.onload = resolve; s.onerror = reject;
                document.head.appendChild(s);
            });
        }
        const dataUrl = canvas.toDataURL('image/png');
        const result = await Tesseract.recognize(dataUrl, 'ind+eng', {
            logger: m => {
                if (m.status === 'recognizing text') {
                    const pct = Math.round((m.progress || 0) * 100);
                    $('#ocrStatus').html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses ' + label + '... ' + pct + '%');
                } else if (m.status) {
                    $('#ocrStatus').html('<i class="fas fa-spinner fa-spin mr-1"></i> ' + m.status + '...');
                }
            }
        });
        ocrText = result.data.text.trim();
        if (ocrText) {
            const lines = ocrText.split('\n').filter(l => l.trim()).length;
            $('#ocrStatus').html('<i class="fas fa-check-circle text-success mr-1"></i> ' + lines + ' baris teks — <small class="text-muted">teks bisa di-select/copy</small>');
            renderOcrText(ocrText, $('#ocrSearchInput').val());
        } else {
            $('#ocrStatus').html('<i class="fas fa-info-circle text-warning mr-1"></i> Tidak ada teks yang terdeteksi');
        }
    } catch (err) {
        $('#ocrStatus').html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Gagal: ' + err.message);
    } finally {
        $('#btnOcrExtract').prop('disabled', false);
    }
}

function renderOcrText(text, search) {
    const s = (search || '').trim();
    if (!s) {
        $('#ocrResultText').text(text);
        $('#ocrSearchCount').text('');
        return;
    }
    const esc = s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const matches = text.match(new RegExp(esc, 'gi'));
    const count = matches ? matches.length : 0;
    $('#ocrSearchCount').text(count > 0 ? count + ' ditemukan' : 'Tidak ditemukan');
    const safe = text.replace(/[<>&"]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]));
    const highlighted = safe.replace(new RegExp(esc, 'gi'), m =>
        '<mark style="background:#ffd700;color:#111;border-radius:2px;padding:0 1px;">' + m + '</mark>');
    $('#ocrResultText').html(highlighted.replace(/\n/g, '<br>'));
}

$('#ocrSearchInput').on('input', function () { renderOcrText(ocrText, $(this).val()); });

// Reset saat modal ditutup
$('#imagePreviewModal').on('hidden.bs.modal', function () {
    imgScale = 1;
    imgRotation = 0;
    currentPreviewUrl = '';
    selectMode = false;
    selDown = false;
    ocrText = '';
    $('#imagePreviewImg').attr('src', '').css('transform', 'scale(1) rotate(0deg)');
    $('#ocrSelCanvas').hide();
    $('#ocrPanel').hide();
    $('#ocrResultText').html('');
    $('#ocrStatus').text('');
    $('#ocrSearchInput').val('');
    $('#ocrSearchCount').text('');
    $('#btnSelectRegion').html('<i class="fas fa-crop-alt mr-1"></i>Seleksi').removeClass('btn-danger').addClass('btn-secondary');
    $('#imagePreviewContainer').css('cursor', 'grab');
});

$(document).ready(function() {
    // Pre-fill filter selects saat datang dari link statistik
    @if(!empty($contextQuery['status']))
        $('#filterStatus').val('{{ $contextQuery["status"] }}');
    @endif
    $('#filterPopulation').val(statsContextFilters.population || 'active_year');

    // Auto-open edit modal jika URL mengandung ?edit={id} (dari show.blade.php)
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        // Tunggu DataTable selesai load baru buka modal
        $(document).one('init.dt', function() {
            setTimeout(function() { editSiswa(editId); }, 400);
        });
        // Bersihkan query string dari URL tanpa reload
        history.replaceState(null, '', window.location.pathname);
    }

    // Initialize DataTable
    siswaTable = $('#siswa-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.siswa.data') }}',
            type: 'GET',
            data: function(d) {
                return $.extend({}, d, statsContextFilters);
            },
            error: function(xhr, error, code) {
                console.log('Ajax error:', xhr, error, code);
                if (xhr.status === 500) {
                    alert('Terjadi kesalahan server. Silakan coba lagi atau pilih jumlah data yang lebih sedikit.');
                }
            }
        },
        autoWidth: false,
        columnDefs: [
            { targets: 0, width: '4%'  },   // foto
            { targets: 1, width: '19%' },   // nama/nisn
            { targets: 2, width: '4%'  },   // jk
            { targets: 3, width: '7%'  },   // kelas
            { targets: [4, 5, 6, 7], width: '8%' }, // status
            { targets: 8, width: '10%' },   // keberadaan
            { targets: 9, width: '9%'  },   // tgl masuk
            { targets: 10, width: '15%' },  // aksi
        ],
        columns: [
            { data: 'foto',          name: 'foto',          orderable: false, searchable: false, className: 'text-center align-middle siswa-col-foto' },
            { data: 'nama_nisn',     name: 'nama_lengkap',  className: 'align-middle siswa-col-nama' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin', className: 'text-center align-middle siswa-col-jk' },
            { data: 'kelas',         name: 'kelas',         className: 'align-middle siswa-col-kelas' },
            { data: 'status_ortu',   name: 'status_ortu',   orderable: false, searchable: false, className: 'text-center align-middle siswa-col-status' },
            { data: 'status_diri',   name: 'status_diri',   orderable: false, searchable: false, className: 'text-center align-middle siswa-col-status' },
            { data: 'verval_ijazah', name: 'verval_ijazah', orderable: false, searchable: false, className: 'text-center align-middle siswa-col-status' },
            { data: 'emis_registered', name: 'emis_registered', orderable: false, searchable: false, className: 'text-center align-middle siswa-col-status' },
            { data: 'keberadaan',     name: 'keberadaan', orderable: false, searchable: false, className: 'text-center align-middle siswa-col-keberadaan' },
            { data: 'created_at',    name: 'created_at',    className: 'text-center align-middle siswa-col-tanggal' },
            { data: 'actions',       name: 'actions',       orderable: false, searchable: false, className: 'text-center align-middle siswa-col-aksi' }
        ],
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 10,
        pagingType: 'simple_numbers',
        order: [[9, 'desc']],
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            loadingRecords: "Memuat...",
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Form submit handler
    $('#siswaForm').on('submit', function(e) {
        e.preventDefault();
        saveSiswa();
    });

    // Clear form when modal is closed
    $('#siswaModal').on('hidden.bs.modal', function() {
        clearForm();
    });

    // Clear overlay and tab content when view modal is closed
    $('#viewSiswaModal').on('hidden.bs.modal', function() {
        if (typeof hideAppGlobalOverlay === 'function') {
            hideAppGlobalOverlay();
        }
        // Clear tab contents to avoid stale data on next open
        $('#data-siswa, #data-diri, #data-ortu, #sekolah-asal, #dokumen').empty();
        // Reset to first tab
        $('#siswaDetailTabs a:first').tab('show');
    });

    $(document).on('click', '.js-preview-foto', function() {
        const previewUrl = $(this).data('preview-url');
        const downloadUrl = $(this).data('download-url');
        const studentName = $(this).data('student-name');

        $('#fotoPreviewName').text(studentName);
        $('#fotoPreviewImage').attr('src', previewUrl);
        $('#fotoPreviewDownload').attr('href', downloadUrl);
        $('#fotoPreviewModal').modal('show');
    });

    // Toggle Verval Ijazah
    $(document).on('click', '.btn-toggle-verval', function() {
        const btn = $(this);
        const url = btn.data('url');
        btn.prop('disabled', true);
        $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                if (res.success) {
                    btn.closest('td').html(res.badge);
                    toastr.success(res.verval_ijazah ? 'Ditandai sudah verval ijazah' : 'Tanda verval ijazah dibatalkan');
                }
            })
            .fail(function() {
                toastr.error('Gagal mengubah status verval ijazah');
                btn.prop('disabled', false);
            });
    });

    // Toggle status siswa sudah masuk EMIS
    $(document).on('click', '.btn-toggle-emis', function() {
        const btn = $(this);
        const url = btn.data('url');
        btn.prop('disabled', true);
        $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                if (res.success) {
                    btn.closest('td').html(res.badge);
                    toastr.success(res.emis_registered ? 'Ditandai sudah masuk EMIS' : 'Tanda masuk EMIS dibatalkan');
                }
            })
            .fail(function() {
                toastr.error('Gagal mengubah status EMIS');
                btn.prop('disabled', false);
            });
    });

    // Toggle keberadaan siswa pada rombel aktif (tombol hanya dikirim untuk Super Admin).
    $(document).on('click', '.btn-toggle-keberadaan', function() {
        const btn = $(this);
        const url = btn.data('url');
        btn.prop('disabled', true);
        $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                if (res.success) {
                    toastr.success(res.message || 'Status keberadaan berhasil diperbarui');
                    siswaTable.ajax.reload(null, false);
                }
            })
            .fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal mengubah status keberadaan');
                btn.prop('disabled', false);
            });
    });
});

// Functions outside document.ready
function addSiswa() {
    editingId = null;
    $('#siswaModalLabel').text('Tambah Siswa');
    $('#siswaModal').modal('show');
}

function editSiswa(id) {
    editingId = id;
    $('#siswaModalLabel').text('Edit Siswa');
    
    // Load siswa data
    $.get(`{{ url('admin/siswa') }}/${id}`)
        .done(function(response) {
            if (response.success) {
                const siswa = response.data;
                $('#nisn').val(siswa.nisn);
                $('#nama_lengkap').val(siswa.nama_lengkap);
                $('#jenis_kelamin').val(siswa.jenis_kelamin);
                $('#siswaModal').modal('show');
            }
        })
        .fail(function() {
            toastr.error('Gagal memuat data siswa', 'Error!');
        });
}

function showSiswa(id) {
    $.get(`{{ url('admin/siswa') }}/${id}`)
        .done(function(response) {
            if (response.success) {
                const siswa = response.data;
                $('#viewSiswaFullDetailLink').attr('href', `{{ url('admin/siswa') }}/${id}`);
                loadSiswaDataTab(siswa);
                loadDataDiriTab(siswa);
                loadDataOrtuTab(siswa);
                loadSekolahAsalTab(siswa);
                loadDokumenTab(siswa.id);
                $('#viewSiswaModal').modal('show');
            }
        })
        .fail(function() {
            toastr.error('Gagal memuat data siswa', 'Error!');
        });
}

function loadSiswaDataTab(siswa) {
    const createdAt = new Date(siswa.created_at).toLocaleString('id-ID');
    const updatedAt = new Date(siswa.updated_at).toLocaleString('id-ID');
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-user"></i> Informasi Akun</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NISN</strong></td><td>${siswa.nisn || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nomor Tes PPDB</strong></td><td>${siswa.nomor_tes || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama Lengkap</strong></td><td>${siswa.nama_lengkap || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Jenis Kelamin</strong></td><td>${siswa.jenis_kelamin == 'L' ? '<span class="badge badge-primary">Laki-laki</span>' : '<span class="badge badge-danger">Perempuan</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Username</strong></td><td>${siswa.user.username || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Email</strong></td><td>${siswa.user.email || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-check-circle"></i> Status Kelengkapan</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Data Ortu</strong></td><td>${siswa.data_ortu_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Data Diri</strong></td><td>${siswa.data_diri_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Status Login</strong></td><td>${siswa.user.is_first_login ? '<span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Ganti Password</span>' : '<span class="badge badge-success"><i class="fas fa-check"></i> Sudah Ganti Password</span>'}</td></tr>
                </table>
                
                <h6 class="text-primary mt-3"><i class="fas fa-key"></i> Akun Login</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Username</strong></td><td><code>${siswa.user.username || '-'}</code></td></tr>
                    <tr><td class="bg-light"><strong>Email</strong></td><td>${siswa.user.email || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Password</strong></td><td>${renderPasswordPreview(siswa.user.readable_password)}</td></tr>
                </table>
                
                <h6 class="text-primary mt-3"><i class="fas fa-history"></i> History</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Dibuat Oleh</strong></td><td>${siswa.created_by_name || 'System'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Dibuat</strong></td><td>${createdAt}</td></tr>
                    <tr><td class="bg-light"><strong>Diupdate Oleh</strong></td><td>${siswa.updated_by_name || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Update</strong></td><td>${updatedAt}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-siswa').html(html);
}

function renderPhoneLink(value, label) {
    if (!value) return '-';

    const display = $('<div>').text(value).html();
    const number = String(value).replace(/[^0-9+]/g, '');
    const title = $('<div>').text(label || 'Hubungi nomor ini').html();

    return number
        ? `<a href="tel:${number}" title="${title}"><i class="fas fa-phone-alt mr-1"></i>${display}</a>`
        : display;
}

function loadDataDiriTab(siswa) {
    const tglLahir = siswa.tanggal_lahir ? new Date(siswa.tanggal_lahir).toLocaleDateString('id-ID') : '-';
    
    // Handle alamat siswa
    let alamatHtml = '';
    
    // Cek jenis tempat tinggal atau alamat_sama_ortu
    const tinggalBersamaOrtu = siswa.jenis_tempat_tinggal === 'Bersama Orang Tua' || siswa.alamat_sama_ortu;
    
    if (tinggalBersamaOrtu) {
        // Alamat sama dengan ortu / tinggal bersama ortu
        const ortu = siswa.ortu;
        if (ortu && ortu.alamat_ortu) {
            const jenisInfo = siswa.jenis_tempat_tinggal === 'Bersama Orang Tua' 
                ? 'Tinggal Bersama Orang Tua' 
                : 'Alamat sama dengan Orang Tua';
            
            alamatHtml = `
                <div class="alert alert-info mb-2">
                    <i class="fas fa-info-circle"></i> <strong>${jenisInfo}</strong>
                </div>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>No. KK</strong></td><td>${ortu.no_kk || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${ortu.alamat_ortu}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${ortu.rt_ortu || '-'} / ${ortu.rw_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${ortu.kelurahan ? ortu.kelurahan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${ortu.kecamatan ? ortu.kecamatan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${ortu.kabupaten ? ortu.kabupaten.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${ortu.provinsi ? ortu.provinsi.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            `;
        } else {
            alamatHtml = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Tinggal bersama ortu, tapi data alamat ortu belum dilengkapi</div>';
        }
    } else if (siswa.alamat_siswa) {
        // Alamat sendiri
        const jenisInfo = siswa.jenis_tempat_tinggal ? `<div class="alert alert-info mb-2"><i class="fas fa-home"></i> <strong>Jenis Tempat Tinggal: ${siswa.jenis_tempat_tinggal}</strong></div>` : '';
        
        alamatHtml = `
            ${jenisInfo}
            <table class="table table-detail table-sm table-bordered">
                <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${siswa.alamat_siswa}</td></tr>
                <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${siswa.rt_siswa || '-'} / ${siswa.rw_siswa || '-'}</td></tr>
                <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${siswa.kodepos_siswa || '-'}</td></tr>
            </table>
        `;
    } else {
        alamatHtml = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Data alamat belum dilengkapi</div>';
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NIK</strong></td><td>${siswa.nik || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tempat Lahir</strong></td><td>${siswa.tempat_lahir || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Lahir</strong></td><td>${tglLahir}</td></tr>
                    <tr><td class="bg-light"><strong>Jumlah Saudara</strong></td><td>${siswa.jumlah_saudara || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Anak Ke</strong></td><td>${siswa.anak_ke || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Hobi</strong></td><td>${siswa.hobi || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Cita-cita</strong></td><td>${siswa.cita_cita || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>No. HP</strong></td><td>${renderPhoneLink(siswa.nomor_hp, 'Hubungi siswa')}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Alamat Siswa</h6>
                ${alamatHtml}
            </div>
        </div>
    `;
    $('#data-diri').html(html);
}

function loadDataOrtuTab(siswa) {
    const ortu = siswa.ortu;
    
    if (!ortu || !siswa.data_ortu_completed) {
        $('#data-ortu').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Data orang tua belum dilengkapi
            </div>
        `);
        return;
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-male"></i> Data Ayah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ayah == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : ortu.status_ayah == 'meninggal' ? '<span class="badge badge-secondary">Meninggal</span>' : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${renderPhoneLink(ortu.hp_ayah, 'Hubungi ayah')}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ayah || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-female"></i> Data Ibu</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ibu == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : ortu.status_ibu == 'meninggal' ? '<span class="badge badge-secondary">Meninggal</span>' : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${renderPhoneLink(ortu.hp_ibu, 'Hubungi ibu')}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ibu || '-'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-primary"><i class="fas fa-home"></i> Alamat Orang Tua</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="20%" class="bg-light"><strong>No. KK</strong></td><td>${ortu.no_kk || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${ortu.alamat_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${ortu.rt_ortu || '-'} / ${ortu.rw_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${ortu.kelurahan ? ortu.kelurahan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${ortu.kecamatan ? ortu.kecamatan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${ortu.kabupaten ? ortu.kabupaten.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${ortu.provinsi ? ortu.provinsi.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-ortu').html(html);
}

function loadSekolahAsalTab(siswa) {
    if (!siswa.npsn_asal_sekolah) {
        $('#sekolah-asal').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Data sekolah asal belum diisi
            </div>
        `);
        return;
    }
    
    // Handle both camelCase and snake_case
    const sekolah = siswa.sekolah_asal || siswa.sekolahAsal;
    
    if (!sekolah) {
        $('#sekolah-asal').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> NPSN: ${siswa.npsn_asal_sekolah} - Data sekolah tidak ditemukan di database
            </div>
        `);
        return;
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-school"></i> Informasi Sekolah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NPSN | NSM</strong></td><td><span class="badge badge-primary">${sekolah.npsn || '-'}</span> <span class="text-muted">|</span> <span class="badge badge-info">${sekolah.nsm || '-'}</span></td></tr>
                    <tr><td class="bg-light"><strong>Nama Sekolah</strong></td><td><strong>${sekolah.nama || '-'}</strong></td></tr>
                    <tr><td class="bg-light"><strong>Bentuk Pendidikan</strong></td><td>${sekolah.bentuk_pendidikan || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Status</strong></td><td>
                        ${sekolah.status_sekolah == 'Negeri' ? '<span class="badge badge-success">Negeri</span>' : sekolah.status_sekolah == 'Swasta' ? '<span class="badge badge-info">Swasta</span>' : '<span class="badge badge-secondary">' + (sekolah.status_sekolah || '-') + '</span>'}
                    </td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Lokasi Sekolah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Provinsi</strong></td><td>${sekolah.provinsi || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${sekolah.kabupaten_kota || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${sekolah.kecamatan || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${sekolah.alamat_jalan || '-'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12 text-center">
                <a href="{{ url('admin/sekolah-asal') }}/${sekolah.npsn}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Lihat Detail Sekolah & Daftar Siswa
                </a>
            </div>
        </div>
    `;
    $('#sekolah-asal').html(html);
}

function loadDokumenTab(siswaId) {
    $('#dokumen').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat dokumen...</div>');
    
    $.get(`{{ url('admin/siswa') }}/${siswaId}/dokumen`)
        .done(function(response) {
            if (response.success) {
                const dokumen = response.data;
                let html = '';
                
                if (dokumen.length === 0) {
                    html = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada dokumen yang diupload
                        </div>
                    `;
                } else {
                    html = '<div class="row">';
                    dokumen.forEach(dok => {
                        const uploadDate = new Date(dok.created_at).toLocaleDateString('id-ID');
                        // Deteksi tipe dari mime_type (tersedia untuk file baru); fallback ke ekstensi URL (file lama)
                        const mimeType = dok.mime_type || '';
                        const isPdf = mimeType ? mimeType === 'application/pdf' : dok.file_url.toLowerCase().endsWith('.pdf');
                        const isImage = mimeType ? mimeType.startsWith('image/') : /\.(jpg|jpeg|png|gif|webp)$/i.test(dok.file_url);
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="dokumen-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="fas ${isPdf ? 'fa-file-pdf text-danger' : isImage ? 'fa-file-image text-primary' : 'fa-file text-secondary'}"></i> 
                                                ${dok.jenis_dokumen_label}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> ${uploadDate} | 
                                                <i class="fas fa-hdd"></i> ${dok.file_size_formatted}
                                            </small>
                                            ${dok.keterangan ? `<p class="mb-1 mt-2"><small>${dok.keterangan}</small></p>` : ''}
                                        </div>
                                        <div class="btn-group-vertical">
                                            <a href="${dok.file_url}" 
                                               class="btn btn-sm ${isImage ? 'btn-info' : 'btn-primary'} mb-1 btn-preview-doc" 
                                               data-url="${dok.file_url}"
                                               data-type="${isImage ? 'image' : isPdf ? 'pdf' : 'other'}"
                                               data-title="${dok.jenis_dokumen_label}"
                                               data-download-jpg-url="${dok.download_jpg_url}"
                                               title="${isImage ? 'Preview & Zoom' : 'Lihat File'}">
                                                <i class="fas ${isImage ? 'fa-search-plus' : 'fa-eye'}"></i>
                                            </a>
                                            <a href="${dok.file_url}" 
                                               download 
                                               class="btn btn-sm btn-success mb-1" 
                                               title="Download"
                                               onclick="setTimeout(()=>$('#viewSiswaModal').modal('hide'),300)">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="${dok.download_jpg_url}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Download JPG"
                                               onclick="setTimeout(()=>$('#viewSiswaModal').modal('hide'),300)">
                                                <i class="fas fa-file-image"></i> JPG
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                
                $('#dokumen').html(html);
                
                // Initialize preview click handler
                $(document).off('click', '.btn-preview-doc');
                $(document).on('click', '.btn-preview-doc', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    const type = $(this).data('type');
                    const title = $(this).data('title');
                    const dlJpgUrl = $(this).data('download-jpg-url');
                    if (type === 'image') {
                        openImagePreview(url, title, url, dlJpgUrl);
                    } else {
                        // Untuk PDF dan file lain, buka di tab baru
                        window.open(url, '_blank');
                    }
                });
            }
        })
        .fail(function() {
            $('#dokumen').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat dokumen</div>');
        });
}

function deleteSiswa(id) {
    Swal.fire({
        title: 'Hapus Data Siswa?',
        html: '<p>Data berikut akan dihapus <strong>permanen</strong>:</p>' +
              '<ul class="text-left">' +
              '<li>Data siswa</li>' +
              '<li>User account</li>' +
              '<li>Data orang tua</li>' +
              '<li>Dokumen yang diupload</li>' +
              '</ul>' +
              '<p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `{{ url('admin/siswa') }}/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    siswaTable.ajax.reload();
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data siswa', 'error');
            });
        }
    });
  }

  function renderPasswordPreview(password) {
    if (!password) {
      return '<span class="text-muted">Tidak tersedia</span>';
    }

    const safePassword = $('<div>').text(password).html();

    return `
      <code class="text-danger js-password-text" data-password="${safePassword}">••••••••</code>
      <button type="button" class="btn btn-xs btn-outline-secondary ml-2 js-toggle-password" aria-label="Tampilkan password">
        <i class="fas fa-eye"></i>
      </button>
      <button type="button" class="btn btn-xs btn-outline-secondary ml-1 js-copy-password" data-password="${safePassword}" aria-label="Salin password">
        <i class="fas fa-copy"></i>
      </button>
    `;
  }

  $(document).on('click', '.js-toggle-password', function () {
    const button = $(this);
    const passwordElement = button.siblings('.js-password-text');

    if (!passwordElement.length) {
      return;
    }

    const isHidden = passwordElement.text() === '••••••••';
    passwordElement.text(isHidden ? passwordElement.data('password') : '••••••••');
    button.html('<i class="fas ' + (isHidden ? 'fa-eye-slash' : 'fa-eye') + '"></i>');
  });

  $(document).on('click', '.js-copy-password', function () {
    const password = $(this).data('password');
    navigator.clipboard.writeText(password).then(function () {
      toastr.success('Password berhasil disalin!');
    }, function () {
      toastr.error('Gagal menyalin password', 'Error!');
    });
  });

function resetPassword(id) {
    resetPasswordSiswaId = id;
    $('#resetPasswordSiswaModal').modal('show');
}

$('#confirmResetPasswordSiswa').on('click', function () {
    if (!resetPasswordSiswaId) {
        $('#resetPasswordSiswaModal').modal('hide');
        return;
    }

    const button = $(this);
    const originalHtml = button.html();

    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

    $.ajax({
        url: `{{ url('admin/siswa') }}/${resetPasswordSiswaId}/reset-password`,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}'
        }
    })
    .done(function(response) {
        if (response.success) {
            const info = response.default_password
                ? `${response.message}\nPassword default baru: ${response.default_password}`
                : response.message;
            toastr.success(info, 'Berhasil!');
            $('#resetPasswordSiswaModal').modal('hide');
            resetPasswordSiswaId = null;
        } else {
            toastr.error(response.message, 'Gagal!');
        }
    })
    .fail(function() {
        toastr.error('Terjadi kesalahan saat reset password', 'Error!');
    })
    .always(function () {
        button.prop('disabled', false).html(originalHtml);
    });
});

function saveSiswa() {
    const formData = new FormData($('#siswaForm')[0]);
    const url = editingId ? `{{ url('admin/siswa') }}/${editingId}` : '{{ route('admin.siswa.store') }}';
    const method = editingId ? 'PUT' : 'POST';
    
    if (editingId) {
        formData.append('_method', 'PUT');
    }

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .done(function(response) {
        if (response.success) {
            $('#siswaModal').modal('hide');
            toastr.success(response.message, 'Berhasil!');
            siswaTable.ajax.reload();
            clearForm();
        } else {
            toastr.error(response.message, 'Gagal!');
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(field) {
                const input = $(`#${field}`);
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(errors[field][0]);
            });
            toastr.error('Silakan periksa data yang diisi', 'Validasi Gagal!');
        } else {
            toastr.error('Terjadi kesalahan pada server. Silakan coba lagi.', 'Error!');
        }
    });
}

function clearForm() {
    $('#siswaForm')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    editingId = null;
}

// Filter Functions
$(document).ready(function() {
    function syncAcademicFilters() {
        const isActiveRoster = $('#filterPopulation').val() === 'active_year';
        const $tingkat = $('#filterTingkat');
        const $kelas = $('#filterKelas');

        $tingkat.prop('disabled', !isActiveRoster);
        if (!isActiveRoster) {
            $tingkat.val('');
            $kelas.val('').prop('disabled', true).html('<option value="">Tidak berlaku</option>');
        } else if (!$tingkat.val()) {
            $kelas.prop('disabled', true).html('<option value="">Pilih Tingkat Dulu</option>');
        }

        const label = populationLabels[$('#filterPopulation').val()] || populationLabels.active_year;
        $('#hero-stat-label, #stat-total-label').text(label);
    }

    syncAcademicFilters();

    $('#filterPopulation').on('change', function() {
        syncAcademicFilters();
        applyFilters();
    });

    // Filter Tingkat - Load Kelas
    $('#filterTingkat').on('change', function() {
        let tingkat = $(this).val();
        let $kelasSelect = $('#filterKelas');
        
        // Jika pilih "Tanpa Rombel", disable kelas select
        if (tingkat === 'tanpa_rombel') {
            $kelasSelect.prop('disabled', true).html('<option value="">N/A (Tanpa Rombel)</option>');
            applyFilters();
            return;
        }
        
        $kelasSelect.prop('disabled', true).html('<option value="">Memuat...</option>');
        
        if (!tingkat) {
            $kelasSelect.html('<option value="">Pilih Tingkat Dulu</option>');
            applyFilters();
            return;
        }
        
        $.ajax({
            url: '{{ route('admin.siswa.kelas-by-tingkat') }}',
            data: { tingkat: tingkat },
            success: function(data) {
                let options = '<option value="">Semua Kelas</option>';
                data.forEach(function(kelas) {
                    options += `<option value="${kelas.id}">${kelas.text}</option>`;
                });
                $kelasSelect.html(options).prop('disabled', false);
                applyFilters();
            },
            error: function() {
                $kelasSelect.html('<option value="">Error loading</option>');
                toastr.error('Gagal memuat data kelas');
            }
        });
    });
    
    // Apply filter on change
    $('#filterJenisKelamin, #filterKelas, #filterStatus, #filterEmisStatus').on('change', function() {
        applyFilters();
    });
    
    // Reset Filter
    $('#btnResetFilter').on('click', function() {
        $('#filterJenisKelamin').val('');
        $('#filterPopulation').val('active_year');
        $('#filterTingkat').val('');
        $('#filterKelas').val('').prop('disabled', true).html('<option value="">Pilih Tingkat Dulu</option>');
        $('#filterStatus').val('');
        $('#filterEmisStatus').val('');
        syncAcademicFilters();
        applyFilters();
    });
    
    function applyFilters() {
        let jk = $('#filterJenisKelamin').val();
        let population = $('#filterPopulation').val() || 'active_year';
        let tingkat = $('#filterTingkat').val();
        let kelas = $('#filterKelas').val();
        let status = $('#filterStatus').val();
        let emisStatus = $('#filterEmisStatus').val();
        
        // Build filter parameters
        let filterParams = Object.assign({}, statsContextFilters);
        filterParams.population = population;
        if (jk) filterParams.jenis_kelamin = jk;
        if (tingkat) filterParams.tingkat = tingkat;
        if (kelas) filterParams.kelas_id = kelas;
        if (status) filterParams.status = status;
        if (emisStatus) filterParams.emis_status = emisStatus;
        
        // Reload DataTable with filters
        siswaTable.settings()[0].ajax.data = function(d) {
            return $.extend({}, d, filterParams);
        };
        siswaTable.ajax.reload();

        // Update export URL with active filters
        let exportBase = '{{ route("admin.siswa.export") }}';
        let exportParams = new URLSearchParams(filterParams);
        $('#btnExportSiswa').attr('href', exportBase + (exportParams.toString() ? '?' + exportParams.toString() : ''));

        // Update stats cards
        $.get('{{ route("admin.siswa.stats") }}', filterParams, function(data) {
            $('#stat-total').text(new Intl.NumberFormat('id-ID').format(data.total_siswa));
            $('#stat-laki').text(new Intl.NumberFormat('id-ID').format(data.laki_laki));
            $('#stat-perempuan').text(new Intl.NumberFormat('id-ID').format(data.perempuan));
            $('#stat-lengkap').text(new Intl.NumberFormat('id-ID').format(data.data_lengkap));
            $('#hero-stat-total').text(new Intl.NumberFormat('id-ID').format(data.total_siswa));
            $('#hero-stat-lengkap').text(new Intl.NumberFormat('id-ID').format(data.data_lengkap));
        });
    }
});

    // Export: beforeunload fires saat browser navigasi ke URL download, tapi pageshow tidak fire
    // Sembunyikan overlay via focus event atau timeout fallback
    $('#btnExportSiswa').on('click', function () {
        var hideOverlay = function () {
            if (typeof hideAppGlobalOverlay === 'function') {
                hideAppGlobalOverlay();
            } else if (typeof appHideGlobalOverlay === 'function') {
                appHideGlobalOverlay();
            }
            $('#appGlobalOverlay').removeClass('active').attr('aria-hidden', 'true');
        };
        var timers = [
            setTimeout(hideOverlay, 800),
            setTimeout(hideOverlay, 2000),
            setTimeout(hideOverlay, 5000)
        ];
        $(window).one('focus.exportHide', function () {
            timers.forEach(clearTimeout);
            setTimeout(hideOverlay, 200);
        });
    });

</script>
@stop
