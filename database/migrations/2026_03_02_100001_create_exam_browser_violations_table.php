<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_browser_violations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->comment('Relasi ke session aktif');
            $table->uuid('siswa_id')->nullable()->comment('Relasi ke tabel siswa (denormalized for quick query)');
            $table->string('violation_type')->comment('Jenis: app_switch, bluetooth, developer_mode, usb_debugging, root, split_screen, pip, headset, adware_keyboard, floating_app');
            $table->text('violation_detail')->nullable()->comment('Detail tambahan');
            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('exam_browser_sessions')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->nullOnDelete();
            $table->index('session_id');
            $table->index('siswa_id');
            $table->index('violation_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_browser_violations');
    }
};
