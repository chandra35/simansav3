<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Urutan Mapel untuk Upload Nilai SPAN-PTKIN
    |--------------------------------------------------------------------------
    |
    | Daftar kode mapel dengan urutan yang fixed untuk import nilai dari Excel.
    | Urutan ini digunakan untuk menentukan kolom mana berisi nilai mapel apa.
    |
    | Format Excel yang diharapkan:
    | No | NIS | NISN | Nama | JK | QH | AA | FIK | SKI | BAR | PP | BINDO | MTK | BING | PJOK | SEJ | SB | PRKW MULOK | THF | BIO | KIM | FIS | INFOP | MTL | EKO
    |
    */

    'urutan_mapel' => [
        'QH',           // 1. Al-Quran Hadits
        'AA',           // 2. Akidah Akhlak
        'FIK',          // 3. Fikih
        'SKI',          // 4. Sejarah Kebudayaan Islam
        'BAR',          // 5. Bahasa Arab
        'PP',           // 6. Pendidikan Pancasila
        'BINDO',        // 7. Bahasa Indonesia
        'MTK',          // 8. Matematika
        'BING',         // 9. Bahasa Inggris
        'PJOK',         // 10. Pendidikan Jasmani Olahraga dan Kesehatan
        'SEJ',          // 11. Sejarah
        'SB',           // 12. Seni Budaya
        'MULOK PRKW',   // 13. Muatan Lokal Prakarya
        'THF',          // 14. Tahfidz
        'BIO',          // 15. Biologi
        'KIM',          // 16. Kimia
        'FIS',          // 17. Fisika
        'INFOP',        // 18. Informatika Peminatan
        'MTL',          // 19. Matematika Lanjut
        'EKO',          // 20. Ekonomi
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom Non-Nilai
    |--------------------------------------------------------------------------
    |
    | Kolom yang ada sebelum kolom nilai (urutan 0-indexed):
    | 0: No
    | 1: NIS
    | 2: NISN (untuk matching siswa)
    | 3: Nama
    | 4: JK (Jenis Kelamin)
    |
    | Jadi kolom nilai dimulai dari index 5
    |
    */

    'kolom_nisn' => 2,          // Index kolom NISN (0-indexed)
    'kolom_nilai_mulai' => 5,   // Index kolom pertama yang berisi nilai

    /*
    |--------------------------------------------------------------------------
    | Baris Data Mulai
    |--------------------------------------------------------------------------
    |
    | Baris ke berapa data siswa dimulai (1-indexed seperti Excel)
    | Baris 1: Header (No, NIS, NISN, Nama, JK, QH, AA, ...)
    | Baris 2+: Data siswa
    |
    */

    'baris_header' => 1,        // Baris header (1-indexed)
    'baris_data_mulai' => 2,    // Baris data pertama (1-indexed)
];
