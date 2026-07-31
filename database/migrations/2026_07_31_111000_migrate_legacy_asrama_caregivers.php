<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $legacyCaregiverIds = DB::table('asrama_kelas')
            ->whereNotNull('wali_asatidz_id')
            ->pluck('wali_asatidz_id')
            ->unique()
            ->values();

        if ($legacyCaregiverIds->isNotEmpty()) {
            DB::table('asrama_asatidz')->whereIn('id', $legacyCaregiverIds)->update([
                'dapat_mengasuh_rombel' => true,
                'updated_at' => now(),
            ]);
        }

        DB::table('asrama_kelas')
            ->whereNotNull('wali_asatidz_id')
            ->orderBy('created_at')
            ->get()
            ->each(function ($rombel): void {
                if (DB::table('asrama_rombel_pengasuh')->where([
                    'asrama_kelas_id' => $rombel->id,
                    'asrama_asatidz_id' => $rombel->wali_asatidz_id,
                ])->exists()) {
                    return;
                }

                DB::table('asrama_rombel_pengasuh')->insert([
                    'id' => (string) Str::uuid(),
                    'asrama_kelas_id' => $rombel->id,
                    'asrama_asatidz_id' => $rombel->wali_asatidz_id,
                    'is_primary' => true,
                    'tanggal_mulai' => now()->toDateString(),
                    'is_active' => true,
                    'created_by' => $rombel->created_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Data pengasuh dipertahankan karena mungkin sudah dilanjutkan oleh operator.
    }
};
