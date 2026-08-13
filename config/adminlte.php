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
        'enabled' => false,
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
    'classes_auth_footer' => 'text-center',
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
    'classes_sidebar' => 'sidebar-dark-indigo elevation-4 text-sm',
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
    'dashboard_url' => '',
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
            'can' => 'sidebar-gtk-menu-only',
        ],
        [
            'key' => 'gtk-profile',
            'text' => 'Profil Saya',
            'route' => 'admin.gtk.profile',
            'icon' => 'fas fa-fw fa-user-circle',
            'can' => 'sidebar-gtk-menu-only',
        ],
        [
            'key' => 'gtk-osis-election',
            'text' => 'Pemilihan OSIM',
            'route' => 'admin.gtk.osis-election.index',
            'icon' => 'fas fa-fw fa-vote-yea',
            'can' => 'sidebar-gtk-menu-only',
            'active' => ['admin/gtk/pemilihan-osis*'],
        ],
        [
            'key' => 'gtk-active-polling',
            'text' => 'Polling Aktif',
            'route' => 'admin.gtk.polling.index',
            'icon' => 'fas fa-fw fa-poll-h',
            'can' => 'sidebar-gtk-active-polling',
            'active' => ['admin/gtk/polling*'],
        ],
        
        // Portal Wali Kelas ("Kelas Saya") — hanya GTK yang jadi wali kelas aktif
        [
            'key' => 'wali-kelas-menu',
            'text' => 'Kelas Saya',
            'icon' => 'fas fa-fw fa-chalkboard-teacher',
            'can' => 'sidebar-wali-kelas-menu',
            'submenu' => [
                [
                    'text' => 'Daftar Siswa',
                    'route' => 'admin.gtk.wali.siswa.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/gtk/wali/siswa*'],
                ],
                [
                    'text' => 'Statistik Siswa',
                    'route' => 'admin.siswa.statistics',
                    'icon' => 'fas fa-fw fa-chart-pie',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/siswa-statistik*'],
                ],
                [
                    'text' => 'Absensi Harian',
                    'route' => 'admin.gtk.wali.absensi.index',
                    'icon' => 'fas fa-fw fa-clipboard-check',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/gtk/wali/absensi'],
                ],
                [
                    'text' => 'Rekap Absensi',
                    'route' => 'admin.gtk.wali.absensi.rekap',
                    'icon' => 'fas fa-fw fa-chart-bar',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/gtk/wali/absensi/rekap'],
                ],
                [
                    'text' => 'Analitik Kehadiran',
                    'route' => 'admin.absensi-siswa.analytics',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/absensi-siswa/analitik*'],
                ],
                [
                    'text' => 'Catatan Siswa',
                    'route' => 'admin.gtk.wali.catatan.index',
                    'icon' => 'fas fa-fw fa-sticky-note',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/gtk/wali/catatan*'],
                ],
                [
                    'text' => 'Jadwal Kelas',
                    'route' => 'admin.gtk.wali.jadwal.index',
                    'icon' => 'fas fa-fw fa-calendar-alt',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/gtk/wali/jadwal*'],
                ],
                [
                    'text' => 'Sekolah Asal',
                    'route' => 'admin.sekolah-asal.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'sidebar-wali-kelas-menu',
                    'active' => ['admin/sekolah-asal*'],
                ],
            ],
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
                    'text' => 'NIS Lokal',
                    'route' => 'admin.nis-lokal.index',
                    'icon' => 'fas fa-fw fa-id-card',
                    'can' => 'manage-nis-lokal',
                    'active' => ['admin/nis-lokal*'],
                ],
                [
                    'text' => 'Data Siswa KIP/SKTM/PKH',
                    'route' => 'admin.kip-sktm.index',
                    'icon' => 'fas fa-fw fa-hand-holding-heart',
                    'can' => 'view-pip',
                    'active' => ['admin/kip-sktm*', 'admin/pip*'],
                ],
                [
                    'text' => 'Statistik Siswa',
                    'route' => 'admin.siswa.statistics',
                    'icon' => 'fas fa-fw fa-chart-pie',
                    'can' => 'sidebar-student-statistics-global',
                    'active' => ['admin/siswa-statistik*'],
                ],
                [
                    'text' => 'Cek Data EMIS',
                    'route' => 'admin.emis-comparison.index',
                    'icon' => 'fas fa-fw fa-exchange-alt',
                    'can' => 'view-emis-comparison',
                    'active' => ['admin/cek-data-emis*'],
                ],
                [
                    'text' => 'Sekolah Asal',
                    'route' => 'admin.sekolah-asal.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'sidebar-school-origin-global',
                    'active' => ['admin/sekolah-asal*'],
                ],
                [
                    'text' => 'Verifikasi Ijazah',
                    'route' => 'admin.verifikasi-ijazah.index',
                    'icon' => 'fas fa-fw fa-certificate',
                    'can' => 'verifikasi-ijazah',
                    'active' => ['admin/verifikasi-ijazah*'],
                ],
                [
                    'text' => 'GTK & Penugasan',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                    'active' => ['admin/gtk*', 'admin/mutasi-gtk*', 'admin/penugasan-gtk*', 'admin/beban-kerja-gtk*'],
                    'submenu' => [
                        [
                            'text' => 'Data GTK',
                            'route' => 'admin.gtk.index',
                            'icon' => 'fas fa-fw fa-users',
                            'can' => 'view-gtk',
                            'active' => ['admin/gtk*'],
                        ],
                        [
                            'text' => 'Mutasi & Status GTK',
                            'route' => 'admin.mutasi-gtk.index',
                            'icon' => 'fas fa-fw fa-user-clock',
                            'can' => 'view-mutasi-gtk',
                            'active' => ['admin/mutasi-gtk*'],
                        ],
                        [
                            'text' => 'Penugasan GTK',
                            'route' => 'admin.penugasan-gtk.index',
                            'icon' => 'fas fa-fw fa-user-tie',
                            'can' => 'view-penugasan-gtk',
                            'active' => ['admin/penugasan-gtk', 'admin/penugasan-gtk-jenis*'],
                        ],
                        [
                            'text' => 'Beban Kerja GTK',
                            'route' => 'admin.penugasan-gtk.workload',
                            'icon' => 'fas fa-fw fa-balance-scale',
                            'can' => 'view-beban-kerja-gtk',
                            'active' => ['admin/beban-kerja-gtk*'],
                        ],
                    ],
                ],
            ],
        ],
        
        // AKADEMIK - Collapsible
        [
            'text' => 'Akademik',
            'icon' => 'fas fa-fw fa-graduation-cap',
            'submenu' => [
                [
                    'text' => 'Manajemen Kelas',
                    'route' => 'admin.kelas.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'manage-kelas',
                    'active' => ['admin/kelas*'],
                ],
                [
                    'text' => 'Cetak Dokumen',
                    'icon' => 'fas fa-fw fa-print',
                    'can' => 'view-kelas',
                    'active' => ['admin/cetak*'],
                    'submenu' => [
                        [
                            'text' => 'Cetak Absensi',
                            'route' => 'admin.cetak.index',
                            'icon' => 'fas fa-fw fa-clipboard-check',
                            'can' => 'view-kelas',
                            'active' => ['admin/cetak'],
                        ],
                        [
                            'text' => 'ID Card Siswa',
                            'route' => 'admin.cetak.id-card-siswa.index',
                            'icon' => 'fas fa-fw fa-id-card',
                            'can' => 'view-siswa',
                            'active' => ['admin/cetak/id-card-siswa*'],
                        ],
                        [
                            'text' => 'ID Card GTK',
                            'route' => 'admin.cetak.id-card-gtk.index',
                            'icon' => 'fas fa-fw fa-id-badge',
                            'can' => 'view-gtk',
                            'active' => ['admin/cetak/id-card-gtk*'],
                        ],
                        [
                            'text' => 'Download Foto Siswa',
                            'route' => 'admin.cetak.download-foto-siswa.index',
                            'icon' => 'fas fa-fw fa-file-archive',
                            'can' => 'download-foto-kelas',
                            'active' => ['admin/cetak/download-foto-siswa*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Mutasi Siswa',
                    'route' => 'admin.mutasi-siswa.index',
                    'icon' => 'fas fa-fw fa-exchange-alt',
                    'can' => 'view-mutasi',
                    'active' => ['admin/mutasi*'],
                ],
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
                    'text' => 'Mata Pelajaran',
                    'route' => 'admin.mapel.index',
                    'icon' => 'fas fa-fw fa-book',
                    'can' => 'view-mapel',
                    'active' => ['admin/mapel*'],
                ],
                [
                    'text' => 'Jadwal Pelajaran',
                    'route' => 'admin.jadwal-pelajaran.index',
                    'icon' => 'fas fa-fw fa-clock',
                    'can' => 'view-jadwal-pelajaran',
                    'active' => ['admin/jadwal-pelajaran*'],
                ],
                [
                    'text' => 'Mapping Jadwal',
                    'route' => 'admin.jadwal-mapping.index',
                    'icon' => 'fas fa-fw fa-link',
                    'can' => 'view-jadwal-mapping',
                    'active' => ['admin/jadwal-mapping*'],
                ],
                [
                    'text' => 'Nilai Siswa',
                    'route' => 'admin.nilai.index',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'can' => 'view-nilai',
                    'active' => ['admin/nilai*'],
                ],
                [
                    'text' => 'Kalender Akademik',
                    'route' => 'admin.kalender-akademik.index',
                    'icon' => 'fas fa-fw fa-calendar',
                    'can' => 'view-kalender-akademik',
                    'active' => ['admin/kalender-akademik*'],
                ],
                [
                    'text' => 'PPDB',
                    'icon' => 'fas fa-fw fa-user-plus',
                    'can' => 'manage-kelas',
                    'active' => ['admin/matrikulasi-ppdb*'],
                    'submenu' => [
                        [
                            'text' => 'Matrikulasi PPDB',
                            'route' => 'admin.matrikulasi-ppdb.index',
                            'icon' => 'fas fa-fw fa-user-check',
                            'can' => 'manage-kelas',
                            'active' => ['admin/matrikulasi-ppdb*'],
                        ],
                    ],
                ],
                [
                    'text' => 'RDM',
                    'icon' => 'fas fa-fw fa-database',
                    'can' => 'view-rdm',
                    'active' => ['admin/rdm-sync*', 'admin/rdm-mapel-mapping*', 'admin/rdm-matching*'],
                    'submenu' => [
                        [
                            'text' => 'Integrasi RDM',
                            'route' => 'admin.rdm-sync.index',
                            'icon' => 'fas fa-fw fa-sync-alt',
                            'can' => 'view-rdm',
                            'active' => ['admin/rdm-sync*'],
                        ],
                        [
                            'text' => 'Mapping Mapel RDM',
                            'route' => 'admin.rdm-mapel-mapping.index',
                            'icon' => 'fas fa-fw fa-exchange-alt',
                            'can' => 'view-rdm',
                            'active' => ['admin/rdm-mapel-mapping*'],
                        ],
                        [
                            'text' => 'Matching Siswa RDM',
                            'route' => 'admin.rdm-matching.index',
                            'icon' => 'fas fa-fw fa-random',
                            'can' => 'view-rdm',
                            'active' => ['admin/rdm-matching*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Proses Akhir Tahun',
                    'route' => 'admin.kenaikan-kelas.index',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                    'can' => 'manage-settings',
                    'active' => ['admin/kenaikan-kelas*'],
                ],
            ],
        ],

        // ASRAMA - modul mandiri dengan master siswa dan GTK dari SIMANSA
        [
            'key' => 'asrama-main',
            'text' => 'ASRAMA',
            'icon' => 'fas fa-fw fa-mosque text-info',
            'can' => 'view-asrama-portal',
            'active' => ['asrama*'],
            'submenu' => [
                [
                    'text' => 'Dashboard Asrama',
                    'route' => 'asrama.dashboard',
                    'icon' => 'fas fa-fw fa-home',
                    'can' => 'view-asrama-portal',
                ],
                [
                    'text' => 'Operator Asrama',
                    'route' => 'asrama.operator.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'can' => 'manage-asrama-operator',
                ],
                [
                    'text' => 'Santri Asrama',
                    'route' => 'asrama.santri.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'manage-asrama-santri',
                ],
                [
                    'text' => 'Nomor Induk Santri',
                    'route' => 'asrama.santri.induk.index',
                    'icon' => 'fas fa-fw fa-id-card',
                    'can' => 'manage-asrama-santri',
                ],
                [
                    'text' => 'Pengasuh & Pengajar',
                    'route' => 'asrama.asatidz.index',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                    'can' => 'manage-asrama-asatidz',
                ],
                [
                    'text' => 'Rombel Asrama',
                    'route' => 'asrama.kelas.index',
                    'icon' => 'fas fa-fw fa-school',
                    'can' => 'manage-asrama-kelas',
                ],
                [
                    'text' => 'Kamar Asrama',
                    'route' => 'asrama.kamar.index',
                    'icon' => 'fas fa-fw fa-bed',
                    'can' => 'manage-asrama-kamar',
                ],
                [
                    'text' => 'Mapel Asrama',
                    'route' => 'asrama.mapel.index',
                    'icon' => 'fas fa-fw fa-book-open',
                    'can' => 'manage-asrama-mapel',
                ],
                [
                    'text' => 'Input Nilai',
                    'route' => 'asrama.nilai.index',
                    'icon' => 'fas fa-fw fa-pen',
                    'can' => 'input-nilai-asrama',
                ],
                [
                    'text' => 'Rapor Asrama',
                    'route' => 'asrama.rapor.index',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'can' => 'asrama-rapor-access',
                ],
            ],
        ],

        // SMART-Q KELAS UNGGULAN
        [
            'text' => 'SMART-Q Unggulan',
            'icon' => 'fas fa-fw fa-star text-warning',
            'route' => 'admin.smartq.index',
            'can' => 'view-smartq',
            'active' => ['admin/smartq*'],
        ],
        
        // DOWNLOAD CENTER
        [
            'text' => 'Download Center',
            'icon' => 'fas fa-fw fa-download',
            'submenu' => [
                [
                    'text' => 'File Download',
                    'route' => 'admin.downloads.index',
                    'icon' => 'fas fa-fw fa-file-download',
                    'can' => 'view-downloads',
                    'active' => ['admin/downloads*'],
                ],
                [
                    'text' => 'Kategori Download',
                    'route' => 'admin.download-categories.index',
                    'icon' => 'fas fa-fw fa-folder-open',
                    'can' => 'manage-download-settings',
                    'active' => ['admin/download-categories*'],
                ],
                [
                    'text' => 'Pengaturan Storage',
                    'route' => 'admin.download-settings.edit',
                    'icon' => 'fas fa-fw fa-cog',
                    'can' => 'manage-download-settings',
                    'active' => ['admin/download-settings*'],
                ],
            ],
        ],

        // KESISWAAN - Collapsible
        [
            'text' => 'Kesiswaan',
            'icon' => 'fas fa-fw fa-users',
            'submenu' => [
                [
                    'text' => 'Prestasi Siswa',
                    'route' => 'admin.prestasi-siswa.index',
                    'icon' => 'fas fa-fw fa-trophy',
                    'can' => 'view-prestasi-siswa',
                    'active' => ['admin/prestasi-siswa*'],
                ],
                [
                    'text' => 'Ekstrakurikuler',
                    'route' => 'admin.ekstrakurikuler.index',
                    'icon' => 'fas fa-fw fa-futbol',
                    'can' => 'view-ekstrakurikuler',
                    'active' => ['admin/ekstrakurikuler*'],
                ],
                [
                    'text' => 'Bimbingan & Konseling',
                    'route' => 'admin.catatan-konseling.index',
                    'icon' => 'fas fa-fw fa-comments',
                    'can' => 'view-catatan-konseling',
                    'active' => ['admin/catatan-konseling*'],
                ],
                [
                    'text' => 'Pemilihan OSIM',
                    'route' => 'admin.osis-election.index',
                    'icon' => 'fas fa-fw fa-vote-yea',
                    'can' => 'view-osis-election',
                    'active' => ['admin/pemilihan-osis*'],
                ],
                [
                    'text' => 'Lulusan',
                    'route' => 'admin.lulusan.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'kesiswaan-lulusan-access',
                    'active' => ['admin/lulusan*'],
                ],
                [
                    'text' => 'Alumni',
                    'route' => 'admin.alumni.index',
                    'icon' => 'fas fa-fw fa-user-friends',
                    'can' => 'kesiswaan-lulusan-access',
                    'active' => ['admin/alumni*'],
                ],
                [
                    'text' => 'Pengumuman Kelulusan',
                    'route' => 'admin.kelulusan-pengumuman.index',
                    'icon' => 'fas fa-fw fa-envelope-open-text',
                    'can' => 'kesiswaan-lulusan-access',
                    'active' => ['admin/kelulusan-pengumuman*'],
                ],
                [
                    'text' => 'Checker Jalur PTN',
                    'icon' => 'fas fa-fw fa-search-location',
                    'can' => 'kesiswaan-lulusan-access',
                    'active' => ['admin/snbp-menu*', 'admin/span-ptkin-menu*'],
                    'submenu' => [
                        [
                            'text' => 'Checker SNBP',
                            'route' => 'admin.snbp-menu.index',
                            'icon' => 'fas fa-fw fa-graduation-cap',
                            'can' => 'kesiswaan-lulusan-access',
                            'active' => ['admin/snbp-menu*'],
                        ],
                        [
                            'text' => 'Checker SPAN-PTKIN',
                            'route' => 'admin.span-ptkin-menu.index',
                            'icon' => 'fas fa-fw fa-mosque',
                            'can' => 'kesiswaan-lulusan-access',
                            'active' => ['admin/span-ptkin-menu*'],
                        ],
                    ],
                ],
            ],
        ],
        
        // PRESENSI
        [
            'text' => 'Presensi',
            'icon' => 'fas fa-fw fa-fingerprint',
            'can' => 'staff-presensi-menu',
            'active' => ['admin/absensi', 'admin/absensi/*', 'admin/absensi-siswa*'],
            'submenu' => [
                [
                    'text' => 'Face Recognition',
                    'icon' => 'fas fa-fw fa-id-card-alt',
                    'can' => 'face-registration-access',
                    'active' => ['admin/absensi/face-*'],
                    'submenu' => [
                        [
                            'text' => 'Registrasi Wajah',
                            'route' => 'admin.absensi.face-register',
                            'icon' => 'fas fa-fw fa-camera',
                            'can' => 'face-registration-access',
                            'active' => ['admin/absensi/face-register*'],
                        ],
                        [
                            'text' => 'Verifikasi & Data',
                            'route' => 'admin.absensi.face-verification',
                            'icon' => 'fas fa-fw fa-user-check',
                            'can' => 'face-registration-admin',
                            'active' => ['admin/absensi/face-verification*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Presensi Terpusat',
                    'icon' => 'fas fa-fw fa-clock',
                    'active' => ['admin/absensi', 'admin/absensi/kiosk*', 'admin/absensi/rekap*', 'admin/absensi/settings*'],
                    'submenu' => [
                        [
                            'text' => 'Dashboard GTK',
                            'route' => 'admin.absensi.index',
                            'icon' => 'fas fa-fw fa-clipboard-check',
                            'can' => 'view-absensi',
                            'active' => ['admin/absensi'],
                        ],
                        [
                            'text' => 'Kiosk GTK',
                            'url' => 'admin/absensi/kiosk?type=gtk',
                            'icon' => 'fas fa-fw fa-desktop',
                            'can' => 'face-registration-admin',
                            'active' => ['admin/absensi/kiosk*'],
                        ],
                        [
                            'text' => 'Kiosk Siswa',
                            'url' => 'admin/absensi/kiosk?type=siswa',
                            'icon' => 'fas fa-fw fa-user-graduate',
                            'can' => 'face-registration-admin',
                            'active' => [],
                        ],
                        [
                            'text' => 'Rekap GTK',
                            'route' => 'admin.absensi.rekap',
                            'icon' => 'fas fa-fw fa-chart-bar',
                            'can' => 'view-absensi',
                            'active' => ['admin/absensi/rekap*'],
                        ],
                        [
                            'text' => 'Pengaturan Presensi',
                            'route' => 'admin.absensi.settings',
                            'icon' => 'fas fa-fw fa-cog',
                            'can' => 'manage-settings',
                            'active' => ['admin/absensi/settings*'],
                        ],
                    ],
                ],
                [
                    'text' => 'Absensi Siswa',
                    'icon' => 'fas fa-fw fa-user-check',
                    'can' => 'sidebar-student-attendance-global',
                    'active' => ['admin/absensi-siswa*'],
                    'submenu' => [
                        [
                            'text' => 'Input Absensi',
                            'route' => 'admin.absensi-siswa.index',
                            'icon' => 'fas fa-fw fa-clipboard-check',
                            'can' => 'input-daily-attendance',
                            'active' => ['admin/absensi-siswa'],
                        ],
                        [
                            'text' => 'Pemantauan Harian',
                            'route' => 'admin.absensi-siswa.monitoring',
                            'icon' => 'fas fa-fw fa-clipboard-list',
                            'can' => 'monitor-all-student-attendance',
                            'active' => ['admin/absensi-siswa/pemantauan'],
                        ],
                        [
                            'text' => 'Analitik Kehadiran',
                            'route' => 'admin.absensi-siswa.analytics',
                            'icon' => 'fas fa-fw fa-chart-line',
                            'can' => 'view-attendance-analytics',
                            'active' => ['admin/absensi-siswa/analitik*'],
                        ],
                    ],
                ],
            ],
        ],

        // HOTSPOT MANAGER
        [
            'text' => 'Hotspot',
            'icon' => 'fas fa-fw fa-wifi',
            'can' => 'view-hotspot',
            'active' => ['admin/hotspot*'],
            'submenu' => [
                [
                    'text' => 'Akun Hotspot',
                    'route' => 'admin.hotspot.index',
                    'icon' => 'fas fa-fw fa-users',
                    'active' => ['admin/hotspot'],
                ],
                [
                    'text' => 'Monitoring Online',
                    'route' => 'admin.hotspot.online',
                    'icon' => 'fas fa-fw fa-satellite-dish',
                    'active' => ['admin/hotspot/online*'],
                ],
                [
                    'text' => 'Log Autentikasi',
                    'route' => 'admin.hotspot.auth-logs',
                    'icon' => 'fas fa-fw fa-clipboard-list',
                    'active' => ['admin/hotspot/auth-logs*'],
                ],
                [
                    'text' => 'Setting',
                    'route' => 'admin.hotspot.settings',
                    'icon' => 'fas fa-fw fa-cogs',
                    'can' => 'manage-hotspot',
                    'active' => ['admin/hotspot/settings*', 'admin/hotspot/radius-profiles*'],
                ],
            ],
        ],

        // KEUANGAN - Collapsible
        [
            'text' => 'Keuangan',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'submenu' => [
                [
                    'text' => 'Jenis Pembayaran',
                    'route' => 'admin.pembayaran.jenis',
                    'icon' => 'fas fa-fw fa-list',
                    'can' => 'view-keuangan',
                    'active' => ['admin/pembayaran/jenis*'],
                ],
                [
                    'text' => 'Tagihan',
                    'route' => 'admin.pembayaran.tagihan',
                    'icon' => 'fas fa-fw fa-file-invoice',
                    'can' => 'view-keuangan',
                    'active' => ['admin/pembayaran/tagihan*'],
                ],
                [
                    'text' => 'Pembayaran',
                    'route' => 'admin.pembayaran.index',
                    'icon' => 'fas fa-fw fa-cash-register',
                    'can' => 'view-keuangan',
                    'active' => ['admin/pembayaran'],
                ],
                [
                    'text' => 'Laporan Keuangan',
                    'route' => 'admin.pembayaran.laporan',
                    'icon' => 'fas fa-fw fa-chart-pie',
                    'can' => 'view-keuangan',
                    'active' => ['admin/pembayaran/laporan*'],
                ],
            ],
        ],
        
        // LAYANAN SURAT - Collapsible
        [
            'text' => 'Layanan Surat',
            'icon' => 'fas fa-fw fa-envelope',
            'submenu' => [
                [
                    'text' => 'Template Surat',
                    'route' => 'admin.surat-keterangan.template',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'can' => 'view-layanan-surat',
                    'active' => ['admin/surat-keterangan/template*'],
                ],
                [
                    'text' => 'Surat Keterangan',
                    'route' => 'admin.surat-keterangan.index',
                    'icon' => 'fas fa-fw fa-file-signature',
                    'can' => 'view-layanan-surat',
                    'active' => ['admin/surat-keterangan'],
                ],
            ],
        ],
        
        // INFORMASI - Collapsible
        [
            'text' => 'Informasi',
            'icon' => 'fas fa-fw fa-bullhorn',
            'submenu' => [
                [
                    'text' => 'Pengumuman',
                    'route' => 'admin.pengumuman.index',
                    'icon' => 'fas fa-fw fa-bell',
                    'can' => 'view-pengumuman',
                    'active' => ['admin/pengumuman*'],
                ],
                [
                    'text' => 'Polling & Survei',
                    'route' => 'admin.polling.index',
                    'icon' => 'fas fa-fw fa-poll-h',
                    'can' => 'view-polling-results',
                    'active' => ['admin/polling*'],
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
                    'can' => 'view-activity-log',
                    'active' => ['admin/activity-logs*'],
                ],
                [
                    'text' => 'Monitoring Users',
                    'route' => 'admin.monitoring.users',
                    'icon' => 'fas fa-fw fa-users-cog',
                    'can' => 'view-monitoring-users',
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
                    'active' => ['admin/settings'],
                ],
                [
                    'text' => 'Pengaturan SMTP',
                    'route' => 'admin.settings.smtp',
                    'icon' => 'fas fa-fw fa-envelope',
                    'can' => 'manage-settings',
                    'active' => ['admin/settings/smtp*'],
                ],
                [
                    'text' => 'Kesehatan Akademik',
                    'route' => 'admin.settings.academic-health',
                    'icon' => 'fas fa-fw fa-shield-alt',
                    'can' => 'manage-settings',
                    'active' => ['admin/settings/academic-health*'],
                ],
                [
                    'text' => 'Detail Server',
                    'route' => 'admin.settings.server-info',
                    'icon' => 'fas fa-fw fa-server',
                    'can' => 'manage-settings',
                    'active' => ['admin/settings/server-info*'],
                ],
                [
                    'text' => 'Log Email',
                    'route' => 'admin.email-logs.index',
                    'icon' => 'fas fa-fw fa-envelope-open-text',
                    'can' => 'manage-settings',
                    'active' => ['admin/email-logs*'],
                ],
                [
                    'text' => 'Template Email',
                    'route' => 'admin.email-templates.index',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'can' => 'manage-settings',
                    'active' => ['admin/email-templates*'],
                ],
                [
                    'text' => 'Activity Logs',
                    'route' => 'admin.activity-logs.index',
                    'icon' => 'fas fa-fw fa-history',
                    'can' => 'manage-settings',
                    'active' => ['admin/activity-logs*'],
                ],
                [
                    'text' => 'Custom Menu Siswa',
                    'route' => 'admin.custom-menu.index',
                    'icon' => 'fas fa-fw fa-th-list',
                    'can' => 'manage-settings',
                    'active' => ['admin/custom-menu*'],
                ],
                [
                    'text' => 'Referensi Kampus',
                    'route' => 'admin.referensi-perguruan-tinggi.index',
                    'icon' => 'fas fa-fw fa-university',
                    'can' => 'manage-settings',
                    'active' => ['admin/referensi-perguruan-tinggi*'],
                ],
                [
                    'text' => 'Referensi Prodi',
                    'route' => 'admin.referensi-program-studi.index',
                    'icon' => 'fas fa-fw fa-book-open',
                    'can' => 'manage-settings',
                    'active' => ['admin/referensi-program-studi*'],
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
        
        // USER & ROLE - Collapsible (dibawah Pengaturan)
        [
            'text' => 'User & Role',
            'icon' => 'fas fa-fw fa-users-cog',
            'can' => 'view-users',
            'submenu' => [
                [
                    'text' => 'Data User',
                    'route' => 'admin.users.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'can' => 'view-users',
                    'active' => ['admin/users*'],
                ],
                [
                    'text' => 'Permission Matrix',
                    'route' => 'admin.users.permission-matrix',
                    'icon' => 'fas fa-fw fa-th',
                    'can' => 'view-permission',
                    'active' => ['admin/permission-matrix*'],
                ],
                [
                    'text' => 'Role Management',
                    'route' => 'admin.roles.index',
                    'icon' => 'fas fa-fw fa-user-tag',
                    'can' => 'assign-roles',
                    'active' => ['admin/roles*'],
                ],
                [
                    'text' => 'Permission List',
                    'route' => 'admin.permissions.index',
                    'icon' => 'fas fa-fw fa-key',
                    'can' => 'assign-permissions',
                    'active' => ['admin/permissions*'],
                ],
            ],
        ],

        // CBT EXAM - Collapsible
        [
            'text' => 'CBT Exam',
            'icon' => 'fas fa-fw fa-laptop-code',
            'icon_color' => 'info',
            'can' => 'manage-settings',
            'submenu' => [
                [
                    'text' => 'Pengaturan Browser',
                    'route' => 'admin.exam-browser.index',
                    'icon' => 'fas fa-fw fa-desktop',
                    'icon_color' => 'info',
                    'can' => 'manage-settings',
                    'active' => ['admin/exam-browser*'],
                ],
                [
                    'text' => 'Notifikasi Exam',
                    'route' => 'admin.exam-notifications.index',
                    'icon' => 'fas fa-fw fa-bell',
                    'icon_color' => 'warning',
                    'can' => 'manage-settings',
                    'active' => ['admin/exam-notifications*'],
                ],
            ],
        ],

        // TOOLS - Collapsible
        [
            'text' => 'Tools',
            'icon' => 'fas fa-fw fa-tools',
            'can' => 'manage-settings',
            'submenu' => [
                [
                    'text' => 'Cek NIP',
                    'route' => 'admin.pengaturan.cek-nip.index',
                    'icon' => 'fas fa-fw fa-id-card',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/cek-nip*'],
                ],
                [
                    'text' => 'Cek NIK Dukcapil',
                    'route' => 'admin.pengaturan.cek-nik.index',
                    'icon' => 'fas fa-fw fa-fingerprint',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/cek-nik*'],
                ],
                [
                    'text' => 'Cek NISN Siswa',
                    'route' => 'admin.pengaturan.cek-nisn.index',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/cek-nisn*'],
                ],
                [
                    'text' => 'Update API Token',
                    'route' => 'admin.pengaturan.update-api-token.index',
                    'icon' => 'fas fa-fw fa-key',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/update-api-token*'],
                ],
                [
                    'text' => 'Reset Data Sistem',
                    'route' => 'admin.reset-system.index',
                    'icon' => 'fas fa-fw fa-exclamation-triangle',
                    'icon_color' => 'danger',
                    'can' => 'manage-settings',
                    'active' => ['admin/pengaturan/reset-system*'],
                ],
            ],
        ],

        // ============================================
        // SISWA MENU
        // Urutan: Dashboard, Profil & Data, Presensi, modul custom, Pengaturan Akun
        // ============================================
        [
            'key' => 'siswa-dashboard',
            'text' => 'Dashboard',
            'route' => 'siswa.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'sidebar-siswa-access',
        ],

        // PROFIL & DATA - Collapsible
        [
            'text' => 'Profil & Data',
            'icon' => 'fas fa-fw fa-user-circle',
            'can' => 'sidebar-siswa-access',
            'key' => 'siswa-profil-data',
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
                ],
            ],
        ],

        [
            'text' => 'Presensi',
            'icon' => 'fas fa-fw fa-fingerprint',
            'can' => 'sidebar-siswa-menu-only',
            'key' => 'siswa-presensi',
            'active' => ['siswa/face-register*'],
            'submenu' => [
                [
                    'key' => 'siswa-face-register',
                    'text' => 'Registrasi Wajah',
                    'route' => 'siswa.face-register',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'can' => 'sidebar-siswa-menu-only',
                    'active' => ['siswa/face-register*'],
                ],
            ],
        ],

        // MODUL CUSTOM
        [
            'key' => 'siswa-osis-election',
            'text' => 'Pemilihan OSIM',
            'route' => 'siswa.osis-election.index',
            'icon' => 'fas fa-fw fa-vote-yea',
            'can' => 'sidebar-siswa-access',
            'active' => ['siswa/pemilihan-osis*'],
        ],
        [
            'key' => 'siswa-active-polling',
            'text' => 'Polling Aktif',
            'route' => 'siswa.polling.index',
            'icon' => 'fas fa-fw fa-poll-h',
            'can' => 'sidebar-siswa-active-polling',
            'active' => ['siswa/polling*'],
        ],
        [
            'text' => 'Jalur PTN',
            'icon' => 'fas fa-fw fa-university',
            'can' => 'sidebar-siswa-access',
            'key' => 'siswa-jalur-ptn',
            'active' => ['siswa/snbp*', 'siswa/span-ptkin*'],
            'submenu' => [
                [
                    'text' => 'SNBP',
                    'route' => 'siswa.snbp.index',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                    'can' => 'sidebar-siswa-access',
                    'key' => 'siswa-snbp',
                    'active' => ['siswa/snbp*'],
                ],
                [
                    'text' => 'SPAN-PTKIN',
                    'route' => 'siswa.span-ptkin.index',
                    'icon' => 'fas fa-fw fa-mosque',
                    'can' => 'sidebar-siswa-access',
                    'key' => 'siswa-span-ptkin',
                    'active' => ['siswa/span-ptkin*'],
                ],
            ],
        ],
        [
            'text' => 'SMART-Q Unggulan',
            'route' => 'siswa.smartq.index',
            'icon' => 'fas fa-fw fa-star text-warning',
            'can' => 'sidebar-siswa-smartq',
            'key' => 'siswa-smartq',
            'active' => ['siswa/smartq*'],
        ],
        [
            'text' => 'Pengumuman Kelulusan',
            'route' => 'siswa.kelulusan-pengumuman.index',
            'icon' => 'fas fa-fw fa-envelope-open-text',
            'can' => 'sidebar-siswa-graduation',
            'key' => 'siswa-kelulusan-pengumuman',
            'active' => ['siswa/kelulusan-pengumuman*'],
        ],
        [
            'text' => 'Data Lulusan',
            'route' => 'siswa.lulusan.index',
            'icon' => 'fas fa-fw fa-university',
            'can' => 'sidebar-siswa-access',
            'key' => 'siswa-lulusan',
        ],

        // AKUN - Collapsible (selalu paling bawah)
        [
            'text' => 'Pengaturan Akun',
            'icon' => 'fas fa-fw fa-user-cog',
            'can' => 'sidebar-siswa-access',
            'key' => 'siswa-pengaturan-akun',
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
        App\Menu\Filters\SnbpMenuFilter::class,
        App\Menu\Filters\SpanPtkinMenuFilter::class,
        App\Menu\Filters\GraduationAnnouncementMenuFilter::class,
        App\Menu\Filters\LulusanMenuFilter::class,
        App\Menu\Filters\OsisElectionMenuFilter::class,
        App\Menu\Filters\GtkPersonalMenuFilter::class,
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
        'GoogleFontsInter' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
                ],
            ],
        ],
        'CustomCompact' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/custom-compact.css',
                    'version' => true,
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
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js',
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
