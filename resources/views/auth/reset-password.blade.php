@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Reset Password')

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <p class="login-box-msg">Masukkan password baru Anda</p>

    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Password Baru" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control" 
                   placeholder="Konfirmasi Password Baru" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <p class="mb-0">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </p>
@stop
