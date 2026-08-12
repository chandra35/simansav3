<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HotspotSecurityArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_hotspot_routes_have_view_and_manage_permissions(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');

        $this->assertStringContainsString("Route::middleware('permission:view-hotspot')->prefix('hotspot')", $routes);
        $this->assertStringContainsString("Route::middleware('permission:manage-hotspot')->group", $routes);
    }

    public function test_insecure_fallback_passwords_are_not_used(): void
    {
        $command = file_get_contents($this->root.'/app/Console/Commands/HotspotSync.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');

        $this->assertStringNotContainsString("fallbackPassword = 'man1metro'", $command);
        $this->assertStringNotContainsString('return $hotspot->username;', $controller);
        $this->assertStringContainsString('REJECT-INSECURE', $command);
    }

    public function test_guest_password_is_encrypted_and_expiry_is_reconciled(): void
    {
        $model = file_get_contents($this->root.'/app/Models/HotspotUser.php');
        $command = file_get_contents($this->root.'/app/Console/Commands/HotspotSync.php');

        $this->assertStringContainsString("'password_secret' => 'encrypted'", $model);
        $this->assertStringContainsString('deactivateExpiredGuests', $command);
    }

    public function test_secure_password_reset_reactivates_eligible_account(): void
    {
        $observer = file_get_contents($this->root.'/app/Observers/UserObserver.php');

        $this->assertStringContainsString('isEligibleForRadius()', $observer);
        $this->assertStringContainsString('isSecurePassword($plainPassword)', $observer);
        $this->assertStringContainsString("'is_active' => true", $observer);
    }

    public function test_public_deployment_scripts_do_not_contain_production_secrets(): void
    {
        $scripts = file_get_contents($this->root.'/deploy/setup_mikrotik.sh')
            .file_get_contents($this->root.'/deploy/install-radius.sh');

        $this->assertDoesNotMatchRegularExpression('/RADIUS_SECRET="[A-Za-z0-9@!?]{8,}"/', $scripts);
        $this->assertDoesNotMatchRegularExpression('/RADIUS_DB_PASS="[A-Za-z0-9@!?]{8,}"/', $scripts);
        $this->assertStringNotContainsString('MIKROTIK_PASS="vscode"', $scripts);
        $this->assertStringNotContainsString('echo " MikroTik secret: ${RADIUS_SECRET}"', $scripts);
    }
}
