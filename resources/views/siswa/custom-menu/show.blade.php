@extends('adminlte::page')

@section('title', $menu->judul)

@section('content_header')
@stop

@section('content')
<div class="container-fluid">
    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="icon-circle bg-white text-primary" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                <i class="{{ $menu->icon ?? 'fas fa-file-alt' }}"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h2 class="mb-1 font-weight-bold">{{ $menu->judul }}</h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-folder mr-1"></i> 
                                {{ ucfirst($menu->menu_group) }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ $menu->created_at->format('d F Y') }}
                            </p>
                        </div>
                        @if($showPersonalDataCard)
                        <div class="col-auto d-none d-md-block">
                            <span class="badge badge-warning px-3 py-2" style="font-size: 0.9rem;">
                                <i class="fas fa-key mr-1"></i> Akun Khusus Tersedia
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-{{ $showPersonalDataCard ? '8' : '12' }}">
            <!-- Info Card -->
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Informasi {{ $menu->judul }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="content-wrapper" style="line-height: 1.8;">
                        {!! $menu->konten !!}
                    </div>
                </div>
            </div>
            
            <!-- Timeline / Steps Card (if menu is SNBP/SNBT related) -->
            @if(str_contains(strtolower($menu->judul), 'snbp') || str_contains(strtolower($menu->judul), 'snbt') || str_contains(strtolower($menu->judul), 'seleksi'))
            <div class="card shadow-sm mt-3" style="border-radius: 12px; border: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks text-success mr-2"></i>
                        Langkah-langkah Pendaftaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-steps">
                        <div class="step completed">
                            <div class="step-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-content">
                                <h6 class="mb-1">1. Terima Akun</h6>
                                <small class="text-muted">Anda telah menerima informasi akun dari sekolah</small>
                            </div>
                        </div>
                        <div class="step {{ $showPersonalDataCard ? 'active' : '' }}">
                            <div class="step-icon {{ $showPersonalDataCard ? 'bg-primary' : 'bg-secondary' }}">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="step-content">
                                <h6 class="mb-1">2. Login ke Portal</h6>
                                <small class="text-muted">Gunakan username dan password di samping untuk login</small>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon bg-secondary">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="step-content">
                                <h6 class="mb-1">3. Lengkapi Data</h6>
                                <small class="text-muted">Isi semua formulir pendaftaran dengan lengkap</small>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon bg-secondary">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="step-content">
                                <h6 class="mb-1">4. Pilih PTN & Prodi</h6>
                                <small class="text-muted">Tentukan pilihan perguruan tinggi dan program studi</small>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon bg-secondary">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="step-content">
                                <h6 class="mb-1">5. Finalisasi</h6>
                                <small class="text-muted">Kirim pendaftaran dan tunggu pengumuman</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Quick Links -->
            <div class="card shadow-sm mt-3" style="border-radius: 12px; border: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-external-link-alt text-info mr-2"></i>
                        Link Penting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(str_contains(strtolower($menu->judul), 'snbp') || str_contains(strtolower($menu->judul), 'snbt'))
                        <div class="col-md-6 mb-2">
                            <a href="https://portal-snpmb.bppp.kemdikbud.go.id" target="_blank" class="btn btn-outline-primary btn-block text-left" style="border-radius: 8px;">
                                <i class="fas fa-globe mr-2"></i> Portal SNPMB Resmi
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="https://snpmb.bppp.kemdikbud.go.id" target="_blank" class="btn btn-outline-info btn-block text-left" style="border-radius: 8px;">
                                <i class="fas fa-info-circle mr-2"></i> Informasi SNPMB
                            </a>
                        </div>
                        @endif
                        <div class="col-md-6 mb-2">
                            <a href="https://pddikti.kemdikbud.go.id" target="_blank" class="btn btn-outline-success btn-block text-left" style="border-radius: 8px;">
                                <i class="fas fa-search mr-2"></i> Cari PTN/Prodi (PDDikti)
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary btn-block text-left" style="border-radius: 8px;">
                                <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data Sidebar - Only show if there are additional fields -->
        @if($showPersonalDataCard)
        <div class="col-lg-4">
            <!-- Account Credentials Card -->
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-gradient-warning text-dark py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-key mr-2"></i>
                        Akun Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 mb-3" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Gunakan data berikut untuk login ke portal pendaftaran
                    </div>
                    
                    @foreach($additionalFields as $key => $field)
                        @if(isset($personalData[$key]))
                            <div class="data-field mb-3">
                                <label class="text-muted mb-1 d-block" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                    <i class="fas fa-{{ ($field['type'] ?? '') === 'password' ? 'lock' : 'user' }} mr-1"></i>
                                    {{ $field['label'] ?? $key }}
                                </label>
                                
                                <div class="input-group">
                                    @if(isset($field['type']) && $field['type'] === 'password')
                                        <input type="password" 
                                               class="form-control form-control-lg password-field bg-light font-weight-bold" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="border-radius: 8px 0 0 8px; font-family: monospace;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" 
                                                    type="button" 
                                                    data-target="field-{{ $key }}"
                                                    title="Lihat/Sembunyikan">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-primary copy-btn" 
                                                    type="button" 
                                                    data-target="field-{{ $key }}"
                                                    title="Salin"
                                                    style="border-radius: 0 8px 8px 0;">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    @else
                                        <input type="text" 
                                               class="form-control form-control-lg bg-light font-weight-bold" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="border-radius: 8px 0 0 8px; font-family: monospace;">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary copy-btn" 
                                                    type="button" 
                                                    data-target="field-{{ $key }}"
                                                    title="Salin"
                                                    style="border-radius: 0 8px 8px 0;">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer bg-light border-0">
                    <button class="btn btn-success btn-block copy-all-btn" style="border-radius: 8px;">
                        <i class="fas fa-clipboard-list mr-1"></i> Salin Semua Data Akun
                    </button>
                </div>
            </div>
            
            <!-- Security Warning -->
            <div class="card shadow-sm border-0 mt-3" style="border-radius: 12px; background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="mr-3">
                            <i class="fas fa-shield-alt text-danger fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-danger mb-1">Peringatan Keamanan</h6>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                Data akun ini bersifat <strong>RAHASIA</strong>. Jangan bagikan ke siapapun termasuk teman atau keluarga.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Help Card -->
            <div class="card shadow-sm border-0 mt-3" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-question-circle mr-1"></i> Butuh Bantuan?
                    </h6>
                    <p class="text-muted mb-2" style="font-size: 0.85rem;">
                        Jika mengalami kendala, silakan hubungi:
                    </p>
                    <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                        <li class="mb-1">
                            <i class="fas fa-user-tie text-primary mr-2"></i>
                            Guru BK Sekolah
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-headset text-success mr-2"></i>
                            Halo SNPMB: 021-xxxxxxx
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Siswa Info (if any siswa-sourced fields exist) -->
    @if(count($siswaFields) > 0)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate text-secondary mr-2"></i>
                        Data Siswa Terdaftar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($siswaFields as $key => $field)
                            @if(isset($personalData[$key]))
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="mr-3">
                                        <i class="fas fa-id-card text-primary fa-lg"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">{{ $field['label'] ?? $key }}</small>
                                        <strong>{{ $personalData[$key] }}</strong>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .content-wrapper {
        font-size: 1rem;
    }
    .content-wrapper p {
        margin-bottom: 1rem;
    }
    .content-wrapper ul, .content-wrapper ol {
        padding-left: 1.5rem;
    }
    .content-wrapper a {
        color: #007bff;
        text-decoration: underline;
    }
    .content-wrapper a:hover {
        color: #0056b3;
    }
    
    .data-field .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    
    .icon-circle {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .input-group-append .btn {
        border: 1px solid #ced4da;
    }
    
    .copy-btn:hover, .toggle-password:hover {
        transform: scale(1.05);
    }
    
    /* Timeline Steps */
    .timeline-steps {
        position: relative;
    }
    
    .timeline-steps .step {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .timeline-steps .step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 17px;
        top: 40px;
        width: 2px;
        height: calc(100% - 10px);
        background: #dee2e6;
    }
    
    .timeline-steps .step.completed:not(:last-child)::after {
        background: #28a745;
    }
    
    .timeline-steps .step.active:not(:last-child)::after {
        background: linear-gradient(to bottom, #007bff 50%, #dee2e6 50%);
    }
    
    .timeline-steps .step-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.85rem;
        flex-shrink: 0;
        margin-right: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .timeline-steps .step-content h6 {
        font-weight: 600;
    }
    
    .timeline-steps .step.active .step-content h6 {
        color: #007bff;
    }
    
    .timeline-steps .step.completed .step-content h6 {
        color: #28a745;
    }
    
    @media (max-width: 991px) {
        .icon-circle {
            width: 60px !important;
            height: 60px !important;
            font-size: 1.5rem !important;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Remove "NEW" badge from sidebar for this menu
    const currentUrl = window.location.href;
    $('aside .sidebar a[href="' + currentUrl + '"]').find('.badge').fadeOut(300, function() {
        $(this).remove();
    });

    // Toggle Password Visibility
    $('.toggle-password').click(function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            $(this).addClass('btn-warning').removeClass('btn-outline-secondary');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
            $(this).removeClass('btn-warning').addClass('btn-outline-secondary');
        }
    });

    // Copy to Clipboard
    $('.copy-btn').click(function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        
        // If password field, temporarily show
        const wasPassword = input.attr('type') === 'password';
        if (wasPassword) input.attr('type', 'text');
        
        // Use modern clipboard API if available
        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.val()).then(() => {
                showCopySuccess($(this));
            });
        } else {
            // Fallback
            input.select();
            document.execCommand('copy');
            showCopySuccess($(this));
        }
        
        // Restore password type
        if (wasPassword) input.attr('type', 'password');
    });
    
    function showCopySuccess(btn) {
        const icon = btn.find('i');
        icon.removeClass('fa-copy').addClass('fa-check');
        btn.removeClass('btn-primary').addClass('btn-success');
        
        setTimeout(() => {
            icon.removeClass('fa-check').addClass('fa-copy');
            btn.removeClass('btn-success').addClass('btn-primary');
        }, 1500);
        
        // Toast notification
        if (typeof toastr !== 'undefined') {
            toastr.success('Berhasil disalin!', '', {timeOut: 1500, positionClass: 'toast-bottom-right'});
        }
    }

    // Copy All Data
    $('.copy-all-btn').click(function() {
        let allData = [];
        
        $('.data-field').each(function() {
            const label = $(this).find('label').text().trim();
            const value = $(this).find('input').val();
            allData.push(label + ': ' + value);
        });
        
        const textToCopy = allData.join('\n');
        
        // Use modern clipboard API if available
        if (navigator.clipboard) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopyAllSuccess($(this));
            });
        } else {
            // Fallback
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(textToCopy).select();
            document.execCommand('copy');
            $temp.remove();
            showCopyAllSuccess($(this));
        }
    });
    
    function showCopyAllSuccess(btn) {
        btn.html('<i class="fas fa-check mr-1"></i> Tersalin!');
        btn.removeClass('btn-success').addClass('btn-primary');
        
        setTimeout(() => {
            btn.html('<i class="fas fa-clipboard-list mr-1"></i> Salin Semua Data Akun');
            btn.removeClass('btn-primary').addClass('btn-success');
        }, 2000);
        
        if (typeof toastr !== 'undefined') {
            toastr.success('Semua data berhasil disalin!', '', {timeOut: 2000, positionClass: 'toast-bottom-right'});
        }
    }
});
</script>
@stop
