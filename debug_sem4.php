<?php
/**
 * Debug semester 4 view data
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;
use App\Models\NilaiSiswa;

$semester = 4;

echo "=== DEBUG SEMESTER 4 VIEW ===\n\n";

// Config mapel semester 4
$urutanMapel = config('nilai.urutan_mapel_sem_4');
echo "Config urutan_mapel_sem_4:\n";
print_r($urutanMapel);

echo "\n\nMapel di database dengan kode tersebut:\n";
$mapelList = MataPelajaran::whereIn('kode_mapel', $urutanMapel)
    ->where('is_active', true)
    ->get();

echo "Total ditemukan: " . $mapelList->count() . "\n";
foreach ($mapelList as $m) {
    echo "  - {$m->kode_mapel}: {$m->nama_mapel}\n";
}

// Cek mapel yang tidak ditemukan
echo "\n\nMapel yang TIDAK ditemukan di database:\n";
$foundCodes = $mapelList->pluck('kode_mapel')->toArray();
foreach ($urutanMapel as $kode) {
    if (!in_array($kode, $foundCodes)) {
        echo "  - {$kode} (MISSING)\n";
    }
}

// Cek nilai semester 4 mapel apa saja
echo "\n\nMapel yang punya nilai semester 4:\n";
$mapelWithNilai = NilaiSiswa::where('semester', 4)
    ->with('mataPelajaran')
    ->get()
    ->pluck('mataPelajaran.kode_mapel')
    ->unique()
    ->sort()
    ->values();

foreach ($mapelWithNilai as $kode) {
    echo "  - {$kode}\n";
}
