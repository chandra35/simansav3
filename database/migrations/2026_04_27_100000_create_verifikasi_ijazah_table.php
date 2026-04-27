<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_ijazah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('verifikator_id')->nullable(); // user_id GTK verifikator
            $table->string('verifikator_nama')->nullable(); // snapshot nama verifikator
            
            // Status verifikasi
            $table->enum('status', [
                'belum_diverifikasi',
                'sesuai',
                'tidak_sesuai',
                'perlu_perbaikan',
            ])->default('belum_diverifikasi');
            
            // Snapshot data sumber (disimpan sebagai JSON saat verifikasi)
            $table->json('data_simansa')->nullable();        // snapshot data simansa saat verifikasi
            $table->json('data_emis_kemdikbud')->nullable(); // snapshot dari pusdatin/kemdikbud
            $table->json('data_emis_kemenag')->nullable();   // snapshot dari ppdb-search/kemenag
            
            // Hasil verifikasi
            $table->json('field_tidak_sesuai')->nullable();  // ['nama_lengkap', 'tanggal_lahir', ...]
            $table->json('saran_perbaikan')->nullable();     // {field: nilai_yang_benar, ...}
            $table->text('catatan')->nullable();
            
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        Schema::create('verifikasi_ijazah_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('verifikasi_id');
            $table->uuid('user_id'); // siapa yang melakukan aksi
            $table->string('user_nama');
            $table->string('aksi'); // created, updated, status_changed, catatan_added
            $table->string('status_lama')->nullable();
            $table->string('status_baru')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('verifikasi_id')->references('id')->on('verifikasi_ijazah')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_ijazah_logs');
        Schema::dropIfExists('verifikasi_ijazah');
    }
};
