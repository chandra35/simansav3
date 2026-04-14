@extends('adminlte::page')

@section('title', 'Import Kelulusan SMART-Q - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-import text-warning"></i> Import Kelulusan SMART-Q</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
{{-- Period Info --}}
<div class="callout callout-info">
    <h5 class="mb-1"><i class="fas fa-star text-warning"></i> {{ $smartq->nama }}</h5>
    <p class="mb-0 text-muted">Tahun Pelajaran: {{ $smartq->tahunPelajaran->nama ?? '-' }} &bull; Peserta: {{ $smartq->pesertas()->count() }}</p>
</div>

{{-- FORM SECTION --}}
<div id="formSection">
<div class="row">
    {{-- Left: Info + Template + Mapel --}}
    <div class="col-lg-5">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan Import</h3>
            </div>
            <div class="card-body">
                <ol class="pl-3">
                    <li class="mb-2"><strong>Download template Excel</strong> — sudah berisi nama & NISN peserta</li>
                    <li class="mb-2"><strong>Isi kolom kuning:</strong> Peringkat Mapel, Peringkat Umum, Mapel, dan Status</li>
                    <li class="mb-2"><strong>Upload file</strong> — sistem akan menampilkan <strong>preview</strong> untuk dikonfirmasi</li>
                    <li class="mb-2"><strong>Periksa & Konfirmasi</strong> — pastikan data sudah sesuai, lalu simpan</li>
                </ol>

                <div class="alert alert-warning mb-0">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Ketentuan</h5>
                    <ul class="mb-0 pl-3">
                        <li>Kolom <strong>NAMA</strong> & <strong>NISN</strong> sudah terisi otomatis (jangan diubah)</li>
                        <li><strong>MAPEL:</strong> pilih dari dropdown atau ketik nama mapel pilihan</li>
                        <li><strong>Peringkat Mapel:</strong> ranking dalam mapel tersebut</li>
                        <li><strong>Peringkat Umum:</strong> ranking keseluruhan</li>
                        <li><strong>STATUS:</strong> pilih <code>diterima</code> atau <code>cadangan</code></li>
                        <li>Baris tanpa MAPEL akan <strong>dilewati</strong> (hanya proses yang sudah diisi)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-download"></i> Download Template</h3>
            </div>
            <div class="card-body text-center">
                <p class="text-muted">Template berisi data peserta (NAMA & NISN) + daftar mapel pilihan.</p>
                <a href="{{ route('admin.smartq.kelulusan.template', $smartq) }}" class="btn btn-success btn-lg" download>
                    <i class="fas fa-download"></i> Download Template
                </a>
            </div>
        </div>

        {{-- Daftar Mapel Pilihan --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book"></i> Daftar Mapel Pilihan</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Nama Mapel</th>
                            <th width="100">Kode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapelPilihan as $m)
                            <tr>
                                <td>{{ $m->nama_mapel }}</td>
                                <td><code>{{ $m->kode_mapel }}</code></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    <i class="fas fa-exclamation-circle"></i> Belum ada mapel pilihan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Upload Form --}}
    <div class="col-lg-7">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-upload"></i> Upload File Kelulusan</h3>
            </div>
            <div class="card-body">
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Pilih File <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file"
                                       accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="file">
                                    <i class="fas fa-file-upload text-muted"></i> Pilih file Excel (.xlsx)...
                                </label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Format: .xlsx, .xls &bull; Maks: 2MB
                        </small>
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg btn-block" id="btnImport">
                        <i class="fas fa-search"></i> Upload & Preview
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- end col-lg-7 --}}
</div>{{-- end row --}}
</div>{{-- end #formSection --}}

{{-- PREVIEW SECTION (full width) --}}
<div id="previewSection" style="display: none;">
    {{-- Stats bar --}}
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="import-stat-card import-stat-card--gray">
                <div class="import-stat-card__icon"><i class="fas fa-list"></i></div>
                <div class="import-stat-card__body">
                    <div class="import-stat-card__label">Total Baris Dibaca</div>
                    <div class="import-stat-card__value" id="previewTotal">0</div>
                    <div class="import-stat-card__desc">Data yang terbaca dari file Excel</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="import-stat-card import-stat-card--green" id="boxValid">
                <div class="import-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <div class="import-stat-card__body">
                    <div class="import-stat-card__label">Data Valid</div>
                    <div class="import-stat-card__value" id="countValid">0</div>
                    <div class="import-stat-card__desc">Siap disimpan ke database</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="import-stat-card import-stat-card--red" id="boxInvalid">
                <div class="import-stat-card__icon"><i class="fas fa-times-circle"></i></div>
                <div class="import-stat-card__body">
                    <div class="import-stat-card__label">Data Bermasalah</div>
                    <div class="import-stat-card__value" id="countInvalid">0</div>
                    <div class="import-stat-card__desc">Perlu diperbaiki di file Excel</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Table --}}
    <div class="card" style="border:0;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.10);overflow:hidden">
        {{-- Gradient header with inline override --}}
        <div class="card-header" style="background:linear-gradient(135deg,#2563eb,#0d9488)!important;border-bottom:1px solid rgba(255,255,255,.15)!important;padding:.7rem 1rem!important">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px">
                <div class="d-flex align-items-center" style="gap:8px">
                    <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-table" style="color:#fff;font-size:.9rem"></i>
                    </div>
                    <div>
                        <div style="color:rgba(255,255,255,.75);font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Langkah 2 dari 3</div>
                        <div style="color:#fff;font-weight:700;font-size:.95rem;line-height:1.2">Preview Data Import</div>
                    </div>
                </div>
                <div style="color:rgba(255,255,255,.8);font-size:.8rem">
                    Periksa data sebelum menyimpan
                </div>
            </div>
        </div>

        {{-- Filter tabs strip --}}
        <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:.55rem 1rem;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <span style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-right:4px">Filter:</span>
            <div id="filterBtns" style="display:flex;gap:6px;flex-wrap:wrap">
                <button type="button" class="filter-pill active" data-filter="all">
                    <i class="fas fa-list fa-xs"></i> Semua <span class="pill-count" id="fcAll">0</span>
                </button>
                <button type="button" class="filter-pill" data-filter="valid">
                    <i class="fas fa-check-circle fa-xs"></i> Valid <span class="pill-count" id="fcValid">0</span>
                </button>
                <button type="button" class="filter-pill" data-filter="invalid">
                    <i class="fas fa-times-circle fa-xs"></i> Bermasalah <span class="pill-count" id="fcInvalid">0</span>
                </button>
            </div>
        </div>

        {{-- DataTable --}}
        <div class="card-body" style="padding:0">
            <table id="previewTable" class="table table-hover mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th width="260">Peserta</th>
                        <th class="text-center" width="85">P. Mapel</th>
                        <th class="text-center" width="85">P. Umum</th>
                        <th width="155">Bidang Mapel</th>
                        <th width="125">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="previewTableBody"></tbody>
            </table>
        </div>
    </div>

    {{-- Confirm + Batal bar --}}
    <div class="card mb-4" style="border:0;border-radius:14px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #bbf7d0;box-shadow:0 4px 16px rgba(16,185,129,.1)">
        <div class="card-body" style="padding:.85rem 1.1rem">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-2 mb-lg-0">
                    <p class="mb-0" style="color:#15803d;font-size:.88rem">
                        <i class="fas fa-info-circle mr-1"></i>
                        Periksa data di atas, lalu klik <strong>Konfirmasi & Simpan</strong> jika sudah benar.
                    </p>
                </div>
                <div class="col-lg-3 mb-2 mb-lg-0">
                    <button type="button" class="btn btn-outline-secondary btn-block" id="btnBatal">
                        <i class="fas fa-undo mr-1"></i> Batal
                    </button>
                </div>
                <div class="col-lg-4">
                    <button type="button" class="btn btn-success btn-lg btn-block font-weight-bold" id="btnConfirm" disabled>
                        <i class="fas fa-save mr-1"></i> Konfirmasi &amp; Simpan
                        <span class="badge badge-light font-weight-bold ml-1" id="confirmCount">0</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FINAL RESULT SECTION --}}
