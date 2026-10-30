<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->boolean('is_sekretaris_kelas')->default(false)->after('is_ketua_kelas');
            $table->timestamp('sekretaris_kelas_mulai_at')->nullable()->after('ketua_kelas_mulai_at');
            $table->timestamp('sekretaris_kelas_selesai_at')->nullable()->after('ketua_kelas_selesai_at');
            $table->foreignUuid('sekretaris_kelas_ditetapkan_by')
                ->nullable()
                ->after('ketua_kelas_ditetapkan_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(
                ['kelas_id', 'tahun_pelajaran_id', 'status', 'is_sekretaris_kelas'],
                'siswa_kelas_sekretaris_aktif_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropIndex('siswa_kelas_sekretaris_aktif_index');
            $table->dropConstrainedForeignId('sekretaris_kelas_ditetapkan_by');
            $table->dropColumn([
                'is_sekretaris_kelas',
                'sekretaris_kelas_mulai_at',
                'sekretaris_kelas_selesai_at',
            ]);
        });
    }
};
