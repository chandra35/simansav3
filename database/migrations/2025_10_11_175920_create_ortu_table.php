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
        Schema::create('ortu', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->string('no_kk')->nullable();
            
            // Data Ayah
            $table->enum('status_ayah', ['masih_hidup', 'meninggal']);
            $table->string('nik_ayah')->nullable();
            $table->string('nama_ayah');
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();
            $table->string('hp_ayah')->nullable();
            
            // Data Ibu
            $table->enum('status_ibu', ['masih_hidup', 'meninggal']);
            $table->string('nik_ibu')->nullable();
            $table->string('nama_ibu');
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();
            $table->string('hp_ibu')->nullable();
            
            // Alamat Ortu
            $table->text('alamat_ortu');
            $table->string('rt_ortu');
            $table->string('rw_ortu');
            $table->char('provinsi_id', 2);
            $table->char('kabupaten_id', 4);
            $table->char('kecamatan_id', 7);
            $table->char('kelurahan_id', 10);
            $table->string('kodepos');
            
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('provinsi_id')->references('code')->on('indonesia_provinces');
            $table->foreign('kabupaten_id')->references('code')->on('indonesia_cities');
            $table->foreign('kecamatan_id')->references('code')->on('indonesia_districts');
            $table->foreign('kelurahan_id')->references('code')->on('indonesia_villages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ortu');
    }
};
