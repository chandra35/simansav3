<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * STEP 1: Convert kurikulum table to UUID
     * This must run FIRST before other tables since they have FK to kurikulum
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop and recreate kurikulum table with UUID
        Schema::dropIfExists('kurikulum');
        
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

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Revert back to bigInteger ID
        Schema::dropIfExists('kurikulum');
        
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
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

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
