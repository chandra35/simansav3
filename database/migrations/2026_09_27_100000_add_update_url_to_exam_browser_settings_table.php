<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->string('update_url', 500)->nullable()->after('minimum_app_version');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn('update_url');
        });
    }
};
