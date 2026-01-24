<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kurikulum;
use App\Models\TahunPelajaran;

class TahunPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kurikulum IDs
        $k13 = Kurikulum::where('kode', 'K13')->first();
        $merdeka = Kurikulum::where('kode', 'MERDEKA')->first();

        // Tahun Pelajaran yang sudah selesai
        TahunPelajaran::create([
            'kurikulum_id' => $k13->id,
            'nama' => '2022/2023',
            'tahun_mulai' => 2022,
            'tahun_selesai' => 2023,
            'semester_aktif' => 'Ganjil',
            'tanggal_mulai' => '2022-07-11',
            'tanggal_selesai' => '2023-06-30',
            'is_active' => false,
            'status' => 'selesai',
            'kuota_ppdb' => 360,
            'keterangan' => 'Tahun pelajaran 2022/2023 dengan Kurikulum 2013',
        ]);
        
        TahunPelajaran::create([
            'kurikulum_id' => $k13->id,
            'nama' => '2023/2024',
            'tahun_mulai' => 2023,
            'tahun_selesai' => 2024,
            'semester_aktif' => 'Ganjil',
            'tanggal_mulai' => '2023-07-17',
            'tanggal_selesai' => '2024-06-29',
            'is_active' => false,
            'status' => 'selesai',
            'kuota_ppdb' => 360,
            'keterangan' => 'Tahun pelajaran 2023/2024 dengan Kurikulum 2013',
        ]);

        // Tahun Pelajaran Aktif Saat Ini (2024/2025 - Kurikulum Merdeka)
        TahunPelajaran::create([
            'kurikulum_id' => $merdeka->id,
            'nama' => '2024/2025',
            'tahun_mulai' => 2024,
            'tahun_selesai' => 2025,
            'semester_aktif' => 'Ganjil',
            'tanggal_mulai' => '2024-07-15',
            'tanggal_selesai' => '2025-06-28',
            'is_active' => true, // Tahun aktif saat ini
            'status' => 'aktif',
            'kuota_ppdb' => 400,
            'keterangan' => 'Tahun pelajaran 2024/2025 - Implementasi Kurikulum Merdeka',
        ]);

        // Tahun Pelajaran Masa Depan (untuk planning)
        TahunPelajaran::create([
            'kurikulum_id' => $merdeka->id,
            'nama' => '2025/2026',
            'tahun_mulai' => 2025,
            'tahun_selesai' => 2026,
            'semester_aktif' => 'Ganjil',
            'tanggal_mulai' => '2025-07-14',
            'tanggal_selesai' => '2026-06-27',
            'is_active' => false,
            'status' => 'non-aktif',
            'kuota_ppdb' => 400,
            'keterangan' => 'Tahun pelajaran 2025/2026 - Persiapan',
        ]);

        $this->command->info('✅ Seeder Tahun Pelajaran berhasil!');
        $this->command->info('   - 2022/2023: Selesai (K13)');
        $this->command->info('   - 2023/2024: Selesai (K13)');
        $this->command->info('   - 2024/2025: AKTIF (Kurikulum Merdeka) ⭐');
        $this->command->info('   - 2025/2026: Non-aktif (Planning)');
    }
}
