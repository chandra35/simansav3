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
        Schema::create('penilaian_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester')->comment('1: Ganjil, 2: Genap');
            $table->foreignUuid('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignUuid('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignUuid('gtk_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();

            $table->enum('kategori', ['harian', 'tengah_semester', 'akhir_semester', 'tugas', 'proyek'])->default('harian');
            $table->string('kode_penilaian', 50)->nullable()->comment('PH1, PH2, ATS, ASAS');
            $table->string('nama_penilaian', 100);

            $table->unsignedBigInteger('moodle_quiz_id')->nullable()->index();
            $table->foreignUuid('moodle_quiz_archive_id')->nullable()->constrained('moodle_quiz_archives')->nullOnDelete();

            $table->decimal('nilai', 5, 2)->default(0);
            $table->decimal('remedial', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->default(0);

            $table->string('keterangan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun_pelajaran_id', 'semester', 'kelas_id', 'mata_pelajaran_id'], 'idx_penilaian_filter');
            $table->index(['siswa_id', 'mata_pelajaran_id', 'kategori'], 'idx_penilaian_siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_siswa');
    }
};
