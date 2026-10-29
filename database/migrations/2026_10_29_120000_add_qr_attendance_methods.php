<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE absensi_siswa_sessions MODIFY attendance_method ENUM('manual', 'face', 'qrcode', 'hybrid') NOT NULL DEFAULT 'manual'");
            DB::statement("ALTER TABLE absensi_siswa_records MODIFY attendance_method ENUM('manual', 'face', 'qrcode') NOT NULL DEFAULT 'manual'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE absensi_siswa_sessions SET attendance_method = 'hybrid' WHERE attendance_method = 'qrcode'");
            DB::statement("UPDATE absensi_siswa_records SET attendance_method = 'manual' WHERE attendance_method = 'qrcode'");
            DB::statement("ALTER TABLE absensi_siswa_sessions MODIFY attendance_method ENUM('manual', 'face', 'hybrid') NOT NULL DEFAULT 'manual'");
            DB::statement("ALTER TABLE absensi_siswa_records MODIFY attendance_method ENUM('manual', 'face') NOT NULL DEFAULT 'manual'");
        }
    }
};
