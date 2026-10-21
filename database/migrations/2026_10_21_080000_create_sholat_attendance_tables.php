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
        // 1. Sholat Settings (Daftar Sholat & Konfigurasi Aktif)
        Schema::create('sholat_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->boolean('is_active')->default(true);
            $table->string('jam_mulai', 5)->nullable();
            $table->string('jam_selesai', 5)->nullable();
            $table->json('hari_aktif')->nullable();
            $table->enum('target_gender', ['all', 'L', 'P'])->default('all');
            $table->unsignedInteger('urutan')->default(1);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Petugas Kerohanian Kelas (Seksi Keagamaan Siswa per Rombel)
        Schema::create('sholat_petugas_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kelas_id');
            $table->uuid('siswa_id');
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['kelas_id', 'is_active'], 'idx_sholat_ptg_kls');
            $table->index(['siswa_id', 'is_active'], 'idx_sholat_ptg_ssw');
        });

        // 3. Catatan Udzur Syar'i Siswi (Haid / Halangan)
        Schema::create('sholat_udzur_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['siswa_id', 'tanggal_mulai', 'tanggal_selesai'], 'idx_sholat_udzur_tgl');
        });

        // 4. Sesi Sholat (Sholat Session per Tanggal, Sholat, dan Rombel)
        Schema::create('sholat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->uuid('sholat_setting_id');
            $table->uuid('kelas_id');
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->enum('status', ['draft', 'finalized'])->default('finalized');
            $table->uuid('recorded_by')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('sholat_setting_id')->references('id')->on('sholat_settings')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['tanggal', 'sholat_setting_id', 'kelas_id'], 'uq_sholat_session_kls');
            $table->index(['tanggal', 'sholat_setting_id'], 'idx_sholat_sess_tgl');
        });

        // 5. Rekaman Kehadiran Siswa per Sesi Sholat
        Schema::create('sholat_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sholat_session_id');
            $table->uuid('siswa_id');
            $table->enum('status', ['berjamaah', 'masbuq', 'munfarid', 'udzur', 'sakit', 'izin', 'alpa'])->default('berjamaah');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('sholat_session_id')->references('id')->on('sholat_sessions')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();

            $table->unique(['sholat_session_id', 'siswa_id'], 'uq_sholat_rec_sess_ssw');
            $table->index(['status'], 'idx_sholat_rec_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sholat_records');
        Schema::dropIfExists('sholat_sessions');
        Schema::dropIfExists('sholat_udzur_records');
        Schema::dropIfExists('sholat_petugas_kelas');
        Schema::dropIfExists('sholat_settings');
    }
};
