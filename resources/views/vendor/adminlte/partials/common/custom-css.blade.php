{{-- Custom Compact Styles for SIMANSA v3 --}}
<link rel="stylesheet" href="{{ asset('css/custom-compact.css') }}?v={{ filemtime(public_path('css/custom-compact.css')) }}">
<style>
    .simansa-flash {
        border: 0;
        border-radius: 0.85rem;
        padding: 1rem 1rem 0.95rem;
    }

    .simansa-flash__icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background: rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }

    .simansa-flash__title {
        line-height: 1.1;
        margin-bottom: 0.2rem;
    }

    .simansa-flash__message,
    .simansa-flash__hint {
        line-height: 1.45;
    }

    .simansa-flash-session {
        border-left: 5px solid #856404;
    }
</style>
