<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_browser_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id')->nullable()->comment('Relasi ke tabel siswa (auto-match via NISN)');
            $table->string('device_id')->comment('Android ID perangkat');
            $table->string('device_model')->nullable()->comment('Model HP (misal: itel C671L)');
            $table->string('moodle_username')->nullable()->comment('Username dari Moodle (biasanya NISN)');
            $table->string('moodle_fullname')->nullable()->comment('Nama lengkap dari Moodle');
            $table->string('app_version')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_locked')->default(false)->comment('Apakah ujian dikunci oleh pengawas');
            $table->uuid('locked_by')->nullable()->comment('User ID yang mengunci');
            $table->string('lock_reason')->nullable()->comment('Alasan penguncian');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('last_heartbeat')->nullable()->comment('Terakhir app mengirim sinyal');
            $table->string('ip_address')->nullable();
            $table->integer('violation_count')->default(0);
            $table->boolean('is_active')->default(true)->comment('Session masih aktif');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->nullOnDelete();
            $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['device_id', 'is_active']);
            $table->index(['siswa_id', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_browser_sessions');
    }
};
