@extends('adminlte::page')

@section('title', 'Wajib Ganti Password - SIMANSA')

@section('css')
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .loading-content {
        text-align: center;
    }
    .progress-bar {
        transition: width 0.3s, background-color 0.3s;
    }

    /* Full-screen blocking notice */
    .force-pwd-blocker {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: blockerFadeIn 0.4s ease;
    }
    @keyframes blockerFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .force-pwd-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        max-width: 520px;
        width: 90%;
        overflow: hidden;
        animation: cardSlideUp 0.5s ease 0.2s both;
    }
    @keyframes cardSlideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .force-pwd-header {
        padding: 30px 30px 20px;
        text-align: center;
    }
    .force-pwd-header .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 2rem;
    }
    .force-pwd-header .icon-circle.warning {
        background: #fff3cd;
        color: #856404;
    }
    .force-pwd-header .icon-circle.danger {
        background: #f8d7da;
        color: #721c24;
        animation: pulseIcon 2s ease-in-out infinite;
    }
    @keyframes pulseIcon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    .force-pwd-header h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 8px;
    }
    .force-pwd-header p {
        color: #6c757d;
        font-size: 0.95rem;
        margin: 0;
        line-height: 1.5;
    }
    .force-pwd-body {
        padding: 0 30px 30px;
    }
    .force-pwd-footer {
        padding: 0 30px 25px;
        text-align: center;
    }
    .reset-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8d7da;
        color: #721c24;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 12px;
    }

    /* Foto Upload Styling */
    .foto-frame {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        cursor: pointer;
    }
    .foto-ring {
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #00d4ff);
        animation: pulse-ring 2s ease-in-out infinite;
    }
    @keyframes pulse-ring {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.02); }
    }
    .foto-img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .foto-overlay {
        position: absolute;
        top: 5px;
        left: 5px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(0,0,0,0.6);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
    }
    .foto-frame:hover .foto-overlay {
        opacity: 1;
    }
    /* Cropper Modal */
    #cropperModal .modal-body { 
        padding: 0;
        max-height: 70vh;
        overflow: hidden;
    }
    #cropperPreview {
        max-width: 100%;
        display: block;
    }

    .setup-requirement-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 8px;
        margin-bottom: 12px;
    }

    /* Wizard step indicator */
    .wizard-steps {
        display: flex;
        align-items: flex-start;
        margin-bottom: 4px;
    }
    .wizard-step {
        flex: 1;
        text-align: center;
        position: relative;
    }
    .wizard-step:not(:first-child)::before {
        content: '';
        position: absolute;
        top: 22px;
        left: -50%;
        width: 100%;
        height: 3px;
        background: #dee2e6;
        z-index: 0;
        transition: background .3s;
    }
    .wizard-step-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        font-size: 1rem;
        position: relative;
        z-index: 1;
        transition: all .3s;
    }
    .wizard-step.active .wizard-step-icon {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
        box-shadow: 0 0 0 5px rgba(0,123,255,.15);
    }
    .wizard-step.done .wizard-step-icon {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }
    .wizard-step.done:not(:first-child)::before {
        background: #28a745;
    }
    .wizard-step-label {
        font-size: .8rem;
        font-weight: 600;
        color: #495057;
        line-height: 1.2;
    }
    .wizard-step-label small {
        display: block;
        font-weight: 400;
        color: #adb5bd;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .setup-requirement-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 12px;
        background: #f8fafc;
        font-size: .86rem;
        color: #334155;
    }
    .setup-requirement-item i { margin-right: 6px; }
    .setup-requirement-item.valid {
        border-color: #86efac;
        background: #f0fdf4;
        color: #166534;
    }
    .setup-requirement-item.invalid {
        border-color: #fda4af;
        background: #fff1f2;
        color: #9f1239;
    }

    /* Mode fokus: sembunyikan menu & sidebar selama wizard */
    .main-sidebar, #sidebar-overlay { display: none !important; }
    .content-wrapper, .main-header, .main-footer { margin-left: 0 !important; }
    [data-widget="pushmenu"] { display: none !important; }

    /* Pane wizard: tampil satu langkah per layar */
    .wizard-pane { display: none; }
    .wizard-pane.active { display: block; animation: paneIn .3s ease; }
    @keyframes paneIn {
        from { opacity: 0; transform: translateX(14px); }
        to { opacity: 1; transform: none; }
    }
    .wizard-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
        margin-top: 1rem;
    }
    .wizard-step { cursor: pointer; }

    @media (max-width: 575.98px) {
        .wizard-nav {
            align-items: stretch;
            flex-direction: column-reverse;
        }
        .wizard-nav .btn {
            min-height: 48px;
            width: 100%;
        }
    }
