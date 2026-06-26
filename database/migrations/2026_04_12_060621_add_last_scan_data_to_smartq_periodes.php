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
            $afterColumn = Schema::hasColumn('smartq_periodes', 'moodle_quizzes')
                ? 'moodle_quizzes'
                : 'moodle_quiz_name';

            $table->longText('last_scan_data')->nullable()->after($afterColumn);
            $table->timestamp('last_scan_at')->nullable()->after('last_scan_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            if (Schema::hasColumn('smartq_periodes', 'last_scan_data')) {
                $table->dropColumn(['last_scan_data', 'last_scan_at']);
            }
        });
    }
};
