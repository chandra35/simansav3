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
        $this->assertStringContainsString('dropdown simansa-user-actions', $controller);
        $this->assertStringContainsString("{ data: 'identity', name: 'name' }", $view);
        $this->assertStringContainsString("{ data: 'contact', name: 'email' }", $view);
        $this->assertStringNotContainsString('scrollX: true', $view);
        $this->assertStringContainsString('drawCallback: function()', $view);
        $this->assertStringContainsString(".addClass('dropup')", $view);
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
}
