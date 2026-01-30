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
        Schema::create('nilai_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('mata_pelajaran_id');
            $table->uuid('tahun_pelajaran_id');
            $table->integer('semester'); // 1-5 untuk kelas X sem 1, X sem 2, XI sem 1, XI sem 2, XII sem 1
            
            // Nilai
            $table->decimal('nilai', 5, 2)->nullable(); // Nilai akhir/rapor
            $table->decimal('nilai_pengetahuan', 5, 2)->nullable();
            $table->decimal('nilai_keterampilan', 5, 2)->nullable();
            
            // Predikat
            $table->string('predikat', 5)->nullable(); // A/B/C/D/E
            
            // Deskripsi (opsional untuk K13/Merdeka)
            $table->text('deskripsi_pengetahuan')->nullable();
            $table->text('deskripsi_keterampilan')->nullable();
            
            // Metadata upload
            $table->string('sumber_data')->default('manual'); // manual, import_excel
            $table->timestamp('imported_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->onDelete('cascade');
                
            $table->foreign('mata_pelajaran_id')
                ->references('id')
                ->on('mata_pelajaran')
                ->onDelete('cascade');
                
            $table->foreign('tahun_pelajaran_id')
                ->references('id')
                ->on('tahun_pelajaran')
                ->onDelete('cascade');
            
            // Unique constraint: 1 siswa, 1 mapel, 1 tahun pelajaran, 1 semester = 1 nilai
            $table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_pelajaran_id', 'semester'], 'nilai_unique');
            
            // Indexes
            $table->index('semester');
            $table->index('nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_siswa');
    }
};
