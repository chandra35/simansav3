<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('app_settings', 'graduation_announcement_starts_at')) {
                $table->timestamp('graduation_announcement_starts_at')->nullable()->after('graduation_announcement_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (Schema::hasColumn('app_settings', 'graduation_announcement_starts_at')) {
                $table->dropColumn('graduation_announcement_starts_at');
            }
        });
    }
};
