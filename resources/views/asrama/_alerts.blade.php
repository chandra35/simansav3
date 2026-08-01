@php
    $asramaFlash = collect(['success' => 'success', 'warning' => 'warning', 'error' => 'error'])
        ->map(fn ($icon, $key) => session($key) ? ['icon' => $icon, 'text' => session($key)] : null)
        ->filter()->values();
@endphp
@if($asramaFlash->isNotEmpty() || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Swal) return;
            @foreach($asramaFlash as $flash)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    timer: 4500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    icon: @json($flash['icon']),
                    title: @json($flash['text'])
                });
            @endforeach
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Data belum dapat disimpan',
                    html: @json('<ul style="text-align:left;margin:0;padding-left:1.25em;">'.implode('', array_map(fn ($e) => '<li>'.e($e).'</li>', $errors->all())).'</ul>'),
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#17324d'
                });
            @endif
        });
    </script>
@endif
