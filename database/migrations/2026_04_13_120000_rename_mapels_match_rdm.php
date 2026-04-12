<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rename SIMANSA mapels to match RDM naming convention.
 * Safe operation: UUID (primary key) tidak berubah, semua relasi nilai_siswa tetap utuh.
 *
 * Changes:
 * 1. Rename K13 mapel names to match RDM K13
 * 2. Rename Merdeka mapel names to match RDM Merdeka
 * 3. Move Geografi from KTSP → K13 (correct kurikulum)
 * 4. Move KTSP custom mapels to K13 (since kelas 12 uses K13)
 */
return new class extends Migration
{
    public function up(): void
    {
        $k13 = DB::table('kurikulum')->where('kode', 'K13')->first();
        $ktsp = DB::table('kurikulum')->where('kode', 'KTSP')->first();

        // ── 1. Rename K13 mapels ──
        $k13Renames = [
            'QH'   => 'Al Qur\'an Hadis',         // was: Al-Quran Hadits
            'PP'   => 'Pendidikan Pancasila dan Kewarganegaraan', // was: Pendidikan Pancasila
            'PJOK' => 'Pendidikan Jasmani, Olahraga dan Kesehatan', // was: ..., Olahraga, dan...
        ];

        foreach ($k13Renames as $kode => $newName) {
            DB::table('mata_pelajaran')
                ->where('kode_mapel', $kode)
                ->where('kurikulum_id', $k13->id)
                ->update(['nama_mapel' => $newName, 'updated_at' => now()]);
        }

        // ── 2. Rename Merdeka mapels ──
        $merdekaRenames = [
            'M-QH'     => 'Al Qur\'an Hadis',       // was: Al-Quran Hadits (PAI)
            'M-PJOK'   => 'Pendidikan Jasmani, Olahraga dan Kesehatan', // SMP (missing comma)
            'M-PJOK-F' => 'Pendidikan Jasmani, Olahraga dan Kesehatan', // SMA Fase F
            'M-PP-F'   => 'Pendidikan Pancasila',    // confirm match RDM Merdeka (not + Kewarganegaraan)
        ];

        $merdeka = DB::table('kurikulum')->where('kode', 'MERDEKA')->first();
        foreach ($merdekaRenames as $kode => $newName) {
            DB::table('mata_pelajaran')
                ->where('kode_mapel', $kode)
                ->where('kurikulum_id', $merdeka->id)
                ->update(['nama_mapel' => $newName, 'updated_at' => now()]);
        }

        // ── 3. Move Geografi from KTSP → K13 (it's a standard K13 mapel) ──
        if ($ktsp && $k13) {
            DB::table('mata_pelajaran')
                ->where('kode_mapel', 'GEO')
                ->where('kurikulum_id', $ktsp->id)
                ->update([
                    'kurikulum_id' => $k13->id,
                    'kelompok' => 'C',
                    'tingkat' => json_encode([10, 11, 12]),
                    'updated_at' => now(),
                ]);
        }

        // ── 4. Move other KTSP custom mapels to K13 ──
        // These are school-specific mapels that were put in KTSP as workaround
        // Moving to K13 so they're under the same kurikulum as other kelas 12 mapels
        if ($ktsp && $k13) {
            $ktspToK13 = ['KAG', 'INFO', 'BLMP', 'IPAT', 'IPST', 'ULOK PRK', 'NFOI'];
            DB::table('mata_pelajaran')
                ->whereIn('kode_mapel', $ktspToK13)
                ->where('kurikulum_id', $ktsp->id)
                ->update([
                    'kurikulum_id' => $k13->id,
                    'tingkat' => json_encode([10, 11, 12]),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $k13 = DB::table('kurikulum')->where('kode', 'K13')->first();
        $ktsp = DB::table('kurikulum')->where('kode', 'KTSP')->first();
        $merdeka = DB::table('kurikulum')->where('kode', 'MERDEKA')->first();

        // Revert K13 renames
        $k13Reverts = [
            'QH'   => 'Al-Quran Hadits',
            'PP'   => 'Pendidikan Pancasila',
            'PJOK' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        ];
        foreach ($k13Reverts as $kode => $oldName) {
            DB::table('mata_pelajaran')
                ->where('kode_mapel', $kode)
                ->where('kurikulum_id', $k13->id)
                ->update(['nama_mapel' => $oldName]);
        }

        // Revert Merdeka renames
        $merdekaReverts = [
            'M-QH'     => 'Al-Quran Hadits',
            'M-PJOK'   => 'Pendidikan Jasmani Olahraga dan Kesehatan',
            'M-PJOK-F' => 'Pendidikan Jasmani Olahraga dan Kesehatan',
            'M-PP-F'   => 'Pendidikan Pancasila',
        ];
        foreach ($merdekaReverts as $kode => $oldName) {
            DB::table('mata_pelajaran')
                ->where('kode_mapel', $kode)
                ->where('kurikulum_id', $merdeka->id)
                ->update(['nama_mapel' => $oldName]);
        }

        // Move mapels back to KTSP
        if ($ktsp && $k13) {
            $backToKtsp = ['GEO', 'KAG', 'INFO', 'BLMP', 'IPAT', 'IPST', 'ULOK PRK', 'NFOI'];
            DB::table('mata_pelajaran')
                ->whereIn('kode_mapel', $backToKtsp)
                ->where('kurikulum_id', $k13->id)
                ->update(['kurikulum_id' => $ktsp->id]);
        }
    }
};
