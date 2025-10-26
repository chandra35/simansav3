@extends('adminlte::page')

@section('title', 'Pengaturan Aplikasi')

@section('css')
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.4/dist/select2-bootstrap4.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Sortable.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
    
    <style>
        /* Card Styling */
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
        
        .card-secondary .card-header {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        }
        
        /* Form Styling */
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .select2-container--bootstrap4 .select2-selection {
            border-radius: 6px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }
        
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.5rem + 2px) !important;
        }
        
        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 2.5rem !important;
            padding-left: 0.75rem;
        }
        
        .select2-container--bootstrap4 .select2-selection__arrow {
            height: calc(2.5rem + 2px) !important;
        }
        
        /* Upload Area Styling */
        .upload-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 3px dashed #ced4da;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            min-height: 320px;
        }
        
        .upload-area:hover {
            border-color: #007bff;
            background: linear-gradient(135deg, #e7f3ff 0%, #d4e9ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.2);
        }
        
        .upload-area.dragover {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-style: solid;
        }
        
        .upload-content {
            pointer-events: none;
        }
        
        .preview-container {
            min-height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .preview-container img {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            padding: 0.5rem;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .upload-placeholder i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .upload-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 6px;
            display: inline-block;
        }
        
        .upload-actions {
            pointer-events: all;
        }
        
        .upload-actions .btn {
            margin: 0 0.25rem;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
        }
        
        .btn-upload:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        
        .btn-save-logo {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border: none;
            color: white;
        }
        
        .btn-remove-logo {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
        }
        
        /* Kepala Sekolah Card */
        .kepala-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1.25rem;
            border: 2px solid #dee2e6;
        }
        
        .kepala-info-card img {
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* Kop Builder */
        .kop-preview-container {
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            min-height: 200px;
        }
        
        .kop-preview-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .kop-preview-table td {
            vertical-align: top;
            padding: 0.5rem;
        }
        
        .element-item {
            border: 2px solid #dee2e6;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            background: #fff;
            transition: all 0.3s ease;
        }
        
        .element-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        }
        
        .handle {
            cursor: grab;
            color: #6c757d;
            margin-right: 0.5rem;
        }
        
        .handle:active {
            cursor: grabbing;
        }
        
        .sortable-ghost {
            opacity: 0.5;
            background: #e3f2fd;
        }
        
        /* Buttons */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        /* Info Callout */
        .callout {
            border-radius: 6px;
            border-left-width: 4px;
        }
        
        /* Row Spacing */
        .row.form-row {
            margin-left: -8px;
            margin-right: -8px;
        }
        
        .row.form-row > [class*='col-'] {
            padding-left: 8px;
            padding-right: 8px;
        }
        
        /* Element Preview for Kop Builder */
        .element-preview {
            margin-top: 5px;
            padding: 5px;
            background: #f8f9fa;
            border-left: 3px solid #007bff;
            font-size: 13px;
        }
        
        .preview-area {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        #livePreview {
            transition: all 0.2s;
        }
        
        .custom-file-label::after {
            content: "Browse";
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Pengaturan Aplikasi</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
        @csrf
        @method('PUT')

        {{-- Card 1: Identitas Sekolah & Logo --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-school"></i> Identitas Sekolah & Logo</h3>
            </div>
            <div class="card-body">
                {{-- Section: Identitas --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="nama_sekolah">Nama Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_sekolah" id="nama_sekolah" 
                                   class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                   value="{{ old('nama_sekolah', $setting->nama_sekolah) }}" 
                                   placeholder="Contoh: MTs NEGERI 1 KOTA KUPANG"
                                   required>
                            @error('nama_sekolah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="npsn">NPSN <span class="text-danger">*</span></label>
                            <input type="text" name="npsn" id="npsn" 
                                   class="form-control @error('npsn') is-invalid @enderror" 
                                   value="{{ old('npsn', $setting->npsn) }}" 
                                   placeholder="8 digit angka"
                                   maxlength="8" required>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Nomor Pokok Sekolah Nasional (8 digit)</small>
                            @error('npsn')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                {{-- Section: Logo Upload --}}
                <h5 class="mb-3">
                    <i class="fas fa-image text-primary"></i> Logo Sekolah
                    <small class="text-muted font-weight-normal"> - Drag & Drop atau Klik untuk Upload</small>
                </h5>
                
                <div class="row form-row">
                    {{-- Logo Kemenag --}}
                    <div class="col-lg-6">
                        <div class="upload-area" id="uploadAreaKemenag">
                            <input type="file" id="logoKemenag" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                            <div class="upload-content">
                                <div class="preview-container">
                                    <img id="previewLogoKemenag" 
                                         src="{{ $setting->logo_kemenag_url }}" 
                                         alt="Logo Kemenag"
                                         style="max-width: 150px; max-height: 150px; display: {{ $setting->logo_kemenag_path ? 'block' : 'none' }};">
                                    <div class="upload-placeholder" style="display: {{ $setting->logo_kemenag_path ? 'none' : 'block' }};">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-1">Drag & Drop atau Klik</p>
                                        <p class="text-muted small mb-0">PNG/JPG, Max 2MB</p>
                                    </div>
                                </div>
                                <div class="upload-label">
                                    <strong><i class="fas fa-flag"></i> Logo Kemenag</strong>
                                </div>
                                <div class="upload-actions mt-2">
                                    <button type="button" class="btn btn-sm btn-primary btn-upload" data-target="logoKemenag">
                                        <i class="fas fa-folder-open"></i> Pilih File
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success btn-save-logo" id="btnUploadKemenag" style="display: none;">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-logo" data-target="logoKemenag" style="display: {{ $setting->logo_kemenag_path ? 'inline-block' : 'none' }};">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Rekomendasi: 200x200px (persegi)
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Logo Sekolah --}}
                    <div class="col-lg-6">
                        <div class="upload-area" id="uploadAreaSekolah">
                            <input type="file" id="logoSekolah" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                            <div class="upload-content">
                                <div class="preview-container">
                                    <img id="previewLogoSekolah" 
                                         src="{{ $setting->logo_sekolah_url }}" 
                                         alt="Logo Sekolah"
                                         style="max-width: 150px; max-height: 150px; display: {{ $setting->logo_sekolah_path ? 'block' : 'none' }};">
                                    <div class="upload-placeholder" style="display: {{ $setting->logo_sekolah_path ? 'none' : 'block' }};">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-1">Drag & Drop atau Klik</p>
                                        <p class="text-muted small mb-0">PNG/JPG, Max 2MB</p>
                                    </div>
                                </div>
                                <div class="upload-label">
                                    <strong><i class="fas fa-university"></i> Logo Sekolah</strong>
                                </div>
                                <div class="upload-actions mt-2">
                                    <button type="button" class="btn btn-sm btn-primary btn-upload" data-target="logoSekolah">
                                        <i class="fas fa-folder-open"></i> Pilih File
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success btn-save-logo" id="btnUploadSekolah" style="display: none;">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-logo" data-target="logoSekolah" style="display: {{ $setting->logo_sekolah_path ? 'inline-block' : 'none' }};">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Rekomendasi: 200x200px (persegi)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Alamat (Laravolt Indonesia) --}}
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat Sekolah</h3>
            </div>
            <div class="card-body">
                {{-- Section: Alamat Detail --}}
                <div class="form-group">
                    <label for="alamat"><i class="fas fa-road"></i> Alamat Lengkap (Jalan) <span class="text-danger">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3" 
                              class="form-control @error('alamat') is-invalid @enderror" 
                              placeholder="Contoh: Jl. Timor Raya No. 81"
                              required>{{ old('alamat', $setting->alamat) }}</textarea>
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Tuliskan alamat lengkap termasuk nomor rumah/gedung</small>
                    @error('alamat')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="row form-row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="rt">RT</label>
                            <input type="text" name="rt" id="rt" 
                                   class="form-control @error('rt') is-invalid @enderror" 
                                   value="{{ old('rt', $setting->rt) }}" 
                                   placeholder="001"
                                   maxlength="3">
                            @error('rt')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="rw">RW</label>
                            <input type="text" name="rw" id="rw" 
                                   class="form-control @error('rw') is-invalid @enderror" 
                                   value="{{ old('rw', $setting->rw) }}" 
                                   placeholder="001"
                                   maxlength="3">
                            @error('rw')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                {{-- Section: Wilayah --}}
                <h5 class="mb-3">
                    <i class="fas fa-globe-asia text-info"></i> Wilayah Administratif
                    <small class="text-muted font-weight-normal"> - Cascading Dropdown</small>
                </h5>
                
                <div class="row form-row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="provinsi_code"><i class="fas fa-map"></i> Provinsi <span class="text-danger">*</span></label>
                            <select name="provinsi_code" id="provinsi_code" 
                                    class="form-control select2 @error('provinsi_code') is-invalid @enderror" 
                                    required style="width: 100%;">
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach($provinsiList as $prov)
                                    <option value="{{ $prov->code }}" 
                                        {{ old('provinsi_code', $setting->provinsi_code) == $prov->code ? 'selected' : '' }}>
                                        {{ $prov->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provinsi_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="kota_code"><i class="fas fa-city"></i> Kota/Kabupaten <span class="text-danger">*</span></label>
                            <select name="kota_code" id="kota_code" 
                                    class="form-control select2 @error('kota_code') is-invalid @enderror" 
                                    required style="width: 100%;">
                                <option value="">-- Pilih Kota/Kabupaten --</option>
                                @if($setting->kota)
                                    <option value="{{ $setting->kota->code }}" selected>{{ $setting->kota->name }}</option>
                                @endif
                            </select>
                            @error('kota_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="kecamatan_code"><i class="fas fa-building"></i> Kecamatan <span class="text-danger">*</span></label>
                            <select name="kecamatan_code" id="kecamatan_code" 
                                    class="form-control select2 @error('kecamatan_code') is-invalid @enderror" 
                                    required style="width: 100%;">
                                <option value="">-- Pilih Kecamatan --</option>
                                @if($setting->kecamatan)
                                    <option value="{{ $setting->kecamatan->code }}" selected>{{ $setting->kecamatan->name }}</option>
                                @endif
                            </select>
                            @error('kecamatan_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="kelurahan_code"><i class="fas fa-home"></i> Kelurahan/Desa <span class="text-danger">*</span></label>
                            <select name="kelurahan_code" id="kelurahan_code" 
                                    class="form-control select2 @error('kelurahan_code') is-invalid @enderror" 
                                    required style="width: 100%;">
                                <option value="">-- Pilih Kelurahan/Desa --</option>
                                @if($setting->kelurahan)
                                    <option value="{{ $setting->kelurahan->code }}" selected>{{ $setting->kelurahan->name }}</option>
                                @endif
                            </select>
                            @error('kelurahan_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="kode_pos">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" name="kode_pos" id="kode_pos" 
                                   class="form-control @error('kode_pos') is-invalid @enderror" 
                                   value="{{ old('kode_pos', $setting->kode_pos) }}" 
                                   placeholder="85111"
                                   maxlength="5" required>
                            <small class="text-muted"><i class="fas fa-info-circle text-info"></i> Isi kode pos sesuai wilayah</small>
                            @error('kode_pos')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                {{-- Info Box --}}
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                    <ul class="mb-0 pl-3">
                        <li>Dropdown wilayah akan otomatis terisi sesuai pilihan sebelumnya</li>
                        <li>Pilih <strong>Provinsi</strong> terlebih dahulu untuk memunculkan pilihan Kota/Kabupaten</li>
                        <li><strong>Kode Pos</strong> akan otomatis terisi setelah memilih Kecamatan</li>
                        <li>Data wilayah menggunakan database resmi dari Kemendagri</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Card 3: Kontak --}}
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-phone"></i> Informasi Kontak</h3>
            </div>
            <div class="card-body">
                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telepon"><i class="fas fa-phone-alt"></i> Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="telepon" id="telepon" 
                                   class="form-control @error('telepon') is-invalid @enderror" 
                                   value="{{ old('telepon', $setting->telepon) }}" 
                                   placeholder="Contoh: 0380-8553728"
                                   required>
                            @error('telepon')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $setting->email) }}" 
                                   placeholder="email@sekolah.sch.id"
                                   required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="website"><i class="fas fa-globe"></i> Website</label>
                    <input type="url" name="website" id="website" 
                           class="form-control @error('website') is-invalid @enderror" 
                           value="{{ old('website', $setting->website) }}" 
                           placeholder="https://sekolah.sch.id">
                    @error('website')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card 4: Sosial Media --}}
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-share-alt"></i> Media Sosial</h3>
            </div>
            <div class="card-body">
                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="facebook_url"><i class="fab fa-facebook text-primary"></i> Facebook</label>
                            <input type="url" name="facebook_url" id="facebook_url" 
                                   class="form-control @error('facebook_url') is-invalid @enderror" 
                                   value="{{ old('facebook_url', $setting->facebook_url) }}" 
                                   placeholder="https://facebook.com/sekolah">
                            @error('facebook_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="instagram_url"><i class="fab fa-instagram text-danger"></i> Instagram</label>
                            <input type="url" name="instagram_url" id="instagram_url" 
                                   class="form-control @error('instagram_url') is-invalid @enderror" 
                                   value="{{ old('instagram_url', $setting->instagram_url) }}" 
                                   placeholder="https://instagram.com/sekolah">
                            @error('instagram_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="youtube_url"><i class="fab fa-youtube text-danger"></i> YouTube</label>
                            <input type="url" name="youtube_url" id="youtube_url" 
                                   class="form-control @error('youtube_url') is-invalid @enderror" 
                                   value="{{ old('youtube_url', $setting->youtube_url) }}" 
                                   placeholder="https://youtube.com/@sekolah">
                            @error('youtube_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="twitter_url"><i class="fab fa-twitter text-info"></i> Twitter / X</label>
                            <input type="url" name="twitter_url" id="twitter_url" 
                                   class="form-control @error('twitter_url') is-invalid @enderror" 
                                   value="{{ old('twitter_url', $setting->twitter_url) }}" 
                                   placeholder="https://twitter.com/sekolah">
                            @error('twitter_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: Kepala Sekolah (READ ONLY) --}}
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Kepala Sekolah</h3>
                <span class="badge badge-light float-right">Otomatis dari Tugas Tambahan</span>
            </div>
            <div class="card-body">
                @if($kepalaSekolah)
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="{{ $kepalaSekolah->foto_url ?? asset('vendor/adminlte/dist/img/avatar.png') }}" 
                                 class="img-circle img-fluid" style="max-width: 150px; border: 3px solid #007bff;">
                        </div>
                        <div class="col-md-9">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="200">Nama</th>
                                    <td>: <strong>{{ $kepalaSekolah->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>: {{ $kepalaSekolah->gtk->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>: {{ $kepalaSekolah->email }}</td>
                                </tr>
                                @if($tugasTambahan)
                                <tr>
                                    <th>Periode Tugas</th>
                                    <td>: {{ \Carbon\Carbon::parse($tugasTambahan->mulai_tugas)->format('d/m/Y') }} 
                                        s/d {{ $tugasTambahan->selesai_tugas ? \Carbon\Carbon::parse($tugasTambahan->selesai_tugas)->format('d/m/Y') : 'Sekarang' }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Status</th>
                                    <td>: <span class="badge badge-success">Aktif</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> 
                        Data Kepala Sekolah diambil otomatis dari <strong>Tugas Tambahan</strong> yang aktif.
                        Untuk mengubah, silakan kelola di menu <a href="{{ route('admin.users.index') }}" class="alert-link font-weight-bold">User Management → Tugas Tambahan</a>.
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <h5><i class="fas fa-exclamation-triangle"></i> Belum ada Kepala Madrasah yang aktif!</h5>
                        <p class="mb-0">Silakan assign Kepala Madrasah di menu <a href="{{ route('admin.users.index') }}" class="alert-link font-weight-bold">User Management → Tugas Tambahan</a>.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 6: Kop Surat Builder --}}
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Kop Surat</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-light" id="btnPreviewKop">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Mode Toggle --}}
                <div class="form-group">
                    <label>Mode Kop Surat</label>
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary {{ $setting->kop_mode === 'builder' ? 'active' : '' }}">
                            <input type="radio" name="kop_mode" value="builder" 
                                   {{ $setting->kop_mode === 'builder' ? 'checked' : '' }}> 
                            <i class="fas fa-edit"></i> Builder (Editable Text)
                        </label>
                        <label class="btn btn-outline-primary {{ $setting->kop_mode === 'custom' ? 'active' : '' }}">
                            <input type="radio" name="kop_mode" value="custom" 
                                   {{ $setting->kop_mode === 'custom' ? 'checked' : '' }}> 
                            <i class="fas fa-image"></i> Upload Gambar Custom
                        </label>
                    </div>
                </div>

                {{-- Builder Mode Section --}}
                <div id="builderModeSection" style="display: {{ $setting->kop_mode === 'builder' ? 'block' : 'none' }}">
                    {{-- Preview Area --}}
                    <div class="preview-area mb-3" style="border: 2px solid #ddd; padding: 20px; background: #fff; min-height: 200px;">
                        <table width="100%" id="kopPreviewTable">
                            <tr>
                                <td width="15%" align="center" valign="top">
                                    <img id="kopPreviewLogoKemenag" src="{{ $setting->logo_kemenag_url }}" style="height: 80px;">
                                </td>
                                <td width="70%" align="center" valign="top">
                                    <div id="kop-elements-preview">
                                        {{-- Dynamic elements rendered here --}}
                                    </div>
                                </td>
                                <td width="15%" align="center" valign="top">
                                    <img id="kopPreviewLogoSekolah" src="{{ $setting->logo_sekolah_url }}" style="height: 80px;">
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- Editor Panel --}}
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title mb-0">Element Kop Surat</h4>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success" id="btnAddText">
                                    <i class="fas fa-plus"></i> Text
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnAddDivider">
                                    <i class="fas fa-minus"></i> Garis
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div id="kop-elements-list" class="list-group">
                                {{-- Sortable items rendered here --}}
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input untuk menyimpan JSON config --}}
                    <input type="hidden" name="kop_surat_config" id="kop_surat_config" value="{{ json_encode($setting->kop_surat_config) }}">
                </div>

                {{-- Custom Upload Mode Section --}}
                <div id="customModeSection" style="display: {{ $setting->kop_mode === 'custom' ? 'block' : 'none' }}">
                    <div class="form-group">
                        <label>Upload Header Kop Surat</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="kopSuratCustom" accept="image/*">
                                <label class="custom-file-label" for="kopSuratCustom">Pilih file...</label>
                            </div>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btnUploadKopCustom">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Format: PNG/JPG, Max: 5MB, Rekomendasi ukuran: 2100x300px</small>
                    </div>
                    
                    @if($setting->kop_surat_custom_path)
                    <div id="customKopPreview" class="text-center">
                        <img src="{{ $setting->kop_surat_custom_url }}" class="img-thumbnail" style="max-width: 100%;">
                    </div>
                    @endif
                </div>

                {{-- Settings Margin --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kop_margin_top">Margin Top (mm)</label>
                            <input type="number" name="kop_margin_top" id="kop_margin_top" class="form-control" 
                                   value="{{ old('kop_margin_top', $setting->kop_margin_top) }}" min="0">
                            <small class="text-muted">Untuk keperluan cetak PDF</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kop_height">Height Kop (mm)</label>
                            <input type="number" name="kop_height" id="kop_height" class="form-control" 
                                   value="{{ old('kop_height', $setting->kop_height) }}" min="10">
                            <small class="text-muted">Untuk keperluan cetak PDF</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </div>
    </form>

    {{-- Modal: Text Element Editor --}}
    <div class="modal fade" id="textElementModal" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Text Element</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Isi Text</label>
                        <input type="text" class="form-control" id="elementContent" placeholder="Masukkan text...">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ukuran Font (px)</label>
                                <select class="form-control" id="fontSize">
                                    <option value="8">8px</option>
                                    <option value="10">10px</option>
                                    <option value="12">12px</option>
                                    <option value="14" selected>14px</option>
                                    <option value="16">16px</option>
                                    <option value="18">18px</option>
                                    <option value="20">20px</option>
                                    <option value="22">22px</option>
                                    <option value="24">24px</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Font Weight</label>
                                <select class="form-control" id="fontWeight">
                                    <option value="normal">Normal</option>
                                    <option value="bold" selected>Bold</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Text Align</label>
                                <select class="form-control" id="textAlign">
                                    <option value="left">Kiri</option>
                                    <option value="center" selected>Tengah</option>
                                    <option value="right">Kanan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Margin Bottom (px)</label>
                        <input type="number" class="form-control" id="marginBottom" value="2" min="0">
                    </div>
                    
                    <div class="alert alert-secondary">
                        <strong>Preview:</strong>
                        <div id="livePreview" style="text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 2px;">
                            Preview text akan muncul di sini
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveElement">
                        <i class="fas fa-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Divider Element Editor --}}
    <div class="modal fade" id="dividerElementModal" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h4 class="modal-title"><i class="fas fa-minus"></i> Edit Garis Pembatas</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jenis Garis</label>
                        <select class="form-control" id="borderStyle">
                            <option value="solid">Solid (─────)</option>
                            <option value="double" selected>Double (═════)</option>
                            <option value="dashed">Dashed (- - - -)</option>
                            <option value="dotted">Dotted (·····)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Ketebalan (px)</label>
                        <select class="form-control" id="borderWidth">
                            <option value="1">1px</option>
                            <option value="2">2px</option>
                            <option value="3" selected>3px</option>
                            <option value="4">4px</option>
                            <option value="5">5px</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Warna</label>
                        <input type="color" class="form-control" id="borderColor" value="#000000">
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Margin Top (px)</label>
                                <input type="number" class="form-control" id="marginTop" value="5" min="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Margin Bottom (px)</label>
                                <input type="number" class="form-control" id="marginBottomDivider" value="5" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-secondary">
                        <strong>Preview:</strong>
                        <hr id="dividerPreview" style="border-style: double; border-width: 3px; border-color: #000; margin-top: 5px; margin-bottom: 5px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveDivider">
                        <i class="fas fa-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Preview Kop Surat --}}
    <div class="modal fade" id="previewKopModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title"><i class="fas fa-eye"></i> Preview Kop Surat</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="previewKopContent" style="padding: 20px; background: white; border: 1px solid #ddd;">
                        {{-- Preview will be inserted here --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sortable.js -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Global variable untuk kop config
            let kopConfig = @json($setting->kop_surat_config ?? ['elements' => []]);
            
            // Initialize kop builder
            if (kopConfig.elements) {
                renderList();
                renderPreview();
            }

            // =============================================
            // SELECT2 INITIALIZATION
            // =============================================
            
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownAutoWidth: true
            });

            // =============================================
            // LARAVOLT CASCADING DROPDOWN
            // =============================================
            
            // Provinsi change - using Select2 event
            $('#provinsi_code').on('select2:select', function(e) {
                const provinsiCode = $(this).val();
                
                // Reset child dropdowns
                $('#kota_code').empty().append('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
                $('#kecamatan_code').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true).trigger('change');
                $('#kelurahan_code').empty().append('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true).trigger('change');
                $('#kode_pos').val('');
                
                if (provinsiCode) {
                    $.get(`{{ url('siswa/api/cities') }}/${provinsiCode}`)
                        .done(function(cities) {
                            $('#kota_code').empty().append('<option value="">-- Pilih Kota/Kabupaten --</option>');
                            cities.forEach(function(city) {
                                $('#kota_code').append(new Option(city.name, city.code));
                            });
                            $('#kota_code').prop('disabled', false).trigger('change');
                        })
                        .fail(function() {
                            console.error('Failed to load cities');
                            Swal.fire('Error', 'Gagal memuat data kota/kabupaten', 'error');
                            $('#kota_code').empty().append('<option value="">-- Pilih Kota/Kabupaten --</option>').prop('disabled', false).trigger('change');
                        });
                }
            });
            
            // Kota change - using Select2 event
            $('#kota_code').on('select2:select', function(e) {
                const kotaCode = $(this).val();
                
                // Reset child dropdowns
                $('#kecamatan_code').empty().append('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
                $('#kelurahan_code').empty().append('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true).trigger('change');
                $('#kode_pos').val('');
                
                if (kotaCode) {
                    $.get(`{{ url('siswa/api/districts') }}/${kotaCode}`)
                        .done(function(districts) {
                            $('#kecamatan_code').empty().append('<option value="">-- Pilih Kecamatan --</option>');
                            districts.forEach(function(district) {
                                $('#kecamatan_code').append(new Option(district.name, district.code));
                            });
                            $('#kecamatan_code').prop('disabled', false).trigger('change');
                        })
                        .fail(function() {
                            console.error('Failed to load districts');
                            Swal.fire('Error', 'Gagal memuat data kecamatan', 'error');
                            $('#kecamatan_code').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false).trigger('change');
                        });
                }
            });
            
            // Kecamatan change - using Select2 event
            $('#kecamatan_code').on('select2:select', function(e) {
                const kecamatanCode = $(this).val();
                
                // Reset child dropdown
                $('#kelurahan_code').empty().append('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
                $('#kode_pos').val('');
                
                if (kecamatanCode) {
                    $.get(`{{ url('siswa/api/villages') }}/${kecamatanCode}`)
                        .done(function(villages) {
                            $('#kelurahan_code').empty().append('<option value="">-- Pilih Kelurahan/Desa --</option>');
                            villages.forEach(function(village) {
                                $('#kelurahan_code').append(new Option(village.name, village.code));
                            });
                            $('#kelurahan_code').prop('disabled', false).trigger('change');
                        })
                        .fail(function() {
                            console.error('Failed to load villages');
                            Swal.fire('Error', 'Gagal memuat data kelurahan/desa', 'error');
                            $('#kelurahan_code').empty().append('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', false).trigger('change');
                        });
                }
            });
            
            // Helper function to load cities
            function loadCities(provinsiCode, selectedKotaCode) {
                if (!provinsiCode) return;
                
                $.get(`{{ url('siswa/api/cities') }}/${provinsiCode}`)
                    .done(function(cities) {
                        $('#kota_code').empty().append('<option value="">-- Pilih Kota/Kabupaten --</option>');
                        cities.forEach(function(city) {
                            $('#kota_code').append(new Option(city.name, city.code, false, city.code == selectedKotaCode));
                        });
                        $('#kota_code').prop('disabled', false).trigger('change');
                    });
            }
            
            // Helper function to load districts
            function loadDistricts(kotaCode, selectedKecamatanCode) {
                if (!kotaCode) return;
                
                $.get(`{{ url('siswa/api/districts') }}/${kotaCode}`)
                    .done(function(districts) {
                        $('#kecamatan_code').empty().append('<option value="">-- Pilih Kecamatan --</option>');
                        districts.forEach(function(district) {
                            $('#kecamatan_code').append(new Option(district.name, district.code, false, district.code == selectedKecamatanCode));
                        });
                        $('#kecamatan_code').prop('disabled', false).trigger('change');
                    });
            }
            
            // Helper function to load villages
            function loadVillages(kecamatanCode, selectedKelurahanCode) {
                if (!kecamatanCode) return;
                
                $.get(`{{ url('siswa/api/villages') }}/${kecamatanCode}`)
                    .done(function(villages) {
                        $('#kelurahan_code').empty().append('<option value="">-- Pilih Kelurahan/Desa --</option>');
                        villages.forEach(function(village) {
                            $('#kelurahan_code').append(new Option(village.name, village.code, false, village.code == selectedKelurahanCode));
                        });
                        $('#kelurahan_code').prop('disabled', false).trigger('change');
                    });
            }
            
            // Load existing data on page load (edit mode)
            @if($setting->provinsi_code)
                setTimeout(function() {
                    loadCities('{{ $setting->provinsi_code }}', '{{ $setting->kota_code }}');
                    
                    @if($setting->kota_code)
                        setTimeout(function() {
                            loadDistricts('{{ $setting->kota_code }}', '{{ $setting->kecamatan_code }}');
                            
                            @if($setting->kecamatan_code)
                                setTimeout(function() {
                                    loadVillages('{{ $setting->kecamatan_code }}', '{{ $setting->kelurahan_code }}');
                                }, 500);
                            @endif
                        }, 500);
                    @endif
                }, 300);
            @endif

            // =============================================
            // LOGO UPLOAD
            // =============================================
            
            // Handle file selection and preview with drag & drop
            function handleFileSelect(input, previewId, placeholderId, saveButtonId, removeButtonId) {
                const file = input.files[0];
                if (file) {
                    // Validate file
                    if (!file.type.match('image.*')) {
                        Swal.fire('Error', 'File harus berupa gambar (PNG/JPG)', 'error');
                        return;
                    }
                    
                    if (file.size > 2 * 1024 * 1024) { // 2MB
                        Swal.fire('Error', 'Ukuran file maksimal 2MB', 'error');
                        return;
                    }
                    
                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).attr('src', e.target.result).show();
                        $(placeholderId).hide();
                        $(saveButtonId).show();
                        $(removeButtonId).hide();
                    };
                    reader.readAsDataURL(file);
                }
            }
            
            // File input changes
            $('#logoKemenag').on('change', function() {
                handleFileSelect(
                    this,
                    '#previewLogoKemenag',
                    '#uploadAreaKemenag .upload-placeholder',
                    '#btnUploadKemenag',
                    '#uploadAreaKemenag .btn-remove-logo'
                );
            });
            
            $('#logoSekolah').on('change', function() {
                handleFileSelect(
                    this,
                    '#previewLogoSekolah',
                    '#uploadAreaSekolah .upload-placeholder',
                    '#btnUploadSekolah',
                    '#uploadAreaSekolah .btn-remove-logo'
                );
            });
            
            // Direct button clicks for file selection
            $(document).on('click', '.btn-upload', function(e) {
                e.stopPropagation(); // Prevent bubbling to upload-area
                const target = $(this).data('target');
                document.getElementById(target).click();
            });
            
            // Drag & Drop for Logo Kemenag
            $('#uploadAreaKemenag').on('click', function(e) {
                // Only trigger if clicking on the area itself, not on buttons/images/inputs
                const $target = $(e.target);
                if ($target.closest('.btn, input[type="file"], img').length === 0) {
                    e.preventDefault();
                    document.getElementById('logoKemenag').click();
                }
            }).on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            }).on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            }).on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('logoKemenag').files = files;
                    $(document.getElementById('logoKemenag')).trigger('change');
                }
            });
            
            // Drag & Drop for Logo Sekolah
            $('#uploadAreaSekolah').on('click', function(e) {
                // Only trigger if clicking on the area itself, not on buttons/images/inputs
                const $target = $(e.target);
                if ($target.closest('.btn, input[type="file"], img').length === 0) {
                    e.preventDefault();
                    document.getElementById('logoSekolah').click();
                }
            }).on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            }).on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            }).on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('logoSekolah').files = files;
                    $(document.getElementById('logoSekolah')).trigger('change');
                }
            });
            
            // Save Logo Kemenag
            $('#btnUploadKemenag').on('click', function() {
                const fileInput = document.getElementById('logoKemenag');
                if (!fileInput.files[0]) {
                    Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('logo_kemenag', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                
                $.ajax({
                    url: '{{ route("admin.settings.upload-logo-kemenag") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#previewLogoKemenag, #kopPreviewLogoKemenag').attr('src', response.url);
                        $btn.hide();
                        $('#uploadAreaKemenag .btn-remove-logo').show();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal upload logo', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                    }
                });
            });
            
            // Save Logo Sekolah
            $('#btnUploadSekolah').on('click', function() {
                const fileInput = document.getElementById('logoSekolah');
                if (!fileInput.files[0]) {
                    Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('logo_sekolah', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                
                $.ajax({
                    url: '{{ route("admin.settings.upload-logo-sekolah") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#previewLogoSekolah, #kopPreviewLogoSekolah').attr('src', response.url);
                        $btn.hide();
                        $('#uploadAreaSekolah .btn-remove-logo').show();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal upload logo', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                    }
                });
            });
            
            // Remove Logo buttons
            $('.btn-remove-logo').on('click', function(e) {
                e.stopPropagation();
                const target = $(this).data('target');
                Swal.fire({
                    title: 'Hapus Logo?',
                    text: 'Logo akan dihapus dari preview',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(`#${target}`).val('');
                        $(`#preview${target.charAt(4).toUpperCase() + target.slice(5)}`).hide();
                        $(`#${target.replace('logo', 'uploadArea')} .upload-placeholder`).show();
                        $(this).hide();
                        Swal.fire('Berhasil', 'Logo telah dihapus dari preview.', 'success');
                    }
                });
            });
            
            // Handle kop custom upload
            $('#kopSuratCustom').on('change', function() {
                if (this.files[0]) {
                    $(this).next('.custom-file-label').html(this.files[0].name);
                }
            });
            
            // Upload Kop Surat Custom
            $('#btnUploadKopCustom').on('click', function() {
                const fileInput = document.getElementById('kopSuratCustom');
                if (!fileInput.files[0]) {
                    Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('kop_surat_custom', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                Swal.fire({
                    title: 'Uploading...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("admin.settings.upload-kop-surat") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        $('#customKopPreview').html(`<img src="${response.url}" class="img-thumbnail" style="max-width: 100%;">`);
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal upload kop surat', 'error');
                    }
                });
            });

            // =============================================
            // KOP MODE TOGGLE
            // =============================================
            
            $('input[name="kop_mode"]').on('change', function() {
                const mode = $(this).val();
                if (mode === 'builder') {
                    $('#builderModeSection').show();
                    $('#customModeSection').hide();
                } else {
                    $('#builderModeSection').hide();
                    $('#customModeSection').show();
                }
            });

            // =============================================
            // KOP BUILDER - SORTABLE
            // =============================================
            
            let sortable = Sortable.create(document.getElementById('kop-elements-list'), {
                animation: 150,
                handle: '.handle',
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    updateOrder();
                    renderPreview();
                    updateHiddenInput();
                }
            });

            // =============================================
            // KOP BUILDER - FUNCTIONS
            // =============================================
            
            function renderList() {
                const list = $('#kop-elements-list');
                list.empty();
                
                if (!kopConfig.elements || kopConfig.elements.length === 0) {
                    list.html('<div class="alert alert-info">Belum ada element. Klik tombol "Text" atau "Garis" untuk menambah.</div>');
                    return;
                }
                
                const sortedElements = kopConfig.elements.sort((a, b) => a.order - b.order);
                
                sortedElements.forEach(element => {
                    const typeLabel = element.type === 'text' ? 'Text' : 'Garis';
                    const typeIcon = element.type === 'text' ? 'fa-font' : 'fa-minus';
                    const typeBadge = element.type === 'text' ? 'badge-info' : 'badge-secondary';
                    
                    let preview = '';
                    if (element.type === 'text') {
                        preview = element.content;
                    } else {
                        preview = `<hr style="border-style: ${element.style.borderStyle}; border-width: ${element.style.borderWidth}px;">`;
                    }
                    
                    const html = `
                        <div class="element-item list-group-item" data-id="${element.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-grip-vertical handle"></i>
                                    <span class="badge ${typeBadge}"><i class="fas ${typeIcon}"></i> ${typeLabel}</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="editElement(${element.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteElement(${element.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="element-preview">${preview}</div>
                        </div>
                    `;
                    
                    list.append(html);
                });
            }
            
            function renderPreview() {
                const preview = $('#kop-elements-preview');
                preview.empty();
                
                if (!kopConfig.elements || kopConfig.elements.length === 0) {
                    preview.html('<p class="text-muted">Preview kop surat kosong</p>');
                    return;
                }
                
                const sortedElements = kopConfig.elements.sort((a, b) => a.order - b.order);
                
                sortedElements.forEach(element => {
                    if (element.type === 'text') {
                        const style = element.style;
                        const html = `
                            <div style="
                                font-size: ${style.fontSize || 14}px;
                                font-weight: ${style.fontWeight || 'normal'};
                                text-align: ${style.textAlign || 'center'};
                                margin-bottom: ${style.marginBottom || 2}px;
                                line-height: 1.4;
                            ">${element.content}</div>
                        `;
                        preview.append(html);
                    } else if (element.type === 'divider') {
                        const style = element.style;
                        const html = `
                            <hr style="
                                border: none;
                                border-top-style: ${style.borderStyle || 'solid'};
                                border-top-width: ${style.borderWidth || 1}px;
                                border-top-color: ${style.borderColor || '#000000'};
                                margin-top: ${style.marginTop || 5}px;
                                margin-bottom: ${style.marginBottom || 5}px;
                            ">
                        `;
                        preview.append(html);
                    }
                });
            }
            
            function updateOrder() {
                const items = document.querySelectorAll('.element-item');
                items.forEach((item, index) => {
                    const id = parseInt(item.dataset.id);
                    const element = kopConfig.elements.find(el => el.id === id);
                    if (element) {
                        element.order = index + 1;
                    }
                });
            }
            
            function updateHiddenInput() {
                $('#kop_surat_config').val(JSON.stringify(kopConfig));
            }
            
            // Add Text Element
            $('#btnAddText').on('click', function() {
                const maxId = kopConfig.elements.length > 0 
                    ? Math.max(...kopConfig.elements.map(e => e.id)) 
                    : 0;
                
                const newElement = {
                    id: maxId + 1,
                    type: 'text',
                    content: 'Text Baru',
                    style: {
                        fontSize: '14',
                        fontWeight: 'normal',
                        textAlign: 'center',
                        marginBottom: '2'
                    },
                    order: kopConfig.elements.length + 1
                };
                
                kopConfig.elements.push(newElement);
                renderList();
                renderPreview();
                updateHiddenInput();
                
                // Auto edit
                editElement(newElement.id);
            });
            
            // Add Divider Element
            $('#btnAddDivider').on('click', function() {
                const maxId = kopConfig.elements.length > 0 
                    ? Math.max(...kopConfig.elements.map(e => e.id)) 
                    : 0;
                
                const newElement = {
                    id: maxId + 1,
                    type: 'divider',
                    style: {
                        borderStyle: 'solid',
                        borderWidth: '1',
                        borderColor: '#000000',
                        marginTop: '5',
                        marginBottom: '5'
                    },
                    order: kopConfig.elements.length + 1
                };
                
                kopConfig.elements.push(newElement);
                renderList();
                renderPreview();
                updateHiddenInput();
            });
            
            // Edit Element (Global function)
            window.editElement = function(id) {
                const element = kopConfig.elements.find(e => e.id === id);
                if (!element) return;
                
                if (element.type === 'text') {
                    // Load data ke modal
                    $('#elementContent').val(element.content);
                    $('#fontSize').val(element.style.fontSize || '14');
                    $('#fontWeight').val(element.style.fontWeight || 'normal');
                    $('#textAlign').val(element.style.textAlign || 'center');
                    $('#marginBottom').val(element.style.marginBottom || '2');
                    
                    updateLivePreview();
                    
                    $('#textElementModal').data('element-id', id).modal('show');
                } else if (element.type === 'divider') {
                    $('#borderStyle').val(element.style.borderStyle || 'solid');
                    $('#borderWidth').val(element.style.borderWidth || '1');
                    $('#borderColor').val(element.style.borderColor || '#000000');
                    $('#marginTop').val(element.style.marginTop || '5');
                    $('#marginBottomDivider').val(element.style.marginBottom || '5');
                    
                    updateDividerPreview();
                    
                    $('#dividerElementModal').data('element-id', id).modal('show');
                }
            }
            
            // Delete Element (Global function)
            window.deleteElement = function(id) {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Yakin ingin menghapus element ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        kopConfig.elements = kopConfig.elements.filter(e => e.id !== id);
                        renderList();
                        renderPreview();
                        updateHiddenInput();
                        
                        Swal.fire('Terhapus!', 'Element berhasil dihapus.', 'success');
                    }
                });
            }
            
            // Live preview text element
            function updateLivePreview() {
                const content = $('#elementContent').val();
                const fontSize = $('#fontSize').val();
                const fontWeight = $('#fontWeight').val();
                const textAlign = $('#textAlign').val();
                const marginBottom = $('#marginBottom').val();
                
                $('#livePreview').css({
                    'font-size': fontSize + 'px',
                    'font-weight': fontWeight,
                    'text-align': textAlign,
                    'margin-bottom': marginBottom + 'px'
                }).text(content);
            }
            
            $('#elementContent, #fontSize, #fontWeight, #textAlign, #marginBottom').on('input change', updateLivePreview);
            
            // Live preview divider
            function updateDividerPreview() {
                const borderStyle = $('#borderStyle').val();
                const borderWidth = $('#borderWidth').val();
                const borderColor = $('#borderColor').val();
                const marginTop = $('#marginTop').val();
                const marginBottom = $('#marginBottomDivider').val();
                
                $('#dividerPreview').css({
                    'border-style': borderStyle,
                    'border-width': borderWidth + 'px',
                    'border-color': borderColor,
                    'margin-top': marginTop + 'px',
                    'margin-bottom': marginBottom + 'px'
                });
            }
            
            $('#borderStyle, #borderWidth, #borderColor, #marginTop, #marginBottomDivider').on('input change', updateDividerPreview);
            
            // Save Text Element
            $('#btnSaveElement').on('click', function() {
                const id = $('#textElementModal').data('element-id');
                const element = kopConfig.elements.find(e => e.id === id);
                
                if (element) {
                    element.content = $('#elementContent').val();
                    element.style.fontSize = $('#fontSize').val();
                    element.style.fontWeight = $('#fontWeight').val();
                    element.style.textAlign = $('#textAlign').val();
                    element.style.marginBottom = $('#marginBottom').val();
                    
                    renderList();
                    renderPreview();
                    updateHiddenInput();
                    
                    $('#textElementModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Element berhasil diupdate',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
            
            // Save Divider Element
            $('#btnSaveDivider').on('click', function() {
                const id = $('#dividerElementModal').data('element-id');
                const element = kopConfig.elements.find(e => e.id === id);
                
                if (element) {
                    element.style.borderStyle = $('#borderStyle').val();
                    element.style.borderWidth = $('#borderWidth').val();
                    element.style.borderColor = $('#borderColor').val();
                    element.style.marginTop = $('#marginTop').val();
                    element.style.marginBottom = $('#marginBottomDivider').val();
                    
                    renderList();
                    renderPreview();
                    updateHiddenInput();
                    
                    $('#dividerElementModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Garis berhasil diupdate',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
            
            // Preview Kop Surat
            $('#btnPreviewKop').on('click', function() {
                const previewHtml = $('#kopPreviewTable')[0].outerHTML;
                $('#previewKopContent').html(previewHtml);
                $('#previewKopModal').modal('show');
            });

            // =============================================
            // FORM SUBMIT
            // =============================================
            
            $('#settingsForm').on('submit', function(e) {
                e.preventDefault();
                
                // Debug: Check if Select2 values are present
                console.log('=== Form Submit Debug ===');
                console.log('Provinsi:', $('#provinsi_code').val());
                console.log('Kota:', $('#kota_code').val());
                console.log('Kecamatan:', $('#kecamatan_code').val());
                console.log('Kelurahan:', $('#kelurahan_code').val());
                console.log('Kode Pos:', $('#kode_pos').val());
                
                // Validate that all wilayah fields are filled
                const provinsi = $('#provinsi_code').val();
                const kota = $('#kota_code').val();
                const kecamatan = $('#kecamatan_code').val();
                const kelurahan = $('#kelurahan_code').val();
                
                if (!provinsi || !kota || !kecamatan || !kelurahan) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Belum Lengkap',
                        html: 'Pastikan semua wilayah sudah dipilih:<br>' +
                              '<ul class="text-left">' +
                              (!provinsi ? '<li>Provinsi</li>' : '') +
                              (!kota ? '<li>Kota/Kabupaten</li>' : '') +
                              (!kecamatan ? '<li>Kecamatan</li>' : '') +
                              (!kelurahan ? '<li>Kelurahan/Desa</li>' : '') +
                              '</ul>'
                    });
                    return false;
                }
                
                Swal.fire({
                    title: 'Konfirmasi Simpan',
                    text: 'Yakin ingin menyimpan pengaturan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Update hidden input before submit
                        updateHiddenInput();
                        
                        // Submit form
                        this.submit();
                    }
                });
            });
        });
    </script>
@stop
