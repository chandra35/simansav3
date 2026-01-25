@extends('adminlte::page')

@section('title', 'Setup Akun - SIMANSA')

@section('css')
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .loading-content {
        text-align: center;
    }
    .progress-bar {
        transition: width 0.3s, background-color 0.3s;
    }
    .widget-user-header {
        padding: 20px;
        text-align: center;
    }
    .widget-user-image {
        margin-bottom: 10px;
    }
    .widget-user-image img {
        width: 80px;
        height: 80px;
        border: 3px solid rgba(255,255,255,0.3);
    }
    .widget-user-username {
        font-size: 1.25rem;
        margin-bottom: 5px;
    }
    .widget-user-desc {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    /* Foto Upload Styling */
    .foto-frame {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        cursor: pointer;
    }
    .foto-ring {
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #00d4ff);
        animation: pulse-ring 2s ease-in-out infinite;
    }
    @keyframes pulse-ring {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.02); }
    }
    .foto-img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .foto-overlay {
        position: absolute;
        top: 5px;
        left: 5px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(0,0,0,0.6);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
    }
    .foto-frame:hover .foto-overlay {
        opacity: 1;
    }
    /* Cropper Modal */
    #cropperModal .modal-body { 
        padding: 0;
        max-height: 70vh;
        overflow: hidden;
    }
    #cropperPreview {
        max-width: 100%;
        display: block;
    }
</style>
@stop

@section('content_header')
    <div class="row">
        <div class="col-12">
            <h1 class="m-0"><i class="fas fa-user-shield text-primary"></i> Setup Akun Pertama Kali</h1>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Main Form Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key"></i> Pengaturan Password & Email
                </h3>
            </div>
            <form action="{{ route('siswa.force-setup.update') }}" method="POST" id="setupForm">
                @csrf
                <div class="card-body">
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> Penting!</h5>
                        <p class="mb-0">Untuk keamanan akun, Anda wajib mengubah password default dan mengisi email aktif sebelum melanjutkan.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope text-primary"></i> Email Aktif <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="Masukkan email aktif Anda"
                                       required>
                                <small class="text-muted">Email akan digunakan untuk reset password dan notifikasi penting</small>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Username (readonly) -->
                            <div class="form-group">
                                <label><i class="fas fa-user text-secondary"></i> Username</label>
                                <input type="text" class="form-control" value="{{ $user->username }}" readonly disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Password Baru -->
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock text-primary"></i> Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           placeholder="Minimal 8 karakter"
                                           required minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                
                                <!-- Password Strength -->
                                <div class="mt-2">
                                    <small class="text-muted">Kekuatan Password:</small>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" id="password-strength" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="password-hint" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Konfirmasi Password -->
                            <div class="form-group">
                                <label for="password_confirmation"><i class="fas fa-lock text-primary"></i> Konfirmasi Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control" 
                                           placeholder="Ulangi password baru"
                                           required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="password-match" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan & Lanjutkan ke Dashboard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Foto Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-camera"></i> Foto Profile (Opsional)
                </h3>
            </div>
            <div class="card-body text-center">
                <div class="foto-frame" id="fotoFrame" title="Klik untuk upload foto">
                    <div class="foto-ring"></div>
                    <img id="previewFoto" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background={{ $user->siswa && $user->siswa->jenis_kelamin == 'P' ? 'e83e8c' : '007bff' }}&color=fff" 
                         class="foto-img"
                         alt="Foto Profile">
                    <div class="foto-overlay">
                        <i class="fas fa-camera fa-2x mb-2"></i>
                        <span>Upload Foto</span>
                    </div>
                </div>
                <input type="file" id="fotoInput" class="d-none" accept="image/jpeg,image/jpg,image/png">
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseFoto">
                        <i class="fas fa-folder-open"></i> Pilih File
                    </button>
                </div>
                
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Format: JPG/PNG, Max 2MB
                </small>
                <small class="text-success">
                    <i class="fas fa-crop-alt"></i> Foto dipotong 1:1 otomatis
                </small>
                
                <div id="fotoStatus" class="mt-2" style="display: none;">
                    <span class="badge badge-success"><i class="fas fa-check"></i> Foto berhasil diupload</span>
                </div>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="card card-widget widget-user-2 shadow-sm">
            <div class="widget-user-header bg-gradient-primary">
                <div class="widget-user-image">
                    <img class="img-circle elevation-2" id="welcomeFoto"
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background={{ $user->siswa && $user->siswa->jenis_kelamin == 'P' ? 'e83e8c' : '007bff' }}&color=fff" 
                         alt="User Avatar">
                </div>
                <h3 class="widget-user-username">{{ $user->name }}</h3>
                <h5 class="widget-user-desc">Siswa Baru</h5>
            </div>
            <div class="card-footer p-0">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-id-badge text-primary"></i> NISN
                            <span class="float-right badge bg-primary">{{ $user->username }}</span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-clock text-warning"></i> Status
                            <span class="float-right badge bg-warning">Perlu Setup</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb"></i> Tips Keamanan
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check text-success"></i> Gunakan minimal 8 karakter
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check text-success"></i> Kombinasi huruf besar & kecil
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check text-success"></i> Tambahkan angka dan simbol
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check text-success"></i> Jangan gunakan data pribadi
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-envelope text-info"></i> Gunakan email yang aktif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-3 text-white">Menyimpan data...</p>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-crop-alt"></i> Crop Foto Profile
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="cropperPreview" src="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnCropSave">
                    <i class="fas fa-check"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- Cropper.js -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
