<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->integer('peringkat_mapel')->nullable()->after('ranking');
        });
    }

    public function down(): void
    {
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->dropColumn('peringkat_mapel');
        });
    }
};
