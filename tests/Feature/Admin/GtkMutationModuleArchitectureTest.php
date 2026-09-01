<?php

namespace Tests\Feature\Admin;

use App\Models\Gtk;
use App\Models\PenugasanGtk;
use App\Models\User;
use App\Services\GtkOnboardingService;
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
        $assignment = PenugasanGtk::query()->where('status', 'active')
            ->whereHas('gtk', fn ($query) => $query->where('status_aktif', true)
                ->when($admin, fn ($gtk) => $gtk->where('user_id', '!=', $admin->id))
                ->whereHas('user', fn ($user) => $user->where('is_active', true)))
            ->with('gtk.user')->first();
        $gtk = $assignment?->gtk;
        if (! $admin || ! $gtk || ! $assignment) {
            $this->markTestSkipped('Penugasan GTK aktif atau Super Admin tidak tersedia untuk simulasi transaksi.');
        }

        $this->withSession([])->actingAs($admin);
        request()->setLaravelSession(app('session.store'));
        $service = app(GtkStatusService::class);
        $initialHistoryCount = \App\Models\MutasiGtk::count();
        $service->change($gtk, ['status_baru' => false, 'alasan' => 'pensiun', 'tanggal_efektif' => today()->toDateString(), 'keterangan' => 'Simulasi test']);

        $this->assertFalse($gtk->fresh()->status_aktif);
        $this->assertFalse($gtk->user->fresh()->is_active);
        $this->assertSame('ended', $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->selesai_tugas);
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

    public function test_new_and_incoming_gtk_share_atomic_onboarding_with_distinct_history(): void
    {
        $service = file_get_contents(app_path('Services/GtkOnboardingService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/GtkController.php'));
        $mutationController = file_get_contents(app_path('Http/Controllers/Admin/GtkMutationController.php'));

        $this->assertStringContainsString('DB::transaction', $service);
        $this->assertStringContainsString("'status_sebelumnya' => null", $service);
        $this->assertStringContainsString("\$onboarding->create(\$validated, 'gtk_baru')", $controller);
        $this->assertStringContainsString("\$onboarding->create(\$data, 'mutasi_masuk')", $mutationController);
        $this->assertStringContainsString('gunakan Aktif Kembali agar tidak membuat data ganda', $mutationController);
        $this->assertStringContainsString('unique:users,username', $controller);
        $this->assertStringContainsString("Rule::in(\$request->kategori_ptk === 'Pendidik'", $mutationController);
    }

    public function test_incoming_gtk_form_requires_both_mutation_and_create_permissions(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/GtkMutationController.php'));
        $view = file_get_contents(resource_path('views/admin/gtk-mutation/create.blade.php'));

        $this->assertStringContainsString("\$this->authorize('manage-status-gtk')", $controller);
        $this->assertStringContainsString("\$this->authorize('create-gtk')", $controller);
        $this->assertStringContainsString('Registrasi GTK dari Instansi Lain', $view);
        $this->assertStringContainsString('name="instansi_asal"', $view);
    }

    public function test_onboarding_creates_profile_account_and_initial_history_atomically(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }
        $this->withSession([])->actingAs($admin);
        request()->setLaravelSession(app('session.store'));
        $nik = '9999'.str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT);

        $gtk = app(GtkOnboardingService::class)->create([
            'nama_lengkap' => 'GTK MUTASI TEST', 'nik' => $nik, 'nip' => null,
            'jenis_kelamin' => 'L', 'kategori_ptk' => 'Pendidik', 'jenis_ptk' => 'Guru Mapel',
            'status_kepegawaian' => 'PNS', 'tanggal_efektif' => today()->toDateString(),
            'instansi_asal' => 'MADRASAH ASAL TEST', 'keterangan' => 'Rollback otomatis',
        ], 'mutasi_masuk');

        $this->assertTrue($gtk->status_aktif);
        $this->assertTrue($gtk->user->is_active);
        $this->assertSame('gtk', $gtk->user->role);
        $this->assertSame('Pendidik', $gtk->kategori_ptk);
        $this->assertSame('Guru Mapel', $gtk->jenis_ptk);
        $this->assertDatabaseHas('mutasi_gtk', ['gtk_id' => $gtk->id, 'alasan' => 'mutasi_masuk', 'instansi_asal_tujuan' => 'MADRASAH ASAL TEST']);
    }

    public function test_legacy_student_role_does_not_block_a_linked_gtk_account(): void
    {
        $middleware = file_get_contents(app_path('Http/Middleware/AdminMiddleware.php'));
        $onboarding = file_get_contents(app_path('Services/GtkOnboardingService.php'));
        $import = file_get_contents(app_path('Imports/GtkImport.php'));

        $this->assertStringContainsString("! \$user->gtk()->exists()", $middleware);
        $this->assertStringContainsString("'role' => 'gtk'", $onboarding);
        $this->assertStringContainsString("'role' => 'gtk'", $import);
    }

    public function test_legacy_gtk_history_is_backfilled_without_claiming_it_is_new_or_incoming(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_08_13_110100_backfill_initial_gtk_history.php'));

        $this->assertStringContainsString("'alasan' => 'data_awal'", $migration);
        $this->assertStringContainsString("'status_sebelumnya' => null", $migration);
        $this->assertStringContainsString('Snapshot data GTK sebelum modul histori diterapkan.', $migration);
    }
}
