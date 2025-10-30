@extends('adminlte::page')

@section('title', 'Ubah Password - SIMANSA')

@section('content_header')
    <h1>Ubah Password</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key mr-1"></i>
                    Ubah Password Akun
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    Pastikan Anda mengingat password baru yang akan dibuat. Password akan digunakan untuk login ke sistem.
                </div>

                <form method="POST" action="{{ route('siswa.profile.change-password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Password Lama</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   placeholder="Masukkan password lama" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Masukkan password baru (minimal 8 karakter)" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            <div id="password-strength" class="mt-2"></div>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" placeholder="Ketik ulang password baru" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Simpan Password Baru
                        </button>
                    </div>
                </form>

                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Tips Keamanan Password:</strong>
                        <ul class="mb-0">
                            <li>Gunakan kombinasi huruf besar dan kecil</li>
                            <li>Tambahkan angka dan karakter khusus (!@#$%^&*)</li>
                            <li>Minimal 8 karakter</li>
                            <li>Jangan gunakan informasi pribadi (nama, tanggal lahir, dll)</li>
                            <li>Jangan gunakan password yang sama dengan akun lain</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .password-strength {
        height: 5px;
        border-radius: 3px;
        transition: all 0.3s;
    }
    .strength-weak { background-color: #dc3545; width: 33%; }
    .strength-medium { background-color: #ffc107; width: 66%; }
    .strength-strong { background-color: #28a745; width: 100%; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        
        let strengthHtml = '';
        let strengthClass = '';
        let strengthText = '';
        
        if (password.length > 0) {
            if (strength <= 2) {
                strengthClass = 'strength-weak';
                strengthText = 'Lemah';
            } else if (strength <= 3) {
                strengthClass = 'strength-medium';
                strengthText = 'Sedang';
            } else {
                strengthClass = 'strength-strong';
                strengthText = 'Kuat';
            }
            
            strengthHtml = `
                <div>Kekuatan Password: <strong>${strengthText}</strong></div>
                <div class="password-strength ${strengthClass}"></div>
            `;
        }
        
        $('#password-strength').html(strengthHtml);
    });

    // Check password confirmation match
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        
        if (confirmation.length > 0) {
            if (password === confirmation) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}
</script>
@stop
