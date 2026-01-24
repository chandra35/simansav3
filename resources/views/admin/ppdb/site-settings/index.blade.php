@extends('adminlte::page')

@section('title', 'Pengaturan Site PPDB')

@section('content_header')
    <h1>Pengaturan Site PPDB</h1>
@stop

@section('content')
    <form action="{{ route('admin.settings.halaman.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="settings-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-general">
                            <i class="fas fa-cog"></i> Umum
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-contact">
                            <i class="fas fa-phone"></i> Kontak
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-social">
                            <i class="fas fa-share-alt"></i> Social Media
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-landing">
                            <i class="fas fa-home"></i> Landing Page
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-seo">
                            <i class="fas fa-search"></i> SEO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-registration">
                            <i class="fas fa-user-plus"></i> Pendaftaran
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="settings-tabs-content">
                    <!-- Tab Umum -->
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_name">Nama Site <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="{{ old('site_name', $settings->site_name) }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="site_tagline">Tagline</label>
                                    <input type="text" class="form-control" id="site_tagline" name="site_tagline" 
                                           value="{{ old('site_tagline', $settings->site_tagline) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    @if($settings->logo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" style="max-height: 80px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                                        <label class="custom-file-label" for="logo">Pilih logo...</label>
                                    </div>
                                    <small class="form-text text-muted">Max: 1MB. Format: JPG, PNG, SVG</small>
                                </div>
                                <div class="form-group">
                                    <label for="favicon">Favicon</label>
                                    @if($settings->favicon)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings->favicon) }}" alt="Favicon" style="max-height: 32px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="favicon" name="favicon" accept=".ico,.png">
                                        <label class="custom-file-label" for="favicon">Pilih favicon...</label>
                                    </div>
                                    <small class="form-text text-muted">Max: 512KB. Format: ICO, PNG</small>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Warna Theme</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="primary_color">Warna Utama</label>
                                    <input type="color" class="form-control" id="primary_color" name="primary_color" 
                                           value="{{ old('primary_color', $settings->primary_color) }}" style="height: 40px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="secondary_color">Warna Sekunder</label>
                                    <input type="color" class="form-control" id="secondary_color" name="secondary_color" 
                                           value="{{ old('secondary_color', $settings->secondary_color) }}" style="height: 40px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="accent_color">Warna Aksen</label>
                                    <input type="color" class="form-control" id="accent_color" name="accent_color" 
                                           value="{{ old('accent_color', $settings->accent_color) }}" style="height: 40px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Kontak -->
                    <div class="tab-pane fade" id="tab-contact">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', $settings->email) }}">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="{{ old('phone', $settings->phone) }}">
                                </div>
                                <div class="form-group">
                                    <label for="whatsapp">WhatsApp</label>
                                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                           value="{{ old('whatsapp', $settings->whatsapp) }}" 
                                           placeholder="628xxxxxxxxxx">
                                    <small class="form-text text-muted">Format: 628xxxxxxxxxx (tanpa + atau spasi)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Alamat</label>
                                    <textarea class="form-control" id="address" name="address" rows="4">{{ old('address', $settings->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Google Maps</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="maps_latitude">Latitude</label>
                                    <input type="text" class="form-control" id="maps_latitude" name="maps_latitude" 
                                           value="{{ old('maps_latitude', $settings->maps_latitude) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="maps_longitude">Longitude</label>
                                    <input type="text" class="form-control" id="maps_longitude" name="maps_longitude" 
                                           value="{{ old('maps_longitude', $settings->maps_longitude) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <a href="https://www.google.com/maps" target="_blank" class="btn btn-info btn-block">
                                        <i class="fas fa-map-marker-alt"></i> Cari Koordinat
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="maps_embed">Embed Code Maps (iFrame)</label>
                            <textarea class="form-control" id="maps_embed" name="maps_embed" rows="3" 
                                      placeholder='<iframe src="..."></iframe>'>{{ old('maps_embed', $settings->maps_embed) }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Tab Social Media -->
                    <div class="tab-pane fade" id="tab-social">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_url"><i class="fab fa-facebook text-primary"></i> Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                           value="{{ old('facebook_url', $settings->facebook_url) }}" placeholder="https://facebook.com/...">
                                </div>
                                <div class="form-group">
                                    <label for="instagram_url"><i class="fab fa-instagram text-danger"></i> Instagram URL</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                           value="{{ old('instagram_url', $settings->instagram_url) }}" placeholder="https://instagram.com/...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="youtube_url"><i class="fab fa-youtube text-danger"></i> YouTube URL</label>
                                    <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                           value="{{ old('youtube_url', $settings->youtube_url) }}" placeholder="https://youtube.com/...">
                                </div>
                                <div class="form-group">
                                    <label for="twitter_url"><i class="fab fa-twitter text-info"></i> Twitter/X URL</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                           value="{{ old('twitter_url', $settings->twitter_url) }}" placeholder="https://twitter.com/...">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5><i class="fab fa-facebook"></i> Integrasi Facebook Page</h5>
                        <p class="text-muted">Untuk fitur posting berita otomatis ke Facebook Page</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_page_id">Facebook Page ID</label>
                                    <input type="text" class="form-control" id="facebook_page_id" name="facebook_page_id" 
                                           value="{{ old('facebook_page_id', $settings->facebook_page_id) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_access_token">Facebook Access Token</label>
                                    <input type="password" class="form-control" id="facebook_access_token" name="facebook_access_token" 
                                           value="{{ old('facebook_access_token', $settings->facebook_access_token) }}">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="testFacebookBtn">
                            <i class="fas fa-plug"></i> Test Koneksi Facebook
                        </button>
                        <div id="facebookTestResult" class="mt-2"></div>
                    </div>
                    
                    <!-- Tab Landing Page -->
                    <div class="tab-pane fade" id="tab-landing">
                        <h5 class="mb-3">Hero Section</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hero_title">Judul Hero</label>
                                    <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                           value="{{ old('hero_title', $settings->hero_title) }}">
                                </div>
                                <div class="form-group">
                                    <label for="hero_subtitle">Subtitle Hero</label>
                                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="3">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hero_button_text">Teks Tombol</label>
                                            <input type="text" class="form-control" id="hero_button_text" name="hero_button_text" 
                                                   value="{{ old('hero_button_text', $settings->hero_button_text) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hero_button_link">Link Tombol</label>
                                            <input type="text" class="form-control" id="hero_button_link" name="hero_button_link" 
                                                   value="{{ old('hero_button_link', $settings->hero_button_link) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hero_image">Gambar Hero</label>
                                    @if($settings->hero_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings->hero_image) }}" alt="Hero" style="max-height: 150px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="hero_image" name="hero_image" accept="image/*">
                                        <label class="custom-file-label" for="hero_image">Pilih gambar...</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Tentang Kami</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="about_content">Konten Tentang</label>
                                    <textarea class="form-control" id="about_content" name="about_content" rows="6">{{ old('about_content', $settings->about_content) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="about_image">Gambar Tentang</label>
                                    @if($settings->about_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings->about_image) }}" alt="About" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="about_image" name="about_image" accept="image/*">
                                        <label class="custom-file-label" for="about_image">Pilih gambar...</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Footer</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="footer_text">Teks Footer</label>
                                    <textarea class="form-control" id="footer_text" name="footer_text" rows="3">{{ old('footer_text', $settings->footer_text) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="footer_copyright">Copyright</label>
                                    <input type="text" class="form-control" id="footer_copyright" name="footer_copyright" 
                                           value="{{ old('footer_copyright', $settings->footer_copyright) }}" 
                                           placeholder="© 2025 Nama Sekolah. All rights reserved.">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab SEO -->
                    <div class="tab-pane fade" id="tab-seo">
                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                   value="{{ old('meta_title', $settings->meta_title) }}" maxlength="60">
                            <small class="form-text text-muted">Maksimal 60 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $settings->meta_description) }}</textarea>
                            <small class="form-text text-muted">Maksimal 160 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                   value="{{ old('meta_keywords', $settings->meta_keywords) }}" 
                                   placeholder="ppdb, pendaftaran, siswa baru, sekolah">
                            <small class="form-text text-muted">Pisahkan dengan koma</small>
                        </div>
                    </div>
                    
                    <!-- Tab Pendaftaran -->
                    <div class="tab-pane fade" id="tab-registration">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="registration_open" 
                                       name="registration_open" value="1" {{ old('registration_open', $settings->registration_open) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="registration_open">
                                    <strong>Pendaftaran Dibuka</strong>
                                </label>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_start">Tanggal Mulai Pendaftaran</label>
                                    <input type="date" class="form-control" id="registration_start" name="registration_start" 
                                           value="{{ old('registration_start', $settings->registration_start ? $settings->registration_start->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_end">Tanggal Berakhir Pendaftaran</label>
                                    <input type="date" class="form-control" id="registration_end" name="registration_end" 
                                           value="{{ old('registration_end', $settings->registration_end ? $settings->registration_end->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="registration_closed_message">Pesan Pendaftaran Ditutup</label>
                            <textarea class="form-control" id="registration_closed_message" name="registration_closed_message" rows="3" 
                                      placeholder="Maaf, pendaftaran saat ini sedang ditutup...">{{ old('registration_closed_message', $settings->registration_closed_message) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
    
    // Test Facebook connection
    $('#testFacebookBtn').on('click', function() {
        const btn = $(this);
        const resultDiv = $('#facebookTestResult');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        $.ajax({
            url: '{{ route("admin.settings.halaman.test-facebook") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="alert alert-success"><i class="fas fa-check"></i> ' + response.message + ' (Page: ' + (response.page_name || 'N/A') + ')</div>');
                } else {
                    resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times"></i> Gagal menghubungi server</div>');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plug"></i> Test Koneksi Facebook');
            }
        });
    });
});
</script>
@stop
