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
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->string('supervisor_password')->nullable()
                  ->after('exit_password')
                  ->comment('Password pengawas untuk unlock ujian saat offline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn('supervisor_password');
        });
    }
};
