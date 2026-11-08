@extends('adminlte::page')

@section('title', 'Data Siswa Yatim/Piatu - SIMANSA')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-hands-helping text-primary"></i> Data Siswa Yatim/Piatu</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.siswa.index') }}">Kesiswaan</a></li>
                <li class="breadcrumb-item active">Siswa Yatim/Piatu</li>
            </ol>
        </div>
    </div>
@stop

@section('css')
<style>
    .yatim-page .yatim-hero__eyebrow { font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.78); }
    .yatim-page .yatim-hero__lead { max-width: 680px; color: rgba(255,255,255,.85); }
    .yatim-page .yatim-hero__metric { border-left: 1px solid rgba(255,255,255,.25); min-height: 56px; }
    .yatim-page .yatim-hero__metric-label { color: rgba(255,255,255,.75); font-size: .72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    
    .yatim-page .yatim-stat-card {
        border-radius: .75rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform .18s ease, box-shadow .18s ease;
        margin-bottom: 1rem;
    }
    .yatim-page .yatim-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(15, 23, 42, .08);
    }
    .yatim-page .yatim-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .yatim-page .filter-card {
        border-radius: .75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    
    .yatim-page #yatim-table { border-collapse: separate; border-spacing: 0; width: 100% !important; }
    .yatim-page #yatim-table th {
        padding: .65rem .75rem;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .yatim-page #yatim-table td {
        padding: .55rem .75rem;
        border-top: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        font-size: .84rem;
    }
    .yatim-page #yatim-table tbody tr:hover {
        background-color: #f8fbff;
    }
    
    .yatim-page .badge-purple {
        background-color: #8b5cf6;
        color: #fff;
    }
    
    #modalDetailSiswaYatim .modal-content {
        border-radius: .75rem;
        overflow: hidden;
    }
    #modalDetailSiswaYatim .nav-pills .nav-link {
        font-weight: 600;
        font-size: .82rem;
        border-radius: .5rem;
        color: #475569;
        padding: .5rem .85rem;
    }
    #modalDetailSiswaYatim .nav-pills .nav-link.active {
        background-color: #2563eb;
        color: #fff;
    }
    #modalDetailSiswaYatim .table-detail td {
        padding: .45rem .6rem;
        font-size: .84rem;
        vertical-align: middle;
    }
    #modalDetailSiswaYatim .table-detail td.bg-label {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        width: 35%;
    }
    
    @media (max-width: 991.98px) {
        .yatim-page .yatim-hero__metric {
            border-left: 0;
            border-top: 1px solid rgba(255,255,255,.25);
            padding-top: .75rem;
            margin-top: .75rem;
        }
    }
</style>
@stop