</style>
@stop

@section('content_header')
    <div class="row">
        <div class="col-12">
            <h1 class="m-0">
                @if($isAdminReset)
                    <i class="fas fa-exclamation-triangle text-danger"></i> Wajib Ganti Password
                @else
                    <i class="fas fa-user-shield text-primary"></i> Setup Akun
                @endif
            </h1>
        </div>
    </div>
@stop

@section('content')

{{-- ============================================================ --}}
{{-- FULL-SCREEN BLOCKING NOTICE (Admin Reset only, shown once)   --}}
{{-- ============================================================ --}}
@if($isAdminReset)
<div class="force-pwd-blocker" id="blockerOverlay">
    <div class="force-pwd-card">
        <div class="force-pwd-header">
            <div class="icon-circle danger">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Password Anda Telah Di-Reset</h2>
            <p>
                Demi keamanan akun, password Anda telah di-reset oleh administrator.
                <br>Anda <strong>WAJIB</strong> membuat password baru sekarang untuk melanjutkan.
            </p>
            <div class="reset-info-badge">
                <i class="fas fa-user-shield"></i>
                Di-reset oleh <strong>{{ $resetBy }}</strong> &mdash; {{ $resetAt?->translatedFormat('d M Y, H:i') ?? '-' }}
            </div>
        </div>
        <div class="force-pwd-footer">
            <button type="button" class="btn btn-danger btn-lg btn-block" id="btnDismissBlocker">
                <i class="fas fa-key"></i> Ya, Saya Mengerti &mdash; Buat Password Baru
            </button>
            <small class="text-muted d-block mt-2">
                Anda tidak dapat mengakses halaman lain sampai password diganti.
            </small>
        </div>
    </div>
