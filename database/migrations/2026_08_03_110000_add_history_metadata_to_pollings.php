<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pollings', function (Blueprint $table) {
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->after('status')
                ->constrained('tahun_pelajaran')->nullOnDelete();
            $table->string('tahun_pelajaran_snapshot')->nullable()->after('tahun_pelajaran_id');
            $table->string('semester_snapshot', 20)->nullable()->after('tahun_pelajaran_snapshot');
            $table->foreignUuid('source_polling_id')->nullable()->after('semester_snapshot')
                ->constrained('pollings')->nullOnDelete();
        });

        $activeYear = DB::table('tahun_pelajaran')->where('is_active', true)->first();
        if ($activeYear) {
            DB::table('pollings')->whereNull('tahun_pelajaran_id')->update([
                'tahun_pelajaran_id' => $activeYear->id,
                'tahun_pelajaran_snapshot' => $activeYear->nama,
                'semester_snapshot' => $activeYear->semester_aktif,
            ]);
        }

        DB::table('pollings')->whereNotNull('deleted_at')->update([
            'status' => 'closed',
            'deleted_at' => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('pollings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_polling_id');
            $table->dropConstrainedForeignId('tahun_pelajaran_id');
            $table->dropColumn(['tahun_pelajaran_snapshot', 'semester_snapshot']);
        });
    }
};
