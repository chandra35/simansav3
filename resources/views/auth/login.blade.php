@extends('adminlte::auth.login')

@section('title', 'Login - SIMANSA')

@section('auth_header', 'Sistem Informasi MAN 1 Metro')

@section('auth_body')
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
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
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
    // Try to get device location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success - got GPS coordinates
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('locationStatus').innerHTML = 
                    '<i class="fas fa-check-circle text-success"></i> Lokasi terdeteksi';
            },
            function(error) {
                // Failed or denied
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
    <small class="text-muted">
        Gunakan username/NISN dan password yang telah diberikan.<br>
        Untuk siswa, NISN adalah username default Anda.
    </small>
</p>
@stop