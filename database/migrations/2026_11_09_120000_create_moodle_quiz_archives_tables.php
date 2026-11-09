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
        Schema::create('moodle_quiz_archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('gtk_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajaran')->nullOnDelete();
            $table->unsignedTinyInteger('semester')->default(1)->comment('1: Ganjil, 2: Genap');
            $table->foreignUuid('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->nullOnDelete();
            $table->foreignUuid('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();

            $table->unsignedBigInteger('moodle_course_id')->index();
            $table->string('moodle_course_name');
            $table->unsignedBigInteger('moodle_category_id')->nullable()->index();
            $table->string('moodle_category_name')->nullable();
            $table->unsignedBigInteger('moodle_quiz_id')->index();
            $table->string('moodle_quiz_name');

            $table->enum('kategori_penilaian', ['harian', 'tengah_semester', 'akhir_semester', 'lainnya'])->default('harian');
            $table->string('nama_penilaian')->nullable();
            
            $table->json('statistics')->nullable()->comment('Ringkasan statistik kuis (attempts, avg, min, max, sd, reliabilitas)');
            $table->longText('items_analysis')->nullable()->comment('Analisis butir soal lengkap termasuk teks soal HTML, gambar, opsi A-E, kesukaran & pembeda');

            $table->boolean('is_locked')->default(false)->index();
            $table->timestamp('locked_at')->nullable();
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gtk_id', 'tahun_pelajaran_id', 'semester']);
            $table->index(['moodle_quiz_id', 'is_locked']);
        });

        Schema::create('moodle_quiz_archive_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('archive_id')->constrained('moodle_quiz_archives')->cascadeOnDelete();
            $table->unsignedBigInteger('moodle_userid')->nullable()->index();
            $table->string('moodle_username', 50)->nullable()->index()->comment('NISN atau NIK');
            $table->foreignUuid('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->string('nama_siswa');
            $table->string('kelas_nama', 50)->nullable();
            $table->decimal('grade', 5, 2)->default(0);
            $table->decimal('max_grade', 5, 2)->default(100);
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->timestamp('time_finished')->nullable();
            $table->json('responses_summary')->nullable();
            $table->timestamps();

            $table->index(['archive_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moodle_quiz_archive_scores');
        Schema::dropIfExists('moodle_quiz_archives');
    }
};
