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
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignUuid('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->date('tanggal_masuk')->comment('Tanggal siswa masuk ke kelas ini');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal siswa keluar dari kelas (naik/tinggal/lulus/keluar)');
            $table->enum('status', ['aktif', 'naik_kelas', 'tinggal_kelas', 'lulus', 'keluar'])->default('aktif');
            $table->integer('nomor_urut_absen')->nullable()->comment('Nomor absen siswa di kelas');
            $table->text('catatan_perpindahan')->nullable()->comment('Catatan kenapa pindah kelas');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('siswa_id');
            $table->index('kelas_id');
            $table->index('tahun_pelajaran_id');
            $table->index('status');
            $table->index(['siswa_id', 'tahun_pelajaran_id']);
            
            // Prevent duplicate active enrollment
            $table->unique(['siswa_id', 'kelas_id', 'tahun_pelajaran_id'], 'siswa_kelas_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_kelas');
    }
};
