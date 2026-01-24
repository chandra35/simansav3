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
        Schema::create('kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->foreignUuid('kurikulum_id')->constrained('kurikulum')->onDelete('restrict');
            $table->foreignUuid('jurusan_id')->nullable()->constrained('jurusan')->onDelete('set null')->comment('Nullable untuk Kurikulum Merdeka yang tidak punya jurusan');
            $table->string('nama_kelas', 50)->comment('Format: X IPA 1, XI IPS 2, XII UMUM 1');
            $table->integer('tingkat')->comment('10, 11, atau 12');
            $table->string('kode_kelas', 20)->unique()->comment('Kode unik kelas: X-IPA-1-2024');
            $table->foreignUuid('wali_kelas_id')->nullable()->constrained('users')->onDelete('set null')->comment('User dengan role guru/admin');
            $table->integer('kapasitas')->default(36)->comment('Maksimal jumlah siswa');
            $table->string('ruang_kelas', 50)->nullable()->comment('Nama/nomor ruang kelas');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tahun_pelajaran_id');
            $table->index('kurikulum_id');
            $table->index('jurusan_id');
            $table->index('tingkat');
            $table->index('kode_kelas');
            $table->index('wali_kelas_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
