<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'SIMANSA',
    'title_prefix' => '',
    'title_postfix' => ' - MAN 1 Metro',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>SIMANSA</b>',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'SIMANSA Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => 'text-sm',
    'classes_brand' => 'text-sm',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4 text-sm',
    'classes_sidebar_nav' => 'nav-compact nav-flat',
    'classes_topnav' => 'navbar-white navbar-light text-sm',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // ============================================
        // ADMIN MENU
        // ============================================
        
        // Dashboard
        [
            'key' => 'admin-dashboard',
            'text' => 'Dashboard',
            'url' => 'admin/dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'admin-dashboard-access',
        ],
        
        // GTK Personal Menu (ONLY for pure GTK role)
        [
            'key' => 'gtk-dashboard',
            'text' => 'Dashboard Saya',
            'route' => 'admin.gtk.dashboard',
            'icon' => 'fas fa-fw fa-home',
            'can' => 'gtk-menu-only',
        ],
        [
            'key' => 'gtk-profile',
            'text' => 'Profil Saya',
            'route' => 'admin.gtk.profile',
            'icon' => 'fas fa-fw fa-user-circle',
            'can' => 'gtk-menu-only',
        ],
        
        // MANAJEMEN DATA - Collapsible
        [
            'text' => 'Manajemen Data',
            'icon' => 'fas fa-fw fa-database',
            'submenu' => [
                [
                    'text' => 'Data Siswa',
                    'route' => 'admin.siswa.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'view-siswa',
                    'active' => ['admin/siswa*'],
                ],
                [
                    'text' => 'Sekolah Asal',
                    'route' => 'admin.sekolah-asal.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'view-siswa',
                    'active' => ['admin/sekolah-asal*'],
                ],
                [
                    'text' => 'Data GTK',
                    'route' => 'admin.gtk.index',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                    'can' => 'view-gtk',
                    'active' => ['admin/gtk*'],
                ],
                [
                    'text' => 'Data User',
                    'route' => 'admin.users.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'can' => 'view-user',
                    'active' => ['admin/users*'],
                ],
            ],
        ],
        
        // AKADEMIK - Collapsible
        [
            'text' => 'Akademik',
            'icon' => 'fas fa-fw fa-graduation-cap',
            'submenu' => [
                [
                    'text' => 'Tahun Pelajaran',
                    'route' => 'admin.tahun-pelajaran.index',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                    'can' => 'view-tahun-pelajaran',
                    'active' => ['admin/tahun-pelajaran*'],
                ],
                [
                    'text' => 'Kurikulum',
                    'route' => 'admin.kurikulum.index',
                    'icon' => 'fas fa-fw fa-book-open',
                    'can' => 'view-kurikulum',
                    'active' => ['admin/kurikulum*'],
                ],
                [
                    'text' => 'Manajemen Kelas',
                    'route' => 'admin.kelas.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'manage-kelas',
                    'active' => ['admin/kelas*'],
                ],
                [
                    'text' => 'Cetak Dokumen',
                    'route' => 'admin.cetak.index',
                    'icon' => 'fas fa-fw fa-print',
                    'can' => 'view-kelas',
                    'active' => ['admin/cetak*'],
                ],
                [
                    'text' => 'Mutasi Siswa',
                    'route' => 'admin.under-development',
                    'icon' => 'fas fa-fw fa-exchange-alt',
                    'can' => 'view-mutasi',
                    'label' => 'Soon',
                    'label_color' => 'warning',
                    'active' => ['admin/mutasi*'],
                ],
            ],
        ],
        
        // LAPORAN & MONITORING - Collapsible
        [
            'text' => 'Laporan & Monitoring',
            'icon' => 'fas fa-fw fa-chart-line',
            'submenu' => [
                [
                    'text' => 'Activity Log',
                    'route' => 'admin.activity-logs.index',
                    'icon' => 'fas fa-fw fa-history',
                    'can' => 'admin-access',
                    'active' => ['admin/activity-logs*'],
                ],
                [
                    'text' => 'Monitoring Users',
                    'route' => 'admin.monitoring.users',
                    'icon' => 'fas fa-fw fa-users-cog',
                    'can' => 'admin-access',
                    'active' => ['admin/monitoring*'],
                ],
            ],
        ],
        
        // PENGATURAN - Collapsible
        [
            'text' => 'Pengaturan',
            'icon' => 'fas fa-fw fa-cogs',
            'submenu' => [
                [
                    'text' => 'Pengaturan Aplikasi',
                    'route' => 'admin.settings.edit',
                    'icon' => 'fas fa-fw fa-sliders-h',
                    'can' => 'manage-settings',
                    'active' => ['admin/settings*'],
                ],
                [
                    'text' => 'Cek NIP',
                    'route' => 'admin.pengaturan.cek-nip.index',
                    'icon' => 'fas fa-fw fa-id-card',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/cek-nip*'],
                ],
                [
                    'text' => 'Cek NISN Siswa',
                    'route' => 'admin.pengaturan.cek-nisn.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/cek-nisn*'],
                ],
                [
                    'text' => 'Custom Menu Siswa',
                    'route' => 'admin.custom-menu.index',
                    'icon' => 'fas fa-fw fa-th-list',
                    'can' => 'manage-settings',
                    'active' => ['admin/custom-menu*'],
                ],
                [
                    'text' => 'Profile',
                    'route' => 'admin.profile',
                    'icon' => 'fas fa-fw fa-user',
                    'can' => 'admin-access',
                    'active' => ['admin/profile*'],
                ],
            ],
        ],

        // ============================================
        // SISWA MENU
        // ============================================
        [
            'text' => 'Dashboard',
            'route' => 'siswa.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'siswa-access',
        ],
        
        // PROFIL & DATA - Collapsible
        [
            'text' => 'Profil & Data',
            'icon' => 'fas fa-fw fa-user-circle',
            'can' => 'siswa-access',
            'submenu' => [
                [
                    'text' => 'Data Diri',
                    'route' => 'siswa.profile.diri',
                    'icon' => 'fas fa-fw fa-id-card',
                ],
                [
                    'text' => 'Data Orangtua',
                    'route' => 'siswa.profile.ortu',
                    'icon' => 'fas fa-fw fa-users',
                ],
                [
                    'text' => 'Dokumen Siswa',
                    'route' => 'siswa.dokumen',
                    'icon' => 'fas fa-fw fa-folder-open',
                    'key' => 'siswa-dokumen',
                ],
            ],
        ],
        
        // AKUN - Collapsible
        [
            'text' => 'Pengaturan Akun',
            'icon' => 'fas fa-fw fa-user-cog',
            'can' => 'siswa-access',
            'submenu' => [
                [
                    'text' => 'Ubah Password',
                    'route' => 'siswa.profile.password',
                    'icon' => 'fas fa-fw fa-lock',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'CustomCompact' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/custom-compact.css',
                ],
            ],
        ],
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
