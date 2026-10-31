<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_journals', function (Blueprint $table) {
            $table->foreignUuid('absensi_siswa_session_id')
                ->nullable()
                ->after('catatan')
                ->constrained('absensi_siswa_sessions')
                ->nullOnDelete();
            $table->json('attendance_summary')
                ->nullable()
                ->after('absensi_siswa_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_journals', function (Blueprint $table) {
            $table->dropForeign(['absensi_siswa_session_id']);
            $table->dropColumn(['absensi_siswa_session_id', 'attendance_summary']);
        });
    }
};
