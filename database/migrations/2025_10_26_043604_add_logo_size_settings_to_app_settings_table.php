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
        Schema::table('app_settings', function (Blueprint $table) {
            $table->integer('logo_kemenag_height')->default(100)->after('logo_kemenag_path')->comment('Tinggi logo kemenag dalam pixel untuk PDF');
            $table->integer('logo_sekolah_height')->default(100)->after('logo_sekolah_path')->comment('Tinggi logo sekolah dalam pixel untuk PDF');
            $table->integer('logo_display_height')->default(50)->after('logo_sekolah_height')->comment('Tinggi tampilan logo di PDF (px)');
            $table->integer('logo_column_width')->default(12)->after('logo_display_height')->comment('Lebar kolom logo dalam persen (%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_kemenag_height', 'logo_sekolah_height', 'logo_display_height', 'logo_column_width']);
        });
    }
};
