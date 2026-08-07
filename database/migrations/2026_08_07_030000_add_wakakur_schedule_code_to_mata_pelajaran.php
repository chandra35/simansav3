<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            // Kode A-Z dari format jadwal Wakakur. Kode internal tetap dipakai
            // untuk nilai/RDM agar integrasi historis tidak berubah.
            $table->string('kode_jadwal', 20)->nullable()->after('kode_mapel');
            $table->unique(['kurikulum_id', 'kode_jadwal'], 'mapel_kurikulum_kode_jadwal_unique');
        });

        $reference = require base_path('config/jadwal_reference_2026.php');
        $merdekaId = DB::table('kurikulum')->whereRaw('UPPER(kode) = ?', ['MERDEKA'])->value('id');

        if (! $merdekaId) {
            return;
        }

        foreach ($reference['mapel'] ?? [] as $mapel) {
            DB::table('mata_pelajaran')
                ->where('kurikulum_id', $merdekaId)
                ->where('kode_mapel', $mapel['canonical_code'])
                ->update(['kode_jadwal' => $mapel['code'], 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropUnique('mapel_kurikulum_kode_jadwal_unique');
            $table->dropColumn('kode_jadwal');
        });
    }
};
