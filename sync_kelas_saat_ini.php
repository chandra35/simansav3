<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Syncing kelas_saat_ini_id untuk siswa aktif...\n";

$siswaKelas = DB::table('siswa_kelas')
    ->where('status', 'aktif')
    ->whereNull('deleted_at')
    ->get();

$updated = 0;

foreach ($siswaKelas as $sk) {
    DB::table('siswa')
        ->where('id', $sk->siswa_id)
        ->update(['kelas_saat_ini_id' => $sk->kelas_id]);
    $updated++;
}

echo "Berhasil update {$updated} siswa\n";
echo "Selesai!\n";
