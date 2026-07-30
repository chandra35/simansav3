<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rdm_sync_staging', function (Blueprint $table) {
            $table->unsignedInteger('rdm_kurikulum_id')->nullable()->after('rdm_mapel_nama');
            $table->string('rdm_kurikulum_kode', 20)->nullable()->after('rdm_kurikulum_id');
            $table->string('rdm_kurikulum_nama', 80)->nullable()->after('rdm_kurikulum_kode');
            $table->index(['run_id', 'rdm_kurikulum_kode'], 'rdm_staging_run_curriculum_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rdm_sync_staging', function (Blueprint $table) {
            $table->dropIndex('rdm_staging_run_curriculum_idx');
            $table->dropColumn(['rdm_kurikulum_id', 'rdm_kurikulum_kode', 'rdm_kurikulum_nama']);
        });
    }
};