<script>
var cropper = null;

// Foto upload handlers
$('#fotoFrame, #btnChooseFoto').on('click', function() {
    $('#fotoInput').click();
});

$('#fotoInput').on('change', function() {
    var file = this.files[0];
    if (!file) return;
    
    // Validate file type
    var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Format file harus JPG atau PNG!');
        this.value = '';
        return;
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB!');
        this.value = '';
        return;
    }
    
    // Read and show in cropper
    var reader = new FileReader();
    reader.onload = function(e) {
        $('#cropperPreview').attr('src', e.target.result);
        $('#cropperModal').modal('show');
    };
    reader.readAsDataURL(file);
});

// Initialize cropper when modal is shown
$('#cropperModal').on('shown.bs.modal', function() {
    var image = document.getElementById('cropperPreview');
    if (cropper) {
        cropper.destroy();
    }
    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 2,
        dragMode: 'move',
        autoCropArea: 0.8,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
});

// Destroy cropper when modal is hidden
$('#cropperModal').on('hidden.bs.modal', function() {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    $('#fotoInput').val('');
});

// Save cropped image
$('#btnCropSave').on('click', function() {
    if (!cropper) return;
    
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    
    var canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    var base64 = canvas.toDataURL('image/jpeg', 0.9);
    
    // Upload via AJAX
    $.ajax({
        url: '{{ route("siswa.profile.diri.upload-foto") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            cropped_image: base64
        },
        success: function(response) {
            if (response.success) {
                // Update preview images
                $('#previewFoto').attr('src', response.foto_url);
                $('#welcomeFoto').attr('src', response.foto_url);
                $('#fotoStatus').show();
                $('#cropperModal').modal('hide');
                alert('Foto berhasil diupload!');
            } else {
                alert('Gagal: ' + response.message);
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
            alert('Error: ' + msg);
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Simpan Foto');
        }
    });
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength');
    const hint = document.getElementById('password-hint');
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 10;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 30) {
        strengthBar.className = 'progress-bar bg-danger';
        hint.textContent = 'Lemah - Tambahkan huruf besar, angka, atau simbol';
        hint.className = 'text-danger';
    } else if (strength < 60) {
        strengthBar.className = 'progress-bar bg-warning';
        hint.textContent = 'Sedang - Bisa lebih kuat lagi';
        hint.className = 'text-warning';
    } else if (strength < 80) {
        strengthBar.className = 'progress-bar bg-info';
        hint.textContent = 'Cukup Kuat';
        hint.className = 'text-info';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        hint.textContent = 'Sangat Kuat!';
        hint.className = 'text-success';
    }
    
    checkPasswordMatch();
});

// Password match checker
document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchDiv = document.getElementById('password-match');
    
    if (confirmation === '') {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirmation) {
        matchDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Password cocok</span>';
    } else {
        matchDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Password tidak cocok</span>';
    }
}

// Form submit with loading
document.getElementById('setupForm').addEventListener('submit', function() {
    document.getElementById('loadingOverlay').style.display = 'flex';
});
</script>
@stop
