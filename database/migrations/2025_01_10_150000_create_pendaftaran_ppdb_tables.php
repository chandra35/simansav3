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
        // Tabel Pendaftaran PPDB
        Schema::create('pendaftaran_ppdb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_pendaftaran', 20)->unique();
            $table->uuid('tahun_pelajaran_id')->nullable();
            
            // Data NISN & Identitas
            $table->string('nisn', 20)->nullable()->index();
            $table->string('nik', 20)->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            
            // Alamat Calon Siswa
            $table->text('alamat')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            
            // Kontak
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();
            
            // Data Asal Sekolah
            $table->string('asal_sekolah')->nullable();
            $table->string('npsn_asal_sekolah', 20)->nullable();
            $table->text('alamat_asal_sekolah')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('no_ijazah', 50)->nullable();
            $table->string('no_skhun', 50)->nullable();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable();
            
            // Data Ayah
            $table->string('nama_ayah')->nullable();
            $table->string('nik_ayah', 20)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('penghasilan_ayah', 50)->nullable();
            $table->string('no_hp_ayah', 20)->nullable();
            
            // Data Ibu
            $table->string('nama_ibu')->nullable();
            $table->string('nik_ibu', 20)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('penghasilan_ibu', 50)->nullable();
            $table->string('no_hp_ibu', 20)->nullable();
            
            // Data Wali (opsional)
            $table->string('nama_wali')->nullable();
            $table->string('nik_wali', 20)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->string('penghasilan_wali', 50)->nullable();
            $table->string('no_hp_wali', 20)->nullable();
            $table->string('hubungan_wali', 50)->nullable();
            $table->text('alamat_orangtua')->nullable();
            
            // Pilihan Jurusan
            $table->string('jurusan_pilihan_1', 100)->nullable();
            $table->string('jurusan_pilihan_2', 100)->nullable();
            
            // Jalur Pendaftaran
            $table->enum('jalur_pendaftaran', ['reguler', 'prestasi', 'afirmasi', 'zonasi'])->default('reguler');
            
            // Status Pendaftaran
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected', 'accepted', 'enrolled'])->default('draft');
            $table->text('catatan_verifikasi')->nullable();
            $table->uuid('diverifikasi_oleh')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->string('diterima_di_jurusan', 100)->nullable();
            
            // Files
            $table->string('pas_foto')->nullable();
            
            // Token untuk akses edit
            $table->string('token', 100)->unique();
            
            // Step tracking
            $table->tinyInteger('step_terakhir')->default(1);
            $table->json('data_sementara')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->nullOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // Tabel Dokumen Pendaftaran
        Schema::create('dokumen_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pendaftaran_id');
            $table->string('jenis_dokumen', 50);
            $table->string('nama_file');
            $table->string('path_file');
            $table->integer('ukuran_file')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->enum('status_verifikasi', ['pending', 'valid', 'invalid', 'reupload'])->default('pending');
            $table->text('catatan')->nullable();
            $table->uuid('diverifikasi_oleh')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('pendaftaran_id')->references('id')->on('pendaftaran_ppdb')->cascadeOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id')->on('users')->nullOnDelete();
            
            // Index
            $table->index(['pendaftaran_id', 'jenis_dokumen']);
        });

        // Tabel Jurusan untuk pilihan pendaftaran
        Schema::create('jurusan_ppdb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('kuota')->default(0);
            $table->integer('terisi')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Tabel Pengaturan PPDB
        Schema::create('pengaturan_ppdb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->date('tanggal_buka')->nullable();
            $table->date('tanggal_tutup')->nullable();
            $table->date('tanggal_pengumuman')->nullable();
            $table->date('tanggal_daftar_ulang_mulai')->nullable();
            $table->date('tanggal_daftar_ulang_selesai')->nullable();
            $table->text('persyaratan')->nullable();
            $table->text('alur_pendaftaran')->nullable();
            $table->text('kontak_info')->nullable();
            $table->boolean('pendaftaran_dibuka')->default(false);
            $table->decimal('biaya_pendaftaran', 12, 2)->default(0);
            $table->text('rekening_pembayaran')->nullable();
            $table->json('dokumen_wajib')->nullable();
            $table->json('jalur_tersedia')->nullable();
            $table->timestamps();
            
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_ppdb');
        Schema::dropIfExists('jurusan_ppdb');
        Schema::dropIfExists('dokumen_pendaftaran');
        Schema::dropIfExists('pendaftaran_ppdb');
    }
};
