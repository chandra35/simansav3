@extends('adminlte::page')

@section('title', 'Ganti Password - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-user-lock mr-2"></i>Setup Akun</h1>
@stop

@section('content')
<div class="first-pass-shell">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="first-pass-lead">
                <h4 class="mb-2"><i class="fas fa-key mr-2"></i>Ganti Password Pertama Kali</h4>
                <p class="mb-0">Ini adalah login pertama Anda. Silakan buat password baru yang aman dan mudah diingat sebelum melanjutkan.</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card first-pass-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-lg-7">
                            <div class="first-pass-panel h-100">
                                <div class="first-pass-note">
                                    <h5><i class="fas fa-info-circle mr-2"></i>Informasi</h5>
                                    <p class="mb-0">Setelah password diganti, password default tidak bisa dipakai lagi untuk masuk ke sistem.</p>
                                </div>

                                <form method="POST" action="{{ route('siswa.profile.password.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="password">Password Baru</label>
                                        <input type="password" name="password" id="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Minimal 8 karakter" required>
                                        @error('password')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <div id="password-strength" class="mt-2"></div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                               class="form-control"
                                               placeholder="Ketik ulang password baru" required>
                                    </div>

                                    <div class="first-pass-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-1"></i> Simpan Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                            <div class="first-pass-side h-100">
                                <div class="first-pass-tip">
                                    <h5><i class="fas fa-shield-alt mr-2"></i>Tips Password</h5>
                                    <ul class="mb-0">
                                        <li>Gunakan kombinasi huruf, angka, dan karakter khusus.</li>
                                        <li>Buat minimal 8 karakter.</li>
                                        <li>Hindari tanggal lahir, nama, atau data yang mudah ditebak.</li>
                                    </ul>
                                </div>

                                <div class="first-pass-tip alt">
                                    <h5><i class="fas fa-check-circle mr-2"></i>Setelah Selesai</h5>
                                    <p class="mb-0">Setelah password tersimpan, Anda akan diarahkan untuk melengkapi data profil siswa.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .first-pass-shell {
        display: grid;
        gap: 1rem;
    }

    .first-pass-lead {
        padding: 1.1rem 1.25rem;
        border-radius: 18px;
        background: linear-gradient(135deg, #eef6ff 0%, #f9fbff 55%, #ffffff 100%);
        box-shadow: 0 16px 36px rgba(59, 130, 246, 0.12);
    }

    .first-pass-lead h4 {
        font-size: 1.18rem;
        font-weight: 700;
        color: #0f172a;
    }

    .first-pass-lead p {
        color: #64748b;
        line-height: 1.6;
    }

    .first-pass-card {
        border: 1px solid rgba(99, 102, 241, 0.08);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .first-pass-card .card-body {
        padding: 1.25rem;
    }

    .first-pass-panel {
        padding: 1.15rem;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
    }

    .first-pass-note,
    .first-pass-tip {
        border-radius: 16px;
        padding: 1rem 1.05rem;
    }

    .first-pass-note {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border: 1px solid #bfdbfe;
        margin-bottom: 1rem;
    }

    .first-pass-note h5,
    .first-pass-tip h5 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .first-pass-note p,
    .first-pass-tip p,
    .first-pass-tip li {
        color: #64748b;
        line-height: 1.6;
    }

    .first-pass-panel label {
        font-weight: 600;
        color: #334155;
    }

    .first-pass-panel .form-control {
        min-height: 48px;
        border-radius: 12px;
    }

    .first-pass-actions {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #eef2f7;
        display: flex;
        justify-content: flex-end;
    }

    .first-pass-actions .btn {
        min-width: 180px;
        min-height: 46px;
        border-radius: 12px;
        font-weight: 600;
    }

    .first-pass-side {
        display: grid;
        gap: 1rem;
    }

    .first-pass-tip {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e5e7eb;
    }

    .first-pass-tip.alt {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border-color: #bbf7d0;
    }

    .first-pass-tip ul {
        padding-left: 1.1rem;
        margin-bottom: 0;
    }

    .password-strength-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
        margin-top: 0.45rem;
    }

    .password-strength-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.25s ease, background-color 0.25s ease;
    }

    .strength-weak { background-color: #dc3545; width: 33%; }
    .strength-medium { background-color: #f59e0b; width: 66%; }
    .strength-strong { background-color: #10b981; width: 100%; }

    @media (max-width: 767.98px) {
        .first-pass-lead,
        .first-pass-card,
        .first-pass-panel,
        .first-pass-note,
        .first-pass-tip {
            border-radius: 16px;
        }

        .first-pass-lead,
        .first-pass-card .card-body,
        .first-pass-panel {
            padding: 1rem;
        }

        .first-pass-actions .btn {
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
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
                <small class="text-muted">Kekuatan Password: <strong>${strengthText}</strong></small>
                <div class="password-strength-track">
                    <div class="password-strength-bar ${strengthClass}"></div>
                </div>
            `;
        }

        $('#password-strength').html(strengthHtml);
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
