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
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kurikulum_id');
            $table->uuid('jurusan_id')->nullable();
            
            $table->string('kode_mapel', 10)->unique();
            $table->string('nama_mapel');
            
            // Flexible Grouping untuk semua kurikulum
            $table->string('kelompok', 20)->nullable(); // A/B/C untuk K13, umum/pilihan untuk Merdeka
            $table->string('kategori', 50)->nullable(); // umum/peminatan/lintas_minat/muatan_lokal
            
            // Assessment (flexible untuk semua kurikulum)
            $table->integer('kkm')->nullable(); // K13 & KTSP, default 75
            $table->text('capaian_pembelajaran')->nullable(); // Kurikulum Merdeka
            
            // MAPEL AGAMA - FLEXIBLE untuk Kemenag & Umum
            $table->boolean('is_mapel_agama')->default(false);
            $table->enum('jenis_agama', [
                'islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu'
            ])->nullable();
            
            // KHUSUS MADRASAH (Kemenag) - Rumpun PAI
            $table->boolean('is_rumpun_pai')->default(false);
            $table->enum('sub_pai', [
                'quran_hadits', 'akidah_akhlak', 'fikih', 'ski'
            ])->nullable();
            
            // Bahasa Arab (wajib di Madrasah, pilihan di Umum)
            $table->boolean('is_bahasa_arab')->default(false);
            
            // Kurikulum Merdeka specific
            $table->boolean('is_mapel_pilihan')->default(false);
            $table->boolean('is_projek_p5')->default(false);
            
            // KTSP & Local
            $table->boolean('is_muatan_lokal')->default(false);
            
            // Common fields
            $table->integer('jam_pelajaran')->default(2);
            $table->json('tingkat')->nullable(); // [10,11,12] atau ["X","XI","XII"]
            $table->json('semester')->nullable(); // [1,2] atau ["ganjil","genap"]
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('kurikulum_id')
                ->references('id')
                ->on('kurikulum')
                ->onDelete('restrict');
                
            $table->foreign('jurusan_id')
                ->references('id')
                ->on('jurusan')
                ->onDelete('set null');
            
            // Indexes untuk performance
            $table->index('kode_mapel');
            $table->index('kelompok');
            $table->index('kategori');
            $table->index('is_active');
            $table->index(['is_mapel_agama', 'jenis_agama']);
            $table->index(['is_rumpun_pai', 'sub_pai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_pelajaran');
    }
};
