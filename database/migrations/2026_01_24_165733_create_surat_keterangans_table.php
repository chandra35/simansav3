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
        // Template Surat
        Schema::create('template_surat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->enum('jenis', [
                'sk_aktif',           // Surat Keterangan Aktif
                'sk_berkelakuan_baik', // Surat Kelakuan Baik
                'sk_pindah',          // Surat Pindah
                'sk_lulus',           // Surat Keterangan Lulus
                'sk_domisili',        // Surat Domisili
                'sk_tidak_mampu',     // Surat Tidak Mampu
                'sk_rekomendasi',     // Surat Rekomendasi
                'sk_mutasi',          // Surat Mutasi
                'sk_izin',            // Surat Izin
                'sk_lainnya'          // Lainnya
            ]);
            $table->text('konten'); // HTML template with placeholders
            $table->string('kop_surat')->nullable();
            $table->string('ttd_path')->nullable();
            $table->string('stempel_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Surat Keterangan (generated)
        Schema::create('surat_keterangan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_surat_id');
            $table->uuid('siswa_id')->nullable();
            $table->uuid('gtk_id')->nullable();
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->text('keperluan')->nullable();
            $table->text('konten_generate'); // Final generated content
            $table->uuid('penandatangan_id')->nullable(); // GTK yang menandatangani
            $table->string('jabatan_penandatangan')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->enum('status', ['draft', 'menunggu_ttd', 'selesai', 'dibatalkan'])->default('draft');
            $table->string('file_pdf')->nullable();
            $table->integer('jumlah_cetak')->default(0);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_surat_id')->references('id')->on('template_surat')->onDelete('cascade');
            $table->index('nomor_surat');
            $table->index('tanggal_surat');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan');
        Schema::dropIfExists('template_surat');
    }
};
