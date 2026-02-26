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
        Schema::create('exam_browser_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('app_name')->default('ExamAnmet');
            $table->string('app_logo_path')->nullable();
            $table->string('school_name')->default('MAN 1 Metro');
            $table->string('moodle_url')->default('https://elearning.man1metro.sch.id');
            $table->string('user_agent')->default('SEB/3.0 ExamAnmet/1.0');
            $table->string('app_password')->nullable()->comment('Password untuk masuk aplikasi');
            $table->string('exit_password')->nullable()->comment('Password untuk keluar aplikasi');
            $table->text('seb_config_key')->nullable()->comment('SEB Config Key untuk validasi di Moodle');
            $table->text('seb_exam_key')->nullable()->comment('SEB Exam Key');
            $table->boolean('allow_screenshot')->default(false);
            $table->boolean('allow_clipboard')->default(false);
            $table->boolean('allow_navigation')->default(false)->comment('Izinkan navigasi ke URL lain');
            $table->boolean('allow_reload')->default(true);
            $table->boolean('show_toolbar')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('allowed_urls')->nullable()->comment('JSON array URL yang diizinkan');
            $table->string('blocked_apps')->nullable()->comment('JSON array package name app yang harus diblokir');
            $table->text('custom_css')->nullable()->comment('CSS kustom untuk inject ke WebView');
            $table->text('custom_js')->nullable()->comment('JS kustom untuk inject ke WebView');
            $table->string('minimum_app_version')->default('1.0.0');
            $table->text('announcement')->nullable()->comment('Pengumuman yang ditampilkan saat buka app');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_browser_settings');
    }
};
