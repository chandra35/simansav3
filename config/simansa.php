<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pilihan Pekerjaan Orang Tua
    |--------------------------------------------------------------------------
    |
    | Daftar pilihan pekerjaan untuk data orang tua siswa
    |
    */
    'pekerjaan_ortu' => [
        'tidak_bekerja' => 'Tidak Bekerja',
        'pensiunan' => 'Pensiunan',
        'pns' => 'PNS (Pegawai Negeri Sipil)',
        'tni_polri' => 'TNI/Polisi',
        'guru_dosen' => 'Guru/Dosen',
        'pegawai_swasta' => 'Pegawai Swasta',
        'wiraswasta' => 'Wiraswasta (Pemilik/Pengelola Usaha)',
        'pengacara' => 'Pengacara/Jaksa/Hakim/Notaris',
        'seniman' => 'Seniman/Pelukis/Artis/Sejenis',
        'tenaga_kesehatan' => 'Dokter/Bidan/Perawat',
        'pilot_pramugara' => 'Pilot/Pramugara',
        'pedagang' => 'Pedagang',
        'petani_peternak' => 'Petani/Peternak',
        'nelayan' => 'Nelayan',
        'buruh' => 'Buruh (Tani/Pabrik/Bangunan)',
        'sopir' => 'Sopir/Masinis/Kondektur',
        'politikus' => 'Politikus',
        'lainnya' => 'Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pilihan Penghasilan Orang Tua
    |--------------------------------------------------------------------------
    |
    | Daftar range penghasilan untuk data orang tua siswa
    |
    */
    'penghasilan_ortu' => [
        'kurang_1jt' => 'Kurang dari Rp 1.000.000',
        '1jt_2jt' => 'Rp 1.000.000 - Rp 2.000.000',
        '2jt_3jt' => 'Rp 2.000.000 - Rp 3.000.000',
        '3jt_5jt' => 'Rp 3.000.000 - Rp 5.000.000',
        '5jt_10jt' => 'Rp 5.000.000 - Rp 10.000.000',
        'lebih_10jt' => 'Lebih dari Rp 10.000.000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dokumen Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi penyimpanan dokumen siswa
    | Path bisa dikonfigurasi via .env untuk flexibility
    |
    */
    'dokumen_storage' => [
        // Primary storage path - default ke folder dokumen-siswa di root project
        // Bisa diubah via .env: DOKUMEN_STORAGE_PATH=/path/custom
        'primary_path' => env('DOKUMEN_STORAGE_PATH', base_path('dokumen-siswa')),
        
        // Fallback storage path - jika primary tidak writable
        // Menggunakan Laravel storage default
        'fallback_path' => storage_path('app/private/dokumen-siswa'),
        
        // Auto create folder jika belum ada
        'auto_create' => env('DOKUMEN_AUTO_CREATE', true),
        
        // Check writable sebelum pakai
        'check_writable' => env('DOKUMEN_CHECK_WRITABLE', true),
        
        // Log jika pakai fallback
        'log_fallback' => env('DOKUMEN_LOG_FALLBACK', true),
    ],
];
