@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Lupa Password')

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <p class="login-box-msg">Masukkan email untuk menerima link reset password</p>

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="Email" required autofocus>
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
                    <i class="fas fa-paper-plane"></i> Kirim Link Reset
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
