@extends('adminlte::auth.login')

@section('title', 'Login - SIMANSA')

@section('auth_header')
<div class="simansa-header">
    <div class="simansa-icon">
        <i class="fas fa-graduation-cap"></i>
    </div>
    <div class="simansa-title">SIMANSA</div>
    <div class="simansa-subtitle">Sistem Informasi MAN 1 Metro</div>
</div>
@stop

@section('css')
<style>
/* ── Reset AdminLTE defaults ── */
body.login-page {
    background: linear-gradient(135deg, #0f2027 0%, #203a43 40%, #2c5364 100%) !important;
    min-height: 100vh;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.login-page .wrapper { display: contents; }

/* ── Login Box ── */
.login-box {
    width: 400px;
    animation: cardEntry .5s cubic-bezier(.22,1,.36,1);
}
.login-logo { display: none !important; }

/* ── Card ── */
.login-box .card {
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 60px rgba(0,0,0,.3), 0 0 0 1px rgba(255,255,255,.06) !important;
    overflow: hidden;
    background: #fff;
    border-top: none !important;
}
.login-box .card.card-outline.card-primary {
    border-top: none !important;
}

/* ── Card Header ── */
.login-box .card-header {
    background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%) !important;
    border: none !important;
    border-bottom: none !important;
    padding: 28px 24px 24px !important;
}
.login-box .card-header .card-title {
    font-size: inherit !important;
    font-weight: inherit !important;
    margin: 0 !important;
}
.simansa-header { text-align: center; }
.simansa-icon {
    width: 56px; height: 56px;
    margin: 0 auto 12px;
    background: rgba(255,255,255,.15);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: iconFloat 3s ease-in-out infinite;
}
.simansa-icon i {
    font-size: 24px;
    color: #fff;
}
.simansa-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1.5px;
    margin-bottom: 2px;
}
.simansa-subtitle {
    font-size: .82rem;
    color: rgba(255,255,255,.75);
    font-weight: 400;
    letter-spacing: .3px;
}

/* ── Card Body ── */
.login-box .card-body {
    padding: 28px 28px 20px !important;
}

/* ── Form controls ── */
.login-box .form-control {
    border-radius: 10px !important;
    border: 1.5px solid #e0e0e0 !important;
    padding: 10px 14px !important;
    font-size: .9rem;
    transition: border-color .2s, box-shadow .2s;
    height: auto !important;
}
.login-box .form-control:focus {
    border-color: #1a73e8 !important;
    box-shadow: 0 0 0 3px rgba(26,115,232,.12) !important;
}
.login-box .input-group {
    margin-bottom: 16px !important;
}
.login-box .input-group-text {
    border-radius: 0 10px 10px 0 !important;
    border: 1.5px solid #e0e0e0 !important;
    border-left: none !important;
    background: #f8f9fa !important;
    color: #90a4ae;
    transition: border-color .2s, color .2s;
}
.login-box .form-control:focus + .input-group-append .input-group-text {
    border-color: #1a73e8 !important;
    color: #1a73e8;
}

/* Stable field layout to avoid local bootstrap/input-group rendering differences */
.simansa-field {
    position: relative;
    margin-bottom: 14px;
}
.simansa-field .simansa-field-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #90a4ae;
    font-size: .9rem;
    pointer-events: none;
}
.simansa-field .form-control {
    padding-left: 36px !important;
}
.simansa-field .form-control:focus ~ .simansa-field-icon {
    color: #1a73e8;
}

/* ── Submit button ── */
#btnLogin {
    border-radius: 10px !important;
    padding: 10px 20px !important;
    font-weight: 600;
    font-size: .88rem;
    letter-spacing: .3px;
    background: linear-gradient(135deg, #1a73e8, #0d47a1) !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(26,115,232,.35);
    transition: transform .15s, box-shadow .15s;
    color: #fff !important;
}
#btnLogin:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(26,115,232,.45);
}
#btnLogin:active:not(:disabled) {
    transform: translateY(0);
}

