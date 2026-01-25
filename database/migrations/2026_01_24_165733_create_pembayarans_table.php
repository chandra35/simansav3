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
        // Jenis Pembayaran (master)
        Schema::create('jenis_pembayaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('nama'); // SPP, Uang Gedung, Seragam, dll
            $table->string('kode')->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->enum('tipe', ['bulanan', 'tahunan', 'sekali'])->default('bulanan');
            $table->boolean('is_wajib')->default(true);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
        });

        // Tagihan (per siswa per jenis)
        Schema::create('tagihan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('jenis_pembayaran_id');
            $table->uuid('tahun_pelajaran_id');
            $table->tinyInteger('bulan')->nullable(); // 1-12 untuk SPP bulanan
            $table->integer('tahun')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2); // nominal - diskon
            $table->decimal('total_terbayar', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->enum('status', ['belum_bayar', 'cicilan', 'lunas'])->default('belum_bayar');
            $table->text('keterangan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('jenis_pembayaran_id')->references('id')->on('jenis_pembayaran')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index(['siswa_id', 'tahun_pelajaran_id']);
            $table->index('status');
        });

        // Pembayaran (transaksi)
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tagihan_id');
            $table->uuid('siswa_id');
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_bayar', ['tunai', 'transfer', 'qris', 'virtual_account', 'lainnya'])->default('tunai');
            $table->string('bukti_bayar')->nullable();
            $table->string('bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            $table->text('keterangan')->nullable();
            $table->uuid('diterima_oleh')->nullable(); // GTK yang menerima
            $table->boolean('is_verified')->default(false);
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tagihan_id')->references('id')->on('tagihan')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->index('tanggal_bayar');
            $table->index('nomor_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('tagihan');
        Schema::dropIfExists('jenis_pembayaran');
    }
};
