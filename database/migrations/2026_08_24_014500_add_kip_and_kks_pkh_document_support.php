<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('siswa', 'nomor_kip')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->string('nomor_kip', 50)->nullable()->after('nomor_hp');
            });
        }

        DB::statement("ALTER TABLE dokumen_siswa MODIFY COLUMN jenis_dokumen ENUM('kk', 'ijazah_smp', 'kip', 'pkh', 'sktm', 'lainnya') NOT NULL");
    }

    public function down(): void
    {
        DB::table('dokumen_siswa')->where('jenis_dokumen', 'pkh')->update(['jenis_dokumen' => 'lainnya']);
        DB::statement("ALTER TABLE dokumen_siswa MODIFY COLUMN jenis_dokumen ENUM('kk', 'ijazah_smp', 'kip', 'sktm', 'lainnya') NOT NULL");

        if (Schema::hasColumn('siswa', 'nomor_kip')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropColumn('nomor_kip');
            });
        }
    }
};
