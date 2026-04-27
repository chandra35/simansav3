@extends('adminlte::page')

@section('title', 'Import EMIS - SIMANSA')

@section('css')
<style>
/* ── Upload Zone ─────────────────────────────── */
.emis-dropzone {
    border: 2px dashed #adb5bd;
    border-radius: 12px;
    background: #f8f9fa;
    padding: 2.5rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.emis-dropzone.drag-over {
    border-color: #3b82f6;
    background: #eff6ff;
}
.emis-dropzone .dz-icon { font-size: 2.5rem; color: #adb5bd; margin-bottom: .5rem; }
.emis-dropzone.drag-over .dz-icon { color: #3b82f6; }
.emis-dropzone .dz-text { font-size: .9rem; color: #6c757d; }
.emis-dropzone .dz-hint { font-size: .75rem; color: #adb5bd; margin-top: .3rem; }
.emis-dropzone .dz-filename { font-size: .82rem; color: #1e40af; font-weight: 600; margin-top: .4rem; display: none; }

/* ── Overlay (spinner / progress) ───────────── */
.emis-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: #fff;
}
.emis-overlay .ov-box {
    background: #fff; color: #374151;
    border-radius: 16px; padding: 2rem 2.5rem;
    text-align: center; min-width: 300px;
    box-shadow: 0 8px 32px rgba(0,0,0,.25);
}
.emis-overlay .ov-title { font-size: 1rem; font-weight: 700; margin-bottom: .5rem; }
.emis-overlay .ov-sub   { font-size: .8rem; color: #6b7280; margin-bottom: 1rem; }
.emis-overlay .ov-spinner { font-size: 2rem; color: #3b82f6; margin-bottom: .75rem; }
.emis-progress { height: 8px; border-radius: 4px; overflow: hidden; background: #e5e7eb; margin-top: .75rem; }
.emis-progress-bar {
    height: 100%; border-radius: 4px;
    background: linear-gradient(90deg, #3b82f6, #06b6d4);
    transition: width .3s ease;
    width: 0%;
}

/* ── Preview Section ─────────────────────────── */
.preview-section { display: none; }

/* Tab bar */
.emis-tabs { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .75rem; }
.emis-tab {
    padding: .3rem .85rem; border-radius: 20px;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid #dee2e6; background: #f8f9fa; color: #6c757d;
    transition: all .15s;
}
.emis-tab:hover   { border-color: #adb5bd; }
.emis-tab.active  { background: #1e40af; color: #fff; border-color: #1e40af; }
.emis-tab.tab-baru    .tab-badge { background: #d1fae5; color: #065f46; }
.emis-tab.tab-update  .tab-badge { background: #dbeafe; color: #1e40af; }
.emis-tab.tab-fuzzy   .tab-badge { background: #fef9c3; color: #78350f; }
.emis-tab.tab-skip    .tab-badge { background: #f3f4f6; color: #6b7280; }
.emis-tab.active .tab-badge { background: rgba(255,255,255,.25); color: #fff; }
.tab-badge { display: inline-block; border-radius: 10px; padding: 0 .45rem; font-size: .7rem; margin-left: .25rem; }

/* Preview table */
.preview-wrap { overflow-x: auto; }
.ptable { width: 100%; border-collapse: collapse; font-size: .78rem; }
.ptable th {
    font-size: .7rem; text-transform: uppercase; letter-spacing: .03em;
    padding: .45rem .75rem; border-bottom: 2px solid #dee2e6;
    background: #f8f9fa; color: #6c757d; white-space: nowrap;
}
.ptable td {
    padding: .4rem .75rem; border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.ptable tr:last-child td { border-bottom: none; }
.ptable tr.row-baru   { border-left: 3px solid #10b981; }
.ptable tr.row-update { border-left: 3px solid #3b82f6; }
.ptable tr.row-fuzzy  { border-left: 3px solid #f59e0b; background: #fffdf0; }
.ptable tr.row-skip   { border-left: 3px solid #d1d5db; opacity: .55; }

/* Status badges */
.s-baru    { background: #d1fae5; color: #065f46; }
.s-update  { background: #dbeafe; color: #1e40af; }
.s-fuzzy   { background: #fef9c3; color: #78350f; }
.s-skip    { background: #f3f4f6; color: #6b7280; }
.s-lengkap { background: #f0fdf4; color: #15803d; }
.s-badge   { display: inline-block; border-radius: 20px; padding: .1rem .55rem; font-size: .68rem; font-weight: 700; }
.ptable tr.row-complete { opacity: .6; }

/* Fuzzy note */
.fuzzy-note {
    font-size: .7rem; color: #78350f;
    background: #fef3c7; border-radius: 4px;
    padding: .15rem .4rem; margin-top: .2rem;
    display: inline-block;
}

/* Diff cell */
.diff-old { color: #dc2626; text-decoration: line-through; font-size: .7rem; }
.diff-new { color: #059669; font-weight: 600; }

/* Bulk action bar */
.bulk-bar {
    background: #f8f9fa; border-radius: 8px; padding: .5rem .85rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .5rem; margin-bottom: .75rem;
    font-size: .8rem;
}
.bulk-bar .sel-count { color: #374151; }
.bulk-bar .sel-count strong { color: #1e40af; }

/* Result section */
.result-section { display: none; }
.result-stat {
    border-radius: 12px; padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: .75rem;
}
.result-stat .rs-icon { font-size: 1.6rem; }
.result-stat .rs-num  { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.result-stat .rs-lbl  { font-size: .78rem; opacity: .8; }
.rs-success { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
.rs-error   { background: linear-gradient(135deg, #fee2e2, #fecaca); }

/* Responsive: hide some cols on small screens */
@media (max-width: 768px) {
    .ptable .col-hide-sm { display: none; }
}
</style>
@endsection

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cloud-download-alt"></i> Import Data EMIS</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Siswa
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ── Overlays ────────────────────────────── --}}
    <div class="emis-overlay d-none" id="overlayParse">
        <div class="ov-box">
            <div class="ov-spinner"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="ov-title">Membaca File EMIS...</div>
            <div class="ov-sub">Sedang menganalisis dan mencocokkan data dengan Simansa</div>
        </div>
    </div>

    <div class="emis-overlay d-none" id="overlaySave">
        <div class="ov-box">
            <div class="ov-spinner"><i class="fas fa-database"></i></div>
            <div class="ov-title" id="savingTitle">Menyimpan Data...</div>
            <div class="ov-sub" id="savingSubtitle">Harap tunggu, jangan tutup halaman ini</div>
            <div class="emis-progress">
                <div class="emis-progress-bar" id="saveProgressBar" style="width:5%"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">

            {{-- ── Step 1: Upload ─────────────────── --}}
            <div id="uploadSection">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-upload text-primary mr-1"></i>
                            Upload File Export EMIS
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="emis-dropzone" id="dropZone">
                            <div class="dz-icon"><i class="fas fa-file-excel"></i></div>
                            <div class="dz-text">Klik atau <strong>drag &amp; drop</strong> file Excel EMIS di sini</div>
                            <div class="dz-hint">Format: .xlsx atau .xls — Maksimal 10MB — Mendukung multi-sheet (semua tingkat)</div>
                            <div class="dz-filename" id="dzFilename"><i class="fas fa-check-circle text-success mr-1"></i><span id="dzFilenameTxt"></span></div>
                        </div>
                        <input type="file" id="fileInput" accept=".xlsx,.xls" class="d-none">

                        <div class="mt-3 d-flex align-items-center" style="gap:.5rem;">
                            <button type="button" class="btn btn-primary" id="btnParse" disabled>
                                <i class="fas fa-search mr-1"></i> Analisis &amp; Preview
                            </button>
                            <small class="text-muted">Pilih file terlebih dahulu, lalu klik Analisis untuk melihat preview sebelum menyimpan.</small>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle text-info mr-1"></i> Cara Menggunakan</h3>
                        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                    </div>
                    <div class="card-body">
                        <ol class="mb-2" style="font-size:.85rem; padding-left:1.2rem;">
                            <li>Export data siswa dari portal EMIS Kemenag (format Excel, boleh multi-tingkat)</li>
                            <li>Upload file di atas → sistem akan membaca semua sheet secara otomatis</li>
                            <li>Review preview: centang baris yang ingin disimpan, koreksi jika ada ketidaksesuaian</li>
                            <li>Klik <strong>Simpan Data Terpilih</strong> untuk menyimpan ke database</li>
                        </ol>
                        <div class="row" style="font-size:.8rem;">
                            <div class="col-sm-6">
                                <p class="mb-1"><span class="s-badge s-baru">BARU</span> Siswa belum ada di Simansa → akan ditambahkan</p>
                                <p class="mb-1"><span class="s-badge s-update">UPDATE</span> Cocok by NISN/NIK → data kosong akan diisi dari EMIS</p>
                                <p class="mb-1"><span class="s-badge s-lengkap">✓ LENGKAP</span> Data di Simansa sudah lengkap → default tidak dipilih, bisa di-toggle</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><span class="s-badge s-fuzzy">FUZZY</span> Nama mirip (≥80%) + tingkat cocok → perlu dikonfirmasi</p>
                                <p class="mb-1"><span class="s-badge s-skip">SKIP</span> Duplikat dalam file → dilewati otomatis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Step 2: Preview Table ───────────── --}}
            <div class="preview-section" id="previewSection">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-table text-secondary mr-1"></i>
                                Preview Data EMIS
                            </h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-secondary" id="btnReupload">
                                <i class="fas fa-redo mr-1"></i> Upload Ulang
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-0">

                        {{-- Tab bar --}}
                        <div class="emis-tabs" id="tabBar">
                            <div class="emis-tab active" data-filter="all">Semua <span class="tab-badge" id="cnt-all">0</span></div>
                            <div class="emis-tab tab-baru" data-filter="baru">Baru <span class="tab-badge" id="cnt-baru">0</span></div>
                            <div class="emis-tab tab-update" data-filter="update">Update <span class="tab-badge" id="cnt-update">0</span></div>
                            <div class="emis-tab tab-fuzzy" data-filter="fuzzy"><i class="fas fa-exclamation-triangle mr-1" style="font-size:.65rem;"></i>Fuzzy <span class="tab-badge" id="cnt-fuzzy">0</span></div>
                            <div class="emis-tab tab-skip" data-filter="skip">Skip <span class="tab-badge" id="cnt-skip">0</span></div>
                        </div>

                        {{-- Bulk action bar --}}
                        <div class="bulk-bar">
                            <div class="sel-count"><strong id="selCountNum">0</strong> baris dipilih dari <span id="selCountTotal">0</span> yang ditampilkan</div>
                            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                                <label style="display:flex;align-items:center;gap:.3rem;cursor:pointer;font-size:.8rem;color:#374151;white-space:nowrap;margin:0;" title="Data yang sudah lengkap di Simansa (NISN, NIK, TTL, ortu) akan ikut diproses">
                                    <input type="checkbox" id="chkUpdateLengkap" style="cursor:pointer;">
                                    <span>Sertakan data lengkap</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnCheckAll">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnUncheckAll">Batal Semua</button>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="preview-wrap">
                            <table class="ptable" id="previewTable">
                                <thead>
                                    <tr>
                                        <th style="width:36px;"></th>
                                        <th style="width:32px;">#</th>
                                        <th>Status</th>
                                        <th>Nama Lengkap (EMIS)</th>
                                        <th>NISN</th>
                                        <th class="col-hide-sm">NIK</th>
                                        <th class="col-hide-sm">Tgl Lahir</th>
                                        <th style="width:50px;">Tingkat</th>
                                        <th>Nama Ayah</th>
                                        <th>Nama Ibu</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTbody">
                                    {{-- Diisi via JS --}}
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-success" id="btnSave" disabled>
                            <i class="fas fa-save mr-1"></i> Simpan <span id="btnSaveCount">0</span> Data Terpilih
                        </button>
                        <small class="text-muted ml-2">Hanya baris yang dicentang yang akan disimpan.</small>
                    </div>
                </div>
            </div>

            {{-- ── Step 3: Result ──────────────────── --}}
            <div class="result-section" id="resultSection">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle text-success mr-1"></i> Import Selesai</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6 mb-2">
                                <div class="result-stat rs-success">
                                    <div class="rs-icon">✅</div>
                                    <div>
                                        <div class="rs-num" id="rsDone">0</div>
                                        <div class="rs-lbl">Data berhasil disimpan</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="result-stat rs-error">
                                    <div class="rs-icon">❌</div>
                                    <div>
                                        <div class="rs-num" id="rsErr">0</div>
                                        <div class="rs-lbl">Data gagal disimpan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="errList" class="d-none">
                            <h6 class="font-weight-bold text-danger">Detail Error:</h6>
                            <table class="table table-sm table-bordered" style="font-size:.78rem;">
                                <thead class="thead-light"><tr><th>#</th><th>Nama</th><th>NISN</th><th>Keterangan</th></tr></thead>
                                <tbody id="errListBody"></tbody>
                            </table>
                        </div>
                        <div class="mt-2" style="display:flex;gap:.5rem;flex-wrap:wrap;">
                            <a href="{{ route('admin.siswa.index') }}" class="btn btn-primary">
                                <i class="fas fa-list mr-1"></i> Lihat Data Siswa
                            </a>
                            <button type="button" class="btn btn-secondary" id="btnImportLagi">
                                <i class="fas fa-redo mr-1"></i> Import Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Right sidebar: format info ─────────── --}}
        <div class="col-lg-4">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-columns mr-1"></i> Kolom yang Dikenali</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.78rem;">
                        <thead class="thead-light"><tr><th>Kolom EMIS</th><th>Disimpan ke</th></tr></thead>
                        <tbody>
                            <tr><td>Nama Lengkap</td><td>nama_lengkap</td></tr>
                            <tr><td>NISN</td><td>nisn</td></tr>
                            <tr><td>NIK</td><td>nik</td></tr>
                            <tr><td>Tempat Lahir</td><td>tempat_lahir</td></tr>
                            <tr><td>Tanggal Lahir</td><td>tanggal_lahir</td></tr>
                            <tr><td>Jenis Kelamin</td><td>jenis_kelamin</td></tr>
                            <tr><td>Alamat</td><td>alamat_siswa</td></tr>
                            <tr><td>No Telepon</td><td>nomor_hp</td></tr>
                            <tr><td>Nama Ayah Kandung</td><td>ortu.nama_ayah</td></tr>
                            <tr><td>Nama Ibu Kandung</td><td>ortu.nama_ibu</td></tr>
                            <tr><td>Tingkat - Rombel</td><td>deteksi tingkat</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle text-warning mr-1"></i> Catatan Penting</h3>
                </div>
                <div class="card-body" style="font-size:.8rem;">
                    <ul class="mb-0 pl-3">
                        <li>Data siswa yang sudah ada <strong>tidak akan ditimpa</strong> kecuali field yang masih kosong</li>
                        <li>Nama Ayah/Ibu <strong>selalu diperbarui</strong> dari EMIS (data resmi)</li>
                        <li>Kelas/rombel <strong>tidak diubah</strong> — tetap seperti yang sudah di-assign</li>
                        <li>Status Fuzzy perlu dikonfirmasi manual sebelum menyimpan</li>
                        <li>File multi-sheet (semua tingkat dalam satu file) didukung</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<script>
(function ($) {
    'use strict';

    // ── State ────────────────────────────────────
    let previewData = [];          // full parse result from server
    let currentFilter = 'all';    // active tab filter

    // ── DOM refs ─────────────────────────────────
    const $uploadSection  = $('#uploadSection');
    const $previewSection = $('#previewSection');
    const $resultSection  = $('#resultSection');
    const $overlayParse   = $('#overlayParse');
    const $overlaySave    = $('#overlaySave');
    const $progressBar    = $('#saveProgressBar');

    // ── File input / drag-drop ───────────────────
    $('#dropZone').on('click', function () { $('#fileInput').trigger('click'); });

    $('#fileInput').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (!/\.(xlsx|xls)$/i.test(file.name)) {
            toastr.error('Format file harus .xlsx atau .xls');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            toastr.error('Ukuran file maksimal 10MB');
            return;
        }
        $('#dzFilenameTxt').text(file.name);
        $('#dzFilename').show();
        $('#btnParse').prop('disabled', false);
    });

    $('#dropZone').on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        if (e.type === 'drop') {
            const file = e.originalEvent.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                $('#fileInput')[0].files = dt.files;
                $('#fileInput').trigger('change');
            }
        }
    });

    // ── Analisis button ──────────────────────────
    $('#btnParse').on('click', function () {
        const file = $('#fileInput')[0].files[0];
        if (!file) { toastr.warning('Pilih file terlebih dahulu.'); return; }

        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', '{{ csrf_token() }}');

        $overlayParse.removeClass('d-none');

        $.ajax({
            url: '{{ route("admin.emis-import.parse") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            timeout: 120000,
            success: function (res) {
                if (!res.success) {
                    toastr.error(res.message || 'Gagal membaca file.');
                    return;
                }
                previewData = res.preview;
                renderPreview(res.stats);
                $uploadSection.hide();
                $previewSection.show();
                $resultSection.hide();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat membaca file.';
                toastr.error(msg);
            },
            complete: function () {
                $overlayParse.addClass('d-none');
            }
        });
    });

    // ── Render preview ───────────────────────────
    function renderPreview(stats) {
        // Update tab counts
        $('#cnt-all').text(stats.total);
        $('#cnt-baru').text(stats.baru);
        $('#cnt-update').text(stats.update);
        $('#cnt-fuzzy').text(stats.fuzzy);
        $('#cnt-skip').text(stats.skip);

        // Reset tab
        currentFilter = 'all';
        $('.emis-tab').removeClass('active');
        $('.emis-tab[data-filter="all"]').addClass('active');

        renderTable();
    }

    function renderTable() {
        const $tbody = $('#previewTbody');
        $tbody.empty();

        let shown = 0;
        previewData.forEach(function (item, idx) {
            if (currentFilter !== 'all' && item.action !== currentFilter) return;
            shown++;

            const isSkip     = item.action === 'skip';
            const canSelect  = !isSkip;
            const isChecked  = canSelect && item.selected;
            const isComplete = !!item.existing_complete;

            let actionLabel = {
                baru:   '<span class="s-badge s-baru">BARU</span>',
                update: '<span class="s-badge s-update">UPDATE</span>',
                fuzzy:  '<span class="s-badge s-fuzzy"><i class="fas fa-exclamation-triangle mr-1"></i>FUZZY</span>',
                skip:   '<span class="s-badge s-skip">SKIP</span>',
            }[item.action] || item.action;
            if (isComplete) {
                actionLabel += '<br><span class="s-badge s-lengkap" style="margin-top:.15rem;">&#10003; LENGKAP</span>';
            }

            // Keterangan column
            let keterangan = '';
            if (item.action === 'update' || item.action === 'fuzzy') {
                keterangan = '<span style="font-size:.7rem;color:#6b7280;">by ' + (item.confidence || '') + '</span>';
            }
            if (item.action === 'fuzzy' && item.fuzzy_note) {
                keterangan += '<br><span class="fuzzy-note"><i class="fas fa-info-circle mr-1"></i>' + escHtml(item.fuzzy_note) + '</span>';
            }
            if (item.action === 'skip' && item.confidence) {
                keterangan = '<span style="font-size:.7rem;color:#6b7280;">' + escHtml(item.confidence) + '</span>';
            }

            // Name: show EMIS name, and if update/fuzzy show old name below
            let namaCell = escHtml(item.emis.nama_lengkap || '-');
            if ((item.action === 'update' || item.action === 'fuzzy') && item.existing) {
                if (item.existing.nama_lengkap !== item.emis.nama_lengkap) {
                    namaCell += '<br><span class="diff-old">' + escHtml(item.existing.nama_lengkap) + '</span>';
                }
            }

            // Diff cells for NISN
            let nisnCell = escHtml(item.emis.nisn || '-');
            if (item.action !== 'baru' && item.existing && item.existing.nisn !== item.emis.nisn && item.emis.nisn) {
                nisnCell = '<span class="diff-new">' + escHtml(item.emis.nisn) + '</span>';
                if (item.existing.nisn) nisnCell += '<br><span class="diff-old">' + escHtml(item.existing.nisn) + '</span>';
            }

            // Ortu diff
            const ayahEmis    = item.emis.nama_ayah || '-';
            const ibuEmis     = item.emis.nama_ibu  || '-';
            let ayahCell = escHtml(ayahEmis);
            let ibuCell  = escHtml(ibuEmis);
            if (item.existing) {
                if (item.existing.nama_ayah && item.existing.nama_ayah !== ayahEmis) {
                    ayahCell = '<span class="diff-new">' + escHtml(ayahEmis) + '</span><br><span class="diff-old">' + escHtml(item.existing.nama_ayah) + '</span>';
                }
                if (item.existing.nama_ibu && item.existing.nama_ibu !== ibuEmis) {
                    ibuCell  = '<span class="diff-new">' + escHtml(ibuEmis) + '</span><br><span class="diff-old">' + escHtml(item.existing.nama_ibu) + '</span>';
                }
            }

            const tr = `<tr class="row-${item.action}${isComplete ? ' row-complete' : ''}" data-idx="${idx}" data-action="${item.action}" data-complete="${isComplete ? '1' : '0'}">
                <td style="text-align:center;">
                    <input type="checkbox" class="row-check" data-idx="${idx}"
                        ${isChecked ? 'checked' : ''} ${!canSelect ? 'disabled' : ''}>
                </td>
                <td style="color:#9ca3af;">${shown}</td>
                <td>${actionLabel}</td>
                <td>${namaCell}</td>
                <td style="white-space:nowrap;">${nisnCell}</td>
                <td class="col-hide-sm" style="white-space:nowrap;font-size:.72rem;color:#6b7280;">${escHtml(item.emis.nik || '-')}</td>
                <td class="col-hide-sm" style="white-space:nowrap;font-size:.72rem;">${escHtml(item.emis.tanggal_lahir || '-')}</td>
                <td style="text-align:center;">${item.emis.tingkat_emis || '-'}</td>
                <td style="font-size:.75rem;">${ayahCell}</td>
                <td style="font-size:.75rem;">${ibuCell}</td>
                <td>${keterangan}</td>
            </tr>`;

            $tbody.append(tr);
        });

        updateSelCount();
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Tabs ─────────────────────────────────────
    $(document).on('click', '.emis-tab', function () {
        currentFilter = $(this).data('filter');
        $('.emis-tab').removeClass('active');
        $(this).addClass('active');
        renderTable();
    });

    // ── Checkbox handling ────────────────────────
    $(document).on('change', '.row-check', function () {
        const idx = parseInt($(this).data('idx'));
        if (!isNaN(idx) && previewData[idx]) {
            previewData[idx].selected = $(this).is(':checked');
        }
        updateSelCount();
    });

    $('#btnCheckAll').on('click', function () {
        $('#previewTbody .row-check:not([disabled])').each(function () {
            $(this).prop('checked', true);
            const idx = parseInt($(this).data('idx'));
            if (!isNaN(idx) && previewData[idx]) previewData[idx].selected = true;
        });
        updateSelCount();
    });

    $('#btnUncheckAll').on('click', function () {
        $('#previewTbody .row-check:not([disabled])').each(function () {
            $(this).prop('checked', false);
            const idx = parseInt($(this).data('idx'));
            if (!isNaN(idx) && previewData[idx]) previewData[idx].selected = false;
        });
        updateSelCount();
    });

    // ── Toggle: sertakan / lewati data yang sudah lengkap ──
    $('#chkUpdateLengkap').on('change', function () {
        const include = $(this).is(':checked');
        previewData.forEach(function (item) {
            if (item.existing_complete) {
                item.selected = include;
            }
        });
        renderTable();
    });

    function updateSelCount() {
        const totalSelected = previewData.filter(r => r.selected).length;

        // Count visible checked
        const visibleChecked = $('#previewTbody .row-check:checked').length;
        const visibleTotal   = $('#previewTbody .row-check').length;

        $('#selCountNum').text(visibleChecked);
        $('#selCountTotal').text(visibleTotal);
        $('#btnSaveCount').text(totalSelected);
        $('#btnSave').prop('disabled', totalSelected === 0);
    }

    // ── Re-upload ─────────────────────────────────
    $('#btnReupload').on('click', resetToUpload);

    function resetToUpload() {
        previewData = [];
        $('#fileInput').val('');
        $('#dzFilename').hide();
        $('#btnParse').prop('disabled', true);
        $('#chkUpdateLengkap').prop('checked', false);
        $uploadSection.show();
        $previewSection.hide();
        $resultSection.hide();
    }

    // ── Save ─────────────────────────────────────
    $('#btnSave').on('click', function () {
        const selectedIndices = [];
        previewData.forEach(function (item, idx) {
            if (item.selected) selectedIndices.push(idx);
        });

        if (selectedIndices.length === 0) {
            toastr.warning('Tidak ada baris yang dipilih.');
            return;
        }

        if (!confirm('Simpan ' + selectedIndices.length + ' data siswa sekarang?')) return;

        // Show progress overlay with indeterminate animation
        $overlaySave.removeClass('d-none');
        $progressBar.css('width', '10%');
        animateProgress();

        $.ajax({
            url: '{{ route("admin.emis-import.execute") }}',
            method: 'POST',
            data: JSON.stringify({
                _token: '{{ csrf_token() }}',
                selected_indices: selectedIndices
            }),
            contentType: 'application/json',
            timeout: 300000, // 5 menit
            success: function (res) {
                $progressBar.css('width', '100%');
                setTimeout(function () {
                    $overlaySave.addClass('d-none');
                    showResult(res);
                }, 400);
            },
            error: function (xhr) {
                $overlaySave.addClass('d-none');
                $progressBar.css('width', '0%');
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.';
                toastr.error(msg);
            }
        });
    });

    // Fake progress animation (indeterminate style)
    let _progTimer = null;
    function animateProgress() {
        clearInterval(_progTimer);
        let w = 10;
        _progTimer = setInterval(function () {
            if (w < 85) {
                w += Math.random() * 3;
                $progressBar.css('width', w + '%');
            }
        }, 400);
    }

    // ── Show result ──────────────────────────────
    function showResult(res) {
        clearInterval(_progTimer);
        $('#rsDone').text(res.done || 0);
        $('#rsErr').text((res.errors || []).length);

        if (res.errors && res.errors.length > 0) {
            const $tbody = $('#errListBody').empty();
            res.errors.forEach(function (e) {
                $tbody.append(
                    '<tr><td>' + e.row + '</td><td>' + escHtml(e.nama) + '</td><td>' + escHtml(e.nisn || '-') + '</td><td>' + escHtml(e.error) + '</td></tr>'
                );
            });
            $('#errList').removeClass('d-none');
        } else {
            $('#errList').addClass('d-none');
        }

        $previewSection.hide();
        $resultSection.show();

        if (res.done > 0) {
            toastr.success(res.done + ' data berhasil disimpan!');
        }
    }

    // ── Import lagi ──────────────────────────────
    $('#btnImportLagi').on('click', function () {
        $resultSection.hide();
        resetToUpload();
    });

}(jQuery));
</script>
@endsection
