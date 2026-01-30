<?php

/**
 * Script untuk menyesuaikan kode mapel dengan format Excel RDM
 * Jalankan dengan: php update_mapel_rdm.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\TahunPelajaran;

echo "=== Update Kode Mapel sesuai Format Excel RDM ===\n\n";

// 1. Update kode mapel yang sudah ada
$updates = [
    ['old' => 'FIQ', 'new_kode' => 'FIK', 'new_nama' => 'Fikih'],
    ['old' => 'BA', 'new_kode' => 'BAR', 'new_nama' => 'Bahasa Arab'],
    ['old' => 'PPKN', 'new_kode' => 'PP', 'new_nama' => 'Pendidikan Pancasila'],
    ['old' => 'BIN', 'new_kode' => 'BINDO', 'new_nama' => 'Bahasa Indonesia'],
    ['old' => 'MAT', 'new_kode' => 'MTK', 'new_nama' => 'Matematika'],
    ['old' => 'SENBUD', 'new_kode' => 'SB', 'new_nama' => 'Seni Budaya'],
    ['old' => 'PKWU', 'new_kode' => 'MULOK PRKW', 'new_nama' => 'Muatan Lokal Prakarya'],
];

echo "1. Updating existing mapel codes...\n";
foreach ($updates as $u) {
    $mapel = MataPelajaran::where('kode_mapel', $u['old'])->first();
    if ($mapel) {
        $mapel->update(['kode_mapel' => $u['new_kode'], 'nama_mapel' => $u['new_nama']]);
        echo "   ✓ Updated: {$u['old']} -> {$u['new_kode']} ({$u['new_nama']})\n";
    } else {
        echo "   - Not found: {$u['old']}\n";
    }
}

// 2. Tambah mapel baru yang belum ada
echo "\n2. Adding new mapel...\n";

// Get default kurikulum and tahun pelajaran
$kurikulum = Kurikulum::where('is_active', true)->first();
$tahunPelajaran = TahunPelajaran::where('is_active', true)->first();

if (!$kurikulum || !$tahunPelajaran) {
    echo "   ! Error: Kurikulum atau Tahun Pelajaran aktif tidak ditemukan\n";
    exit(1);
}

$newMapels = [
    ['kode' => 'SEJ', 'nama' => 'Sejarah', 'kelompok' => 'B', 'kategori' => 'umum'],
    ['kode' => 'THF', 'nama' => 'Tahfidz', 'kelompok' => 'A', 'kategori' => 'muatan_lokal'],
    ['kode' => 'BIO', 'nama' => 'Biologi', 'kelompok' => 'C', 'kategori' => 'peminatan'],
    ['kode' => 'KIM', 'nama' => 'Kimia', 'kelompok' => 'C', 'kategori' => 'peminatan'],
    ['kode' => 'FIS', 'nama' => 'Fisika', 'kelompok' => 'C', 'kategori' => 'peminatan'],
    ['kode' => 'INFOP', 'nama' => 'Informatika Peminatan', 'kelompok' => 'C', 'kategori' => 'peminatan'],
    ['kode' => 'MTL', 'nama' => 'Matematika Lanjut', 'kelompok' => 'C', 'kategori' => 'peminatan'],
    ['kode' => 'EKO', 'nama' => 'Ekonomi', 'kelompok' => 'C', 'kategori' => 'peminatan'],
];

foreach ($newMapels as $m) {
    $exists = MataPelajaran::where('kode_mapel', $m['kode'])->first();
    if (!$exists) {
        MataPelajaran::create([
            'kurikulum_id' => $kurikulum->id,
            'tahun_pelajaran_id' => $tahunPelajaran->id,
            'kode_mapel' => $m['kode'],
            'nama_mapel' => $m['nama'],
            'kelompok' => $m['kelompok'],
            'kategori' => $m['kategori'],
            'kkm' => 75,
            'jam_pelajaran' => 2,
            'tingkat' => [10, 11, 12],
            'semester' => [1, 2],
            'is_active' => true,
        ]);
        echo "   ✓ Added: {$m['kode']} - {$m['nama']}\n";
    } else {
        echo "   - Already exists: {$m['kode']}\n";
    }
}

echo "\n=== Selesai! ===\n";
echo "\nDaftar mapel setelah update:\n";

$mapels = MataPelajaran::orderBy('kode_mapel')
    ->get(['kode_mapel', 'nama_mapel'])
    ->filter(function ($m) {
        // Only show non M- prefix (K13 format)
        return !str_starts_with($m->kode_mapel, 'M-');
    });

foreach ($mapels as $m) {
    echo "   {$m->kode_mapel} = {$m->nama_mapel}\n";
}
