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
    | Semester 1-2 (18 mapel):
    | QH | AA | FIK | SKI | BAR | PP | BINDO | MTK | IPAT | IPST | BING | PJOK | INFO | SB | MULOK PRKW | BLMP | KAG | THF
    |
    | Semester 3 (18 mapel):
    | QH | AA | FIK | SKI | BAR | PP | BINDO | MTK | IPAT | IPST | BING | PJOK | SEJ | INFO | SB | ULOK PRK | THF | KMPM FIS
    |
    | Semester 4 (20 mapel):
    | QH | AA | FIK | SKI | BAR | PP | BINDO | MTK | BING | PJOK | SEJ | SB | ULOK PRK | THF | BIO | KIM | FIS | INFOP | EKO | GEO
    |
    | Semester 5 (20 mapel):
    | QH | AA | FIK | SKI | BAR | PP | BINDO | MTK | BING | PJOK | SEJ | SB | ULOK PRK | THF | BIO | KIM | FIS | INFOP | MTL | EKO
    |
    */

    // Mapel untuk semester 1-2 (18 mapel)
    'urutan_mapel_sem_1_2' => [
        'QH',           // 1. Al-Quran Hadits
        'AA',           // 2. Akidah Akhlak
        'FIK',          // 3. Fikih
        'SKI',          // 4. Sejarah Kebudayaan Islam
        'BAR',          // 5. Bahasa Arab
        'PP',           // 6. Pendidikan Pancasila
        'BINDO',        // 7. Bahasa Indonesia
        'MTK',          // 8. Matematika
        'IPAT',         // 9. IPA Terpadu
        'IPST',         // 10. IPS Terpadu
        'BING',         // 11. Bahasa Inggris
        'PJOK',         // 12. Pendidikan Jasmani Olahraga dan Kesehatan
        'INFO',         // 13. Informatika
        'SB',           // 14. Seni Budaya
        'MULOK PRKW',   // 15. Muatan Lokal Prakarya
        'BLMP',         // 16. Bimbingan Lomba/Prestasi
        'KAG',          // 17. Keagamaan
        'THF',          // 18. Tahfidz
    ],

    // Mapel untuk semester 3 (18 mapel)
    'urutan_mapel_sem_3' => [
        'QH',           // 1. Al-Quran Hadits
        'AA',           // 2. Akidah Akhlak
        'FIK',          // 3. Fikih
        'SKI',          // 4. Sejarah Kebudayaan Islam
        'BAR',          // 5. Bahasa Arab
        'PP',           // 6. Pendidikan Pancasila
        'BINDO',        // 7. Bahasa Indonesia
        'MTK',          // 8. Matematika
        'IPAT',         // 9. IPA Terpadu
        'IPST',         // 10. IPS Terpadu
        'BING',         // 11. Bahasa Inggris
        'PJOK',         // 12. Pendidikan Jasmani Olahraga dan Kesehatan
        'SEJ',          // 13. Sejarah
        'INFO',         // 14. Informatika
        'SB',           // 15. Seni Budaya
        'ULOK PRK',     // 16. Muatan Lokal Prakarya
        'THF',          // 17. Tahfidz
        'KMPM FIS',     // 18. KMPM Fisika
    ],

    // Mapel untuk semester 4 (20 mapel)
    'urutan_mapel_sem_4' => [
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
        'ULOK PRK',     // 13. Muatan Lokal Prakarya
        'THF',          // 14. Tahfidz
        'BIO',          // 15. Biologi
        'KIM',          // 16. Kimia
        'FIS',          // 17. Fisika
        'INFOP',        // 18. Informatika Peminatan
        'EKO',          // 19. Ekonomi
        'GEO',          // 20. Geografi
    ],

    // Mapel untuk semester 5 (20 mapel) - ada tambahan MTL, tanpa GEO
    'urutan_mapel_sem_5' => [
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
        'ULOK PRK',     // 13. Muatan Lokal Prakarya
        'THF',          // 14. Tahfidz
        'BIO',          // 15. Biologi
        'KIM',          // 16. Kimia
        'FIS',          // 17. Fisika
        'INFOP',        // 18. Informatika Peminatan
        'MTL',          // 19. Matematika Lanjut
        'EKO',          // 20. Ekonomi
    ],

    // Helper: urutan_mapel untuk backward compatibility (semester 1-3)
    'urutan_mapel' => [
        'QH',           // 1. Al-Quran Hadits
        'AA',           // 2. Akidah Akhlak
        'FIK',          // 3. Fikih
        'SKI',          // 4. Sejarah Kebudayaan Islam
        'BAR',          // 5. Bahasa Arab
        'PP',           // 6. Pendidikan Pancasila
        'BINDO',        // 7. Bahasa Indonesia
        'MTK',          // 8. Matematika
        'IPAT',         // 9. IPA Terpadu
        'IPST',         // 10. IPS Terpadu
        'BING',         // 11. Bahasa Inggris
        'PJOK',         // 12. Pendidikan Jasmani Olahraga dan Kesehatan
        'INFO',         // 13. Informatika
        'SB',           // 14. Seni Budaya
        'MULOK PRKW',   // 15. Muatan Lokal Prakarya
        'BLMP',         // 16. Bimbingan Lomba/Prestasi
        'KAG',          // 17. Keagamaan
        'THF',          // 18. Tahfidz
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
