<?php

namespace Tests\Feature\Admin;

use App\Services\PermissionSyncService;
use Illuminate\Routing\Route;
use Tests\TestCase;

class PermissionMatrixComprehensiveEnforcementTest extends TestCase
{
    public function test_core_crud_routes_are_guarded_by_the_matching_permission(): void
    {
        $expectations = [
            'admin.siswa.index' => 'permission:view-siswa',
            'admin.siswa.store' => 'permission:create-siswa',
            'admin.siswa.update' => 'permission:edit-siswa',
            'admin.siswa.destroy' => 'permission:delete-siswa',
            'admin.kelas.index' => 'permission:view-kelas',
            'admin.kelas.store' => 'permission:create-kelas',
            'admin.kelas.update' => 'permission:edit-kelas',
            'admin.kelas.destroy' => 'permission:delete-kelas',
            'admin.users.index' => 'permission:view-users',
            'admin.users.store' => 'permission:create-users',
            'admin.users.update' => 'permission:edit-users',
            'admin.users.destroy' => 'permission:delete-users',
            'admin.mutasi-siswa.index' => 'permission:view-mutasi',
            'admin.mutasi-siswa.store' => 'permission:create-mutasi',
            'admin.mutasi-siswa.update' => 'permission:edit-mutasi',
            'admin.mutasi-siswa.destroy' => 'permission:delete-mutasi',
            'admin.prestasi-siswa.index' => 'permission:view-prestasi-siswa',
            'admin.prestasi-siswa.store' => 'permission:create-prestasi-siswa',
            'admin.prestasi-siswa.update' => 'permission:edit-prestasi-siswa',
            'admin.prestasi-siswa.destroy' => 'permission:delete-prestasi-siswa',
            'admin.ekstrakurikuler.index' => 'permission:view-ekstrakurikuler',
            'admin.ekstrakurikuler.store' => 'permission:create-ekstrakurikuler',
            'admin.ekstrakurikuler.update' => 'permission:edit-ekstrakurikuler',
            'admin.ekstrakurikuler.destroy' => 'permission:delete-ekstrakurikuler',
            'admin.pembayaran.index' => 'permission:view-keuangan',
            'admin.pembayaran.store' => 'permission:manage-keuangan',
            'admin.surat-keterangan.index' => 'permission:view-layanan-surat',
            'admin.surat-keterangan.store' => 'permission:manage-layanan-surat',
            'admin.activity-logs.index' => 'permission:view-activity-log',
            'admin.monitoring.users' => 'permission:view-monitoring-users',
            'admin.monitoring.users.force-logout' => 'permission:manage-monitoring-users',
            'admin.exam-browser.index' => 'permission:manage-cbt',
        ];

        foreach ($expectations as $name => $middleware) {
            $route = app('router')->getRoutes()->getByName($name);

            $this->assertInstanceOf(Route::class, $route, "Route {$name} tidak ditemukan.");
            $this->assertContains($middleware, $route->gatherMiddleware(), "Middleware {$name} tidak sesuai.");
        }
    }

    public function test_kesiswaan_catalog_has_separate_read_and_write_permissions(): void
    {
        $permissions = app(PermissionSyncService::class)->getModuleDefinitions()['kesiswaan']['permissions'];

        foreach ([
            'view-prestasi-siswa', 'create-prestasi-siswa', 'edit-prestasi-siswa',
            'delete-prestasi-siswa', 'verify-prestasi-siswa', 'view-ekstrakurikuler',
            'create-ekstrakurikuler', 'edit-ekstrakurikuler', 'delete-ekstrakurikuler',
            'manage-anggota-ekstrakurikuler',
        ] as $permission) {
            $this->assertContains($permission, $permissions);
        }
    }

    public function test_server_rendered_write_controls_check_write_permissions(): void
    {
        $controllers = [
            file_get_contents(app_path('Http/Controllers/Admin/UserController.php')),
            file_get_contents(app_path('Http/Controllers/Admin/PrestasiSiswaController.php')),
            file_get_contents(app_path('Http/Controllers/Admin/EkstrakurikulerController.php')),
            file_get_contents(app_path('Http/Controllers/Admin/PembayaranController.php')),
            file_get_contents(app_path('Http/Controllers/Admin/SuratKeteranganController.php')),
        ];
        $views = [
            file_get_contents(resource_path('views/admin/users/index.blade.php')),
            file_get_contents(resource_path('views/admin/prestasi-siswa/index.blade.php')),
            file_get_contents(resource_path('views/admin/ekstrakurikuler/index.blade.php')),
            file_get_contents(resource_path('views/admin/ekstrakurikuler/anggota.blade.php')),
        ];
        $source = implode("\n", [...$controllers, ...$views]);

        foreach ([
            'create-users', 'edit-users', 'delete-users', 'assign-roles',
            'create-prestasi-siswa', 'edit-prestasi-siswa', 'delete-prestasi-siswa',
            'create-ekstrakurikuler', 'edit-ekstrakurikuler', 'delete-ekstrakurikuler',
            'manage-anggota-ekstrakurikuler',
            'manage-keuangan', 'manage-layanan-surat',
        ] as $permission) {
            $this->assertStringContainsString($permission, $source);
        }

        $this->assertStringNotContainsString("authorize('create-user')", $source);
        $this->assertStringNotContainsString("@can('create-user')", $source);
    }
}
