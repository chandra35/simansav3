<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_nomor_surat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_surat', 100)->index();
            $table->unsignedInteger('nomor_urut')->index();
            $table->unsignedSmallInteger('tahun')->index();
            $table->date('tanggal_surat')->index();
            $table->foreignUuid('surat_nomor_config_id')->nullable()->constrained('surat_nomor_configs')->nullOnDelete();
            $table->string('kode_klasifikasi', 50)->index();
            $table->string('perihal', 255);
            $table->string('tujuan', 255)->nullable();
            $table->string('modul_asal', 50)->default('manual_tu')->index();
            $table->string('suratable_type', 255)->nullable()->index();
            $table->uuid('suratable_id')->nullable()->index();
            $table->foreignUuid('penandatangan_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->string('nama_penandatangan', 255)->nullable();
            $table->string('jabatan_penandatangan', 255)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['aktif', 'dibatalkan'])->default('aktif')->index();
            $table->text('alasan_dibatalkan')->nullable();
            $table->foreignUuid('dibatalkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->string('file_arsip', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_nomor_surat');
    }
};
