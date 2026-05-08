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
        Schema::create('jadwal_jam_config', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->tinyInteger('urutan')->unsigned(); // display order (termasuk baris istirahat)
            $table->tinyInteger('jam_ke')->unsigned()->nullable(); // null = baris istirahat
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->boolean('is_istirahat')->default(false);
            $table->string('label')->nullable(); // e.g. "Istirahat", atau null
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->unique(['tahun_pelajaran_id', 'jam_ke'], 'jam_config_unique_jam');
            $table->index('tahun_pelajaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_jam_config');
    }
};
