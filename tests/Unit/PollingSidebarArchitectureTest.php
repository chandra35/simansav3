<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PollingSidebarArchitectureTest extends TestCase
{
    public function test_gtk_and_student_polling_menus_use_separate_audience_gates(): void
    {
        $menu = file_get_contents(dirname(__DIR__, 2).'/config/adminlte.php');
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/AuthServiceProvider.php');

        $this->assertSame(1, substr_count($menu, "'can' => 'sidebar-gtk-active-polling'"));
        $this->assertSame(1, substr_count($menu, "'can' => 'sidebar-siswa-active-polling'"));
        $this->assertStringNotContainsString("'can' => 'sidebar-active-polling'", $menu);

        $this->assertStringContainsString("Gate::define('sidebar-gtk-active-polling'", $provider);
        $this->assertStringContainsString("Gate::define('sidebar-siswa-active-polling'", $provider);
        $this->assertStringContainsString("! \$user->hasRole('Siswa')", $provider);
        $this->assertStringContainsString("['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK']", $provider);
    }
}
