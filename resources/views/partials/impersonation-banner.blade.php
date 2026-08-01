@php
    $impersonation = request()->attributes->get('impersonation');
    $impersonator = request()->attributes->get('impersonator');
@endphp

@if($impersonation)
    <div class="impersonation-banner" role="status">
        <div class="impersonation-banner__identity">
            <span class="impersonation-banner__icon"><i class="fas fa-user-secret"></i></span>
            <div>
                <strong>Mode Login As: {{ auth()->user()->name }}</strong>
                <small>Admin {{ $impersonator?->name }} tetap aktif di tab utama. Perubahan password diblokir.</small>
            </div>
        </div>
        <form method="POST"
              action="{{ route($impersonation->target_type === 'siswa' ? 'siswa.impersonation.stop' : 'admin.gtk.impersonation.stop') }}"
              data-no-overlay>
            @csrf
            <button type="submit" class="btn btn-primary-soft btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Admin
            </button>
        </form>
    </div>

    @once
        <style>
            .impersonation-banner {
                align-items: center;
                background: linear-gradient(135deg, #312e81, #4338ca);
                border-radius: .65rem;
                box-shadow: 0 .35rem 1rem rgba(67, 56, 202, .18);
                color: #fff;
                display: flex;
                gap: 1rem;
                justify-content: space-between;
                margin-bottom: 1rem;
                padding: .75rem 1rem;
            }
            .impersonation-banner__identity {
                align-items: center;
                display: flex;
                gap: .75rem;
                min-width: 0;
            }
            .impersonation-banner__identity strong,
            .impersonation-banner__identity small {
                display: block;
            }
            .impersonation-banner__identity small {
                color: rgba(255, 255, 255, .78);
                margin-top: .1rem;
            }
            .impersonation-banner__icon {
                align-items: center;
                background: rgba(255, 255, 255, .14);
                border-radius: 50%;
                display: inline-flex;
                flex: 0 0 2.35rem;
                height: 2.35rem;
                justify-content: center;
            }
            .impersonation-banner form {
                flex: 0 0 auto;
                margin: 0;
            }
            @media (max-width: 575.98px) {
                .impersonation-banner {
                    align-items: stretch;
                    flex-direction: column;
                }
                .impersonation-banner .btn {
                    width: 100%;
                }
            }
        </style>
    @endonce
@endif
