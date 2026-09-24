<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('app_settings', 'akreditasi')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->string('akreditasi', 20)->nullable()->after('nsm');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_settings', 'akreditasi')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->dropColumn('akreditasi');
            });
        }
    }
};
