<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentQrAttendanceArchitectureTest extends TestCase
{
    public function test_qr_attendance_migration_supports_expiring_daily_and_sholat_tokens(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_10_29_113500_create_attendance_qr_tokens_table.php');

        foreach (['attendance_qr_tokens', 'context', 'absensi_siswa_session_id', 'sholat_session_id', 'token_hash', 'expires_at', 'revoked_at'] as $expected) {
            $this->assertStringContainsString($expected, $migration);
        }
    }

    public function test_routes_register_admin_issue_and_student_checkin_endpoints(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString('/absensi-siswa/{session}/qr-token', $routes);
        $this->assertStringContainsString("AttendanceQrTokenController::class, 'issueDaily'", $routes);
        $this->assertStringContainsString('/sessions/{sholatSession}/qr-token', $routes);
        $this->assertStringContainsString("AttendanceQrTokenController::class, 'issueSholat'", $routes);
        $this->assertStringContainsString("Route::prefix('qr-attendance')->name('qr-attendance.')", $routes);
        $this->assertStringContainsString("Route::get('/', [App\\Http\\Controllers\\Siswa\\QrAttendanceController::class, 'index'])", $routes);
        $this->assertStringContainsString("Route::post('/check-in', [App\\Http\\Controllers\\Siswa\\QrAttendanceController::class, 'checkIn'])", $routes);
        $this->assertStringContainsString("QrAttendanceController::class, 'checkIn'", $routes);
        $this->assertStringContainsString("StudentQrKioskController::class, 'index'", $routes);
    }

    public function test_service_validates_token_expiry_and_uses_existing_attendance_models_for_single_use(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/StudentQrAttendanceService.php');

        $this->assertStringContainsString('throw ValidationException::withMessages([', $service);
        $this->assertStringContainsString('Token QR sudah kedaluwarsa', $service);
        $this->assertStringContainsString('AbsensiSiswaRecord::withTrashed()', $service);
        $this->assertStringContainsString("->where('session_id', \$session->id)", $service);
        $this->assertStringContainsString('SholatRecord::query()', $service);
        $this->assertStringContainsString("->where('sholat_session_id', \$session->id)", $service);
        $this->assertStringContainsString("->where('siswa_id', \$siswa->id)", $service);
        $this->assertStringContainsString("'source_reference' => self::DAILY_SOURCE_REFERENCE", $service);
        $this->assertStringContainsString("'attendance_method' => 'qrcode'", $service);
        $this->assertStringContainsString('ensureSholatQrAvailability', $service);
        $this->assertStringContainsString('qr_daily_enabled', $service);
        $this->assertStringContainsString('qr_sholat_enabled', $service);
    }

    public function test_qr_method_migration_extends_existing_attendance_enums(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_10_29_120000_add_qr_attendance_methods.php');

        $this->assertStringContainsString("ENUM('manual', 'face', 'qrcode', 'hybrid')", $migration);
        $this->assertStringContainsString("ENUM('manual', 'face', 'qrcode')", $migration);
    }

    public function test_existing_manual_and_face_attendance_paths_remain_unchanged(): void
    {
        $adminAttendance = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $waliAttendance = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/WaliKelas/AbsensiController.php');

        $this->assertStringContainsString("'attendance_method' => 'manual'", $adminAttendance);
        $this->assertStringContainsString("'attendance_method' => 'manual'", $waliAttendance);
        $this->assertStringContainsString("'source_reference' => 'homeroom_marking'", $waliAttendance);
    }
}
