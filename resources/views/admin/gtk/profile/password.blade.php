@extends('adminlte::page')

@section('title', 'Ganti Password')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-shield-alt"></i> Keamanan Akun</div>
            <h1 class="simansa-hero__title">Ganti Password</h1>
            <p class="simansa-hero__subtitle">Amankan akun Anda dalam tiga langkah mudah. Password baru langsung berlaku pada sesi berikutnya.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Akun</span>
                <span class="simansa-hero-chip__value">{{ Auth::user()->username }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-account-password">
<div class="row justify-content-center">
    <div class="col-12 col-xl-8 col-lg-9">
        @if(session('info'))
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)<div><i class="fas fa-times-circle mr-1"></i>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div id="pw-wizard" class="card simansa-management-card gtk-account-password__wizard">
            {{-- Stepper --}}
            <div class="card-body pb-0">
                <div class="pw-stepper">
                    <div class="pw-step is-active" data-step="1">
                        <div class="pw-step__dot"><i class="fas fa-user-lock"></i></div>
                        <div class="pw-step__label">Verifikasi</div>
                    </div>
                    <div class="pw-step" data-step="2">
                        <div class="pw-step__dot"><i class="fas fa-key"></i></div>
                        <div class="pw-step__label">Password Baru</div>
                    </div>
                    <div class="pw-step" data-step="3">
                        <div class="pw-step__dot"><i class="fas fa-check-double"></i></div>
                        <div class="pw-step__label">Konfirmasi</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.gtk.profile.password.update') }}" method="POST" id="pwForm" autocomplete="off">
                @csrf
                @method('PUT')

                {{-- STEP 1: Password lama --}}
                <div class="card-body pw-panel is-active" data-panel="1">
                    <h5 class="pw-panel__title"><i class="fas fa-user-lock text-primary mr-2"></i>Verifikasi identitas</h5>
                    <p class="text-muted">Masukkan password lama untuk memastikan ini benar-benar Anda.</p>
                    <div class="form-group">
                        <label for="current_password">Password Lama <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" placeholder="Masukkan password lama"
                                   autocomplete="current-password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary pw-toggle" type="button" data-target="current_password" aria-label="Tampilkan password lama">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @error('current_password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- STEP 2: Password baru --}}
                <div class="card-body pw-panel" data-panel="2">
                    <h5 class="pw-panel__title"><i class="fas fa-key text-primary mr-2"></i>Buat password baru</h5>
                    <p class="text-muted">Semakin kuat password, semakin aman akun Anda.</p>
                    <div class="form-group">
                        <label for="password">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Minimal 8 karakter"
                                   autocomplete="new-password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary pw-toggle" type="button" data-target="password" aria-label="Tampilkan password baru">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    {{-- Strength meter --}}
                    <div class="pw-strength">
                        <div class="pw-strength__bar"><span id="pwBar"></span></div>
                        <div class="pw-strength__label">Kekuatan: <strong id="pwLabel">—</strong></div>
                    </div>

                    {{-- Checklist --}}
                    <ul class="pw-check list-unstyled mt-3 mb-0">
                        <li data-rule="len"><i class="far fa-circle"></i> Minimal 8 karakter</li>
                        <li data-rule="letter"><i class="far fa-circle"></i> Mengandung huruf</li>
                        <li data-rule="number"><i class="far fa-circle"></i> Mengandung angka</li>
                        <li data-rule="symbol"><i class="far fa-circle"></i> Simbol (opsional, lebih aman)</li>
                    </ul>
                </div>

                {{-- STEP 3: Konfirmasi --}}
                <div class="card-body pw-panel" data-panel="3">
                    <h5 class="pw-panel__title"><i class="fas fa-check-double text-primary mr-2"></i>Konfirmasi &amp; simpan</h5>
                    <p class="text-muted">Ketik ulang password baru untuk memastikan tidak ada salah ketik.</p>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" placeholder="Ketik ulang password baru"
                                   autocomplete="new-password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary pw-toggle" type="button" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small id="pwMatch" class="form-text" aria-live="polite"></small>
                    </div>

                    <div class="pw-reminder">
                        <div class="pw-reminder__title"><i class="fas fa-lightbulb mr-1"></i> Tips keamanan</div>
                        <ul class="mb-0 pl-3">
                            <li>Gunakan kombinasi huruf, angka, dan simbol.</li>
                            <li>Jangan pakai password yang mudah ditebak (tanggal lahir, nama).</li>
                            <li>Password baru berlaku untuk sesi login berikutnya.</li>
                        </ul>
                    </div>
                </div>

                {{-- Footer nav --}}
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-secondary" id="btnCancel">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-outline-secondary d-none" id="btnPrev">
                            <i class="fas fa-chevron-left"></i> Sebelumnya
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="btnNext" disabled>
                            Lanjut <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="submit" class="btn btn-primary d-none" id="btnSubmit" disabled>
                            <i class="fas fa-save"></i> Simpan Password Baru
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-outline card-primary gtk-account-password__identity">
            <div class="card-body py-3">
                <div><i class="fas fa-user text-muted mr-1"></i> <strong>Username:</strong> <span>{{ Auth::user()->username }}</span></div>
                <div><i class="fas fa-envelope text-muted mr-1"></i> <strong>Email:</strong> <span>{{ Auth::user()->email ?: '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
</div>
@stop

@section('css')
<style>
    .gtk-account-password { color:#0f172a; }
    .gtk-account-password__wizard { border-top:3px solid #2563eb !important; }
    .gtk-account-password__wizard > .card-body { padding-left:1.35rem; padding-right:1.35rem; }
    .gtk-account-password__wizard .form-control { min-height:42px; }
    .gtk-account-password__wizard .input-group-append .btn { min-width:48px; }
    .gtk-account-password__identity { border-radius:14px; box-shadow:0 8px 20px rgba(15,23,42,.06); }
    .gtk-account-password__identity .card-body { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem 1rem; }
    .gtk-account-password__identity span { overflow-wrap:anywhere; }
    #pw-wizard .pw-stepper { display:flex; justify-content:space-between; position:relative; margin-bottom:.5rem; }
    #pw-wizard .pw-stepper::before { content:''; position:absolute; top:22px; left:12%; right:12%; height:3px; background:#e5e7eb; z-index:0; }
    #pw-wizard .pw-step { position:relative; z-index:1; text-align:center; flex:1; }
    #pw-wizard .pw-step__dot { width:46px; height:46px; border-radius:50%; background:#fff; border:3px solid #e5e7eb; color:#9ca3af;
        display:flex; align-items:center; justify-content:center; margin:0 auto 8px; font-size:1.05rem; transition:.25s; }
    #pw-wizard .pw-step__label { font-size:.82rem; color:#9ca3af; font-weight:600; }
    #pw-wizard .pw-step.is-active .pw-step__dot { border-color:#4F46E5; color:#4F46E5; box-shadow:0 0 0 4px rgba(79,70,229,.12); }
    #pw-wizard .pw-step.is-active .pw-step__label { color:#4F46E5; }
    #pw-wizard .pw-step.is-done .pw-step__dot { border-color:#22c55e; background:#22c55e; color:#fff; }
    #pw-wizard .pw-step.is-done .pw-step__label { color:#16a34a; }
    #pw-wizard .pw-panel { display:none; }
    #pw-wizard .pw-panel.is-active { display:block; animation:pwFade .25s ease; }
    @keyframes pwFade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
    #pw-wizard .pw-panel__title { font-weight:700; }
    #pw-wizard .pw-strength__bar { height:8px; border-radius:6px; background:#e5e7eb; overflow:hidden; }
    #pw-wizard .pw-strength__bar span { display:block; height:100%; width:0; transition:.3s; border-radius:6px; }
    #pw-wizard .pw-strength__label { font-size:.82rem; color:#6b7280; margin-top:6px; }
    #pw-wizard .pw-check li { padding:4px 0; color:#6b7280; font-size:.9rem; transition:.2s; }
    #pw-wizard .pw-check li.ok { color:#16a34a; }
    #pw-wizard .pw-check li.ok i::before { content:"\f058"; } /* check-circle */
    #pw-wizard .pw-check li i { width:18px; }
    #pw-wizard .pw-reminder { background:#eef2ff; border:1px solid #e0e7ff; border-radius:10px; padding:14px 16px; font-size:.88rem; color:#3730a3; }
    #pw-wizard .pw-reminder__title { font-weight:700; margin-bottom:6px; }
    #pw-wizard .card-footer { gap:.75rem; }
    #pw-wizard .card-footer > div { display:flex; gap:.5rem; }
    @media (max-width:575.98px) {
        .gtk-account-password__wizard > .card-body { padding-left:1rem; padding-right:1rem; }
        #pw-wizard .pw-stepper::before { left:16%; right:16%; top:19px; }
        #pw-wizard .pw-step__dot { width:40px; height:40px; margin-bottom:6px; }
        #pw-wizard .pw-step__label { font-size:.72rem; }
        #pw-wizard .pw-panel__title { font-size:1rem; }
        #pw-wizard .card-footer { align-items:stretch !important; flex-direction:column-reverse; }
        #pw-wizard .card-footer > div { width:100%; }
        #pw-wizard .card-footer .btn { flex:1 1 auto; white-space:normal; }
        .gtk-account-password__identity .card-body { grid-template-columns:1fr; }
    }
</style>
@stop

@section('js')
<script>
$(function () {
    var step = 1, total = 3;

    function show(n) {
        step = n;
        $('#pw-wizard .pw-panel').removeClass('is-active').filter('[data-panel="'+n+'"]').addClass('is-active');
        $('#pw-wizard .pw-step').each(function () {
            var s = +$(this).data('step');
            $(this).toggleClass('is-active', s === n).toggleClass('is-done', s < n);
        });
        $('#btnPrev').toggleClass('d-none', n === 1);
        $('#btnCancel').toggleClass('d-none', n !== 1);
        $('#btnNext').toggleClass('d-none', n === total);
        $('#btnSubmit').toggleClass('d-none', n !== total);
        validate();
    }

    // Password visibility toggle
    $('.pw-toggle').on('click', function () {
        var $inp = $('#' + $(this).data('target'));
        var type = $inp.attr('type') === 'password' ? 'text' : 'password';
        $inp.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Strength + checklist
    function rules(pw) {
        return {
            len: pw.length >= 8,
            letter: /[a-zA-Z]/.test(pw),
            number: /[0-9]/.test(pw),
            symbol: /[^a-zA-Z0-9]/.test(pw)
        };
    }
    function strength(pw) {
        var r = rules(pw), score = 0;
        if (r.len) score++;
        if (pw.length >= 12) score++;
        if (r.letter) score++;
        if (r.number) score++;
        if (r.symbol) score++;
        return Math.min(score, 5);
    }

    $('#password').on('input', function () {
        var pw = this.value, r = rules(pw);
        $('.pw-check li').each(function () {
            $(this).toggleClass('ok', !!r[$(this).data('rule')]);
        });
        var s = strength(pw);
        var pct = (s / 5) * 100;
        var color = s <= 2 ? '#ef4444' : (s === 3 ? '#f59e0b' : '#22c55e');
        var label = pw.length === 0 ? '—' : (s <= 2 ? 'Lemah' : (s === 3 ? 'Sedang' : 'Kuat'));
        $('#pwBar').css({ width: pct + '%', background: color });
        $('#pwLabel').text(label).css('color', color);
        validate();
    });

    $('#password_confirmation').on('input', validate);
    $('#current_password').on('input', validate);

    function validate() {
        if (step === 1) {
            $('#btnNext').prop('disabled', $('#current_password').val().length === 0);
        } else if (step === 2) {
            $('#btnNext').prop('disabled', !rules($('#password').val()).len);
        } else if (step === 3) {
            var pw = $('#password').val(), c = $('#password_confirmation').val();
            var match = c.length > 0 && pw === c;
            $('#pwMatch').html(c.length === 0 ? '' :
                (match ? '<span class="text-success"><i class="fas fa-check-circle"></i> Password cocok</span>'
                       : '<span class="text-danger"><i class="fas fa-times-circle"></i> Password belum sama</span>'));
            $('#btnSubmit').prop('disabled', !match);
        }
    }

    $('#btnNext').on('click', function () { if (step < total) show(step + 1); });
    $('#btnPrev').on('click', function () { if (step > 1) show(step - 1); });

    // Jika server mengembalikan error, lompat ke step yang relevan
    @if($errors->has('current_password'))
        show(1);
    @elseif($errors->has('password') || $errors->has('password_confirmation'))
        show(2);
    @else
        show(1);
    @endif
});
</script>
@stop
