<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asrama_pengampu', function (Blueprint $table) {
            $table->index('asrama_kelas_id', 'asrama_pengampu_kelas_fk_index');
        });
        Schema::table('asrama_pengampu', function (Blueprint $table) {
            $table->dropUnique('asrama_pengampu_unique');
            $table->unique(
                ['asrama_kelas_id', 'asrama_mapel_id', 'semester'],
                'asrama_pengampu_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asrama_pengampu', function (Blueprint $table) {
            $table->dropUnique('asrama_pengampu_subject_unique');
            $table->unique(
                ['asrama_kelas_id', 'asrama_mapel_id', 'asrama_asatidz_id', 'semester'],
                'asrama_pengampu_unique'
            );
        });
        Schema::table('asrama_pengampu', function (Blueprint $table) {
            $table->dropIndex('asrama_pengampu_kelas_fk_index');
        });
    }
};
