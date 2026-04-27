@extends('adminlte::page')

@section('title', 'Data Siswa - SIMANSA')

@section('css')
<style>
    .student-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr);
        gap: .7rem;
        align-items: stretch;
        margin-bottom: .65rem;
    }

    .student-hero__main {
        background: linear-gradient(135deg, rgba(37, 99, 235, .92), rgba(13, 148, 136, .84));
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 20px;
        padding: .95rem 1.05rem;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
    }

    .student-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: rgba(255, 255, 255, .86);
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .student-hero__title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 .25rem 0;
    }

    .student-hero__subtitle {
        color: rgba(255, 255, 255, .9);
        font-size: .84rem;
        line-height: 1.45;
        margin: 0;
        max-width: 780px;
    }

    .student-hero__side {
        display: grid;
        gap: .9rem;
    }

    .student-hero-chip {
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 14px;
        padding: .62rem .82rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    .student-hero-chip__label {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .2rem;
    }

    .student-hero-chip__value {
        display: block;
        color: #0f172a;
        font-size: 1.06rem;
        font-weight: 800;
        line-height: 1.2;
    }

    @media (max-width: 991.98px) {
        .student-hero {
            grid-template-columns: 1fr;
        }

        .student-hero__side {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .student-hero__title {
            font-size: 1.7rem;
        }

        .student-hero__side {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop

@section('content_header')
    <div class="student-hero">
        <div class="student-hero__main">
            <div class="student-hero__eyebrow">
                <i class="fas fa-user-graduate"></i>
                Manajemen Peserta Didik
            </div>
            <h1 class="student-hero__title">Data Siswa</h1>
            <p class="student-hero__subtitle">
                Pantau data siswa aktif, kelengkapan biodata, rombel, dan akses akun dari satu halaman operasional yang lebih rapi.
            </p>
        </div>
        <div class="student-hero__side">
            <div class="student-hero-chip">
                <span class="student-hero-chip__label">Total Siswa</span>
                <span class="student-hero-chip__value" id="hero-stat-total">{{ number_format($stats['total_siswa']) }}</span>
            </div>
            <div class="student-hero-chip">
                <span class="student-hero-chip__label">Data Lengkap</span>
                <span class="student-hero-chip__value" id="hero-stat-lengkap">{{ number_format($stats['data_lengkap']) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<style>
    .student-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 132px;
        border: 0;
        border-radius: 16px;
        padding: .86rem .86rem .8rem;
        color: #fff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .10);
        display: flex;
        align-items: flex-start;
        gap: .75rem;
    }

    .student-stat-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -36px;
        width: 144px;
        height: 144px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
    }

    .student-stat-card--blue { background: linear-gradient(135deg, #4f46e5, #6366f1); }
    .student-stat-card--cyan { background: linear-gradient(135deg, #0ea5e9, #22d3ee); }
    .student-stat-card--rose { background: linear-gradient(135deg, #fb7185, #f43f5e); }
    .student-stat-card--green { background: linear-gradient(135deg, #10b981, #34d399); }

    .student-stat-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .16);
        font-size: .92rem;
        position: relative;
        z-index: 1;
        flex: 0 0 42px;
    }

    .student-stat-card__body {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        min-width: 0;
    }

    .student-stat-card__label {
        position: relative;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: .25rem;
    }

    .student-stat-card__value {
        position: relative;
        font-size: 1.48rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: .35rem;
    }

    .student-stat-card__desc {
        position: relative;
        opacity: .92;
        line-height: 1.28;
        font-size: .78rem;
    }

    .student-management-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .student-management-card .card-header {
        background: linear-gradient(135deg, rgba(37, 99, 235, .98), rgba(13, 148, 136, .9));
        color: #fff;
        border-bottom: 0;
        padding: .8rem 1rem;
    }

    .student-filter-panel {
        background: linear-gradient(180deg, rgba(248, 250, 252, .96), rgba(255, 255, 255, .98));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        padding: 1rem 1rem .35rem;
        margin-bottom: 1rem;
    }

    .student-filter-label {
        display: block;
        font-size: .82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: .4rem;
    }

    .student-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .student-table-note {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.5;
        margin: 0;
    }

    @media (max-width: 767.98px) {
        .student-table-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .student-stat-card {
            flex-direction: column;
            gap: .9rem;
        }

        .student-stat-card__body {
            width: 100%;
        }
    }
</style>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="student-stat-card student-stat-card--blue">
            <div class="student-stat-card__icon"><i class="fas fa-users"></i></div>
            <div class="student-stat-card__body">
                <div class="student-stat-card__label">Total Siswa</div>
                <div class="student-stat-card__value" id="stat-total">{{ number_format($stats['total_siswa']) }}</div>
                <div class="student-stat-card__desc">Semua siswa yang saat ini tercatat di SIMANSA.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="student-stat-card student-stat-card--cyan">
            <div class="student-stat-card__icon"><i class="fas fa-male"></i></div>
            <div class="student-stat-card__body">
                <div class="student-stat-card__label">Laki-Laki</div>
                <div class="student-stat-card__value" id="stat-laki">{{ number_format($stats['laki_laki']) }}</div>
                <div class="student-stat-card__desc">Jumlah siswa laki-laki sesuai filter yang sedang aktif.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="student-stat-card student-stat-card--rose">
            <div class="student-stat-card__icon"><i class="fas fa-female"></i></div>
            <div class="student-stat-card__body">
                <div class="student-stat-card__label">Perempuan</div>
                <div class="student-stat-card__value" id="stat-perempuan">{{ number_format($stats['perempuan']) }}</div>
                <div class="student-stat-card__desc">Jumlah siswa perempuan sesuai filter yang sedang aktif.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="student-stat-card student-stat-card--green">
            <div class="student-stat-card__icon"><i class="fas fa-check-circle"></i></div>
            <div class="student-stat-card__body">
                <div class="student-stat-card__label">Data Lengkap</div>
                <div class="student-stat-card__value" id="stat-lengkap">{{ number_format($stats['data_lengkap']) }}</div>
                <div class="student-stat-card__desc">Siswa dengan data diri dan orang tua yang sudah lengkap.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card student-management-card">
            <div class="card-header">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <h3 class="card-title mb-3 mb-lg-0">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Manajemen Data Siswa
                    </h3>
                    <div class="card-tools ml-0">
                        @can('create-siswa')
                            <a href="{{ route('admin.siswa.import') }}" class="btn btn-light mr-2">
                                <i class="fas fa-file-excel"></i> Import Data Siswa
                            </a>
                            <a href="{{ route('admin.emis-import.form') }}" class="btn btn-light mr-2">
                                <i class="fas fa-cloud-download-alt"></i> Import EMIS
                            </a>
                            <a href="{{ route('admin.siswa.import-npsn') }}" class="btn btn-outline-light mr-2">
                                <i class="fas fa-school"></i> Import NPSN
                            </a>
                            <button type="button" class="btn btn-warning" onclick="addSiswa()">
                                <i class="fas fa-plus"></i> Tambah Siswa
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="student-filter-panel">
                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterJenisKelamin" class="student-filter-label">
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
                                <label for="filterTingkat" class="student-filter-label">
                                    <i class="fas fa-layer-group mr-1"></i> Tingkat
                                </label>
                                <select id="filterTingkat" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($tingkatOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                    <option value="tanpa_rombel">Tanpa Rombel</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterKelas" class="student-filter-label">
                                    <i class="fas fa-door-open mr-1"></i> Kelas
                                </label>
                                <select id="filterKelas" class="form-control form-control-sm" disabled>
                                    <option value="">Pilih Tingkat Dulu</option>
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label for="filterStatus" class="student-filter-label">
                                <i class="fas fa-check-circle mr-1"></i> Status Data
                            </label>
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="lengkap">Data Lengkap</option>
                                <option value="belum">Belum Lengkap</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="student-table-toolbar">
                    <p class="student-table-note">
                        Gunakan filter untuk memantau siswa per tingkat, kelengkapan biodata, dan rombel. Klik foto untuk preview dan unduh cepat.
                    </p>
                </div>

                <div class="table-responsive">
                    <table id="siswa-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Kelas</th>
                                <th>Username</th>
                                <th>Status Ortu</th>
                                <th>Status Diri</th>
                                <th>Tgl Dibuat</th>
                                <th>Aksi</th>
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
                    <button type="submit" class="btn btn-primary">
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

$(document).ready(function() {
    // Initialize DataTable
    siswaTable = $('#siswa-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.siswa.data') }}',
            type: 'GET',
            error: function(xhr, error, code) {
                console.log('Ajax error:', xhr, error, code);
                if (xhr.status === 500) {
                    alert('Terjadi kesalahan server. Silakan coba lagi atau pilih jumlah data yang lebih sedikit.');
                }
            }
        },
        columns: [
            { data: 'foto', name: 'foto', orderable: false, searchable: false, className: 'foto-cell align-middle' },
            { data: 'nisn', name: 'nisn' },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin' },
            { data: 'kelas', name: 'kelas' },
            { data: 'username', name: 'username' },
            { data: 'status_ortu', name: 'status_ortu', orderable: false, searchable: false },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        order: [[8, 'desc']],
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
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ayah == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : '<span class="badge badge-secondary">Meninggal</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ayah || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-female"></i> Data Ibu</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ibu == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : '<span class="badge badge-secondary">Meninggal</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ibu || '-'}</td></tr>
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
                    <tr><td width="40%" class="bg-light"><strong>NPSN</strong></td><td><span class="badge badge-primary">${sekolah.npsn || '-'}</span></td></tr>
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
                        const isPdf = dok.file_url.toLowerCase().endsWith('.pdf');
                        const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(dok.file_url);
                        
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
                                               title="${isImage ? 'Preview & Zoom' : 'Lihat File'}">
                                                <i class="fas ${isImage ? 'fa-search-plus' : 'fa-eye'}"></i>
                                            </a>
                                            <a href="${dok.file_url}" 
                                               download 
                                               class="btn btn-sm btn-success" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
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
                    
                    if (type === 'image') {
                        // Open image in new window with zoom functionality
                        const win = window.open('', 'ImagePreview', 'width=1000,height=800,scrollbars=yes,resizable=yes');
                        win.document.write('<!DOCTYPE html><html><head><title>' + title + '</title>');
                        win.document.write('<style>');
                        win.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
                        win.document.write('body { background: #1a1a1a; font-family: Arial, sans-serif; overflow: hidden; }');
                        win.document.write('.header { background: #2d2d2d; padding: 15px 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }');
                        win.document.write('.header h3 { margin: 0; font-size: 18px; font-weight: 500; }');
                        win.document.write('.controls { display: flex; gap: 10px; }');
                        win.document.write('.btn { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background 0.3s; }');
                        win.document.write('.btn:hover { background: #0056b3; }');
                        win.document.write('.btn-success { background: #28a745; }');
                        win.document.write('.btn-success:hover { background: #1e7e34; }');
                        win.document.write('.btn-danger { background: #dc3545; }');
                        win.document.write('.btn-danger:hover { background: #c82333; }');
                        win.document.write('.image-container { width: 100%; height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; overflow: auto; cursor: grab; position: relative; }');
                        win.document.write('.image-container.dragging { cursor: grabbing; }');
                        win.document.write('.image-container img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s; user-select: none; }');
                        win.document.write('.zoom-info { position: absolute; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; }');
                        win.document.write('</style></head><body>');
                        win.document.write('<div class="header"><h3>' + title + '</h3>');
                        win.document.write('<div class="controls">');
                        win.document.write('<button class="btn" onclick="zoomOut()">🔍 Zoom Out</button>');
                        win.document.write('<button class="btn" onclick="resetZoom()">↺ Reset</button>');
                        win.document.write('<button class="btn" onclick="zoomIn()">🔍 Zoom In</button>');
                        win.document.write('<a href="' + url + '" download class="btn btn-success" style="text-decoration:none;">⬇ Download</a>');
                        win.document.write('<button class="btn btn-danger" onclick="window.close()">✕ Close</button>');
                        win.document.write('</div></div>');
                        win.document.write('<div class="image-container" id="imageContainer">');
                        win.document.write('<img src="' + url + '" id="previewImage" alt="' + title + '">');
                        win.document.write('<div class="zoom-info" id="zoomInfo">100%</div></div>');
                        win.document.write('<scr' + 'ipt>');
                        win.document.write('let scale = 1;');
                        win.document.write('const img = document.getElementById("previewImage");');
                        win.document.write('const container = document.getElementById("imageContainer");');
                        win.document.write('const zoomInfo = document.getElementById("zoomInfo");');
                        win.document.write('let isDragging = false;');
                        win.document.write('let startX, startY, scrollLeft, scrollTop;');
                        win.document.write('function updateZoom() { img.style.transform = "scale(" + scale + ")"; zoomInfo.textContent = Math.round(scale * 100) + "%"; }');
                        win.document.write('function zoomIn() { scale = Math.min(scale + 0.2, 5); updateZoom(); }');
                        win.document.write('function zoomOut() { scale = Math.max(scale - 0.2, 0.2); updateZoom(); }');
                        win.document.write('function resetZoom() { scale = 1; updateZoom(); container.scrollTop = 0; container.scrollLeft = 0; }');
                        win.document.write('container.addEventListener("wheel", function(e) { e.preventDefault(); if (e.deltaY < 0) { zoomIn(); } else { zoomOut(); } });');
                        win.document.write('container.addEventListener("mousedown", function(e) { isDragging = true; container.classList.add("dragging"); startX = e.pageX - container.offsetLeft; startY = e.pageY - container.offsetTop; scrollLeft = container.scrollLeft; scrollTop = container.scrollTop; });');
                        win.document.write('container.addEventListener("mouseleave", function() { isDragging = false; container.classList.remove("dragging"); });');
                        win.document.write('container.addEventListener("mouseup", function() { isDragging = false; container.classList.remove("dragging"); });');
                        win.document.write('container.addEventListener("mousemove", function(e) { if (!isDragging) return; e.preventDefault(); const x = e.pageX - container.offsetLeft; const y = e.pageY - container.offsetTop; const walkX = (x - startX) * 2; const walkY = (y - startY) * 2; container.scrollLeft = scrollLeft - walkX; container.scrollTop = scrollTop - walkY; });');
                        win.document.write('document.addEventListener("keydown", function(e) { if (e.key === "+" || e.key === "=") zoomIn(); if (e.key === "-") zoomOut(); if (e.key === "0") resetZoom(); if (e.key === "Escape") window.close(); });');
                        win.document.write('</scr' + 'ipt></body></html>');
                        win.document.close();
                    } else {
                        // For PDF and other files, open in new tab
                        window.open(url, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
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
    if (confirm('Apakah Anda yakin ingin reset password siswa ini?\n\nPassword akan direset ke NISN dan siswa diminta login ulang.')) {
        $.ajax({
            url: `{{ url('admin/siswa') }}/${id}/reset-password`,
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
            } else {
                toastr.error(response.message, 'Gagal!');
            }
        })
        .fail(function() {
            toastr.error('Terjadi kesalahan saat reset password', 'Error!');
        });
    }
}

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
    $('#filterJenisKelamin, #filterKelas, #filterStatus').on('change', function() {
        applyFilters();
    });
    
    // Reset Filter
    $('#btnResetFilter').on('click', function() {
        $('#filterJenisKelamin').val('');
        $('#filterTingkat').val('');
        $('#filterKelas').val('').prop('disabled', true).html('<option value="">Pilih Tingkat Dulu</option>');
        $('#filterStatus').val('');
        applyFilters();
    });
    
    function applyFilters() {
        let jk = $('#filterJenisKelamin').val();
        let tingkat = $('#filterTingkat').val();
        let kelas = $('#filterKelas').val();
        let status = $('#filterStatus').val();
        
        // Build filter parameters
        let filterParams = {};
        if (jk) filterParams.jenis_kelamin = jk;
        if (tingkat) filterParams.tingkat = tingkat;
        if (kelas) filterParams.kelas_id = kelas;
        if (status) filterParams.status = status;
        
        // Reload DataTable with filters
        siswaTable.settings()[0].ajax.data = function(d) {
            return $.extend({}, d, filterParams);
        };
        siswaTable.ajax.reload();

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

</script>
@stop
