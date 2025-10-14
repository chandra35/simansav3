<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Kurikulum;
use App\Models\Jurusan;

class KurikulumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kurikulum KTSP
        $ktsp = Kurikulum::create([
            'kode' => 'KTSP',
            'nama_kurikulum' => 'Kurikulum Tingkat Satuan Pendidikan',
            'deskripsi' => 'KTSP adalah kurikulum operasional yang disusun oleh dan dilaksanakan di masing-masing satuan pendidikan.',
            'tahun_berlaku' => 2006,
            'has_jurusan' => true,
            'is_active' => false, // Sudah tidak aktif
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jurusan untuk KTSP
        Jurusan::create([
            'kurikulum_id' => $ktsp->id,
            'kode_jurusan' => 'IPA',
            'nama_jurusan' => 'Ilmu Pengetahuan Alam',
            'singkatan' => 'IPA',
            'deskripsi' => 'Program studi yang mempelajari ilmu-ilmu alam seperti Matematika, Fisika, Kimia, dan Biologi.',
            'urutan' => 1,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $ktsp->id,
            'kode_jurusan' => 'IPS',
            'nama_jurusan' => 'Ilmu Pengetahuan Sosial',
            'singkatan' => 'IPS',
            'deskripsi' => 'Program studi yang mempelajari ilmu-ilmu sosial seperti Ekonomi, Sosiologi, Geografi, dan Sejarah.',
            'urutan' => 2,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $ktsp->id,
            'kode_jurusan' => 'BAHASA',
            'nama_jurusan' => 'Bahasa dan Budaya',
            'singkatan' => 'Bahasa',
            'deskripsi' => 'Program studi yang mempelajari bahasa dan budaya, termasuk Bahasa Indonesia, Bahasa Inggris, dan bahasa asing lainnya.',
            'urutan' => 3,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $ktsp->id,
            'kode_jurusan' => 'KEAGAMAAN',
            'nama_jurusan' => 'Keagamaan',
            'singkatan' => 'Agama',
            'deskripsi' => 'Program studi yang fokus pada ilmu-ilmu keagamaan Islam.',
            'urutan' => 4,
            'is_active' => true,
        ]);

        // Kurikulum 2013 (K13)
        $k13 = Kurikulum::create([
            'kode' => 'K13',
            'nama_kurikulum' => 'Kurikulum 2013',
            'deskripsi' => 'Kurikulum 2013 adalah kurikulum yang mengutamakan pada pemahaman, skill, dan pendidikan karakter.',
            'tahun_berlaku' => 2013,
            'has_jurusan' => true,
            'is_active' => true, // Masih digunakan
        ]);

        // Jurusan untuk K13 (Peminatan)
        Jurusan::create([
            'kurikulum_id' => $k13->id,
            'kode_jurusan' => 'IPA',
            'nama_jurusan' => 'Matematika dan Ilmu Pengetahuan Alam',
            'singkatan' => 'MIPA',
            'deskripsi' => 'Peminatan yang mempelajari Matematika, Fisika, Kimia, dan Biologi dengan pendekatan saintifik.',
            'urutan' => 1,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $k13->id,
            'kode_jurusan' => 'IPS',
            'nama_jurusan' => 'Ilmu Pengetahuan Sosial',
            'singkatan' => 'IPS',
            'deskripsi' => 'Peminatan yang mempelajari Ekonomi, Sosiologi, Geografi, dan Sejarah dengan pendekatan kontekstual.',
            'urutan' => 2,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $k13->id,
            'kode_jurusan' => 'BAHASA',
            'nama_jurusan' => 'Bahasa dan Budaya',
            'singkatan' => 'Bahasa',
            'deskripsi' => 'Peminatan yang fokus pada pengembangan kemampuan berbahasa dan apresiasi budaya.',
            'urutan' => 3,
            'is_active' => true,
        ]);
        
        Jurusan::create([
            'kurikulum_id' => $k13->id,
            'kode_jurusan' => 'KEAGAMAAN',
            'nama_jurusan' => 'Ilmu-Ilmu Keagamaan',
            'singkatan' => 'Keagamaan',
            'deskripsi' => 'Peminatan yang memperdalam ilmu-ilmu keagamaan Islam dan studi keislaman.',
            'urutan' => 4,
            'is_active' => true,
        ]);

        // Kurikulum Merdeka
        $merdeka = Kurikulum::create([
            'kode' => 'MERDEKA',
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'deskripsi' => 'Kurikulum Merdeka memberikan kebebasan kepada siswa untuk memilih mata pelajaran sesuai minat dan bakatnya.',
            'tahun_berlaku' => 2022,
            'has_jurusan' => false, // Kurikulum Merdeka tidak ada penjurusan IPA/IPS
            'is_active' => true,
        ]);

        // Jurusan untuk Merdeka (Hanya Umum)
        Jurusan::create([
            'kurikulum_id' => $merdeka->id,
            'kode_jurusan' => 'UMUM',
            'nama_jurusan' => 'Program Umum',
            'singkatan' => 'Umum',
            'deskripsi' => 'Kurikulum Merdeka tidak memiliki penjurusan IPA/IPS, siswa memilih mata pelajaran sesuai minat.',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->command->info('✅ Seeder Kurikulum & Jurusan berhasil!');
        $this->command->info('   - KTSP: 4 jurusan (IPA, IPS, Bahasa, Keagamaan)');
        $this->command->info('   - K13: 4 peminatan (MIPA, IPS, Bahasa, Keagamaan)');
        $this->command->info('   - Merdeka: 1 program (Umum)');
    }
}
