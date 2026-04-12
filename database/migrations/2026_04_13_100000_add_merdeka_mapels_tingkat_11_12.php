<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Step 1: Extend existing Merdeka fase E mapels to include tingkat 11,12
 * Step 2: Add missing Merdeka mapels for MA (Ekonomi, Geografi, Sosiologi, Sejarah, Informatika, etc.)
 * Step 3: Add kurikulum_id to rdm_mapel_mappings for kurikulum-aware mapping
 */
return new class extends Migration
{
    public function up(): void
    {
        // Lookup references
        $merdeka = DB::table('kurikulum')->where('kode', 'MERDEKA')->first();
        $jurusanUmum = DB::table('jurusan')
            ->where('kurikulum_id', $merdeka->id)
            ->where('kode_jurusan', 'UMUM')
            ->first();
        $tahunAktif = DB::table('tahun_pelajaran')
            ->where('kurikulum_id', $merdeka->id)
            ->where('is_active', true)
            ->first();

        // ── Step 1: Update existing Merdeka SMA mapels (fase E) to include 11,12 ──
        // Currently these only have ["10"], update to [10,11,12]
        $faseEMapels = DB::table('mata_pelajaran')
            ->where('kurikulum_id', $merdeka->id)
            ->whereNotNull('jurusan_id')
            ->where('is_active', true)
            ->get();

        foreach ($faseEMapels as $mp) {
            $tingkat = json_decode($mp->tingkat, true) ?? [];
            // Normalize: ensure [10,11,12]
            $newTingkat = array_values(array_unique(array_merge(
                array_map('intval', $tingkat),
                [10, 11, 12]
            )));
            sort($newTingkat);

            DB::table('mata_pelajaran')
                ->where('id', $mp->id)
                ->update([
                    'tingkat' => json_encode($newTingkat),
                    'updated_at' => now(),
                ]);
        }

        // ── Step 2: Add missing Merdeka mapels ──
        $newMapels = [
            // Kelompok A - Wajib (yang belum ada)
            [
                'kode_mapel' => 'M-SJR-F',
                'nama_mapel' => 'Sejarah',
                'kelompok' => 'A',
                'kategori' => 'Wajib',
            ],
            [
                'kode_mapel' => 'M-SENBUD-F',
                'nama_mapel' => 'Seni dan Budaya',
                'kelompok' => 'A',
                'kategori' => 'Wajib',
            ],
            [
                'kode_mapel' => 'M-PKW-F',
                'nama_mapel' => 'Prakarya dan Kewirausahaan',
                'kelompok' => 'A',
                'kategori' => 'Wajib',
            ],
            // Kelompok B - Pilihan MIPA/IPS
            [
                'kode_mapel' => 'M-EKO',
                'nama_mapel' => 'Ekonomi',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-GEO',
                'nama_mapel' => 'Geografi',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-SOS',
                'nama_mapel' => 'Sosiologi',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-INF-F',
                'nama_mapel' => 'Informatika',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-MATL',
                'nama_mapel' => 'Matematika Tingkat Lanjut',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-BINGL',
                'nama_mapel' => 'Bahasa Inggris Tingkat Lanjut',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            // Muatan lokal
            [
                'kode_mapel' => 'M-BLMP',
                'nama_mapel' => 'Bahasa Lampung',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_muatan_lokal' => true,
            ],
            [
                'kode_mapel' => 'M-TAHFZ',
                'nama_mapel' => 'Tahfidz',
                'kelompok' => 'A',
                'kategori' => 'Wajib',
                'is_mapel_agama' => true,
                'jenis_agama' => 'islam',
            ],
            [
                'kode_mapel' => 'M-TIK',
                'nama_mapel' => 'Teknologi Informasi dan Komunikasi',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-KSN',
                'nama_mapel' => 'Kesenian',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-KTAGM',
                'nama_mapel' => 'Keterampilan Agama',
                'kelompok' => 'PAI',
                'kategori' => 'Wajib',
                'is_mapel_agama' => true,
                'jenis_agama' => 'islam',
            ],
        ];

        foreach ($newMapels as $mapel) {
            // Skip if kode_mapel already exists
            if (DB::table('mata_pelajaran')->where('kode_mapel', $mapel['kode_mapel'])->exists()) {
                continue;
            }

            DB::table('mata_pelajaran')->insert([
                'id' => (string) Str::uuid(),
                'kurikulum_id' => $merdeka->id,
                'tahun_pelajaran_id' => $tahunAktif?->id,
                'jurusan_id' => $jurusanUmum?->id,
                'kode_mapel' => $mapel['kode_mapel'],
                'nama_mapel' => $mapel['nama_mapel'],
                'kelompok' => $mapel['kelompok'],
                'kategori' => $mapel['kategori'],
                'kkm' => null, // Merdeka tidak pakai KKM
                'is_mapel_agama' => $mapel['is_mapel_agama'] ?? false,
                'jenis_agama' => $mapel['jenis_agama'] ?? null,
                'is_rumpun_pai' => false,
                'is_bahasa_arab' => false,
                'is_mapel_pilihan' => $mapel['is_mapel_pilihan'] ?? false,
                'is_projek_p5' => false,
                'is_muatan_lokal' => $mapel['is_muatan_lokal'] ?? false,
                'jam_pelajaran' => null,
                'tingkat' => json_encode([10, 11, 12]),
                'semester' => json_encode([1, 2]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Step 3: Add kurikulum_id to rdm_mapel_mappings ──
        if (!\Illuminate\Support\Facades\Schema::hasColumn('rdm_mapel_mappings', 'rdm_kurikulum_id')) {
            \Illuminate\Support\Facades\Schema::table('rdm_mapel_mappings', function ($table) {
                $table->unsignedInteger('rdm_kurikulum_id')->nullable()->after('rdm_mapel_nama')
                    ->comment('RDM kurikulum_id: 1=K13, 2=Merdeka');
            });
        }
    }

    public function down(): void
    {
        // Remove added column
        if (\Illuminate\Support\Facades\Schema::hasColumn('rdm_mapel_mappings', 'rdm_kurikulum_id')) {
            \Illuminate\Support\Facades\Schema::table('rdm_mapel_mappings', function ($table) {
                $table->dropColumn('rdm_kurikulum_id');
            });
        }

        // Remove added mapels by kode
        $kodes = ['M-SJR-F','M-SENBUD-F','M-PKW-F','M-EKO','M-GEO','M-SOS','M-INF-F','M-MATL','M-BINGL','M-BLMP','M-TAHFZ','M-TIK','M-KSN','M-KTAGM'];
        DB::table('mata_pelajaran')->whereIn('kode_mapel', $kodes)->delete();

        // Revert tingkat of fase E mapels back to ["10"]
        $merdeka = DB::table('kurikulum')->where('kode', 'MERDEKA')->first();
        if ($merdeka) {
            DB::table('mata_pelajaran')
                ->where('kurikulum_id', $merdeka->id)
                ->whereNotNull('jurusan_id')
                ->where('is_active', true)
                ->whereNotIn('kode_mapel', $kodes)
                ->update(['tingkat' => json_encode(["10"])]);
        }
    }
};
