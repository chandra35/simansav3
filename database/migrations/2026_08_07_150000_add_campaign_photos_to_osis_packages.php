<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osis_packages', function (Blueprint $table) {
            $table->string('campaign_photo')->nullable()->after('message');
            $table->json('live_photos')->nullable()->after('campaign_photo');
        });
    }

    public function down(): void
    {
        Schema::table('osis_packages', function (Blueprint $table) {
            $table->dropColumn(['campaign_photo', 'live_photos']);
        });
    }
};
