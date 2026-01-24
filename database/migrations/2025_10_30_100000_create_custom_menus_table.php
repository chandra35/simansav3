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
        Schema::create('custom_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('menu_group')->nullable()->index(); // akademik, administrasi, hotspot, dll
            $table->enum('content_type', ['general', 'personal'])->default('general'); // general = sama semua, personal = beda per siswa
            $table->text('konten')->nullable(); // Konten HTML dari TinyMCE
            $table->json('custom_fields')->nullable(); // Definisi field dinamis untuk content_type = personal
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->char('created_by', 36)->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_menus');
    }
};
