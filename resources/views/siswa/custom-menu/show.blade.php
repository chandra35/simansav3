@extends('adminlte::page')

@section('title', $menu->judul)

@section('content_header')
@stop

@section('content')
<div class="container-fluid">
    <!-- Compact Hero -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white mb-0" style="border-radius: 10px;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-white text-primary mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="{{ $menu->icon ?? 'fas fa-file-alt' }}"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 font-weight-bold">{{ $menu->judul }}</h4>
                            <small class="opacity-75">
                                <i class="fas fa-folder mr-1"></i> {{ ucfirst($menu->menu_group) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-{{ $showPersonalDataCard ? '8' : '12' }}">
            <div class="card" style="border-radius: 10px;">
                <div class="card-body">
                    <div class="content-wrapper">
                        {!! $menu->konten !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data Card - Only if has additional fields -->
        @if($showPersonalDataCard)
        <div class="col-lg-4">
            <div class="card border-warning" style="border-radius: 10px; border-width: 2px;">
                <div class="card-header bg-warning py-2">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-key mr-1"></i> Data Akun Anda
                    </h6>
                </div>
                <div class="card-body py-3">
                    @foreach($additionalFields as $key => $field)
                        @if(isset($personalData[$key]))
                            <div class="mb-3">
                                <label class="text-muted mb-1 d-block small text-uppercase">
                                    {{ $field['label'] ?? $key }}
                                </label>
                                <div class="input-group input-group-sm">
                                    @if(isset($field['type']) && $field['type'] === 'password')
                                        <input type="password" 
                                               class="form-control bg-light font-weight-bold" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="font-family: monospace;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="field-{{ $key }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-primary copy-btn" type="button" data-target="field-{{ $key }}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    @else
                                        <input type="text" 
                                               class="form-control bg-light font-weight-bold" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}"
                                               style="font-family: monospace;">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary copy-btn" type="button" data-target="field-{{ $key }}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    <button class="btn btn-success btn-sm btn-block copy-all-btn mt-2">
                        <i class="fas fa-clipboard-list mr-1"></i> Salin Semua
                    </button>
                </div>
                <div class="card-footer bg-light py-2">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Jangan bagikan data ini ke siapapun!
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .content-wrapper { font-size: 0.95rem; line-height: 1.7; }
    .content-wrapper p { margin-bottom: 0.75rem; }
    .content-wrapper ul, .content-wrapper ol { padding-left: 1.25rem; }
    .content-wrapper a { color: #007bff; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Toggle Password
    $('.toggle-password').click(function() {
        const input = $('#' + $(this).data('target'));
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Copy single field
    $('.copy-btn').click(function() {
        const input = $('#' + $(this).data('target'));
        const wasPassword = input.attr('type') === 'password';
        if (wasPassword) input.attr('type', 'text');
        
        navigator.clipboard.writeText(input.val()).then(() => {
            $(this).find('i').removeClass('fa-copy').addClass('fa-check');
            setTimeout(() => $(this).find('i').removeClass('fa-check').addClass('fa-copy'), 1000);
        });
        
        if (wasPassword) input.attr('type', 'password');
    });

    // Copy all
    $('.copy-all-btn').click(function() {
        let data = [];
        $('.card-body .mb-3').each(function() {
            const label = $(this).find('label').text().trim();
            const value = $(this).find('input').val();
            if (label && value) data.push(label + ': ' + value);
        });
        
        navigator.clipboard.writeText(data.join('\n')).then(() => {
            $(this).html('<i class="fas fa-check mr-1"></i> Tersalin!');
            setTimeout(() => $(this).html('<i class="fas fa-clipboard-list mr-1"></i> Salin Semua'), 1500);
        });
    });
});
</script>
@stop
