<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id')->nullable()->unique();
            $table->string('angkatan', 30)->nullable()->index();
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->index();
            $table->string('nama_lengkap', 160)->index();
            $table->string('nisn', 20)->nullable()->index();
            $table->string('nik', 20)->nullable()->index();
            $table->string('jenis_kelamin', 5)->nullable()->index();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nomor_hp', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kabupaten_kota', 120)->nullable()->index();
            $table->string('provinsi', 120)->nullable()->index();
            $table->string('status_setelah_lulus', 30)->default('belum_terdata')->index();
            $table->string('institusi_lanjutan', 180)->nullable();
            $table->string('program_studi', 180)->nullable();
            $table->string('pekerjaan', 160)->nullable();
            $table->string('instansi', 180)->nullable();
            $table->string('status_verifikasi', 30)->default('belum_diverifikasi')->index();
            $table->string('sumber_data', 30)->default('manual')->index();
            $table->string('referensi_sumber', 190)->nullable();
            $table->timestamp('last_profile_update_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->nullOnDelete();
            $table->index(['tahun_lulus', 'status_setelah_lulus'], 'alumni_year_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_profiles');
    }
};