</div>
@endif

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        {{-- Alert Banner (always visible in form) --}}
        @if($isAdminReset)
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-left: 5px solid #721c24 !important;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mr-3 mt-1"></i>
                    <div>
                        <h5 class="alert-heading mb-1 font-weight-bold">
                            <i class="fas fa-lock"></i> Ganti Password Wajib!
                        </h5>
                        <p class="mb-1">Password Anda telah di-reset oleh <strong>{{ $resetBy }}</strong> pada <strong>{{ $resetAt?->translatedFormat('d M Y, H:i') ?? '-' }}</strong>.</p>
                        <p class="mb-0 text-sm">Password saat ini adalah NISN Anda. Segera ganti untuk mengamankan akun.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm mb-3" style="border-left: 5px solid #17a2b8 !important;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle fa-2x text-info mr-3 mt-1"></i>
                    <div>
                        <h5 class="alert-heading mb-1 font-weight-bold">Selamat Datang, {{ $user->name }}!</h5>
                        <p class="mb-0">Ini adalah login pertama Anda. Silakan buat password baru dan lengkapi profil sebelum melanjutkan.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Form Card --}}
        <div class="card card-{{ $isAdminReset ? 'danger' : 'primary' }} card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-shield"></i> Setup Akun
                </h3>
                <div class="card-tools">
                    <small class="text-muted">{{ $user->name }} &middot; NISN {{ $user->username }}</small>
                </div>
            </div>
            <form action="{{ route('siswa.force-setup.update') }}" method="POST" id="setupForm" data-no-overlay>
                @csrf
                <div class="card-body">
                    {{-- Wizard step indicator --}}
                    <div class="wizard-steps mb-4">
                        <div class="wizard-step active" id="wstep1">
                            <div class="wizard-step-icon"><i class="fas fa-key" data-icon="fas fa-key"></i></div>
                            <div class="wizard-step-label">Password Baru<small>Wajib</small></div>
                        </div>
                        @if(!$isAdminReset || $emailMustChange)
                        <div class="wizard-step" id="wstep2">
                            <div class="wizard-step-icon"><i class="fas fa-envelope" data-icon="fas fa-envelope"></i></div>
                            <div class="wizard-step-label">Email<small>{{ $emailMustChange ? 'Wajib' : 'Opsional' }}</small></div>
                        </div>
                        @endif
                        <div class="wizard-step" id="wstep3">
                            <div class="wizard-step-icon"><i class="fas fa-camera" data-icon="fas fa-camera"></i></div>
                            <div class="wizard-step-label">Foto Profil<small>Opsional</small></div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ==================== --}}
                    {{-- STEP 1: PASSWORD     --}}
                    {{-- ==================== --}}
                    <div class="wizard-pane active" id="pane1">
                    <div class="card card-outline card-{{ $isAdminReset ? 'danger' : 'warning' }} mb-0">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lock"></i> Buat Password Baru
                                <span class="badge badge-danger ml-2">WAJIB</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password"><i class="fas fa-lock text-danger"></i> Password Baru <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="password"
                                                   class="form-control form-control-lg @error('password') is-invalid @enderror"
                                                   placeholder="Minimal 8 karakter"
                                                   required minlength="8" autofocus>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                    <i class="fas fa-eye" id="password-icon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                        {{-- Password Strength --}}
                                        <div class="mt-2">
                                            <small class="text-muted">Kekuatan Password:</small>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" id="password-strength" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <small id="password-hint" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation"><i class="fas fa-lock text-danger"></i> Konfirmasi Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                   class="form-control form-control-lg"
                                                   placeholder="Ulangi password baru"
                                                   required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                                    <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="password-match" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="callout callout-warning py-2 mb-3">
                                <small><i class="fas fa-exclamation-triangle"></i> Jangan gunakan NISN, tanggal lahir, atau nama sendiri. Kombinasikan huruf besar/kecil, angka &amp; simbol.</small>
                            </div>
                            <div class="setup-requirement-list mb-0">
                                <div class="setup-requirement-item invalid" id="reqPasswordLength">
                                    <i class="fas fa-times-circle"></i> Password minimal 8 karakter
                                </div>
                                <div class="setup-requirement-item invalid" id="reqPasswordMatch">
                                    <i class="fas fa-times-circle"></i> Konfirmasi password sama persis
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wizard-nav">
                        <span></span>
                        <button type="button" class="btn btn-primary btn-lg" data-nav="next" id="btnNext1">
                            Lanjut <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    </div>{{-- /pane1 --}}

                    {{-- ==================== --}}
                    {{-- STEP 2: EMAIL        --}}
                    {{-- Tampil jika: first login asli, atau admin reset + email masih default --}}
                    {{-- ==================== --}}
                    @if(!$isAdminReset || $emailMustChange)
                    <div class="wizard-pane" id="pane2">
                    <div class="card card-outline card-info mb-0">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-envelope"></i>
                                @if($emailMustChange)
                                    Email Wajib Diganti
                                    <span class="badge badge-danger ml-2">WAJIB</span>
                                @else
                                    Email
                                    <small class="text-muted">(Opsional &mdash; email lama tetap dipertahankan)</small>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($emailMustChange)
                                <div class="alert alert-warning mb-3 py-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Demi keamanan, email lama tidak boleh dipakai lagi. Masukkan email aktif milik Anda.
                                </div>
                            @else
                                <div class="alert alert-info mb-3 py-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Email Anda (<strong>{{ $user->email }}</strong>) tetap aktif. Anda boleh menggantinya atau biarkan kosong.
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope text-primary"></i>
                                            Email Aktif
                                            @if($emailMustChange)<span class="text-danger">*</span>@endif
                                        </label>
                                        <input type="hidden" id="initialEmail" value="{{ strtolower((string) $user->email) }}">
                                        <input type="hidden" id="emailMustChange" value="{{ $emailMustChange ? '1' : '0' }}">
                                        <input type="email" name="email" id="email"
                                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                                               value="{{ old('email', $emailMustChange ? '' : $user->email) }}"
                                               placeholder="{{ $emailMustChange ? 'Masukkan email aktif Anda' : 'Kosongkan untuk mempertahankan email lama' }}"
                                               {{ $emailMustChange ? 'required' : '' }}>
                                        <small class="text-muted">Email digunakan untuk reset password dan notifikasi penting. Setelah wizard selesai, link verifikasi akan dikirim ke email ini.</small>
                                        @if(!empty($user->email) && $emailMustChange)
                                            <small class="text-danger d-block mt-1">
                                                Email sebelumnya: <strong>{{ $user->email }}</strong>
                                            </small>
                                        @endif
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @if($emailMustChange)
                            <div class="setup-requirement-list mb-0">
                                <div class="setup-requirement-item invalid" id="reqEmailChanged">
                                    <i class="fas fa-times-circle"></i> Email baru diisi &amp; berbeda dari email lama
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-outline-secondary btn-lg" data-nav="prev">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" data-nav="next" id="btnNext2">
                            Lanjut <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    </div>{{-- /pane2 --}}
                    @endif

                    {{-- ==================== --}}
                    {{-- STEP 3: FOTO         --}}
                    {{-- ==================== --}}
                    <div class="wizard-pane" id="pane3">
                    <div class="card card-outline card-secondary mb-0">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-camera"></i> Foto Profile
                                <small class="text-muted">(Opsional &mdash; boleh dilewati)</small>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <div class="foto-frame" id="fotoFrame" title="Klik untuk upload foto">
                                        <div class="foto-ring"></div>
                                        <img id="previewFoto"
                                             src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background={{ $user->siswa && $user->siswa->jenis_kelamin == 'P' ? 'e83e8c' : '007bff' }}&color=fff"
                                             class="foto-img"
                                             alt="Foto Profile">
                                        <div class="foto-overlay">
                                            <i class="fas fa-camera fa-2x mb-2"></i>
                                            <span>Upload</span>
                                        </div>
                                    </div>
                                    <input type="file" id="fotoInput" class="d-none" accept="image/jpeg,image/jpg,image/png">
                                </div>
                                <div class="col-md-8">
                                    <p class="text-muted mb-2">Klik gambar atau tombol untuk memilih foto:</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseFoto">
                                        <i class="fas fa-upload"></i> Pilih Foto
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted d-block"><i class="fas fa-info-circle"></i> Format: JPG/PNG, Maks 2MB</small>
                                    </div>
                                    <div id="fotoStatus" class="mt-2" style="display: none;">
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Foto berhasil diupload</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-outline-secondary btn-lg" data-nav="prev">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-{{ $isAdminReset ? 'danger' : 'primary' }} btn-lg flex-grow-1" id="submitBtn">
                            <i class="fas fa-shield-alt"></i> Simpan &amp; Amankan Akun Saya
                        </button>
                    </div>
                    </div>{{-- /pane3 --}}
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-3 text-white">Menyimpan data...</p>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-crop-alt"></i> Crop Foto Profile
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="cropperPreview" src="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnCropSave">
                    <i class="fas fa-check"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- Cropper.js -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
