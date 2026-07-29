<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassLeadershipVisibilityTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_student_dashboard_shows_homeroom_teacher_and_class_leader(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Siswa/DashboardController.php');
        $view = file_get_contents($this->root.'/resources/views/siswa/dashboard.blade.php');

        $this->assertStringContainsString("'kelasAktif.waliKelas.gtk'", $controller);
        $this->assertStringContainsString("'kelasAktif.ketuaKelasRecord.siswa'", $controller);
        $this->assertStringContainsString('Wali Kelas Saya', $view);
        $this->assertStringContainsString('Ketua Kelas Saya', $view);
        $this->assertStringContainsString("{{ \$ketuaKelasNama ?? 'Belum ditetapkan' }}", $view);
    }

    public function test_homeroom_dashboard_lists_leadership_for_every_assigned_class(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/GtkDashboardController.php');
        $view = file_get_contents($this->root.'/resources/views/admin/gtk/dashboard.blade.php');

        $this->assertStringContainsString("->where('wali_kelas_id', \$user->id)", $controller);
        $this->assertStringContainsString("'ketuaKelasRecord.siswa'", $controller);
        $this->assertStringContainsString('Rombel Perwalian Saya', $view);
        $this->assertStringContainsString('Wali Kelas', $view);
        $this->assertStringContainsString('Ketua Kelas', $view);
    }

    public function test_class_lists_expose_both_leadership_names(): void
    {
        $classController = file_get_contents($this->root.'/app/Http/Controllers/Admin/KelasController.php');
        $classView = file_get_contents($this->root.'/resources/views/admin/kelas/index.blade.php');
        $scheduleController = file_get_contents($this->root.'/app/Http/Controllers/Admin/JadwalPelajaranController.php');
        $scheduleView = file_get_contents($this->root.'/resources/views/admin/jadwal-pelajaran/index.blade.php');

        $this->assertStringContainsString("->addColumn('ketua_kelas'", $classController);
        $this->assertStringContainsString('<th>Wali Kelas</th>', $classView);
        $this->assertStringContainsString('<th>Ketua Kelas</th>', $classView);
        $this->assertStringContainsString("'ketuaKelasRecord.siswa'", $scheduleController);
        $this->assertStringContainsString("{{ \$k->ketuaKelasRecord?->siswa?->nama_lengkap ?? 'Belum ada ketua kelas' }}", $scheduleView);
    }

    public function test_class_leader_relation_supports_eager_loading_for_multiple_classes(): void
    {
        $model = file_get_contents($this->root.'/app/Models/Kelas.php');
        $relationStart = strpos($model, 'public function ketuaKelasRecord(): HasOne');
        $relation = substr($model, $relationStart, 450);

        $this->assertStringContainsString("return \$this->hasOne(SiswaKelas::class, 'kelas_id')", $relation);
        $this->assertStringNotContainsString('$this->tahun_pelajaran_id', $relation);
    }
}
