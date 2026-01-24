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
                        @if($menu->has_personal_data && count($personalData) > 0)
                        <div class="col-auto d-none d-md-block">
                            <span class="badge badge-light px-3 py-2" style="font-size: 0.9rem;">
                                <i class="fas fa-user-check mr-1"></i> Data Personal Tersedia
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
        <div class="col-lg-{{ ($menu->has_personal_data && count($personalData) > 0) ? '8' : '12' }}">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Informasi
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="content-wrapper" style="line-height: 1.8;">
                        {!! $menu->konten !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data Sidebar -->
        @if($menu->has_personal_data && count($personalData) > 0)
        <div class="col-lg-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield mr-2"></i>
                        Data Anda
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($customFields as $key => $field)
                        @if(isset($personalData[$key]))
                            <div class="data-field mb-3">
                                <label class="text-muted mb-1 d-block" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $field['label'] ?? $key }}
                                </label>
                                
                                <div class="input-group input-group-sm">
                                    @if(isset($field['type']) && $field['type'] === 'password')
                                        <input type="password" 
                                               class="form-control password-field bg-light" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="border-radius: 8px 0 0 8px;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" 
                                                    type="button" 
                                                    data-target="field-{{ $key }}"
                                                    title="Lihat/Sembunyikan">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    @else
                                        <input type="text" 
                                               class="form-control bg-light" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="border-radius: 8px 0 0 8px;">
                                    @endif
                                    <div class="input-group-append">
                                        <button class="btn btn-primary copy-btn" 
                                                type="button" 
                                                data-target="field-{{ $key }}"
                                                title="Salin"
                                                style="border-radius: 0 8px 8px 0;">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer bg-light border-0">
                    <div class="d-flex align-items-center text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-shield-alt text-warning mr-2"></i>
                        <span>Data ini bersifat rahasia. Jangan bagikan ke orang lain.</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0 mt-3" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-bolt mr-1"></i> Aksi Cepat
                    </h6>
                    <button class="btn btn-outline-primary btn-block btn-sm copy-all-btn" style="border-radius: 8px;">
                        <i class="fas fa-clipboard-list mr-1"></i> Salin Semua Data
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
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
        
        // Select and copy
        input.select();
        document.execCommand('copy');
        
        // Restore password type
        if (wasPassword) input.attr('type', 'password');
        
        // Visual feedback
        const icon = $(this).find('i');
        icon.removeClass('fa-copy').addClass('fa-check');
        $(this).removeClass('btn-primary').addClass('btn-success');
        
        setTimeout(() => {
            icon.removeClass('fa-check').addClass('fa-copy');
            $(this).removeClass('btn-success').addClass('btn-primary');
        }, 1500);
        
        // Toast notification
        if (typeof toastr !== 'undefined') {
            toastr.success('Berhasil disalin!', '', {timeOut: 1500, positionClass: 'toast-bottom-right'});
        }
    });

    // Copy All Data
    $('.copy-all-btn').click(function() {
        let allData = [];
        
        $('.data-field').each(function() {
            const label = $(this).find('label').text().trim();
            const value = $(this).find('input').val();
            allData.push(label + ': ' + value);
        });
        
        const textToCopy = allData.join('\n');
        
        // Create temporary textarea
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(textToCopy).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Visual feedback
        $(this).html('<i class="fas fa-check mr-1"></i> Tersalin!');
        $(this).removeClass('btn-outline-primary').addClass('btn-success');
        
        setTimeout(() => {
            $(this).html('<i class="fas fa-clipboard-list mr-1"></i> Salin Semua Data');
            $(this).removeClass('btn-success').addClass('btn-outline-primary');
        }, 2000);
        
        if (typeof toastr !== 'undefined') {
            toastr.success('Semua data berhasil disalin!', '', {timeOut: 2000, positionClass: 'toast-bottom-right'});
        }
    });
});
</script>
@stop
