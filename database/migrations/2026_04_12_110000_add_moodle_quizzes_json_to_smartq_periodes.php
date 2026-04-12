<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            // JSON column for multiple quiz selections:
            // [{"category_id":54,"category_name":"...","course_id":5,"course_name":"...","quiz_id":12,"quiz_name":"...","maxgrade":100}]
            $table->json('moodle_quizzes')->nullable()->after('moodle_quiz_name');
        });
    }

    public function down(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            $table->dropColumn('moodle_quizzes');
        });
    }
};
