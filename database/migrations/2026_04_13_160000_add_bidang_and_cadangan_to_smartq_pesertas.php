<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Change status enum to include 'cadangan'
        // MariaDB: ALTER COLUMN to modify enum
        DB::statement("ALTER TABLE smartq_pesertas MODIFY COLUMN status ENUM('terdaftar','lulus','cadangan','tidak_lulus','mengundurkan_diri') NOT NULL DEFAULT 'terdaftar'");

        // 2. Add bidang (mapel pilihan) - single per peserta
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->uuid('bidang_mapel_id')->nullable()->after('kelas_asal_id');
            $table->foreign('bidang_mapel_id')->references('id')->on('mata_pelajaran')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->dropForeign(['bidang_mapel_id']);
            $table->dropColumn('bidang_mapel_id');
        });

        DB::statement("ALTER TABLE smartq_pesertas MODIFY COLUMN status ENUM('terdaftar','lulus','tidak_lulus','mengundurkan_diri') NOT NULL DEFAULT 'terdaftar'");
    }
};
