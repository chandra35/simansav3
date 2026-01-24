@extends('adminlte::page')

@section('title', 'Import NPSN Siswa - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-school"></i> Import NPSN Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Siswa
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
<!-- Info and Stats Row -->
<div class="row">
    <div class="col-md-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-table"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kolom Wajib</span>
                <span class="info-box-number">2 Kolom</span>
                <small>NISN dan NPSN</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-file-excel"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Format File</span>
                <span class="info-box-number">Excel</span>
                <small>.xlsx atau .xls (Maksimal 2MB)</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Left Column: Info & Template -->
    <div class="col-md-6">
        <!-- Info Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Panduan Import NPSN Siswa
                </h3>
            </div>
            <div class="card-body">
                <ol class="pl-3">
                    <li class="mb-2"><strong>Download template Excel</strong> dengan klik tombol di bawah</li>
                    <li class="mb-2"><strong>Isi NISN dan NPSN</strong> siswa (Nama opsional)</li>
                    <li class="mb-2"><strong>Upload file Excel</strong> yang sudah diisi</li>
                    <li class="mb-2"><strong>Sistem akan update</strong> NPSN siswa berdasarkan NISN</li>
                </ol>

                <div class="alert alert-warning mt-3 mb-0">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    <ul class="mb-0 pl-3">
                        <li><strong>NISN (WAJIB)</strong>: Harus sudah ada di database siswa</li>
                        <li><strong>NPSN (WAJIB)</strong>: Harus 8 digit angka</li>
                        <li><strong>NPSN</strong> harus sudah ada di database sekolah</li>
                        <li><strong>Nama Lengkap (OPSIONAL)</strong>: Hanya untuk identifikasi, tidak akan diupdate</li>
                        <li>Data akan diupdate berdasarkan NISN yang ada</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Download Template Card -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-download"></i> Download Template Excel
                </h3>
            </div>
            <div class="card-body text-center">
                <p class="text-muted">Template Excel sudah dilengkapi dengan contoh data dan format yang benar.</p>
                <a href="{{ route('admin.siswa.import-npsn.template') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-download"></i> Download Template NPSN
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Upload Form -->
    <div class="col-md-6">
        <!-- Upload Card -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-upload"></i> Upload File Excel
                </h3>
            </div>
            <div class="card-body">
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">
                            Pilih File Excel <span class="text-danger">*</span>
                            <i class="fas fa-question-circle text-muted" 
                               data-toggle="tooltip" 
                               data-placement="top" 
                               title="Upload file Excel yang sudah diisi sesuai template. Pastikan format dan data sudah benar."></i>
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="file">
                                    <i class="fas fa-file-upload text-muted"></i> Pilih file Excel...
                                </label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Format: .xlsx atau .xls | Maksimal: 2MB
                        </small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnImport">
                            <i class="fas fa-upload"></i> Upload dan Import NPSN
                        </button>
                    </div>
                </form>

                <!-- Progress Section (Hidden by default) -->
                <div id="progressSection" style="display: none;" class="mt-4">
                    <div class="text-center mb-3">
                        <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <h5 class="text-center mb-3 font-weight-bold text-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Proses Update NPSN Berjalan
                    </h5>
                    <div class="progress mb-3" style="height: 35px; border-radius: 10px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <span style="font-weight: bold; font-size: 15px;">Memproses...</span>
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <p class="mb-1 font-weight-bold text-dark">Sedang mengupdate NPSN siswa...</p>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Mohon tunggu, jangan tutup halaman ini
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Row (Full Width) -->
<div class="row" id="resultRow" style="display: none;">
    <div class="col-12">
        <!-- Result Card -->
        <div id="resultCard" class="card">
            <div class="card-header" id="resultHeader">
                <h3 class="card-title" id="resultTitle">
                    <i class="fas fa-check-circle"></i> Hasil Import NPSN Siswa
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Berhasil Diupdate</span>
                                <span class="info-box-number" id="successCount">0</span>
                                <span class="info-box-text">siswa</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Gagal Diupdate</span>
                                <span class="info-box-number" id="failedCount">0</span>
                                <span class="info-box-text">siswa</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Data Diproses</span>
                                <span class="info-box-number" id="totalCount">0</span>
                                <span class="info-box-text">baris</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Details -->
                <div id="errorSection" style="display: none;">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Detail Data yang Gagal Diupdate</h5>
                        <p class="mb-0">Silakan perbaiki data berikut dan upload ulang file Excel.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="errorTable">
                            <thead class="bg-danger">
                                <tr>
                                    <th width="80" class="text-center">Baris</th>
                                    <th width="120">NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kendala / Error</th>
                                </tr>
                            </thead>
                            <tbody id="errorTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Warning Details -->
                <div id="warningSection" style="display: none;">
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-exclamation-circle"></i> Peringatan - NPSN Tersimpan, Data Sekolah Tidak Ditemukan</h5>
                        <p class="mb-2">NPSN telah disimpan ke data siswa, namun data sekolah tidak ditemukan di database lokal maupun API Kemendikbud.</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-warning">
                                    <tr>
                                        <th width="80" class="text-center">Baris</th>
                                        <th width="120">NISN</th>
                                        <th>Nama Lengkap</th>
                                        <th width="100">NPSN</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="warningTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="successSection" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Import Berhasil!</h5>
                        <p class="mb-0">Semua data NPSN berhasil diupdate ke database.</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="{{ route('admin.siswa.index') }}" class="btn btn-primary">
                        <i class="fas fa-users"></i> Lihat Data Siswa
                    </a>
                    <a href="{{ route('admin.sekolah-asal.index') }}" class="btn btn-info ml-2">
                        <i class="fas fa-school"></i> Lihat Data Sekolah Asal
                    </a>
                    <button type="button" class="btn btn-secondary ml-2" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Import Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .info-box-number {
        font-size: 2rem;
        font-weight: bold;
    }
    .custom-file-label::after {
        content: "Pilih";
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Update file name on file select
    $('#file').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html('<i class="fas fa-file-excel text-success"></i> ' + fileName);
    });
    
    // Handle form submission
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const file = $('#file')[0].files[0];
        
        if (!file) {
            Swal.fire('Perhatian!', 'Silakan pilih file Excel terlebih dahulu', 'warning');
            return;
        }
        
        // Show progress
        $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        $('#progressSection').slideDown();
        $('#resultRow').slideUp();
        
        // Send request
        $.ajax({
            url: '{{ route('admin.siswa.import-npsn.process') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showResult(response);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.data) {
                    showResult(response);
                } else {
                    Swal.fire('Error!', response?.message || 'Terjadi kesalahan saat import', 'error');
                    resetForm();
                }
            }
        });
    });
});

