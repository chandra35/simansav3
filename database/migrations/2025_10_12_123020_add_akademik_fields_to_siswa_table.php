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
        Schema::table('siswa', function (Blueprint $table) {
            // Tambah kolom akademik setelah kolom existing
            $table->year('tahun_masuk')->nullable()->after('nisn')->comment('Tahun pertama kali masuk sekolah');
            $table->enum('asal_siswa', ['ppdb', 'mutasi_masuk', 'reguler'])->default('reguler')->after('tahun_masuk')->comment('Asal siswa: PPDB, Mutasi, atau Input Manual');
            $table->enum('status_siswa', ['aktif', 'lulus', 'keluar', 'mutasi_keluar', 'alumni'])->default('aktif')->after('asal_siswa')->comment('Status siswa saat ini');
            $table->foreignUuid('kelas_saat_ini_id')->nullable()->after('status_siswa')->constrained('kelas')->onDelete('set null')->comment('Kelas siswa saat ini untuk quick access');
            $table->foreignUuid('jurusan_pilihan_id')->nullable()->after('kelas_saat_ini_id')->constrained('jurusan')->onDelete('set null')->comment('Pilihan jurusan siswa (untuk K13/KTSP)');
            $table->bigInteger('ppdb_id')->unsigned()->nullable()->after('jurusan_pilihan_id')->comment('Reference ID dari database PPDB (jika dari PPDB)');
            $table->timestamp('ppdb_imported_at')->nullable()->after('ppdb_id')->comment('Waktu import data dari PPDB');
            
            // Indexes
            $table->index('tahun_masuk');
            $table->index('asal_siswa');
            $table->index('status_siswa');
            $table->index('kelas_saat_ini_id');
            $table->index('jurusan_pilihan_id');
            $table->index('ppdb_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['kelas_saat_ini_id']);
            $table->dropForeign(['jurusan_pilihan_id']);
            
            // Drop indexes
            $table->dropIndex(['tahun_masuk']);
            $table->dropIndex(['asal_siswa']);
            $table->dropIndex(['status_siswa']);
            $table->dropIndex(['kelas_saat_ini_id']);
            $table->dropIndex(['jurusan_pilihan_id']);
            $table->dropIndex(['ppdb_id']);
            
            // Drop columns
            $table->dropColumn([
                'tahun_masuk',
                'asal_siswa',
                'status_siswa',
                'kelas_saat_ini_id',
                'jurusan_pilihan_id',
                'ppdb_id',
                'ppdb_imported_at'
            ]);
        });
    }
};
