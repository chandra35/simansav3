<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migrasi data dari jadwal_jam_config (global, semua hari sama)
 * ke jadwal_hari_jam (per hari, fleksibel).
 *
 * Setiap baris jadwal_jam_config akan diduplikasi ke semua hari
 * (senin, selasa, rabu, kamis, jumat, sabtu) untuk semester 1 dan 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        $configs = DB::table('jadwal_jam_config')->get();

        if ($configs->isEmpty()) {
            return;
        }

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $semesterList = [1, 2];
        $now = now();

        $rows = [];
        foreach ($configs as $cfg) {
            foreach ($semesterList as $semester) {
                foreach ($hariList as $hari) {
                    $tipe = $cfg->is_istirahat ? 'istirahat' : 'pelajaran';
                    $rows[] = [
                        'id'                 => (string) Str::uuid(),
                        'tahun_pelajaran_id' => $cfg->tahun_pelajaran_id,
                        'semester'           => $semester,
                        'hari'               => $hari,
                        'urutan'             => $cfg->urutan,
                        'jam_ke'             => $cfg->is_istirahat ? null : $cfg->jam_ke,
                        'waktu_mulai'        => $cfg->waktu_mulai,
                        'waktu_selesai'      => $cfg->waktu_selesai,
                        'tipe'               => $tipe,
                        'label'              => $cfg->label,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ];
                }
            }
        }

        // Chunk insert to avoid large payload
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('jadwal_hari_jam')->insert($chunk);
        }
    }

    public function down(): void
    {
        // Hapus semua data yang di-migrasi (tidak bisa restore sempurna)
        DB::table('jadwal_hari_jam')->truncate();
    }
};
