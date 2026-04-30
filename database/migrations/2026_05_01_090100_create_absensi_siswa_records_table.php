<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_siswa_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id');
            $table->uuid('siswa_id');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa', 'dispen'])->default('hadir');
            $table->text('notes')->nullable();
            $table->enum('attendance_method', ['manual', 'face'])->default('manual');
            $table->decimal('face_confidence', 5, 4)->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->uuid('checked_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('session_id')->references('id')->on('absensi_siswa_sessions')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('checked_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['session_id', 'siswa_id']);
            $table->index(['status', 'attendance_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa_records');
    }
};
