<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            $table->longText('last_scan_data')->nullable()->after('moodle_quizzes');
            $table->timestamp('last_scan_at')->nullable()->after('last_scan_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            $table->dropColumn(['last_scan_data', 'last_scan_at']);
        });
    }
};
