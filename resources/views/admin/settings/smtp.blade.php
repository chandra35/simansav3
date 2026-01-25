@extends('adminlte::page')

@section('title', 'Pengaturan SMTP Email')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-envelope"></i> Pengaturan SMTP Email</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.edit') }}">Pengaturan</a></li>
                <li class="breadcrumb-item active">SMTP Email</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-times-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i> Konfigurasi SMTP
                    </h3>
                </div>
                <form action="{{ route('admin.settings.smtp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Konfigurasi SMTP digunakan untuk fitur lupa password dan notifikasi email lainnya.
                            Anda bisa menggunakan Gmail, Mailgun, SendGrid, atau provider SMTP lainnya.
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="smtp_enabled" 
                                       name="smtp_enabled" value="1" {{ $setting->smtp_enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="smtp_enabled">
                                    <strong>Aktifkan SMTP Email</strong>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="smtp_host"><i class="fas fa-server"></i> SMTP Host</label>
                                    <input type="text" name="smtp_host" id="smtp_host" 
                                           class="form-control @error('smtp_host') is-invalid @enderror"
                                           value="{{ old('smtp_host', $setting->smtp_host) }}"
                                           placeholder="mail.man1metro.sch.id">
                                    <small class="text-muted">Contoh: mail.man1metro.sch.id, smtp.gmail.com</small>
                                    @error('smtp_host')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="smtp_port"><i class="fas fa-plug"></i> Port</label>
                                    <input type="number" name="smtp_port" id="smtp_port" 
                                           class="form-control @error('smtp_port') is-invalid @enderror"
                                           value="{{ old('smtp_port', $setting->smtp_port ?? 465) }}"
                                           placeholder="465">
                                    <small class="text-muted">465 (SSL) / 587 (TLS)</small>
                                    @error('smtp_port')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_username"><i class="fas fa-user"></i> Username</label>
                                    <input type="text" name="smtp_username" id="smtp_username" 
                                           class="form-control @error('smtp_username') is-invalid @enderror"
                                           value="{{ old('smtp_username', $setting->smtp_username) }}"
                                           placeholder="email@domain.com">
                                    @error('smtp_username')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_password"><i class="fas fa-key"></i> Password</label>
                                    <div class="input-group">
                                        <input type="password" name="smtp_password" id="smtp_password" 
                                               class="form-control @error('smtp_password') is-invalid @enderror"
                                               placeholder="{{ $setting->smtp_password ? '••••••••' : 'App Password' }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                <i class="fas fa-eye" id="password-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                    @error('smtp_password')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="smtp_encryption"><i class="fas fa-shield-alt"></i> Enkripsi</label>
                            <select name="smtp_encryption" id="smtp_encryption" class="form-control">
                                <option value="ssl" {{ old('smtp_encryption', $setting->smtp_encryption ?? 'ssl') == 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="tls" {{ old('smtp_encryption', $setting->smtp_encryption) == 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                <option value="none" {{ empty(old('smtp_encryption', $setting->smtp_encryption)) ? 'selected' : '' }}>None</option>
                            </select>
                            <small class="text-muted">Gunakan SSL untuk port 465, TLS untuk port 587</small>
                        </div>

                        <hr>

                        <h5><i class="fas fa-paper-plane"></i> Pengirim Email</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_from_address">Email Pengirim</label>
                                    <input type="email" name="smtp_from_address" id="smtp_from_address" 
                                           class="form-control @error('smtp_from_address') is-invalid @enderror"
                                           value="{{ old('smtp_from_address', $setting->smtp_from_address ?? $setting->email) }}"
                                           placeholder="noreply@sekolah.sch.id">
                                    @error('smtp_from_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_from_name">Nama Pengirim</label>
                                    <input type="text" name="smtp_from_name" id="smtp_from_name" 
                                           class="form-control @error('smtp_from_name') is-invalid @enderror"
                                           value="{{ old('smtp_from_name', $setting->smtp_from_name ?? $setting->nama_sekolah) }}"
                                           placeholder="SIMANSA">
                                    @error('smtp_from_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                        <a href="{{ route('admin.settings.edit') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Test Email Card -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane"></i> Test Koneksi
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Kirim email test untuk memastikan konfigurasi SMTP sudah benar.</p>
                    
                    <div class="form-group">
                        <label for="test_email">Email Tujuan Test</label>
                        <input type="email" id="test_email" class="form-control" 
                               placeholder="test@email.com">
                    </div>
                    
                    <button type="button" class="btn btn-success btn-block" onclick="testSmtp()" id="testBtn"
                            {{ !$setting->smtp_enabled ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane"></i> Kirim Test Email
                    </button>
                    
                    <div id="testResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h3>
                </div>
                <div class="card-body">
                    <h6><strong><i class="fas fa-server text-success"></i> MAN 1 Metro Hosting:</strong></h6>
                    <ul class="small mb-3">
                        <li><strong>Host:</strong> mail.man1metro.sch.id</li>
                        <li><strong>Port:</strong> 465 (SSL)</li>
                        <li><strong>Encryption:</strong> SSL</li>
                        <li><strong>Username:</strong> support@man1metro.sch.id</li>
                        <li><strong>Password:</strong> Password email akun</li>
                    </ul>

                    <hr>

                    <h6><strong>Gmail SMTP:</strong></h6>
                    <ul class="small">
                        <li>Host: smtp.gmail.com</li>
                        <li>Port: 587 (TLS) atau 465 (SSL)</li>
                        <li>Gunakan App Password, bukan password akun</li>
                    </ul>

                    <h6><strong>Mailgun SMTP:</strong></h6>
                    <ul class="small">
                        <li>Host: smtp.mailgun.org</li>
                        <li>Port: 587</li>
                    </ul>

                    <h6><strong>SendGrid SMTP:</strong></h6>
                    <ul class="small">
                        <li>Host: smtp.sendgrid.net</li>
                        <li>Port: 587</li>
                        <li>Username: apikey</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function togglePassword() {
    const field = document.getElementById('smtp_password');
    const icon = document.getElementById('password-icon');
    
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

function testSmtp() {
    const email = document.getElementById('test_email').value;
    const btn = document.getElementById('testBtn');
    const result = document.getElementById('testResult');
    
    if (!email) {
        alert('Masukkan email tujuan test');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    result.style.display = 'none';
    
    fetch('{{ route("admin.settings.smtp.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        result.style.display = 'block';
        if (data.success) {
            result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Terjadi kesalahan</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Test Email';
    });
}
</script>
@stop