<div id="finalResultSection" style="display: none;">
    <div class="row mb-3">
        <div class="col-md-6 mb-2">
            <div class="import-stat-card import-stat-card--green">
                <div class="import-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <div class="import-stat-card__body">
                    <div class="import-stat-card__label">Berhasil Disimpan</div>
                    <div class="import-stat-card__value" id="countSuccess">0</div>
                    <div class="import-stat-card__desc">Data kelulusan berhasil diperbarui</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="import-stat-card import-stat-card--red">
                <div class="import-stat-card__icon"><i class="fas fa-times-circle"></i></div>
                <div class="import-stat-card__body">
                    <div class="import-stat-card__label">Gagal Disimpan</div>
                    <div class="import-stat-card__value" id="countError">0</div>
                    <div class="import-stat-card__desc">Perlu diperiksa kembali</div>
                </div>
            </div>
        </div>
    </div>

    <div id="successDetail" style="display: none;">
        <div class="card import-result-card mb-3">
            <div class="card-header" style="background:linear-gradient(135deg,#10b981,#34d399);border-bottom:0;">
                <h3 class="card-title text-white mb-0"><i class="fas fa-check-circle mr-2"></i> Data Berhasil Disimpan</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">#</th>
                            <th>Nama Peserta</th>
                            <th width="130">NISN</th>
                            <th class="text-center" width="90">P. Mapel</th>
                            <th class="text-center" width="90">P. Umum</th>
                            <th width="150">Bidang Mapel</th>
                            <th width="120">Status</th>
                        </tr>
                    </thead>
                    <tbody id="successTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="errorDetail" style="display: none;">
        <div class="card import-result-card mb-3">
            <div class="card-header" style="background:linear-gradient(135deg,#fb7185,#f43f5e);border-bottom:0;">
                <h3 class="card-title text-white mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> Data Gagal</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">#</th>
                            <th>Nama Peserta</th>
                            <th width="130">NISN</th>
                            <th>Keterangan Kendala</th>
                        </tr>
                    </thead>
                    <tbody id="errorTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-3 mb-4">
        <button type="button" class="btn btn-primary mr-2" onclick="location.reload()">
            <i class="fas fa-redo"></i> Import Ulang
        </button>
        <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-success btn-lg">
            <i class="fas fa-eye"></i> Lihat Hasil di Periode
        </a>
    </div>
