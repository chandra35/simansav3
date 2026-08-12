<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('is_active');
            $table->uuid('blocked_by')->nullable()->after('blocked_at');
            $table->string('block_reason')->nullable()->after('blocked_by');
            $table->index('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropIndex(['blocked_at']);
            $table->dropColumn(['blocked_at', 'blocked_by', 'block_reason']);
        });
    }
};
