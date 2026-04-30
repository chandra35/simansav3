@extends('adminlte::page')

@section('title', 'Data Diri - SIMANSA')

@section('plugins.BsCustomFileInput', true)

@section('css')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<style>
    /* Flatpickr Custom Styling */
    .flatpickr-calendar {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border-radius: 8px;
        border: none;
        font-family: 'Source Sans Pro', sans-serif;
    }
    
    .flatpickr-calendar.open {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .flatpickr-day.selected {
        background: #007bff !important;
        border-color: #007bff !important;
    }
    
    .flatpickr-day.today {
        border-color: #007bff;
        color: #007bff;
    }
    
    .flatpickr-day:hover {
        background: #e3f2fd;
        border-color: #e3f2fd;
    }
    
    .flatpickr-months .flatpickr-month {
        background: #007bff;
        color: white;
        border-radius: 8px 8px 0 0;
    }
    
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: #007bff;
        color: white;
        font-weight: 600;
    }
    
    .numInputWrapper:hover {
        background: rgba(255,255,255,0.1);
    }
    
    .flatpickr-weekdays {
        background: #f8f9fa;
    }
    
    span.flatpickr-weekday {
        color: #6c757d;
        font-weight: 600;
    }
    
    /* Input styling */
    #tanggal_lahir_picker,
    #tanggal_lahir_mobile {
        cursor: pointer;
    }
    
    #tanggal_lahir_picker:focus,
    #tanggal_lahir_mobile:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .tanggal-lahir-mobile {
        display: none;
    }

    @media (max-width: 767.98px) {
        .tanggal-lahir-desktop {
            display: none;
        }

        .tanggal-lahir-mobile {
            display: block;
        }
    }
    
    /* Foto Container */
    .foto-container {
        display: inline-block;
    }

    /* Foto Preview Styling */
    .foto-clickable {
        position: relative;
        border: 3px solid transparent !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .foto-clickable:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0,123,255,0.3) !important;
        border-color: #007bff !important;
    }
    
    .foto-clickable::after {
        content: '\f00e'; /* FontAwesome search-plus icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
        text-shadow: 0 2px 10px rgba(0,0,0,0.8);
        pointer-events: none;
        z-index: 5;
    }
    
    .foto-clickable:hover::after {
        opacity: 0.9;
    }

    /* Badge Animation */
    .badge-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: translateX(-50%) scale(1);
            opacity: 1;
        }
        50% {
            transform: translateX(-50%) scale(1.05);
            opacity: 0.9;
        }
    }
    
    /* Enhanced Lightbox Modal Styling */
    #modalFotoPreview .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    
    #modalFotoPreview.show {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { 
            opacity: 0; 
            transform: translateY(-20px);
        }
        to { 
            opacity: 1; 
            transform: translateY(0);
        }
    }
    
    /* Close Button - Floating Elegant */
    .btn-close-lightbox {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1060;
        background: rgba(255, 255, 255, 0.95);
        border: 2px solid rgba(0, 0, 0, 0.1);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        color: #333;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-close-lightbox:hover {
        background: #fff;
        transform: rotate(90deg) scale(1.15);
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.5);
        border-color: rgba(0, 0, 0, 0.2);
    }

    .btn-close-lightbox:active {
        transform: rotate(90deg) scale(1.05);
    }

    .btn-close-lightbox:focus {
        outline: none;
    }
    
    /* Lightbox Image Container */
    .lightbox-image-container {
        padding: 0 20px;
        animation: zoomIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0 auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    @keyframes zoomIn {
        from {
            opacity: 0;
            transform: scale(0.92);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* Card Container - Elegant White Card */
    .lightbox-card {
        position: relative;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6);
        margin: 0 auto;
        max-width: 90%;
        width: auto;
        display: inline-block;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Main Image */
    .lightbox-image {
        width: auto;
        height: auto;
        max-height: 80vh;
        max-width: 90vw;
        object-fit: contain;
        display: block;
        background: #f8f9fa;
    }
    
    /* Info Overlay at Bottom - Smooth Slide Up */
    .lightbox-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.6) 50%, transparent 100%);
        padding: 30px;
        color: white;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10;
    }
    
    .lightbox-card:hover .lightbox-info {
        transform: translateY(0);
    }

    .lightbox-info h5 {
        margin: 0 0 8px 0;
        font-size: 22px;
        font-weight: 600;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .lightbox-info h5 i {
        margin-right: 8px;
        color: #4fc3f7;
    }

    .lightbox-info small {
        display: block;
        margin-bottom: 15px;
        opacity: 0.95;
        font-size: 14px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .lightbox-info .btn {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .lightbox-info .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .lightbox-help-text {
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
        font-size: 14px;
        margin-top: 15px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    }
    
    /* Download Button on Foto - Fade In */
    .btn-download-foto {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 15;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    
    .foto-container:hover .btn-download-foto {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Responsive Mobile */
    @media (max-width: 768px) {
        .btn-close-lightbox {
            width: 42px;
            height: 42px;
            font-size: 20px;
            top: 15px;
            right: 15px;
        }
        
        .lightbox-image-container {
            padding: 0 10px;
        }
        
        .lightbox-card {
            max-width: 95%;
            width: auto;
            border-radius: 15px;
        }

        .lightbox-image {
            max-height: 70vh;
            max-width: 95vw;
        }
        
        .lightbox-info {
            padding: 20px;
        }

        .lightbox-info h5 {
            font-size: 18px;
        }

        .lightbox-info small {
            font-size: 13px;
        }

        .lightbox-info .btn {
            padding: 8px 18px;
            font-size: 14px;
        }

        .lightbox-help-text {
            font-size: 12px;
            margin-top: 10px;
        }
    }

    /* Loading state for image */
    .lightbox-image[src=""] {
        min-height: 400px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    .profile-shell {
        display: grid;
        gap: 1.25rem;
    }

    .profile-row {
        margin-bottom: 0;
    }

    .profile-card {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.08);
        margin-bottom: 0;
    }

    .profile-card .card-header {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-bottom: 1px solid #e9eef6;
        padding: 1rem 1.25rem;
    }

    .profile-card .card-body {
        padding: 1.25rem;
    }

    .profile-card .card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
        color: #0f172a;
    }

    .profile-lead {
        border-left: 0;
        border-radius: 18px;
        padding: 1.1rem 1.25rem;
        background: linear-gradient(135deg, #eef6ff 0%, #f8fbff 52%, #ffffff 100%);
        box-shadow: 0 14px 34px rgba(59, 130, 246, 0.12);
    }

    .profile-lead h5 {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.65rem;
        font-weight: 700;
    }

    .profile-lead p {
        color: #4b5563;
        line-height: 1.65;
    }

    .profile-photo-panel {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.9rem;
    }

    .profile-photo-meta {
        text-align: center;
        max-width: 320px;
    }

    .profile-photo-meta p {
        margin-bottom: 0.35rem;
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .foto-action-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        width: 100%;
    }

    .foto-action-group .btn {
        border-radius: 12px;
        min-height: 44px;
        font-weight: 600;
    }

    .profile-inline-search .form-control {
        min-height: 48px;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .profile-inline-search .input-group-append .btn {
        min-width: 104px;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        font-weight: 600;
    }

    .section-hint {
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.55;
    }

    .profile-card .form-control,
    .profile-card .custom-select,
    .profile-card select.form-control,
    .profile-card .input-group-text {
        min-height: 46px;
        border-radius: 12px;
    }

    .profile-card textarea.form-control {
        min-height: 96px;
    }

    .profile-card .input-group > .form-control:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .profile-card .input-group > .input-group-append > .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .profile-card .form-group {
        margin-bottom: 1rem;
    }

    .readonly-grid .form-control,
    .readonly-grid textarea {
        background: #f8fafc !important;
    }

    .address-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .address-choice-card {
        position: relative;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        background: #fff;
        padding: 1rem 1rem 1rem 2.8rem;
        min-height: 92px;
        display: flex;
        align-items: center;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .address-choice-card:hover {
        border-color: #8fb7ff;
        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.12);
        transform: translateY(-1px);
    }

    .address-choice-card .custom-control-input {
        top: 1.15rem;
        left: 1rem;
        z-index: 2;
    }

    .address-choice-card .custom-control-label {
        display: block;
        width: 100%;
        margin: 0;
        line-height: 1.45;
    }

    .address-choice-card .custom-control-label::before,
    .address-choice-card .custom-control-label::after {
        top: 1.2rem;
        left: -1.8rem;
    }

    .address-choice-title {
        display: block;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.15rem;
    }

    .address-choice-copy {
        display: block;
        color: #64748b;
        font-size: 0.88rem;
    }

    .sticky-submit-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.25rem 1.1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .sticky-submit-note {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
        flex: 1 1 240px;
    }

    .sticky-submit-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .sticky-submit-actions .btn {
        min-width: 150px;
        border-radius: 12px;
        font-weight: 600;
    }

    .sticky-media-card {
        height: 100%;
    }

    @media (min-width: 992px) {
        .profile-shell {
            gap: 1.4rem;
        }

        .profile-row {
            margin-left: -0.55rem;
            margin-right: -0.55rem;
        }

        .profile-row > [class*='col-'] {
            padding-left: 0.55rem;
            padding-right: 0.55rem;
        }

        .profile-card .card-header {
            padding: 0.9rem 1.15rem;
        }

        .profile-card .card-body {
            padding: 1.1rem 1.15rem 1rem;
        }

        .profile-card .card-footer {
            padding-left: 1.15rem;
            padding-right: 1.15rem;
        }

        .profile-card .row {
            margin-left: -0.45rem;
            margin-right: -0.45rem;
        }

        .profile-card .row > [class*='col-'] {
            padding-left: 0.45rem;
            padding-right: 0.45rem;
        }

        .profile-card .form-group {
            margin-bottom: 0.85rem;
        }

        .profile-photo-panel {
            gap: 0.75rem;
        }

        .profile-photo-meta {
            max-width: 280px;
        }

        .section-hint,
        .profile-card small.form-text {
            font-size: 0.84rem;
        }

        .address-choice-grid {
            gap: 0.75rem;
        }

        .address-choice-card {
            min-height: 84px;
            padding-right: 1.1rem;
        }

        .sticky-submit-bar {
            padding: 0.95rem 1.15rem 1rem;
        }
    }

    @media (min-width: 1200px) {
        .sticky-media-card {
            position: sticky;
            top: 1rem;
        }
    }

    @media (max-width: 991.98px) {
        .profile-card .card-header,
        .profile-card .card-body,
        .sticky-submit-bar {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .address-choice-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .profile-shell {
            gap: 1rem;
        }

        .profile-lead {
            border-radius: 16px;
            padding: 1rem;
        }

        .profile-card {
            border-radius: 16px;
        }

        .profile-card .card-title {
            font-size: 1rem;
        }

        .foto-action-group {
            grid-template-columns: 1fr;
        }

        .profile-inline-search {
            display: flex;
            flex-direction: column;
        }

        .profile-inline-search .form-control {
            border-radius: 12px 12px 0 0;
        }

        .profile-inline-search .input-group-append {
            width: 100%;
        }

        .profile-inline-search .input-group-append .btn {
            width: 100%;
            border-radius: 0 0 12px 12px;
        }

        .sticky-submit-actions {
            width: 100%;
        }

        .sticky-submit-actions .btn {
            flex: 1 1 100%;
            width: 100%;
        }
    }
</style>
@stop

@section('content_header')
    <h1><i class="fas fa-id-card"></i> Data Diri Siswa</h1>
@stop

@section('content')
<!-- Info Progress -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-success profile-lead">
            <h5><i class="fas fa-check-circle"></i> Langkah 2: Data Diri Siswa</h5>
            <p class="mb-0">
                Lengkapi data diri Anda dengan benar dan lengkap. Data ini penting untuk administrasi dan komunikasi sekolah.
                Pada bagian alamat, Anda dapat memilih <strong>alamat yang sama dengan orangtua</strong> atau alamat berbeda.
                Setelah selesai, silakan lanjut ke <strong>Upload Dokumen</strong>.
            </p>
        </div>
    </div>
</div>

<form action="{{ route('siswa.profile.diri.update') }}" method="POST" enctype="multipart/form-data" id="formDataDiri">
    @csrf
    @method('PUT')
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Validasi Gagal!</h5>
            <p class="mb-2">Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:</p>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-shell">
    <div class="row profile-row">
        <!-- Foto Profile -->
        <div class="col-12 col-xl-4 mb-4 mb-xl-0">
            <div class="card card-outline card-primary profile-card sticky-media-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera"></i> Foto Profile</h3>
                </div>
                <div class="card-body text-center profile-photo-panel">
                    <div class="form-group">
                        <!-- Circle Photo Preview - Same as Dashboard -->
                        <div class="mb-3" style="display: flex; flex-direction: column; align-items: center;">
                            <div id="fotoFrame" style="position: relative; width: 180px; height: 180px; cursor: pointer;">
                                <!-- Animated Ring -->
                                <div id="fotoRing" style="position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; border-radius: 50%; background: linear-gradient(135deg, {{ $siswa->jenis_kelamin == 'L' ? '#007bff, #00d4ff' : '#e83e8c, #ff6b9d' }}); animation: pulse-ring 2s ease-in-out infinite;"></div>
                                
                                <!-- Photo -->
                                <img id="previewFoto" 
                                     src="{{ $siswa->foto_profile_url }}" 
                                     style="width: 170px; height: 170px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; position: relative; z-index: 1; box-shadow: 0 4px 15px rgba(0,0,0,0.15);"
                                     alt="Foto Profile {{ $siswa->nama_lengkap }}"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_lengkap) }}&size=400&background={{ $siswa->jenis_kelamin == 'L' ? '007bff' : 'e83e8c' }}&color=fff'">
                                
                                <!-- Upload overlay -->
                                <div id="uploadOverlay" style="position: absolute; top: 5px; left: 5px; width: 170px; height: 170px; border-radius: 50%; background: rgba(0,0,0,0.6); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; opacity: 0; transition: opacity 0.3s ease; z-index: 10;">
                                    <i class="fas fa-camera fa-2x" style="margin-bottom: 8px;"></i>
                                    <span style="font-weight: 600; font-size: 14px;">Ubah Foto</span>
                                </div>
                            </div>
                            
                            @if(!$siswa->foto_profile)
                                <div class="mt-2">
                                    <span class="badge badge-info badge-pulse">
                                        <i class="fas fa-magic"></i> Avatar Otomatis
                                    </span>
                                </div>
                            @else
                                <button type="button" class="btn btn-sm btn-primary btn-download-foto" id="btnDownloadFoto" title="Download Foto">
                                    <i class="fas fa-download"></i>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Upload Buttons -->
                        <div class="foto-action-group mb-2" role="group" aria-label="Aksi foto profil">
                            <button type="button" class="btn btn-outline-primary" id="btnChooseFile">
                                <i class="fas fa-folder-open mr-1"></i> Pilih File
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnOpenCamera">
                                <i class="fas fa-camera mr-1"></i> Kamera
                            </button>
                        </div>
                        
                        <input type="file" id="foto_profile" class="d-none" accept="image/jpeg,image/jpg,image/png">

                        <div class="profile-photo-meta">
                            <p><i class="fas fa-info-circle text-muted mr-1"></i> Format JPG/PNG dengan ukuran maksimal 2MB.</p>
                            <p class="text-success mb-0"><i class="fas fa-crop-alt mr-1"></i> Foto akan dipotong otomatis ke rasio 1:1 agar rapi di semua perangkat.</p>
                        </div>
                        @error('foto_profile')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Asal Sekolah (NPSN) -->
        <div class="col-12 col-xl-8">
            <div class="card card-outline card-info profile-card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-school"></i> Asal Sekolah (MTs/SMP)</h3>
                </div>
                <div class="card-body">
                    <!-- NPSN Input with Search -->
                    <div class="form-group">
                        <label>NPSN Asal Sekolah <span class="text-danger">*</span></label>
                        <div class="input-group profile-inline-search">
                            <input type="text" name="npsn_asal_sekolah" id="npsn_asal_sekolah" 
                                   class="form-control @error('npsn_asal_sekolah') is-invalid @enderror" 
                                   placeholder="Contoh: 10648374 atau A1234567" 
                                   maxlength="8" 
                                   style="text-transform: uppercase;"
                                   value="{{ old('npsn_asal_sekolah', $siswa->npsn_asal_sekolah ?? '') }}"
                                   required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" id="btnCariSekolah">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                            @error('npsn_asal_sekolah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="form-text text-muted section-hint">
                            <i class="fas fa-info-circle"></i> Masukkan 8 karakter NPSN (huruf/angka), pencarian otomatis dilakukan
                        </small>
                    </div>

                    <!-- Alert: Data Found -->
                    <div id="alertSekolahFound" class="alert alert-success" style="display:none;">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Data sekolah ditemukan!</strong> 
                        <span id="sourceInfo"></span>
                    </div>

                    <!-- Alert: Data Not Found -->
                    <div id="alertSekolahNotFound" class="alert alert-warning" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Data tidak ditemukan.</strong> 
                        Silakan periksa kembali NPSN atau hubungi admin.
                    </div>

                    <!-- Auto-filled Fields (Readonly) -->
                    <div class="row readonly-grid">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Nama Sekolah</small></label>
                                <input type="text" id="nama_sekolah" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row readonly-grid">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Status</small></label>
                                <input type="text" id="status_sekolah" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Bentuk Pendidikan</small></label>
                                <input type="text" id="bentuk_pendidikan" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row readonly-grid">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="mb-1"><small>Alamat Lengkap Sekolah</small></label>
                                <textarea id="alamat_sekolah" class="form-control form-control-sm bg-light" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row profile-row">
        <div class="col-12">
            <!-- Data Pribadi -->
            <div class="card card-outline card-primary profile-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Data Pribadi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="nisn">
                                    NISN
                                    <small class="text-muted">(Username Login)</small>
                                </label>
                                <input type="text" class="form-control bg-light" value="{{ $siswa->nisn }}" disabled>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lock"></i> Hanya dapat diubah oleh Admin
                                </small>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="nik">
                                    NIK <span class="text-danger">*</span>
                                    <small class="text-muted">(16 digit)</small>
                                </label>
                                <input type="text" name="nik" id="nik" 
                                       class="form-control @error('nik') is-invalid @enderror" 
                                       value="{{ old('nik', $siswa->nik ?? '') }}" 
                                       placeholder="3401xxxxxxxxxxxx"
                                       maxlength="16"
                                       required>
                                @error('nik')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="tempat_lahir">
                                    Tempat Lahir <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" 
                                       class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                       value="{{ old('tempat_lahir', $siswa->tempat_lahir ?? '') }}" 
                                       placeholder="Nama Kota/Kabupaten"
                                       required>
                                @error('tempat_lahir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="tanggal_lahir">
                                    Tanggal Lahir <span class="text-danger">*</span>
                                </label>
                                @php
                                    $tanggalLahirValue = old('tanggal_lahir', optional($siswa->tanggal_lahir)->format('Y-m-d'));
                                    $tanggalLahirDisplay = $tanggalLahirValue
                                        ? \Carbon\Carbon::parse($tanggalLahirValue)->translatedFormat('j F Y')
                                        : '';
                                @endphp
                                <div class="input-group tanggal-lahir-desktop">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="hidden"
                                           name="tanggal_lahir"
                                           id="tanggal_lahir"
                                           value="{{ $tanggalLahirValue }}">
                                    <input type="text" 
                                           id="tanggal_lahir_picker" 
                                           class="form-control flatpickr @error('tanggal_lahir') is-invalid @enderror" 
                                           value="{{ $tanggalLahirDisplay }}" 
                                           placeholder="Pilih Tanggal Lahir"
                                           required
                                           readonly>
                                </div>
                                <div class="tanggal-lahir-mobile">
                                    <input type="date"
                                           id="tanggal_lahir_mobile"
                                           class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                           value="{{ $tanggalLahirValue }}"
                                           max="{{ now()->subDay()->format('Y-m-d') }}"
                                           required>
                                </div>
                                @error('tanggal_lahir')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Desktop memakai kalender, sedangkan di ponsel memakai input tanggal bawaan perangkat agar lebih responsif.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="jenis_kelamin">
                                    Jenis Kelamin <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_kelamin" id="jenis_kelamin" 
                                        class="form-control @error('jenis_kelamin') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>
                                        Laki-laki
                                    </option>
                                    <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>
                                        Perempuan
                                    </option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="agama">
                                    Agama <span class="text-danger">*</span>
                                </label>
                                <select name="agama" id="agama" 
                                        class="form-control @error('agama') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam" {{ old('agama', $siswa->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                    <option value="Kristen" {{ old('agama', $siswa->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                    <option value="Katolik" {{ old('agama', $siswa->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                    <option value="Hindu" {{ old('agama', $siswa->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                    <option value="Buddha" {{ old('agama', $siswa->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                    <option value="Konghucu" {{ old('agama', $siswa->agama ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                                </select>
                                @error('agama')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label for="jumlah_saudara">
                                    Jumlah Saudara Kandung <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="jumlah_saudara" id="jumlah_saudara" 
                                       class="form-control @error('jumlah_saudara') is-invalid @enderror" 
                                       value="{{ old('jumlah_saudara', $siswa->jumlah_saudara ?? '') }}" 
                                       placeholder="0"
                                       min="0"
                                       required>
                                @error('jumlah_saudara')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label for="anak_ke">
                                    Anak Ke <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="anak_ke" id="anak_ke" 
                                       class="form-control @error('anak_ke') is-invalid @enderror" 
                                       value="{{ old('anak_ke', $siswa->anak_ke ?? '') }}" 
                                       placeholder="1"
                                       min="1"
                                       required>
                                @error('anak_ke')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="form-group">
                                <label for="nomor_hp">
                                    No. HP <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nomor_hp" id="nomor_hp" 
                                       class="form-control @error('nomor_hp') is-invalid @enderror" 
                                       value="{{ old('nomor_hp', $siswa->nomor_hp ?? '') }}" 
                                       placeholder="08xxxxxxxxxx"
                                       maxlength="15"
                                       required>
                                @error('nomor_hp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label for="hobi">Hobi</label>
                                <input type="text" name="hobi" id="hobi" 
                                       class="form-control @error('hobi') is-invalid @enderror" 
                                       value="{{ old('hobi', $siswa->hobi ?? '') }}" 
                                       placeholder="Membaca, Olahraga">
                                @error('hobi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label for="cita_cita">Cita-cita</label>
                                <input type="text" name="cita_cita" id="cita_cita" 
                                       class="form-control @error('cita_cita') is-invalid @enderror" 
                                       value="{{ old('cita_cita', $siswa->cita_cita ?? '') }}" 
                                       placeholder="Dokter, Guru, Insinyur">
                                @error('cita_cita')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $siswa->user->email ?? '') }}" 
                                       placeholder="email@example.com">
                                <small class="form-text text-muted">
                                    <i class="fas fa-envelope"></i> Untuk komunikasi
                                </small>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row profile-row">
        <div class="col-12">
            <!-- Alamat Siswa -->
            <div class="card card-outline card-success profile-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt"></i> Alamat Siswa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>
                            Pilih Alamat Siswa <span class="text-danger">*</span>
                        </label>
                        <div class="address-choice-grid">
                            <div class="custom-control custom-radio address-choice-card">
                                    <input class="custom-control-input" type="radio" name="alamat_sama_ortu" 
                                           id="alamat_sama" value="1" 
                                           {{ old('alamat_sama_ortu', $siswa->alamat_sama_ortu ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="alamat_sama">
                                        <span class="address-choice-title"><i class="fas fa-home text-primary mr-1"></i> Sama dengan Alamat Orangtua</span>
                                        <span class="address-choice-copy">Alamat siswa mengikuti data orangtua agar pengisian lebih cepat.</span>
                                    </label>
                            </div>
                            <div class="custom-control custom-radio address-choice-card">
                                    <input class="custom-control-input" type="radio" name="alamat_sama_ortu" 
                                           id="alamat_lainnya" value="0" 
                                           {{ old('alamat_sama_ortu', $siswa->alamat_sama_ortu ?? 0) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="alamat_lainnya">
                                        <span class="address-choice-title"><i class="fas fa-map-marker-alt text-success mr-1"></i> Alamat Lainnya</span>
                                        <span class="address-choice-copy">Gunakan opsi ini jika tinggal di asrama, kost, kontrakan, atau bersama saudara.</span>
                                    </label>
                            </div>
                        </div>
                        <small class="form-text text-muted section-hint">
                            <i class="fas fa-info-circle"></i> Jika memilih "Sama dengan Orangtua", data alamat akan otomatis terisi
                        </small>
                    </div>

                    <!-- Jenis Tempat Tinggal - Only show when "Alamat Lainnya" selected -->
                    <div id="form-jenis-tempat-tinggal" style="display: none;">
                        <div class="form-group">
                            <label for="jenis_tempat_tinggal">
                                Jenis Tempat Tinggal <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_tempat_tinggal" id="jenis_tempat_tinggal" 
                                    class="form-control @error('jenis_tempat_tinggal') is-invalid @enderror">
                                <option value="">-- Pilih Jenis Tempat Tinggal --</option>
                                <option value="Asrama" {{ old('jenis_tempat_tinggal', $siswa->jenis_tempat_tinggal ?? '') == 'Asrama' ? 'selected' : '' }}>
                                    <i class="fas fa-building"></i> Asrama
                                </option>
                                <option value="Kost/Kontrakan" {{ old('jenis_tempat_tinggal', $siswa->jenis_tempat_tinggal ?? '') == 'Kost/Kontrakan' ? 'selected' : '' }}>
                                    <i class="fas fa-home"></i> Kost/Kontrakan
                                </option>
                                <option value="Saudara" {{ old('jenis_tempat_tinggal', $siswa->jenis_tempat_tinggal ?? '') == 'Saudara' ? 'selected' : '' }}>
                                    <i class="fas fa-users"></i> Saudara
                                </option>
                            </select>
                            @error('jenis_tempat_tinggal')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted section-hint">
                                <i class="fas fa-info-circle"></i> Pilih jenis tempat tinggal untuk alamat berbeda
                            </small>
                            <div id="alert-asrama" class="alert alert-info mt-2" style="display: none;">
                                <i class="fas fa-building"></i> <strong>Asrama Sekolah</strong><br>
                                <small>Alamat otomatis terisi dengan alamat sekolah, tidak perlu mengisi manual.</small>
                            </div>
                        </div>
                    </div>

                    <div id="form-alamat-siswa" style="display: none;">
                        <hr class="my-3">
                        
                        <div class="form-group">
                            <label for="alamat_siswa">
                                Alamat Lengkap
                            </label>
                            <textarea name="alamat_siswa" id="alamat_siswa" rows="3" 
                                      class="form-control @error('alamat_siswa') is-invalid @enderror" 
                                      placeholder="Jalan, No. Rumah, Nama Perumahan/Kompleks">{{ old('alamat_siswa', $siswa->alamat_siswa ?? '') }}</textarea>
                            @error('alamat_siswa')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="form-group">
                                    <label for="rt_siswa">RT</label>
                                    <input type="text" name="rt_siswa" id="rt_siswa" 
                                           class="form-control @error('rt_siswa') is-invalid @enderror" 
                                           value="{{ old('rt_siswa', $siswa->rt_siswa ?? '') }}" 
                                           placeholder="001"
                                           maxlength="3">
                                    @error('rt_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="form-group">
                                    <label for="rw_siswa">RW</label>
                                    <input type="text" name="rw_siswa" id="rw_siswa" 
                                           class="form-control @error('rw_siswa') is-invalid @enderror" 
                                           value="{{ old('rw_siswa', $siswa->rw_siswa ?? '') }}" 
                                           placeholder="001"
                                           maxlength="3">
                                    @error('rw_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label for="kodepos_siswa">Kode Pos</label>
                                    <input type="text" name="kodepos_siswa" id="kodepos_siswa" 
                                           class="form-control @error('kodepos_siswa') is-invalid @enderror" 
                                           value="{{ old('kodepos_siswa', $siswa->kodepos_siswa ?? '') }}" 
                                           placeholder="34xxx"
                                           maxlength="5">
                                    @error('kodepos_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group">
                                    <label for="provinsi_id_siswa">Provinsi</label>
                                    <select name="provinsi_id_siswa" id="provinsi_id_siswa" 
                                            class="form-control @error('provinsi_id_siswa') is-invalid @enderror">
                                        <option value="">Pilih Provinsi</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->code }}" 
                                                {{ old('provinsi_id_siswa', $siswa->provinsi_id_siswa ?? '') == $province->code ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('provinsi_id_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group">
                                    <label for="kabupaten_id_siswa">Kabupaten/Kota</label>
                                    <select name="kabupaten_id_siswa" id="kabupaten_id_siswa" 
                                            class="form-control @error('kabupaten_id_siswa') is-invalid @enderror">
                                        <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                    @error('kabupaten_id_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group">
                                    <label for="kecamatan_id_siswa">Kecamatan</label>
                                    <select name="kecamatan_id_siswa" id="kecamatan_id_siswa" 
                                            class="form-control @error('kecamatan_id_siswa') is-invalid @enderror">
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                    @error('kecamatan_id_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="form-group">
                                    <label for="kelurahan_id_siswa">Kelurahan/Desa</label>
                                    <select name="kelurahan_id_siswa" id="kelurahan_id_siswa" 
                                            class="form-control @error('kelurahan_id_siswa') is-invalid @enderror">
                                        <option value="">Pilih Kelurahan/Desa</option>
                                    </select>
                                    @error('kelurahan_id_siswa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer sticky-submit-bar">
                    <div class="sticky-submit-note">
                        Periksa kembali NIK, tanggal lahir, nomor HP, dan alamat sebelum menyimpan agar data administrasi tetap akurat.
                    </div>
                    <div class="sticky-submit-actions">
                        <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Simpan Data Diri
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</form>

<!-- Modal Lightbox for Photo Preview - Enhanced Design -->
<div class="modal fade" id="modalFotoPreview" tabindex="-1" role="dialog" aria-labelledby="modalFotoPreviewLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content" style="background: transparent; border: none;">
            <!-- Close Button - Top Right Corner -->
            <button type="button" class="btn-close-lightbox" data-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Image Container with Card Style -->
            <div class="lightbox-image-container">
                <div class="lightbox-card">
                    <!-- Image -->
                    <img id="modalFotoImg" src="" class="lightbox-image" alt="Preview Foto Profile">
                    
                    <!-- Image Info Overlay -->
                    <div class="lightbox-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-white">
                                    <i class="fas fa-user-circle"></i> Foto Profile
                                </h5>
                                <small class="text-white-50">{{ $siswa->nama_lengkap }}</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-light btn-sm rounded-pill shadow" id="btnDownloadModal" title="Download Foto">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Hint -->
            <div class="text-center mt-3">
                <small class="text-white-50">
                    <i class="fas fa-info-circle"></i> Klik di luar foto atau tombol X untuk menutup
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crop Image -->
<div class="modal fade" id="cropModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-crop-alt mr-2"></i> Crop Foto Profil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="img-container" style="max-height: 400px; background: #f5f5f5; border-radius: 8px; overflow: hidden;">
                            <img id="cropImage" src="" style="max-width: 100%; display: block;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <p class="text-muted mb-2"><i class="fas fa-eye"></i> Preview</p>
                            <div class="preview-container mx-auto" style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 3px solid #007bff; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                                <div class="preview" style="width: 100%; height: 100%; overflow: hidden;"></div>
                            </div>
                        </div>
                        
                        <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="rotateLeft" title="Rotate Left">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="rotateRight" title="Rotate Right">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="flipH" title="Flip Horizontal">
                                <i class="fas fa-arrows-alt-h"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="flipV" title="Flip Vertical">
                                <i class="fas fa-arrows-alt-v"></i>
                            </button>
                        </div>
                        
                        <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                            <button type="button" class="btn btn-outline-info" id="zoomIn" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info" id="zoomOut" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="resetCrop" title="Reset">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle"></i> Atur area yang ingin dipotong, lalu klik <strong>Simpan</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="saveCrop">
                    <i class="fas fa-check"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Camera -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-camera mr-2"></i> Ambil Foto dari Kamera
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="camera-container mb-3" style="position: relative; max-width: 500px; margin: 0 auto;">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; border-radius: 10px; background: #000;"></video>
                    <canvas id="cameraCanvas" style="display: none;"></canvas>
                    
                    <!-- Camera overlay guide -->
                    <div class="camera-guide" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 250px; height: 250px; border: 3px dashed rgba(255,255,255,0.5); border-radius: 50%; pointer-events: none;"></div>
                </div>
                
                <div id="cameraError" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <span id="cameraErrorMsg">Kamera tidak tersedia</span>
                </div>
                
                <div class="camera-controls">
                    <button type="button" class="btn btn-danger btn-lg rounded-circle" id="capturePhoto" style="width: 70px; height: 70px;">
                        <i class="fas fa-camera fa-2x"></i>
                    </button>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb"></i> Posisikan wajah dalam lingkaran, lalu tekan tombol untuk mengambil foto
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        /* Pulse Ring Animation for Profile Photo */
        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.03);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* Foto Frame Styling */
        .foto-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .foto-frame {
            width: 200px !important;
            height: 200px !important;
            max-width: 200px !important;
            max-height: 200px !important;
            min-width: 200px !important;
            min-height: 200px !important;
            margin: 0 auto;
            position: relative;
            border-radius: 12px;
            overflow: hidden !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border: 3px solid #e9ecef;
            transition: all 0.3s ease;
            cursor: pointer;
            display: block;
        }
        
        .foto-frame:hover {
            border-color: #007bff;
            box-shadow: 0 6px 20px rgba(0,123,255,0.25);
        }
        
        .foto-preview {
            width: 200px !important;
            height: 200px !important;
            max-width: 200px !important;
            max-height: 200px !important;
            object-fit: cover !important;
            display: block !important;
            transition: all 0.3s ease;
        }
        
        .foto-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,123,255,0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .foto-frame:hover .foto-overlay {
            opacity: 1;
        }
        
        .foto-overlay i {
            margin-bottom: 8px;
        }
        
        .foto-overlay span {
            font-weight: 600;
            font-size: 14px;
        }
        
        .btn-download-foto {
            position: absolute;
            bottom: 10px;
            right: 10px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .callout {
            border-left: 5px solid #17a2b8;
            border-radius: 5px;
        }
        
        .card-outline {
            border-top: 3px solid;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        
        .card-outline.card-primary {
            border-top-color: #007bff;
        }
        
        .card-outline.card-success {
            border-top-color: #28a745;
        }
        
        .form-group label {
            font-weight: 500;
            color: #495057;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .custom-control-label {
            cursor: pointer;
            font-weight: 500;
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
        }
        
        hr {
            border-top: 1px solid #dee2e6;
        }
        
        small.text-muted {
            font-size: 85%;
        }
        
        /* Foto Profile Styling */
        #previewFoto {
            transition: all 0.3s ease;
            border: 3px solid #e9ecef;
        }
        
        #previewFoto:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        
        .position-relative {
            position: relative;
        }
        
        .position-absolute {
            position: absolute;
        }
        
        .badge-info {
            background-color: #17a2b8 !important;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            animation: pulse 2s infinite;
        }
        
        /* Readonly State Styling */
        .form-control:read-only,
        .select2-container--disabled .select2-selection {
            background-color: #f8f9fa !important;
            opacity: 0.8;
            cursor: not-allowed;
        }
        
        select:disabled {
            background-color: #f8f9fa !important;
            opacity: 0.8;
            cursor: not-allowed;
        }
        
        /* Readonly form background */
        .bg-light select.form-control {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
            pointer-events: none; /* Prevent clicking but allow form submission */
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        .custom-file-label::after {
            content: "Browse";
        }
        
        .custom-file-input:focus ~ .custom-file-label {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
@stop

@section('js')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Cropper.js -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>

<script>
// Configure toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

// Global variables
var cropper = null;
var cameraStream = null;

$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // ==================== FOTO FRAME HOVER EFFECT ====================
    $('#fotoFrame').hover(
        function() {
            $(this).css('border-color', '#007bff');
            $('#uploadOverlay').css('opacity', '1');
        },
        function() {
            $(this).css('border-color', '#e9ecef');
            $('#uploadOverlay').css('opacity', '0');
        }
    );
    
    // ==================== FOTO UPLOAD WITH CROP ====================
    
    // Click on frame to change foto
    $('#fotoFrame, #uploadOverlay').on('click', function(e) {
        e.stopPropagation();
        $('#foto_profile').click();
    });
    
    // Choose file button
    $('#btnChooseFile').on('click', function() {
        $('#foto_profile').click();
    });
    
    // Open camera button
    $('#btnOpenCamera').on('click', function() {
        openCamera();
    });
    
    // File selected - open crop modal
    $('#foto_profile').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        
        // Validate file
        if (file.size > 2048000) {
            toastr.error('Ukuran file maksimal 2MB');
            $(this).val('');
            return;
        }
        
        var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            toastr.error('Format file harus JPG, JPEG, atau PNG!');
            $(this).val('');
            return;
        }
        
        // Read file and open crop modal
        var reader = new FileReader();
        reader.onload = function(e) {
            openCropModal(e.target.result);
        };
        reader.readAsDataURL(file);
    });
    
    // Open crop modal with image
    function openCropModal(imageSrc) {
        $('#cropImage').attr('src', imageSrc);
        $('#cropModal').modal('show');
    }
    
    // Initialize cropper when modal opens
    $('#cropModal').on('shown.bs.modal', function() {
        var image = document.getElementById('cropImage');
        
        if (cropper) {
            cropper.destroy();
        }
        
        cropper = new Cropper(image, {
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
            preview: '.preview',
        });
    });
    
    // Destroy cropper when modal closes
    $('#cropModal').on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $('#foto_profile').val('');
    });
    
    // Crop controls
    $('#rotateLeft').on('click', function() { if (cropper) cropper.rotate(-90); });
    $('#rotateRight').on('click', function() { if (cropper) cropper.rotate(90); });
    $('#flipH').on('click', function() { if (cropper) cropper.scaleX(cropper.getData().scaleX === -1 ? 1 : -1); });
    $('#flipV').on('click', function() { if (cropper) cropper.scaleY(cropper.getData().scaleY === -1 ? 1 : -1); });
    $('#zoomIn').on('click', function() { if (cropper) cropper.zoom(0.1); });
    $('#zoomOut').on('click', function() { if (cropper) cropper.zoom(-0.1); });
    $('#resetCrop').on('click', function() { if (cropper) cropper.reset(); });
    
    // Save cropped image
    $('#saveCrop').on('click', function() {
        if (!cropper) return;
        
        var canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        if (!canvas) {
            toastr.error('Gagal memproses gambar');
            return;
        }
        
        var croppedImage = canvas.toDataURL('image/jpeg', 0.9);
        uploadCroppedImage(croppedImage);
    });
    
    // Upload cropped image
    function uploadCroppedImage(base64Image) {
        $('#cropModal').modal('hide');
        
        Swal.fire({
            title: 'Mengupload Foto',
            html: '<div class="text-center"><i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i><br><div class="progress" style="height: 8px;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div><p class="mt-2">Mohon tunggu...</p></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
        
        $.ajax({
            url: '{{ route("siswa.profile.foto.upload") }}',
            method: 'POST',
            data: {
                cropped_image: base64Image,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    $('#previewFoto').fadeOut(200, function() {
                        $(this).attr('src', response.foto_url + '?t=' + Date.now()).fadeIn(200);
                    });
                    
                    $('.badge-info:contains("Avatar Otomatis")').fadeOut();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    toastr.error(response.message || 'Gagal mengupload foto');
                }
            },
            error: function(xhr) {
                Swal.close();
                var errorMsg = xhr.responseJSON?.message || 'Gagal mengupload foto';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Upload',
                    text: errorMsg
                });
            }
        });
    }
    
    // ==================== CAMERA FUNCTIONALITY ====================
    
    function openCamera() {
        $('#cameraError').hide();
        $('#cameraModal').modal('show');
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        })
        .then(function(stream) {
            cameraStream = stream;
            var video = document.getElementById('cameraVideo');
            video.srcObject = stream;
        })
        .catch(function(err) {
            console.error('Camera error:', err);
            $('#cameraError').show();
            $('#cameraErrorMsg').text(err.message || 'Tidak dapat mengakses kamera');
        });
    }
    
    // Capture photo from camera
    $('#capturePhoto').on('click', function() {
        var video = document.getElementById('cameraVideo');
        var canvas = document.getElementById('cameraCanvas');
        var ctx = canvas.getContext('2d');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        ctx.drawImage(video, 0, 0);
        
        // Get image data
        var imageData = canvas.toDataURL('image/jpeg', 0.9);
        
        // Stop camera and close modal
        stopCamera();
        $('#cameraModal').modal('hide');
        
        // Open crop modal with captured image
        openCropModal(imageData);
    });
    
    // Stop camera when modal closes
    $('#cameraModal').on('hidden.bs.modal', function() {
        stopCamera();
    });
    
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function(track) {
                track.stop();
            });
            cameraStream = null;
        }
    }
    
    // ==================== LIGHTBOX PREVIEW ====================
    
    // Foto Preview - Click to Open Modal (on the image, not overlay)
    $('#previewFoto').on('click', function(e) {
        // Check if not clicking on overlay
        if (!$(e.target).closest('#uploadOverlay').length) {
            var imgSrc = $(this).attr('src');
            $('#modalFotoImg').attr('src', imgSrc);
            $('#modalFotoPreview').modal('show');
        }
    });
    
    // Download Foto from Modal
    $('#btnDownloadModal, #btnDownloadFoto').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var fotoUrl = $('#previewFoto').attr('src');
        var fileName = 'foto-profile-{{ $siswa->nama_lengkap }}.jpg';
        
        // Check if it's a real uploaded photo or avatar
        if (fotoUrl.includes('ui-avatars.com')) {
            toastr.warning('Avatar otomatis tidak dapat didownload. Upload foto asli terlebih dahulu.', 'Info');
            return;
        }
        
        // Create temporary link and trigger download
        fetch(fotoUrl)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                toastr.success('Foto berhasil didownload!', '', {
                    timeOut: 2000,
                    progressBar: true
                });
            })
            .catch(error => {
                console.error('Download error:', error);
                toastr.error('Gagal mendownload foto', 'Error');
            });
    });
    
    // Close modal on backdrop click
    $('#modalFotoPreview').on('click', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
    
    const tanggalLahirHidden = document.getElementById('tanggal_lahir');
    const tanggalLahirDesktopInput = document.getElementById('tanggal_lahir_picker');
    const tanggalLahirMobileInput = document.getElementById('tanggal_lahir_mobile');
    const tanggalLahirDefault = tanggalLahirHidden ? tanggalLahirHidden.value : '';
    const isMobileViewport = window.matchMedia('(max-width: 767.98px)').matches;
    let tanggalLahirPicker = null;

    function syncTanggalLahirInputs(value, displayText = null) {
        const normalizedValue = value || '';
        $('#tanggal_lahir').val(normalizedValue).removeClass('is-invalid');
        $('#tanggal_lahir_picker, #tanggal_lahir_mobile').removeClass('is-invalid');

        if (tanggalLahirMobileInput) {
            tanggalLahirMobileInput.value = normalizedValue;
        }

        if (tanggalLahirDesktopInput) {
            if (displayText !== null) {
                tanggalLahirDesktopInput.value = displayText;
            } else if (!normalizedValue) {
                tanggalLahirDesktopInput.value = '';
            }
        }
    }

    if (!isMobileViewport && tanggalLahirDesktopInput) {
        tanggalLahirPicker = flatpickr("#tanggal_lahir_picker", {
            dateFormat: "j F Y",
            locale: "id",
            maxDate: "today",
            defaultDate: tanggalLahirDefault || null,
            allowInput: false,
            clickOpens: true,
            yearSelectorType: "dropdown",
            animate: true,
            disableMobile: true,
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('flatpickr-custom');
                if (selectedDates.length) {
                    syncTanggalLahirInputs(
                        instance.formatDate(selectedDates[0], "Y-m-d"),
                        dateStr
                    );
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                const hiddenValue = selectedDates.length
                    ? instance.formatDate(selectedDates[0], "Y-m-d")
                    : '';

                syncTanggalLahirInputs(hiddenValue, dateStr);

                if (hiddenValue) {
                    toastr.success('Tanggal lahir: ' + dateStr, '', {
                        timeOut: 2000,
                        closeButton: false,
                        progressBar: true
                    });
                }
            }
        });
    } else if (tanggalLahirMobileInput) {
        tanggalLahirMobileInput.addEventListener('change', function() {
            const selectedValue = this.value || '';
            let displayText = '';

            if (selectedValue) {
                const parsedDate = new Date(selectedValue + 'T00:00:00');
                displayText = parsedDate.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            }

            syncTanggalLahirInputs(selectedValue, displayText);
        });

        syncTanggalLahirInputs(tanggalLahirDefault);
    }
    
    // Toggle alamat siswa form
    function toggleAlamatSiswa() {
        if ($('#alamat_lainnya').is(':checked')) {
            // Alamat Lainnya (Berbeda)
            $('#form-jenis-tempat-tinggal').slideDown();
            $('#form-alamat-siswa').slideUp(); // Hide initially, will show based on jenis_tempat_tinggal
            $('#alert-asrama').slideUp();
            
            // Enable jenis tempat tinggal dropdown
            $('#jenis_tempat_tinggal').prop('disabled', false);
            
            // Reset jenis tempat tinggal if not already set
            if (!$('#jenis_tempat_tinggal').val()) {
                $('#jenis_tempat_tinggal').val('');
            }
            
            // Reset form fields only if no jenis selected
            if (!$('#jenis_tempat_tinggal').val()) {
                $('#alamat_siswa').val('');
                $('#rt_siswa').val('');
                $('#rw_siswa').val('');
                $('#kodepos_siswa').val('');
                $('#provinsi_id_siswa').val('').trigger('change');
                $('#kabupaten_id_siswa').html('<option value="">Pilih Kabupaten/Kota</option>');
                $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>');
                $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>');
            }
            
            toastr.info('Pilih jenis tempat tinggal terlebih dahulu', '', {timeOut: 2000});
        } else {
            // Sama dengan Alamat Orangtua
            $('#form-jenis-tempat-tinggal').slideUp();
            $('#alert-asrama').slideUp();
            $('#form-alamat-siswa').slideDown();
            
            if ($('#alamat_sama').is(':checked')) {
                loadAlamatOrtu();
                
                // Make ALL fields completely disabled (readonly + disabled)
                setTimeout(function() {
                    $('#jenis_tempat_tinggal').prop('disabled', true);
                    $('#form-alamat-siswa input, #form-alamat-siswa textarea').prop('readonly', true).prop('disabled', true);
                    $('#form-alamat-siswa select').prop('disabled', true);
                    $('#form-alamat-siswa').addClass('bg-light');
                    
                    toastr.success('Alamat orangtua berhasil dimuat', '', {timeOut: 2000});
                }, 100);
            }
        }
    }

    // Load alamat ortu dengan AJAX
    function loadAlamatOrtu() {
        // Show loading indicator
        $('#form-alamat-siswa').prepend('<div class="alert alert-info" id="loadingAlamat"><i class="fas fa-spinner fa-spin"></i> Memuat alamat orangtua...</div>');
        
        $.get('{{ route("siswa.profile.alamat-ortu") }}', function(data) {
            $('#loadingAlamat').remove();
            
            if (data.error) {
                console.error('Error loading alamat ortu:', data.error);
                toastr.error('Gagal memuat alamat orangtua: ' + data.error);
                return;
            }
            
            // Populate siswa address fields with ortu address data
            $('#alamat_siswa').val(data.alamat_ortu || '');
            $('#rt_siswa').val(data.rt_ortu || '');
            $('#rw_siswa').val(data.rw_ortu || '');
            $('#kodepos_siswa').val(data.kodepos || '');
            
            // Load province first
            if (data.provinsi_id) {
                $('#provinsi_id_siswa').val(data.provinsi_id).trigger('change');
                
                // Wait for cities to load, then set kabupaten
                setTimeout(function() {
                    if (data.kabupaten_id) {
                        $('#kabupaten_id_siswa').val(data.kabupaten_id).trigger('change');
                        
                        // Wait for districts to load, then set kecamatan
                        setTimeout(function() {
                            if (data.kecamatan_id) {
                                $('#kecamatan_id_siswa').val(data.kecamatan_id).trigger('change');
                                
                                // Wait for villages to load, then set kelurahan
                                setTimeout(function() {
                                    if (data.kelurahan_id) {
                                        $('#kelurahan_id_siswa').val(data.kelurahan_id);
                                    }
                                    
                                    // Show success message
                                    toastr.success('Alamat orangtua berhasil dimuat', '', {
                                        timeOut: 2000
                                    });
                                }, 600);
                            }
                        }, 600);
                    }
                }, 600);
            } else {
                toastr.warning('Data alamat orangtua belum lengkap. Silakan lengkapi di halaman Data Orangtua.');
            }
        }).fail(function(xhr, status, error) {
            $('#loadingAlamat').remove();
            console.error('Failed to load alamat ortu:', error);
            toastr.error('Gagal memuat alamat orangtua. Coba lagi atau hubungi admin.');
        });
    }

    $('input[name="alamat_sama_ortu"]').on('change', toggleAlamatSiswa);

    // Initialize on page load
    toggleAlamatSiswa();
    
    // Handle jenis tempat tinggal change
    $('#jenis_tempat_tinggal').on('change', function() {
        var jenisTempat = $(this).val();
        
        if (jenisTempat === 'Asrama') {
            // If Asrama selected, hide address form and show info
            $('#form-alamat-siswa').slideUp();
            $('#alert-asrama').slideDown();
            
            // Clear and disable address fields (will be filled by backend with school address)
            $('#alamat_siswa').val('Asrama Sekolah').prop('readonly', true).prop('disabled', true);
            $('#rt_siswa').val('').prop('readonly', true).prop('disabled', true);
            $('#rw_siswa').val('').prop('readonly', true).prop('disabled', true);
            $('#kodepos_siswa').val('').prop('readonly', true).prop('disabled', true);
            $('#provinsi_id_siswa').val('').prop('disabled', true);
            $('#kabupaten_id_siswa').html('<option value="">Pilih Kabupaten/Kota</option>').prop('disabled', true);
            $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>').prop('disabled', true);
            $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>').prop('disabled', true);
            
            toastr.success('Alamat asrama akan otomatis terisi', '', {timeOut: 2000});
        } else if (jenisTempat === 'Kost/Kontrakan' || jenisTempat === 'Saudara') {
            // Show address form for Kost or Saudara
            $('#alert-asrama').slideUp();
            $('#form-alamat-siswa').slideDown();
            
            // Enable and reset all fields
            $('#form-alamat-siswa').removeClass('bg-light');
            $('#alamat_siswa').val('').prop('readonly', false).prop('disabled', false);
            $('#rt_siswa').val('').prop('readonly', false).prop('disabled', false);
            $('#rw_siswa').val('').prop('readonly', false).prop('disabled', false);
            $('#kodepos_siswa').val('').prop('readonly', false).prop('disabled', false);
            $('#provinsi_id_siswa').val('').prop('disabled', false);
            $('#kabupaten_id_siswa').html('<option value="">Pilih Kabupaten/Kota</option>').prop('disabled', false);
            $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>').prop('disabled', false);
            $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>').prop('disabled', false);
            
            toastr.info('Silakan isi alamat ' + jenisTempat.toLowerCase() + ' Anda', '', {timeOut: 2000});
        } else {
            // No selection, hide both
            $('#alert-asrama').slideUp();
            $('#form-alamat-siswa').slideUp();
        }
    });
    
    // Trigger on page load if already selected
    if ($('#jenis_tempat_tinggal').val()) {
        $('#jenis_tempat_tinggal').trigger('change');
    }
    
    // Enable all form fields before submit to ensure data is sent
    $('#formDataDiri').on('submit', function(e) {
        if (tanggalLahirPicker && tanggalLahirPicker.selectedDates.length) {
            $('#tanggal_lahir').val(
                tanggalLahirPicker.formatDate(tanggalLahirPicker.selectedDates[0], 'Y-m-d')
            );
        } else if (tanggalLahirMobileInput && tanggalLahirMobileInput.value) {
            $('#tanggal_lahir').val(tanggalLahirMobileInput.value);
        }

        // If alamat sama dengan ortu is selected, temporarily enable all fields for submission
        if ($('#alamat_sama').is(':checked')) {
            $('#form-alamat-siswa input, #form-alamat-siswa select, #form-alamat-siswa textarea').prop('disabled', false);
        }
        
        // If Asrama selected, enable fields for submission
        if ($('#jenis_tempat_tinggal').val() === 'Asrama') {
            $('#form-alamat-siswa input, #form-alamat-siswa select, #form-alamat-siswa textarea').prop('disabled', false).prop('readonly', false);
        }
        // Form will submit normally, no need to prevent default
    });

    // Cascade dropdown untuk alamat siswa
    $('#provinsi_id_siswa').on('change', function() {
        var provinceCode = $(this).val();
        $('#kabupaten_id_siswa').html('<option value="">Memuat...</option>');
        $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>');
        $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (provinceCode) {
            $.get('{{ url("siswa/api/cities") }}/' + provinceCode, function(data) {
                var options = '<option value="">Pilih Kabupaten/Kota</option>';
                $.each(data, function(key, city) {
                    options += '<option value="' + city.code + '">' + city.name + '</option>';
                });
                $('#kabupaten_id_siswa').html(options);
            });
        } else {
            $('#kabupaten_id_siswa').html('<option value="">Pilih Kabupaten/Kota</option>');
        }
    });

    $('#kabupaten_id_siswa').on('change', function() {
        var cityCode = $(this).val();
        $('#kecamatan_id_siswa').html('<option value="">Memuat...</option>');
        $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (cityCode) {
            $.get('{{ url("siswa/api/districts") }}/' + cityCode, function(data) {
                var options = '<option value="">Pilih Kecamatan</option>';
                $.each(data, function(key, district) {
                    options += '<option value="' + district.code + '">' + district.name + '</option>';
                });
                $('#kecamatan_id_siswa').html(options);
            });
        } else {
            $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>');
        }
    });

    $('#kecamatan_id_siswa').on('change', function() {
        var districtCode = $(this).val();
        $('#kelurahan_id_siswa').html('<option value="">Memuat...</option>');
        
        if (districtCode) {
            $.get('{{ url("siswa/api/villages") }}/' + districtCode, function(data) {
                var options = '<option value="">Pilih Kelurahan/Desa</option>';
                $.each(data, function(key, village) {
                    options += '<option value="' + village.code + '">' + village.name + '</option>';
                });
                $('#kelurahan_id_siswa').html(options);
            });
        } else {
            $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>');
        }
    });

    // Search Sekolah by NPSN - Function
    function searchSekolahByNPSN() {
        var npsn = $('#npsn_asal_sekolah').val().trim().toUpperCase();
        
        // Validate NPSN format (8 characters, alphanumeric)
        if (npsn.length !== 8 || !/^[A-Z0-9]+$/.test(npsn)) {
            return; // Silent return for autocomplete (not 8 characters yet)
        }
        
        // Disable button & show loading
        var $btn = $('#btnCariSekolah');
        var originalBtnHtml = '<i class="fas fa-search"></i> Cari';
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mencari...');
        
        // Hide previous alerts
        $('#alertSekolahFound, #alertSekolahNotFound').hide();
        
        $.ajax({
            url: '{{ route("siswa.profile.search-sekolah") }}',
            method: 'GET',
            data: { npsn: npsn },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var source = response.source;
                    
                    // Fill form fields
                    $('#nama_sekolah').val(data.nama);
                    $('#status_sekolah').val(data.status || '-');
                    $('#bentuk_pendidikan').val(data.bentuk_pendidikan || '-');
                    $('#alamat_sekolah').val(data.alamat_lengkap || '-');
                    
                    // Show success alert with source info
                    var sourceText = source === 'database' ? '(Data dari database lokal)' : '(Data baru dari Kemendikbud)';
                    $('#sourceInfo').text(sourceText);
                    $('#alertSekolahFound').fadeIn();
                    
                    toastr.success('Data sekolah berhasil ditemukan!');
                    
                    // Change button to success state briefly, then back to normal
                    $btn.removeClass('btn-info').addClass('btn-success')
                        .html('<i class="fas fa-check-circle"></i> Ditemukan');
                    
                    setTimeout(function() {
                        $btn.removeClass('btn-success').addClass('btn-info')
                            .prop('disabled', false)
                            .html(originalBtnHtml);
                    }, 2000);
                } else {
                    $('#alertSekolahNotFound').fadeIn();
                    toastr.error(response.message || 'Data tidak ditemukan');
                    
                    // Reset button immediately on failure
                    $btn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr) {
                $('#alertSekolahNotFound').fadeIn();
                
                var message = 'Terjadi kesalahan saat mencari data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                toastr.error(message);
                
                // Reset button immediately on error
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    }
    
    // Debounce function for NPSN input
    var npsnDebounceTimer;
    $('#npsn_asal_sekolah').on('input', function() {
        clearTimeout(npsnDebounceTimer);
        var npsn = $(this).val().trim().toUpperCase();
        
        // Only trigger auto-search when exactly 8 alphanumeric characters entered
        if (npsn.length === 8 && /^[A-Z0-9]+$/.test(npsn)) {
            npsnDebounceTimer = setTimeout(function() {
                searchSekolahByNPSN();
            }, 500); // 500ms debounce
        }
    });
    
    // Button click still works
    $('#btnCariSekolah').on('click', function() {
        var npsn = $('#npsn_asal_sekolah').val().trim().toUpperCase();
        if (npsn.length !== 8 || !/^[A-Z0-9]+$/.test(npsn)) {
            toastr.error('NPSN harus 8 karakter (huruf/angka)');
            return;
        }
        searchSekolahByNPSN();
    });
    
    // Auto-search on page load ONLY if NPSN exists but fields are empty
    @if($siswa->npsn_asal_sekolah && $siswa->sekolahAsal)
        // Data sudah ada, langsung fill tanpa trigger button (silent load)
        $('#nama_sekolah').val({!! json_encode($siswa->sekolahAsal->nama ?? '') !!});
        $('#status_sekolah').val({!! json_encode($siswa->sekolahAsal->status ?? '') !!} || '-');
        $('#bentuk_pendidikan').val({!! json_encode($siswa->sekolahAsal->bentuk_pendidikan ?? '') !!} || '-');
        $('#alamat_sekolah').val({!! json_encode($siswa->sekolahAsal->alamat_lengkap ?? '') !!} || '-');
        
        // No alert, no loading state - just silent populate
        console.log('Data sekolah loaded from existing relation');
    @elseif($siswa->npsn_asal_sekolah)
        // NPSN ada tapi belum ada di relasi sekolah, trigger search
        console.log('NPSN exists but no school relation, triggering search...');
        $('#btnCariSekolah').trigger('click');
    @endif

    // Scroll to first error on validation failure
    @if($errors->any())
        setTimeout(function() {
            var firstError = $('.is-invalid:first');
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                firstError.focus();
                
                toastr.error('{{ $errors->count() }} field perlu diperbaiki. Silakan periksa form!', 'Validasi Gagal');
            }
        }, 300);
    @endif

    // Load initial data for edit mode
    @if(old('provinsi_id_siswa', $siswa->provinsi_id_siswa ?? ''))
        $('#provinsi_id_siswa').trigger('change');
        setTimeout(function() {
            $('#kabupaten_id_siswa').val('{{ old('kabupaten_id_siswa', $siswa->kabupaten_id_siswa ?? '') }}').trigger('change');
            setTimeout(function() {
                $('#kecamatan_id_siswa').val('{{ old('kecamatan_id_siswa', $siswa->kecamatan_id_siswa ?? '') }}').trigger('change');
                setTimeout(function() {
                    $('#kelurahan_id_siswa').val('{{ old('kelurahan_id_siswa', $siswa->kelurahan_id_siswa ?? '') }}');
                }, 500);
            }, 500);
        }, 500);
    @endif
});
</script>
@stop