// Fitur utama tidak boleh gagal hanya karena CDN notifikasi tidak termuat.
if (window.toastr) {
    window.toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000",
        "extendedTimeOut": "1000"
    };
}

function showSetupNotification(type, message) {
    if (window.toastr && typeof window.toastr[type] === 'function') {
        window.toastr[type](message);
        return;
    }

    console[type === 'error' ? 'error' : 'log'](message);
}

// Dismiss blocker overlay
$('#btnDismissBlocker').on('click', function() {
    $('#blockerOverlay').fadeOut(400, function() {
        $(this).remove();
        // Focus on password field
        $('#password').focus();
    });
});

// ===== Navigasi wizard (satu pane per layar) =====
var wizardPanes = ['pane1', 'pane2', 'pane3'].filter(function(id) { return document.getElementById(id); });
var wizardStepIds = { pane1: 'wstep1', pane2: 'wstep2', pane3: 'wstep3' };
var currentPane = 0;

function showPane(index) {
    if (index < 0 || index >= wizardPanes.length) return;
    currentPane = index;
    wizardPanes.forEach(function(id, i) {
        document.getElementById(id).classList.toggle('active', i === index);
    });
    updateSetupValidationState();
    try {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        window.scrollTo(0, 0);
    }
}

function canLeavePane(index) {
    var paneId = wizardPanes[index];
    var nextBtn = document.querySelector('#' + paneId + ' [data-nav="next"]');
    if (!nextBtn) return true;

    updateSetupValidationState();
    if (nextBtn.getAttribute('data-form-valid') === 'true') return true;

    var pane = document.getElementById(paneId);
    var invalidField = pane ? pane.querySelector(':invalid') : null;
    if (invalidField) {
        invalidField.focus();
        if (typeof invalidField.reportValidity === 'function') invalidField.reportValidity();
    }
    return false;
}

