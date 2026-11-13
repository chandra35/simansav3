<?php

use App\Models\AsramaSantri;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah flag is_asrama pada tabel siswa
        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'is_asrama')) {
                $table->boolean('is_asrama')->default(false)->after('jenis_tempat_tinggal')->index();
                $table->timestamp('is_asrama_at')->nullable()->after('is_asrama');
                $table->uuid('is_asrama_by')->nullable()->after('is_asrama_at');
            }
        });

        // 2. Modifikasi kolom status pada asrama_santri untuk mendukung status alumni dan mutasi_keluar
        if (Schema::hasTable('asrama_santri')) {
            try {
                DB::statement("ALTER TABLE asrama_santri MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'aktif'");
            } catch (\Throwable $e) {
                // Fallback jika bukan MySQL murni
                Schema::table('asrama_santri', function (Blueprint $table) {
                    $table->string('status', 30)->default('aktif')->change();
                });
            }
        }

        // 3. Modifikasi kolom status pada asrama_kelas_santri untuk mendukung status lulus
        if (Schema::hasTable('asrama_kelas_santri')) {
            try {
                DB::statement("ALTER TABLE asrama_kelas_santri MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'aktif'");
            } catch (\Throwable $e) {
                Schema::table('asrama_kelas_santri', function (Blueprint $table) {
                    $table->string('status', 30)->default('aktif')->change();
                });
            }
        }

        // 4. Tambah flag is_santri_asrama dan nomor_induk_asrama pada alumni_profiles
        Schema::table('alumni_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni_profiles', 'is_santri_asrama')) {
                $table->boolean('is_santri_asrama')->default(false)->after('status_verifikasi')->index();
                $table->string('nomor_induk_asrama', 50)->nullable()->after('is_santri_asrama');
            }
        });

        // 5. Backfill data existing:
        // a. Siswa yang saat ini berstatus aktif di asrama_santri ditandai is_asrama = true
        if (Schema::hasTable('asrama_santri') && Schema::hasTable('siswa')) {
            $santriSiswaIds = DB::table('asrama_santri')
                ->where('status', 'aktif')
                ->whereNull('deleted_at')
                ->pluck('siswa_id');

            if ($santriSiswaIds->isNotEmpty()) {
                DB::table('siswa')
                    ->whereIn('id', $santriSiswaIds)
                    ->update([
                        'is_asrama' => true,
                        'is_asrama_at' => now(),
                    ]);
            }

            // b. Siswa yang saat ini berada di rombel bertanda is_asrama = true
            $asramaClassIds = DB::table('kelas')
                ->where('is_asrama', true)
                ->where('is_active', true)
                ->pluck('id');

            if ($asramaClassIds->isNotEmpty()) {
                $classSiswaIds = DB::table('siswa_kelas')
                    ->whereIn('kelas_id', $asramaClassIds)
                    ->where('status', 'aktif')
                    ->pluck('siswa_id');

                if ($classSiswaIds->isNotEmpty()) {
                    DB::table('siswa')
                        ->whereIn('id', $classSiswaIds)
                        ->update([
                            'is_asrama' => true,
                            'is_asrama_at' => now(),
                        ]);
                }
            }
        }

        // c. Sinkronkan ke alumni_profiles jika siswa alumni pernah tercatat sebagai santri
        if (Schema::hasTable('alumni_profiles') && Schema::hasTable('asrama_santri')) {
            $santriList = DB::table('asrama_santri')
                ->select('siswa_id', 'nomor_induk_asrama')
                ->whereNotNull('siswa_id')
                ->get();

            foreach ($santriList as $santri) {
                DB::table('alumni_profiles')
                    ->where('siswa_id', $santri->siswa_id)
                    ->update([
                        'is_santri_asrama' => true,
                        'nomor_induk_asrama' => $santri->nomor_induk_asrama,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('alumni_profiles', 'is_santri_asrama')) {
                $table->dropColumn(['is_santri_asrama', 'nomor_induk_asrama']);
            }
        });

        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'is_asrama')) {
                $table->dropColumn(['is_asrama', 'is_asrama_at', 'is_asrama_by']);
            }
        });
    }
};
