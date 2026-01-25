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
        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('nama');
            $table->string('kode')->unique();
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['wajib', 'pilihan']);
            $table->enum('jenis', ['olahraga', 'seni', 'keagamaan', 'akademik', 'teknologi', 'sosial', 'kepramukaan', 'lainnya']);
            $table->uuid('pembina_id')->nullable(); // GTK pembina
            $table->string('hari_latihan')->nullable(); // JSON array of days
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('tempat')->nullable();
            $table->integer('kuota')->default(0); // 0 = unlimited
            $table->decimal('biaya', 12, 2)->default(0);
            $table->string('foto')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index('kategori');
            $table->index('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler');
    }
};
