@extends('adminlte::page')

@section('title', 'Import Data GTK - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-excel"></i> Import Data GTK</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data GTK
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
                <span class="info-box-number">3 Kolom</span>
                <small>Nama Lengkap, NIK, Jenis Kelamin</small>
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
                    <i class="fas fa-info-circle"></i> Panduan Import Data GTK
                </h3>
            </div>
            <div class="card-body">
                <ol class="pl-3">
                    <li class="mb-2"><strong>Download template Excel</strong> dengan klik tombol di bawah</li>
                    <li class="mb-2"><strong>Isi data GTK</strong> sesuai format yang disediakan</li>
                    <li class="mb-2"><strong>Upload file Excel</strong> yang sudah diisi</li>
                    <li class="mb-2"><strong>Sistem akan memvalidasi</strong> dan mengimport data</li>
                </ol>

                <div class="alert alert-warning mt-3 mb-0">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    <ul class="mb-0 pl-3">
                        <li><strong>NIK</strong> harus 16 digit angka dan unik (digunakan sebagai username dan password)</li>
                        <li><strong>NUPTK</strong> harus 16 digit angka (jika diisi)</li>
                        <li><strong>NIP</strong> maksimal 18 digit (jika diisi)</li>
                        <li><strong>Jenis Kelamin:</strong> L/P atau Laki-laki/Perempuan</li>
                        <li><strong>Tanggal Lahir:</strong> format YYYY-MM-DD (contoh: 1982-05-29)</li>
                        <li><strong>Password default:</strong> sama dengan NIK</li>
                        <li><strong>Username:</strong> otomatis dari NIK</li>
                        <li>Kolom wajib: Nama Lengkap, NIK, Jenis Kelamin</li>
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
                <a href="{{ route('admin.gtk.import.template') }}" class="btn btn-success btn-lg">
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
                            <i class="fas fa-info-circle"></i> Format: .xlsx atau .xls | Maksimal: 2MB | 
                            <kbd>Ctrl+U</kbd> untuk shortcut
                        </small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnImport">
                            <i class="fas fa-upload"></i> Upload dan Import Data
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
                        <i class="fas fa-cloud-upload-alt"></i> Proses Import Berjalan
                    </h5>
                    <div class="progress mb-3" style="height: 35px; border-radius: 10px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span id="progressText" style="font-weight: bold; font-size: 15px;">0%</span>
                        </div>
                    </div>
                    <div class="text-center mb-3" id="progressMessage">
                        <p class="mb-1 font-weight-bold text-dark">Memproses data...</p>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Mohon tunggu, jangan tutup halaman ini
                        </small>
                    </div>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-lightbulb"></i> <strong>Tips:</strong> 
                            Proses import mungkin memakan waktu beberapa saat tergantung jumlah data.
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
                    <i class="fas fa-check-circle"></i> Hasil Import Data GTK
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
                                <span class="info-box-text">GTK</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Data Gagal Diimport</span>
                                <span class="info-box-number" id="failedCount">0</span>
                                <span class="info-box-text">GTK</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Data Diproses</span>
                                <span class="info-box-number" id="totalCount">0</span>
                                <span class="info-box-text">GTK</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Details -->
                <div id="errorSection" style="display: none;">
                    <div class="alert alert-danger">
                        <div class="row">
                            <div class="col-md-8">
                                <h5><i class="fas fa-exclamation-triangle"></i> Detail Data yang Gagal Diimport</h5>
                                <p class="mb-0">Silakan perbaiki data berikut dan upload ulang file Excel.</p>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportErrors()">
                                    <i class="fas fa-file-excel"></i> Export Error ke Excel
                                </button>
                            </div>
                        </div>
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

                <!-- Success Message -->
                <div id="successSection" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check-circle"></i> Import Berhasil!</h5>
                        <p class="mb-0">
                            Semua data GTK berhasil diimport ke sistem.<br>
                            <strong>Username & Password:</strong> NIK masing-masing GTK<br>
                            <strong>Catatan:</strong> GTK dapat melengkapi data kepegawaian setelah login pertama kali.
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
                        <a href="{{ route('admin.gtk.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-chalkboard-teacher"></i> Lihat Data GTK
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
    
    /* Animation untuk progress section */
    #progressSection {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { 
            opacity: 0;
            transform: translateY(-10px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Animation untuk result section */
    #resultRow {
        animation: slideIn 0.6s ease-out;
    }
    
    #resultCard {
        animation: slideIn 0.6s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Box shadows untuk depth */
    .info-box {
        box-shadow: 0 3px 6px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08);
        border-radius: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.1);
    }
    
    .card {
        box-shadow: 0 3px 6px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08);
        border-radius: 8px;
        border: none;
    }
    
    .card:hover {
        box-shadow: 0 6px 12px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.1);
    }
    
    /* Progress bar styling */
    .progress {
        box-shadow: 0 2px 5px rgba(0,0,0,0.12);
        border-radius: 10px;
        height: 35px;
        background-color: #e9ecef;
    }
    
    #progressBar {
        font-size: 15px;
        font-weight: bold;
        line-height: 35px;
        border-radius: 10px;
        transition: width 0.3s ease;
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
    }
    
    /* Spinner animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    /* Button hover effects */
    .btn {
        transition: all 0.3s ease;
        border-radius: 6px;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .btn:active {
        transform: translateY(0);
    }
    
    /* Table styling */
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.03);
        transition: background-color 0.2s ease;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    
    /* Custom file input */
    .custom-file-label {
        border-radius: 6px;
        border: 2px dashed #ced4da;
        transition: border-color 0.2s ease;
    }
    
    .custom-file-label:hover {
        border-color: #007bff;
    }
    
    .custom-file-input:focus ~ .custom-file-label {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
    }
    
    /* Badge styling */
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 600;
    }
    
    /* Success/Error count animation */
    .info-box-number {
        animation: countUp 0.8s ease-out;
    }
    
    @keyframes countUp {
        from {
            opacity: 0;
            transform: scale(0.5);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* Pulse animation for success */
    .pulse-success {
        animation: pulse-success 1.5s infinite;
    }
    
    @keyframes pulse-success {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    /* Shake animation for error */
    .shake-error {
        animation: shake 0.5s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Custom file input label with icon
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        let fileSize = this.files[0].size / 1024 / 1024; // MB
        let fileExt = fileName.split('.').pop().toLowerCase();
        
        // Icon based on file type
        let icon = fileExt === 'xlsx' || fileExt === 'xls' ? 
                   '<i class="fas fa-file-excel text-success"></i>' : 
                   '<i class="fas fa-file text-muted"></i>';
        
        let label = `${icon} ${fileName} <small class="text-muted">(${fileSize.toFixed(2)} MB)</small>`;
        $(this).siblings('.custom-file-label').addClass('selected').html(label);
        
        // Visual feedback
        $(this).siblings('.custom-file-label').css('border-color', '#28a745');
        setTimeout(function() {
            $('.custom-file-label').css('border-color', '');
        }, 1000);
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

        // Show confirmation with file info
        Swal.fire({
            title: '<strong><i class="fas fa-cloud-upload-alt"></i> Konfirmasi Import</strong>',
            html: `
                <div class="text-left" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <div class="mb-2">
                        <strong><i class="fas fa-file-excel text-success"></i> File:</strong><br>
                        <span class="text-muted">${file.name}</span>
                    </div>
                    <div class="mb-2">
                        <strong><i class="fas fa-hdd text-info"></i> Ukuran:</strong><br>
                        <span class="text-muted">${(fileSize).toFixed(2)} MB</span>
                    </div>
                    <div>
                        <strong><i class="fas fa-info-circle text-primary"></i> Catatan:</strong><br>
                        <small class="text-muted">
                            • NISN akan digunakan sebagai username & password<br>
                            • Data duplikat akan ditolak sistem<br>
                            • Proses mungkin memakan waktu beberapa saat
                        </small>
                    </div>
                </div>
                <p class="mb-0 text-center"><strong>Lanjutkan proses import?</strong></p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check-circle"></i> Ya, Import Sekarang!',
            cancelButtonText: '<i class="fas fa-times-circle"></i> Batal',
            width: '600px',
            customClass: {
                confirmButton: 'btn btn-success btn-lg px-4',
                cancelButton: 'btn btn-secondary btn-lg px-4'
            },
            buttonsStyling: false,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mempersiapkan...',
                    html: '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 500
                });
                
                setTimeout(function() {
                    Swal.close();
                    processImport();
                }, 500);
            }
        });
    });

    function processImport() {
        // Hide form, show progress with fade effect
        $('#importForm').fadeOut(300, function() {
            $('#progressSection').fadeIn(400);
        });
        $('#resultRow').hide();
        
        // Animate progress bar with realistic stages
        let progress = 0;
        let stage = 1;
        let progressInterval = setInterval(function() {
            if (stage === 1 && progress < 30) {
                // Stage 1: Upload (fast)
                progress += 3;
                updateProgress(progress, '<i class="fas fa-cloud-upload-alt"></i> Mengupload file...');
            } else if (stage === 2 && progress < 60) {
                // Stage 2: Validation (medium)
                progress += 2;
                updateProgress(progress, '<i class="fas fa-tasks"></i> Memvalidasi data...');
            } else if (stage === 3 && progress < 90) {
                // Stage 3: Processing (slow)
                progress += 1;
                updateProgress(progress, '<i class="fas fa-cog fa-spin"></i> Memproses dan menyimpan data...');
            }
            
            if (progress >= 30 && stage === 1) stage = 2;
            if (progress >= 60 && stage === 2) stage = 3;
        }, 150);

        // Prepare form data
        let formData = new FormData($('#importForm')[0]);

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.gtk.import.process") }}',
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
                updateProgress(100, '<i class="fas fa-check-circle"></i> Selesai!');
                
                // Reset uploading flag - allow navigation
                uploading = false;
                
                // Play success sound (optional)
                // new Audio('/sounds/success.mp3').play();
                
                // Show success toast
                let successCount = response.data.success_count || 0;
                let failedCount = response.data.failed_count || 0;
                
                if (failedCount === 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Berhasil!',
                        text: `${successCount} data GTK berhasil diimport`,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                
                setTimeout(function() {
                    $('#progressSection').fadeOut(300, function() {
                        showResult(response);
                    });
                }, 800);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                
                // Reset uploading flag - allow navigation
                uploading = false;
                
                // Shake animation for error
                $('#progressSection').addClass('shake-error');
                setTimeout(function() {
                    $('#progressSection').removeClass('shake-error');
                }, 500);
                
                let message = 'Terjadi kesalahan saat import';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.data) {
                    // Show validation errors with result display
                    setTimeout(function() {
                        $('#progressSection').fadeOut(300, function() {
                            $('#importForm').fadeIn(300);
                            showResult(xhr.responseJSON);
                        });
                    }, 1000);
                } else {
                    // Show error alert
                    $('#progressSection').fadeOut(300, function() {
                        $('#importForm').fadeIn(300);
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        html: `<p class="mb-2">${message}</p><small class="text-muted">Silakan coba lagi atau hubungi administrator.</small>`,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }
        });
    }

    function updateProgress(percent, message) {
        percent = Math.round(percent);
        $('#progressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#progressText').text(percent + '%');
        
        // Change progress bar color based on progress
        $('#progressBar').removeClass('bg-info bg-success bg-warning bg-primary');
        if (percent < 30) {
            $('#progressBar').addClass('bg-info');
        } else if (percent < 60) {
            $('#progressBar').addClass('bg-primary');
        } else if (percent < 90) {
            $('#progressBar').addClass('bg-warning');
        } else {
            $('#progressBar').addClass('bg-success');
        }
        
        // Update message with better formatting
        $('#progressMessage').html(`
            <p class="mb-1 font-weight-bold text-dark">${message}</p>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Mohon tunggu, jangan tutup halaman ini
            </small>
        `);
    }

    function showResult(response) {
        let data = response.data;
        let successCount = data.success_count || 0;
        let failedCount = data.failed_count || 0;
        let totalCount = data.total || 0;
        
        // Reset button state
        $('#btnImport').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload dan Import Data');
        
        // Animate count up effect
        animateValue('successCount', 0, successCount, 800);
        animateValue('failedCount', 0, failedCount, 800);
        animateValue('totalCount', 0, totalCount, 800);
        
        // Update header with animation
        if (failedCount === 0) {
            $('#resultCard').removeClass().addClass('card card-success pulse-success');
            $('#resultHeader').removeClass().addClass('card-header bg-success');
            $('#resultTitle').html('<i class="fas fa-check-circle"></i> Import Berhasil!');
            $('#successSection').show();
            $('#errorSection').hide();
            
            // Remove pulse after animation
            setTimeout(function() {
                $('#resultCard').removeClass('pulse-success');
            }, 3000);
            
            // Confetti effect (optional - requires canvas-confetti library)
            // if (typeof confetti !== 'undefined') {
            //     confetti({
            //         particleCount: 100,
            //         spread: 70,
            //         origin: { y: 0.6 }
            //     });
            // }
        } else if (successCount === 0) {
            // All failed
            $('#resultCard').removeClass().addClass('card card-danger shake-error');
            $('#resultHeader').removeClass().addClass('card-header bg-danger');
            $('#resultTitle').html('<i class="fas fa-times-circle"></i> Import Gagal!');
            $('#successSection').hide();
            $('#errorSection').show();
            
            setTimeout(function() {
                $('#resultCard').removeClass('shake-error');
            }, 500);
        } else {
            // Partial success
            $('#resultCard').removeClass().addClass('card card-warning');
            $('#resultHeader').removeClass().addClass('card-header bg-warning');
            $('#resultTitle').html('<i class="fas fa-exclamation-triangle"></i> Import Selesai dengan Peringatan');
            $('#successSection').hide();
            $('#errorSection').show();
        }
        
        // Show error details with fade-in animation
        if (data.errors && data.errors.length > 0) {
            let errorTableBody = $('#errorTableBody');
            errorTableBody.empty();
            
            data.errors.forEach(function(error, index) {
                let row = $(`
                    <tr style="opacity: 0;">
                        <td class="text-center"><span class="badge badge-danger">${error.row}</span></td>
                        <td>${error.nisn || '-'}</td>
                        <td>${error.nama || '-'}</td>
                        <td class="text-danger"><small><i class="fas fa-exclamation-circle"></i> ${error.error}</small></td>
                    </tr>
                `);
                errorTableBody.append(row);
                
                // Fade in each row with delay
                setTimeout(function() {
                    row.animate({ opacity: 1 }, 300);
                }, index * 50);
            });
        }
        
        // Show result with fade in
        $('#resultRow').fadeIn(500);
        
        // Smooth scroll to result
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $('#resultCard').offset().top - 100
            }, 800, 'swing');
        }, 200);
    }
    
    // Animate counter function
    function animateValue(id, start, end, duration) {
        let obj = document.getElementById(id);
        if (!obj) return;
        
        // Jika start = end, langsung set nilai
        if (start === end) {
            obj.textContent = end;
            return;
        }
        
        let range = Math.abs(end - start);
        let current = start;
        let increment = end > start ? 1 : -1;
        
        // Pastikan stepTime tidak 0 atau negatif
        let stepTime = Math.max(Math.floor(duration / range), 1);
        
        let timer = setInterval(function() {
            current += increment;
            obj.textContent = current;
            
            // Stop condition
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                obj.textContent = end; // Pastikan nilai akhir tepat
                clearInterval(timer);
            }
        }, stepTime);
    }
    
    // Export errors to Excel
    window.exportErrors = function() {
        let errorData = [];
        $('#errorTableBody tr').each(function() {
            let row = $(this).find('td');
            errorData.push({
                'Baris': row.eq(0).text().trim(),
                'NISN': row.eq(1).text().trim(),
                'Nama Lengkap': row.eq(2).text().trim(),
                'Error': row.eq(3).text().trim().replace('', '')
            });
        });
        
        if (errorData.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Tidak Ada Error',
                text: 'Tidak ada data error untuk diexport'
            });
            return;
        }
        
        // Convert to CSV
        let csv = 'Baris,NISN,Nama Lengkap,Error\n';
        errorData.forEach(function(row) {
            csv += `"${row.Baris}","${row.NISN}","${row['Nama Lengkap']}","${row.Error}"\n`;
        });
        
        // Download CSV
        let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Import_Errors_' + new Date().getTime() + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success notification
        Swal.fire({
            icon: 'success',
            title: 'Export Berhasil!',
            text: 'File error berhasil diexport',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
    
    // Add keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + U = focus upload
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            $('#file').click();
        }
        
        // Esc = reload page
        if (e.key === 'Escape' && $('#resultRow').is(':visible')) {
            if (confirm('Kembali ke halaman import?')) {
                location.reload();
            }
        }
    });
    
    // Prevent accidental page leave during upload
    let uploading = false;
    
    window.addEventListener('beforeunload', function(e) {
        if (uploading) {
            e.preventDefault();
            e.returnValue = 'Import sedang berjalan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });
    
    // Add loading state to import button
    $('#importForm').on('submit', function() {
        uploading = true;
        $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    });
    
    // Add tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
