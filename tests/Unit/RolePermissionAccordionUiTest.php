<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RolePermissionAccordionUiTest extends TestCase
{
    public function test_role_permission_forms_use_the_shared_feature_accordion(): void
    {
        $root = dirname(__DIR__, 2);
        $create = file_get_contents($root.'/resources/views/admin/roles/create.blade.php');
        $edit = file_get_contents($root.'/resources/views/admin/roles/edit.blade.php');
        $accordion = file_get_contents($root.'/resources/views/admin/roles/partials/permission-accordion.blade.php');
        $styles = file_get_contents($root.'/resources/views/admin/roles/partials/permission-accordion-assets.blade.php');

        $this->assertStringContainsString("@include('admin.roles.partials.permission-accordion'", $create);
        $this->assertStringContainsString("@include('admin.roles.partials.permission-accordion'", $edit);
        $this->assertStringNotContainsString('simansa-check-grid', $create);
        $this->assertStringNotContainsString('simansa-check-grid', $edit);
        $this->assertStringContainsString('card bg-gradient-primary text-white mb-4 simansa-role-form-hero', $create);
        $this->assertStringContainsString('card bg-gradient-primary text-white mb-4 simansa-role-form-hero', $edit);
        $this->assertStringContainsString('breadcrumb float-sm-right', $create);
        $this->assertStringContainsString('card card-outline card-primary simansa-form-card', $edit);
        $this->assertStringContainsString('data-toggle="collapse"', $accordion);
        $this->assertStringContainsString('aria-expanded="false"', $accordion);
        $this->assertStringContainsString('class="collapse"', $accordion);
        $this->assertStringNotContainsString("'show'", $accordion);
        $this->assertStringContainsString('data-permission-group=', $accordion);
        $this->assertStringContainsString('simansa-role-permission-row', $accordion);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr))', $styles);
        $this->assertStringContainsString('grid-template-columns: 1fr', $styles);
    }
}
