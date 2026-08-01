<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_wali_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('kelas_id');
            $table->uuid('tahun_pelajaran_id');
            $table->uuid('created_by'); // user wali kelas penulis catatan
            $table->date('tanggal');
            $table->string('kategori', 30)->nullable(); // akademik/sikap/kehadiran/prestasi/lainnya
            $table->text('catatan');
            $table->boolean('is_penting')->default(false);
            $table->timestamp('dibaca_bk_at')->nullable();
            $table->uuid('dibaca_bk_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();

            $table->index(['siswa_id', 'tahun_pelajaran_id']);
            $table->index(['kelas_id', 'tanggal']);
            $table->index('dibaca_bk_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_wali_kelas');
    }
};
