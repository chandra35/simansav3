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
        Schema::create('jurusan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kurikulum_id')->constrained('kurikulum')->onDelete('cascade');
            $table->string('kode_jurusan', 20)->comment('IPA, IPS, BAHASA, UMUM, dll');
            $table->string('nama_jurusan', 100)->comment('Nama lengkap jurusan');
            $table->string('singkatan', 20)->nullable()->comment('Singkatan jurusan');
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0)->comment('Urutan tampilan jurusan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('kurikulum_id');
            $table->index('kode_jurusan');
            $table->index('is_active');
            
            // Unique constraint untuk kombinasi kurikulum dan kode jurusan
            $table->unique(['kurikulum_id', 'kode_jurusan'], 'kurikulum_jurusan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
