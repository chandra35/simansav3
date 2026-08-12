<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_penugasan_gtk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 80)->unique();
            $table->string('nama');
            $table->string('kelompok', 80)->nullable()->index();
            $table->string('kategori', 20)->default('lain')->index();
            $table->unsignedTinyInteger('ekuivalensi_jtm')->default(0);
            $table->unsignedTinyInteger('minimal_jtm_mengajar')->default(0);
            $table->string('jenis_unit', 40)->nullable();
            $table->unsignedTinyInteger('maks_pemegang')->nullable();
            $table->boolean('wajib_sk')->default(true);
            $table->boolean('dapat_dirangkap')->default(false);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('dasar_hukum')->nullable();
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_selesai')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::create('penugasan_gtk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gtk_id');
            $table->uuid('jenis_penugasan_id');
            $table->uuid('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->nullable()->comment('1/2, null berlaku sepanjang tahun');
            $table->string('unit_nama')->nullable();
            $table->date('mulai_tugas');
            $table->date('selesai_tugas')->nullable();
            $table->string('nomor_sk', 150)->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('file_sk')->nullable();
            $table->unsignedTinyInteger('ekuivalensi_jtm');
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('role_diberikan_otomatis')->default(false);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('legacy_tugas_tambahan_id')->nullable()->unique();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('gtk_id')->references('id')->on('gtks')->cascadeOnDelete();
            $table->foreign('jenis_penugasan_id')->references('id')->on('jenis_penugasan_gtk')->restrictOnDelete();
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tahun_pelajaran_id', 'semester', 'status'], 'penugasan_periode_status_idx');
            $table->index(['gtk_id', 'tahun_pelajaran_id'], 'penugasan_gtk_tahun_idx');
        });

        $now = now();
        $roles = DB::table('roles')->whereIn('name', ['Kepala Madrasah', 'WAKA'])->pluck('id', 'name');
        $definitions = [
            ['kepala_madrasah', 'Kepala Madrasah', 'kepala_madrasah', 'penuh', 24, 0, null, 1, true, false, $roles['Kepala Madrasah'] ?? null],
            ['waka_madrasah', 'Wakil Kepala Madrasah', 'waka', 'utama', 12, 12, 'bidang', null, true, false, $roles['WAKA'] ?? null],
            ['waka_kurikulum', 'Waka Kurikulum', 'waka', 'utama', 12, 12, 'bidang', 1, true, false, $roles['WAKA'] ?? null],
            ['waka_kesiswaan', 'Waka Kesiswaan', 'waka', 'utama', 12, 12, 'bidang', 1, true, false, $roles['WAKA'] ?? null],
            ['waka_humas', 'Waka Humas', 'waka', 'utama', 12, 12, 'bidang', 1, true, false, $roles['WAKA'] ?? null],
            ['waka_sarpras', 'Waka Sarana dan Prasarana', 'waka', 'utama', 12, 12, 'bidang', 1, true, false, $roles['WAKA'] ?? null],
            ['kepala_laboratorium', 'Kepala Laboratorium', 'kepala_unit', 'utama', 12, 12, 'laboratorium', null, true, false, null],
            ['kepala_perpustakaan', 'Kepala Perpustakaan', 'kepala_unit', 'utama', 12, 12, 'perpustakaan', 1, true, false, null],
            ['wali_kelas', 'Wali Kelas', 'tugas_lain', 'lain', 6, 18, 'rombel', null, true, true, null],
            ['pembina_osim', 'Pembina OSIM', 'tugas_lain', 'lain', 6, 18, 'organisasi', 1, true, true, null],
            ['pembina_ekstrakurikuler', 'Pembina Ekstrakurikuler', 'tugas_lain', 'lain', 2, 18, 'kegiatan', null, true, true, null],
            ['koordinator_pengembangan_kompetensi', 'Koordinator Pengembangan Kompetensi', 'tugas_lain', 'lain', 2, 18, null, 1, true, true, null],
            ['guru_piket', 'Guru Piket', 'tugas_lain', 'lain', 1, 18, 'jadwal', null, true, true, null],
        ];

        $typeIds = [];
        foreach ($definitions as [$code, $name, $group, $category, $jtm, $minimum, $unit, $maximum, $requiresSk, $stackable, $roleId]) {
            $id = (string) Str::uuid();
            $typeIds[$code] = $id;
            DB::table('jenis_penugasan_gtk')->insert([
                'id' => $id,
                'kode' => $code,
                'nama' => $name,
                'kelompok' => $group,
                'kategori' => $category,
                'ekuivalensi_jtm' => $jtm,
                'minimal_jtm_mengajar' => $minimum,
                'jenis_unit' => $unit,
                'maks_pemegang' => $maximum,
                'wajib_sk' => $requiresSk,
                'dapat_dirangkap' => $stackable,
                'role_id' => $roleId,
                'dasar_hukum' => 'KMA Nomor 736 Tahun 2026',
                'berlaku_mulai' => '2026-07-01',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $activeYear = DB::table('tahun_pelajaran')->where('is_active', true)->value('id');
        if (! $activeYear || ! Schema::hasTable('tugas_tambahan')) {
            return;
        }

        DB::table('tugas_tambahan as tt')
            ->join('roles as r', 'r.id', '=', 'tt.role_id')
            ->join('gtks as g', 'g.user_id', '=', 'tt.user_id')
            ->whereIn('r.name', ['Kepala Madrasah', 'WAKA'])
            ->select(['tt.*', 'r.name as role_name', 'g.id as gtk_id'])
            ->orderBy('tt.id')
            ->each(function ($legacy) use ($activeYear, $typeIds, $now) {
                $code = $legacy->role_name === 'Kepala Madrasah' ? 'kepala_madrasah' : 'waka_madrasah';
                DB::table('penugasan_gtk')->insert([
                    'id' => (string) Str::uuid(),
                    'gtk_id' => $legacy->gtk_id,
                    'jenis_penugasan_id' => $typeIds[$code],
                    'tahun_pelajaran_id' => $activeYear,
                    'semester' => null,
                    'mulai_tugas' => $legacy->mulai_tugas ?: $now->toDateString(),
                    'selesai_tugas' => $legacy->selesai_tugas,
                    'nomor_sk' => $legacy->sk_number,
                    'tanggal_sk' => $legacy->sk_date,
                    'ekuivalensi_jtm' => $code === 'kepala_madrasah' ? 24 : 12,
                    'status' => $legacy->is_active ? 'active' : 'ended',
                    'role_diberikan_otomatis' => false,
                    'keterangan' => $legacy->keterangan,
                    'legacy_tugas_tambahan_id' => $legacy->id,
                    'created_by' => $legacy->created_by,
                    'updated_by' => $legacy->updated_by,
                    'created_at' => $legacy->created_at ?: $now,
                    'updated_at' => $legacy->updated_at ?: $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_gtk');
        Schema::dropIfExists('jenis_penugasan_gtk');
    }
};
