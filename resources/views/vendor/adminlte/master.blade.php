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
        /* Global consistency: header and menu title style */
        .main-sidebar .brand-text,
        .main-header .navbar-nav .nav-link,
        .nav-sidebar .nav-link > p,
        .nav-sidebar .nav-treeview .nav-link > p,
        .card-header .card-title {
            font-weight: 700;
        }

        .nav-sidebar .nav-header {
            color: rgba(255, 255, 255, 0.92) !important;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .card > .card-header {
            background: linear-gradient(135deg, rgba(37, 99, 235, .96), rgba(13, 148, 136, .88));
            color: #fff;
            border-bottom: 0;
        }

        .card > .card-header .card-title,
        .card > .card-header .card-tools,
        .card > .card-header .btn-tool,
        .card > .card-header .btn-tool i,
        .card > .card-header .btn,
        .card > .card-header .nav-link {
            color: #fff !important;
        }

        .card > .card-header .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.65);
        }

        .card > .card-header .btn-light {
            color: #1e3a8a !important;
            font-weight: 700;
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

        @keyframes appGlobalSpin {
            to {
                transform: rotate(360deg);
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
                window.jQuery(document).ajaxError(function (_event, xhr) {
                    if (xhr.status !== 419) {
                        return;
                    }

                    const response = xhr.responseJSON || {};
                    redirectExpiredSession(response.message, response.redirect_url);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                let formSubmitting = false;

                document.addEventListener('click', function (event) {
                    const link = event.target.closest('a[href]');
                    if (!link) {
                        return;
                    }

                    if (event.defaultPrevented || link.hasAttribute('data-no-overlay')) {
                        return;
                    }

                    const href = (link.getAttribute('href') || '').trim();
                    if (!href || href === '#' || href.startsWith('javascript:')) {
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
                }, true);

                document.addEventListener('submit', function (event) {
                    const form = event.target;
                    if (!form || event.defaultPrevented || form.hasAttribute('data-no-overlay')) {
                        return;
                    }

                    formSubmitting = true;
                    appShowGlobalOverlay('Menyimpan data...', 'Mohon tunggu, proses sedang berjalan');
                }, true);

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
            });
        })();
    </script>

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
