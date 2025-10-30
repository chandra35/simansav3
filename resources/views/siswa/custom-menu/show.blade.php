@extends('adminlte::page')

@section('title', $menu->judul)

@section('content_header')
    <h1>
        <i class="{{ $menu->icon ?? 'fas fa-file-alt' }}"></i> 
        {{ $menu->judul }}
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-{{ $menu->content_type === 'personal' && count($personalData) > 0 ? '8' : '12' }}">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="card-body">
                {!! $menu->konten !!}
            </div>
            <div class="card-footer text-muted">
                <small>
                    <i class="fas fa-calendar"></i> Dibuat: {{ $menu->created_at->format('d M Y H:i') }}
                </small>
            </div>
        </div>
    </div>

    @if($menu->content_type === 'personal' && count($personalData) > 0)
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Data Anda</h3>
                </div>
                <div class="card-body">
                    @foreach($customFields as $key => $field)
                        @if(isset($personalData[$key]))
                            <div class="form-group">
                                <label class="text-muted mb-1">
                                    <small>{{ $field['label'] ?? $key }}</small>
                                </label>
                                
                                <div class="input-group">
                                    @if(isset($field['type']) && $field['type'] === 'password')
                                        <input type="password" 
                                               class="form-control form-control-sm password-field" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-sm btn-outline-secondary toggle-password" 
                                                    type="button" 
                                                    data-target="field-{{ $key }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    @else
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               value="{{ $personalData[$key] }}" 
                                               readonly 
                                               id="field-{{ $key }}">
                                    @endif
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-outline-primary copy-btn" 
                                                type="button" 
                                                data-target="field-{{ $key }}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Data ini bersifat rahasia. Jangan bagikan ke orang lain.
                    </small>
                </div>
            </div>
        </div>
    @endif
</div>
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
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Copy to Clipboard
    $('.copy-btn').click(function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        
        // Select and copy
        input.select();
        document.execCommand('copy');
        
        // Visual feedback
        const icon = $(this).find('i');
        const originalClass = icon.attr('class');
        
        icon.removeClass('fa-copy').addClass('fa-check');
        $(this).removeClass('btn-outline-primary').addClass('btn-success');
        
        setTimeout(() => {
            icon.attr('class', originalClass);
            $(this).removeClass('btn-success').addClass('btn-outline-primary');
        }, 1500);
        
        // Notification
        toastr.success('Berhasil disalin ke clipboard!');
    });
});
</script>
@stop
