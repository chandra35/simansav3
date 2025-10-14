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
        Schema::create('sekolah', function (Blueprint $table) {
            $table->char('npsn', 8)->primary()->comment('NPSN sebagai Primary Key');
            $table->string('nama', 255)->comment('Nama sekolah');
            $table->enum('status', ['NEGERI', 'SWASTA'])->nullable()->comment('Status sekolah');
            $table->string('bentuk_pendidikan', 20)->nullable()->comment('MA, MTs, SMP, SMA, SMK, dll');
            $table->text('alamat_jalan')->nullable()->comment('Alamat jalan lengkap');
            $table->string('desa_kelurahan', 100)->nullable()->comment('Nama desa/kelurahan');
            $table->string('kecamatan', 100)->nullable()->comment('Nama kecamatan');
            $table->string('kabupaten_kota', 100)->nullable()->comment('Nama kabupaten/kota');
            $table->string('provinsi', 100)->nullable()->comment('Nama provinsi');
            $table->timestamp('last_fetched_at')->nullable()->comment('Timestamp terakhir fetch dari API');
            $table->timestamps();
            
            // Indexes for search performance
            $table->index('nama');
            $table->index('kabupaten_kota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah');
    }
};
