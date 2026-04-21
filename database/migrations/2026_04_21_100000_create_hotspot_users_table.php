<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username', 64)->unique(); // nisn / nik / custom
            $table->enum('role', ['guru', 'siswa', 'tamu'])->index();
            $table->string('display_name', 150)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('expired_at')->nullable(); // untuk tamu
            $table->string('keterangan', 255)->nullable(); // untuk tamu: nama, keperluan
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 20)->default('pending'); // pending, synced, error
            $table->text('sync_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'is_active']);
            $table->index('sync_status');
        });

        // Tabel radcheck di DB radius dikelola FreeRADIUS
        // Tabel ini hanya untuk tracking di sisi Simansa
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_users');
    }
};
