<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rdm_sync_staging', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('run_id');

            $table->unsignedBigInteger('rdm_siswa_id')->nullable();
            $table->string('rdm_nisn', 100)->nullable();
            $table->string('rdm_nis', 50)->nullable();
            $table->string('rdm_nama', 255)->nullable();
            $table->string('rdm_kelas_nama', 120)->nullable();
            $table->unsignedInteger('rdm_tingkat_id')->nullable();

            $table->unsignedBigInteger('rdm_mapel_id')->nullable();
            $table->string('rdm_mapel_nama', 255)->nullable();
            $table->decimal('rdm_nilai', 6, 2)->nullable();
            $table->unsignedInteger('rdm_tahunajaran_id');
            $table->unsignedTinyInteger('rdm_semester_id');

            $table->uuid('simansa_siswa_id')->nullable();
            $table->uuid('simansa_mata_pelajaran_id')->nullable();
            $table->uuid('simansa_tahun_pelajaran_id')->nullable();
            $table->unsignedTinyInteger('simansa_semester')->nullable();

            $table->string('match_status', 30)->default('matched');
            $table->text('match_notes')->nullable();
            $table->timestamps();

            $table->foreign('run_id')->references('id')->on('rdm_sync_runs')->cascadeOnDelete();
            $table->index(['run_id', 'match_status'], 'rdm_staging_run_status_idx');
            $table->index('rdm_nisn');
            $table->index('rdm_mapel_nama');
            $table->index('simansa_siswa_id');
            $table->index('simansa_mata_pelajaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdm_sync_staging');
    }
};
