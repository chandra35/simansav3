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
        Schema::create('custom_menu_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('custom_menu_id', 36);
            $table->char('siswa_id', 36);
            $table->json('personal_data')->nullable(); // Data spesifik per siswa (username, password, dll)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('custom_menu_id')->references('id')->on('custom_menus')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');

            // Indexes
            $table->index('custom_menu_id');
            $table->index('siswa_id');
            $table->index('is_read');

            // Unique constraint: satu siswa hanya bisa punya satu record per menu
            $table->unique(['custom_menu_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_menu_siswa');
    }
};
