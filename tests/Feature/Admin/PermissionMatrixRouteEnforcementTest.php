<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\PermissionSyncService;
use Illuminate\Routing\Route;
use Tests\TestCase;

class PermissionMatrixRouteEnforcementTest extends TestCase
{
    public function test_information_catalog_has_separate_read_and_write_permissions(): void
    {
        $modules = app(PermissionSyncService::class)->getModuleDefinitions();

        $this->assertSame([
            'view-pengumuman',
            'create-pengumuman',
            'edit-pengumuman',
            'delete-pengumuman',
        ], $modules['informasi']['permissions']);
    }

    public function test_read_and_write_routes_follow_permission_matrix(): void
    {
        $expectations = [
            'admin.tahun-pelajaran.index' => 'permission:view-tahun-pelajaran',
            'admin.tahun-pelajaran.store' => 'permission:create-tahun-pelajaran',
            'admin.tahun-pelajaran.update' => 'permission:edit-tahun-pelajaran',
            'admin.tahun-pelajaran.destroy' => 'permission:delete-tahun-pelajaran',
            'admin.kurikulum.index' => 'permission:view-kurikulum',
            'admin.kurikulum.store' => 'permission:create-kurikulum',
            'admin.kurikulum.update' => 'permission:edit-kurikulum',
            'admin.kurikulum.destroy' => 'permission:delete-kurikulum',
            'admin.mapel.index' => 'permission:view-mapel',
            'admin.mapel.store' => 'permission:create-mapel',
            'admin.mapel.update' => 'permission:edit-mapel',
            'admin.mapel.destroy' => 'permission:delete-mapel',
            'admin.nilai.index' => 'permission:view-nilai',
            'admin.nilai.upload' => 'permission:input-nilai',
            'admin.nilai.delete-semester' => 'permission:delete-nilai',
            'admin.rdm-sync.index' => 'permission:view-rdm',
            'admin.rdm-sync.apply' => 'permission:manage-rdm',
            'admin.rdm-mapel-mapping.store' => 'permission:manage-rdm-mapping',
            'admin.pengumuman.index' => 'permission:view-pengumuman',
            'admin.pengumuman.store' => 'permission:create-pengumuman',
            'admin.pengumuman.update' => 'permission:edit-pengumuman',
            'admin.pengumuman.destroy' => 'permission:delete-pengumuman',
            'admin.kalender-akademik.index' => 'permission:view-kalender-akademik',
            'admin.kalender-akademik.store' => 'permission:manage-kalender-akademik',
            'admin.kalender-akademik.destroy' => 'permission:manage-kalender-akademik',
        ];

        foreach ($expectations as $name => $middleware) {
            $route = app('router')->getRoutes()->getByName($name);

            $this->assertInstanceOf(Route::class, $route, "Route {$name} tidak ditemukan.");
            $this->assertContains($middleware, $route->gatherMiddleware(), "Middleware {$name} tidak sesuai.");
        }
    }

    public function test_sidebar_uses_each_modules_own_view_permission(): void
    {
        $menu = file_get_contents(config_path('adminlte.php'));

        $this->assertMatchesRegularExpression("/'text' => 'Mata Pelajaran',[\\s\\S]*?'can' => 'view-mapel'/", $menu);
        $this->assertMatchesRegularExpression("/'text' => 'Nilai Siswa',[\\s\\S]*?'can' => 'view-nilai'/", $menu);
        $this->assertMatchesRegularExpression("/'text' => 'RDM',[\\s\\S]*?'can' => 'view-rdm'/", $menu);
    }

    public function test_write_controls_are_guarded_by_their_write_permissions(): void
    {
        $mapel = file_get_contents(resource_path('views/admin/mapel/index.blade.php'));
        $nilai = file_get_contents(resource_path('views/admin/nilai/index.blade.php'));
        $kalender = file_get_contents(resource_path('views/admin/kalender-akademik/index.blade.php'));
        $tahunController = file_get_contents(app_path('Http/Controllers/Admin/TahunPelajaranController.php'));
        $mapelController = file_get_contents(app_path('Http/Controllers/Admin/MataPelajaranController.php'));

        $this->assertStringContainsString("@can('create-mapel')", $mapel);
        $this->assertStringContainsString("@can('view-rdm')", $mapel);
        $this->assertStringContainsString("@can('input-nilai')", $nilai);
        $this->assertStringContainsString("@can('manage-kalender-akademik')", $kalender);
        $this->assertStringContainsString("can('edit-tahun-pelajaran')", $tahunController);
        $this->assertStringContainsString("can('delete-tahun-pelajaran')", $tahunController);
        $this->assertStringContainsString("can('edit-mapel')", $mapelController);
        $this->assertStringContainsString("can('delete-mapel')", $mapelController);
    }

    public function test_production_waka_permissions_are_read_only_at_route_boundary(): void
    {
        $waka = User::role('WAKA')->where('is_active', true)->first();
        if (! $waka) {
            $this->markTestSkipped('Akun WAKA aktif tidak tersedia.');
        }

        $this->actingAs($waka)->get(route('admin.tahun-pelajaran.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.tahun-pelajaran.create'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.kurikulum.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.kurikulum.create'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.mapel.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.mapel.create'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.nilai.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.nilai.upload-form'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.kalender-akademik.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.rdm-sync.index'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.polling.index'))->assertOk();
        $this->actingAs($waka)->get(route('admin.polling.create'))->assertForbidden();

        $this->actingAs($waka)->get(route('admin.pengumuman.index'))->assertForbidden();
    }
}
