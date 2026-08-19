<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HotspotInactiveReasonTest extends TestCase
{
    public function test_inactive_reason_is_persisted_and_backfilled(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = file_get_contents($root.'/database/migrations/2026_08_19_170000_add_inactive_reason_to_hotspot_users.php');
        $model = file_get_contents($root.'/app/Models/HotspotUser.php');

        $this->assertStringContainsString("string('inactive_reason_code', 40)", $migration);
        $this->assertStringContainsString("backfillReason('alumni'", $migration);
        $this->assertStringContainsString("backfillReason('mutation'", $migration);
        $this->assertStringContainsString("backfillReason('credentials_missing'", $migration);
        $this->assertStringContainsString("backfillReason('user_removed'", $migration);
        $this->assertStringContainsString("string \$reasonCode = 'ineligible'", $model);
        $this->assertStringContainsString("'inactive_reason_code' => \$reasonCode", $model);
        $this->assertStringContainsString("if (\$this->is_active)", $model);
    }

    public function test_account_directory_filters_and_explains_inactive_categories(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/HotspotController.php');
        $view = file_get_contents($root.'/resources/views/admin/hotspot/index.blade.php');

        $this->assertStringContainsString("filled('account_state')", $controller);
        $this->assertStringContainsString("'alumni' => \$query->where('is_active', false)->where('inactive_reason_code', 'alumni')", $controller);
        $this->assertStringContainsString("'credentials_missing' => \$query->where('is_active', false)->where('inactive_reason_code', 'credentials_missing')", $controller);
        $this->assertStringContainsString("'Password belum tersedia'", $controller);
        $this->assertStringContainsString("d.account_state = \$('#filterAccountState').val()", $view);
        $this->assertStringNotContainsString('let activeFilter', $view);
    }
}
