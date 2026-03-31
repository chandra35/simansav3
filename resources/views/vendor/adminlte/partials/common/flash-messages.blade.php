@php
    $flashMessages = [
        'success' => [
            'class' => 'success',
            'icon' => 'fas fa-check-circle',
            'title' => 'Berhasil',
        ],
        'warning' => [
            'class' => 'warning',
            'icon' => 'fas fa-exclamation-triangle',
            'title' => 'Perlu Perhatian',
        ],
        'error' => [
            'class' => 'danger',
            'icon' => 'fas fa-times-circle',
            'title' => 'Terjadi Kendala',
        ],
        'status' => [
            'class' => 'info',
            'icon' => 'fas fa-info-circle',
            'title' => 'Informasi',
        ],
    ];
@endphp

@foreach($flashMessages as $key => $config)
    @if(session($key))
        @php
            $message = session($key);
            $isSessionExpired = $key === 'warning' && str_contains(strtolower($message), 'sesi');
        @endphp
        <div class="alert alert-{{ $config['class'] }} alert-dismissible fade show shadow-sm simansa-flash {{ $isSessionExpired ? 'simansa-flash-session' : '' }}" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="d-flex align-items-start">
                <div class="simansa-flash__icon mr-3">
                    <i class="{{ $config['icon'] }}"></i>
                </div>
                <div class="simansa-flash__body">
                    <div class="font-weight-bold simansa-flash__title">
                        {{ $isSessionExpired ? 'Sesi Berakhir' : $config['title'] }}
                    </div>
                    <div class="simansa-flash__message mb-0">
                        {{ $message }}
                    </div>
                    @if($isSessionExpired)
                        <small class="d-block mt-1 simansa-flash__hint">
                            Halaman sebelumnya sudah tidak aman dipakai lagi. Lanjutkan dari menu yang tersedia.
                        </small>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endforeach
