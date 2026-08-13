<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class GtkIndexDataUiTest extends TestCase
{
    public function test_gtk_data_endpoint_returns_vertical_identity_and_active_assignment_metadata(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $response = $this->actingAs($admin)->getJson(route('admin.gtk.data', [
            'draw' => 1, 'start' => 0, 'length' => 3,
        ]));

        $response->assertOk()->assertJsonStructure([
            'draw', 'recordsTotal', 'recordsFiltered',
            'data' => [['DT_RowIndex', 'identity', 'role_summary', 'status_summary', 'actions']],
        ]);
        $html = collect($response->json('data'))->pluck('identity')->join(' ')
            .collect($response->json('data'))->pluck('role_summary')->join(' ');
        $this->assertStringContainsString('simansa-gtk-profile__identifiers', $html);
        $this->assertStringContainsString('Penugasan aktif', $html);
    }
}
