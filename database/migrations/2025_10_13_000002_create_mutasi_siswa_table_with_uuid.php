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
        Schema::create('mutasi_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->enum('jenis_mutasi', ['masuk', 'keluar'])->comment('Jenis mutasi: masuk atau keluar');
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            
            // Data Sekolah Asal (untuk mutasi masuk)
            $table->string('sekolah_asal', 200)->nullable()->comment('Nama sekolah asal (untuk mutasi masuk)');
            $table->string('npsn_sekolah_asal', 20)->nullable()->comment('NPSN sekolah asal');
            $table->text('alamat_sekolah_asal')->nullable();
            $table->string('kelas_asal', 50)->nullable()->comment('Kelas di sekolah asal');
            $table->text('alasan_mutasi_masuk')->nullable();
            
            // Data Sekolah Tujuan (untuk mutasi keluar)
            $table->string('sekolah_tujuan', 200)->nullable()->comment('Nama sekolah tujuan (untuk mutasi keluar)');
            $table->string('npsn_sekolah_tujuan', 20)->nullable()->comment('NPSN sekolah tujuan');
            $table->text('alamat_sekolah_tujuan')->nullable();
            $table->text('alasan_mutasi_keluar')->nullable();
            
            // Dokumen & Administrasi
            $table->date('tanggal_mutasi')->comment('Tanggal efektif mutasi');
            $table->string('nomor_surat_mutasi', 100)->nullable()->comment('Nomor surat mutasi');
            $table->string('file_surat_mutasi', 255)->nullable()->comment('Path file surat mutasi (PDF)');
            
            // Verifikasi & Approval
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('verifikator_id')->nullable()->constrained('users')->onDelete('set null')->comment('User yang approve/reject');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable()->comment('Catatan dari verifikator');
            $table->text('catatan')->nullable()->comment('Catatan umum');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('siswa_id');
            $table->index('jenis_mutasi');
            $table->index('tahun_pelajaran_id');
            $table->index('status_verifikasi');
            $table->index('tanggal_mutasi');
            $table->index(['jenis_mutasi', 'status_verifikasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_siswa');
    }
};
