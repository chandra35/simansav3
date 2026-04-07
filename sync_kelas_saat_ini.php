<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Syncing kelas_saat_ini_id untuk siswa aktif berdasarkan tahun pelajaran aktif...\n";

$tahunAktif = DB::table('tahun_pelajaran')
    ->where('is_active', true)
    ->whereNull('deleted_at')
    ->first();

if (!$tahunAktif) {
    echo "Tidak ada tahun pelajaran aktif.\n";
    exit(1);
}

echo "Tahun pelajaran aktif: {$tahunAktif->nama}\n";

$duplicatedSiswa = DB::table('siswa_kelas')
    ->select('siswa_id', DB::raw('COUNT(*) as total'))
    ->where('tahun_pelajaran_id', $tahunAktif->id)
    ->where('status', 'aktif')
    ->whereNull('deleted_at')
    ->groupBy('siswa_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($duplicatedSiswa->isNotEmpty()) {
    echo "Ditemukan {$duplicatedSiswa->count()} siswa dengan lebih dari satu kelas aktif. Sinkron dibatalkan.\n";
    foreach ($duplicatedSiswa->take(10) as $row) {
        echo "- Siswa ID {$row->siswa_id} memiliki {$row->total} kelas aktif\n";
    }
    exit(1);
}

$siswaKelas = DB::table('siswa_kelas')
    ->where('tahun_pelajaran_id', $tahunAktif->id)
    ->where('status', 'aktif')
    ->whereNull('deleted_at')
    ->orderBy('created_at')
    ->get();

$updated = 0;

foreach ($siswaKelas as $sk) {
    DB::table('siswa')
        ->where('id', $sk->siswa_id)
        ->update(['kelas_saat_ini_id' => $sk->kelas_id]);
    $updated++;
}

$cleared = DB::table('siswa')
    ->whereNotNull('kelas_saat_ini_id')
    ->whereNull('deleted_at')
    ->whereNotExists(function ($query) use ($tahunAktif) {
        $query->select(DB::raw(1))
            ->from('siswa_kelas')
            ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
            ->where('siswa_kelas.tahun_pelajaran_id', $tahunAktif->id)
            ->where('siswa_kelas.status', 'aktif')
            ->whereNull('siswa_kelas.deleted_at');
    })
    ->update(['kelas_saat_ini_id' => null]);

echo "Berhasil update {$updated} siswa\n";
echo "Berhasil membersihkan {$cleared} siswa tanpa kelas aktif di tahun berjalan\n";
echo "Selesai!\n";
