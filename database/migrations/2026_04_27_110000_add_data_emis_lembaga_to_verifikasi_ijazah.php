<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasi_ijazah', function (Blueprint $table) {
            $table->json('data_emis_lembaga')->nullable()->after('data_emis_kemenag');
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_ijazah', function (Blueprint $table) {
            $table->dropColumn('data_emis_lembaga');
        });
    }
};
