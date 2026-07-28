<?php

namespace Tests\Feature;

use App\Http\Middleware\ApplyUserImpersonation;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\User;
use App\Models\UserImpersonation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserImpersonationFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_session_survives_full_student_impersonation_flow(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'impersonate-users',
            'guard_name' => 'web',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $siswaRole = Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permission);

        $suffix = Str::lower(Str::random(10));
        $admin = User::create([
            'name' => 'Admin Impersonation Test',
            'username' => "admin-{$suffix}",
            'email' => "admin-{$suffix}@example.test",
            'password' => Hash::make('secret'),
            'role' => 'admin',
            'is_active' => true,
            'is_first_login' => false,
        ]);
        $admin->assignRole($adminRole);

        $student = User::create([
            'name' => 'Siswa Impersonation Test',
            'username' => "siswa-{$suffix}",
            'email' => "siswa-{$suffix}@example.test",
            'password' => Hash::make('secret'),
            'role' => 'siswa',
            'is_active' => true,
            'is_first_login' => false,
        ]);
        $student->assignRole($siswaRole);

        $siswa = Siswa::create([
            'user_id' => $student->id,
            'nisn' => '9'.random_int(100000000, 999999999),
            'nama_lengkap' => $student->name,
            'jenis_kelamin' => 'L',
            'data_ortu_completed' => true,
            'data_diri_completed' => true,
        ]);

        $this->actingAs($admin);

        $start = $this->post(route('admin.impersonation.siswa.start', $siswa));
        $start->assertRedirect(route('siswa.dashboard'));
        $start->assertCookie(ApplyUserImpersonation::COOKIE_NAMES['siswa']);
        $this->assertSame(
            ApplyUserImpersonation::COOKIE_PATHS['siswa'],
            $start->getCookie(ApplyUserImpersonation::COOKIE_NAMES['siswa'], false)->getPath()
        );
        $this->assertAuthenticatedAs($admin);

        $plainToken = $start
            ->getCookie(ApplyUserImpersonation::COOKIE_NAMES['siswa'])
            ->getValue();

        $record = UserImpersonation::query()
            ->where('impersonator_id', $admin->id)
            ->where('target_user_id', $student->id)
            ->firstOrFail();

        $this->assertSame(hash('sha256', $plainToken), $record->token_hash);
        $this->assertNull($record->ended_at);

        $dashboard = $this
            ->withCookie(ApplyUserImpersonation::COOKIE_NAMES['siswa'], $plainToken)
            ->get(route('siswa.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('Mode Login As:');
        $dashboard->assertSee($student->name);
        $this->assertAuthenticatedAs($admin);

        $stop = $this
            ->withCookie(ApplyUserImpersonation::COOKIE_NAMES['siswa'], $plainToken)
            ->post(route('siswa.impersonation.stop'));

        $stop->assertRedirect(route('admin.siswa.index'));
        $stop->assertCookieExpired(ApplyUserImpersonation::COOKIE_NAMES['siswa']);
        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($record->fresh()->ended_at);
    }

    public function test_gtk_impersonation_uses_its_own_cookie_path_and_keeps_admin_authenticated(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'impersonate-users',
            'guard_name' => 'web',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $gtkRole = Role::firstOrCreate(['name' => 'GTK', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permission);

        $suffix = Str::lower(Str::random(10));
        $admin = User::create([
            'name' => 'Admin GTK Impersonation Test',
            'username' => "admin-gtk-{$suffix}",
            'email' => "admin-gtk-{$suffix}@example.test",
            'password' => Hash::make('secret'),
            'role' => 'admin',
            'is_active' => true,
            'is_first_login' => false,
        ]);
        $admin->assignRole($adminRole);

        $gtkUser = User::create([
            'name' => 'GTK Impersonation Test',
            'username' => "gtk-{$suffix}",
            'email' => "gtk-{$suffix}@example.test",
            'password' => Hash::make('secret'),
            'role' => 'gtk',
            'is_active' => true,
            'is_first_login' => false,
        ]);
        $gtkUser->assignRole($gtkRole);

        $gtk = Gtk::create([
            'user_id' => $gtkUser->id,
            'nama_lengkap' => $gtkUser->name,
            'nik' => (string) random_int(1000000000000000, 8999999999999999),
            'jenis_kelamin' => 'L',
        ]);

        $this->actingAs($admin);

        $start = $this->post(route('admin.impersonation.gtk.start', $gtk));
        $start->assertRedirect(route('admin.gtk.dashboard'));
        $start->assertCookie(ApplyUserImpersonation::COOKIE_NAMES['gtk']);
        $this->assertSame(
            ApplyUserImpersonation::COOKIE_PATHS['gtk'],
            $start->getCookie(ApplyUserImpersonation::COOKIE_NAMES['gtk'], false)->getPath()
        );
        $this->assertAuthenticatedAs($admin);

        $plainToken = $start->getCookie(ApplyUserImpersonation::COOKIE_NAMES['gtk'])->getValue();

        $stop = $this
            ->withCookie(ApplyUserImpersonation::COOKIE_NAMES['gtk'], $plainToken)
            ->post(route('admin.gtk.impersonation.stop'));

        $stop->assertRedirect(route('admin.gtk.index'));
        $stop->assertCookieExpired(ApplyUserImpersonation::COOKIE_NAMES['gtk']);
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('user_impersonations', [
            'impersonator_id' => $admin->id,
            'target_user_id' => $gtkUser->id,
            'target_type' => 'gtk',
            'ended_reason' => 'manual',
        ]);
    }
}
