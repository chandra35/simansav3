@extends('adminlte::page')

@section('title', 'Data GTK - SIMANSA')

@section('css')
<style>
    .gtk-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr);
        gap: .7rem;
        align-items: stretch;
        margin-bottom: .65rem;
    }

    .gtk-hero__main {
        background: linear-gradient(135deg, rgba(37, 99, 235, .92), rgba(13, 148, 136, .84));
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 20px;
        padding: .95rem 1.05rem;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
    }

    .gtk-hero__eyebrow {
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

    .gtk-hero__title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 .25rem 0;
    }

    .gtk-hero__subtitle {
        color: rgba(255, 255, 255, .9);
        font-size: .84rem;
        line-height: 1.45;
        margin: 0;
        max-width: 780px;
    }

    .gtk-hero__side {
        display: grid;
        gap: .9rem;
    }

    .gtk-hero-chip {
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 14px;
        padding: .62rem .82rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    .gtk-hero-chip__label {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .gtk-hero-chip__value {
        display: block;
        color: #0f172a;
        font-size: 1.06rem;
        font-weight: 800;
        line-height: 1.2;
    }

    @media (max-width: 991.98px) {
        .gtk-hero {
            grid-template-columns: 1fr;
        }

        .gtk-hero__side {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .gtk-hero__title {
            font-size: 1.7rem;
        }

        .gtk-hero__side {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop

@section('content_header')
    <div class="gtk-hero">
        <div class="gtk-hero__main">
            <div class="gtk-hero__eyebrow">
                <i class="fas fa-chalkboard-teacher"></i>
                Master GTK
            </div>
            <h1 class="gtk-hero__title">Data GTK</h1>
            <p class="gtk-hero__subtitle">
                Kelola guru dan tenaga kependidikan, pantau kelengkapan data, dan jalankan sinkronisasi Kemenag dari satu halaman operasional.
            </p>
        </div>
        <div class="gtk-hero__side">
            <div class="gtk-hero-chip">
                <span class="gtk-hero-chip__label">Total GTK</span>
                <span class="gtk-hero-chip__value">{{ number_format($stats['total_gtk']) }}</span>
            </div>
            <div class="gtk-hero-chip">
                <span class="gtk-hero-chip__label">Siap Sinkron</span>
                <span class="gtk-hero-chip__value">{{ number_format($stats['gtk_with_nip']) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<style>
    .gtk-stat-card {
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

    .gtk-stat-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -36px;
        width: 144px;
        height: 144px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
    }

    .gtk-stat-card--blue { background: linear-gradient(135deg, #4f46e5, #6366f1); }
    .gtk-stat-card--cyan { background: linear-gradient(135deg, #0ea5e9, #22d3ee); }
    .gtk-stat-card--rose { background: linear-gradient(135deg, #fb7185, #f43f5e); }
    .gtk-stat-card--green { background: linear-gradient(135deg, #10b981, #34d399); }

    .gtk-stat-card__icon {
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

    .gtk-stat-card__body {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        min-width: 0;
    }

    .gtk-stat-card__label {
        position: relative;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: .25rem;
    }

    .gtk-stat-card__value {
        position: relative;
        font-size: 1.48rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: .35rem;
    }

    .gtk-stat-card__desc {
        position: relative;
        opacity: .92;
        line-height: 1.28;
        font-size: .78rem;
    }

    @media (max-width: 575.98px) {
        .gtk-stat-card {
            flex-direction: column;
            gap: .9rem;
        }

        .gtk-stat-card__body {
            width: 100%;
        }
    }

    .gtk-management-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .gtk-management-card .card-header {
        background: linear-gradient(135deg, rgba(37, 99, 235, .98), rgba(13, 148, 136, .9));
        color: #fff;
        border-bottom: 0;
        padding: .8rem 1rem;
    }

    .gtk-filter-panel {
        background: linear-gradient(180deg, rgba(248, 250, 252, .96), rgba(255, 255, 255, .98));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        padding: 1rem 1rem .35rem;
        margin-bottom: 1rem;
    }

    .gtk-filter-label {
        display: block;
        font-size: .82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: .4rem;
    }

    .gtk-table-note {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }
</style>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="gtk-stat-card gtk-stat-card--blue">
            <div class="gtk-stat-card__icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="gtk-stat-card__body">
                <div class="gtk-stat-card__label">Total GTK</div>
                <div class="gtk-stat-card__value">{{ number_format($stats['total_gtk']) }}</div>
                <div class="gtk-stat-card__desc">Semua guru dan tenaga kependidikan yang tercatat di SIMANSA.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="gtk-stat-card gtk-stat-card--cyan">
            <div class="gtk-stat-card__icon"><i class="fas fa-male"></i></div>
            <div class="gtk-stat-card__body">
                <div class="gtk-stat-card__label">Laki-Laki</div>
                <div class="gtk-stat-card__value">{{ number_format($stats['laki_laki']) }}</div>
                <div class="gtk-stat-card__desc">Jumlah GTK laki-laki untuk kebutuhan monitoring personalia.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="gtk-stat-card gtk-stat-card--rose">
            <div class="gtk-stat-card__icon"><i class="fas fa-female"></i></div>
            <div class="gtk-stat-card__body">
                <div class="gtk-stat-card__label">Perempuan</div>
                <div class="gtk-stat-card__value">{{ number_format($stats['perempuan']) }}</div>
                <div class="gtk-stat-card__desc">Jumlah GTK perempuan sesuai data aktif yang tersimpan.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="gtk-stat-card gtk-stat-card--green">
            <div class="gtk-stat-card__icon"><i class="fas fa-check-circle"></i></div>
            <div class="gtk-stat-card__body">
                <div class="gtk-stat-card__label">Data Lengkap</div>
                <div class="gtk-stat-card__value">{{ number_format($stats['data_lengkap']) }}</div>
                <div class="gtk-stat-card__desc">GTK dengan data pribadi dan kepegawaian yang sudah lengkap.</div>
            </div>
        </div>
    </div>
</div>

<div class="card gtk-management-card">
    <div class="card-header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
        <h3 class="card-title mb-3 mb-lg-0">
            <i class="fas fa-list mr-2"></i>
            Daftar GTK
        </h3>
        <div class="card-tools ml-0">
            @can('edit-gtk')
                <button type="button" class="btn btn-warning btn-sm mr-1" id="btnBulkSyncKemenag">
                    <i class="fas fa-sync-alt"></i> Sinkron Semua GTK Ber-NIP
                    <span class="badge badge-light ml-1">{{ $stats['gtk_with_nip'] }}</span>
                </button>
            @endcan
            @can('create-gtk')
                <a href="{{ route('admin.gtk.import') }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel"></i> Import GTK
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addGtkModal">
                    <i class="fas fa-plus"></i> Tambah GTK
                </button>
            @endcan
        </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Section --}}
        <div class="gtk-filter-panel">
                        <form id="filterForm">
                        <div class="row">
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterKategoriPtk" class="gtk-filter-label">
                                    <i class="fas fa-users mr-1"></i> Kategori PTK
                                </label>
                                <select id="filterKategoriPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Pendidik">Pendidik (Guru)</option>
                                    <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterJenisPtk" class="gtk-filter-label">
                                    <i class="fas fa-user-tag mr-1"></i> Jenis PTK
                                </label>
                                <select id="filterJenisPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Guru Mapel">Guru Mapel</option>
                                    <option value="Guru BK">Guru BK</option>
                                    <option value="Kepala TU">Kepala TU</option>
                                    <option value="Staff TU">Staff TU</option>
                                    <option value="Bendahara">Bendahara</option>
                                    <option value="Laboran">Laboran</option>
                                    <option value="Pustakawan">Pustakawan</option>
                                    <option value="Cleaning Service">Cleaning Service</option>
                                    <option value="Satpam">Satpam</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterJenisKelamin" class="gtk-filter-label">
                                    <i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin
                                </label>
                                <select id="filterJenisKelamin" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatusKepegawaian" class="gtk-filter-label">
                                    <i class="fas fa-briefcase mr-1"></i> Status Kepeg
                                </label>
                                <select id="filterStatusKepegawaian" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($statusKepegawaianOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatus" class="gtk-filter-label">
                                    <i class="fas fa-database mr-1"></i> Status Data
                                </label>
                                <select id="filterStatus" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="lengkap">Data Lengkap</option>
                                    <option value="belum">Belum Lengkap</option>
                                </select>
                            </div>
                            </div>
                            <div class="d-flex justify-content-end">
                            <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary mb-2">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            </div>
                        </form>
        </div>

        <p class="gtk-table-note">
            Gunakan filter untuk memantau komposisi GTK, kelengkapan data, dan kesiapan sinkronisasi Kemenag tanpa meninggalkan halaman ini.
        </p>

        <div class="table-responsive">
            <table id="gtk-table" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>Kategori PTK</th>
                        <th>Jenis PTK</th>
                        <th>Status Kepeg</th>
                        <th>Jabatan</th>
                        <th>Username</th>
                        <th>Status Diri</th>
                        <th>Status Kepeg</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="bulkSyncOverlay" class="bulk-sync-overlay d-none">
    <div class="bulk-sync-panel">
        <div class="bulk-sync-header">
            <div>
                <div class="bulk-sync-eyebrow">Sinkronisasi GTK Kemenag</div>
                <h4 class="bulk-sync-title mb-0">Memproses data GTK ber-NIP</h4>
            </div>
            <div class="bulk-sync-spinner">
                <i class="fas fa-sync-alt fa-spin"></i>
            </div>
        </div>

        <div class="bulk-sync-meta">
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Progress</span>
                <span class="bulk-sync-stat-value" id="bulkSyncProgressText">0 / 0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Berhasil</span>
                <span class="bulk-sync-stat-value text-success" id="bulkSyncSuccessCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Perubahan</span>
                <span class="bulk-sync-stat-value text-info" id="bulkSyncChangedCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Gagal</span>
                <span class="bulk-sync-stat-value text-danger" id="bulkSyncFailedCount">0</span>
            </div>
        </div>

        <div class="bulk-sync-progress-wrap">
            <div class="progress bulk-sync-progress">
                <div id="bulkSyncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 0%">0%</div>
            </div>
            <div class="bulk-sync-note" id="bulkSyncCurrentLabel">Menyiapkan sinkronisasi...</div>
        </div>

        <div class="bulk-sync-log card">
            <div class="card-header py-2">
                <strong><i class="fas fa-stream mr-1"></i> Aktivitas Terakhir</strong>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="bulkSyncLogList">
                    <li class="list-group-item text-muted">Belum ada proses yang berjalan.</li>
                </ul>
            </div>
        </div>

        <div class="bulk-sync-footer">
            <div class="text-muted small">
                Sinkronisasi massal ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak diubah otomatis.
            </div>
        </div>
    </div>
</div>

{{-- Modal Add GTK --}}
<div class="modal fade" id="addGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah GTK Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addGtkForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Username akan dibuat otomatis dari NIK. Password default adalah NIK.
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        <span class="invalid-feedback d-block" id="error-nama_lengkap"></span>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (16 digit) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" required>
                        <small class="form-text text-muted">NIK akan digunakan sebagai username dan password default</small>
                        <span class="invalid-feedback d-block" id="error-nik"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <span class="invalid-feedback d-block" id="error-jenis_kelamin"></span>
                    </div>

                    <div class="form-group">
                        <label for="kategori_ptk">Kategori PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="kategori_ptk" name="kategori_ptk" required>
                            <option value="">Pilih Kategori PTK</option>
                            <option value="Pendidik">Pendidik (Guru)</option>
                            <option value="Tenaga Kependidikan">Tenaga Kependidikan (Staff TU, dll)</option>
                        </select>
                        <small class="form-text text-muted">Kategori PTK: Pendidik untuk Guru, Tenaga Kependidikan untuk Staff non-Guru</small>
                        <span class="invalid-feedback d-block" id="error-kategori_ptk"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_ptk">Jenis PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_ptk" name="jenis_ptk" required disabled>
                            <option value="">Pilih Kategori PTK terlebih dahulu</option>
                        </select>
                        <small class="form-text text-muted">Jenis PTK akan muncul setelah memilih Kategori PTK</small>
                        <span class="invalid-feedback d-block" id="error-jenis_ptk"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal View GTK --}}
<div class="modal fade" id="viewGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detail GTK
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGtkContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    .info-box {
        min-height: 80px;
    }
    .info-box-number {
        font-weight: bold;
    }
    
    /* DataTables styling */
    .dataTables_length select {
        min-width: 80px !important;
        width: auto !important;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
    }
    .dataTables_length {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 0.35rem;
    }

    .dataTables_wrapper .dataTables_paginate .pagination {
        gap: 0.22rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item {
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item .page-link {
        min-width: 2.45rem;
        min-height: 2.45rem;
        padding: 0.48rem 0.9rem !important;
        border-radius: 0.95rem !important;
        border: 1px solid rgba(203, 213, 225, 0.95) !important;
        background: rgba(255, 255, 255, 0.96) !important;
        color: #334155 !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06) !important;
        transition: all 0.2s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item .page-link:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item .page-link:focus {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(6, 182, 212, 0.1)) !important;
        border-color: rgba(99, 102, 241, 0.35) !important;
        color: #312e81 !important;
        box-shadow: 0 14px 28px rgba(79, 70, 229, 0.12) !important;
        transform: translateY(-1px);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item.active .page-link {
        background: linear-gradient(135deg, #312e81, #4f46e5) !important;
        border-color: transparent !important;
        color: #ffffff !important;
        box-shadow: 0 16px 32px rgba(49, 46, 129, 0.22) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item.disabled .page-link {
        background: rgba(241, 245, 249, 0.82) !important;
        color: #94a3b8 !important;
        border-color: rgba(226, 232, 240, 0.95) !important;
        box-shadow: none !important;
    }
    
    #gtk-table {
        font-size: 0.9rem;
    }
    
    .badge {
        font-size: 85%;
    }

    .bulk-sync-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(17, 24, 39, 0.72);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .bulk-sync-panel {
        width: min(820px, 100%);
        background: #ffffff;
        border-radius: 22px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.3);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .bulk-sync-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem 1rem;
        background: linear-gradient(135deg, #1d4ed8, #0f766e);
        color: #ffffff;
    }

    .bulk-sync-eyebrow {
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 0.35rem;
    }

    .bulk-sync-title {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .bulk-sync-spinner {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .bulk-sync-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        padding: 1.25rem 1.5rem 0;
    }

    .bulk-sync-stat {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 0.9rem 1rem;
    }

    .bulk-sync-stat-label {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bulk-sync-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }

    .bulk-sync-progress-wrap {
        padding: 1rem 1.5rem 0.75rem;
    }

    .bulk-sync-progress {
        height: 18px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .bulk-sync-progress .progress-bar {
        font-weight: 700;
    }

    .bulk-sync-note {
        margin-top: 0.85rem;
        font-size: 0.96rem;
        color: #334155;
        font-weight: 600;
    }

    .bulk-sync-log {
        margin: 0.75rem 1.5rem 0;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .bulk-sync-log .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .bulk-sync-log .list-group-item {
        font-size: 0.92rem;
        border-color: #eef2f7;
    }

    .bulk-sync-footer {
        padding: 1rem 1.5rem 1.4rem;
    }

    @media (max-width: 767.98px) {
        .bulk-sync-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            justify-content: center;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.page-item .page-link {
            min-width: 2.1rem;
            min-height: 2.1rem;
            padding: 0.34rem 0.62rem !important;
            border-radius: 0.8rem !important;
            font-size: 0.8125rem !important;
        }

        .bulk-sync-header {
            align-items: flex-start;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const BULK_SYNC_DELAY_MS = 350;

$(document).ready(function() {
    // Initialize DataTable
    let gtkTable = $('#gtk-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.gtk.data') }}',
            data: function(d) {
                d.kategori_ptk = $('#filterKategoriPtk').val();
                d.jenis_ptk = $('#filterJenisPtk').val();
                d.jenis_kelamin = $('#filterJenisKelamin').val();
                d.status_kepegawaian = $('#filterStatusKepegawaian').val();
                d.status = $('#filterStatus').val();
            }
        },
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'nik', name: 'nik' },
            { data: 'kategori_ptk', name: 'kategori_ptk' },
            { data: 'jenis_ptk', name: 'jenis_ptk' },
            { data: 'status_kepegawaian', name: 'status_kepegawaian' },
            { data: 'jabatan', name: 'jabatan' },
            { data: 'username', name: 'username' },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'status_kepeg', name: 'status_kepeg', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 - 0 dari 0 data",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Filter functionality - Cascading Kategori PTK -> Jenis PTK
    const filterJenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mapel' },
            { value: 'Guru BK', text: 'Guru BK' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala TU' },
            { value: 'Staff TU', text: 'Staff TU' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#filterKategoriPtk').on('change', function() {
        const kategori = $(this).val();
        const filterJenisPtk = $('#filterJenisPtk');
        const currentValue = filterJenisPtk.val();
        
        // Reset jenis_ptk filter
        filterJenisPtk.empty();
        filterJenisPtk.append('<option value="">Semua</option>');
        
        if (kategori && filterJenisPtkOptions[kategori]) {
            filterJenisPtkOptions[kategori].forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            // If no kategori selected, show all jenis options
            Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        }
        
        // Reload table
        gtkTable.ajax.reload();
    });

    $('#filterJenisPtk, #filterJenisKelamin, #filterStatusKepegawaian, #filterStatus').on('change', function() {
        gtkTable.ajax.reload();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterKategoriPtk').val('');
        $('#filterJenisPtk').empty().append('<option value="">Semua</option>');
        // Repopulate all jenis options
        Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
            $('#filterJenisPtk').append(`<option value="${option.value}">${option.text}</option>`);
        });
        $('#filterJenisKelamin').val('');
        $('#filterStatusKepegawaian').val('');
        $('#filterStatus').val('');
        gtkTable.ajax.reload();
    });

    $('#btnBulkSyncKemenag').on('click', async function() {
        const $button = $(this);

        try {
            $button.prop('disabled', true);

            const candidateResponse = await $.ajax({
                url: '{{ route('admin.gtk.sync-kemenag-candidates') }}',
                type: 'GET'
            });

            if (!candidateResponse.success || !candidateResponse.total) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Data',
                    text: candidateResponse.message || 'Belum ada GTK ber-NIP yang bisa disinkronkan.'
                });
                return;
            }

            const candidates = candidateResponse.candidates || [];
            const previewNames = candidates.slice(0, 5).map(item => `<li>${escapeHtml(item.nama_lengkap)} <span class="text-muted">(${escapeHtml(item.nip)})</span></li>`).join('');
            const moreText = candidates.length > 5
                ? `<div class="mt-2 text-muted small">Dan ${candidates.length - 5} GTK lainnya akan diproses satu per satu.</div>`
                : '';

            const confirmResult = await Swal.fire({
                title: 'Sinkron Semua GTK Ber-NIP?',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Sistem akan menyinkronkan <strong>${candidateResponse.total}</strong> GTK yang sudah memiliki NIP.</p>
                        <div class="alert alert-info py-2 px-3 mb-2">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Mode aman aktif: proses ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak akan diubah otomatis.
                        </div>
                        <div class="small text-muted mb-2">Contoh data yang akan diproses:</div>
                        <ul class="pl-3 mb-0">${previewNames}</ul>
                        ${moreText}
                    </div>
                `,
                icon: 'question',
                width: 680,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Mulai Sinkronisasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280'
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            await runBulkSyncKemenag(candidates, gtkTable);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyiapkan Sinkronisasi',
                text: error?.responseJSON?.message || error?.message || 'Terjadi kesalahan saat menyiapkan data sinkronisasi.'
            });
        } finally {
            $button.prop('disabled', false);
        }
    });

    // Add GTK Form Submit
    $('#addGtkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: '{{ route('admin.gtk.store') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addGtkModal').modal('hide');
                $('#addGtkForm')[0].reset();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 3000
                });
                
                gtkTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#error-' + field).text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            }
        });
    });

    // NIK input validation (only numbers, max 16)
    $('#nik').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
    });

    // Cascading Dropdown: Kategori PTK → Jenis PTK
    const jenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mata Pelajaran' },
            { value: 'Guru BK', text: 'Guru BK (Bimbingan Konseling)' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala Tata Usaha' },
            { value: 'Staff TU', text: 'Staff Tata Usaha' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#kategori_ptk').on('change', function() {
        const kategori = $(this).val();
        const jenisPtkSelect = $('#jenis_ptk');
        
        jenisPtkSelect.empty();
        jenisPtkSelect.prop('disabled', true);
        
        if (kategori && jenisPtkOptions[kategori]) {
            jenisPtkSelect.prop('disabled', false);
            jenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
            
            jenisPtkOptions[kategori].forEach(function(option) {
                jenisPtkSelect.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            jenisPtkSelect.append('<option value="">Pilih Kategori PTK terlebih dahulu</option>');
        }
    });
});

async function runBulkSyncKemenag(candidates, gtkTable) {
    const stats = {
        total: candidates.length,
        processed: 0,
        success: 0,
        failed: 0,
        changed: 0,
        unchanged: 0,
    };

    showBulkSyncOverlay();
    resetBulkSyncOverlay(stats.total);
    addBulkSyncLog('Memulai sinkronisasi massal GTK ber-NIP...', 'info');

    for (const candidate of candidates) {
        updateBulkSyncCurrent(candidate, stats);

        try {
            const response = await $.ajax({
                url: `/admin/gtk/${candidate.id}/sync-kemenag`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            });

            stats.processed++;

            if (response.success) {
                stats.success++;

                if (response.has_differences) {
                    stats.changed++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, ditemukan ${response.applicable_differences_count ?? response.differences_count ?? 0} perubahan yang bisa ditinjau.`, 'success');
                } else {
                    stats.unchanged++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, tidak ada perubahan data.`, 'neutral');
                }
            } else {
                stats.failed++;
                addBulkSyncLog(`${candidate.nama_lengkap}: ${response.message || 'sinkronisasi gagal.'}`, 'danger');
            }
        } catch (error) {
            stats.processed++;
            stats.failed++;
            addBulkSyncLog(`${candidate.nama_lengkap}: ${error?.responseJSON?.message || 'terjadi kesalahan saat menghubungi server.'}`, 'danger');
        }

        renderBulkSyncStats(stats);

        if (BULK_SYNC_DELAY_MS > 0 && stats.processed < stats.total) {
            await wait(BULK_SYNC_DELAY_MS);
        }
    }

    updateBulkSyncCurrent(null, stats, 'Sinkronisasi selesai. Menyiapkan ringkasan hasil...');
    gtkTable.ajax.reload(null, false);

    await wait(250);
    hideBulkSyncOverlay();

    await Swal.fire({
        icon: stats.failed > 0 ? 'warning' : 'success',
        title: stats.failed > 0 ? 'Sinkronisasi Selesai Dengan Catatan' : 'Sinkronisasi Selesai',
        html: `
            <div class="text-left">
                <div class="row text-center mb-3">
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Total</div><div class="font-weight-bold">${stats.total}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Berhasil</div><div class="font-weight-bold text-success">${stats.success}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Perubahan</div><div class="font-weight-bold text-info">${stats.changed}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Gagal</div><div class="font-weight-bold text-danger">${stats.failed}</div></div></div>
                </div>
                <p class="mb-0">Hasil sinkronisasi sudah tersimpan per GTK. Kamu bisa membuka detail GTK tertentu untuk meninjau perubahan sebelum menerapkannya ke data lokal.</p>
            </div>
        `,
        confirmButtonText: 'Tutup'
    });
}

function showBulkSyncOverlay() {
    $('#bulkSyncOverlay').removeClass('d-none');
    $('body').addClass('overflow-hidden');
}

function hideBulkSyncOverlay() {
    $('#bulkSyncOverlay').addClass('d-none');
    $('body').removeClass('overflow-hidden');
}

function resetBulkSyncOverlay(total) {
    $('#bulkSyncProgressText').text(`0 / ${total}`);
    $('#bulkSyncSuccessCount').text('0');
    $('#bulkSyncChangedCount').text('0');
    $('#bulkSyncFailedCount').text('0');
    $('#bulkSyncProgressBar')
        .css('width', '0%')
        .text('0%');
    $('#bulkSyncCurrentLabel').text('Menyiapkan sinkronisasi...');
    $('#bulkSyncLogList').html('<li class="list-group-item text-muted">Belum ada aktivitas yang diproses.</li>');
}

function renderBulkSyncStats(stats) {
    const percent = stats.total > 0 ? Math.round((stats.processed / stats.total) * 100) : 0;

    $('#bulkSyncProgressText').text(`${stats.processed} / ${stats.total}`);
    $('#bulkSyncSuccessCount').text(stats.success);
    $('#bulkSyncChangedCount').text(stats.changed);
    $('#bulkSyncFailedCount').text(stats.failed);
    $('#bulkSyncProgressBar')
        .css('width', `${percent}%`)
        .text(`${percent}%`);
}

function updateBulkSyncCurrent(candidate, stats, customText = null) {
    if (customText) {
        $('#bulkSyncCurrentLabel').text(customText);
        return;
    }

    const currentNumber = Math.min(stats.processed + 1, stats.total);
    $('#bulkSyncCurrentLabel').text(`Memproses ${currentNumber}/${stats.total}: ${candidate.nama_lengkap} (${candidate.nip})`);
}

function addBulkSyncLog(message, type = 'info') {
    const colorClass = {
        success: 'text-success',
        danger: 'text-danger',
        neutral: 'text-secondary',
        info: 'text-primary',
    }[type] || 'text-primary';

    const iconClass = {
        success: 'fa-check-circle',
        danger: 'fa-times-circle',
        neutral: 'fa-minus-circle',
        info: 'fa-info-circle',
    }[type] || 'fa-info-circle';

    const $list = $('#bulkSyncLogList');
    const emptyState = $list.find('.text-muted').length === 1 && $list.children().length === 1;

    if (emptyState) {
        $list.empty();
    }

    const timestamp = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    $list.prepend(`
        <li class="list-group-item">
            <div class="d-flex align-items-start">
                <i class="fas ${iconClass} ${colorClass} mt-1 mr-2"></i>
                <div>
                    <div>${escapeHtml(message)}</div>
                    <div class="small text-muted mt-1">${timestamp}</div>
                </div>
            </div>
        </li>
    `);

    if ($list.children().length > 8) {
        $list.children().last().remove();
    }
}

function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Show GTK Detail
function showGtk(id) {
    $('#viewGtkModal').modal('show');
    $('#viewGtkContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: '/admin/gtk/' + id,
        type: 'GET',
        success: function(response) {
            const gtk = response.data;
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Pribadi</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Nama Lengkap</th><td>${gtk.nama_lengkap}</td></tr>
                            <tr><th>NIK</th><td>${gtk.nik}</td></tr>
                            <tr><th>NUPTK</th><td>${gtk.nuptk || '-'}</td></tr>
                            <tr><th>NIP</th><td>${gtk.nip || '-'}</td></tr>
                            <tr><th>Jenis Kelamin</th><td>${gtk.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                            <tr><th>Tempat, Tgl Lahir</th><td>${gtk.tempat_lahir || '-'}, ${gtk.tanggal_lahir || '-'}</td></tr>
                            <tr><th>Email</th><td>${gtk.email || '-'}</td></tr>
                            <tr><th>No HP</th><td>${gtk.nomor_hp || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Kepegawaian</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Kategori PTK</th><td>${gtk.kategori_ptk ? '<span class="badge badge-' + (gtk.kategori_ptk === 'Pendidik' ? 'primary' : 'info') + '">' + gtk.kategori_ptk + '</span>' : '-'}</td></tr>
                            <tr><th>Jenis PTK</th><td>${gtk.jenis_ptk || '-'}</td></tr>
                            <tr><th>Status Kepegawaian</th><td>${gtk.status_kepegawaian || '-'}</td></tr>
                            <tr><th>Jabatan</th><td>${gtk.jabatan || '-'}</td></tr>
                            <tr><th>TMT Kerja</th><td>${gtk.tmt_kerja || '-'}</td></tr>
                        </table>
                        
                        <h5 class="border-bottom pb-2 mt-3">Alamat</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Alamat</th><td>${gtk.alamat || '-'}</td></tr>
                            <tr><th>RT/RW</th><td>${gtk.rt || '-'} / ${gtk.rw || '-'}</td></tr>
                            <tr><th>Kelurahan</th><td>${gtk.kelurahan?.name || '-'}</td></tr>
                            <tr><th>Kecamatan</th><td>${gtk.kecamatan?.name || '-'}</td></tr>
                            <tr><th>Kabupaten</th><td>${gtk.kabupaten?.name || '-'}</td></tr>
                            <tr><th>Provinsi</th><td>${gtk.provinsi?.name || '-'}</td></tr>
                            <tr><th>Kode Pos</th><td>${gtk.kodepos || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            $('#viewGtkContent').html(html);
        },
        error: function() {
            $('#viewGtkContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
        }
    });
}

// Reset Password
function resetPassword(id) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password akan direset menjadi NIK',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id + '/reset-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}

// Delete GTK
function deleteGtk(id) {
    Swal.fire({
        title: 'Hapus GTK?',
        text: 'Data GTK dan akun user akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                    
                    $('#gtk-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}
</script>
@stop
