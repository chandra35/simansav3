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
        Schema::create('gtks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            
            // Data utama (wajib)
            $table->string('nama_lengkap');
            $table->string('nik', 16)->unique();
            $table->enum('jenis_kelamin', ['L', 'P']);
            
            // Data tambahan (tidak wajib)
            $table->string('nuptk', 16)->nullable()->unique();
            $table->string('nip', 18)->nullable()->unique();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            
            // Data kontak
            $table->string('email')->nullable();
            $table->string('nomor_hp')->nullable();
            
            // Alamat
            $table->text('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->char('provinsi_id', 2)->nullable();
            $table->char('kabupaten_id', 4)->nullable();
            $table->char('kecamatan_id', 7)->nullable();
            $table->char('kelurahan_id', 10)->nullable();
            $table->string('kodepos')->nullable();
            
            // Data kepegawaian
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'GTY', 'PTY', 'Honorer'])->nullable();
            $table->string('jabatan')->nullable();
            $table->date('tmt_kerja')->nullable(); // Tanggal Mulai Tugas
            
            // Status data completion
            $table->boolean('data_diri_completed')->default(false);
            $table->boolean('data_kepegawaian_completed')->default(false);
            
            // Audit fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('provinsi_id')->references('code')->on('indonesia_provinces');
            $table->foreign('kabupaten_id')->references('code')->on('indonesia_cities');
            $table->foreign('kecamatan_id')->references('code')->on('indonesia_districts');
            $table->foreign('kelurahan_id')->references('code')->on('indonesia_villages');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtks');
    }
};
