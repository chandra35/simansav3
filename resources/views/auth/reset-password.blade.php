@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Reset Password')

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            Periksa kembali data yang Anda masukkan. Password baru dan konfirmasinya harus sama, dan link reset harus masih valid.
        </div>
    @endif

    <p class="login-box-msg">Masukkan password baru untuk akun <strong>{{ $email }}</strong></p>

    <div class="alert alert-info" style="font-size: 12px;">
        <i class="fas fa-shield-alt"></i>
        Gunakan minimal 8 karakter dan jangan bagikan password kepada orang lain. Link reset ini hanya berlaku 60 menit.
    </div>

    <form action="{{ route('password.update') }}" method="POST" id="resetPasswordForm">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="input-group mb-3">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Password Baru" required>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="password" aria-label="Tampilkan password baru">
                    <span class="fas fa-eye"></span>
                </button>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" 
                   placeholder="Konfirmasi Password Baru" required>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                    <span class="fas fa-eye"></span>
                </button>
            </div>
            @error('password_confirmation')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div id="password-match-feedback" class="small mb-3"></div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block" id="resetPasswordSubmit">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    document.querySelectorAll('.js-toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const target = document.getElementById(button.dataset.target);
            if (!target) {
                return;
            }

            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            button.innerHTML = '<span class="fas ' + (isPassword ? 'fa-eye-slash' : 'fa-eye') + '"></span>';
        });
    });

    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const passwordMatchFeedback = document.getElementById('password-match-feedback');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const resetPasswordSubmit = document.getElementById('resetPasswordSubmit');

    function updatePasswordMatchFeedback() {
        if (!passwordInput || !passwordConfirmationInput || !passwordMatchFeedback) {
            return;
        }

        const password = passwordInput.value;
        const confirmation = passwordConfirmationInput.value;

        if (!confirmation.length) {
            passwordMatchFeedback.textContent = '';
            passwordConfirmationInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (password === confirmation) {
            passwordMatchFeedback.textContent = 'Konfirmasi password sudah sesuai.';
            passwordMatchFeedback.className = 'small text-success mb-3';
            passwordConfirmationInput.classList.add('is-valid');
            passwordConfirmationInput.classList.remove('is-invalid');
            return;
        }

        passwordMatchFeedback.textContent = 'Konfirmasi password belum sama dengan password baru.';
        passwordMatchFeedback.className = 'small text-danger mb-3';
        passwordConfirmationInput.classList.add('is-invalid');
        passwordConfirmationInput.classList.remove('is-valid');
    }

    passwordInput?.addEventListener('input', updatePasswordMatchFeedback);
    passwordConfirmationInput?.addEventListener('input', updatePasswordMatchFeedback);

    resetPasswordForm?.addEventListener('submit', function () {
        if (!resetPasswordSubmit) {
            return;
        }

        resetPasswordSubmit.disabled = true;
        resetPasswordSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses Reset Password...';
    });
</script>
@stop

@section('auth_footer')
    <p class="mb-0">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </p>
@stop
