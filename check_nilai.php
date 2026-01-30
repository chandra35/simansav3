<?php
/**
 * Debug script to check nilai_siswa data
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NilaiSiswa;
use App\Models\TahunPelajaran;

echo "=== CHECK NILAI SISWA DATA ===\n\n";

$total = NilaiSiswa::count();
echo "Total nilai: $total\n\n";

if ($total == 0) {
    echo "Tidak ada data nilai di database!\n";
    exit;
}

$nilai = NilaiSiswa::first();
echo "Sample data:\n";
echo "  Semester: " . $nilai->semester . "\n";
echo "  Tahun Pelajaran ID: " . $nilai->tahun_pelajaran_id . "\n";
echo "  Siswa ID: " . $nilai->siswa_id . "\n";
echo "  Mapel ID: " . $nilai->mata_pelajaran_id . "\n";
echo "  Nilai: " . $nilai->nilai . "\n";

echo "\nNilai per semester:\n";
for ($s = 1; $s <= 5; $s++) {
    $count = NilaiSiswa::where('semester', $s)->count();
    echo "  Semester $s: $count\n";
}

echo "\nTahun Pelajaran yang digunakan:\n";
$tahunIds = NilaiSiswa::distinct()->pluck('tahun_pelajaran_id');
foreach ($tahunIds as $tid) {
    $tp = TahunPelajaran::find($tid);
    $cnt = NilaiSiswa::where('tahun_pelajaran_id', $tid)->count();
    if ($tp) {
        echo "  {$tp->nama} ({$tp->id}): $cnt nilai\n";
    } else {
        echo "  ID $tid (NOT FOUND): $cnt nilai\n";
    }
}

echo "\nTahun Pelajaran aktif:\n";
$aktif = TahunPelajaran::where('is_active', true)->first();
if ($aktif) {
    echo "  {$aktif->nama} ({$aktif->id})\n";
} else {
    echo "  Tidak ada tahun pelajaran aktif!\n";
}
