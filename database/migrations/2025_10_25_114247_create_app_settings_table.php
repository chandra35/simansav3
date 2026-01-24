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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identitas Sekolah
            $table->string('nama_sekolah');
            $table->string('npsn', 8);
            
            // Logo (2 files)
            $table->string('logo_kemenag_path')->nullable();
            $table->string('logo_sekolah_path')->nullable();
            
            // Alamat (Laravolt Indonesia)
            $table->text('alamat');
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->char('kelurahan_code', 10);
            $table->char('kecamatan_code', 7);
            $table->char('kota_code', 4);
            $table->char('provinsi_code', 2);
            $table->string('kode_pos', 5);
            
            // Kontak
            $table->string('telepon', 20);
            $table->string('email');
            $table->string('website')->nullable();
            
            // Sosial Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            
            // Kop Surat Configuration
            $table->enum('kop_mode', ['builder', 'custom'])->default('builder');
            $table->json('kop_surat_config')->nullable(); // JSON config untuk builder mode
            $table->string('kop_surat_custom_path')->nullable(); // Path upload custom kop
            $table->integer('kop_margin_top')->default(0); // mm
            $table->integer('kop_height')->default(30); // mm
            
            $table->timestamps();
            
            // Indexes
            $table->index('npsn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
