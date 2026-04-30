@extends('adminlte::page')

@section('title', 'Ubah Password - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-user-shield mr-2"></i>Pengaturan Akun</h1>
@stop

@section('content')
<div class="account-shell">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="account-lead">
                <div>
                    <h4 class="mb-2"><i class="fas fa-key mr-2"></i>Ubah Password Akun</h4>
                    <p class="mb-0">Gunakan password yang kuat dan mudah Anda ingat. Perubahan ini akan langsung dipakai untuk login berikutnya.</p>
                </div>
                <div class="account-lead-badge">
                    <span><i class="fas fa-shield-alt mr-1"></i> Aman</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show account-alert" role="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show account-alert" role="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-exclamation-circle mr-1"></i>
                    Periksa kembali password lama, password baru, dan konfirmasi password Anda.
                </div>
            @endif
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card account-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-lg-7">
                            <div class="account-panel h-100">
                                <div class="account-panel-header">
                                    <h3 class="account-panel-title">
                                        <i class="fas fa-lock"></i>
                                        Form Password
                                    </h3>
                                    <p class="mb-0">Simpan password baru yang aman untuk menjaga akun tetap terlindungi.</p>
                                </div>

                                <form method="POST" action="{{ route('siswa.profile.change-password.update') }}" id="changePasswordForm">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="current_password">Password Lama</label>
                                        <div class="input-group password-input-group">
                                            <input type="password" name="current_password" id="current_password"
                                                   class="form-control @error('current_password') is-invalid @enderror"
                                                   placeholder="Masukkan password lama" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password" aria-label="Tampilkan password lama">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @error('current_password')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password Baru</label>
                                        <div class="input-group password-input-group">
                                            <input type="password" name="password" id="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   placeholder="Minimal 8 karakter" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Tampilkan password baru">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <div id="password-strength" class="mt-2"></div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                                        <div class="input-group password-input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                                   placeholder="Ketik ulang password baru" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @error('password_confirmation')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="account-actions">
                                        <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-1"></i> Simpan Password Baru
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                            <div class="account-side h-100">
                                <div class="account-tip-card warning">
                                    <h5><i class="fas fa-exclamation-triangle mr-2"></i>Perhatian</h5>
                                    <p class="mb-0">Pastikan Anda mengingat password baru yang dibuat karena password ini dipakai untuk masuk ke sistem.</p>
                                </div>

                                <div class="account-tip-card neutral">
                                    <h5><i class="fas fa-lightbulb mr-2"></i>Tips Password Aman</h5>
                                    <ul class="mb-0">
                                        <li>Gunakan kombinasi huruf besar dan huruf kecil.</li>
                                        <li>Tambahkan angka dan karakter khusus.</li>
                                        <li>Hindari nama, tanggal lahir, atau data yang mudah ditebak.</li>
                                        <li>Gunakan password yang berbeda dari akun lain.</li>
                                    </ul>
                                </div>

                                <div class="account-tip-card info">
                                    <h5><i class="fas fa-info-circle mr-2"></i>Saran</h5>
                                    <p class="mb-0">Setelah password berhasil diganti, simpan di tempat yang aman agar tidak mudah lupa.</p>
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
    .account-shell {
        display: grid;
        gap: 1rem;
    }

    .account-lead {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        border-radius: 18px;
        background: linear-gradient(135deg, #eef4ff 0%, #f9fbff 55%, #ffffff 100%);
        box-shadow: 0 16px 36px rgba(59, 130, 246, 0.12);
    }

    .account-lead h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }

    .account-lead p {
        color: #64748b;
        line-height: 1.6;
    }

    .account-lead-badge span {
        display: inline-flex;
        align-items: center;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 700;
        white-space: nowrap;
    }

    .account-alert {
        border-radius: 14px;
    }

    .account-card {
        border: 1px solid rgba(99, 102, 241, 0.08);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .account-card .card-body {
        padding: 1.25rem;
    }

    .account-panel,
    .account-side {
        border-radius: 18px;
        background: #fff;
    }

    .account-panel {
        padding: 1.15rem;
        border: 1px solid #e5e7eb;
    }

    .account-panel-header {
        margin-bottom: 1rem;
    }

    .account-panel-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .account-panel-header p {
        color: #64748b;
        line-height: 1.55;
    }

    .account-panel label {
        font-weight: 600;
        color: #334155;
    }

    .account-panel .form-control,
    .password-input-group .btn {
        min-height: 48px;
        border-radius: 12px;
    }

    .password-input-group > .form-control:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .password-input-group > .input-group-append > .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        min-width: 50px;
    }

    .account-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #eef2f7;
    }

    .account-actions .btn {
        min-width: 168px;
        min-height: 46px;
        border-radius: 12px;
        font-weight: 600;
    }

    .account-side {
        display: grid;
        gap: 1rem;
    }

    .account-tip-card {
        border-radius: 18px;
        padding: 1rem 1.05rem;
        border: 1px solid #e5e7eb;
    }

    .account-tip-card h5 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.55rem;
    }

    .account-tip-card p,
    .account-tip-card li {
        color: #64748b;
        line-height: 1.6;
    }

    .account-tip-card ul {
        padding-left: 1.1rem;
    }

    .account-tip-card.warning {
        background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
        border-color: #fed7aa;
    }

    .account-tip-card.warning h5 {
        color: #c2410c;
    }

    .account-tip-card.neutral {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    }

    .account-tip-card.info {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border-color: #bfdbfe;
    }

    .account-tip-card.info h5 {
        color: #1d4ed8;
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

    @media (min-width: 992px) {
        .account-card .card-body {
            padding: 1.15rem;
        }
    }

    @media (max-width: 767.98px) {
        .account-lead {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 16px;
        }

        .account-card,
        .account-panel,
        .account-tip-card {
            border-radius: 16px;
        }

        .account-card .card-body,
        .account-panel {
            padding: 1rem;
        }

        .account-actions .btn {
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
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
