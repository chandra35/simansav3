<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentAttendanceArchitectureTest extends TestCase
{
    public function test_subject_attendance_is_manual_and_finalized_before_analytics(): void
    {
        $input = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $analytics = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php');

        $this->assertStringContainsString("'attendance_method' => 'manual'", $input);
        $this->assertStringContainsString("'source_reference' => \$validated['mode'] === 'mapel' ? 'teacher_marking'", $input);
        $this->assertStringContainsString("where('sessions.status', 'final')", $analytics);
    }

    public function test_daily_face_and_subject_records_are_analyzed_as_separate_sources(): void
    {
        $analytics = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php');
        $insights = file_get_contents(dirname(__DIR__, 2).'/app/Services/AttendanceInsightService.php');

        $this->assertStringContainsString("DB::table('absensis')", $analytics);
        $this->assertStringContainsString("\$row->source_type = 'daily_face'", $analytics);
        $this->assertStringContainsString("'daily_subject_conflict'", $insights);
    }

    public function test_schema_keeps_snapshots_audits_and_granular_permissions(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_07_19_150000_harden_student_attendance_system.php');

        foreach (['kelas_snapshot', 'mapel_snapshot', 'guru_snapshot', 'absensi_siswa_audits',
            'view-attendance-analytics', 'view-attendance-audit', 'edit-final-student-attendance'] as $required) {
            $this->assertStringContainsString($required, $migration);
        }
    }
}