$(document).on('click', '[data-nav="next"]', function() {
    if (canLeavePane(currentPane)) showPane(currentPane + 1);
});
$(document).on('click', '[data-nav="prev"]', function() {
    showPane(currentPane - 1);
});

// Klik indikator step: mundur bebas, maju hanya jika step sebelumnya valid
$('.wizard-step').on('click', function() {
    var stepId = this.id;
    var paneId = null;
    Object.keys(wizardStepIds).some(function(p) {
        if (wizardStepIds[p] === stepId) {
            paneId = p;
            return true;
        }
        return false;
    });
    var target = wizardPanes.indexOf(paneId);
    if (target < 0) return;
    if (target > currentPane) {
        for (var i = currentPane; i < target; i++) {
            if (!canLeavePane(i)) return;
        }
    }
    showPane(target);
});

var cropper = null;

// Foto upload handlers
$('#fotoFrame, #btnChooseFoto').on('click', function() {
    $('#fotoInput').click();
});

$('#fotoInput').on('change', function() {
    var file = this.files[0];
    if (!file) return;
    
    // Validate file type
    var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (allowedTypes.indexOf(file.type) === -1) {
        showSetupNotification('error', 'Format file harus JPG atau PNG!');
        this.value = '';
        return;
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        showSetupNotification('error', 'Ukuran file maksimal 2MB!');
        this.value = '';
        return;
    }
    
    // Read and show in cropper
    var reader = new FileReader();
    reader.onload = function(e) {
        $('#cropperPreview').attr('src', e.target.result);
        $('#cropperModal').modal('show');
    };
    reader.readAsDataURL(file);
});

