<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emis_student_syncs', function (Blueprint $table) {
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->after('institution_id')
                ->constrained('tahun_pelajaran')->nullOnDelete();
        });

        Schema::table('emis_student_snapshots', function (Blueprint $table) {
            $table->dropUnique('emis_student_snapshots_emis_student_id_unique');
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->after('sync_id')
                ->constrained('tahun_pelajaran')->nullOnDelete();
            $table->json('simansa_data')->nullable()->after('academic_year_status');
            $table->unique(['tahun_pelajaran_id', 'emis_student_id'], 'emis_snapshot_period_student_unique');
            $table->index(['tahun_pelajaran_id', 'comparison_status'], 'emis_snapshot_period_status_index');
        });

        $activeYearId = DB::table('tahun_pelajaran')->where('is_active', true)->value('id');
        if ($activeYearId) {
            DB::table('emis_student_syncs')->whereNull('tahun_pelajaran_id')->update(['tahun_pelajaran_id' => $activeYearId]);
            DB::table('emis_student_snapshots')->whereNull('tahun_pelajaran_id')->update(['tahun_pelajaran_id' => $activeYearId]);
        }
    }

    public function down(): void
    {
        Schema::table('emis_student_snapshots', function (Blueprint $table) {
            $table->dropIndex('emis_snapshot_period_status_index');
            $table->dropUnique('emis_snapshot_period_student_unique');
            $table->dropConstrainedForeignId('tahun_pelajaran_id');
            $table->dropColumn('simansa_data');
            $table->unique('emis_student_id');
        });
        Schema::table('emis_student_syncs', fn (Blueprint $table) => $table->dropConstrainedForeignId('tahun_pelajaran_id'));
    }
};
