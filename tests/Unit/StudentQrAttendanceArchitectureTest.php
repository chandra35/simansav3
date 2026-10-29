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

    public function test_student_card_scanner_uses_existing_card_qr_and_single_sholat_match(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/StudentQrAttendanceService.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/AttendanceCardScannerController.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_10_29_124500_create_attendance_scanner_access_links_table.php');

        $this->assertStringContainsString("Route::middleware(['auth'])->prefix('admin/absensi/pemindai-kartu')->name('card-scanner.')", $routes);
        $this->assertStringContainsString("Route::post('/scan'", $routes);
        $this->assertStringContainsString("Route::get('/akses/{token}'", $routes);
        $this->assertStringContainsString("/verifikasi/siswa/([a-f0-9-]{36})", $controller);
        $this->assertStringContainsString("route('card-scanner.claim'", $controller);
        $this->assertStringContainsString('recordDailyCardScan', $service);
        $this->assertStringContainsString('recordSholatCardScan', $service);
        $this->assertStringContainsString('$sholatSessions->count() > 1', $service);
        $this->assertStringContainsString('jadwal sholat tidak saling tumpang tindih', $service);
        $this->assertStringContainsString('attendance_scanner_access_links', $migration);
        $this->assertStringContainsString('token_hash', $migration);
        $this->assertStringContainsString('claimed_by', $migration);
        $this->assertStringContainsString('expires_at', $migration);
    }

    public function test_card_scanner_loads_a_production_safe_camera_asset(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/absensi/card-scanner.blade.php');

        $this->assertStringContainsString("vendor/html5-qrcode.min.js", $view);
        $this->assertStringNotContainsString("@vite('resources/js/card-scanner.js')", $view);
        $this->assertStringContainsString("useBarCodeDetectorIfSupported:false", $view);
        $this->assertStringContainsString("capture=\"environment\"", $view);
        $this->assertStringContainsString("scanFile(image,true)", $view);
        $this->assertStringContainsString('scanner-dots', $view);
        $this->assertStringContainsString('scanner-line', $view);
        $this->assertFileExists(dirname(__DIR__, 2).'/public/vendor/html5-qrcode.min.js');
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
