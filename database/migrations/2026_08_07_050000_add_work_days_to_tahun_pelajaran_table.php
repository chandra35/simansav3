<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_pelajaran', function (Blueprint $table) {
            $table->unsignedTinyInteger('jumlah_hari_kerja')->default(5)->after('semester_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_pelajaran', function (Blueprint $table) {
            $table->dropColumn('jumlah_hari_kerja');
        });
    }
};
