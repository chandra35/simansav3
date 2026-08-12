<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('siswa')
            ->where('status_siswa', 'aktif')
            ->whereNull('deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('siswa_kelas')
                    ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                    ->where('siswa_kelas.status', 'lulus')
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->update([
                'status_siswa' => 'lulus',
                'kelas_saat_ini_id' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Pengarsipan kelulusan adalah histori final dan tidak aman dikembalikan
        // otomatis menjadi siswa aktif saat rollback kode.
    }
};
