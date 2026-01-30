<?php
/**
 * Add missing mapel codes from Excel
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;

echo "=== TAMBAH KODE MAPEL YANG HILANG ===\n\n";

// Kode mapel yang tidak ditemukan
$missingCodes = [
    'IPAT' => ['nama' => 'IPA Terapan', 'kelompok' => 'C'],
    'IPST' => ['nama' => 'IPS Terapan', 'kelompok' => 'C'],
    'INFO' => ['nama' => 'Informatika', 'kelompok' => 'B'],
    'BLMP' => ['nama' => 'Bimbingan Lomba/Prestasi', 'kelompok' => 'C'],
    'KAG' => ['nama' => 'Keagamaan', 'kelompok' => 'A'],
];

$kurikulum = Kurikulum::first();
$tahunAktif = TahunPelajaran::where('is_active', true)->first();

if (!$kurikulum) {
    echo "Error: Tidak ada kurikulum di database!\n";
    exit(1);
}

if (!$tahunAktif) {
    echo "Error: Tidak ada tahun pelajaran aktif!\n";
    exit(1);
}

echo "Kurikulum: {$kurikulum->nama_kurikulum}\n";
echo "Tahun Pelajaran: {$tahunAktif->nama}\n\n";

foreach ($missingCodes as $kode => $data) {
    $existing = MataPelajaran::where('kode_mapel', $kode)->first();
    
    if ($existing) {
        echo "  {$kode}: Sudah ada -> {$existing->nama_mapel}\n";
        
        // Pastikan aktif
        if (!$existing->is_active) {
            $existing->update(['is_active' => true]);
            echo "    -> Diaktifkan\n";
        }
    } else {
        MataPelajaran::create([
            'kode_mapel' => $kode,
            'nama_mapel' => $data['nama'],
            'kurikulum_id' => $kurikulum->id,
            'kelompok' => $data['kelompok'],
            'is_active' => true,
        ]);
        echo "  {$kode}: DITAMBAHKAN -> {$data['nama']}\n";
    }
}

echo "\nSelesai! Silakan upload Excel lagi.\n";
