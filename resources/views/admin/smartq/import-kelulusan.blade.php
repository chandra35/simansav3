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
    <div class="row">
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-secondary"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Baris Dibaca</span>
                    <span class="info-box-number" id="previewTotal">0</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm" id="boxValid">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Data Valid (Siap Simpan)</span>
                    <span class="info-box-number" id="countValid">0</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm" id="boxInvalid">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Data Bermasalah</span>
                    <span class="info-box-number" id="countInvalid">0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Table --}}
    <div class="card card-outline card-warning shadow">
        <div class="card-header py-2">
            <div class="d-flex align-items-center flex-wrap" style="gap:10px">
                <h3 class="card-title mb-0 mr-2"><i class="fas fa-table mr-1"></i> Preview Data Import</h3>
                <div class="btn-group btn-group-sm" id="filterBtns">
                    <button type="button" class="btn btn-secondary active" data-filter="all">
                        <i class="fas fa-list"></i> Semua <span class="badge badge-light ml-1" id="fcAll">0</span>
                    </button>
                    <button type="button" class="btn btn-outline-success" data-filter="valid">
                        <i class="fas fa-check"></i> Valid <span class="badge badge-success ml-1" id="fcValid">0</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-filter="invalid">
                        <i class="fas fa-times"></i> Bermasalah <span class="badge badge-danger ml-1" id="fcInvalid">0</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body px-3 pt-3 pb-0">
            <table id="previewTable" class="table table-bordered table-hover mb-0" style="width:100%">
                <thead>
                    <tr class="bg-warning text-dark">
                        <th class="text-center" width="45">No</th>
                        <th width="260">Peserta</th>
                        <th class="text-center" width="80">P. Mapel</th>
                        <th class="text-center" width="80">P. Umum</th>
                        <th width="160">Bidang Mapel</th>
                        <th width="120">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="previewTableBody"></tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 pb-2"></div>
    </div>

    {{-- Action Buttons --}}
    <div class="card shadow-sm">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-secondary btn-block" id="btnBatal">
                        <i class="fas fa-undo"></i> Batal & Upload Ulang
                    </button>
                </div>
                <div class="col-md-5 offset-md-3">
                    <button type="button" class="btn btn-success btn-lg btn-block" id="btnConfirm" disabled>
                        <i class="fas fa-save"></i> Konfirmasi & Simpan
                        <span class="badge badge-light font-weight-bold" id="confirmCount">0</span> data valid
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FINAL RESULT SECTION --}}
<div id="finalResultSection" style="display: none;">
    <div class="row">
        <div class="col-md-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Berhasil Disimpan</span>
                    <span class="info-box-number" id="countSuccess">0</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Gagal Disimpan</span>
                    <span class="info-box-number" id="countError">0</span>
                </div>
            </div>
        </div>
    </div>

    <div id="successDetail" style="display: none;">
        <div class="card card-success card-outline shadow">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Data Berhasil Disimpan</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Nama Peserta</th>
                            <th width="120">NISN</th>
                            <th class="text-center" width="90">P. Mapel</th>
                            <th class="text-center" width="90">P. Umum</th>
                            <th width="140">Bidang Mapel</th>
                            <th width="110">Status</th>
                        </tr>
                    </thead>
                    <tbody id="successTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="errorDetail" style="display: none;">
        <div class="card card-danger card-outline shadow">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Data Gagal</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Nama Peserta</th>
                            <th width="120">NISN</th>
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
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .custom-file-label { border: 2px dashed #ced4da; border-radius: 6px; transition: border-color .2s; }
    .custom-file-label:hover { border-color: #ffc107; }
    .custom-file-label::after { content: "Pilih File"; }
    #previewSection, #finalResultSection { animation: fadeSlideIn .4s ease; }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    /* Preview table row colors */
    #previewTable tbody tr.row-valid td { background-color: #f0fff4 !important; }
    #previewTable tbody tr.row-invalid td { background-color: #fff5f5 !important; }
    #previewTable tbody tr.row-invalid td:first-child { border-left: 3px solid #dc3545 !important; }
    #previewTable tbody tr.row-valid td:first-child { border-left: 3px solid #28a745 !important; }
    /* Peserta cell styling */
    .peserta-cell .peserta-nama { font-weight: 600; font-size: 0.9rem; color: #343a40; }
    .peserta-cell .peserta-nisn { font-size: 0.78rem; color: #6c757d; font-family: monospace; }
    .peserta-cell .match-status { font-size: 0.75rem; margin-top: 2px; }
    /* match icons */
    .match-ok { color: #28a745; }
    .match-fail { color: #dc3545; }
    .match-icon { font-size: 0.9rem; }
    /* info-box adjustment */
    .info-box { min-height: 60px; }
    .info-box-icon { width: 70px; font-size: 1.5rem; }
    .info-box-number { font-size: 1.8rem; }
    /* filter buttons */
    #filterBtns .btn { transition: all .15s; }
    #filterBtns .btn .badge { font-size: 0.75rem; min-width: 20px; }
    /* filter buttons active */
    #filterBtns .btn.active { font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,.2); }
    /* DataTables controls area */
    #previewTable_wrapper { padding: 0; }
    #previewTable_wrapper .row { padding: 0 1px; }
    #previewTable_wrapper .dataTables_length,
    #previewTable_wrapper .dataTables_filter { font-size: 0.85rem; }
    #previewTable_wrapper .dataTables_info { font-size: 0.82rem; color: #6c757d; }
    #previewTable_wrapper .dataTables_paginate { font-size: 0.85rem; }
    #previewTable_wrapper .dataTables_paginate .paginate_button { padding: 2px 8px; }
    /* Confirmation bar */
    #previewSection > .card:last-child { border-top: 3px solid #28a745; }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
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
                    var namaMatch = r.nisn_match
                        ? (r.nama_match
                            ? '<span class="match-status text-success"><i class="fas fa-check-circle"></i> Nama cocok</span>'
                            : '<span class="match-status text-warning"><i class="fas fa-exclamation-circle"></i> Nama berbeda: <em>' + escHtml(r.nama_db) + '</em></span>')
                        : '<span class="match-status text-danger"><i class="fas fa-times-circle"></i> NISN tidak ditemukan</span>';
                    var pesertaCell = '<div class="peserta-cell">'
                        + '<div class="peserta-nama">' + escHtml(r.nama_file) + '</div>'
                        + '<div class="peserta-nisn"><i class="fas fa-id-card fa-xs text-muted mr-1"></i>' + escHtml(r.nisn) + '</div>'
                        + namaMatch
                        + '</div>';

                    // Mapel cell
                    var mapelCell = '';
                    if (r.mapel) {
                        mapelCell = r.mapel_match
                            ? '<span class="badge badge-info px-2 py-1">' + escHtml(r.mapel) + '</span>'
                            : '<span class="badge badge-secondary px-2 py-1">' + escHtml(r.mapel) + '</span> <small class="text-danger">tidak dikenal</small>';
                    } else {
                        mapelCell = '<span class="text-muted"><em>kosong</em></span>';
                    }

                    // Status cell
                    var statusBadge = '';
                    if (r.status) {
                        if (r.status_valid) {
                            statusBadge = r.status === 'diterima'
                                ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Diterima</span>'
                                : '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock"></i> Cadangan</span>';
                        } else {
                            statusBadge = '<span class="badge badge-secondary">' + escHtml(r.status) + '</span><br><small class="text-danger">tidak valid</small>';
                        }
                    } else {
                        statusBadge = '<span class="text-muted"><em>kosong</em></span>';
                    }

                    // Keterangan cell
                    var keterangan = r.valid
                        ? '<span class="text-success"><i class="fas fa-check-circle"></i> <strong>Siap disimpan</strong></span>'
                        : '<ul class="mb-0 pl-3">' + r.errors.map(function(e) { return '<li class="text-danger"><small>' + escHtml(e) + '</small></li>'; }).join('') + '</ul>';

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
                    responsive: true,
                    columnDefs: [
                        { orderable: false, targets: [1, 6] },
                        { className: 'text-center align-middle', targets: [0, 2, 3] },
                        { className: 'align-middle', targets: [1, 4, 5, 6] },
                    ],
                    dom: '<"row align-items-center mb-2"<"col-sm-5"l><"col-sm-7"f>>'
                        +'<"row"<"col-12"t>>'
                        +'<"row mt-2 mb-1"<"col-sm-6"i><"col-sm-6"p>>',
                    drawCallback: function() {
                        // Update info badges after draw
                        var info = this.api().page.info();
                        $('#filterBtns .btn[data-filter="all"] #fcAll').text(info.recordsTotal);
                    }
                });

                // Set filter badge counts
                var totalAll = d.rows.length;
                var totalValid = d.valid_count;
                var totalInvalid = d.error_count;
                $('#fcAll').text(totalAll);
                $('#fcValid').text(totalValid);
                $('#fcInvalid').text(totalInvalid);

                // If there are errors, auto-highlight invalid badge
                if (totalInvalid > 0) {
                    $('#fcInvalid').addClass('badge-danger').removeClass('badge-light');
                }
                if (totalValid > 0) {
                    $('#fcValid').addClass('badge-success').removeClass('badge-light');
                }

                // Filter buttons click
                $('#filterBtns .btn').off('click').on('click', function() {
                    $('#filterBtns .btn').removeClass('active btn-secondary btn-success btn-danger')
                        .addClass(function() {
                            var f = $(this).data('filter');
                            return f === 'valid' ? 'btn-outline-success'
                                : f === 'invalid' ? 'btn-outline-danger'
                                : 'btn-outline-secondary';
                        });
                    $(this).removeClass('btn-outline-secondary btn-outline-success btn-outline-danger')
                        .addClass('active')
                        .addClass(function() {
                            var f = $(this).data('filter');
                            return f === 'valid' ? 'btn-success'
                                : f === 'invalid' ? 'btn-danger'
                                : 'btn-secondary';
                        });
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

    // Filter buttons (global click handler removed - handled inside DataTable init)

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
                                ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Diterima</span>'
                                : '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock"></i> Cadangan</span>';
                            html += '<tr><td class="text-center">' + r.row + '</td><td><strong>' + escHtml(r.nama) + '</strong></td><td><code>' + escHtml(r.nisn) + '</code></td><td class="text-center">' + (r.peringkat_mapel || '-') + '</td><td class="text-center">' + (r.peringkat_umum || '-') + '</td><td><span class="badge badge-info">' + escHtml(r.mapel) + '</span></td><td>' + badge + '</td></tr>';
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
