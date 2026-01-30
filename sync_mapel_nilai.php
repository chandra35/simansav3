<?php
/**
 * Sinkronisasi mapel dengan config nilai
 * Pastikan semua kode mapel di config/nilai.php ada di database
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;

echo "=== SINKRONISASI MAPEL UNTUK NILAI SPAN-PTKIN ===\n\n";

// Ambil semua mapel dari config (dasar + peminatan)
$mapelDasar = config('nilai.urutan_mapel_dasar', config('nilai.urutan_mapel', []));
$mapelPeminatan = config('nilai.urutan_mapel_peminatan', []);
$urutanMapel = array_merge($mapelDasar, $mapelPeminatan);

// Definisi lengkap mapel
$mapelDefinitions = [
    'QH' => ['nama' => 'Al-Quran Hadits', 'kelompok' => 'A'],
    'AA' => ['nama' => 'Akidah Akhlak', 'kelompok' => 'A'],
    'FIK' => ['nama' => 'Fikih', 'kelompok' => 'A'],
    'SKI' => ['nama' => 'Sejarah Kebudayaan Islam', 'kelompok' => 'A'],
    'BAR' => ['nama' => 'Bahasa Arab', 'kelompok' => 'A'],
    'PP' => ['nama' => 'Pendidikan Pancasila', 'kelompok' => 'A'],
    'BINDO' => ['nama' => 'Bahasa Indonesia', 'kelompok' => 'B'],
    'MTK' => ['nama' => 'Matematika', 'kelompok' => 'B'],
    'IPAT' => ['nama' => 'IPA Terpadu', 'kelompok' => 'C'],
    'IPST' => ['nama' => 'IPS Terpadu', 'kelompok' => 'C'],
    'BING' => ['nama' => 'Bahasa Inggris', 'kelompok' => 'B'],
    'PJOK' => ['nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kelompok' => 'B'],
    'INFO' => ['nama' => 'Informatika', 'kelompok' => 'B'],
    'SB' => ['nama' => 'Seni Budaya', 'kelompok' => 'B'],
    'MULOK PRKW' => ['nama' => 'Muatan Lokal Prakarya', 'kelompok' => 'C'],
    'BLMP' => ['nama' => 'Bimbingan Lomba/Prestasi', 'kelompok' => 'C'],
    'KAG' => ['nama' => 'Keagamaan', 'kelompok' => 'A'],
    'THF' => ['nama' => 'Tahfidz', 'kelompok' => 'A'],
    // Mapel Peminatan (Semester 4-5)
    'BIO' => ['nama' => 'Biologi', 'kelompok' => 'C'],
    'KIM' => ['nama' => 'Kimia', 'kelompok' => 'C'],
    'FIS' => ['nama' => 'Fisika', 'kelompok' => 'C'],
    'NFOI' => ['nama' => 'Informatika Peminatan', 'kelompok' => 'C'],
    'EKO' => ['nama' => 'Ekonomi', 'kelompok' => 'C'],
    'GEO' => ['nama' => 'Geografi', 'kelompok' => 'C'],
];

$kurikulum = Kurikulum::first();
if (!$kurikulum) {
    echo "Error: Tidak ada kurikulum di database!\n";
    exit(1);
}

echo "Kurikulum: {$kurikulum->nama_kurikulum}\n\n";

$created = 0;
$activated = 0;
$exists = 0;

foreach ($urutanMapel as $index => $kode) {
    $no = $index + 1;
    $existing = MataPelajaran::where('kode_mapel', $kode)->first();
    
    if ($existing) {
        if (!$existing->is_active) {
            $existing->update(['is_active' => true]);
            echo "  {$no}. {$kode}: Diaktifkan -> {$existing->nama_mapel}\n";
            $activated++;
        } else {
            echo "  {$no}. {$kode}: OK -> {$existing->nama_mapel}\n";
            $exists++;
        }
    } else {
        $def = $mapelDefinitions[$kode] ?? ['nama' => $kode, 'kelompok' => 'C'];
        MataPelajaran::create([
            'kode_mapel' => $kode,
            'nama_mapel' => $def['nama'],
            'kurikulum_id' => $kurikulum->id,
            'kelompok' => $def['kelompok'],
            'is_active' => true,
        ]);
        echo "  {$no}. {$kode}: DIBUAT -> {$def['nama']}\n";
        $created++;
    }
}

echo "\n=== RINGKASAN ===\n";
echo "Sudah ada: {$exists}\n";
echo "Diaktifkan: {$activated}\n";
echo "Dibuat baru: {$created}\n";
echo "\nSelesai!\n";
