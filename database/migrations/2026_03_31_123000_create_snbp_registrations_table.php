<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snbp_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('snbp_menu_id')->constrained('snbp_menus')->cascadeOnDelete();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('nomor_pendaftaran', 50);
            $table->string('check_status', 30)->default('belum_dicek');
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_check_message')->nullable();
            $table->json('last_check_payload')->nullable();
            $table->timestamps();

            $table->unique(['snbp_menu_id', 'siswa_id'], 'snbp_registrations_menu_siswa_unique');
            $table->unique(['siswa_id', 'tahun_pelajaran_id'], 'snbp_registrations_siswa_tahun_unique');
            $table->index(['tahun_pelajaran_id', 'check_status'], 'snbp_registrations_tahun_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snbp_registrations');
    }
};
