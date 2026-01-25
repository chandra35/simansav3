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
        Schema::create('ekstrakurikuler_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ekstrakurikuler_id');
            $table->uuid('siswa_id');
            $table->uuid('tahun_pelajaran_id');
            $table->date('tanggal_bergabung');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'lulus', 'keluar'])->default('aktif');
            $table->enum('jabatan', ['anggota', 'ketua', 'wakil_ketua', 'sekretaris', 'bendahara'])->default('anggota');
            $table->integer('nilai_ekskul')->nullable(); // 1-100
            $table->string('predikat')->nullable(); // A, B, C, D
            $table->text('catatan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ekstrakurikuler_id')->references('id')->on('ekstrakurikuler')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->unique(['ekstrakurikuler_id', 'siswa_id', 'tahun_pelajaran_id'], 'ekskul_siswa_tp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_anggota');
    }
};
