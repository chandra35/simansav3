<?php
/**
 * Check active mapel codes in database
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;

echo "=== MAPEL AKTIF DI DATABASE ===\n\n";

$mapels = MataPelajaran::where('is_active', true)
    ->orderBy('kode_mapel')
    ->get(['kode_mapel', 'nama_mapel']);

foreach ($mapels as $m) {
    echo "  {$m->kode_mapel} => {$m->nama_mapel}\n";
}

echo "\nTotal: " . $mapels->count() . "\n";

// Check specific mapel codes from Excel RDM
echo "\n=== CEK KODE MAPEL RDM ===\n";
$rdmCodes = ['QH', 'AA', 'FIK', 'SKI', 'BAR', 'PP', 'BINDO', 'MTK', 'BING', 'PJOK', 'SEJ', 'SB', 'MULOK PRKW', 'THF', 'BIO', 'KIM', 'FIS', 'INFOP', 'MTL', 'EKO'];

foreach ($rdmCodes as $code) {
    $found = MataPelajaran::where('kode_mapel', $code)->where('is_active', true)->exists();
    $status = $found ? '✓' : '✗ TIDAK ADA';
    echo "  {$code}: {$status}\n";
}