// Initialize cropper when modal is shown
$('#cropperModal').on('shown.bs.modal', function() {
    if (typeof window.Cropper !== 'function') {
        $('#cropperModal').modal('hide');
        showSetupNotification('error', 'Editor foto belum termuat. Silakan coba lagi atau lewati foto profil.');
        return;
    }

    var image = document.getElementById('cropperPreview');
    if (cropper) {
        cropper.destroy();
    }
    cropper = new window.Cropper(image, {
        aspectRatio: 1,
        viewMode: 2,
        dragMode: 'move',
        autoCropArea: 0.8,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
});

// Destroy cropper when modal is hidden
$('#cropperModal').on('hidden.bs.modal', function() {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    $('#fotoInput').val('');
});

// Save cropped image
$('#btnCropSave').on('click', function() {
    if (!cropper) return;
    
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    
    var canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    var base64 = canvas.toDataURL('image/jpeg', 0.9);
    
    // Upload via AJAX
    $.ajax({
        url: '{{ route("siswa.profile.foto.upload") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            cropped_image: base64
        },
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                // Update preview images
                $('#previewFoto').attr('src', response.foto_url);
                $('#fotoStatus').show();
                $('#cropperModal').modal('hide');
                window.fotoUploaded = true;
                updateSetupValidationState();
                showSetupNotification('success', 'Foto berhasil diupload!');
            } else {
                showSetupNotification('error', 'Gagal: ' + (response.message || 'Terjadi kesalahan tidak diketahui'));
            }
        },
        error: function(xhr, status, error) {
            console.log('Error:', xhr.status, xhr.responseText);
            var msg = 'Terjadi kesalahan';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.status === 419) {
                msg = 'Sesi telah berakhir. Silakan refresh halaman.';
            } else if (xhr.status === 404) {
                msg = 'Data siswa tidak ditemukan.';
            } else if (xhr.status === 500) {
                msg = 'Server error. Silakan coba lagi.';
            } else if (error) {
                msg = error;
            }
            showSetupNotification('error', msg);
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Simpan Foto');
        }
    });
});

function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    var strengthBar = document.getElementById('password-strength');
    var hint = document.getElementById('password-hint');
    
    var strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 10;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 30) {
        strengthBar.className = 'progress-bar bg-danger';
        hint.textContent = 'Lemah - Tambahkan huruf besar, angka, atau simbol';
        hint.className = 'text-danger';
    } else if (strength < 60) {
        strengthBar.className = 'progress-bar bg-warning';
        hint.textContent = 'Sedang - Bisa lebih kuat lagi';
        hint.className = 'text-warning';
    } else if (strength < 80) {
        strengthBar.className = 'progress-bar bg-info';
        hint.textContent = 'Cukup Kuat';
        hint.className = 'text-info';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        hint.textContent = 'Sangat Kuat!';
        hint.className = 'text-success';
    }
    
    checkPasswordMatch();
});

// Password match checker
document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    var password = document.getElementById('password').value;
    var confirmationField = document.getElementById('password_confirmation');
    var confirmation = confirmationField.value;
    var matchDiv = document.getElementById('password-match');
    
    if (confirmation === '') {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirmation) {
        matchDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Password cocok</span>';
    } else {
        matchDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Password tidak cocok</span>';
    }

    updateSetupValidationState();
}

function setRequirementState(el, isValid, validText, invalidText) {
    if (!el) return;
    el.classList.remove('valid', 'invalid');
    el.classList.add(isValid ? 'valid' : 'invalid');
    el.innerHTML = '<i class="fas ' + (isValid ? 'fa-check-circle' : 'fa-times-circle') + '"></i>' + (isValid ? validText : invalidText);
}