@section('content')
<div class="yatim-page">
    {{-- Hero Section --}}
    <div class="card bg-gradient-primary text-white mb-4 border-0 shadow-sm" style="border-radius: .75rem;">
        <div class="card-body py-4 px-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="yatim-hero__eyebrow mb-2">
                        <i class="fas fa-shield-alt mr-1"></i> Manajemen Kesiswaan & Bimbingan Konseling
                    </div>
                    <h3 class="mb-2 font-weight-bold">
                        <i class="fas fa-hands-helping mr-2"></i>Data Siswa Yatim / Piatu
                    </h3>
                    <p class="mb-0 yatim-hero__lead">
                        Pusat pemantauan dan pendataan siswa yatim, piatu, dan yatim piatu di SIMANSA. Memudahkan evaluasi kesejahteraan siswa, pemberian beasiswa, dan pendampingan sosial secara berkelanjutan.
                    </p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-6 yatim-hero__metric">
                            <div class="yatim-hero__metric-label">Siswa Yatim/Piatu Aktif</div>
                            <div class="h2 mb-0 font-weight-bold">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="col-6 yatim-hero__metric">
                            <div class="yatim-hero__metric-label">Yatim Piatu</div>
                            <div class="h2 mb-0 font-weight-bold">{{ number_format($stats['yatim_piatu']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="yatim-stat-card">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Yatim (Ayah)</div>
                    <div class="h3 mb-0 font-weight-bold text-warning">{{ number_format($stats['yatim']) }}</div>
                    <div class="text-muted small mt-1">Ayah Meninggal</div>
                </div>
                <div class="yatim-stat-icon bg-warning text-white">
                    <i class="fas fa-male"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="yatim-stat-card">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Piatu (Ibu)</div>
                    <div class="h3 mb-0 font-weight-bold" style="color: #8b5cf6;">{{ number_format($stats['piatu']) }}</div>
                    <div class="text-muted small mt-1">Ibu Meninggal</div>
                </div>
                <div class="yatim-stat-icon text-white" style="background-color: #8b5cf6;">
                    <i class="fas fa-female"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="yatim-stat-card">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Yatim Piatu</div>
                    <div class="h3 mb-0 font-weight-bold text-danger">{{ number_format($stats['yatim_piatu']) }}</div>
                    <div class="text-muted small mt-1">Kedua Ortu Meninggal</div>
                </div>
                <div class="yatim-stat-icon bg-danger text-white">
                    <i class="fas fa-heart-broken"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="yatim-stat-card">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Penerima Bantuan</div>
                    <div class="h3 mb-0 font-weight-bold text-success">{{ number_format($stats['bantuan']) }}</div>
                    <div class="text-muted small mt-1">Terdata KIP / PKH</div>
                </div>
                <div class="yatim-stat-icon bg-success text-white">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card filter-card mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-filter text-primary mr-1"></i> Filter Data Siswa
            </h6>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetFilter">
                    <i class="fas fa-redo-alt mr-1"></i> Reset Filter
                </button>
                <button type="button" class="btn btn-sm btn-success ml-2" id="btnExportExcel">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Kategori Yatim/Piatu</label>
                    <select id="filterKategori" class="form-control form-control-sm">
                        <option value="">Semua Kategori</option>
                        <option value="yatim">Yatim (Ayah Meninggal)</option>
                        <option value="piatu">Piatu (Ibu Meninggal)</option>
                        <option value="yatim_piatu">Yatim Piatu (Keduanya Meninggal)</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Status Siswa</label>
                    <select id="filterStatusSiswa" class="form-control form-control-sm">
                        <option value="aktif" selected>Siswa Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="mutasi_keluar">Mutasi Keluar</option>
                        <option value="semua">Semua Status</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Tingkat</label>
                    <select id="filterTingkat" class="form-control form-control-sm">
                        <option value="">Semua Tingkat</option>
                        @foreach ($tingkatOptions as $tingkat => $label)
                            <option value="{{ $tingkat }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Kelas / Rombel</label>
                    <select id="filterKelas" class="form-control form-control-sm" disabled>
                        <option value="">Pilih Tingkat Dulu</option>
                    </select>
                </div>
                <div class="col-md-1 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Gender</label>
                    <select id="filterGender" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="L">L</option>
                        <option value="P">P</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <label class="small text-muted font-weight-bold mb-1">Status Bantuan</label>
                    <select id="filterBantuan" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="ya">Penerima KIP / PKH</option>
                        <option value="tidak">Belum Penerima</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Main DataTable Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: .75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="yatim-table" class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th style="min-width: 220px;">Nama Siswa</th>
                            <th class="text-center" style="width: 60px;">JK</th>
                            <th class="text-center" style="width: 80px;">Status</th>
                            <th style="min-width: 110px;">Kelas</th>
                            <th class="text-center" style="min-width: 130px;">Kategori</th>
                            <th style="min-width: 160px;">Data Ayah</th>
                            <th style="min-width: 160px;">Data Ibu</th>
                            <th style="min-width: 150px;">Kontak</th>
                            <th class="text-center" style="min-width: 100px;">Bantuan</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="modalDetailSiswaYatim" tabindex="-1" role="dialog" aria-labelledby="modalDetailSiswaYatimLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light py-3 border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalDetailSiswaYatimLabel">
                    <i class="fas fa-id-card-alt text-primary mr-2"></i>Detail Siswa & Orang Tua
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                    <div class="mr-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 font-weight-bold text-dark" id="modalStudentName">-</h5>
                        <div class="small text-muted mt-1">
                            <span class="mr-2"><i class="fas fa-id-card mr-1"></i>NISN: <span id="modalStudentNisn">-</span></span>
                            <span class="mr-2"><i class="fas fa-address-card mr-1"></i>NIK: <span id="modalStudentNik">-</span></span>
                            <span class="mr-2"><i class="fas fa-school mr-1"></i>Kelas: <span id="modalStudentKelas" class="badge badge-primary">-</span></span>
                            <span id="modalStudentKategori">-</span>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-pills mb-3" id="yatimDetailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-biodata-link" data-toggle="pill" href="#tab-biodata" role="tab"><i class="fas fa-user mr-1"></i>Biodata Siswa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-ortu-link" data-toggle="pill" href="#tab-ortu" role="tab"><i class="fas fa-user-friends mr-1"></i>Status Orang Tua</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-alamat-link" data-toggle="pill" href="#tab-alamat" role="tab"><i class="fas fa-map-marker-alt mr-1"></i>Alamat & Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-bantuan-link" data-toggle="pill" href="#tab-bantuan" role="tab"><i class="fas fa-hand-holding-usd mr-1"></i>Bantuan & Berkas</a>
                    </li>
                </ul>

                <div class="tab-content" id="yatimDetailTabContent">
                    {{-- Tab 1: Biodata --}}
                    <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel">
                        <table class="table table-bordered table-sm table-detail mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-label">Nama Lengkap</td>
                                    <td id="dt-nama-lengkap">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">NISN / NIS Lokal</td>
                                    <td id="dt-nisn-lokal">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Jenis Kelamin</td>
                                    <td id="dt-jenis-kelamin">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Tempat, Tanggal Lahir</td>
                                    <td id="dt-ttl">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Agama</td>
                                    <td id="dt-agama">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Status Siswa</td>
                                    <td id="dt-status-siswa">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Asal Sekolah</td>
                                    <td id="dt-asal-sekolah">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 2: Orang Tua --}}
                    <div class="tab-pane fade" id="tab-ortu" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border h-100 mb-0 shadow-none">
                                    <div class="card-header bg-light py-2 font-weight-bold text-dark">
                                        <i class="fas fa-male text-primary mr-1"></i> Data Ayah
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-detail mb-0">
                                            <tr>
                                                <td class="bg-label" style="width:40%;">Nama</td>
                                                <td id="dt-ayah-nama">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Status Hidup</td>
                                                <td id="dt-ayah-status">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">NIK</td>
                                                <td id="dt-ayah-nik">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Pekerjaan</td>
                                                <td id="dt-ayah-pekerjaan">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Penghasilan</td>
                                                <td id="dt-ayah-penghasilan">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">No. Telepon/HP</td>
                                                <td id="dt-ayah-hp">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border h-100 mb-0 shadow-none">
                                    <div class="card-header bg-light py-2 font-weight-bold text-dark">
                                        <i class="fas fa-female text-pink mr-1" style="color:#ec4899;"></i> Data Ibu
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-detail mb-0">
                                            <tr>
                                                <td class="bg-label" style="width:40%;">Nama</td>
                                                <td id="dt-ibu-nama">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Status Hidup</td>
                                                <td id="dt-ibu-status">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">NIK</td>
                                                <td id="dt-ibu-nik">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Pekerjaan</td>
                                                <td id="dt-ibu-pekerjaan">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">Penghasilan</td>
                                                <td id="dt-ibu-penghasilan">-</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-label">No. Telepon/HP</td>
                                                <td id="dt-ibu-hp">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border mb-0 shadow-none">
                            <div class="card-header bg-light py-2 font-weight-bold text-dark">
                                <i class="fas fa-user-shield text-secondary mr-1"></i> Data Wali (Bila Ada)
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-detail mb-0">
                                    <tr>
                                        <td class="bg-label" style="width:25%;">Nama Wali</td>
                                        <td id="dt-wali-nama">-</td>
                                        <td class="bg-label" style="width:25%;">Pekerjaan</td>
                                        <td id="dt-wali-pekerjaan">-</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-label">No. Telepon Wali</td>
                                        <td colspan="3" id="dt-wali-hp">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Alamat & Kontak --}}
                    <div class="tab-pane fade" id="tab-alamat" role="tabpanel">
                        <table class="table table-bordered table-sm table-detail mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-label">No. Kartu Keluarga (KK)</td>
                                    <td id="dt-no-kk">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Alamat Orang Tua / Rumah</td>
                                    <td id="dt-alamat-ortu">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">RT / RW</td>
                                    <td id="dt-rt-rw">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">Wilayah (Kelurahan, Kec, Kab, Prov)</td>
                                    <td id="dt-wilayah">-</td>
                                </tr>
                                <tr>
                                    <td class="bg-label">No. HP Siswa</td>
                                    <td id="dt-siswa-hp">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 4: Bantuan & Berkas --}}
                    <div class="tab-pane fade" id="tab-bantuan" role="tabpanel">
                        <div class="card border mb-3 shadow-none">
                            <div class="card-header bg-light py-2 font-weight-bold text-dark">
                                <i class="fas fa-hand-holding-heart text-success mr-1"></i> Status Kartu Perlindungan Sosial
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-detail mb-0">
                                    <tr>
                                        <td class="bg-label" style="width:35%;">Nomor KIP (Kartu Indonesia Pintar)</td>
                                        <td id="dt-nomor-kip">-</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-label">Nomor PKH / KKS</td>
                                        <td id="dt-nomor-pkh">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card border mb-0 shadow-none">
                            <div class="card-header bg-light py-2 font-weight-bold text-dark">
                                <i class="fas fa-file-pdf text-danger mr-1"></i> Dokumen Terkait
                            </div>
                            <div class="card-body" id="modalStudentDocuments">
                                <div class="text-muted small">Memuat dokumen...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top justify-content-between">
                <div>
                    <a href="#" id="btnFullProfileLink" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Profil Siswa Lengkap
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function () {
    const kelasData = @json($kelasOptions);

    // ── DataTable Initialization ───────────────────────────────────────────────
    const table = $('#yatim-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("admin.siswa-yatim.data") }}',
            data: function (d) {
                d.kategori      = $('#filterKategori').val();
                d.status_siswa  = $('#filterStatusSiswa').val();
                d.tingkat       = $('#filterTingkat').val();
                d.kelas_id      = $('#filterKelas').val();
                d.jenis_kelamin = $('#filterGender').val();
                d.bantuan       = $('#filterBantuan').val();
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false, className: 'text-center text-muted font-weight-bold' },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'jk', name: 'jenis_kelamin', className: 'text-center' },
            { data: 'status_siswa', name: 'status_siswa', className: 'text-center' },
            { data: 'kelas', name: 'kelas', orderable: false },
            { data: 'kategori', name: 'kategori', orderable: false, className: 'text-center' },
            { data: 'ayah', name: 'ayah', orderable: false },
            { data: 'ibu', name: 'ibu', orderable: false },
            { data: 'kontak', name: 'kontak', orderable: false },
            { data: 'bantuan', name: 'bantuan', orderable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center text-nowrap' },
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin text-primary mr-1"></i> Memuat data siswa yatim/piatu...',
            emptyTable: 'Tidak ada data siswa yatim/piatu yang sesuai kriteria.',
            zeroRecords: 'Tidak ada siswa yang cocok dengan filter.',
            lengthMenu: 'Tampilkan _MENU_ siswa per halaman',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ siswa',
            infoEmpty: 'Menampilkan 0 data',
            search: 'Cari:',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
    });

    // ── Cascading Tingkat → Kelas Dropdown ────────────────────────────────────
    $('#filterTingkat').on('change', function () {
        const tingkat = $(this).val();
        const $selKelas = $('#filterKelas');
        $selKelas.html('<option value="">Semua Kelas</option>');
        if (tingkat) {
            const filtered = kelasData.filter(k => k.tingkat == tingkat);
            filtered.forEach(k => {
                $selKelas.append(`<option value="${k.id}">${k.nama_kelas}</option>`);
            });
            $selKelas.prop('disabled', filtered.length === 0);
        } else {
            $selKelas.html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        }
        table.draw();
    });

    // ── Filter Triggers ────────────────────────────────────────────────────────
    $('#filterKategori, #filterStatusSiswa, #filterKelas, #filterGender, #filterBantuan').on('change', function () {
        table.draw();
    });

    // Reset filter
    $('#btnResetFilter').on('click', function () {
        $('#filterKategori').val('');
        $('#filterStatusSiswa').val('aktif');
        $('#filterTingkat').val('');
        $('#filterKelas').html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        $('#filterGender').val('');
        $('#filterBantuan').val('');
        table.draw();
    });

    // ── Export Excel ───────────────────────────────────────────────────────────
    $('#btnExportExcel').on('click', function () {
        const params = new URLSearchParams({
            kategori:      $('#filterKategori').val(),
            status_siswa:  $('#filterStatusSiswa').val(),
            tingkat:       $('#filterTingkat').val(),
            kelas_id:      $('#filterKelas').val(),
            jenis_kelamin: $('#filterGender').val(),
            bantuan:       $('#filterBantuan').val(),
            search:        $('#yatim-table_filter input').val() || '',
        });
        window.open('{{ route("admin.siswa-yatim.export") }}?' + params.toString(), '_blank');
    });

    // ── Detail Modal Handler ───────────────────────────────────────────────────
    $(document).on('click', '.js-btn-detail-siswa', function () {
        const detailUrl = $(this).data('detail-url');
        const fullUrl = $(this).data('full-url');

        if (!detailUrl) return;

        $('#modalDetailSiswaYatim').modal('show');
        $('#btnFullProfileLink').attr('href', fullUrl || '#');

        // Reset to first tab
        $('#yatimDetailTabs a[href="#tab-biodata"]').tab('show');

        // Clear existing fields with loading placeholder
        $('#modalStudentName').text('Memuat...');
        $('#modalStudentNisn, #modalStudentNik, #modalStudentKelas, #modalStudentKategori').text('-');
        $('#modalStudentDocuments').html('<div class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat dokumen...</div>');

        $.get(detailUrl)
            .done(function (res) {
                if (!res || !res.success || !res.data) {
                    toastr.error('Gagal mengambil data detail siswa.');
                    return;
                }

                const s = res.data.siswa;
                const o = res.data.ortu;
                const sa = res.data.sekolah_asal;
                const docs = res.data.dokumen || [];

                // Header
                $('#modalStudentName').text(s.nama_lengkap);
                $('#modalStudentNisn').text(s.nisn);
                $('#modalStudentNik').text(s.nik);
                $('#modalStudentKelas').text(s.kelas);

                let kategoriBadge = '<span class="badge badge-secondary">-</span>';
                if (res.data.kategori === 'Yatim Piatu') {
                    kategoriBadge = '<span class="badge badge-danger"><i class="fas fa-heart-broken mr-1"></i>Yatim Piatu</span>';
                } else if (res.data.kategori === 'Yatim') {
                    kategoriBadge = '<span class="badge badge-warning text-dark"><i class="fas fa-male mr-1"></i>Yatim</span>';
                } else if (res.data.kategori === 'Piatu') {
                    kategoriBadge = '<span class="badge badge-purple text-white" style="background:#8b5cf6;"><i class="fas fa-female mr-1"></i>Piatu</span>';
                }
                $('#modalStudentKategori').html(kategoriBadge);

                // Tab 1: Biodata
                $('#dt-nama-lengkap').text(s.nama_lengkap);
                $('#dt-nisn-lokal').text(s.nisn + (s.nis_lokal !== '-' ? ' / ' + s.nis_lokal : ''));
                $('#dt-jenis-kelamin').text(s.jenis_kelamin);
                $('#dt-ttl').text(s.tempat_lahir + ', ' + s.tanggal_lahir);
                $('#dt-agama').text(s.agama);
                $('#dt-status-siswa').html('<span class="badge badge-success">' + s.status_siswa + '</span>');
                $('#dt-asal-sekolah').text(sa.nama + (sa.npsn !== '-' ? ' (NPSN: ' + sa.npsn + ')' : ''));

                // Tab 2: Orang Tua
                $('#dt-ayah-nama').text(o.nama_ayah);
                $('#dt-ayah-status').html(o.status_ayah === 'Meninggal' ? '<span class="badge badge-danger">Meninggal</span>' : '<span class="badge badge-success">' + o.status_ayah + '</span>');
                $('#dt-ayah-nik').text(o.nik_ayah);
                $('#dt-ayah-pekerjaan').text(o.pekerjaan_ayah);
                $('#dt-ayah-penghasilan').text(o.penghasilan_ayah);
                $('#dt-ayah-hp').html(o.hp_ayah !== '-' ? `<a href="tel:${o.hp_ayah}"><i class="fas fa-phone mr-1"></i>${o.hp_ayah}</a>` : '-');

                $('#dt-ibu-nama').text(o.nama_ibu);
                $('#dt-ibu-status').html(o.status_ibu === 'Meninggal' ? '<span class="badge badge-danger">Meninggal</span>' : '<span class="badge badge-success">' + o.status_ibu + '</span>');
                $('#dt-ibu-nik').text(o.nik_ibu);
                $('#dt-ibu-pekerjaan').text(o.pekerjaan_ibu);
                $('#dt-ibu-penghasilan').text(o.penghasilan_ibu);
                $('#dt-ibu-hp').html(o.hp_ibu !== '-' ? `<a href="tel:${o.hp_ibu}"><i class="fas fa-phone mr-1"></i>${o.hp_ibu}</a>` : '-');

                $('#dt-wali-nama').text(o.nama_wali);
                $('#dt-wali-pekerjaan').text(o.pekerjaan_wali);
                $('#dt-wali-hp').html(o.hp_wali !== '-' ? `<a href="tel:${o.hp_wali}"><i class="fas fa-phone mr-1"></i>${o.hp_wali}</a>` : '-');

                // Tab 3: Alamat & Kontak
                $('#dt-no-kk').text(o.no_kk);
                $('#dt-alamat-ortu').text(o.alamat_ortu);
                $('#dt-rt-rw').text(o.rt_rw_ortu);
                $('#dt-wilayah').text(o.wilayah);
                $('#dt-siswa-hp').html(s.nomor_hp !== '-' ? `<a href="tel:${s.nomor_hp}"><i class="fas fa-mobile-alt mr-1"></i>${s.nomor_hp}</a>` : '-');

                // Tab 4: Bantuan & Berkas
                $('#dt-nomor-kip').html(s.nomor_kip !== '-' ? `<span class="badge badge-success mr-2"><i class="fas fa-check-circle mr-1"></i>Terdaftar KIP</span> <strong>${s.nomor_kip}</strong>` : '<span class="text-muted">Tidak terdaftar</span>');
                $('#dt-nomor-pkh').html(s.nomor_pkh !== '-' ? `<span class="badge badge-info mr-2"><i class="fas fa-check-circle mr-1"></i>Terdaftar PKH</span> <strong>${s.nomor_pkh}</strong>` : '<span class="text-muted">Tidak terdaftar</span>');

                if (docs.length > 0) {
                    let docHtml = '<div class="row">';
                    docs.forEach(doc => {
                        docHtml += `
                            <div class="col-md-6 mb-2">
                                <div class="border rounded p-2 d-flex align-items-center justify-content-between bg-light">
                                    <div class="text-truncate mr-2">
                                        <div class="font-weight-bold small text-truncate"><i class="fas fa-file-alt text-primary mr-1"></i>${doc.jenis}</div>
                                        <div class="text-muted text-truncate" style="font-size: .75rem;">${doc.file_name} (${doc.size})</div>
                                    </div>
                                    <div class="text-nowrap">
                                        <a href="${doc.url}" class="btn btn-xs btn-outline-primary" target="_blank" title="Unduh"><i class="fas fa-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    docHtml += '</div>';
                    $('#modalStudentDocuments').html(docHtml);
                } else {
                    $('#modalStudentDocuments').html('<div class="text-muted small font-italic"><i class="fas fa-info-circle mr-1"></i>Belum ada berkas pendukung yang diunggah.</div>');
                }
            })
            .fail(function (xhr) {
                toastr.error('Terjadi kesalahan saat memuat detail siswa.');
                $('#modalStudentName').text('Gagal memuat');
            });
    });
});
</script>
@stop
