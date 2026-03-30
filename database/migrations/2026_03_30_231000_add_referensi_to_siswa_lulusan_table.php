<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->foreignUuid('referensi_perguruan_tinggi_id')
                ->nullable()
                ->after('tahun_pelajaran_id')
                ->constrained('referensi_perguruan_tinggi')
                ->nullOnDelete();
            $table->string('nama_universitas_manual')->nullable()->after('nama_universitas');
        });
    }

    public function down(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referensi_perguruan_tinggi_id');
            $table->dropColumn('nama_universitas_manual');
        });
    }
};
