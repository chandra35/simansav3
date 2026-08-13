<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('gtks')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('mutasi_gtk')->whereColumn('mutasi_gtk.gtk_id', 'gtks.id'))
            ->select(['id', 'status_aktif', 'tmt_kerja', 'tanggal_status', 'created_at'])
            ->orderBy('id')
            ->each(function ($gtk) use ($now) {
                DB::table('mutasi_gtk')->insert([
                    'id' => (string) Str::uuid(),
                    'gtk_id' => $gtk->id,
                    'status_sebelumnya' => null,
                    'status_baru' => (bool) $gtk->status_aktif,
                    'alasan' => 'data_awal',
                    'tanggal_efektif' => $gtk->tmt_kerja ?: $gtk->tanggal_status ?: substr((string) $gtk->created_at, 0, 10) ?: $now->toDateString(),
                    'keterangan' => 'Snapshot data GTK sebelum modul histori diterapkan.',
                    'dampak_operasional' => json_encode(['snapshot_awal' => 1]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('mutasi_gtk')->where('alasan', 'data_awal')->where('keterangan', 'Snapshot data GTK sebelum modul histori diterapkan.')->delete();
    }
};
