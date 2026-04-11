<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            $table->integer('moodle_category_id')->nullable()->after('moodle_base_url');
            $table->string('moodle_category_name')->nullable()->after('moodle_category_id');
            $table->string('moodle_course_name')->nullable()->after('moodle_course_id');
        });
    }

    public function down(): void
    {
        Schema::table('smartq_periodes', function (Blueprint $table) {
            $table->dropColumn(['moodle_category_id', 'moodle_category_name', 'moodle_course_name']);
        });
    }
};
