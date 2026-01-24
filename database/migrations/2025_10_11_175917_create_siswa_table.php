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
        Schema::create('siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('nisn')->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            
            // Data diri siswa
            $table->string('nik')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('jumlah_saudara')->nullable();
            $table->integer('anak_ke')->nullable();
            $table->string('hobi')->nullable();
            $table->string('cita_cita')->nullable();
            $table->string('nomor_hp')->nullable();
            
            // Alamat siswa
            $table->boolean('alamat_sama_ortu')->default(true);
            $table->text('alamat_siswa')->nullable();
            $table->string('rt_siswa')->nullable();
            $table->string('rw_siswa')->nullable();
            $table->char('provinsi_id_siswa', 2)->nullable();
            $table->char('kabupaten_id_siswa', 4)->nullable();
            $table->char('kecamatan_id_siswa', 7)->nullable();
            $table->char('kelurahan_id_siswa', 10)->nullable();
            $table->string('kodepos_siswa')->nullable();
            
            // Status data completion
            $table->boolean('data_ortu_completed')->default(false);
            $table->boolean('data_diri_completed')->default(false);
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provinsi_id_siswa')->references('code')->on('indonesia_provinces');
            $table->foreign('kabupaten_id_siswa')->references('code')->on('indonesia_cities');
            $table->foreign('kecamatan_id_siswa')->references('code')->on('indonesia_districts');
            $table->foreign('kelurahan_id_siswa')->references('code')->on('indonesia_villages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
