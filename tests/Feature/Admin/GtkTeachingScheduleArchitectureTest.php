<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class GtkTeachingScheduleArchitectureTest extends TestCase
{
    public function test_gtk_action_exposes_a_read_only_teaching_schedule(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/GtkController.php'));
        $index = file_get_contents(resource_path('views/admin/gtk/index.blade.php'));
        $schedule = file_get_contents(resource_path('views/admin/gtk/schedule.blade.php'));

        $this->assertStringContainsString('data-action="schedule"', $controller);
        $this->assertStringContainsString("route('admin.gtk.schedule'", $controller);
        $this->assertStringContainsString("action === 'schedule'", $index);
        $this->assertStringContainsString("where('gtk_id', \$gtk->id)", $controller);
        $this->assertStringContainsString("where('is_active', true)", $controller);
        $this->assertStringContainsString('Tahun Pelajaran', $schedule);
        $this->assertStringContainsString('Mata Pelajaran', $schedule);
        $this->assertStringContainsString('Rombel Diampu', $schedule);
        $this->assertStringNotContainsString('<form method="POST"', $schedule);
    }

    public function test_schedule_route_is_guarded_by_view_gtk_permission(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.gtk.schedule');

        $this->assertNotNull($route);
        $this->assertSame('admin/gtk/{gtk}/jadwal', $route->uri());
        $this->assertContains('permission:view-gtk', $route->gatherMiddleware());
    }
}
