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
        Schema::create('prestasi_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('tahun_pelajaran_id');
            $table->string('nama_prestasi');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['akademik', 'non_akademik', 'keagamaan', 'olahraga', 'seni', 'teknologi', 'lainnya']);
            $table->enum('tingkat', ['sekolah', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional']);
            $table->enum('peringkat', ['juara_1', 'juara_2', 'juara_3', 'harapan_1', 'harapan_2', 'harapan_3', 'peserta', 'finalis', 'lainnya']);
            $table->string('penyelenggara');
            $table->date('tanggal_prestasi');
            $table->string('tempat')->nullable();
            $table->string('nomor_sertifikat')->nullable();
            $table->string('file_sertifikat')->nullable();
            $table->string('foto')->nullable();
            $table->uuid('pembina_id')->nullable(); // GTK yang membina
            $table->boolean('is_verified')->default(false);
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->index(['jenis', 'tingkat']);
            $table->index('tanggal_prestasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_siswa');
    }
};
