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
                    <li class="mb-2"><strong>Isi kolom kuning:</strong> Peringkat Mapel, Peringkat Umum, dan Mapel</li>
                    <li class="mb-2"><strong>Upload file</strong> — sistem akan memproses dan menampilkan hasil</li>
                </ol>

                <div class="alert alert-warning mb-0">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Ketentuan</h5>
                    <ul class="mb-0 pl-3">
                        <li>Kolom <strong>NAMA</strong> & <strong>NISN</strong> sudah terisi otomatis (jangan diubah)</li>
                        <li><strong>MAPEL:</strong> pilih dari dropdown atau ketik nama mapel pilihan</li>
                        <li><strong>Peringkat Mapel:</strong> ranking dalam mapel tersebut</li>
                        <li><strong>Peringkat Umum:</strong> ranking keseluruhan</li>
                        <li>Baris tanpa MAPEL akan <strong>dilewati</strong> (hanya proses yang sudah diisi)</li>
                        <li>Semua peserta yang diimport akan berstatus <strong>Lulus/Diterima</strong></li>
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
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnImport">
                        <i class="fas fa-upload"></i> Upload & Proses Kelulusan
                    </button>
                </form>
            </div>
        </div>

        {{-- Result Section (hidden) --}}
        <div id="resultSection" style="display: none;">
            {{-- Summary boxes --}}
            <div class="row">
                <div class="col-6">
                    <div class="small-box bg-success" id="boxSuccess">
                        <div class="inner">
                            <h3 id="countSuccess">0</h3>
                            <p>Berhasil</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="small-box bg-danger" id="boxError">
                        <div class="inner">
                            <h3 id="countError">0</h3>
                            <p>Gagal</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            {{-- Success rows --}}
            <div id="successDetail" style="display: none;">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Data Berhasil Diproses</h3>
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
                                </tr>
                            </thead>
                            <tbody id="successTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Error rows --}}
            <div id="errorDetail" style="display: none;">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Data Gagal Diproses</h3>
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

            {{-- Action buttons --}}
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
    #resultSection { animation: fadeSlideIn .5s ease; }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-15px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@stop

@section('js')
<script>
$(function() {
    // File input label
    $('.custom-file-input').on('change', function() {
        var name = $(this).val().split('\\').pop();
        var ext = name.split('.').pop().toLowerCase();
        var icon = ['xlsx','xls'].includes(ext) ? 'fa-file-excel text-success' : 'fa-file text-muted';
        $(this).siblings('.custom-file-label').html('<i class="fas ' + icon + '"></i> ' + name);
    });

    // Submit
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

        // Confirm
        Swal.fire({
            title: '<i class="fas fa-file-import text-warning"></i> Konfirmasi Import',
            html: '<p>File: <strong>' + file.name + '</strong></p>' +
                  '<p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Status peserta yang cocok akan diperbarui.</small></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            confirmButtonColor: '#e6a819',
            reverseButtons: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;

            // Show overlay
            showSmartqOverlay('Memproses import kelulusan...', 'Mohon tunggu, jangan tutup halaman ini', 'file-import');
            var msgInterval = smartqOverlayMessages([
                'Memproses import kelulusan...',
                'Membaca file...',
                'Mencocokkan NISN dengan peserta...',
                'Memvalidasi kode bidang...',
                'Mengupdate status kelulusan...',
                'Menyimpan data...',
                'Hampir selesai...',
            ], 2000);

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

                    var d = res.data;
                    $('#countSuccess').text(d.success_count);
                    $('#countError').text(d.failed_count);

                    // Success rows
                    if (d.success_count > 0) {
                        var html = '';
                        d.success_rows.forEach(function(r) {
                            html += '<tr><td>' + r.row + '</td><td>' + r.nama + '</td><td>' + r.nisn + '</td><td class="text-center">' + (r.peringkat_mapel || '-') + '</td><td class="text-center">' + (r.peringkat_umum || '-') + '</td><td><span class="badge badge-info">' + r.mapel + '</span></td></tr>';
                        });
                        $('#successTableBody').html(html);
                        $('#successDetail').show();
                    }

                    // Error rows
                    if (d.failed_count > 0) {
                        var html = '';
                        d.errors.forEach(function(r) {
                            html += '<tr><td>' + r.row + '</td><td>' + (r.nama || '-') + '</td><td>' + r.nisn + '</td><td class="text-danger">' + r.error + '</td></tr>';
                        });
                        $('#errorTableBody').html(html);
                        $('#errorDetail').show();
                    }

                    $('#importForm').closest('.card').slideUp(300);
                    $('#resultSection').show();

                    // Summary SweetAlert
                    if (d.failed_count === 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil!',
                            html: '<strong>' + d.success_count + '</strong> data kelulusan berhasil diproses.',
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Import Selesai (Sebagian Gagal)',
                            html: '<strong>' + d.success_count + '</strong> berhasil, <strong>' + d.failed_count + '</strong> gagal.',
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
    });
});
</script>
@stop
