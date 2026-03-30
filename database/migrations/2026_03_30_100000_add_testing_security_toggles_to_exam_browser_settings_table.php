<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->boolean('testing_allow_developer_options')
                ->default(false)
                ->after('show_toolbar');
            $table->boolean('testing_allow_usb_debugging')
                ->default(false)
                ->after('testing_allow_developer_options');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn([
                'testing_allow_developer_options',
                'testing_allow_usb_debugging',
            ]);
        });
    }
};
