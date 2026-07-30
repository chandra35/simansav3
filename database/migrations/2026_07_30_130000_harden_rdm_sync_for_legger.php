<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rdm_sync_runs', function (Blueprint $table) {
            $table->uuid('simansa_tahun_pelajaran_id')->nullable()->after('rdm_kelas_nama');
            $table->uuid('simansa_kelas_id')->nullable()->after('simansa_tahun_pelajaran_id');
        });

        Schema::table('rdm_sync_staging', function (Blueprint $table) {
            $table->decimal('rdm_nilai_pengetahuan', 6, 2)->nullable()->after('rdm_nilai');
            $table->decimal('rdm_nilai_keterampilan', 6, 2)->nullable()->after('rdm_nilai_pengetahuan');
            $table->string('rdm_predikat', 5)->nullable()->after('rdm_nilai_keterampilan');
            $table->text('rdm_deskripsi_pengetahuan')->nullable()->after('rdm_predikat');
            $table->text('rdm_deskripsi_keterampilan')->nullable()->after('rdm_deskripsi_pengetahuan');
            $table->string('apply_action', 30)->nullable()->after('simansa_semester');
            $table->decimal('existing_nilai', 6, 2)->nullable()->after('apply_action');
            $table->decimal('existing_nilai_pengetahuan', 6, 2)->nullable()->after('existing_nilai');
            $table->decimal('existing_nilai_keterampilan', 6, 2)->nullable()->after('existing_nilai_pengetahuan');
            $table->index(['run_id', 'apply_action'], 'rdm_staging_run_action_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rdm_sync_staging', function (Blueprint $table) {
            $table->dropIndex('rdm_staging_run_action_idx');
            $table->dropColumn([
                'rdm_nilai_pengetahuan',
                'rdm_nilai_keterampilan',
                'rdm_predikat',
                'rdm_deskripsi_pengetahuan',
                'rdm_deskripsi_keterampilan',
                'apply_action',
                'existing_nilai',
                'existing_nilai_pengetahuan',
                'existing_nilai_keterampilan',
            ]);
        });

        Schema::table('rdm_sync_runs', function (Blueprint $table) {
            $table->dropColumn(['simansa_tahun_pelajaran_id', 'simansa_kelas_id']);
        });
    }
};