function updateSetupValidationState() {
    var initialEmailField = document.getElementById('initialEmail');
    var emailField = document.getElementById('email');
    var emailMustChangeField = document.getElementById('emailMustChange');
    var initialEmail = ((initialEmailField ? initialEmailField.value : '') || '').trim().toLowerCase();
    var currentEmail = ((emailField ? emailField.value : '') || '').trim().toLowerCase();
    var emailMustChange = !!emailMustChangeField && emailMustChangeField.value === '1';
    var password = document.getElementById('password').value;
    var confirmationField = document.getElementById('password_confirmation');
    var confirmation = confirmationField.value;
    var submitBtn = document.getElementById('submitBtn');

    var isEmailValid;
    if (emailMustChange) {
        // Email wajib diisi, harus berbeda dari email lama
        isEmailValid = currentEmail !== '' && (initialEmail === '' || currentEmail !== initialEmail);
    } else {
        // Email opsional — field mungkin tidak tampil atau boleh sama dengan email lama
        isEmailValid = true;
    }

    var isPasswordLength = password.length >= 8;
    var isPasswordMatch = isPasswordLength && confirmation !== '' && password === confirmation;

    confirmationField.setCustomValidity(
        confirmation !== '' && password !== confirmation ? 'Konfirmasi password tidak sama.' : ''
    );
    if (emailField) {
        emailField.setCustomValidity(
            emailMustChange && currentEmail !== '' && initialEmail !== '' && currentEmail === initialEmail
                ? 'Gunakan email aktif yang berbeda dari email lama.'
                : ''
        );
    }

    setRequirementState(
        document.getElementById('reqPasswordLength'),
        isPasswordLength,
        'Password minimal 8 karakter',
        'Password minimal 8 karakter'
    );
    setRequirementState(
        document.getElementById('reqPasswordMatch'),
        isPasswordMatch,
        'Konfirmasi password sama persis',
        'Konfirmasi password sama persis'
    );
    if (emailMustChange) {
        setRequirementState(
            document.getElementById('reqEmailChanged'),
            isEmailValid,
            'Email baru diisi & berbeda dari email lama',
            'Email baru diisi & berbeda dari email lama'
        );
    }

    // Wizard step states: done dari validasi, active mengikuti pane yang tampil
    var activePaneId = wizardPanes[currentPane];
    setWizardStep('wstep1', isPasswordMatch, activePaneId === 'pane1');
    var emailFieldExists = !!document.getElementById('email');
    if (emailFieldExists) {
        var emailDone = emailMustChange ? isEmailValid : currentEmail !== '';
        setWizardStep('wstep2', emailDone, activePaneId === 'pane2');
    }
    setWizardStep('wstep3', window.fotoUploaded === true, activePaneId === 'pane3');

    var btnNext1 = document.getElementById('btnNext1');
    if (btnNext1) btnNext1.setAttribute('data-form-valid', isPasswordMatch ? 'true' : 'false');
    var btnNext2 = document.getElementById('btnNext2');
    if (btnNext2) btnNext2.setAttribute('data-form-valid', (!emailMustChange || isEmailValid) ? 'true' : 'false');

    // Tombol final sengaja tidak dikunci lewat JavaScript. Browser akan menjalankan
    // validasi native saat submit, termasuk saat autofill mobile tidak memicu event input.
    if (submitBtn) submitBtn.setAttribute('data-form-valid', (isEmailValid && isPasswordMatch) ? 'true' : 'false');
}

function setWizardStep(id, isDone, isActive) {
    var step = document.getElementById(id);
    if (!step) return;
    var icon = step.querySelector('.wizard-step-icon i');
    step.classList.remove('done', 'active');
    if (isDone) {
        step.classList.add('done');
        icon.className = 'fas fa-check';
    } else {
        icon.className = icon.dataset.icon;
        if (isActive) step.classList.add('active');
    }
}

var setupEmailField = document.getElementById('email');
if (setupEmailField) {
    setupEmailField.addEventListener('input', updateSetupValidationState);
    setupEmailField.addEventListener('change', updateSetupValidationState);
}
document.getElementById('password').addEventListener('input', updateSetupValidationState);
document.getElementById('password').addEventListener('change', updateSetupValidationState);
document.getElementById('password_confirmation').addEventListener('input', updateSetupValidationState);
document.getElementById('password_confirmation').addEventListener('change', updateSetupValidationState);
updateSetupValidationState();

// Form submit with loading
document.getElementById('setupForm').addEventListener('submit', function(event) {
    var form = this;
    var password = document.getElementById('password').value;
    var confirmation = document.getElementById('password_confirmation').value;

    updateSetupValidationState();

    var passwordInvalid = password.length < 8 || password !== confirmation;
    if (passwordInvalid || !form.checkValidity()) {
        event.preventDefault();
        showPane(passwordInvalid ? 0 : wizardPanes.indexOf('pane2'));
        if (typeof form.reportValidity === 'function') form.reportValidity();
        return;
    }

    var submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    document.getElementById('loadingOverlay').style.display = 'flex';
});
</script>
@stop
