<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('span_ptkin_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_menu');
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->longText('konten_informasi')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_berakhir')->nullable();
            $table->timestamps();

            $table->unique('tahun_pelajaran_id', 'span_ptkin_menus_tahun_unique');
            $table->index(['tahun_pelajaran_id', 'is_active'], 'span_ptkin_menus_tahun_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('span_ptkin_menus');
    }
};
