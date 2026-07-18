<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('emis_student_syncs')
            ->where('status', 'completed')
            ->update([
                'progress_percent' => 100,
                'stage' => 'completed',
                'progress_message' => 'Sinkronisasi selesai dan snapshot siap digunakan.',
            ]);

        DB::table('emis_student_syncs')
            ->where('status', 'failed')
            ->update([
                'stage' => 'failed',
                'progress_message' => 'Sinkronisasi tidak berhasil. Snapshot sebelumnya tetap aman.',
            ]);
    }

    public function down(): void
    {
        // Riwayat progress tetap dipertahankan saat rollback data-only migration.
    }
};
