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
        Schema::create('kalender_akademik', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->string('kategori')->default('umum'); // umum, libur, ujian, kegiatan, rapat, hari_besar_islam
            $table->string('warna')->default('#3788d8'); // hex color for calendar display
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->boolean('is_libur')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_type')->nullable(); // weekly, monthly, yearly
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalender_akademik');
    }
};
