<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserIndexUiArchitectureTest extends TestCase
{
    public function test_user_table_is_compact_and_groups_related_information(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/UserController.php'));
        $view = file_get_contents(resource_path('views/admin/users/index.blade.php'));

        $this->assertStringContainsString("'identity' => \$identityHtml", $controller);
        $this->assertStringContainsString("'contact' => \$contactHtml", $controller);
        $this->assertStringContainsString('simansa-user-presence', $controller);
        $this->assertStringContainsString("'gtk:id,user_id,nama_lengkap,jenis_kelamin,foto_profile'", $controller);
        $this->assertStringContainsString("'siswa:id,user_id,nama_lengkap,jenis_kelamin,foto_profile'", $controller);
        $this->assertStringContainsString('$user->gtk?->foto_profile_url', $controller);
        $this->assertStringContainsString("\$request->account_type", $controller);
        $this->assertStringContainsString('$this->accountDirectoryQuery()->with([', $controller);
        $this->assertStringContainsString("\$gtk->where('status_aktif', true)", $controller);
        $this->assertStringContainsString("\$siswa->where('status_siswa', 'aktif')", $controller);
        $this->assertStringContainsString('dropdown simansa-user-actions', $controller);
        $this->assertStringContainsString("{ data: 'identity', name: 'name' }", $view);
        $this->assertStringContainsString("{ data: 'contact', name: 'email' }", $view);
        $this->assertStringNotContainsString('scrollX: true', $view);
        $this->assertStringContainsString('drawCallback: function()', $view);
        $this->assertStringContainsString(".addClass('dropup')", $view);
        $this->assertStringContainsString('id="filterAccountType"', $view);
        $this->assertStringContainsString('class="custom-select custom-select-sm"', $view);
        $this->assertStringNotContainsString("$('#filterRole, #filterAccountType').select2", $view);
        $this->assertStringContainsString('simansa-reset-filter', $view);
        $this->assertStringContainsString('simansa-users-operation-hero', $view);
        $this->assertStringContainsString('card bg-gradient-primary text-white', $view);
        $this->assertStringContainsString('<div class="col-lg-8">', $view);
        $this->assertStringContainsString('<i class="fas fa-users-cog text-primary mr-1"></i> User & Role', $view);
    }

    public function test_user_page_renders_for_super_admin(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Daftar Akun Pengguna')
            ->assertSee('Status & Aktivitas', false);
    }

    public function test_user_data_endpoint_returns_the_compact_cells(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $response = $this->actingAs($admin)->getJson(route('admin.users.data', [
            'draw' => 1, 'start' => 0, 'length' => 1,
        ]));

        $response->assertOk()->assertJsonStructure([
            'draw', 'recordsTotal', 'recordsFiltered',
            'data' => [['DT_RowIndex', 'identity', 'contact', 'roles', 'status', 'action']],
        ]);
    }

    public function test_account_type_filters_return_profile_photos(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        foreach (['gtk' => 'GTK', 'siswa' => 'Siswa'] as $type => $label) {
            $response = $this->actingAs($admin)->getJson(route('admin.users.data', [
                'draw' => 1, 'start' => 0, 'length' => 3, 'account_type' => $type,
            ]))->assertOk();
            $identities = collect($response->json('data'))->pluck('identity');
            $this->assertNotEmpty($identities);
            $this->assertTrue($identities->every(fn ($html) => str_contains($html, '<img ') && str_contains($html, $label)));
        }
    }

    public function test_account_directory_excludes_inactive_gtk_alumni_and_outgoing_students(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $expectedTotal = User::query()->where(function ($query): void {
            $query->where(function ($systemAccount): void {
                $systemAccount->whereDoesntHave('gtk')->whereDoesntHave('siswa');
            })->orWhereHas('gtk', fn ($gtk) => $gtk->where('status_aktif', true))
                ->orWhereHas('siswa', fn ($siswa) => $siswa->where('status_siswa', 'aktif'));
        })->count();
        $expectedStudents = User::query()
            ->whereHas('siswa', fn ($siswa) => $siswa->where('status_siswa', 'aktif'))
            ->count();

        $all = $this->actingAs($admin)->getJson(route('admin.users.data', [
            'draw' => 1, 'start' => 0, 'length' => 1,
        ]))->assertOk();
        $students = $this->actingAs($admin)->getJson(route('admin.users.data', [
            'draw' => 2, 'start' => 0, 'length' => 1, 'account_type' => 'siswa',
        ]))->assertOk();

        $this->assertSame($expectedTotal, $all->json('recordsTotal'));
        $this->assertSame($expectedStudents, $students->json('recordsFiltered'));

        $inactiveStudent = User::query()
            ->whereHas('siswa', fn ($siswa) => $siswa->where('status_siswa', '!=', 'aktif'))
            ->first();

        if ($inactiveStudent) {
            $hidden = $this->actingAs($admin)->getJson(route('admin.users.data', [
                'draw' => 3,
                'start' => 0,
                'length' => 10,
                'account_type' => 'siswa',
                'search' => ['value' => $inactiveStudent->username],
            ]))->assertOk();

            $this->assertSame(0, $hidden->json('recordsFiltered'));
            $this->assertSame([], $hidden->json('data'));
        }
    }
}
