<?php

namespace Tests\Feature;

use Tests\TestCase;

class QrAttendanceRouteRegistrationTest extends TestCase
{
    public function test_qr_attendance_routes_are_registered(): void
    {
        $this->artisan('route:list --name=qr-token')
            ->expectsOutputToContain('admin/absensi-siswa/{session}/qr-token')
            ->expectsOutputToContain('admin/sholat/sessions/{sholatSession}/qr-token')
            ->assertExitCode(0);

        $this->artisan('route:list --name=qr-attendance')
            ->expectsOutputToContain('siswa/qr-attendance/check-in')
            ->assertExitCode(0);
    }
}
