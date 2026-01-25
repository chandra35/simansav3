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
        Schema::table('snbp_menus', function (Blueprint $table) {
            $table->datetime('tanggal_mulai')->nullable()->after('is_active');
            $table->datetime('tanggal_berakhir')->nullable()->after('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snbp_menus', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai', 'tanggal_berakhir']);
        });
    }
};
