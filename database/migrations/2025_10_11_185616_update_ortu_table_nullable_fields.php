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
        Schema::table('ortu', function (Blueprint $table) {
            // Make nama_ayah and nama_ibu nullable
            $table->string('nama_ayah')->nullable()->change();
            $table->string('nama_ibu')->nullable()->change();
            
            // Make address fields nullable
            $table->text('alamat_ortu')->nullable()->change();
            $table->string('rt_ortu')->nullable()->change();
            $table->string('rw_ortu')->nullable()->change();
            $table->string('kodepos')->nullable()->change();
            
            // Make foreign keys nullable
            $table->char('provinsi_id', 2)->nullable()->change();
            $table->char('kabupaten_id', 4)->nullable()->change();
            $table->char('kecamatan_id', 7)->nullable()->change();
            $table->char('kelurahan_id', 10)->nullable()->change();
            
            // Make status fields nullable with default
            $table->enum('status_ayah', ['masih_hidup', 'meninggal'])->nullable()->change();
            $table->enum('status_ibu', ['masih_hidup', 'meninggal'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ortu', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->string('nama_ayah')->nullable(false)->change();
            $table->string('nama_ibu')->nullable(false)->change();
            $table->text('alamat_ortu')->nullable(false)->change();
            $table->string('rt_ortu')->nullable(false)->change();
            $table->string('rw_ortu')->nullable(false)->change();
            $table->string('kodepos')->nullable(false)->change();
            $table->char('provinsi_id', 2)->nullable(false)->change();
            $table->char('kabupaten_id', 4)->nullable(false)->change();
            $table->char('kecamatan_id', 7)->nullable(false)->change();
            $table->char('kelurahan_id', 10)->nullable(false)->change();
            $table->enum('status_ayah', ['masih_hidup', 'meninggal'])->nullable(false)->change();
            $table->enum('status_ibu', ['masih_hidup', 'meninggal'])->nullable(false)->change();
        });
    }
};
