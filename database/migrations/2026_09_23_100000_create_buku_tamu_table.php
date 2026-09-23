<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_tamu', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal_kunjungan')->index();
            $table->string('nama', 150);
            $table->text('alamat_instansi');
            $table->string('nomor_hp', 30);
            $table->text('keperluan');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_tamu');
    }
};
