<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('siswa_kelas', 'tingkat')) {
            Schema::table('siswa_kelas', function (Blueprint $table) {
                $table->unsignedTinyInteger('tingkat')
                    ->nullable()
                    ->after('tahun_pelajaran_id')
                    ->comment('Tingkat akademik siswa pada tahun pelajaran ini: 10, 11, atau 12');
                $table->index(['tahun_pelajaran_id', 'tingkat', 'status'], 'siswa_kelas_tahun_tingkat_status_idx');
            });
        }

        DB::table('siswa_kelas')
            ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->whereNull('siswa_kelas.tingkat')
            ->update(['siswa_kelas.tingkat' => DB::raw('kelas.tingkat')]);

        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
        });

        DB::statement('ALTER TABLE siswa_kelas MODIFY kelas_id CHAR(36) NULL');

        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->foreign('kelas_id')
                ->references('id')
                ->on('kelas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
        });

        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->foreign('kelas_id')
                ->references('id')
                ->on('kelas')
                ->cascadeOnDelete();
        });

        if (Schema::hasColumn('siswa_kelas', 'tingkat')) {
            Schema::table('siswa_kelas', function (Blueprint $table) {
                $table->dropIndex('siswa_kelas_tahun_tingkat_status_idx');
                $table->dropColumn('tingkat');
            });
        }
    }
};
