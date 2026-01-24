@extends('adminlte::page')

@section('title', 'Ganti Password - SIMANSA')

@section('content_header')
    <h1>Ganti Password</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key mr-1"></i>
                    Ganti Password Pertama Kali
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Informasi!</h5>
                    Ini adalah login pertama Anda. Silakan ganti password default dengan password yang mudah Anda ingat.
                </div>

                <form method="POST" action="{{ route('siswa.profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Masukkan password baru (minimal 8 karakter)" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control" placeholder="Ketik ulang password baru" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Simpan Password
                        </button>
                    </div>
                </form>

                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Tips:</strong>
                        <ul class="mb-0">
                            <li>Gunakan kombinasi huruf, angka, dan karakter khusus</li>
                            <li>Minimal 8 karakter</li>
                            <li>Jangan gunakan informasi pribadi seperti tanggal lahir</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
<script>
$(document).ready(function() {
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        
        // You can add password strength indicator here
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