</div>

@include('admin.smartq._overlay')
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
    /* ====== IMPORT FORM CARD ====== */
    .custom-file-label { border: 2px dashed #ced4da; border-radius: 6px; transition: border-color .2s; }
    .custom-file-label:hover { border-color: #ffc107; }
    .custom-file-label::after { content: "Pilih File"; }

    /* ====== STAT CARDS ====== */
    .import-stat-card {
        position: relative; overflow: hidden; border: 0;
        border-radius: 16px; padding: .8rem .9rem;
        color: #fff; box-shadow: 0 10px 22px rgba(15,23,42,.10);
        display: flex; align-items: center; gap: .75rem;
    }
    .import-stat-card::after {
        content: ""; position: absolute; right: -24px; bottom: -28px;
        width: 120px; height: 120px; border-radius: 999px;
        background: rgba(255,255,255,.12);
    }
    .import-stat-card--gray  { background: linear-gradient(135deg,#64748b,#94a3b8); }
    .import-stat-card--green { background: linear-gradient(135deg,#10b981,#34d399); }
    .import-stat-card--red   { background: linear-gradient(135deg,#fb7185,#f43f5e); }
    .import-stat-card__icon {
        width: 42px; height: 42px; border-radius: 12px; flex: 0 0 42px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.18); font-size: 1rem; position: relative; z-index: 1;
    }
    .import-stat-card__body { position: relative; z-index: 1; flex: 1 1 auto; min-width: 0; }
    .import-stat-card__label { font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; opacity: .9; margin-bottom: .15rem; }
    .import-stat-card__value { font-size: 1.55rem; font-weight: 800; line-height: 1; margin-bottom: .2rem; }
    .import-stat-card__desc  { font-size: .75rem; opacity: .88; line-height: 1.3; }

    /* Remove unused preview card class */
    .import-preview-card { border: 0; border-radius: 16px; overflow: hidden; }
    .import-confirm-bar { display: none; } /* replaced with inline */

    /* ====== FILTER PILLS (light bg, not on gradient) ====== */
    .filter-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .3rem .8rem; border-radius: 999px; font-size: .8rem; font-weight: 600;
        border: 1.5px solid #e2e8f0; color: #64748b;
        background: #fff; cursor: pointer;
        transition: all .15s; user-select: none; line-height: 1;
    }
    .filter-pill:hover { border-color: #94a3b8; color: #374151; background: #f1f5f9; }
    .filter-pill.active { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,.3); }
    .filter-pill.active .pill-count { background: rgba(255,255,255,.25); color: #fff; }
    .filter-pill .pill-count { font-size: .71rem; background: #e2e8f0; border-radius: 999px; padding: .05rem .42rem; font-weight: 700; color: #475569; transition: all .15s; }

    /* ====== TABLE ====== */
    #previewTable thead th {
        background: #f8fafc; color: #475569;
        font-size: .72rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; border-bottom: 2px solid #e2e8f0;
        white-space: nowrap; padding: .55rem .75rem;
    }
    #previewTable tbody td { padding: .6rem .75rem; vertical-align: middle; font-size: .875rem; border-color: #f1f5f9; }
    #previewTable tbody tr:hover td { background-color: #f8fafc !important; }
    #previewTable tbody tr.row-valid td { background-color: #f0fdf4 !important; }
    #previewTable tbody tr.row-invalid td { background-color: #fff1f2 !important; }
    #previewTable tbody tr.row-valid td:first-child { border-left: 3px solid #22c55e; }
    #previewTable tbody tr.row-invalid td:first-child { border-left: 3px solid #f43f5e; }

    /* Peserta cell */
    .peserta-nama { font-weight: 700; font-size: .88rem; color: #0f172a; }
    .peserta-nisn { font-size: .77rem; color: #64748b; font-family: "SFMono-Regular",Consolas,monospace; letter-spacing: .02em; }
    .peserta-match { font-size: .73rem; margin-top: 3px; }
    .peserta-match.ok   { color: #16a34a; }
    .peserta-match.warn { color: #d97706; }
    .peserta-match.fail { color: #dc2626; }

    /* Badges */
    .badge-pill-success { background: #dcfce7; color: #15803d; border-radius: 999px; padding: .25rem .65rem; font-size: .76rem; font-weight: 700; }
    .badge-pill-warning { background: #fef9c3; color: #a16207; border-radius: 999px; padding: .25rem .65rem; font-size: .76rem; font-weight: 700; }
    .badge-pill-info    { background: #e0f2fe; color: #0369a1; border-radius: 999px; padding: .25rem .65rem; font-size: .76rem; font-weight: 700; }
    .badge-pill-danger  { background: #fee2e2; color: #b91c1c; border-radius: 999px; padding: .25rem .65rem; font-size: .76rem; font-weight: 700; }
    .badge-pill-gray    { background: #f1f5f9; color: #64748b; border-radius: 999px; padding: .25rem .65rem; font-size: .76rem; font-weight: 700; }

    /* DataTables controls - inside card-body */
    #previewTable_wrapper { padding: 0; }
    #previewTable_wrapper .dataTables_length label,
    #previewTable_wrapper .dataTables_filter label { font-size: .84rem; color: #64748b; margin: 0; }
    #previewTable_wrapper .dataTables_length select,
    #previewTable_wrapper .dataTables_filter input {
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: .28rem .55rem; font-size: .84rem; color: #374151;
    }
    #previewTable_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); outline: none;
    }
    #previewTable_wrapper .dataTables_info { font-size: .8rem; color: #94a3b8; }
    #previewTable_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important; padding: 3px 10px !important; font-size: .82rem;
    }
    #previewTable_wrapper .dataTables_paginate .paginate_button.current,
    #previewTable_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #3b82f6 !important; border-color: #3b82f6 !important; color: #fff !important;
    }
    /* Remove vertical column borders from DataTables */
    #previewTable.dataTable thead th,
    #previewTable.dataTable tbody td { border-left: none !important; border-right: none !important; }
    #previewTable.dataTable { border-collapse: collapse !important; }
    /* Keep valid/invalid left accent */
    #previewTable tbody tr.row-valid td:first-child { border-left: 3px solid #22c55e !important; }
    #previewTable tbody tr.row-invalid td:first-child { border-left: 3px solid #f43f5e !important; }

    /* Confirm bar */
    .import-confirm-bar {
        border: 0; border-radius: 14px;
        background: linear-gradient(135deg,#f0fdf4,#dcfce7);
        box-shadow: 0 4px 16px rgba(16,185,129,.12);
        border: 1.5px solid #bbf7d0;
    }

    /* Result cards */
    .import-result-card {
        border: 0; border-radius: 18px;
        box-shadow: 0 14px 30px rgba(15,23,42,.08); overflow: hidden;
    }
    .import-result-card thead th {
        background: #f8fafc; color: #475569;
        font-size: .72rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; padding: .55rem .75rem;
        white-space: nowrap; border-bottom: 2px solid #e2e8f0;
    }
    .import-result-card tbody td { padding: .6rem .75rem; vertical-align: middle; font-size: .875rem; border-color: #f1f5f9; }
    .import-result-card tbody tr:hover td { background: #f8fafc !important; }

    /* Animations */
    #previewSection, #finalResultSection { animation: fadeSlideIn .35s ease; }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
    var tempPath = null;
    var dtPreview = null;
    var allRows = [];

    // File input label
    $('.custom-file-input').on('change', function() {
        var name = $(this).val().split('\\').pop();
        var ext = name.split('.').pop().toLowerCase();
        var icon = ['xlsx','xls'].includes(ext) ? 'fa-file-excel text-success' : 'fa-file text-muted';
        $(this).siblings('.custom-file-label').html('<i class="fas ' + icon + '"></i> ' + name);
    });

    function matchIcon(ok) {
        return ok
            ? '<i class="fas fa-check-circle match-icon match-ok"></i>'
            : '<i class="fas fa-times-circle match-icon match-fail"></i>';
    }

    function escHtml(str) {
        if (!str) return '-';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Step 1: Upload & Preview
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        var fileInput = $('#file')[0];
        if (!fileInput.files.length) {
            Swal.fire({ icon: 'error', title: 'File belum dipilih', text: 'Pilih file Excel (.xlsx) terlebih dahulu.' });
            return;
        }

        var file = fileInput.files[0];
        var ext = file.name.split('.').pop().toLowerCase();
        if (!['xlsx','xls'].includes(ext)) {
            Swal.fire({ icon: 'error', title: 'Format Salah', text: 'Gunakan file .xlsx atau .xls' });
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Maksimal 2MB.' });
            return;
        }

        showSmartqOverlay('Memproses preview...', 'Membaca dan memvalidasi data', 'search');
        var msgInterval = smartqOverlayMessages([
            'Membaca file Excel...',
            'Mencocokkan NISN dengan peserta...',
            'Memvalidasi mapel & status...',
            'Menyiapkan preview...',
        ], 1500);

        $('#btnImport').prop('disabled', true);
        var formData = new FormData($('#importForm')[0]);

        $.ajax({
            url: '{{ route("admin.smartq.kelulusan.import.process", $smartq) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                clearInterval(msgInterval);
                hideSmartqOverlay();

                tempPath = res.temp_path;
                var d = res.data;

                $('#countValid').text(d.valid_count);
                $('#countInvalid').text(d.error_count);
                $('#previewTotal').text(d.total);
                allRows = d.rows;

                // Build preview table rows
                var html = '';
                d.rows.forEach(function(r) {
                    var cls = r.valid ? 'row-valid' : 'row-invalid';

                    // Peserta cell: nama + nisn + match indicator
                    var namaMatchHtml = r.nisn_match
                        ? (r.nama_match
                            ? '<div class="peserta-match ok"><i class="fas fa-check-circle"></i> Nama cocok</div>'
                            : '<div class="peserta-match warn"><i class="fas fa-exclamation-circle"></i> Nama berbeda: <em>' + escHtml(r.nama_db) + '</em></div>')
                        : '<div class="peserta-match fail"><i class="fas fa-times-circle"></i> NISN tidak ditemukan</div>';
                    var pesertaCell = '<div>'
                        + '<div class="peserta-nama">' + escHtml(r.nama_file) + '</div>'
                        + '<div class="peserta-nisn"><i class="fas fa-id-card fa-xs mr-1"></i>' + escHtml(r.nisn) + '</div>'
                        + namaMatchHtml
                        + '</div>';

                    // Mapel cell
                    var mapelCell = '';
                    if (r.mapel) {
                        mapelCell = r.mapel_match
                            ? '<span class="badge-pill-info">' + escHtml(r.mapel) + '</span>'
                            : '<span class="badge-pill-gray">' + escHtml(r.mapel) + '</span> <small class="text-danger"><i class="fas fa-exclamation-circle"></i> tidak dikenal</small>';
                    } else {
                        mapelCell = '<span class="text-muted fst-italic">—</span>';
                    }

                    // Status cell
                    var statusBadge = '';
                    if (r.status) {
                        if (r.status_valid) {
                            statusBadge = r.status === 'diterima'
                                ? '<span class="badge-pill-success"><i class="fas fa-check mr-1"></i>Diterima</span>'
                                : '<span class="badge-pill-warning"><i class="fas fa-clock mr-1"></i>Cadangan</span>';
                        } else {
                            statusBadge = '<span class="badge-pill-danger">' + escHtml(r.status) + '</span><br><small class="text-danger">tidak valid</small>';
                        }
                    } else {
                        statusBadge = '<span class="badge-pill-gray">Kosong</span>';
                    }

                    // Keterangan cell
                    var keterangan = r.valid
                        ? '<span class="text-success font-weight-600"><i class="fas fa-check-circle mr-1"></i>Siap disimpan</span>'
                        : '<div>' + r.errors.map(function(e) { return '<div class="d-flex align-items-start" style="gap:4px"><i class="fas fa-dot-circle text-danger mt-1" style="font-size:.65rem;flex:0 0 auto"></i><small class="text-danger">' + escHtml(e) + '</small></div>'; }).join('') + '</div>';

                    html += '<tr class="' + cls + '" data-valid="' + (r.valid ? '1' : '0') + '">'
                        + '<td class="text-center align-middle"><strong>' + r.row + '</strong></td>'
                        + '<td class="align-middle">' + pesertaCell + '</td>'
                        + '<td class="text-center align-middle"><strong>' + (r.peringkat_mapel || '<span class="text-muted">-</span>') + '</strong></td>'
                        + '<td class="text-center align-middle"><strong>' + (r.peringkat_umum || '<span class="text-muted">-</span>') + '</strong></td>'
                        + '<td class="align-middle">' + mapelCell + '</td>'
                        + '<td class="align-middle">' + statusBadge + '</td>'
                        + '<td class="align-middle">' + keterangan + '</td>'
                        + '</tr>';
                });
                $('#previewTableBody').html(html);

                // Init DataTable with proper custom search
                if (dtPreview) { dtPreview.destroy(); }

                // Custom search function for valid/invalid filter
                var currentFilter = 'all';
                $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
                    return fn._previewFilter !== true; // remove old filter if reinit
                });
                var filterFn = function(settings, data, dataIndex) {
                    if (!dtPreview || settings.nTable.id !== 'previewTable') return true;
                    if (currentFilter === 'all') return true;
                    var node = dtPreview.row(dataIndex).node();
                    var isValid = $(node).data('valid') == '1';
                    return currentFilter === 'valid' ? isValid : !isValid;
                };
                filterFn._previewFilter = true;
                $.fn.dataTable.ext.search.push(filterFn);

                dtPreview = $('#previewTable').DataTable({
                    pageLength: 25,
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ baris',
                        info: 'Menampilkan _START_–_END_ dari _TOTAL_ baris',
                        infoEmpty: 'Tidak ada data',
                        infoFiltered: '(difilter dari _MAX_ total)',
                        paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                        zeroRecords: 'Tidak ada data yang sesuai filter.'
                    },
                    responsive: false,
                    columnDefs: [
                        { orderable: false, targets: [1, 6] },
                        { className: 'text-center align-middle', targets: [0, 2, 3] },
                        { className: 'align-middle', targets: [1, 4, 5, 6] },
                    ],
                    dom: '<"d-flex align-items-center justify-content-between px-3 pt-3 pb-2"<""l><""f>>'
                        + 't'
                        + '<"d-flex align-items-center justify-content-between px-3 pt-2 pb-3"<""i><""p>>',
                    drawCallback: function() {
                        $('#fcAll').text(this.api().page.info().recordsTotal);
                    }
                });

                // Set filter badge counts
                var totalAll = d.rows.length;
                var totalValid = d.valid_count;
                var totalInvalid = d.error_count;
                $('#fcAll').text(totalAll);
                $('#fcValid').text(totalValid);
                $('#fcInvalid').text(totalInvalid);

                // Filter pill click handler (works for both span and button)
                $('#filterBtns').off('click', '.filter-pill').on('click', '.filter-pill', function() {
                    $('#filterBtns .filter-pill').removeClass('active');
                    $(this).addClass('active');
                    currentFilter = $(this).data('filter');
                    dtPreview.draw();
                });

                // Enable confirm button if there are valid rows
                if (d.valid_count > 0) {
                    $('#btnConfirm').prop('disabled', false);
                    $('#confirmCount').text(d.valid_count);
                } else {
                    $('#btnConfirm').prop('disabled', true);
                    $('#confirmCount').text('0');
                }

                $('#formSection').slideUp(300);
                $('#previewSection').show();
                $('html, body').animate({ scrollTop: $('#previewSection').offset().top - 60 }, 400);

                if (d.error_count > 0 && d.valid_count > 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Preview Siap',
                        html: '<strong>' + d.valid_count + '</strong> data valid, <strong>' + d.error_count + '</strong> bermasalah.<br>Periksa tabel lalu klik <b>Konfirmasi & Simpan</b>.',
                    });
                } else if (d.valid_count === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Semua Data Bermasalah',
                        html: 'Tidak ada data valid. Perbaiki file lalu upload ulang.',
                    });
                }
            },
            error: function(xhr) {
                clearInterval(msgInterval);
                hideSmartqOverlay();
                $('#btnImport').prop('disabled', false);

                var msg = 'Terjadi kesalahan server.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // Filter buttons (handled inside DataTable init after data loaded)

    // Batal button
    $('#btnBatal').on('click', function() {
        location.reload();
    });

    // Step 2: Confirm & Save
    $('#btnConfirm').on('click', function() {
        if (!tempPath) return;

        Swal.fire({
            title: '<i class="fas fa-save text-success"></i> Konfirmasi Simpan',
            html: '<p>Simpan <strong>' + $('#confirmCount').text() + '</strong> data valid ke database?</p>' +
                  '<p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Data peserta yang cocok akan diperbarui.</small></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            confirmButtonColor: '#28a745',
            reverseButtons: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;

            showSmartqOverlay('Menyimpan data kelulusan...', 'Mohon tunggu, jangan tutup halaman ini', 'save');
            var msgInterval = smartqOverlayMessages([
                'Menyimpan data kelulusan...',
                'Mengupdate status peserta...',
                'Hampir selesai...',
            ], 2000);

            $('#btnConfirm').prop('disabled', true);
            $('#btnBatal').prop('disabled', true);

            $.ajax({
                url: '{{ route("admin.smartq.kelulusan.import.confirm", $smartq) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', temp_path: tempPath },
                success: function(res) {
                    clearInterval(msgInterval);
                    hideSmartqOverlay();

                    var d = res.data;
                    $('#countSuccess').text(d.success_count);
                    $('#countError').text(d.failed_count);

                    if (d.success_count > 0) {
                        var html = '';
                        d.success_rows.forEach(function(r) {
                            var badge = r.status === 'diterima'
                                ? '<span class="badge-pill-success"><i class="fas fa-check mr-1"></i>Diterima</span>'
                                : '<span class="badge-pill-warning"><i class="fas fa-clock mr-1"></i>Cadangan</span>';
                            html += '<tr><td class="text-center">' + r.row + '</td><td><strong>' + escHtml(r.nama) + '</strong></td><td><span class="peserta-nisn">' + escHtml(r.nisn) + '</span></td><td class="text-center">' + (r.peringkat_mapel || '-') + '</td><td class="text-center">' + (r.peringkat_umum || '-') + '</td><td><span class="badge-pill-info">' + escHtml(r.mapel) + '</span></td><td>' + badge + '</td></tr>';
                        });
                        $('#successTableBody').html(html);
                        $('#successDetail').show();
                    }

                    if (d.failed_count > 0) {
                        var html = '';
                        d.errors.forEach(function(r) {
                            html += '<tr><td class="text-center">' + r.row + '</td><td>' + escHtml(r.nama || '-') + '</td><td><code>' + escHtml(r.nisn) + '</code></td><td><span class="text-danger">' + escHtml(r.error) + '</span></td></tr>';
                        });
                        $('#errorTableBody').html(html);
                        $('#errorDetail').show();
                    }

                    $('#previewSection').slideUp(300);
                    $('#finalResultSection').show();
                    $('html, body').animate({ scrollTop: 0 }, 400);

                    if (d.failed_count === 0) {
                        Swal.fire({ icon: 'success', title: 'Berhasil Disimpan!', html: '<strong>' + d.success_count + '</strong> data kelulusan berhasil disimpan.' });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Sebagian Gagal', html: '<strong>' + d.success_count + '</strong> berhasil, <strong>' + d.failed_count + '</strong> gagal.' });
                    }
                },
                error: function(xhr) {
                    clearInterval(msgInterval);
                    hideSmartqOverlay();
                    $('#btnConfirm').prop('disabled', false);
                    $('#btnBatal').prop('disabled', false);

                    var msg = 'Terjadi kesalahan server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: msg });
                }
            });
        });
    });
});
</script>
@stop
