<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            $table->json('tracking_lulusan')->nullable()->after('referensi_sumber');
            $table->timestamp('tracking_lulusan_updated_at')->nullable()->after('tracking_lulusan');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            $table->dropColumn(['tracking_lulusan', 'tracking_lulusan_updated_at']);
        });
    }
};
