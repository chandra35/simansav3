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
        // Main SNBP Menu table
        Schema::create('snbp_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_menu'); // Flexible name: SNBP, SNMPTN, etc.
            $table->uuid('tahun_pelajaran_id');
            $table->longText('konten_eligible')->nullable(); // Content for eligible students
            $table->longText('konten_not_eligible')->nullable(); // Content for not eligible students
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')
                  ->references('id')
                  ->on('tahun_pelajaran')
                  ->onDelete('cascade');

            // One menu per tahun pelajaran
            $table->unique('tahun_pelajaran_id');
        });

        // Pivot table for SNBP-Siswa assignment
        Schema::create('snbp_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('snbp_menu_id');
            $table->uuid('siswa_id');
            $table->boolean('is_eligible')->default(false); // true = eligible, false = not eligible
            $table->timestamps();

            $table->foreign('snbp_menu_id')
                  ->references('id')
                  ->on('snbp_menus')
                  ->onDelete('cascade');

            $table->foreign('siswa_id')
                  ->references('id')
                  ->on('siswa')
                  ->onDelete('cascade');

            // One assignment per siswa per menu
            $table->unique(['snbp_menu_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snbp_siswa');
        Schema::dropIfExists('snbp_menus');
    }
};
