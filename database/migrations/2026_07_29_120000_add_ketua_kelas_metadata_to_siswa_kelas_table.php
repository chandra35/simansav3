<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->boolean('is_ketua_kelas')->default(false)->after('nomor_urut_absen');
            $table->timestamp('ketua_kelas_mulai_at')->nullable()->after('is_ketua_kelas');
            $table->timestamp('ketua_kelas_selesai_at')->nullable()->after('ketua_kelas_mulai_at');
            $table->foreignUuid('ketua_kelas_ditetapkan_by')
                ->nullable()
                ->after('ketua_kelas_selesai_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(
                ['kelas_id', 'tahun_pelajaran_id', 'status', 'is_ketua_kelas'],
                'siswa_kelas_ketua_aktif_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropIndex('siswa_kelas_ketua_aktif_index');
            $table->dropConstrainedForeignId('ketua_kelas_ditetapkan_by');
            $table->dropColumn([
                'is_ketua_kelas',
                'ketua_kelas_mulai_at',
                'ketua_kelas_selesai_at',
            ]);
        });
    }
};
