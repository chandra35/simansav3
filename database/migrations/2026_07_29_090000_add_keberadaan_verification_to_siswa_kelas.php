<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->timestamp('keberadaan_diverifikasi_at')
                ->nullable()
                ->after('catatan_perpindahan')
                ->comment('Waktu keberadaan fisik siswa di rombel diverifikasi');
            $table->foreignUuid('keberadaan_diverifikasi_by')
                ->nullable()
                ->after('keberadaan_diverifikasi_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropForeign(['keberadaan_diverifikasi_by']);
            $table->dropColumn([
                'keberadaan_diverifikasi_at',
                'keberadaan_diverifikasi_by',
            ]);
        });
    }
};
