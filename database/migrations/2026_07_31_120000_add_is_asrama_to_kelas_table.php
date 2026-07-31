<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->boolean('is_asrama')->default(false)->after('is_active');
        });

        // Rombel yang sudah pernah diaktifkan manual di modul asrama ikut ditandai.
        DB::table('kelas')->whereIn('id', DB::table('asrama_kelas')
            ->whereNull('deleted_at')->whereNotNull('kelas_id')->pluck('kelas_id'))
            ->update(['is_asrama' => true]);
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('is_asrama');
        });
    }
};
