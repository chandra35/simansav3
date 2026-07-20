@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        @if(Auth::check())
            <li class="nav-item d-flex align-items-center mr-2">
                <div class="simansa-navbar-academic-year" title="Tahun pelajaran aktif">
                    <span class="simansa-navbar-academic-year__dot"></span>
                    <span class="simansa-navbar-academic-year__label">Tahun Aktif</span>
                    <strong>{{ $navbarActiveAcademicYear?->nama ?? 'Belum diatur' }}</strong>
                    @if($navbarActiveAcademicYear?->semester_aktif)<small>{{ $navbarActiveAcademicYear->semester_aktif }}</small>@endif
                </div>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center mr-2">
                <div class="simansa-navbar-live">
                    <i class="far fa-clock text-primary"></i>
                    <span id="simansaServerClock" class="simansa-navbar-live__clock">--:--:--</span>
                    <span id="simansaServerTimezone" class="simansa-navbar-live__tz">{{ config('app.timezone') }}</span>
                </div>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center mr-2">
                <div id="simansaNavbarPresence" class="simansa-navbar-presence is-syncing">
                    <span class="simansa-navbar-presence__dot"></span>
                    <span id="simansaNavbarPresenceLabel">Menyinkronkan</span>
                </div>
            </li>
        @endif

        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
