<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas', 'jenis_kelas')) {
                $table->string('jenis_kelas', 30)
                    ->default('reguler')
                    ->after('tingkat')
                    ->comment('reguler|matrikulasi');
                $table->index(['tahun_pelajaran_id', 'jenis_kelas']);
            }
        });

        Schema::table('dokumen_siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumen_siswa', 'ppdb_calon_dokumen_id')) {
                $table->uuid('ppdb_calon_dokumen_id')->nullable()->after('siswa_id');
                $table->string('ppdb_jenis_dokumen', 80)->nullable()->after('ppdb_calon_dokumen_id');
                $table->string('ppdb_source_disk', 30)->nullable()->after('ppdb_jenis_dokumen');
                $table->string('ppdb_source_url', 700)->nullable()->after('ppdb_source_disk');
                $table->timestamp('ppdb_imported_at')->nullable()->after('ppdb_source_url');

                $table->index('ppdb_calon_dokumen_id');
                $table->index(['siswa_id', 'ppdb_calon_dokumen_id'], 'dokumen_siswa_ppdb_source_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('dokumen_siswa', 'ppdb_calon_dokumen_id')) {
                $table->dropIndex('dokumen_siswa_ppdb_source_idx');
                $table->dropIndex(['ppdb_calon_dokumen_id']);
                $table->dropColumn([
                    'ppdb_calon_dokumen_id',
                    'ppdb_jenis_dokumen',
                    'ppdb_source_disk',
                    'ppdb_source_url',
                    'ppdb_imported_at',
                ]);
            }
        });

        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'jenis_kelas')) {
                $table->dropIndex(['tahun_pelajaran_id', 'jenis_kelas']);
                $table->dropColumn('jenis_kelas');
            }
        });
    }
};
