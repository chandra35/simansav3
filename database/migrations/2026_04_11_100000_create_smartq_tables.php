<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Periode seleksi SMART-Q
        Schema::create('smartq_periodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama'); // e.g. "Seleksi SMART-Q 2025/2026"
            $table->uuid('tahun_pelajaran_id');
            $table->text('deskripsi')->nullable();
            $table->integer('kuota')->default(30);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['pendaftaran', 'seleksi', 'pengumuman', 'selesai'])->default('pendaftaran');
            // Moodle integration
            $table->string('moodle_base_url')->nullable(); // e.g. https://elearning.man1metro.sch.id
            $table->integer('moodle_course_id')->nullable();
            $table->integer('moodle_quiz_id')->nullable();
            $table->string('moodle_quiz_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
        });

        // Komponen penilaian dan bobotnya
        Schema::create('smartq_komponen_nilais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('smartq_periode_id');
            $table->string('nama'); // e.g. "Tes CBT", "Tahfidz", "Wawancara"
            $table->string('kode', 50); // e.g. "cbt", "tahfidz", "wawancara"
            $table->decimal('bobot', 5, 2); // percentage weight, e.g. 40.00
            $table->decimal('nilai_maksimal', 8, 2)->default(100);
            $table->enum('sumber', ['manual', 'moodle'])->default('manual');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('smartq_periode_id')->references('id')->on('smartq_periodes')->cascadeOnDelete();
            $table->unique(['smartq_periode_id', 'kode']);
        });

        // Peserta seleksi
        Schema::create('smartq_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('smartq_periode_id');
            $table->uuid('siswa_id');
            $table->string('nomor_peserta', 50)->nullable();
            $table->uuid('kelas_asal_id')->nullable();
            $table->enum('status', ['terdaftar', 'lulus', 'tidak_lulus', 'mengundurkan_diri'])->default('terdaftar');
            $table->decimal('total_nilai', 8, 2)->nullable();
            $table->integer('ranking')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('smartq_periode_id')->references('id')->on('smartq_periodes')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('kelas_asal_id')->references('id')->on('kelas')->nullOnDelete();
            $table->unique(['smartq_periode_id', 'siswa_id']);
        });

        // Nilai per komponen per peserta
        Schema::create('smartq_nilais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('smartq_peserta_id');
            $table->uuid('smartq_komponen_nilai_id');
            $table->decimal('nilai', 8, 2)->nullable();
            $table->decimal('nilai_konversi', 8, 2)->nullable(); // normalized to 100
            $table->text('catatan')->nullable();
            // Moodle reference
            $table->integer('moodle_attempt_id')->nullable();
            $table->string('moodle_username')->nullable();
            // Audit
            $table->uuid('dinilai_oleh')->nullable();
            $table->timestamp('dinilai_pada')->nullable();
            $table->timestamps();

            $table->foreign('smartq_peserta_id')->references('id')->on('smartq_pesertas')->cascadeOnDelete();
            $table->foreign('smartq_komponen_nilai_id')->references('id')->on('smartq_komponen_nilais')->cascadeOnDelete();
            $table->foreign('dinilai_oleh')->references('id')->on('users')->nullOnDelete();
            $table->unique(['smartq_peserta_id', 'smartq_komponen_nilai_id'], 'smartq_nilais_peserta_komponen_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smartq_nilais');
        Schema::dropIfExists('smartq_pesertas');
        Schema::dropIfExists('smartq_komponen_nilais');
        Schema::dropIfExists('smartq_periodes');
    }
};
