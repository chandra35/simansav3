<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('tahun');
            $table->unsignedInteger('nomor_urut');
            $table->string('nomor_berkas', 40)->unique();
            $table->string('tanggal_nomor', 255);
            $table->string('asal', 255);
            $table->text('isi_ringkasan');
            $table->date('diterima_tanggal');
            $table->string('status', 30)->default('dicatat');
            $table->string('surat_masuk_path')->nullable();
            $table->string('surat_masuk_nama')->nullable();
            $table->string('print_path')->nullable();
            $table->unsignedInteger('print_count')->default(0);
            $table->timestamp('printed_at')->nullable();
            $table->string('hasil_disposisi_path')->nullable();
            $table->string('hasil_disposisi_nama')->nullable();
            $table->timestamp('hasil_disposisi_uploaded_at')->nullable();
            $table->uuid('hasil_disposisi_uploaded_by')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun', 'nomor_urut']);
            $table->index(['tahun', 'status']);
            $table->index('diterima_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
