<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'nomor_tes')) {
                $table->string('nomor_tes', 80)->nullable()->after('nisn')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'nomor_tes')) {
                $table->dropIndex(['nomor_tes']);
                $table->dropColumn('nomor_tes');
            }
        });
    }
};
