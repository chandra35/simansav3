@extends('adminlte::page')

@section('title', 'Data Diri - SIMANSA')

@section('plugins.BsCustomFileInput', true)

@section('css')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
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
    #tanggal_lahir {
        cursor: pointer;
    }
    
    #tanggal_lahir:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
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
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Main Image */
    .lightbox-image {
        width: 100%;
        height: auto;
        max-height: 80vh;
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
            border-radius: 15px;
        }

        .lightbox-image {
            max-height: 70vh;
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
</style>
@stop

@section('content_header')
    <h1><i class="fas fa-id-card"></i> Data Diri Siswa</h1>
@stop

@section('content')
<!-- Info Progress -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-success">
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

    <div class="row">
        <!-- Foto Profile -->
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera"></i> Foto Profile</h3>
                </div>
                <div class="card-body text-center">
                    <div class="form-group">
                        <div class="mb-3 position-relative foto-container">
                            <img id="previewFoto" 
                                 src="{{ $siswa->foto_profile_url }}" 
                                 class="img-thumbnail foto-clickable" 
                                 style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.3s ease;"
                                 alt="Foto Profile {{ $siswa->nama_lengkap }}"
                                 data-toggle="tooltip" 
                                 data-placement="top" 
                                 title="Klik untuk melihat foto ukuran penuh"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_lengkap) }}&size=400&background=6c757d&color=fff'">
                            
                            @if(!$siswa->foto_profile)
                                <div class="position-absolute" style="bottom: 10px; left: 50%; transform: translateX(-50%);">
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
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="foto_profile" name="foto_profile" accept="image/jpeg,image/jpg,image/png">
                            <label class="custom-file-label" for="foto_profile">Pilih Foto</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> JPG/PNG, Max 2MB, Square (1:1)
                        </small>
                        @if(!$siswa->foto_profile)
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-lightbulb"></i> Upload foto sendiri untuk tampilan lebih personal
                            </small>
                        @endif
                        @error('foto_profile')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Asal Sekolah (NPSN) -->
        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-school"></i> Asal Sekolah (MTs/SMP)</h3>
                </div>
                <div class="card-body">
                    <!-- NPSN Input with Search -->
                    <div class="form-group">
                        <label>NPSN Asal Sekolah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="npsn_asal_sekolah" id="npsn_asal_sekolah" 
                                   class="form-control @error('npsn_asal_sekolah') is-invalid @enderror" 
                                   placeholder="Contoh: 10648374" 
                                   maxlength="8" 
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Masukkan 8 digit NPSN kemudian klik tombol <strong>Cari</strong>
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Nama Sekolah</small></label>
                                <input type="text" id="nama_sekolah" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Status</small></label>
                                <input type="text" id="status_sekolah" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-1"><small>Bentuk Pendidikan</small></label>
                                <input type="text" id="bentuk_pendidikan" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
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

    <div class="row">
        <div class="col-12">
            <!-- Data Pribadi -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Data Pribadi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_lahir">
                                    Tanggal Lahir <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           name="tanggal_lahir" 
                                           id="tanggal_lahir" 
                                           class="form-control flatpickr @error('tanggal_lahir') is-invalid @enderror" 
                                           value="{{ old('tanggal_lahir', $siswa->tanggal_lahir ?? '') }}" 
                                           placeholder="Pilih Tanggal Lahir"
                                           required
                                           readonly>
                                    @error('tanggal_lahir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Klik untuk membuka kalender
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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

    <div class="row">
        <div class="col-12">
            <!-- Alamat Siswa -->
            <div class="card card-outline card-success">
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="alamat_sama_ortu" 
                                           id="alamat_sama" value="1" 
                                           {{ old('alamat_sama_ortu', $siswa->alamat_sama_ortu ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="alamat_sama">
                                        <i class="fas fa-home text-primary"></i> Sama dengan Alamat Orangtua
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="alamat_sama_ortu" 
                                           id="alamat_lainnya" value="0" 
                                           {{ old('alamat_sama_ortu', $siswa->alamat_sama_ortu ?? 0) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="alamat_lainnya">
                                        <i class="fas fa-map-marker-alt text-success"></i> Alamat Lainnya (Berbeda)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Jika memilih "Sama dengan Orangtua", data alamat akan otomatis terisi
                        </small>
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
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
                            <div class="col-md-4">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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

                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Simpan Data Diri
                    </button>
                    <a href="{{ route('siswa.dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Batal
                    </a>
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
@stop

@section('css')
    <style>
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

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Foto Preview - Click to Open Modal
    $('#previewFoto').on('click', function() {
        var imgSrc = $(this).attr('src');
        $('#modalFotoImg').attr('src', imgSrc);
        $('#modalFotoPreview').modal('show');
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
    
    // Initialize Flatpickr for tanggal_lahir
    flatpickr("#tanggal_lahir", {
        dateFormat: "Y-m-d",
        locale: "id",
        maxDate: "today",
        defaultDate: "{{ old('tanggal_lahir', $siswa->tanggal_lahir ?? '') }}",
        allowInput: false,
        clickOpens: true,
        altInput: true,
        altFormat: "j F Y",
        yearSelectorType: "dropdown",
        animate: true,
        onReady: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.classList.add('flatpickr-custom');
        },
        onChange: function(selectedDates, dateStr, instance) {
            console.log('Tanggal dipilih:', dateStr);
            
            // Remove invalid feedback if exists
            $('#tanggal_lahir').removeClass('is-invalid');
            
            // Show success feedback
            if (dateStr) {
                toastr.success('Tanggal lahir: ' + instance.altInput.value, '', {
                    timeOut: 2000,
                    closeButton: false,
                    progressBar: true
                });
            }
        }
    });
    
    // Toggle alamat siswa form
    function toggleAlamatSiswa() {
        if ($('#alamat_lainnya').is(':checked')) {
            $('#form-alamat-siswa').slideDown();
        } else {
            $('#form-alamat-siswa').slideUp();
            // If alamat sama dengan ortu, ambil data ortu
            if ($('#alamat_sama').is(':checked')) {
                loadAlamatOrtu();
            }
        }
    }

    // Load alamat ortu dengan AJAX
    function loadAlamatOrtu() {
        $.get('{{ route("siswa.profile.alamat-ortu") }}', function(data) {
            if (data.error) {
                console.error('Error loading alamat ortu:', data.error);
                return;
            }
            
            // Populate siswa address fields with ortu address data
            $('#alamat_siswa').val(data.alamat_ortu);
            $('#rt_siswa').val(data.rt_ortu);
            $('#rw_siswa').val(data.rw_ortu);
            $('#kodepos_siswa').val(data.kodepos);
            
            // Load province first
            if (data.provinsi_id) {
                $('#provinsi_id_siswa').val(data.provinsi_id).trigger('change');
                
                // Wait for cities to load, then set kabupaten
                setTimeout(function() {
                    $('#kabupaten_id_siswa').val(data.kabupaten_id).trigger('change');
                    
                    // Wait for districts to load, then set kecamatan
                    setTimeout(function() {
                        $('#kecamatan_id_siswa').val(data.kecamatan_id).trigger('change');
                        
                        // Wait for villages to load, then set kelurahan
                        setTimeout(function() {
                            $('#kelurahan_id_siswa').val(data.kelurahan_id);
                        }, 500);
                    }, 500);
                }, 500);
            }
        }).fail(function(xhr, status, error) {
            console.error('Failed to load alamat ortu:', error);
        });
    }

    $('input[name="alamat_sama_ortu"]').on('change', toggleAlamatSiswa);

    // Initialize on page load
    toggleAlamatSiswa();

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

    // Preview foto before upload dengan animasi
    $('#foto_profile').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            // Validasi ukuran file (max 2MB)
            if (file.size > 2048000) {
                toastr.error('Ukuran file maksimal 2MB');
                $(this).val('');
                return;
            }
            
            // Validasi tipe file
            if (!file.type.match('image/jpeg') && !file.type.match('image/jpg') && !file.type.match('image/png')) {
                toastr.error('Format file harus JPG, JPEG, atau PNG');
                $(this).val('');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                // Fade out, change src, fade in
                $('#previewFoto').fadeOut(200, function() {
                    $(this).attr('src', e.target.result).fadeIn(200);
                });
                
                // Hapus badge "Avatar Otomatis"
                $('.badge-info').fadeOut();
            };
            reader.readAsDataURL(file);
            
            // Update label dengan animasi
            $(this).next('.custom-file-label').html('<i class="fas fa-check-circle text-success"></i> ' + file.name);
            
            toastr.success('Foto siap diupload. Klik Simpan untuk menyimpan perubahan.');
        }
    });
    
    // Search Sekolah by NPSN
    $('#btnCariSekolah').on('click', function() {
        var npsn = $('#npsn_asal_sekolah').val().trim();
        
        // Validate NPSN format
        if (npsn.length !== 8 || !/^\d+$/.test(npsn)) {
            toastr.error('NPSN harus 8 digit angka');
            return;
        }
        
        // Disable button & show loading
        var $btn = $(this);
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
                } else {
                    $('#alertSekolahNotFound').fadeIn();
                    toastr.error(response.message || 'Data tidak ditemukan');
                }
            },
            error: function(xhr) {
                $('#alertSekolahNotFound').fadeIn();
                
                var message = 'Terjadi kesalahan saat mencari data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                toastr.error(message);
            },
            complete: function() {
                // Re-enable button
                $btn.prop('disabled', false).html('<i class="fas fa-search"></i> Cari');
            }
        });
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
