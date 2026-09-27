<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->string('cbtman_app_password')->nullable()->after('supervisor_password');
            $table->string('cbtman_exit_password')->nullable()->after('cbtman_app_password');
            $table->string('cbtman_supervisor_password')->nullable()->after('cbtman_exit_password');
        });
    }

    public function down(): void
    {
        Schema::table('exam_browser_settings', function (Blueprint $table) {
            $table->dropColumn([
                'cbtman_app_password',
                'cbtman_exit_password',
                'cbtman_supervisor_password',
            ]);
        });
    }
};
