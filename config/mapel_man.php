<?php

/*
|--------------------------------------------------------------------------
| Template Mata Pelajaran MAN
|--------------------------------------------------------------------------
|
| Struktur minimum MA Kurikulum Merdeka berdasarkan KMA 1503 Tahun 2025.
| "kelompok" tetap disimpan sebagai kode kompatibilitas lama/RDM, sedangkan
| struktur resmi disimpan per fase pada struktur_fase_e/struktur_fase_f.
|
*/

return [
    'pai_bahasa_arab' => [
        'label' => 'PAI & Bahasa Arab Madrasah',
        'description' => 'Mapel wajib/umum berciri khas madrasah, bukan kelompok kurikulum terpisah.',
        'mapel' => [
            ['kode_mapel' => 'M-QH', 'nama_mapel' => "Al-Qur'an Hadis", 'kelompok' => 'PAI', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'pai', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_mapel_agama' => true, 'jenis_agama' => 'islam', 'is_rumpun_pai' => true, 'sub_pai' => 'quran_hadits'],
            ['kode_mapel' => 'M-AA', 'nama_mapel' => 'Akidah Akhlak', 'kelompok' => 'PAI', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'pai', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_mapel_agama' => true, 'jenis_agama' => 'islam', 'is_rumpun_pai' => true, 'sub_pai' => 'akidah_akhlak'],
            ['kode_mapel' => 'M-FIQ', 'nama_mapel' => 'Fikih', 'kelompok' => 'PAI', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'pai', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_mapel_agama' => true, 'jenis_agama' => 'islam', 'is_rumpun_pai' => true, 'sub_pai' => 'fikih'],
            ['kode_mapel' => 'M-SKI', 'nama_mapel' => 'Sejarah Kebudayaan Islam', 'kelompok' => 'PAI', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'pai', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_mapel_agama' => true, 'jenis_agama' => 'islam', 'is_rumpun_pai' => true, 'sub_pai' => 'ski'],
            ['kode_mapel' => 'M-BA', 'nama_mapel' => 'Bahasa Arab', 'kelompok' => 'PAI', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'bahasa', 'alokasi_jp' => ['10' => 4, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_bahasa_arab' => true],
        ],
    ],

    'wajib_umum' => [
        'label' => 'Mata Pelajaran Wajib / Umum',
        'description' => 'Wajib pada Fase E dan menjadi kelompok umum pada Fase F.',
        'mapel' => [
            ['kode_mapel' => 'M-PP-F', 'nama_mapel' => 'Pendidikan Pancasila', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'umum', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2],
            ['kode_mapel' => 'M-BIN-F', 'nama_mapel' => 'Bahasa Indonesia', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'bahasa', 'alokasi_jp' => ['10' => 3, '11' => 3, '12' => 3], 'jam_pelajaran' => 3],
            ['kode_mapel' => 'M-MAT-F', 'nama_mapel' => 'Matematika', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'mipa', 'alokasi_jp' => ['10' => 3, '11' => 3, '12' => 3], 'jam_pelajaran' => 3],
            ['kode_mapel' => 'M-BING-F', 'nama_mapel' => 'Bahasa Inggris', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'bahasa', 'alokasi_jp' => ['10' => 3, '11' => 3, '12' => 3], 'jam_pelajaran' => 3],
            ['kode_mapel' => 'M-PJOK-F', 'nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'pjok', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2],
            ['kode_mapel' => 'M-SENBUD-F', 'nama_mapel' => 'Seni dan Budaya', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'seni_prakarya', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2],
            ['kode_mapel' => 'M-SJR-F', 'nama_mapel' => 'Sejarah', 'kelompok' => 'A', 'kategori' => 'Wajib', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'wajib_umum', 'rumpun' => 'ips', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2],
        ],
    ],

    'ipa_ips_fase_e' => [
        'label' => 'Muatan IPA & IPS Fase E / Pilihan Fase F',
        'description' => 'Kelas X dapat dijadwalkan paralel per disiplin; kelas XI–XII menjadi mapel pilihan.',
        'mapel' => [
            ['kode_mapel' => 'M-FIS', 'nama_mapel' => 'Fisika', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'mipa', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-KIM', 'nama_mapel' => 'Kimia', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'mipa', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-BIO', 'nama_mapel' => 'Biologi', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'mipa', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-EKO', 'nama_mapel' => 'Ekonomi', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'ips', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-SOS', 'nama_mapel' => 'Sosiologi', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'ips', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-GEO', 'nama_mapel' => 'Geografi', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'ips', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-INF-F', 'nama_mapel' => 'Informatika', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'wajib_umum', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'teknologi', 'alokasi_jp' => ['10' => 2, '11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
        ],
    ],

    'pilihan_fase_f' => [
        'label' => 'Mata Pelajaran Pilihan Fase F',
        'description' => 'Madrasah menyediakan sedikitnya 7 pilihan; peserta didik memilih 4–5 mapel.',
        'mapel' => [
            ['kode_mapel' => 'M-MATL', 'nama_mapel' => 'Matematika Tingkat Lanjut', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'mipa', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-BINGL', 'nama_mapel' => 'Bahasa Inggris Tingkat Lanjut', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'bahasa', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-BINL', 'nama_mapel' => 'Bahasa Indonesia Tingkat Lanjut', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'bahasa', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-ANTRO', 'nama_mapel' => 'Antropologi', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'ips', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-TAF', 'nama_mapel' => 'Ilmu Tafsir', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'pai', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true, 'is_mapel_agama' => true, 'jenis_agama' => 'islam'],
            ['kode_mapel' => 'M-HAD', 'nama_mapel' => 'Ilmu Hadis', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'pai', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true, 'is_mapel_agama' => true, 'jenis_agama' => 'islam'],
            ['kode_mapel' => 'M-UFI', 'nama_mapel' => 'Ushul Fikih', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'pai', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true, 'is_mapel_agama' => true, 'jenis_agama' => 'islam'],
            ['kode_mapel' => 'M-BAL', 'nama_mapel' => 'Bahasa Arab Tingkat Lanjut', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => null, 'struktur_fase_f' => 'pilihan', 'rumpun' => 'bahasa', 'alokasi_jp' => ['11' => 5, '12' => 5], 'jam_pelajaran' => 5, 'is_mapel_pilihan' => true, 'is_bahasa_arab' => true],
        ],
    ],

    'pilihan_lain' => [
        'label' => 'Pilihan Teknologi & Program Madrasah',
        'description' => 'Dibuka sesuai sumber daya dan/atau SK program resmi madrasah.',
        'mapel' => [
            ['kode_mapel' => 'M-KKA', 'nama_mapel' => 'Koding dan Kecerdasan Artifisial', 'kelompok' => 'B', 'kategori' => 'Pilihan', 'struktur_fase_e' => 'pilihan', 'struktur_fase_f' => 'pilihan', 'rumpun' => 'teknologi', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_mapel_pilihan' => true],
            ['kode_mapel' => 'M-RIS', 'nama_mapel' => 'Riset', 'kelompok' => 'B', 'kategori' => 'Penguatan Program', 'struktur_fase_e' => 'penguatan_program', 'struktur_fase_f' => 'penguatan_program', 'rumpun' => 'teknologi', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2],
        ],
    ],

    'muatan_lokal' => [
        'label' => 'Muatan Lokal',
        'description' => 'Dipilih sesuai kekhasan madrasah dan potensi Lampung.',
        'mapel' => [
            ['kode_mapel' => 'M-BLMP', 'nama_mapel' => 'Bahasa Lampung', 'kelompok' => 'Muatan Lokal', 'kategori' => 'Muatan Lokal', 'struktur_fase_e' => 'muatan_lokal', 'struktur_fase_f' => 'muatan_lokal', 'rumpun' => 'bahasa', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_muatan_lokal' => true],
            ['kode_mapel' => 'M-TAHFZ', 'nama_mapel' => 'Tahfiz', 'kelompok' => 'Muatan Lokal', 'kategori' => 'Muatan Lokal', 'struktur_fase_e' => 'muatan_lokal', 'struktur_fase_f' => 'muatan_lokal', 'rumpun' => 'pai', 'alokasi_jp' => ['10' => 2, '11' => 2, '12' => 2], 'jam_pelajaran' => 2, 'is_muatan_lokal' => true, 'is_mapel_agama' => true, 'jenis_agama' => 'islam'],
        ],
    ],
];
