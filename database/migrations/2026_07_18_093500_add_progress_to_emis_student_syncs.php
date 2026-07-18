<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emis_student_syncs', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress_percent')->default(0)->after('status');
            $table->string('stage', 30)->default('queued')->after('progress_percent');
            $table->string('progress_message')->nullable()->after('stage');
        });
    }

    public function down(): void
    {
        Schema::table('emis_student_syncs', function (Blueprint $table) {
            $table->dropColumn(['progress_percent', 'stage', 'progress_message']);
        });
    }
};
