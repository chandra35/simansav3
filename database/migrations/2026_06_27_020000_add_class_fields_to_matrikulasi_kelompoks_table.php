<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matrikulasi_kelompoks', function (Blueprint $table) {
            if (!Schema::hasColumn('matrikulasi_kelompoks', 'tingkat_kelas')) {
                $table->string('tingkat_kelas', 30)->nullable()->after('kode');
            }

            if (!Schema::hasColumn('matrikulasi_kelompoks', 'jenis_kelompok')) {
                $table->string('jenis_kelompok', 30)->default('reguler')->after('tingkat_kelas');
                $table->index(['matrikulasi_periode_id', 'jenis_kelompok'], 'matrikulasi_kelompok_periode_jenis_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('matrikulasi_kelompoks', function (Blueprint $table) {
            if (Schema::hasColumn('matrikulasi_kelompoks', 'jenis_kelompok')) {
                $table->dropIndex('matrikulasi_kelompok_periode_jenis_idx');
                $table->dropColumn('jenis_kelompok');
            }

            if (Schema::hasColumn('matrikulasi_kelompoks', 'tingkat_kelas')) {
                $table->dropColumn('tingkat_kelas');
            }
        });
    }
};
