<?php
/**
 * Check mapel di database
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;

echo "=== DAFTAR MATA PELAJARAN DI DATABASE ===\n\n";

$mapels = MataPelajaran::where('is_active', true)
    ->orderBy('kode_mapel')
    ->get(['kode_mapel', 'nama_mapel', 'kelompok']);

echo "Total: " . $mapels->count() . " mapel aktif\n\n";

echo str_pad("KODE", 15) . str_pad("NAMA", 40) . "KELOMPOK\n";
echo str_repeat("-", 65) . "\n";

foreach ($mapels as $m) {
    echo str_pad($m->kode_mapel, 15) . str_pad($m->nama_mapel, 40) . $m->kelompok . "\n";
}

// Cek mapel yang diperlukan config
echo "\n\n=== CEK MAPEL DARI CONFIG ===\n\n";

$configSem13 = config('nilai.urutan_mapel_sem_1_3', []);
$configSem4 = config('nilai.urutan_mapel_sem_4', []);
$configSem5 = config('nilai.urutan_mapel_sem_5', []);
$allConfig = array_unique(array_merge($configSem13, $configSem4, $configSem5));

echo "Mapel Semester 1-3: " . count($configSem13) . "\n";
echo "Mapel Semester 4: " . count($configSem4) . "\n";
echo "Mapel Semester 5: " . count($configSem5) . "\n";
echo "Total Unique: " . count($allConfig) . "\n\n";

$mapelByKode = $mapels->keyBy('kode_mapel');

$missing = [];

echo "Status Semester 1-3:\n";
foreach ($configSem13 as $kode) {
    $status = isset($mapelByKode[$kode]) ? '✓ OK' : '✗ MISSING';
    echo "  {$kode}: {$status}\n";
    if (!isset($mapelByKode[$kode])) {
        $missing[] = $kode;
    }
}

echo "\nStatus Semester 4:\n";
foreach ($configSem4 as $kode) {
    $status = isset($mapelByKode[$kode]) ? '✓ OK' : '✗ MISSING';
    echo "  {$kode}: {$status}\n";
    if (!isset($mapelByKode[$kode]) && !in_array($kode, $missing)) {
        $missing[] = $kode;
    }
}

echo "\nStatus Semester 5:\n";
foreach ($configSem5 as $kode) {
    $status = isset($mapelByKode[$kode]) ? '✓ OK' : '✗ MISSING';
    echo "  {$kode}: {$status}\n";
    if (!isset($mapelByKode[$kode]) && !in_array($kode, $missing)) {
        $missing[] = $kode;
    }
}

if (!empty($missing)) {
    echo "\n⚠️  Mapel yang belum ada: " . implode(', ', $missing) . "\n";
} else {
    echo "\n✓ Semua mapel dari config sudah ada di database!\n";
}
