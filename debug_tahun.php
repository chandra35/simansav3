<?php
/**
 * Debug tahun pelajaran nilai semester 4
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TahunPelajaran;
use App\Models\NilaiSiswa;

echo "=== DEBUG TAHUN PELAJARAN ===\n\n";

// Tahun aktif
$tahunAktif = TahunPelajaran::where('is_active', true)->first();
echo "Tahun Aktif: " . ($tahunAktif ? $tahunAktif->nama : 'TIDAK ADA') . "\n";
if ($tahunAktif) {
    echo "  ID: " . $tahunAktif->id . "\n";
}

// Tahun pelajaran dari nilai semester 4
echo "\nTahun Pelajaran yang punya nilai semester 4:\n";
$tahunIds = NilaiSiswa::where('semester', 4)
    ->distinct()
    ->pluck('tahun_pelajaran_id');

foreach ($tahunIds as $id) {
    $tp = TahunPelajaran::find($id);
    $count = NilaiSiswa::where('semester', 4)->where('tahun_pelajaran_id', $id)->count();
    echo "  - " . ($tp ? $tp->nama : 'NULL') . " (ID: {$id}) - {$count} nilai\n";
}

// Cek apakah tahun aktif sama dengan tahun nilai
echo "\n\nKesimpulan:\n";
if ($tahunAktif && $tahunIds->contains($tahunAktif->id)) {
    echo "✓ Tahun aktif SAMA dengan tahun yang punya nilai semester 4\n";
} else {
    echo "✗ Tahun aktif BERBEDA dengan tahun yang punya nilai semester 4!\n";
    echo "  -> Ini menyebabkan data kosong karena filter tahun pelajaran\n";
}
