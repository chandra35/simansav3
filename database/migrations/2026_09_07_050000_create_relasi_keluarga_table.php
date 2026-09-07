<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relasi_keluarga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('siswa_terkait_id')->nullable();
            $table->uuid('gtk_id')->nullable();
            $table->enum('jenis_relasi', ['saudara_kandung', 'anak_gtk']);
            $table->json('bukti_kecocokan');
            $table->enum('status', ['terverifikasi', 'ditolak'])->default('terverifikasi');
            $table->uuid('diverifikasi_oleh')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('siswa_terkait_id')->references('id')->on('siswa')->nullOnDelete();
            $table->foreign('gtk_id')->references('id')->on('gtks')->nullOnDelete();
            $table->index(['siswa_id', 'jenis_relasi', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('relasi_keluarga'); }
};
