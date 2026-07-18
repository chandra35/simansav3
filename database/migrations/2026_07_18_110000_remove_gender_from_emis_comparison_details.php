<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('emis_student_snapshots')
            ->select(['id', 'comparison_details'])
            ->orderBy('id')
            ->chunkById(250, function ($snapshots) {
                foreach ($snapshots as $snapshot) {
                    $details = json_decode((string) $snapshot->comparison_details, true);
                    if (! is_array($details)) {
                        continue;
                    }

                    unset($details['jenis_kelamin']);
                    $comparable = collect($details)->whereNotIn('status', ['both_empty', 'emis_empty']);
                    $status = 'exact';

                    if ($comparable->contains(fn ($detail) => in_array($detail['status'] ?? null, ['different', 'simansa_empty'], true))) {
                        $status = 'different';
                    } elseif ($comparable->contains('status', 'similar')) {
                        $status = 'similar';
                    } elseif ($comparable->contains('status', 'equivalent')) {
                        $status = 'normalized';
                    }

                    DB::table('emis_student_snapshots')
                        ->where('id', $snapshot->id)
                        ->update([
                            'comparison_details' => json_encode($details, JSON_UNESCAPED_UNICODE),
                            'comparison_status' => $status,
                            'updated_at' => now(),
                        ]);
                }
            }, 'id');
    }

    public function down(): void
    {
        // Field jenis kelamin tidak dikembalikan karena bukan lagi bagian pembandingan.
    }
};
