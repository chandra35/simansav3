<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->boolean('ticker_enabled')->default(true)->after('theme_preset');
            $table->string('ticker_text', 255)->nullable()->after('ticker_enabled');
            $table->string('ticker_speed', 20)->default('normal')->after('ticker_text');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn(['ticker_enabled', 'ticker_text', 'ticker_speed']);
        });
    }
};
