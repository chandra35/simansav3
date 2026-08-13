<?php

namespace Tests\Feature\Admin;

use App\Models\Gtk;
use App\Models\User;
use App\Services\GtkStatusService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GtkMutationModuleArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mutation_permissions_are_registered_under_gtk_module(): void
    {
        $permissions = app(PermissionSyncService::class)->getModuleDefinitions()['gtk']['permissions'];

        $this->assertContains('view-mutasi-gtk', $permissions);
        $this->assertContains('manage-status-gtk', $permissions);
    }

    public function test_status_change_is_atomic_and_preserves_operational_history(): void
    {
        $service = file_get_contents(app_path('Services/GtkStatusService.php'));

        $this->assertStringContainsString('DB::transaction', $service);
        $this->assertStringContainsString("'is_active' => false", $service);
        $this->assertStringContainsString("'status' => 'ended'", $service);
        $this->assertStringContainsString("where('wali_kelas_id', \$gtk->user_id)", $service);
        $this->assertStringContainsString('Anda tidak dapat menonaktifkan akun GTK sendiri', $service);
        $this->assertStringNotContainsString('JadwalPelajaran::', $service);
    }

    public function test_gtk_status_history_is_exposed_through_permission_guarded_routes(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $view = file_get_contents(resource_path('views/admin/gtk-mutation/index.blade.php'));

        $this->assertStringContainsString('permission:view-mutasi-gtk', $routes);
        $this->assertStringContainsString('permission:manage-status-gtk', $routes);
        $this->assertStringContainsString('Data tidak dapat diedit untuk menjaga jejak audit', $view);
        $this->assertStringContainsString('Menonaktifkan GTK akan memblokir akun', $view);
    }

    public function test_gtk_account_form_cannot_bypass_mutation_history(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/GtkController.php'));
        $view = file_get_contents(resource_path('views/admin/gtk/edit.blade.php'));

        $this->assertStringNotContainsString("'is_active' => 'required|boolean'", $controller);
        $this->assertStringContainsString('Kelola melalui Mutasi & Status GTK', $view);
        $userController = file_get_contents(app_path('Http/Controllers/Admin/UserController.php'));
        $this->assertStringContainsString('Status akun GTK harus diubah melalui modul Mutasi & Status GTK', $userController);
    }

    public function test_deactivation_and_reactivation_keep_status_and_account_in_sync(): void
    {
        $admin = User::role('Super Admin')->first();
        $gtk = Gtk::query()->where('status_aktif', true)->whereHas('user', fn ($query) => $query->where('is_active', true))->first();
        if (! $admin || ! $gtk) {
            $this->markTestSkipped('Data GTK aktif atau Super Admin tidak tersedia untuk simulasi transaksi.');
        }

        $this->withSession([])->actingAs($admin);
        request()->setLaravelSession(app('session.store'));
        $service = app(GtkStatusService::class);
        $initialHistoryCount = \App\Models\MutasiGtk::count();
        $service->change($gtk, ['status_baru' => false, 'alasan' => 'pensiun', 'tanggal_efektif' => today()->toDateString(), 'keterangan' => 'Simulasi test']);

        $this->assertFalse($gtk->fresh()->status_aktif);
        $this->assertFalse($gtk->user->fresh()->is_active);
        $this->assertDatabaseHas('mutasi_gtk', ['gtk_id' => $gtk->id, 'status_baru' => false, 'alasan' => 'pensiun']);

        $service->change($gtk->fresh(), ['status_baru' => true, 'alasan' => 'aktif_kembali', 'tanggal_efektif' => today()->toDateString()]);
        $this->assertTrue($gtk->fresh()->status_aktif);
        $this->assertTrue($gtk->user->fresh()->is_active);
        $this->assertDatabaseCount('mutasi_gtk', $initialHistoryCount + 2);
    }

    public function test_super_admin_can_open_gtk_mutation_workspace(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $this->actingAs($admin)->get(route('admin.mutasi-gtk.index'))
            ->assertOk()
            ->assertSee('Mutasi &amp; Status GTK', false)
            ->assertSee('Riwayat Perubahan');
    }
}
