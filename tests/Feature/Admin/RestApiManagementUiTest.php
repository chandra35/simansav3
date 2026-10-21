<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\RestApiController;
use Tests\TestCase;

class RestApiManagementUiTest extends TestCase
{
    public function test_rest_api_admin_routes_are_registered(): void
    {
        $this->assertTrue(\Route::has('admin.pengaturan.rest-api.index'));
        $this->assertTrue(\Route::has('admin.pengaturan.rest-api.tokens.store'));
        $this->assertTrue(\Route::has('admin.pengaturan.rest-api.tokens.destroy'));
    }

    public function test_controller_passes_abilities_and_available_abilities(): void
    {
        $controllerContent = file_get_contents(app_path('Http/Controllers/Admin/RestApiController.php'));

        $this->assertStringContainsString("'abilities' => self::ABILITIES", $controllerContent);
        $this->assertStringContainsString("'availableAbilities' => self::ABILITIES", $controllerContent);
        $this->assertStringContainsString("'user_id' => ['required', 'string', 'exists:users,id']", $controllerContent);
        $this->assertStringContainsString("int|string \$tokenId", $controllerContent);
    }

    public function test_view_has_abilities_fallback_guard_and_clean_badges(): void
    {
        $viewContent = file_get_contents(resource_path('views/admin/pengaturan/rest-api.blade.php'));

        $this->assertStringContainsString('$abilities = $abilities ?? $availableAbilities ??', $viewContent);
        $this->assertStringContainsString("str_replace('badge-', '', \$ability['badge'] ?? 'secondary')", $viewContent);
        $this->assertStringContainsString("str_replace('badge-', '', \$abilities[\$ab]['badge'] ?? 'secondary')", $viewContent);
        $this->assertStringContainsString('admin.pengaturan.rest-api.tokens.store', $viewContent);
        $this->assertStringContainsString('admin.pengaturan.rest-api.tokens.destroy', $viewContent);
    }
}