/* Interactive login guide: lightweight vector hand, no extra runtime. */
.login-button-stage {
    position: relative;
    display: inline-flex;
    align-items: center;
    padding-right: 40px;
    isolation: isolate;
}
.login-guide-hand {
    position: absolute;
    z-index: 2;
    top: 50%;
    right: -3px;
    width: 76px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: .78;
    pointer-events: none;
    transform-origin: 78% 66%;
    transform: translate(4px, 3px) rotate(-5deg);
    filter: drop-shadow(0 6px 8px rgba(13, 71, 161, .25));
    animation: loginHandIdle 2.4s ease-in-out infinite;
}
.login-guide-hand svg {
    display: block;
    width: 100%;
    height: 100%;
    overflow: visible;
}
.login-hand-glove {
    fill: #fff;
    stroke: #1a73e8;
    stroke-width: 2.4;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.login-hand-detail {
    fill: none;
    stroke: #90caf9;
    stroke-width: 1.6;
    stroke-linecap: round;
}
.login-hand-cuff {
    fill: url(#loginHandCuffGradient);
    stroke: #0d47a1;
    stroke-width: 2.2;
    stroke-linejoin: round;
}
.login-hand-spark {
    fill: none;
    stroke: #29b6f6;
    stroke-width: 2.2;
    stroke-linecap: round;
    opacity: 0;
    transform-origin: 12px 25px;
}
.login-button-stage.is-guiding .login-hand-spark,
.login-button-stage.is-pressing .login-hand-spark {
    animation: loginHandSpark .5s ease-out both;
}
.login-guide-hand::after {
    content: '';
    position: absolute;
    top: 18px;
    left: 1px;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(26, 115, 232, .55);
    border-radius: 50%;
    opacity: 0;
    transform: scale(.35);
}
.login-button-stage.is-guiding .login-guide-hand {
    animation: loginHandGuide 2.15s cubic-bezier(.22, 1, .36, 1) both;
}
.login-button-stage:hover .login-guide-hand,
.login-button-stage:focus-within .login-guide-hand {
    animation: loginHandHover .42s cubic-bezier(.22, 1, .36, 1) both;
}
.login-button-stage.is-pressing .login-guide-hand {
    animation: loginHandPress .2s ease-out both;
}
.login-button-stage.is-pressing .login-guide-hand::after {
    animation: loginHandTapRing .42s ease-out both;
}

/* ── Remember me ── */
.icheck-primary label {
    font-size: .85rem;
    color: #546e7a;
}

/* ── Location badge ── */
#locationStatus {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .78rem;
    padding: 4px 10px;
    border-radius: 20px;
    background: #f5f5f5;
    color: #78909c !important;
    transition: all .3s ease;
}
#locationStatus.detected {
    background: #e8f5e9 !important;
    color: #2e7d32 !important;
}

