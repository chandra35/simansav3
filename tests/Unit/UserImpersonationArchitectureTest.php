<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserImpersonationArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_impersonation_uses_separate_scoped_cookies_without_replacing_admin_login(): void
    {
        $middleware = file_get_contents($this->root.'/app/Http/Middleware/ApplyUserImpersonation.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/UserImpersonationController.php');

        $this->assertStringContainsString("'siswa' => '/siswa'", $middleware);
        $this->assertStringContainsString("'gtk' => '/admin/gtk'", $middleware);
        $this->assertStringContainsString("hash('sha256', \$token)", $middleware);
        $this->assertStringContainsString("Auth::guard('web')->setUser(\$target)", $middleware);
        $this->assertStringContainsString("Auth::guard('web')->setUser(\$sessionUser)", $middleware);
        $this->assertStringNotContainsString('Auth::login(', $middleware.$controller);
        $this->assertStringNotContainsString('Auth::logout(', $middleware.$controller);
    }

    public function test_only_admin_roles_receive_the_login_as_permission(): void
    {
        $migration = file_get_contents(
            $this->root.'/database/migrations/2026_07_28_170000_create_user_impersonations_table.php'
        );
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/UserImpersonationController.php');

        $this->assertStringContainsString("'impersonate-users'", $migration);
        $this->assertStringContainsString("['Super Admin', 'Admin']", $migration);
        $this->assertStringNotContainsString("['Super Admin', 'Admin', 'Operator']", $migration);
        $this->assertStringContainsString("hasAnyRole(['Super Admin', 'Admin'])", $controller);
        $this->assertStringContainsString('Login As ke akun admin atau operator tidak diizinkan.', $controller);
    }

    public function test_student_and_gtk_actions_open_directly_in_a_new_tab(): void
    {
        $siswaController = file_get_contents($this->root.'/app/Http/Controllers/Admin/SiswaController.php');
        $gtkController = file_get_contents($this->root.'/app/Http/Controllers/Admin/GtkController.php');

        foreach ([$siswaController, $gtkController] as $source) {
            $this->assertStringContainsString("can('impersonate-users')", $source);
            $this->assertStringContainsString('target="_blank"', $source);
            $this->assertStringContainsString('fas fa-user-secret', $source);
            $this->assertStringNotContainsString('alasan', strtolower($source));
        }
    }

    public function test_routes_apply_impersonation_before_personal_permissions(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');

        $this->assertStringContainsString(
            "Route::middleware(['auth', 'impersonation:siswa'])->prefix('siswa')",
            $routes
        );
        $this->assertStringContainsString(
            "['impersonation:gtk', 'permission:view-gtk-dashboard']",
            $routes
        );
        $this->assertStringContainsString(
            "['impersonation:gtk', 'permission:change-password-gtk']",
            $routes
        );
        $this->assertStringContainsString("middleware('permission:impersonate-users')", $routes);
    }

    public function test_banner_exposes_identity_and_safe_return_to_admin(): void
    {
        $banner = file_get_contents($this->root.'/resources/views/partials/impersonation-banner.blade.php');
        $layout = file_get_contents(
            $this->root.'/resources/views/vendor/adminlte/partials/cwrapper/cwrapper-default.blade.php'
        );
        $middleware = file_get_contents($this->root.'/app/Http/Middleware/ApplyUserImpersonation.php');

        $this->assertStringContainsString('Mode Login As:', $banner);
        $this->assertStringContainsString('Kembali ke Admin', $banner);
        $this->assertStringContainsString('Perubahan password diblokir', $banner);
        $this->assertStringContainsString("@include('partials.impersonation-banner')", $layout);
        $this->assertStringContainsString('Perubahan password dinonaktifkan selama mode Login As.', $middleware);
    }
}
