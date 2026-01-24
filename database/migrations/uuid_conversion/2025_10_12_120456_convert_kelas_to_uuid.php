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
     * STEP 4 (FINAL): Convert kelas and siswa_kelas tables to UUID
     * Depends on: kurikulum, jurusan, tahun_pelajaran (all must be UUID first)
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop siswa_kelas table first (depends on kelas)
        Schema::dropIfExists('siswa_kelas');

        // Drop and recreate kelas table with UUID
        Schema::dropIfExists('kelas');
        
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

        // Recreate siswa_kelas table with UUID foreign keys
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignUuid('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->date('tanggal_masuk')->comment('Tanggal siswa masuk ke kelas ini');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal siswa keluar dari kelas (naik/tinggal/lulus/keluar)');
            $table->enum('status', ['aktif', 'naik_kelas', 'tinggal_kelas', 'lulus', 'keluar'])->default('aktif');
            $table->integer('nomor_urut_absen')->nullable()->comment('Nomor absen siswa di kelas');
            $table->text('catatan_perpindahan')->nullable()->comment('Catatan kenapa pindah kelas');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('siswa_id');
            $table->index('kelas_id');
            $table->index('tahun_pelajaran_id');
            $table->index('status');
            $table->index(['siswa_id', 'tahun_pelajaran_id']);
            
            // Prevent duplicate active enrollment
            $table->unique(['siswa_id', 'kelas_id', 'tahun_pelajaran_id'], 'siswa_kelas_tahun_unique');
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
        Schema::dropIfExists('siswa_kelas');
        Schema::dropIfExists('kelas');
        
        // Recreate with bigInteger IDs
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->foreignId('kurikulum_id')->constrained('kurikulum')->onDelete('restrict');
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->onDelete('set null');
            $table->string('nama_kelas', 50);
            $table->integer('tingkat');
            $table->string('kode_kelas', 20)->unique();
            $table->foreignUuid('wali_kelas_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('kapasitas')->default(36);
            $table->string('ruang_kelas', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tahun_pelajaran_id');
            $table->index('kurikulum_id');
            $table->index('jurusan_id');
            $table->index('tingkat');
            $table->index('kode_kelas');
            $table->index('wali_kelas_id');
            $table->index('is_active');
        });

        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->onDelete('cascade');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'naik_kelas', 'tinggal_kelas', 'lulus', 'keluar'])->default('aktif');
            $table->integer('nomor_urut_absen')->nullable();
            $table->text('catatan_perpindahan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('siswa_id');
            $table->index('kelas_id');
            $table->index('tahun_pelajaran_id');
            $table->index('status');
            $table->index(['siswa_id', 'tahun_pelajaran_id']);
            $table->unique(['siswa_id', 'kelas_id', 'tahun_pelajaran_id'], 'siswa_kelas_tahun_unique');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
