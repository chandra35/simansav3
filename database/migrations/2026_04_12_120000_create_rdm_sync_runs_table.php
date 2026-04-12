<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rdm_sync_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('rdm_tahunajaran_id');
            $table->unsignedTinyInteger('rdm_semester_id');
            $table->unsignedInteger('rdm_tingkat_id')->nullable();
            $table->string('rdm_kelas_nama', 120)->nullable();

            $table->string('status', 20)->default('preview'); // preview, applied, failed
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('matched_records')->default(0);
            $table->unsignedInteger('mismatch_siswa_count')->default(0);
            $table->unsignedInteger('mismatch_mapel_count')->default(0);
            $table->unsignedInteger('mismatch_tahun_count')->default(0);
            $table->unsignedInteger('applied_count')->default(0);

            $table->uuid('initiated_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['rdm_tahunajaran_id', 'rdm_semester_id'], 'rdm_runs_tahun_semester_idx');
            $table->index('status');
            $table->foreign('initiated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdm_sync_runs');
    }
};
