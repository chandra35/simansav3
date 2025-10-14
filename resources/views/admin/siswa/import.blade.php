@extends('adminlte::page')

@section('title', 'Import Data Siswa - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-excel"></i> Import Data Siswa</h1>
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
                <span class="info-box-number">4 Kolom</span>
                <small>NISN, NIK, Nama Lengkap, Jenis Kelamin</small>
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
                    <i class="fas fa-info-circle"></i> Panduan Import Data Siswa
                </h3>
            </div>
            <div class="card-body">
                <ol class="pl-3">
                    <li class="mb-2"><strong>Download template Excel</strong> dengan klik tombol di bawah</li>
                    <li class="mb-2"><strong>Isi data siswa</strong> sesuai format yang disediakan</li>
                    <li class="mb-2"><strong>Upload file Excel</strong> yang sudah diisi</li>
                    <li class="mb-2"><strong>Sistem akan memvalidasi</strong> dan mengimport data</li>
                </ol>

                <div class="alert alert-warning mt-3 mb-0">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    <ul class="mb-0 pl-3">
                        <li><strong>NISN</strong> harus 10 digit angka dan unik (digunakan sebagai username dan password)</li>
                        <li><strong>NIK</strong> harus 16 digit angka dan unik</li>
                        <li><strong>Jenis Kelamin:</strong> L/P atau Laki-laki/Perempuan</li>
                        <li><strong>Password default:</strong> sama dengan NISN</li>
                        <li><strong>Data orang tua:</strong> siswa akan melengkapi sendiri setelah login</li>
                        <li>Semua kolom wajib diisi</li>
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
                <a href="{{ route('admin.siswa.import.template') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-download"></i> Download Template
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
                        <label for="file">Pilih File Excel <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Format: .xlsx atau .xls | Maksimal: 2MB
                        </small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnImport">
                            <i class="fas fa-upload"></i> Upload dan Import Data
                        </button>
                    </div>
                </form>

                <!-- Progress Section (Hidden by default) -->
                <div id="progressSection" style="display: none;" class="mt-3">
                    <hr>
                    <h5 class="text-center mb-3">
                        <i class="fas fa-spinner fa-spin text-primary"></i> Proses Import
                    </h5>
                    <div class="progress mb-3" style="height: 30px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span id="progressText" style="font-weight: bold;">0%</span>
                        </div>
                    </div>
                    <p class="text-center text-muted mb-0" id="progressMessage">Memproses data...</p>
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
                    <i class="fas fa-check-circle"></i> Hasil Import Data Siswa
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
                                <span class="info-box-text">Data Berhasil Diimport</span>
                                <span class="info-box-number" id="successCount">0</span>
                                <span class="info-box-text">siswa</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Data Gagal Diimport</span>
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
                                <span class="info-box-text">siswa</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Details -->
                <div id="errorSection" style="display: none;">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Detail Data yang Gagal Diimport</h5>
                        <p class="mb-0">Silakan perbaiki data berikut dan upload ulang file Excel.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
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

                <!-- Success Message -->
                <div id="successSection" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check-circle"></i> Import Berhasil!</h5>
                        <p class="mb-0">
                            Semua data siswa berhasil diimport ke sistem.<br>
                            <strong>Username & Password:</strong> NISN masing-masing siswa<br>
                            <strong>Catatan:</strong> Siswa akan melengkapi data orang tua sendiri setelah login pertama kali.
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary btn-lg" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Import Data Lagi
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.siswa.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-users"></i> Lihat Data Siswa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@stop

@section('css')
<style>
    .info-box {
        min-height: 90px;
    }
    
    .custom-file-label::after {
        content: "Browse";
    }
    
    #progressSection {
        animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    #resultRow {
        animation: slideIn 0.5s;
    }
    
    #resultCard {
        animation: slideIn 0.5s;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .info-box {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 5px;
    }
    
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .progress {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    #progressBar {
        font-size: 14px;
        font-weight: bold;
        line-height: 30px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Handle form submit
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate file
        let fileInput = $('#file')[0];
        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'error',
                title: 'File Tidak Dipilih',
                text: 'Silakan pilih file Excel terlebih dahulu'
            });
            return;
        }

        let file = fileInput.files[0];
        let fileSize = file.size / 1024 / 1024; // in MB
        let fileExt = file.name.split('.').pop().toLowerCase();

        if (!['xlsx', 'xls'].includes(fileExt)) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Salah',
                text: 'File harus berformat Excel (.xlsx atau .xls)'
            });
            return;
        }

        if (fileSize > 2) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 2MB'
            });
            return;
        }

        // Show confirmation
        Swal.fire({
            title: 'Konfirmasi Import',
            html: `Anda akan mengimport data dari file:<br><strong>${file.name}</strong><br><br>
                   <small class="text-muted">Proses ini mungkin memakan waktu beberapa saat.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Import!',
            cancelButtonText: '<i class="fas fa-times"></i> Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                processImport();
            }
        });
    });

    function processImport() {
        // Hide form, show progress
        $('#importForm').hide();
        $('#progressSection').show();
        $('#resultCard').hide();
        
        // Animate progress bar
        let progress = 0;
        let progressInterval = setInterval(function() {
            progress += 5;
            if (progress <= 90) {
                updateProgress(progress, 'Memvalidasi dan memproses data...');
            }
        }, 200);

        // Prepare form data
        let formData = new FormData($('#importForm')[0]);

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.siswa.import.process") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        let percentComplete = (evt.loaded / evt.total) * 100;
                        updateProgress(Math.min(percentComplete, 90), 'Mengupload file...');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                clearInterval(progressInterval);
                updateProgress(100, 'Selesai!');
                
                setTimeout(function() {
                    $('#progressSection').hide();
                    showResult(response);
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                $('#progressSection').hide();
                $('#importForm').show();
                
                let message = 'Terjadi kesalahan saat import';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.data) {
                    // Show validation errors
                    showResult(xhr.responseJSON);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        text: message
                    });
                }
            }
        });
    }

    function updateProgress(percent, message) {
        percent = Math.round(percent);
        $('#progressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#progressText').text(percent + '%');
        $('#progressMessage').html('<strong>' + message + '</strong>');
    }

    function showResult(response) {
        // Show result row and card
        $('#resultRow').show();
        $('#resultCard').show();
        
        let data = response.data;
        let successCount = data.success_count || 0;
        let failedCount = data.failed_count || 0;
        let totalCount = data.total || 0;
        
        // Update counts
        $('#successCount').text(successCount);
        $('#failedCount').text(failedCount);
        $('#totalCount').text(totalCount);
        
        // Update header
        if (failedCount === 0) {
            $('#resultCard').removeClass().addClass('card card-success');
            $('#resultHeader').removeClass().addClass('card-header bg-success');
            $('#resultTitle').html('<i class="fas fa-check-circle"></i> Import Berhasil!');
            $('#successSection').show();
            $('#errorSection').hide();
        } else {
            $('#resultCard').removeClass().addClass('card card-warning');
            $('#resultHeader').removeClass().addClass('card-header bg-warning');
            $('#resultTitle').html('<i class="fas fa-exclamation-triangle"></i> Import Selesai dengan Peringatan');
            $('#successSection').hide();
            $('#errorSection').show();
            
            // Show error details
            let errorTableBody = $('#errorTableBody');
            errorTableBody.empty();
            
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(function(error) {
                    let row = `
                        <tr>
                            <td><span class="badge badge-danger">${error.row}</span></td>
                            <td>${error.nisn || '-'}</td>
                            <td>${error.nama || '-'}</td>
                            <td class="text-danger"><small>${error.error}</small></td>
                        </tr>
                    `;
                    errorTableBody.append(row);
                });
            }
        }
        
        // Scroll to result
        $('html, body').animate({
            scrollTop: $('#resultCard').offset().top - 100
        }, 500);
    }
});
</script>
@stop
