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
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_surat_id')->constrained('template_surat')->onDelete('cascade');
            $table->string('nomor_surat', 100)->nullable()->unique()->index();
            $table->date('tanggal_surat')->index();
            $table->string('perihal', 255);
            $table->string('tujuan', 255)->nullable();
            $table->longText('konten_generate');
            $table->foreignUuid('penandatangan_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->string('nama_penandatangan', 255)->nullable();
            $table->string('jabatan_penandatangan', 255)->nullable();
            $table->enum('status', ['draft', 'menunggu_ttd', 'selesai', 'dibatalkan'])->default('draft')->index();
            $table->string('file_pdf', 255)->nullable();
            $table->integer('jumlah_cetak')->default(0);
            $table->json('data_tambahan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};

