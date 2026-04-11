@extends('adminlte::auth.login')

@section('title', 'Login - SIMANSA')

@section('auth_header', 'Sistem Informasi MAN 1 Metro')

@section('auth_body')
@include('adminlte::partials.common.flash-messages')

{{-- Login Overlay --}}
<div id="loginOverlay" style="display:none;">
    <div class="login-overlay-backdrop"></div>
    <div class="login-overlay-content">
        <div class="login-overlay-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring spinner-ring-2"></div>
        </div>
        <div class="login-overlay-text">Memverifikasi akun...</div>
        <div class="login-overlay-subtext">Mohon tunggu sebentar</div>
    </div>
</div>

<style>
.login-overlay-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 9998;
    animation: overlayFadeIn .3s ease;
}
.login-overlay-content {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    text-align: center;
    animation: overlaySlideUp .4s ease;
}
.login-overlay-spinner {
    width: 64px; height: 64px;
    margin: 0 auto 20px;
    position: relative;
}
.spinner-ring {
    width: 64px; height: 64px;
    border: 3px solid transparent;
    border-top-color: #fff;
    border-radius: 50%;
    position: absolute;
    top: 0; left: 0;
    animation: spinnerRotate .9s linear infinite;
}
.spinner-ring-2 {
    width: 48px; height: 48px;
    top: 8px; left: 8px;
    border-top-color: rgba(255,255,255,.4);
    animation-direction: reverse;
    animation-duration: 1.2s;
}
.login-overlay-text {
    color: #fff;
    font-size: 1.15rem;
    font-weight: 600;
    letter-spacing: .3px;
    margin-bottom: 4px;
    text-shadow: 0 1px 3px rgba(0,0,0,.3);
}
.login-overlay-subtext {
    color: rgba(255,255,255,.7);
    font-size: .82rem;
}
@keyframes overlayFadeIn {
    from { opacity: 0; } to { opacity: 1; }
}
@keyframes overlaySlideUp {
    from { opacity: 0; transform: translate(-50%, -45%); }
    to { opacity: 1; transform: translate(-50%, -50%); }
}
@keyframes spinnerRotate {
    to { transform: rotate(360deg); }
}
</style>

<form action="{{ route('login') }}" method="post" id="loginForm">
    @csrf
    
    {{-- Hidden fields for GPS location --}}
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
    
    {{-- Username field --}}
    <div class="input-group mb-3">
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username') }}" placeholder="Username / NISN" autofocus>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-user"></span>
            </div>
        </div>
        @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Password">
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

    {{-- Remember me checkbox --}}
    <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">
                    Ingat Saya
                </label>
            </div>
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="btnLogin">
                <i class="fas fa-sign-in-alt" id="btnIcon"></i> Masuk
            </button>
        </div>
    </div>
    
    {{-- Location status --}}
    <div class="row mt-2">
        <div class="col-12">
            <small id="locationStatus" class="text-muted">
                <i class="fas fa-map-marker-alt"></i> Mendeteksi lokasi...
            </small>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('btnLogin');
    const overlay = document.getElementById('loginOverlay');
    let submitted = false;

    form.addEventListener('submit', function(e) {
        if (submitted) {
            e.preventDefault();
            return false;
        }
        submitted = true;
        btn.disabled = true;
        overlay.style.display = 'block';
    });

    // Re-enable if browser back button is used
    window.addEventListener('pageshow', function() {
        submitted = false;
        btn.disabled = false;
        overlay.style.display = 'none';
    });

    // Try to get device location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('locationStatus').innerHTML = 
                    '<i class="fas fa-check-circle text-success"></i> Lokasi terdeteksi';
            },
            function(error) {
                console.log('Geolocation error:', error);
                document.getElementById('locationStatus').innerHTML = 
                    '<i class="fas fa-info-circle text-info"></i> Lokasi dari IP akan digunakan';
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    } else {
        document.getElementById('locationStatus').innerHTML = 
            '<i class="fas fa-info-circle text-info"></i> Browser tidak support GPS';
    }
});
</script>
@stop

@section('auth_footer')
<p class="mb-1">
    <a href="{{ route('password.request') }}" class="text-primary">
        <i class="fas fa-key"></i> Lupa Password?
    </a>
</p>
<p class="mb-1">
    <small class="text-muted">
        Gunakan username/NISN dan password yang telah diberikan.<br>
        Untuk siswa, NISN adalah username default Anda.
    </small>
</p>
@stop
