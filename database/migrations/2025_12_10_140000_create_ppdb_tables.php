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
        // Create sliders table
        Schema::create('sliders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('gambar');
            $table->string('link')->nullable();
            $table->integer('urutan')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Create beritas table
        Schema::create('beritas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->longText('konten');
            $table->text('excerpt')->nullable();
            $table->string('gambar')->nullable();
            $table->string('kategori')->nullable();
            $table->string('penulis')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->boolean('shared_to_facebook')->default(false);
            $table->string('facebook_post_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Create jadwal_ppdb table
        Schema::create('jadwal_ppdb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->string('warna')->default('#007bff');
            $table->string('icon')->default('fas fa-calendar');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create site_settings table
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            
            // General Settings
            $table->string('site_name')->default('PPDB Online');
            $table->string('site_tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            
            // Contact Info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            
            // Facebook Integration
            $table->string('facebook_page_id')->nullable();
            $table->text('facebook_access_token')->nullable();
            
            // Landing Page Content
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_button_text')->default('Daftar Sekarang');
            $table->string('hero_button_link')->nullable();
            
            // About Section
            $table->text('about_content')->nullable();
            $table->string('about_image')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Theme Settings
            $table->string('primary_color')->default('#007bff');
            $table->string('secondary_color')->default('#6c757d');
            $table->string('accent_color')->default('#28a745');
            
            // Maps
            $table->string('maps_latitude')->nullable();
            $table->string('maps_longitude')->nullable();
            $table->text('maps_embed')->nullable();
            
            // Registration Settings
            $table->boolean('registration_open')->default(true);
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->text('registration_closed_message')->nullable();
            
            // Footer
            $table->text('footer_text')->nullable();
            $table->text('footer_copyright')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('jadwal_ppdb');
        Schema::dropIfExists('beritas');
        Schema::dropIfExists('sliders');
    }
};
