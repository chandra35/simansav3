<?php
/**
 * Check nilai di database
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NilaiSiswa;

echo "=== CEK DATA NILAI DI DATABASE ===\n\n";

echo "Total nilai di DB: " . NilaiSiswa::count() . "\n\n";

echo "Per Semester:\n";
for ($i = 1; $i <= 5; $i++) {
    $count = NilaiSiswa::where('semester', $i)->count();
    echo "  Semester {$i}: {$count}\n";
}

echo "\n\nSample data semester 4:\n";
$samples = NilaiSiswa::where('semester', 4)
    ->with(['siswa:id,nama_lengkap,nisn', 'mataPelajaran:id,kode_mapel,nama_mapel'])
    ->limit(10)
    ->get();

foreach ($samples as $n) {
    $siswa = $n->siswa ? $n->siswa->nama_lengkap : 'N/A';
    $mapel = $n->mataPelajaran ? $n->mataPelajaran->kode_mapel : 'N/A';
    echo "  - {$siswa} | {$mapel} | Nilai: {$n->nilai}\n";
}

// Cek struktur tabel
echo "\n\nStruktur NilaiSiswa:\n";
$firstRecord = NilaiSiswa::where('semester', 4)->first();
if ($firstRecord) {
    print_r($firstRecord->toArray());
} else {
    echo "Tidak ada data semester 4\n";
}
