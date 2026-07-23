<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->string('struktur_fase_e', 30)->nullable()->after('kategori');
            $table->string('struktur_fase_f', 30)->nullable()->after('struktur_fase_e');
            $table->string('rumpun', 40)->nullable()->after('struktur_fase_f');
            $table->json('alokasi_jp')->nullable()->after('jam_pelajaran');
            $table->string('regulasi', 80)->nullable()->after('alokasi_jp');
            $table->boolean('is_schedulable')->default(true)->after('regulasi');

            $table->index('struktur_fase_e');
            $table->index('struktur_fase_f');
            $table->index('rumpun');
            $table->index('is_schedulable');
        });

        $merdekaId = DB::table('kurikulum')
            ->whereRaw('UPPER(kode) = ?', ['MERDEKA'])
            ->value('id');

        if (!$merdekaId) {
            return;
        }

        DB::table('mata_pelajaran')
            ->where('kurikulum_id', $merdekaId)
            ->orderBy('id')
            ->each(function ($mapel): void {
                $tingkat = array_map('intval', json_decode($mapel->tingkat ?: '[]', true) ?: []);
                $kategori = mb_strtolower((string) $mapel->kategori);
                $nama = mb_strtolower((string) $mapel->nama_mapel);
                $kode = strtoupper((string) $mapel->kode_mapel);

                $isKokurikuler = (bool) $mapel->is_projek_p5
                    || str_contains($kode, 'P5')
                    || str_contains($nama, 'projek penguatan');
                $isMulok = (bool) $mapel->is_muatan_lokal
                    || str_contains($kategori, 'muatan lokal');
                $isPilihan = (bool) $mapel->is_mapel_pilihan
                    || str_contains($kategori, 'pilihan');

                $struktur = $isKokurikuler
                    ? 'kokurikuler'
                    : ($isMulok ? 'muatan_lokal' : ($isPilihan ? 'pilihan' : 'wajib_umum'));

                $rumpun = match (true) {
                    (bool) $mapel->is_rumpun_pai => 'pai',
                    (bool) $mapel->is_bahasa_arab => 'bahasa',
                    preg_match('/matematika|fisika|kimia|biologi|ipa/u', $nama) === 1 => 'mipa',
                    preg_match('/sejarah|ekonomi|geografi|sosiologi|antropologi|ips/u', $nama) === 1 => 'ips',
                    preg_match('/bahasa|sastra/u', $nama) === 1 => 'bahasa',
                    preg_match('/informatika|teknologi|komputer|robotik|koding/u', $nama) === 1 => 'teknologi',
                    preg_match('/seni|prakarya|keterampilan/u', $nama) === 1 => 'seni_prakarya',
                    preg_match('/jasmani|olahraga|pjok/u', $nama) === 1 => 'pjok',
                    default => 'umum',
                };

                $alokasi = [];
                foreach ($tingkat as $level) {
                    $alokasi[(string) $level] = (int) $mapel->jam_pelajaran;
                }

                DB::table('mata_pelajaran')
                    ->where('id', $mapel->id)
                    ->update([
                        'struktur_fase_e' => in_array(10, $tingkat, true) ? $struktur : null,
                        'struktur_fase_f' => (in_array(11, $tingkat, true) || in_array(12, $tingkat, true)) ? $struktur : null,
                        'rumpun' => $rumpun,
                        'alokasi_jp' => $alokasi ? json_encode($alokasi) : null,
                        'regulasi' => array_intersect($tingkat, [10, 11, 12])
                            ? 'KMA 1503 Tahun 2025'
                            : null,
                        'is_schedulable' => !$isKokurikuler && (int) $mapel->jam_pelajaran > 0,
                        'updated_at' => now(),
                    ]);
            });

        $templates = require base_path('config/mapel_man.php');
        foreach ($templates as $group) {
            foreach ($group['mapel'] ?? [] as $template) {
                $levels = array_map('intval', array_keys($template['alokasi_jp'] ?? []));
                $existing = DB::table('mata_pelajaran')
                    ->where('kurikulum_id', $merdekaId)
                    ->where('kode_mapel', $template['kode_mapel'])
                    ->first();

                if (!$existing) {
                    continue;
                }

                DB::table('mata_pelajaran')
                    ->where('id', $existing->id)
                    ->update([
                        'tingkat' => json_encode($levels),
                        'struktur_fase_e' => $template['struktur_fase_e'] ?? null,
                        'struktur_fase_f' => $template['struktur_fase_f'] ?? null,
                        'rumpun' => $template['rumpun'] ?? null,
                        'alokasi_jp' => json_encode($template['alokasi_jp'] ?? []),
                        'jam_pelajaran' => (int) ($template['jam_pelajaran'] ?? $existing->jam_pelajaran),
                        'regulasi' => 'KMA 1503 Tahun 2025',
                        'is_schedulable' => (bool) ($template['is_schedulable'] ?? true),
                        'is_mapel_pilihan' => (bool) ($template['is_mapel_pilihan'] ?? false),
                        'is_muatan_lokal' => (bool) ($template['is_muatan_lokal'] ?? false),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropIndex(['struktur_fase_e']);
            $table->dropIndex(['struktur_fase_f']);
            $table->dropIndex(['rumpun']);
            $table->dropIndex(['is_schedulable']);
            $table->dropColumn([
                'struktur_fase_e',
                'struktur_fase_f',
                'rumpun',
                'alokasi_jp',
                'regulasi',
                'is_schedulable',
            ]);
        });
    }
};
