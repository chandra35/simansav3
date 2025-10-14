<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * STEP 3: Convert tahun_pelajaran table to UUID
     * Depends on: kurikulum (must be UUID first)
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop and recreate tahun_pelajaran table with UUID
        Schema::dropIfExists('tahun_pelajaran');
        
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kurikulum_id')->constrained('kurikulum')->onDelete('restrict');
            $table->string('nama', 20)->unique()->comment('Format: 2024/2025');
            $table->year('tahun_mulai')->comment('Tahun mulai: 2024');
            $table->year('tahun_selesai')->comment('Tahun selesai: 2025');
            $table->enum('semester_aktif', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->date('tanggal_mulai')->comment('Tanggal mulai tahun pelajaran');
            $table->date('tanggal_selesai')->comment('Tanggal selesai tahun pelajaran');
            $table->boolean('is_active')->default(false)->comment('Hanya 1 tahun pelajaran yang aktif');
            $table->enum('status', ['aktif', 'non-aktif', 'selesai'])->default('non-aktif');
            $table->integer('kuota_ppdb')->nullable()->comment('Kuota penerimaan siswa baru dari PPDB');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('kurikulum_id');
            $table->index('nama');
            $table->index('is_active');
            $table->index('status');
            $table->index(['tahun_mulai', 'tahun_selesai']);
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('tahun_pelajaran');
        
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kurikulum_id')->constrained('kurikulum')->onDelete('restrict');
            $table->string('nama', 20)->unique()->comment('Format: 2024/2025');
            $table->year('tahun_mulai')->comment('Tahun mulai: 2024');
            $table->year('tahun_selesai')->comment('Tahun selesai: 2025');
            $table->enum('semester_aktif', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->date('tanggal_mulai')->comment('Tanggal mulai tahun pelajaran');
            $table->date('tanggal_selesai')->comment('Tanggal selesai tahun pelajaran');
            $table->boolean('is_active')->default(false)->comment('Hanya 1 tahun pelajaran yang aktif');
            $table->enum('status', ['aktif', 'non-aktif', 'selesai'])->default('non-aktif');
            $table->integer('kuota_ppdb')->nullable()->comment('Kuota penerimaan siswa baru dari PPDB');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('kurikulum_id');
            $table->index('nama');
            $table->index('is_active');
            $table->index('status');
            $table->index(['tahun_mulai', 'tahun_selesai']);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
