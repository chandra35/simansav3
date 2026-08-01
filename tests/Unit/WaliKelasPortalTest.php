<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Menjaga invarian Portal Wali Kelas:
 *  1. session_key absensi portal IDENTIK dengan format Admin\AbsensiSiswaController
 *     ("$tgl:$kelasId:harian:daily") agar monitoring pusat membaca sesi yang sama.
 *  2. Data siswa & catatan portal di-scope ketat ke rombel wali (server-side).
 *  3. Gate menu memakai prefix sidebar- (hindari bentrok Spatie Gate::before).
 */
class WaliKelasPortalTest extends TestCase
{
    private function file(string $relative): string
    {
        return file_get_contents(dirname(__DIR__, 2).'/'.$relative);
    }

    public function test_portal_attendance_session_key_matches_admin_format(): void
    {
        $admin = $this->file('app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $portal = $this->file('app/Http/Controllers/Admin/WaliKelas/AbsensiController.php');

        // Admin memakai 'daily' untuk mode harian.
        $this->assertStringContainsString("\$mode === 'mapel' ? \$scheduleId : 'daily'", $admin);
        // Portal hanya harian → harus menghasilkan segmen ...:harian:daily yang sama.
        $this->assertStringContainsString("implode(':', [\$date, \$kelasId, 'harian', 'daily'])", $portal);
        $this->assertStringContainsString("\$session->mode = 'harian'", $portal);
        // Wajib memakai audit service bersama, bukan menulis audit sendiri.
        $this->assertStringContainsString('StudentAttendanceAuditService', $portal);
    }

    public function test_portal_scopes_queries_to_wali_classes(): void
    {
        $base = $this->file('app/Http/Controllers/Admin/WaliKelas/BaseWaliKelasController.php');

        // Rombel hanya milik user aktif (tahun aktif, is_active) — scope keamanan.
        $this->assertStringContainsString("->where('wali_kelas_id', Auth::id())", $base);
        $this->assertStringContainsString("TahunPelajaran::query()->active()->select('id')", $base);
        // Siswa harus tergabung di rombel wali, else 404.
        $this->assertStringContainsString('kelasTahunAktif', $base);
        // Bukan wali kelas → 403.
        $this->assertStringContainsString('abort_unless($this->waliClasses()->isNotEmpty(), 403', $base);
    }

    public function test_menu_gate_uses_sidebar_prefix(): void
    {
        $provider = $this->file('app/Providers/AuthServiceProvider.php');
        $config = $this->file('config/adminlte.php');

        $this->assertStringContainsString("Gate::define('sidebar-wali-kelas-menu'", $provider);
        $this->assertStringContainsString('isActiveWaliKelas()', $provider);
        $this->assertStringContainsString("'can' => 'sidebar-wali-kelas-menu'", $config);
    }
}
