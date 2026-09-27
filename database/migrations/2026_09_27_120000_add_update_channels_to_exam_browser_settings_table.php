<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->string('update_channel', 20)->default('direct')->after('update_url');
            $table->string('play_store_url', 500)->nullable()->after('update_channel');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn(['update_channel', 'play_store_url']);
        });
    }
};
