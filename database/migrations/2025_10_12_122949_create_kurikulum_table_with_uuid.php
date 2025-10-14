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
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique()->comment('KTSP, K13, MERDEKA');
            $table->string('nama_kurikulum', 100)->comment('Nama lengkap kurikulum');
            $table->text('deskripsi')->nullable();
            $table->year('tahun_berlaku')->comment('Tahun mulai berlaku kurikulum');
            $table->boolean('has_jurusan')->default(true)->comment('Apakah kurikulum ini memiliki peminatan/jurusan');
            $table->boolean('is_active')->default(true)->comment('Status aktif kurikulum');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('kode');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum');
    }
};