function showResult(response) {
    // Hide progress
    $('#progressSection').slideUp();
    $('#btnImport').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload dan Import NPSN');
    
    // Show result
    $('#resultRow').slideDown();
    
    // Update counts
    $('#successCount').text(response.data.success_count);
    $('#failedCount').text(response.data.failed_count);
    $('#totalCount').text(response.data.total);
    
    // Update header
    if (response.success) {
        $('#resultCard').removeClass('card-danger').addClass('card-success');
        $('#resultHeader').removeClass('bg-danger').addClass('bg-success');
        $('#resultTitle').html('<i class="fas fa-check-circle"></i> Import Berhasil!');
    } else {
        $('#resultCard').removeClass('card-success').addClass('card-danger');
        $('#resultHeader').removeClass('bg-success').addClass('bg-danger');
        $('#resultTitle').html('<i class="fas fa-times-circle"></i> Import Selesai dengan Error');
    }
    
    // Show errors if any
    if (response.data.errors && response.data.errors.length > 0) {
        $('#errorSection').show();
        $('#successSection').hide();
        
        let errorHtml = '';
        response.data.errors.forEach(function(error) {
            errorHtml += `
                <tr>
                    <td class="text-center">${error.row}</td>
                    <td>${error.nisn}</td>
                    <td>${error.nama}</td>
                    <td class="text-danger">${error.error}</td>
                </tr>
            `;
        });
        $('#errorTableBody').html(errorHtml);
    } else {
        $('#errorSection').hide();
        $('#successSection').show();
    }

    // Show warnings if any
    if (response.data.warnings && response.data.warnings.length > 0) {
        $('#warningSection').show();
        
        let warningHtml = '';
        response.data.warnings.forEach(function(warning) {
            warningHtml += `
                <tr>
                    <td class="text-center">${warning.row}</td>
                    <td>${warning.nisn}</td>
                    <td>${warning.nama}</td>
                    <td class="text-center"><strong>${warning.npsn}</strong></td>
                    <td class="text-warning"><i class="fas fa-info-circle"></i> ${warning.message}</td>
                </tr>
            `;
        });
        $('#warningTableBody').html(warningHtml);
    } else {
        $('#warningSection').hide();
    }
    
    // Show notification
    if (response.success && response.data.failed_count === 0 && (!response.data.warnings || response.data.warnings.length === 0)) {
        Swal.fire('Berhasil!', `${response.data.success_count} NPSN siswa berhasil diupdate`, 'success');
    } else if (response.data.warnings && response.data.warnings.length > 0 && response.data.failed_count === 0) {
        Swal.fire('Perhatian!', `${response.data.success_count} NPSN berhasil diupdate, ${response.data.warnings.length} dengan peringatan (data sekolah tidak ditemukan)`, 'warning');
    } else if (response.data.success_count > 0) {
        Swal.fire('Perhatian!', `${response.data.success_count} berhasil, ${response.data.failed_count} gagal`, 'warning');
    } else {
        Swal.fire('Gagal!', 'Tidak ada data yang berhasil diupdate', 'error');
    }
}

function resetForm() {
    $('#progressSection').slideUp();
    $('#btnImport').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload dan Import NPSN');
}
</script>
@stop

@section('plugins.Sweetalert2', true)