/* ── Card Footer ── */
.login-box .card-footer {
    background: #fafbfc !important;
    border-top: 1px solid #f0f0f0 !important;
    padding: 16px 28px !important;
}
.login-box .card-footer a {
    color: #1a73e8 !important;
    font-weight: 500;
    font-size: .88rem;
    text-decoration: none;
}
.login-box .card-footer a:hover { color: #0d47a1 !important; }
.login-box .card-footer .text-muted {
    font-size: .78rem;
    color: #90a4ae !important;
    line-height: 1.5;
}

/* ── Flash messages ── */
.login-box .alert {
    border-radius: 10px;
    font-size: .85rem;
    border: none;
}

/* ── Overlay ── */
.login-overlay-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15,32,39,.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
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

/* ── Keyframes ── */
@keyframes cardEntry {
    from { opacity: 0; transform: translateY(20px) scale(.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
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
@keyframes loginHandGuide {
    0%, 8% { opacity: .15; transform: translate(30px, 13px) rotate(8deg) scale(.92); }
    24% { opacity: 1; transform: translate(15px, 7px) rotate(1deg) scale(1); }
    43% { opacity: 1; transform: translate(1px, 1px) rotate(-5deg) scale(1); }
    52% { opacity: 1; transform: translate(-5px, 0) rotate(-7deg) scale(.91); }
    62% { opacity: 1; transform: translate(0, 2px) rotate(-4deg) scale(1); }
    78% { opacity: 1; transform: translate(7px, 4px) rotate(-2deg) scale(.98); }
    100% { opacity: .82; transform: translate(4px, 3px) rotate(-5deg) scale(1); }
}
@keyframes loginHandIdle {
    0%, 100% { opacity: .76; transform: translate(5px, 4px) rotate(-4deg) scale(.98); }
    50% { opacity: .95; transform: translate(2px, 1px) rotate(-7deg) scale(1.02); }
}
@keyframes loginHandHover {
    from { opacity: .35; transform: translate(20px, 9px) rotate(5deg) scale(.94); }
    to { opacity: 1; transform: translate(0, 0) rotate(-6deg) scale(1); }
}
@keyframes loginHandPress {
    from { opacity: 1; transform: translate(0, 0) rotate(-6deg) scale(1); }
    to { opacity: 1; transform: translate(-6px, 0) rotate(-8deg) scale(.9); }
}
@keyframes loginHandTapRing {
    0% { opacity: .9; transform: scale(.35); }
    100% { opacity: 0; transform: scale(1.7); }
}
@keyframes loginHandSpark {
    0% { opacity: 0; transform: scale(.55); }
    35% { opacity: 1; transform: scale(1); }
    100% { opacity: 0; transform: scale(1.25); }
}

/* ── Responsive ── */
@media (max-width: 480px) {
    .login-box { width: 92% !important; }
    .login-box .card-body { padding: 22px 20px 16px !important; }
    .login-box .card-header { padding: 22px 20px 18px !important; }
}
@media (prefers-reduced-motion: reduce) {
    .login-button-stage { padding-right: 0; }
    .login-guide-hand { display: none; }
}
</style>
@stop

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

<form action="{{ route('login') }}" method="post" id="loginForm">
    @csrf

    {{-- Hidden fields for GPS location --}}
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
    <input type="hidden" name="location_accuracy" id="location_accuracy">

    {{-- Username field --}}
    <div class="simansa-field">
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username') }}" placeholder="Username / NISN" autofocus>
        <span class="fas fa-user simansa-field-icon"></span>
        @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password field --}}
    <div class="simansa-field">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Password">
        <span class="fas fa-lock simansa-field-icon"></span>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Remember & Submit --}}
    <div class="d-flex align-items-center justify-content-between mt-1 mb-2">
        <div class="icheck-primary">
            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Ingat Saya</label>
        </div>
        <span class="login-button-stage" id="loginButtonStage">
            <button type="submit" class="btn btn-primary px-4" id="btnLogin">
                <i class="fas fa-sign-in-alt mr-1"></i> Masuk
            </button>
            <span class="login-guide-hand" aria-hidden="true">
                <svg viewBox="0 0 104 68" role="presentation" focusable="false">
                    <defs>
                        <linearGradient id="loginHandCuffGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#29b6f6"/>
                            <stop offset="1" stop-color="#0d47a1"/>
                        </linearGradient>
                    </defs>
                    <g class="login-hand-spark">
                        <path d="M12 8v7"/>
                        <path d="M2 15l6 4"/>
                        <path d="M1 29l7-1"/>
                    </g>
                    <path class="login-hand-glove" d="M17 20.5h41.5l-5.7-7.2c-2.1-2.7-1.5-6.4 1.2-8.1 2.5-1.6 5.8-.8 7.7 1.5l13.6 16.8 8.7 7.4 8.2 3.7-9.8 24.7-13.1-5.1c-4.7-1.8-9.9-2.2-14.9-1.1l-8.6 1.9c-4.3.9-8.6-1.5-9.8-5.5-.6-2.2-.1-4.4 1.2-6.1-4.1.3-7.5-2.5-7.7-6.2-.1-2.1.9-4 2.6-5.2H17c-5.8 0-10.4-2.8-10.4-6.3s4.6-6.2 10.4-6.2Z"/>
                    <path class="login-hand-detail" d="M38.2 43.4h16.2"/>
                    <path class="login-hand-detail" d="M32.6 32.5h24.8"/>
                    <path class="login-hand-detail" d="M58.5 20.5l7.7 9.5"/>
                    <path class="login-hand-cuff" d="M84 30.9 103 38.4 92.2 66 73.4 58.6Z"/>
                    <path d="M88.5 54.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" fill="#fff" opacity=".9"/>
                </svg>
            </span>
        </span>
    </div>

    {{-- Location status --}}
    <div class="text-center mt-2">
        <small id="locationStatus">
            <i class="fas fa-map-marker-alt"></i> Mendeteksi lokasi...
        </small>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('btnLogin');
    const buttonStage = document.getElementById('loginButtonStage');
    const overlay = document.getElementById('loginOverlay');
    const locStatus = document.getElementById('locationStatus');
    let submitted = false;

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.setTimeout(function() {
            if (!submitted && document.activeElement !== btn) {
                buttonStage.classList.add('is-guiding');
            }
        }, 850);

        buttonStage.addEventListener('animationend', function(event) {
            if (event.animationName === 'loginHandGuide') {
                buttonStage.classList.remove('is-guiding');
            }
        });

        ['mousedown', 'touchstart'].forEach(function(eventName) {
            btn.addEventListener(eventName, function() {
                buttonStage.classList.remove('is-guiding');
                buttonStage.classList.add('is-pressing');
            }, { passive: true });
        });

        ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach(function(eventName) {
            btn.addEventListener(eventName, function() {
                buttonStage.classList.remove('is-pressing');
            }, { passive: true });
        });
    }

    form.addEventListener('submit', function(e) {
        if (submitted) { e.preventDefault(); return false; }
        submitted = true;
        btn.disabled = true;
        overlay.style.display = 'block';
    });

    window.addEventListener('pageshow', function() {
        submitted = false;
        btn.disabled = false;
        buttonStage.classList.remove('is-guiding', 'is-pressing');
        overlay.style.display = 'none';
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('location_accuracy').value = position.coords.accuracy || '';
                locStatus.innerHTML = '<i class="fas fa-check-circle"></i> Lokasi terdeteksi' + (position.coords.accuracy ? ' (±' + Math.round(position.coords.accuracy) + ' m)' : '');
                locStatus.classList.add('detected');
            },
            function() {
                locStatus.innerHTML = '<i class="fas fa-info-circle"></i> Lokasi dari IP akan digunakan';
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    } else {
        locStatus.innerHTML = '<i class="fas fa-info-circle"></i> Browser tidak support GPS';
    }
});
</script>
@stop

@section('auth_footer')
<p class="mb-2">
    <a href="{{ route('password.request') }}">
        <i class="fas fa-key mr-1"></i>Lupa Password?
    </a>
</p>
<p class="mb-0">
    <small class="text-muted">
        Gunakan username/NISN dan password yang telah diberikan.<br>
        Untuk siswa, NISN adalah username default Anda.
    </small>
</p>
@stop
