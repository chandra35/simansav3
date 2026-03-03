<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite index for violation rate-limiting query:
     * WHERE session_id = ? AND violation_type = ? AND created_at >= ?
     */
    public function up(): void
    {
        Schema::table('exam_browser_violations', function (Blueprint $table) {
            $table->index(['session_id', 'violation_type', 'created_at'], 'violations_rate_limit_idx');
        });

        // Add composite index for session heartbeat query optimization
        Schema::table('exam_browser_sessions', function (Blueprint $table) {
            $table->index(['is_active', 'last_heartbeat'], 'sessions_active_heartbeat_idx');
            $table->index(['is_active', 'started_at'], 'sessions_active_started_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_violations', function (Blueprint $table) {
            $table->dropIndex('violations_rate_limit_idx');
        });

        Schema::table('exam_browser_sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_active_heartbeat_idx');
            $table->dropIndex('sessions_active_started_idx');
        });
    }
};
