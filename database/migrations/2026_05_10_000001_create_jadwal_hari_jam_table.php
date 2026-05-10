<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_hari_jam', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->tinyInteger('semester')->default(1); // 1 atau 2
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->tinyInteger('urutan')->unsigned(); // urutan tampil dalam 1 hari
            $table->tinyInteger('jam_ke')->unsigned()->nullable(); // null = baris non-pelajaran (istirahat, upacara)
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->enum('tipe', ['pelajaran', 'istirahat', 'upacara', 'khusus'])->default('pelajaran');
            $table->string('label')->nullable(); // mis: "Istirahat Sholat", "Upacara", dll
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index(['tahun_pelajaran_id', 'semester', 'hari']);
            // jam_ke boleh null untuk non-pelajaran, jadi unique hanya untuk baris pelajaran (ditangani di app level)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_hari_jam');
    }
};
