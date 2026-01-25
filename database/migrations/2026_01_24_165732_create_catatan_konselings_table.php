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
        Schema::create('catatan_konseling', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('konselor_id'); // GTK (guru BK)
            $table->uuid('tahun_pelajaran_id');
            $table->date('tanggal_konseling');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->enum('jenis_konseling', [
                'individual', 
                'kelompok', 
                'klasikal', 
                'konsultasi_orangtua',
                'home_visit'
            ]);
            $table->enum('kategori_masalah', [
                'pribadi',
                'sosial', 
                'belajar',
                'karir',
                'keluarga',
                'perilaku',
                'kesehatan',
                'lainnya'
            ]);
            $table->text('permasalahan');
            $table->text('hasil_konseling')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->date('tanggal_tindak_lanjut')->nullable();
            $table->enum('status', ['baru', 'dalam_proses', 'selesai', 'perlu_rujukan'])->default('baru');
            $table->string('rujukan_ke')->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index('tanggal_konseling');
            $table->index('status');
            $table->index('kategori_masalah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_konseling');
    }
};
