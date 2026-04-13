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

<div class="row">
    {{-- Left: Info + Template --}}
    <div class="col-lg-6">
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
    <div class="col-lg-6">
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

        {{-- Preview Section (hidden) --}}
        <div id="previewSection" style="display: none;">
            <div class="row">
                <div class="col-6">
                    <div class="small-box bg-success" id="boxValid">
                        <div class="inner">
                            <h3 id="countValid">0</h3>
                            <p>Data Valid (Siap Simpan)</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="small-box bg-danger" id="boxInvalid">
                        <div class="inner">
                            <h3 id="countInvalid">0</h3>
                            <p>Data Bermasalah</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Preview Data Import</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary" id="previewTotal">0 baris</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="previewTable">
                            <thead>
                                <tr class="bg-warning">
                                    <th width="40" class="text-center">No</th>
                                    <th width="110">NISN</th>
                                    <th>Nama (File)</th>
                                    <th>Nama (Database)</th>
                                    <th width="70" class="text-center">P.Mapel</th>
                                    <th width="70" class="text-center">P.Umum</th>
                                    <th width="130">Mapel</th>
                                    <th width="90">Status</th>
                                    <th width="55" class="text-center">Valid</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-block" id="btnBatal">
                        <i class="fas fa-redo"></i> Batal & Upload Ulang
                    </button>
                </div>
                <div class="col-md-6 mb-2">
                    <button type="button" class="btn btn-success btn-lg btn-block" id="btnConfirm" disabled>
                        <i class="fas fa-save"></i> Konfirmasi & Simpan (<span id="confirmCount">0</span> data)
                    </button>
                </div>
            </div>
        </div>

        {{-- Final Result Section (hidden) --}}
        <div id="finalResultSection" style="display: none;">
            <div class="row">
                <div class="col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="countSuccess">0</h3>
                            <p>Berhasil Disimpan</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="countError">0</h3>
                            <p>Gagal</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div id="successDetail" style="display: none;">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Data Berhasil Disimpan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th width="50">Baris</th>
                                    <th>Nama</th>
                                    <th width="110">NISN</th>
                                    <th width="80">P. Mapel</th>
                                    <th width="80">P. Umum</th>
                                    <th width="120">Mapel</th>
                                    <th width="90">Status</th>
                                </tr>
                            </thead>
                            <tbody id="successTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="errorDetail" style="display: none;">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Data Gagal</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-danger text-white">
                                <tr>
                                    <th width="50">Baris</th>
                                    <th>Nama</th>
                                    <th width="110">NISN</th>
                                    <th>Kendala</th>
                                </tr>
                            </thead>
                            <tbody id="errorTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary mr-2" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Import Ulang
                </button>
                <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-success">
                    <i class="fas fa-eye"></i> Lihat Hasil di Periode
                </a>
            </div>
        </div>
    </div>
</div>

@include('admin.smartq._overlay')
@stop

@section('css')
<style>
    .custom-file-label { border: 2px dashed #ced4da; border-radius: 6px; transition: border-color .2s; }
    .custom-file-label:hover { border-color: #007bff; }
    .custom-file-label::after { content: "Browse"; }
    .small-box { border-radius: 8px; box-shadow: 0 3px 6px rgba(0,0,0,.12); }
    #previewSection, #finalResultSection { animation: fadeSlideIn .5s ease; }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-15px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    #previewTable tbody tr.row-valid { background-color: #d4edda !important; }
    #previewTable tbody tr.row-invalid { background-color: #f8d7da !important; }
    .match-icon { font-size: 1rem; }
    .match-ok { color: #28a745; }
    .match-fail { color: #dc3545; }
</style>
@stop

@section('js')
<script>
$(function() {
    var tempPath = null;

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
                $('#previewTotal').text(d.total + ' baris');

                // Build preview table
                var html = '';
                d.rows.forEach(function(r) {
                    var cls = r.valid ? 'row-valid' : 'row-invalid';
                    var statusBadge = '';
                    if (r.status) {
                        statusBadge = r.status_valid
                            ? '<span class="badge badge-' + (r.status === 'diterima' ? 'success' : 'warning') + '">' + escHtml(r.status) + '</span> ' + matchIcon(true)
                            : '<span class="badge badge-secondary">' + escHtml(r.status) + '</span> ' + matchIcon(false);
                    } else {
                        statusBadge = '<span class="text-muted">-</span>';
                    }
                    var mapelCell = r.mapel_match
                        ? '<span class="badge badge-info">' + escHtml(r.mapel) + '</span> ' + matchIcon(true)
                        : '<span class="badge badge-secondary">' + escHtml(r.mapel) + '</span> ' + matchIcon(false);
                    var namaMatchIcon = r.nisn_match ? ' ' + matchIcon(r.nama_match) : '';
                    var errors = r.errors.length ? '<small class="text-danger">' + r.errors.map(escHtml).join('<br>') + '</small>' : '<span class="text-success"><i class="fas fa-check"></i> OK</span>';

                    html += '<tr class="' + cls + '">'
                        + '<td class="text-center">' + r.row + '</td>'
                        + '<td>' + escHtml(r.nisn) + ' ' + matchIcon(r.nisn_match) + '</td>'
                        + '<td>' + escHtml(r.nama_file) + '</td>'
                        + '<td>' + escHtml(r.nama_db) + namaMatchIcon + '</td>'
                        + '<td class="text-center">' + (r.peringkat_mapel || '-') + '</td>'
                        + '<td class="text-center">' + (r.peringkat_umum || '-') + '</td>'
                        + '<td>' + mapelCell + '</td>'
                        + '<td>' + statusBadge + '</td>'
                        + '<td class="text-center">' + matchIcon(r.valid) + '</td>'
                        + '<td>' + errors + '</td>'
                        + '</tr>';
                });
                $('#previewTableBody').html(html);

                // Enable confirm button if there are valid rows
                if (d.valid_count > 0) {
                    $('#btnConfirm').prop('disabled', false);
                    $('#confirmCount').text(d.valid_count);
                } else {
                    $('#btnConfirm').prop('disabled', true);
                    $('#confirmCount').text('0');
                }

                $('#importForm').closest('.card').slideUp(300);
                $('#previewSection').show();

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
                                ? '<span class="badge badge-success">Diterima</span>'
                                : '<span class="badge badge-warning">Cadangan</span>';
                            html += '<tr><td>' + r.row + '</td><td>' + escHtml(r.nama) + '</td><td>' + escHtml(r.nisn) + '</td><td class="text-center">' + (r.peringkat_mapel || '-') + '</td><td class="text-center">' + (r.peringkat_umum || '-') + '</td><td><span class="badge badge-info">' + escHtml(r.mapel) + '</span></td><td>' + badge + '</td></tr>';
                        });
                        $('#successTableBody').html(html);
                        $('#successDetail').show();
                    }

                    if (d.failed_count > 0) {
                        var html = '';
                        d.errors.forEach(function(r) {
                            html += '<tr><td>' + r.row + '</td><td>' + escHtml(r.nama || '-') + '</td><td>' + escHtml(r.nisn) + '</td><td class="text-danger">' + escHtml(r.error) + '</td></tr>';
                        });
                        $('#errorTableBody').html(html);
                        $('#errorDetail').show();
                    }

                    $('#previewSection').slideUp(300);
                    $('#finalResultSection').show();

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
