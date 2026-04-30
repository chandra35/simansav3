<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('app_settings', 'activity_log_require_location')) {
                $table->boolean('activity_log_require_location')
                    ->default(false)
                    ->after('smtp_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (Schema::hasColumn('app_settings', 'activity_log_require_location')) {
                $table->dropColumn('activity_log_require_location');
            }
        });
    }
};
