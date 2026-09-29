@extends('adminlte::page')

@section('title', 'CBTman Secure Exam Browser')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .exam-page-intro {
        margin-bottom: 1rem;
    }
    .exam-page-intro .lead {
        margin-bottom: 0;
    }
    .top-actions .btn {
        margin-left: .35rem;
    }
    .static-config-meta {
        font-size: .92rem;
    }
    .card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .card-header {
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .card-primary .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    .card-info .card-header {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    }
    .card-success .card-header {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    .card-warning .card-header {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    .card-danger .card-header {
        background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
    }
    .card-secondary .card-header {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
    }
    .custom-switch .custom-control-label {
        cursor: pointer;
        font-weight: 500;
    }
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    .upload-area:hover {
        border-color: #007bff;
        background: #e8f0fe;
    }
    .logo-preview {
        max-width: 200px;
        max-height: 100px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .api-url-box {
        background: #1e1e2e;
        color: #a6e3a1;
        border-radius: 8px;
        padding: 15px 20px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        position: relative;
    }
    .api-url-box .copy-btn {
        position: absolute;
        right: 10px;
        top: 10px;
    }
    .feature-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .section-divider {
        border-top: 2px solid #e9ecef;
        margin: 20px 0;
    }
    .info-callout {
        background: #e8f4fd;
        border-left: 4px solid #17a2b8;
        padding: 15px;
        border-radius: 0 8px 8px 0;
        margin-bottom: 20px;
    }
    .config-preview {
        background: #1e1e2e;
        color: #cdd6f4;
        border-radius: 8px;
        padding: 20px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
    }
    .cbtman-guide-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 55px rgba(15, 23, 42, .25);
    }
    .cbtman-guide-modal .modal-header {
        background: linear-gradient(135deg, #4338ca, #2563eb);
        padding: 1.1rem 1.35rem;
    }
    .cbtman-guide-modal .modal-body {
        background: #f8fafc;
        color: #334155;
        font-size: .82rem;
        line-height: 1.55;
        padding: 1.15rem;
    }
    .cbtman-guide-modal .guide-intro {
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        border-radius: 11px;
        padding: .75rem .85rem;
        margin-bottom: .85rem;
    }
    .cbtman-guide-modal .guide-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        padding: .8rem .9rem;
        margin-bottom: .7rem;
    }
    .cbtman-guide-modal .guide-section h6 {
        color: #1e293b;
        font-size: .88rem;
        font-weight: 700;
        margin: 0 0 .45rem;
    }
    .cbtman-guide-modal .guide-section h6 i {
        color: #4f46e5;
        width: 20px;
        text-align: center;
        margin-right: .2rem;
    }
    .cbtman-guide-modal ol,
    .cbtman-guide-modal ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    .cbtman-guide-modal li {
        margin-bottom: .28rem;
    }
    .cbtman-guide-modal .guide-note {
        border-left: 3px solid #f59e0b;
        background: #fffbeb;
        border-radius: 0 8px 8px 0;
        padding: .55rem .7rem;
        margin-top: .55rem;
    }
    .cbtman-guide-modal code {
        color: #4338ca;
        background: #eef2ff;
        padding: .1rem .25rem;
        border-radius: 4px;
    }
</style>
@endsection

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-7">
        <h1><i class="fas fa-desktop"></i> CBTman <small class="text-muted">Secure Exam Browser</small></h1>
        <p class="text-muted exam-page-intro">Kelola konfigurasi aplikasi CBTman untuk ujian Moodle siswa</p>
    </div>
    <div class="col-sm-5 text-sm-right top-actions">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#cbtmanGuideModal">
            <i class="fas fa-book-open"></i> Panduan CBTman
        </button>
        <button type="button" class="btn btn-info btn-sm" onclick="previewConfig()">
            <i class="fas fa-eye"></i> Preview Config
        </button>
        <a href="{{ \App\Models\ExamBrowserSetting::staticConfigUrl() }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-file-code"></i> Buka File Config
        </a>
    </div>
</div>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<!-- Info Callout -->
<div class="info-callout">
    <strong><i class="fas fa-info-circle"></i> Tentang CBTman</strong>
    <p class="mb-0 mt-1">CBTman adalah secure exam browser Android untuk Moodle. Aplikasi menggunakan fullscreen, FLAG_SECURE, DND wajib, screen pinning best-effort, pembatasan copy-paste, dan perlindungan overlay sesuai kemampuan perangkat BYOD. Konfigurasi di bawah ini dibaca aplikasi melalui snapshot statis Simansa.</p>
</div>

<div class="alert alert-primary d-flex align-items-center justify-content-between flex-wrap">
    <div class="mr-3 mb-2 mb-md-0">
        <strong>Profil CBTman</strong><br>
        <small>Mengaktifkan default keamanan CBTman tanpa mengubah URL Moodle, logo, atau password yang tersimpan.</small>
    </div>
    <form action="{{ route('admin.exam-browser.apply-cbtman-profile') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary" onclick="return confirm('Terapkan profil keamanan CBTman?')">
            <i class="fas fa-shield-alt"></i> Terapkan Profil CBTman
        </button>
    </form>
</div>

<!-- Static Config Snapshot Info -->
<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-code"></i> Config Statis untuk Mobile App</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Aplikasi CBTman membaca <strong>file config statis</strong> ini secara langsung
            (disajikan web server tanpa proses PHP/database, sehingga aman dari overload saat
            ratusan perangkat membuka aplikasi bersamaan). File diperbarui otomatis setiap kali
            pengaturan di bawah disimpan. Password disimpan dalam bentuk <strong>hash bcrypt</strong> —
            tidak pernah dikirim dalam bentuk teks asli.
        </p>
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="text-muted">URL Config Statis (dipakai aplikasi)</label>
                <div class="api-url-box">
                    GET {{ \App\Models\ExamBrowserSetting::staticConfigUrl() }}
                    <button class="btn btn-xs btn-outline-light copy-btn" type="button" onclick="copyToClipboard('{{ \App\Models\ExamBrowserSetting::staticConfigUrl() }}')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="text-muted">Status File</label>
                <div class="static-config-meta">
                    @php($generatedAt = \App\Models\ExamBrowserSetting::staticConfigGeneratedAt())
                    @if($generatedAt)
                        <span class="badge badge-success">Tersedia</span>
                        <small class="text-muted d-block mt-1">Dibuat: {{ $generatedAt->translatedFormat('d M Y H:i:s') }}</small>
                    @else
                        <span class="badge badge-warning">Belum dibuat</span>
                        <small class="text-muted d-block mt-1">Klik "Buat Ulang" atau simpan pengaturan.</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center">
            <form action="{{ route('admin.exam-browser.regenerate-config') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary mr-2 mb-2">
                    <i class="fas fa-sync-alt"></i> Buat Ulang File Config
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-secondary mb-2" onclick="previewConfig()">
                <i class="fas fa-eye"></i> Pratinjau Isi Config
            </button>
        </div>
    </div>
</div>

<form action="{{ route('admin.exam-browser.update') }}" method="POST" enctype="multipart/form-data" id="examBrowserForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- General Settings -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan Umum</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Aplikasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                    name="app_name" value="{{ old('app_name', $setting->app_name) }}" required>
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Nama yang ditampilkan di aplikasi</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Sekolah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('school_name') is-invalid @enderror" 
                                    name="school_name" value="{{ old('school_name', $setting->school_name) }}" required>
                                @error('school_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>URL Moodle <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                            </div>
                            <input type="url" class="form-control @error('moodle_url') is-invalid @enderror" 
                                name="moodle_url" value="{{ old('moodle_url', $setting->moodle_url) }}" 
                                placeholder="https://elearning.man1metro.sch.id" required>
                            @error('moodle_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">URL lengkap Moodle yang akan dimuat di WebView aplikasi</small>
                    </div>

                    <div class="form-group">
                        <label>User Agent</label>
                        <input type="text" class="form-control @error('user_agent') is-invalid @enderror" 
                            name="user_agent" value="{{ old('user_agent', $setting->user_agent) }}">
                        @error('user_agent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">User-Agent yang dikirim CBTman ke Moodle. Profil CBTman menggunakan penanda MAN1Metro-CBT-SecureBrowser.</small>
                    </div>

                    <div class="form-group">
                        <label>Tema CBTman</label>
                        @php($themePreset = old('theme_preset', $setting->theme_preset ?? 'pink_comfort'))
                        <select class="form-control @error('theme_preset') is-invalid @enderror" name="theme_preset">
                            <option value="pink_comfort" @selected($themePreset === 'pink_comfort')>Pink Comfort</option>
                            <option value="rose_soft" @selected($themePreset === 'rose_soft')>Rose Soft</option>
                            <option value="blue_academic" @selected($themePreset === 'blue_academic')>Blue Academic</option>
                            <option value="default" @selected($themePreset === 'default')>Default MAN 1 Metro</option>
                        </select>
                        <small class="text-muted">Mengubah warna UI native CBTman. Tampilan Moodle tidak diubah.</small>
                        @error('theme_preset')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Versi Minimum Aplikasi</label>
                        <input type="text" class="form-control" name="minimum_app_version" 
                            value="{{ old('minimum_app_version', $setting->minimum_app_version) }}" placeholder="1.0.0">
                        <small class="text-muted">Siswa dengan versi app di bawah ini akan diminta update</small>
                    </div>

                    <div class="form-group">
                        <label>Tautan Update APK</label>
                        <input type="url" class="form-control" name="update_url"
                            value="{{ old('update_url', $setting->update_url) }}"
                            placeholder="https://simansa.man1metro.sch.id/downloads/cbtman">
                        <small class="text-muted">
                            Jika versi minimum dinaikkan, CBTman akan memblokir akses sampai siswa membuka tautan ini dan memasang versi terbaru.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Saluran Update Aktif</label>
                        <select class="form-control" name="update_channel">
                            <option value="direct" @selected(old('update_channel', $setting->update_channel ?? 'direct') === 'direct')>
                                Download Center Simansa
                            </option>
                            <option value="play" @selected(old('update_channel', $setting->update_channel ?? 'direct') === 'play')>
                                Google Play Store
                            </option>
                        </select>
                        <small class="text-muted">Pilih sumber yang dibuka ketika versi aplikasi wajib diperbarui.</small>
                    </div>

                    <div class="form-group">
                        <label>URL Google Play Store</label>
                        <input type="url" class="form-control" name="play_store_url"
                            value="{{ old('play_store_url', $setting->play_store_url ?? '') }}"
                            placeholder="https://play.google.com/store/apps/details?id=id.sch.man1metro.cbtman">
                        <small class="text-muted">Isi sejak awal agar dapat langsung dipakai setelah aplikasi disetujui Google Play.</small>
                    </div>
                </div>
            </div>

            <!-- Password & Security -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt"></i> Keamanan & Password</h3>
                </div>
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-history text-secondary mr-1"></i> Password ExaManmet (Lama)</h5>
                    <div class="alert alert-secondary py-2 small">
                        Password pada bagian ini hanya digunakan ExaManmet. Pertahankan selama masa transisi sebelum ExaManmet dihentikan.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-sign-in-alt text-success"></i> Password Masuk ExaManmet</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="app_password" 
                                        value="{{ old('app_password', $setting->app_password) }}" 
                                        placeholder="Kosongkan jika tidak pakai password">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('app_password')">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Password lama untuk membuka ExaManmet.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-sign-out-alt text-danger"></i> Password Keluar ExaManmet</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="exit_password" 
                                        value="{{ old('exit_password', $setting->exit_password) }}" 
                                        placeholder="Kosongkan jika tidak pakai password">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('exit_password')">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Password lama untuk keluar dari ExaManmet.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-user-shield text-warning"></i> Password Unlock ExaManmet</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="supervisor_password" 
                                        value="{{ old('supervisor_password', $setting->supervisor_password) }}" 
                                        placeholder="Wajib diisi untuk fitur unlock offline">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('supervisor_password')">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Password lama pengawas untuk unlock ExaManmet.</small>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <h5 class="mb-3"><i class="fas fa-shield-alt text-primary mr-1"></i> Password Khusus CBTman</h5>
                    <div class="alert alert-info py-2 small">
                        Password di area ini hanya digunakan CBTman. Password ExaManmet lama di atas tetap dipertahankan.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Password Masuk CBTman</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="cbtman_app_password"
                                        value="{{ old('cbtman_app_password', is_string($setting->cbtman_app_password) && str_starts_with($setting->cbtman_app_password, '$2') ? '' : $setting->cbtman_app_password) }}"
                                        placeholder="Kosongkan untuk mempertahankan password saat ini">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('cbtman_app_password')"><i class="fas fa-random"></i></button>
                                    </div>
                                </div>
                                <small class="text-muted">Untuk membuka CBTman.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Password Keluar CBTman</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="cbtman_exit_password"
                                        value="{{ old('cbtman_exit_password', is_string($setting->cbtman_exit_password) && str_starts_with($setting->cbtman_exit_password, '$2') ? '' : $setting->cbtman_exit_password) }}"
                                        placeholder="Kosongkan untuk mempertahankan password saat ini">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('cbtman_exit_password')"><i class="fas fa-random"></i></button>
                                    </div>
                                </div>
                                <small class="text-muted">Untuk keluar dari ujian CBTman.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Password Unlock CBTman</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="cbtman_supervisor_password"
                                        value="{{ old('cbtman_supervisor_password', is_string($setting->cbtman_supervisor_password) && str_starts_with($setting->cbtman_supervisor_password, '$2') ? '' : $setting->cbtman_supervisor_password) }}"
                                        placeholder="Kosongkan untuk mempertahankan password saat ini">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('cbtman_supervisor_password')"><i class="fas fa-random"></i></button>
                                    </div>
                                </div>
                                <small class="text-muted">Untuk membuka kunci pelanggaran.</small>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <h6 class="text-bold mb-3"><i class="fas fa-lock"></i> Pengaturan Keamanan Perangkat</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="allow_screenshot" name="allow_screenshot" value="1"
                                    {{ old('allow_screenshot', $setting->allow_screenshot) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_screenshot">
                                    <i class="fas fa-camera text-warning"></i> Izinkan Screenshot
                                </label>
                                <br><small class="text-muted">Jika dimatikan, siswa tidak bisa screenshot/screen record</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="allow_clipboard" name="allow_clipboard" value="1"
                                    {{ old('allow_clipboard', $setting->allow_clipboard) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_clipboard">
                                    <i class="fas fa-clipboard text-info"></i> Izinkan Clipboard (Copy/Paste)
                                </label>
                                <br><small class="text-muted">Jika dimatikan, siswa tidak bisa copy/paste teks</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="allow_navigation" name="allow_navigation" value="1"
                                    {{ old('allow_navigation', $setting->allow_navigation) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_navigation">
                                    <i class="fas fa-compass text-primary"></i> Izinkan Navigasi ke URL Lain
                                </label>
                                <br><small class="text-muted">Jika dimatikan, hanya bisa akses Moodle URL saja</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="allow_reload" name="allow_reload" value="1"
                                    {{ old('allow_reload', $setting->allow_reload) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_reload">
                                    <i class="fas fa-sync text-success"></i> Izinkan Reload Halaman
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="show_toolbar" name="show_toolbar" value="1"
                                    {{ old('show_toolbar', $setting->show_toolbar) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_toolbar">
                                    <i class="fas fa-window-maximize text-secondary"></i> Tampilkan Toolbar
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <i class="fas fa-power-off text-success"></i> Konfigurasi Aktif
                                </label>
                                <br><small class="text-muted">Jika dimatikan, aplikasi mobile tidak akan bisa digunakan</small>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="alert alert-warning mb-3">
                        <strong><i class="fas fa-tools"></i> Mode Testing / Maintenance</strong>
                        <div class="mt-2">
                            Toggle ini hanya untuk analisa device, maintenance, dan uji APK.
                            Saat ujian berlangsung, pastikan kembali dimatikan agar proteksi penuh tetap aktif.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="testing_allow_developer_options" name="testing_allow_developer_options" value="1"
                                    {{ old('testing_allow_developer_options', $setting->testing_allow_developer_options) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testing_allow_developer_options">
                                    <i class="fas fa-tools text-warning"></i> Izinkan Developer Options Saat Testing
                                </label>
                                <br><small class="text-muted">Jika aktif, Developer Options tidak memblokir ujian saat mode testing/maintenance.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="testing_allow_usb_debugging" name="testing_allow_usb_debugging" value="1"
                                    {{ old('testing_allow_usb_debugging', $setting->testing_allow_usb_debugging) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testing_allow_usb_debugging">
                                    <i class="fas fa-tools text-warning"></i> Izinkan USB Debugging Saat Testing
                                </label>
                                <br><small class="text-muted">Jika aktif, USB Debugging tidak dianggap pelanggaran saat maintenance atau analisa APK.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEB Configuration -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key"></i> Penguncian SEB (Browser Exam Key)</h3>
                </div>
                <div class="card-body">
                    <div class="info-callout">
                        <strong>Mengunci kuis hanya untuk aplikasi CBTman:</strong>
                        <ol class="mb-0 mt-1">
                            <li><strong>Generate</strong> Browser Exam Key di bawah, lalu <strong>Simpan</strong> halaman ini.</li>
                            <li>Di Moodle: buka Quiz &rarr; <em>Edit settings</em> &rarr; <em>Safe Exam Browser</em>.</li>
                            <li><em>Require the use of Safe Exam Browser</em> &rarr; pilih <strong>"Yes &ndash; Configure manually"</strong>.</li>
                            <li>Di kolom <strong>"Allowed browser exam keys"</strong>, tempel (paste) Browser Exam Key di bawah ini.</li>
                            <li>Simpan kuis. Kuis kini hanya bisa dibuka aplikasi CBTman yang membawa key tersebut.</li>
                        </ol>
                    </div>

                    <div class="form-group">
                        <label>Browser Exam Key</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" name="seb_exam_key" id="seb_exam_key"
                                value="{{ old('seb_exam_key', $setting->seb_exam_key) }}"
                                placeholder="Klik 'Generate' untuk membuat key baru">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" onclick="generateSebKey()">
                                    <i class="fas fa-sync-alt"></i> Generate
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard(document.getElementById('seb_exam_key').value)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">
                            Key inilah yang ditempel ke kolom <strong>"Allowed browser exam keys"</strong> pada pengaturan kuis Moodle.
                            Aplikasi mengirimnya sebagai header <code>X-SafeExamBrowser-RequestHash</code> di tiap halaman kuis.
                            <strong>Kosongkan</strong> bila penguncian SEB tidak dipakai (aplikasi tetap jalan normal).
                        </small>
                    </div>

                    <input type="hidden" name="seb_config_key" value="{{ old('seb_config_key', $setting->seb_config_key) }}">
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="card card-secondary collapsed-card">
                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                    <h3 class="card-title"><i class="fas fa-sliders-h"></i> Pengaturan Lanjutan</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>URL yang Diizinkan (JSON Array)</label>
                        <textarea class="form-control" name="allowed_urls" rows="3" 
                            placeholder='["https://elearning.man1metro.sch.id", "https://moodle.man1metro.sch.id"]'>{{ old('allowed_urls', $setting->allowed_urls) }}</textarea>
                        <small class="text-muted">Daftar URL tambahan yang boleh diakses selain URL Moodle utama</small>
                    </div>

                    <div class="form-group">
                        <label>Aplikasi yang Diblokir (JSON Array - Package Name Android)</label>
                        <textarea class="form-control" name="blocked_apps" rows="3" 
                            placeholder='["com.whatsapp", "org.telegram.messenger", "com.google.android.apps.messaging"]'>{{ old('blocked_apps', $setting->blocked_apps) }}</textarea>
                        <small class="text-muted">Package name aplikasi Android yang harus ditutup sebelum memulai ujian</small>
                    </div>

                    <div class="form-group">
                        <label>Custom CSS (Inject ke WebView)</label>
                        <textarea class="form-control font-monospace" name="custom_css" rows="4" 
                            placeholder="/* CSS kustom yang di-inject ke halaman Moodle */
body { font-size: 16px; }">{{ old('custom_css', $setting->custom_css) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Custom JavaScript (Inject ke WebView)</label>
                        <textarea class="form-control font-monospace" name="custom_js" rows="4" 
                            placeholder="// JavaScript kustom yang di-inject ke halaman Moodle">{{ old('custom_js', $setting->custom_js) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Logo Upload -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-image"></i> Logo Aplikasi</h3>
                </div>
                <div class="card-body text-center">
                    @if($setting->app_logo_path)
                        <div class="mb-3">
                            <img src="{{ Storage::disk('public')->url($setting->app_logo_path) }}" 
                                alt="App Logo" class="logo-preview mb-2">
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLogo()">
                                <i class="fas fa-trash"></i> Hapus Logo
                            </button>
                        </div>
                    @endif
                    <div class="upload-area">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2">Upload logo baru</p>
                        <input type="file" class="form-control-file" name="app_logo" accept="image/png,image/jpeg,image/svg+xml">
                        <small class="text-muted">PNG, JPG, SVG. Maks 2MB</small>
                    </div>
                </div>
            </div>

            <!-- Announcement -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bullhorn"></i> Pengumuman</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <textarea class="form-control" name="announcement" rows="4" 
                            placeholder="Pengumuman yang ditampilkan saat siswa membuka aplikasi...">{{ old('announcement', $setting->announcement) }}</textarea>
                        <small class="text-muted">Kosongkan jika tidak ada pengumuman</small>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Status Konfigurasi</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($setting->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Aktif</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Moodle URL</td>
                            <td><a href="{{ $setting->moodle_url }}" target="_blank" class="text-truncate d-block" style="max-width: 200px;">{{ $setting->moodle_url }}</a></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Password Masuk</td>
                            <td>{!! $setting->app_password ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tidak Ada</span>' !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Password Keluar</td>
                            <td>{!! $setting->exit_password ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tidak Ada</span>' !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Browser Exam Key</td>
                            <td>{!! $setting->seb_exam_key ? '<span class="badge badge-info">Aktif (kuis terkunci)</span>' : '<span class="badge badge-secondary">Nonaktif</span>' !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Screenshot</td>
                            <td>{!! $setting->allow_screenshot ? '<span class="badge badge-warning">Diizinkan</span>' : '<span class="badge badge-success">Diblokir</span>' !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terakhir Update</td>
                            <td>{{ $setting->updated_at ? $setting->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Features -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt"></i> Fitur Keamanan App</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ban text-danger"></i> Anti Screenshot</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ban text-danger"></i> Anti Floating Window</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ban text-danger"></i> Anti App Switching</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ban text-danger"></i> Anti Split Screen</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ban text-danger"></i> Anti Copy/Paste</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-lock text-primary"></i> Kiosk Mode (Android)</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-lock text-primary"></i> Guided Access (iOS)</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-agent text-info"></i> Custom User-Agent (SEB)</span>
                            <span class="badge badge-success">✓</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Setelah menyimpan, konfigurasi akan langsung tersinkronisasi ke semua aplikasi mobile siswa.
                    </span>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- CBTman Guide Modal -->
<div class="modal fade cbtman-guide-modal" id="cbtmanGuideModal" tabindex="-1" role="dialog" aria-labelledby="cbtmanGuideTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <div>
                    <h5 class="modal-title mb-1" id="cbtmanGuideTitle">
                        <i class="fas fa-shield-alt mr-1"></i> Panduan CBTman
                    </h5>
                    <small class="text-white-50">Secure Exam Browser Moodle untuk perangkat pribadi siswa</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="guide-intro">
                    <strong><i class="fas fa-info-circle mr-1"></i> Gambaran singkat</strong><br>
                    CBTman memuat Moodle dalam WebView terkunci. Siswa tetap login menggunakan akun Moodle, sedangkan Simansa mengatur URL, password, keamanan, dan versi minimum melalui file config statis.
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-list-ol"></i> Persiapan pertama kali</h6>
                    <ol>
                        <li>Pastikan <strong>URL Moodle</strong>, logo, nama sekolah, dan User-Agent sudah benar.</li>
                        <li>Isi <strong>Password Masuk</strong>, <strong>Password Keluar</strong>, dan <strong>Password Pengawas</strong>.</li>
                        <li>Klik <strong>Terapkan Profil CBTman</strong> untuk memakai default keamanan yang disarankan.</li>
                        <li>Klik <strong>Simpan Konfigurasi</strong>. Config akan dibuat di URL file statis dan dibaca aplikasi saat startup.</li>
                        <li>Bagikan APK release CBTman yang sudah ditandatangani dengan keystore resmi.</li>
                    </ol>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-mobile-alt"></i> Cara siswa menggunakan CBTman</h6>
                    <ol>
                        <li>Instal atau perbarui CBTman, lalu buka aplikasi.</li>
                        <li>Izinkan akses <strong>Jangan Ganggu</strong> dan izin proteksi yang diminta Android.</li>
                        <li>Aktifkan <strong>screen pinning/kunci layar</strong> ketika diminta.</li>
                        <li>Masukkan Password Masuk Aplikasi.</li>
                        <li>Login ke Moodle dan kerjakan kuis seperti biasa.</li>
                        <li>Untuk keluar, gunakan tombol keluar dan masukkan Password Keluar.</li>
                    </ol>
                    <div class="guide-note">
                        <strong>Catatan:</strong> pada perangkat pribadi, nama menu izin dapat berbeda antara Samsung, Xiaomi, OPPO, Vivo, Realme, dan merek lain. CBTman hanya meminta izin yang diperlukan dan tidak dapat menjamin semua pembatasan Android seragam di setiap merek.
                    </div>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-lock"></i> Proteksi ujian</h6>
                    <ul>
                        <li>Screenshot dan screen recording diblokir melalui <code>FLAG_SECURE</code>.</li>
                        <li>Fullscreen, DND, screen pinning, anti copy-paste, dan pembatasan navigasi diterapkan sesuai kemampuan perangkat.</li>
                        <li>Perpindahan aplikasi, Home, kehilangan fokus, atau percobaan keluar dapat dihitung sebagai pelanggaran.</li>
                        <li>Setelah batas pelanggaran tercapai, aplikasi meminta Password Pengawas untuk membuka kunci.</li>
                        <li>Ganti password di Simansa lalu simpan config jika password lama perlu dicabut.</li>
                    </ul>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-sync-alt"></i> Cara melakukan update paksa</h6>
                    <ol>
                        <li>Build APK baru dengan <strong>versionCode</strong> lebih tinggi.</li>
                        <li>Tandatangani APK menggunakan keystore release yang sama. Jangan gunakan APK debug.</li>
                        <li>Upload APK ke server atau halaman download resmi sekolah.</li>
                        <li>Isi <strong>Tautan Update APK</strong> dengan URL HTTPS file/halaman download tersebut.</li>
                        <li>Pilih <strong>Saluran Update Aktif</strong>: Download Center Simansa selama masa awal, lalu Google Play Store setelah listing disetujui.</li>
                        <li>Simpan URL Google Play Store sejak awal agar perpindahan saluran cukup dilakukan dengan mengubah pilihan channel.</li>
                        <li>Naikkan <strong>Versi Minimum Aplikasi</strong>, lalu klik <strong>Simpan Konfigurasi</strong>.</li>
                        <li>Versi CBTman di bawah minimum akan berhenti sebelum login dan menampilkan tombol update.</li>
                    </ol>
                    <div class="guide-note">
                        Jangan menaikkan versi minimum sebelum APK baru tersedia dan URL update dapat dibuka dari jaringan siswa.
                    </div>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-key"></i> Catatan package dan keystore</h6>
                    <ul>
                        <li>Update Android hanya berhasil jika package name dan sertifikat penandatangan sama.</li>
                        <li>Jika CBTman menggantikan ExaManmet, gunakan package serta keystore ExaManmet yang lama.</li>
                        <li>Simpan keystore, alias, password, dan mapping R8 di tempat aman. Kehilangan keystore berarti APK tidak dapat melakukan update in-place.</li>
                    </ul>
                </div>

                <div class="guide-section mb-0">
                    <h6><i class="fas fa-tools"></i> Pemeriksaan sebelum ujian massal</h6>
                    <ul>
                        <li>Uji login Moodle, kuis, timer, submit, tombol back, refresh, keluar, dan unlock.</li>
                        <li>Uji minimal pada Android Samsung, Xiaomi/Redmi, OPPO/Realme, Vivo, dan Itel/Transsion.</li>
                        <li>Pastikan URL config, URL update, dan Moodle dapat diakses melalui jaringan siswa.</li>
                        <li>Jangan mengaktifkan minimum version baru sebelum satu perangkat berhasil diperbarui.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer py-2">
                <small class="text-muted mr-auto"><i class="fas fa-sync-alt mr-1"></i> Config dibaca ulang saat aplikasi dibuka.</small>
                <button type="button" class="btn btn-primary btn-sm px-3" data-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

<!-- Config Preview Modal -->
<div class="modal fade" id="configPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-code"></i> Preview Config JSON</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <pre class="config-preview" id="configPreviewContent">Loading...</pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function generatePassword(fieldName) {
        const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        let password = '';
        for (let i = 0; i < 8; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.querySelector(`[name="${fieldName}"]`).value = password;
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Berhasil disalin!',
                showConfirmButton: false,
                timer: 1500
            });
        });
    }

    function generateSebKey() {
        Swal.fire({
            title: 'Generate Browser Exam Key?',
            text: 'Key lama akan diganti. Setelah ini klik Simpan, lalu update juga di pengaturan kuis Moodle.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("admin.exam-browser.generate-seb-key") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('seb_exam_key').value = data.seb_exam_key;
                        Swal.fire('Berhasil!', data.message, 'success');
                    } else {
                        Swal.fire('Error!', data.message || 'Gagal generate key', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error!', 'Terjadi kesalahan', 'error');
                });
            }
        });
    }

    function deleteLogo() {
        Swal.fire({
            title: 'Hapus Logo?',
            text: 'Logo akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.exam-browser.delete-logo") }}';
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function previewConfig() {
        $('#configPreviewModal').modal('show');
        fetch('{{ route("admin.exam-browser.preview-config") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('configPreviewContent').textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                document.getElementById('configPreviewContent').textContent = 'Error loading config: ' + err.message;
            });
    }
</script>
@endsection
