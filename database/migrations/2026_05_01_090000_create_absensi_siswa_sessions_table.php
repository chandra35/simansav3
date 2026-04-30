<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_siswa_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->uuid('kelas_id');
            $table->uuid('jadwal_pelajaran_id')->nullable();
            $table->uuid('mapel_id')->nullable();
            $table->uuid('guru_user_id')->nullable();
            $table->date('tanggal');
            $table->enum('mode', ['harian', 'mapel'])->default('mapel');
            $table->enum('attendance_method', ['manual', 'face', 'hybrid'])->default('manual');
            $table->enum('status', ['draft', 'final'])->default('final');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->nullOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('jadwal_pelajaran_id')->references('id')->on('jadwal_pelajaran')->nullOnDelete();
            $table->foreign('mapel_id')->references('id')->on('mata_pelajaran')->nullOnDelete();
            $table->foreign('guru_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['tanggal', 'kelas_id']);
            $table->index(['mode', 'attendance_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa_sessions');
    }
};
