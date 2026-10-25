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
        Schema::create('tka_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tahun', 10)->default('2026')->index();
            $table->uuid('siswa_id')->nullable()->index();
            $table->string('nisn', 20)->index();
            $table->string('nama_peserta');
            $table->string('tempat_tanggal_lahir')->nullable();
            $table->string('username', 50)->index();
            $table->string('password', 50);
            $table->string('id_proktor', 50)->nullable();
            $table->string('ruang', 50)->index();
            $table->string('gelombang', 50)->index();
            $table->string('sesi', 20)->index();
            $table->json('jadwal')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->nullOnDelete();

            $table->unique(['tahun', 'nisn'], 'tka_pesertas_tahun_nisn_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_pesertas');
    }
};
