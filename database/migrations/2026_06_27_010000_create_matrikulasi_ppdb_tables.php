<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matrikulasi_periodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('nama', 120);
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('tahun_pelajaran_id');
            $table->index('status');
        });

        Schema::create('matrikulasi_kelompoks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('matrikulasi_periode_id')->constrained('matrikulasi_periodes')->cascadeOnDelete();
            $table->string('nama', 120);
            $table->string('kode', 30)->nullable();
            $table->unsignedSmallInteger('kapasitas')->nullable();
            $table->uuid('pembina_id')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pembina_id')->references('id')->on('gtks')->nullOnDelete();
            $table->unique(['matrikulasi_periode_id', 'nama'], 'matrikulasi_kelompok_periode_nama_unique');
            $table->index(['matrikulasi_periode_id', 'status'], 'matrikulasi_kelompok_periode_status_idx');
        });

        Schema::create('matrikulasi_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('matrikulasi_periode_id')->constrained('matrikulasi_periodes')->cascadeOnDelete();
            $table->foreignUuid('matrikulasi_kelompok_id')->nullable()->constrained('matrikulasi_kelompoks')->nullOnDelete();
            $table->uuid('siswa_id')->nullable();
            $table->uuid('ppdb_calon_siswa_id');
            $table->string('ppdb_tahun_pelajaran_id', 80)->nullable();
            $table->string('nomor_registrasi', 80)->nullable();
            $table->string('nomor_tes', 80)->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('nama_lengkap', 160);
            $table->string('jenis_kelamin', 5)->nullable();
            $table->string('jurusan_awal', 80)->nullable();
            $table->string('jurusan_final', 80)->nullable();
            $table->json('data_siswa')->nullable();
            $table->json('data_ortu')->nullable();
            $table->json('data_ppdb')->nullable();
            $table->enum('status', ['matrikulasi', 'dipromosikan', 'dibatalkan'])->default('matrikulasi');
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('promoted_at')->nullable();
            $table->uuid('promoted_by')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->nullOnDelete();
            $table->foreign('promoted_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['matrikulasi_periode_id', 'ppdb_calon_siswa_id'], 'matrikulasi_peserta_periode_ppdb_unique');
            $table->index(['matrikulasi_periode_id', 'status'], 'matrikulasi_peserta_periode_status_idx');
            $table->index(['nisn', 'nik']);
        });

        Schema::create('matrikulasi_dokumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('matrikulasi_peserta_id')->constrained('matrikulasi_pesertas')->cascadeOnDelete();
            $table->uuid('ppdb_calon_dokumen_id')->nullable();
            $table->string('jenis_dokumen', 80)->nullable();
            $table->string('nama_dokumen', 160)->nullable();
            $table->string('nama_file', 255)->nullable();
            $table->string('file_path', 700)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('storage_disk', 40)->nullable();
            $table->string('ppdb_source_disk', 40)->nullable();
            $table->string('ppdb_source_url', 700)->nullable();
            $table->enum('status_verifikasi', ['pending', 'valid', 'invalid', 'revision'])->default('pending');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['matrikulasi_peserta_id', 'ppdb_calon_dokumen_id'], 'matrikulasi_dokumen_peserta_ppdb_unique');
            $table->index('jenis_dokumen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matrikulasi_dokumens');
        Schema::dropIfExists('matrikulasi_pesertas');
        Schema::dropIfExists('matrikulasi_kelompoks');
        Schema::dropIfExists('matrikulasi_periodes');
    }
};
