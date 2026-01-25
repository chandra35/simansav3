@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Lupa Password')

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <p class="login-box-msg">Masukkan email untuk menerima link reset password</p>

    {{-- Info Box untuk Email Default --}}
    <div class="alert alert-info alert-dismissible fade show mb-3" style="font-size: 12px;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="font-size: 14px;">
            <span aria-hidden="true">&times;</span>
        </button>
        <i class="fas fa-info-circle"></i> <strong>Penting!</strong><br>
        <ul class="mb-0 pl-3 mt-1">
            <li>Gunakan email yang sudah Anda update di profil</li>
            <li>Email default <code>@siswa.simansa.sch.id</code> tidak dapat digunakan</li>
            <li>Jika menggunakan email default, hubungi <strong>Operator Sekolah</strong></li>
        </ul>
    </div>

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Kirim Link Reset Password
                </button>
            </div>
        </div>
    </form>

    {{-- Help Box --}}
    <div class="mt-3 p-3 bg-light rounded" style="font-size: 12px;">
        <strong><i class="fas fa-question-circle text-warning"></i> Butuh Bantuan?</strong>
        <p class="mb-0 mt-1 text-muted">
            Jika Anda tidak dapat mengakses email atau menggunakan email default sistem, 
            silakan hubungi Operator Sekolah untuk reset password manual.
        </p>
    </div>
@stop

@section('auth_footer')
    <p class="mb-0">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </p>
@stop
