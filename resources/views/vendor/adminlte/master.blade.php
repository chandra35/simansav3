<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicons/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    <style>
        .simansa-page-hero {
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(37, 99, 235, .96), rgba(13, 148, 136, .9));
            box-shadow: 0 24px 48px rgba(37, 99, 235, 0.16);
            overflow: hidden;
        }

        .simansa-page-hero__body,
        .simansa-page-hero__content {
            padding: 1.35rem 1.45rem;
        }

        .simansa-hero,
        .simansa-page-hero__body > .simansa-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem 1.25rem;
        }

        .simansa-hero__main,
        .simansa-page-hero__content {
            flex: 1 1 420px;
            min-width: 0;
        }

        .simansa-hero__eyebrow,
        .simansa-page-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: .65rem;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.86);
        }

        .simansa-hero__title,
        .simansa-page-hero__title {
            margin: 0 0 .45rem;
            font-size: clamp(1.7rem, 2.2vw, 2.3rem);
            font-weight: 800;
            line-height: 1.15;
            color: #fff;
        }

        .simansa-hero__subtitle,
        .simansa-page-hero__subtitle {
            margin: 0;
            max-width: 780px;
            font-size: .96rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.9);
        }

        .simansa-hero__side,
        .simansa-page-hero__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
            justify-content: flex-end;
            flex: 0 1 520px;
        }

        .simansa-hero-chip {
            min-width: 158px;
            padding: .8rem .95rem;
            border-radius: 16px;
            border: 1px solid rgba(219, 234, 254, 0.7);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.6);
        }

        .simansa-hero-chip__label {
            display: block;
            margin-bottom: .3rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }

        .simansa-hero-chip__value {
            display: block;
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1.15;
            color: #0f172a;
        }

        .simansa-management-card > .card-header,
        .simansa-surface-card > .card-header {
            padding: 1rem 1.15rem;
        }

        .simansa-management-card > .card-body,
        .simansa-surface-card > .card-body {
            padding: 1.15rem;
        }

        .simansa-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .8rem 1rem;
        }

        .simansa-toolbar__group {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
        }

        .simansa-filter-panel {
            border: 1px solid rgba(191, 219, 254, 0.9);
            border-radius: 18px;
            padding: 1rem 1rem .85rem;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.98), rgba(240, 247, 255, 0.92));
        }

        .simansa-filter-panel--accent {
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.98), rgba(224, 242, 254, 0.96));
        }

        .simansa-filter-label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .45rem;
            font-size: .82rem;
            font-weight: 700;
            color: #1e3a8a;
        }

        .simansa-filter-hint {
            margin-top: .45rem;
            font-size: .77rem;
            color: #64748b;
        }

        .simansa-results-panel {
            border: 1px solid rgba(203, 213, 225, 0.8);
            border-radius: 16px;
            padding: .95rem 1rem;
            background: #fff;
        }

        .simansa-results-panel__title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .8rem;
        }

        .simansa-results-panel__title h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .simansa-selection-grid .custom-control {
            padding: .9rem 1rem .9rem 2.2rem;
            border: 1px solid rgba(203, 213, 225, 0.86);
            border-radius: 14px;
            background: #fff;
            min-height: 100%;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .simansa-selection-grid .custom-control:hover {
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.08);
            transform: translateY(-1px);
        }

        .simansa-selection-grid .custom-control-input:checked ~ .custom-control-label {
            color: #0f172a;
        }

        .simansa-selection-grid .custom-control-input:checked ~ .custom-control-label::before {
            border-color: #2563eb;
            background-color: #2563eb;
        }

        .simansa-selection-grid .custom-control-label {
            cursor: pointer;
            width: 100%;
        }

        .simansa-section-note {
            border: 1px solid rgba(191, 219, 254, .85);
            border-radius: 16px;
            padding: .9rem 1rem;
            background: rgba(239, 246, 255, 0.94);
            color: #1e3a8a;
        }

        .simansa-form-shell {
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }

        .simansa-form-card > .card-body {
            padding: 1.15rem 1.15rem 1rem;
        }

        .simansa-form-section {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .simansa-form-section__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
        }

        .simansa-form-section__desc {
            margin: .3rem 0 0;
            font-size: .85rem;
            line-height: 1.65;
            color: #64748b;
        }

        .simansa-check-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: .8rem;
        }

        .simansa-check-card {
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 15px;
            padding: .85rem .95rem;
            background: #fff;
        }

        .simansa-check-card--active {
            border-color: rgba(34, 197, 94, 0.36);
            background: rgba(240, 253, 244, 0.96);
        }

        .simansa-check-card__meta {
            display: block;
            margin-top: .2rem;
            font-size: .78rem;
            color: #64748b;
        }

        .simansa-sticky-actions {
            position: sticky;
            bottom: 16px;
            z-index: 20;
        }

        .simansa-btn-strong {
            background: linear-gradient(135deg, #1d4ed8, #0f766e);
            border: 1px solid rgba(29, 78, 216, 0.92);
            color: #fff !important;
            font-weight: 700;
            box-shadow: 0 14px 28px rgba(29, 78, 216, 0.18);
        }

        .simansa-btn-strong:hover,
        .simansa-btn-strong:focus {
            background: linear-gradient(135deg, #1e40af, #0f766e);
            border-color: rgba(30, 64, 175, 0.96);
            color: #fff !important;
        }

        .simansa-btn-contrast {
            background: #fff !important;
            border: 1px solid #2563eb !important;
            color: #1d4ed8 !important;
            font-weight: 700;
        }

        .simansa-btn-contrast:hover,
        .simansa-btn-contrast:focus {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }

        .simansa-btn-muted {
            background: rgba(241, 245, 249, 0.96);
            border: 1px solid rgba(203, 213, 225, 0.92);
            color: #334155 !important;
            font-weight: 700;
        }

        .simansa-btn-header-soft {
            background: rgba(255, 255, 255, 0.14) !important;
            border: 1px solid rgba(255, 255, 255, 0.28) !important;
            color: #fff !important;
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }

        .simansa-btn-header-soft i,
        .simansa-btn-header-soft span {
            color: #fff !important;
        }

        .simansa-btn-header-soft:hover,
        .simansa-btn-header-soft:focus {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            color: #fff !important;
        }

        .simansa-mini-stat {
            padding: .75rem .9rem;
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, .92);
            background: rgba(255, 255, 255, .96);
        }

        .simansa-mini-stat__label {
            display: block;
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }

        .simansa-mini-stat__value {
            display: block;
            margin-top: .25rem;
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
        }

        .simansa-empty-state {
            padding: 2.3rem 1.5rem;
            text-align: center;
            color: #64748b;
        }

        .simansa-empty-state i {
            font-size: 2.7rem;
            color: #94a3b8;
            margin-bottom: .8rem;
        }

        .app-global-overlay {
            position: fixed;
            inset: 0;
            z-index: 110000;
            display: none;
        }

        .app-global-overlay.active {
            display: block;
        }

        .app-global-overlay__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.54);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .app-global-overlay__content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
            min-width: 240px;
        }

        .app-global-overlay__spinner {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 999px;
            border: 3px solid rgba(255, 255, 255, 0.18);
            border-top-color: rgba(255, 255, 255, 0.92);
            animation: appGlobalSpin .8s linear infinite;
        }

        .app-global-overlay__title {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .02em;
            margin-bottom: 4px;
        }

        .app-global-overlay__subtitle {
            font-size: .83rem;
            color: rgba(255, 255, 255, 0.72);
        }

        .simansa-menu-coach,
        .simansa-menu-coach__pulse {
            display: none !important;
        }

        @keyframes appGlobalSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 991.98px) {
            .simansa-page-hero__body,
            .simansa-page-hero__content {
                padding: 1.15rem;
            }

            .simansa-hero__side,
            .simansa-page-hero__meta {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .simansa-menu-coach {
                position: fixed;
                top: 58px;
                left: 10px;
                z-index: 1055;
                display: none;
                align-items: center;
                gap: .55rem;
                max-width: min(250px, calc(100vw - 20px));
                padding: .65rem .85rem;
                border-radius: 14px;
                background: linear-gradient(135deg, rgba(30, 64, 175, 0.96), rgba(13, 148, 136, 0.94));
                color: #fff;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
            }

            .simansa-menu-coach.show {
                display: flex;
                animation: simansaCoachIn .28s ease;
            }

            .simansa-menu-coach__icon {
                width: 34px;
                height: 34px;
                flex: 0 0 34px;
                border-radius: 10px;
                background: rgba(255,255,255,.16);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: .95rem;
            }

            .simansa-menu-coach__text {
                min-width: 0;
                font-size: .8rem;
                line-height: 1.45;
                color: rgba(255,255,255,.92);
            }

            .simansa-menu-coach__text strong {
                display: block;
                font-size: .84rem;
                font-weight: 800;
                color: #fff;
                margin-bottom: .08rem;
            }

            .simansa-menu-coach__close {
                border: 0;
                background: transparent;
                color: rgba(255,255,255,.84);
                font-size: 1rem;
                padding: .1rem;
                line-height: 1;
            }

            .simansa-menu-coach__pulse {
                position: fixed;
                top: 14px;
                left: 14px;
                width: 36px;
                height: 36px;
                border-radius: 12px;
                border: 2px solid rgba(37, 99, 235, 0.35);
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.20);
                pointer-events: none;
                opacity: 0;
                z-index: 1054;
            }

            .simansa-menu-coach__pulse.show {
                opacity: 1;
                animation: simansaMenuPulse 1.8s ease-in-out infinite;
            }

            .simansa-filter-panel {
                padding: .95rem .9rem .8rem;
            }

            .simansa-form-section {
                flex-direction: column;
            }

            .simansa-sticky-actions {
                position: static;
            }
        }

        @keyframes simansaCoachIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes simansaMenuPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.24);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(37, 99, 235, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
            }
        }
    </style>

    <div id="appGlobalOverlay" class="app-global-overlay" aria-hidden="true">
        <div class="app-global-overlay__backdrop"></div>
        <div class="app-global-overlay__content">
            <div class="app-global-overlay__spinner"></div>
            <div class="app-global-overlay__title" id="appGlobalOverlayTitle">Memuat halaman...</div>
            <div class="app-global-overlay__subtitle" id="appGlobalOverlaySubtitle">Mohon tunggu sebentar</div>
        </div>
    </div>

    @auth
        <div id="simansaMenuCoach" class="simansa-menu-coach" aria-hidden="true">
            <span class="simansa-menu-coach__icon"><i class="fas fa-bars"></i></span>
            <div class="simansa-menu-coach__text">
                <strong>Buka Menu Navigasi</strong>
                Tap ikon garis tiga di kiri atas untuk melihat semua menu siswa.
            </div>
            <button type="button" class="simansa-menu-coach__close" id="simansaMenuCoachClose" aria-label="Tutup bantuan">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="simansaMenuCoachPulse" class="simansa-menu-coach__pulse" aria-hidden="true"></div>
    @endauth

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    <script>
        (function () {
            const deviceLocationConfig = {
                syncUrl: @json(route('device-location.sync')),
                isAuthenticated: @json(auth()->check()),
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                storageKey: 'simansa_device_location',
            };

            let currentDeviceLocation = null;

            function loadStoredDeviceLocation() {
                try {
                    const raw = window.localStorage.getItem(deviceLocationConfig.storageKey);
                    if (!raw) {
                        return null;
                    }

                    const parsed = JSON.parse(raw);
                    if (!parsed || parsed.latitude === undefined || parsed.longitude === undefined) {
                        return null;
                    }

                    return parsed;
                } catch (error) {
                    return null;
                }
            }

            function persistDeviceLocation(location) {
                currentDeviceLocation = location;

                try {
                    window.localStorage.setItem(deviceLocationConfig.storageKey, JSON.stringify(location));
                } catch (error) {
                    // Ignore storage failures.
                }
            }

            function applyDeviceLocationHeaders() {
                if (!currentDeviceLocation) {
                    return;
                }

                if (window.axios && window.axios.defaults && window.axios.defaults.headers) {
                    window.axios.defaults.headers.common['X-Device-Latitude'] = currentDeviceLocation.latitude;
                    window.axios.defaults.headers.common['X-Device-Longitude'] = currentDeviceLocation.longitude;
                }
            }

            function appendDeviceLocationToForm(form) {
                if (!form || !currentDeviceLocation) {
                    return;
                }

                ['latitude', 'longitude'].forEach(function (field) {
                    let input = form.querySelector('input[name="' + field + '"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field;
                        form.appendChild(input);
                    }

                    input.value = currentDeviceLocation[field];
                });
            }

            function syncDeviceLocationToSession() {
                if (!deviceLocationConfig.isAuthenticated || !currentDeviceLocation || !window.fetch) {
                    return;
                }

                window.fetch(deviceLocationConfig.syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': deviceLocationConfig.csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        latitude: currentDeviceLocation.latitude,
                        longitude: currentDeviceLocation.longitude,
                    }),
                }).catch(function () {
                    // Ignore sync failures and keep local state.
                });
            }

            function activateDeviceLocation(location) {
                persistDeviceLocation(location);
                applyDeviceLocationHeaders();
                syncDeviceLocationToSession();
            }

            function detectDeviceLocation() {
                if (!navigator.geolocation) {
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        activateDeviceLocation({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            captured_at: new Date().toISOString(),
                        });
                    },
                    function () {
                        // Keep the last known coordinates if browser blocks location.
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
                );
            }

            function appShowGlobalOverlay(title, subtitle) {
                const overlay = document.getElementById('appGlobalOverlay');
                if (!overlay) {
                    return;
                }

                const titleEl = document.getElementById('appGlobalOverlayTitle');
                const subtitleEl = document.getElementById('appGlobalOverlaySubtitle');

                if (titleEl) {
                    titleEl.textContent = title || 'Memuat halaman...';
                }

                if (subtitleEl) {
                    subtitleEl.textContent = subtitle || 'Mohon tunggu sebentar';
                }

                overlay.classList.add('active');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function appHideGlobalOverlay() {
                const overlay = document.getElementById('appGlobalOverlay');
                if (!overlay) {
                    return;
                }

                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
            }

            window.showAppGlobalOverlay = appShowGlobalOverlay;
            window.hideAppGlobalOverlay = appHideGlobalOverlay;

            const loginUrl = @json(route('login'));
            const defaultMessage = 'Sesi Anda telah berakhir. Silakan login kembali.';

            function redirectExpiredSession(message, redirectUrl) {
                const targetUrl = redirectUrl || loginUrl;

                try {
                    sessionStorage.setItem('flash_warning', message || defaultMessage);
                } catch (error) {
                    // Ignore storage failures and continue redirecting.
                }

                window.location.href = targetUrl;
            }

            if (window.axios && window.axios.interceptors) {
                window.axios.interceptors.response.use(
                    function (response) {
                        return response;
                    },
                    function (error) {
                        const status = error?.response?.status;
                        const payload = error?.response?.data || {};

                        if (status === 419) {
                            redirectExpiredSession(payload.message, payload.redirect_url);
                        }

                        return Promise.reject(error);
                    }
                );
            }

            if (window.jQuery) {
                window.jQuery(document).ajaxSend(function (_event, xhr) {
                    if (!currentDeviceLocation) {
                        return;
                    }

                    xhr.setRequestHeader('X-Device-Latitude', currentDeviceLocation.latitude);
                    xhr.setRequestHeader('X-Device-Longitude', currentDeviceLocation.longitude);
                });

                window.jQuery(document).ajaxError(function (_event, xhr) {
                    if (xhr.status !== 419) {
                        return;
                    }

                    const response = xhr.responseJSON || {};
                    redirectExpiredSession(response.message, response.redirect_url);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                currentDeviceLocation = loadStoredDeviceLocation();
                applyDeviceLocationHeaders();
                detectDeviceLocation();

                let formSubmitting = false;

                document.addEventListener('click', function (event) {
                    const link = event.target.closest('a[href]');
                    if (!link) {
                        return;
                    }

                    if (event.defaultPrevented || link.hasAttribute('data-no-overlay')) {
                        return;
                    }

                    if (link.closest('.modal')) {
                        return;
                    }

                    const href = (link.getAttribute('href') || '').trim();
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                        return;
                    }

                    if (href.startsWith('mailto:') || href.startsWith('tel:')) {
                        return;
                    }

                    if (link.target === '_blank' || link.hasAttribute('download')) {
                        return;
                    }

                    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) {
                        return;
                    }

                    appShowGlobalOverlay('Membuka halaman...', 'Sedang mengalihkan tampilan');
                });

                document.addEventListener('submit', function (event) {
                    const form = event.target;
                    if (!form || event.defaultPrevented || form.hasAttribute('data-no-overlay')) {
                        return;
                    }

                    appendDeviceLocationToForm(form);
                    formSubmitting = true;
                    appShowGlobalOverlay('Menyimpan data...', 'Mohon tunggu, proses sedang berjalan');
                });

                window.addEventListener('beforeunload', function () {
                    appShowGlobalOverlay(
                        formSubmitting ? 'Menyelesaikan proses...' : 'Memuat ulang halaman...',
                        'Mohon tunggu sebentar'
                    );
                });

                window.addEventListener('pageshow', function () {
                    appHideGlobalOverlay();
                });

                let warning = null;

                try {
                    warning = sessionStorage.getItem('flash_warning');
                    sessionStorage.removeItem('flash_warning');
                } catch (error) {
                    warning = null;
                }

                if (!warning) {
                    initMobileMenuCoach();
                    return;
                }

                const container = document.querySelector('.content-wrapper, .login-box, .register-box, main, body');

                if (!container) {
                    return;
                }

                const alert = document.createElement('div');
                alert.className = 'alert alert-warning alert-dismissible fade show mx-3 mt-3';
                alert.setAttribute('role', 'alert');
                alert.innerHTML = '<strong>Sesi Berakhir</strong><br>' + warning +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button>';

                if (container === document.body) {
                    document.body.prepend(alert);
                } else {
                    container.prepend(alert);
                }

                initMobileMenuCoach();
            });

            function initMobileMenuCoach() {
                if (!window.matchMedia('(max-width: 767.98px)').matches) {
                    return;
                }

                if (document.body.classList.contains('login-page') || !document.querySelector('.main-sidebar')) {
                    return;
                }

                const coach = document.getElementById('simansaMenuCoach');
                const pulse = document.getElementById('simansaMenuCoachPulse');
                const closeButton = document.getElementById('simansaMenuCoachClose');
                const toggler = document.querySelector('[data-widget="pushmenu"]');
                const storageKey = 'simansa_menu_coach_hidden';

                if (!coach || !pulse || !toggler) {
                    return;
                }

                const hideCoach = function () {
                    coach.classList.remove('show');
                    pulse.classList.remove('show');
                    coach.setAttribute('aria-hidden', 'true');
                    pulse.setAttribute('aria-hidden', 'true');

                    try {
                        localStorage.setItem(storageKey, '1');
                    } catch (error) {}
                };

                if (closeButton) {
                    closeButton.addEventListener('click', hideCoach, { once: true });
                }

                toggler.addEventListener('click', hideCoach, { once: true });

                let hidden = false;
                try {
                    hidden = localStorage.getItem(storageKey) === '1';
                } catch (error) {}

                if (hidden) {
                    return;
                }

                window.setTimeout(function () {
                    coach.classList.add('show');
                    pulse.classList.add('show');
                    coach.setAttribute('aria-hidden', 'false');
                    pulse.setAttribute('aria-hidden', 'false');
                }, 700);
            }
        })();
    </script>

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
