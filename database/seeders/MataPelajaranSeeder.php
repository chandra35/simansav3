<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kurikulum (assuming you have kurikulum already seeded)
        $k13 = Kurikulum::where('kode', 'K13')->first();
        $merdeka = Kurikulum::where('kode', 'MERDEKA')->first();

        if (!$k13) {
            $this->command->warn('Kurikulum K13 tidak ditemukan. Silakan jalankan KurikulumSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('Seeding Mata Pelajaran untuk Madrasah (Kemenag)...');

        // ========================================
        // RUMPUN PAI (Madrasah - Kurikulum 2013)
        // ========================================
        $rumpunPai = [
            [
                'kode_mapel' => 'QH',
                'nama_mapel' => 'Al-Quran Hadits',
                'sub_pai' => 'quran_hadits',
                'jam_pelajaran' => 2,
                'kelompok' => 'A',
            ],
            [
                'kode_mapel' => 'AA',
                'nama_mapel' => 'Akidah Akhlak',
                'sub_pai' => 'akidah_akhlak',
                'jam_pelajaran' => 2,
                'kelompok' => 'A',
            ],
            [
                'kode_mapel' => 'FIQ',
                'nama_mapel' => 'Fikih',
                'sub_pai' => 'fikih',
                'jam_pelajaran' => 2,
                'kelompok' => 'A',
            ],
            [
                'kode_mapel' => 'SKI',
                'nama_mapel' => 'Sejarah Kebudayaan Islam',
                'sub_pai' => 'ski',
                'jam_pelajaran' => 2,
                'kelompok' => 'A',
            ],
        ];

        foreach ($rumpunPai as $mapel) {
            MataPelajaran::updateOrCreate(
                [
                    'kode_mapel' => $mapel['kode_mapel'],
                    'kurikulum_id' => $k13->id,
                ],
                [
                    'nama_mapel' => $mapel['nama_mapel'],
                    'kelompok' => $mapel['kelompok'],
                    'kategori' => 'Wajib',
                    'is_mapel_agama' => true,
                    'jenis_agama' => 'islam',
                    'is_rumpun_pai' => true,
                    'sub_pai' => $mapel['sub_pai'],
                    'jam_pelajaran' => $mapel['jam_pelajaran'],
                    'tingkat' => [7, 8, 9, 10, 11, 12], // MTs & MA
                    'semester' => [1, 2],
                    'kkm' => 75,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Rumpun PAI berhasil di-seed (4 mapel)');

        // ========================================
        // BAHASA ARAB (Madrasah)
        // ========================================
        MataPelajaran::updateOrCreate(
            [
                'kode_mapel' => 'BA',
                'kurikulum_id' => $k13->id,
            ],
            [
                'nama_mapel' => 'Bahasa Arab',
                'kelompok' => 'A',
                'kategori' => 'Wajib',
                'is_bahasa_arab' => true,
                'jam_pelajaran' => 2,
                'tingkat' => [7, 8, 9, 10, 11, 12],
                'semester' => [1, 2],
                'kkm' => 75,
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Bahasa Arab berhasil di-seed');

        // ========================================
        // MAPEL UMUM KELOMPOK A (Wajib)
        // ========================================
        $kelompokA = [
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia', 'jam' => 6],
            ['kode' => 'MAT', 'nama' => 'Matematika', 'jam' => 5],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam', 'jam' => 5],
            ['kode' => 'IPS', 'nama' => 'Ilmu Pengetahuan Sosial', 'jam' => 4],
            ['kode' => 'BING', 'nama' => 'Bahasa Inggris', 'jam' => 4],
            ['kode' => 'PPKN', 'nama' => 'Pendidikan Pancasila dan Kewarganegaraan', 'jam' => 3],
        ];

        foreach ($kelompokA as $mapel) {
            MataPelajaran::updateOrCreate(
                [
                    'kode_mapel' => $mapel['kode'],
                    'kurikulum_id' => $k13->id,
                ],
                [
                    'nama_mapel' => $mapel['nama'],
                    'kelompok' => 'A',
                    'kategori' => 'Wajib',
                    'jam_pelajaran' => $mapel['jam'],
                    'tingkat' => [7, 8, 9, 10, 11, 12],
                    'semester' => [1, 2],
                    'kkm' => 75,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Kelompok A (Wajib) berhasil di-seed (6 mapel)');

        // ========================================
        // MAPEL KELOMPOK B (Wajib)
        // ========================================
        $kelompokB = [
            ['kode' => 'SENBUD', 'nama' => 'Seni Budaya', 'jam' => 3],
            ['kode' => 'PJOK', 'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'jam' => 3],
            ['kode' => 'PKWU', 'nama' => 'Prakarya dan Kewirausahaan', 'jam' => 2],
        ];

        foreach ($kelompokB as $mapel) {
            MataPelajaran::updateOrCreate(
                [
                    'kode_mapel' => $mapel['kode'],
                    'kurikulum_id' => $k13->id,
                ],
                [
                    'nama_mapel' => $mapel['nama'],
                    'kelompok' => 'B',
                    'kategori' => 'Wajib',
                    'jam_pelajaran' => $mapel['jam'],
                    'tingkat' => [7, 8, 9, 10, 11, 12],
                    'semester' => [1, 2],
                    'kkm' => 75,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Kelompok B (Wajib) berhasil di-seed (3 mapel)');

        // ========================================
        // MUATAN LOKAL (KTSP)
        // ========================================
        $mulok = [
            ['kode' => 'BJW', 'nama' => 'Bahasa Jawa', 'jam' => 2],
            ['kode' => 'KOMPUTER', 'nama' => 'Komputer dan Informatika', 'jam' => 2],
        ];

        foreach ($mulok as $mapel) {
            MataPelajaran::updateOrCreate(
                [
                    'kode_mapel' => $mapel['kode'],
                    'kurikulum_id' => $k13->id,
                ],
                [
                    'nama_mapel' => $mapel['nama'],
                    'kategori' => 'Muatan Lokal',
                    'is_muatan_lokal' => true,
                    'jam_pelajaran' => $mapel['jam'],
                    'tingkat' => [7, 8, 9],
                    'semester' => [1, 2],
                    'kkm' => 75,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Muatan Lokal berhasil di-seed (2 mapel)');

        // ========================================
        // KURIKULUM MERDEKA (if exists)
        // ========================================
        if ($merdeka) {
            $this->command->info('Seeding Mata Pelajaran untuk Kurikulum Merdeka...');

            $mapelMerdeka = [
                ['kode' => 'M-BIN', 'nama' => 'Bahasa Indonesia', 'jam' => 6, 'pilihan' => false],
                ['kode' => 'M-MAT', 'nama' => 'Matematika', 'jam' => 5, 'pilihan' => false],
                ['kode' => 'M-PAI', 'nama' => 'Pendidikan Agama Islam dan Budi Pekerti', 'jam' => 3, 'pilihan' => false],
                ['kode' => 'M-PPKN', 'nama' => 'Pendidikan Pancasila', 'jam' => 2, 'pilihan' => false],
                ['kode' => 'M-BING', 'nama' => 'Bahasa Inggris', 'jam' => 3, 'pilihan' => false],
                ['kode' => 'M-IPAS', 'nama' => 'Ilmu Pengetahuan Alam dan Sosial', 'jam' => 5, 'pilihan' => false],
                ['kode' => 'M-PJOK', 'nama' => 'Pendidikan Jasmani Olahraga dan Kesehatan', 'jam' => 2, 'pilihan' => false],
                ['kode' => 'M-SENBUD', 'nama' => 'Seni dan Budaya', 'jam' => 2, 'pilihan' => false],
                ['kode' => 'M-INF', 'nama' => 'Informatika', 'jam' => 2, 'pilihan' => true],
                ['kode' => 'M-P5', 'nama' => 'Projek Penguatan Profil Pelajar Pancasila', 'jam' => 2, 'pilihan' => false],
            ];

            foreach ($mapelMerdeka as $mapel) {
                MataPelajaran::updateOrCreate(
                    [
                        'kode_mapel' => $mapel['kode'],
                        'kurikulum_id' => $merdeka->id,
                    ],
                    [
                        'nama_mapel' => $mapel['nama'],
                        'kategori' => $mapel['pilihan'] ? 'Pilihan' : 'Wajib',
                        'is_mapel_pilihan' => $mapel['pilihan'],
                        'is_projek_p5' => ($mapel['kode'] == 'M-P5'),
                        'is_mapel_agama' => (str_contains($mapel['kode'], 'PAI')),
                        'jenis_agama' => (str_contains($mapel['kode'], 'PAI')) ? 'islam' : null,
                        'jam_pelajaran' => $mapel['jam'],
                        'tingkat' => [7, 8, 9],
                        'semester' => [1, 2],
                        'kkm' => 75,
                        'capaian_pembelajaran' => ($mapel['kode'] == 'M-P5') ? 'Peserta didik mampu mengembangkan profil pelajar Pancasila melalui projek berbasis permasalahan nyata di lingkungan sekitar.' : null,
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info('✓ Kurikulum Merdeka berhasil di-seed (10 mapel)');
        }

        $this->command->info('');
        $this->command->info('=====================================================');
        $this->command->info('SEEDING MATA PELAJARAN SELESAI');
        $this->command->info('Total: ' . MataPelajaran::count() . ' mata pelajaran');
        $this->command->info('=====================================================');
    }
}
