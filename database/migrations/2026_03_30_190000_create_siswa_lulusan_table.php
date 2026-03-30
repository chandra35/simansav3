<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_lulusan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->string('jalur_masuk', 50);
            $table->string('nama_universitas');
            $table->string('jurusan_fakultas')->nullable();
            $table->string('program_studi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['siswa_id', 'tahun_pelajaran_id'], 'siswa_lulusan_unique_per_tahun');
            $table->index(['tahun_pelajaran_id', 'jalur_masuk'], 'siswa_lulusan_tahun_jalur_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_lulusan');
    }
};
