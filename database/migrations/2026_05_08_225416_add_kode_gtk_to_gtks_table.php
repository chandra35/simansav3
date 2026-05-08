<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->string('kode_gtk', 20)->nullable()->unique()->after('nip')
                ->comment('Kode singkat guru, opsional, untuk referensi Excel waka kurikulum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->dropColumn('kode_gtk');
        });
    }
};
