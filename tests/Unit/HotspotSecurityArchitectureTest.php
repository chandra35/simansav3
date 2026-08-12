<?php

namespace Tests\Unit;

use App\Models\HotspotUser;
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
        $this->assertStringContainsString("\$hotspot->role === 'guru'", $controller);
        $this->assertStringContainsString("preg_match('/^\\d{16}$/', \$hotspot->username)", $controller);
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

    public function test_hotspot_has_dedicated_monitoring_auth_log_and_radius_profile_pages(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');
        $online = file_get_contents($this->root.'/resources/views/admin/hotspot/online.blade.php');

        $this->assertStringContainsString("->name('auth-logs')", $routes);
        $this->assertStringContainsString("->name('profiles.page')", $routes);
        $this->assertStringContainsString('classifyAuthResult', $controller);
        $this->assertStringContainsString("'photo_url'", $controller);
        $this->assertStringContainsString("'detail_url'", $controller);
        $this->assertStringContainsString("'kelas_url'", $controller);
        $this->assertStringContainsString('sessionDetailModal', $online);
    }

    public function test_hotspot_settings_centralizes_radius_and_nas_configuration(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');
        $menu = file_get_contents($this->root.'/config/adminlte.php');
        $account = file_get_contents($this->root.'/resources/views/admin/hotspot/index.blade.php');
        $settings = file_get_contents($this->root.'/resources/views/admin/hotspot/settings.blade.php');

        $this->assertStringContainsString("'settingsPage'])->middleware('permission:manage-hotspot')->name('settings')", $routes);
        $this->assertStringContainsString("'route' => 'admin.hotspot.settings'", $menu);
        $this->assertStringContainsString("'can' => 'manage-hotspot'", $menu);
        $this->assertStringContainsString('Manajemen Akun Hotspot', $account);
        $this->assertStringNotContainsString('id="mikrotikScript"', $account);
        $this->assertStringNotContainsString('id="btnAddNas"', $account);
        $this->assertStringNotContainsString('id="radiusStatusPanel"', $account);
        $this->assertStringContainsString('Detail Server FreeRADIUS', $settings);
        $this->assertStringContainsString('MikroTik / NAS', $settings);
        $this->assertStringContainsString("route('admin.hotspot.profiles.page')", $settings);
        $this->assertStringContainsString('$radiusDashboardUrl', $settings);
        $this->assertStringContainsString("badge-light text-{{ \$radiusConnected ? 'success' : 'danger' }}", $settings);
    }

    public function test_radius_auth_log_never_exposes_or_persists_attempted_password(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');
        $hardening = file_get_contents($this->root.'/deploy/harden-radius-postauth.sh');

        $this->assertStringContainsString("->select(['id', 'username', 'reply', 'authdate', 'class'])", $controller);
        $this->assertStringNotContainsString("'pass'", $controller);
        $this->assertStringContainsString("UPDATE radpostauth SET pass = ''", $hardening);
        $this->assertStringContainsString("replacement = \"'', \"", $hardening);
    }

    public function test_radius_profile_models_the_mikrotik_group_attribute(): void
    {
        $profile = file_get_contents($this->root.'/app/Models/HotspotRadiusProfile.php');
        $view = file_get_contents($this->root.'/resources/views/admin/hotspot/profiles.blade.php');

        $this->assertStringContainsString("'Mikrotik-Group' => \$this->mikrotik_group", $profile);
        $this->assertStringContainsString('MikroTik Group', $view);
        $this->assertStringContainsString('Sync semua', $view);
    }

    public function test_legacy_nik_password_is_accepted_only_for_gtk(): void
    {
        $model = file_get_contents($this->root.'/app/Models/HotspotUser.php');
        $command = file_get_contents($this->root.'/app/Console/Commands/HotspotSync.php');

        $this->assertStringContainsString("\$this->role === 'guru'", $model);
        $this->assertStringContainsString("preg_match('/^\\d{16}$/', \$this->username)", $model);
        $this->assertStringContainsString("\$role === 'guru'", $command);
        $this->assertStringContainsString("preg_match('/^\\d{16}$/', \$username)", $command);
        $this->assertStringContainsString('return $username;', $command);

        $guru = new HotspotUser(['username' => '1872041185770001', 'role' => 'guru']);
        $siswa = new HotspotUser(['username' => '1234567890', 'role' => 'siswa']);

        $this->assertTrue($guru->isSecurePassword('1872041185770001'));
        $this->assertFalse($siswa->isSecurePassword('1234567890'));
        $this->assertFalse($guru->isSecurePassword('short'));
    }

    public function test_monitoring_supports_live_traffic_disconnect_and_blocking(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');
        $view = file_get_contents($this->root.'/resources/views/admin/hotspot/online.blade.php');
        $service = file_get_contents($this->root.'/app/Services/RadiusDisconnectService.php');
        $migration = file_get_contents($this->root.'/database/migrations/2026_08_12_163000_add_block_metadata_to_hotspot_users.php');
        $bridge = file_get_contents($this->root.'/deploy/radius-disconnect-from-simansa.sh')
            .file_get_contents($this->root.'/deploy/radius-disconnect-on-radius.sh');
        $hardening = file_get_contents($this->root.'/deploy/harden-hotspot-disconnect.rsc');

        $this->assertStringContainsString("->name('sessions.disconnect')", $routes);
        $this->assertStringContainsString("->name('block')", $routes);
        $this->assertStringContainsString("->name('unblock')", $routes);
        $this->assertStringContainsString('disconnectRadiusSession', $controller);
        $this->assertStringContainsString("'blocked_users'", $controller);
        $this->assertStringContainsString("'bytes_download'", $controller);
        $this->assertStringContainsString('setInterval(loadOnline,5000)', $view);
        $this->assertStringContainsString('setInterval(tickDurations,1000)', $view);
        $this->assertStringContainsString('Blokir & putuskan', $view);
        $this->assertStringContainsString("'sudo'", $service);
        $this->assertStringContainsString('catch (\\Throwable $exception)', $service);
        $this->assertStringContainsString('Layanan pemutusan sesi belum tersedia', $service);
        $this->assertStringContainsString("'blocked_at'", $migration);
        $this->assertStringContainsString('Received Disconnect-ACK', $bridge);
        $this->assertStringContainsString('[[ "$nas_ip" == "172.16.250.1" ]]', $bridge);
        $this->assertStringContainsString("'hotspot_active'", $service);
        $this->assertStringContainsString("'dhcp_lease'", $service);
        $this->assertStringContainsString("'reauthentication_required' => true", $service);
        $this->assertStringContainsString('Sesi berhasil diputus', $view);
        $this->assertStringContainsString('login-by=http-chap', $hardening);
        $this->assertStringContainsString('add-mac-cookie=no', $hardening);
        $this->assertStringContainsString('/ip hotspot cookie remove [find]', $hardening);
        $this->assertStringNotContainsString('/ip dhcp-server lease remove', $hardening);
    }

    public function test_mikrotik_portal_prevents_double_submit_and_opens_status_tab(): void
    {
        $portal = $this->root.'/tools/mikrotik-hotspot/simansa-hotspot/';
        $login = file_get_contents($portal.'login.html');
        $success = file_get_contents($portal.'alogin.html');
        $script = file_get_contents($portal.'assets/portal.js');

        $this->assertStringContainsString('if (loginSubmitting) return false', $login);
        $this->assertStringContainsString('button.disabled = true', $login);
        $this->assertStringContainsString('$(if chap-id)', $login);
        $this->assertStringContainsString("document.forms['sendin']", $login);
        $this->assertStringNotContainsString("'$(chap-id)'.indexOf", $login);
        $this->assertStringContainsString("setAttribute('role', 'alertdialog')", $script);
        $this->assertStringContainsString('window.location.reload()', $script);
        $this->assertStringContainsString("window.open('$(link-status)', 'hotspot_status')", $success);
        $this->assertStringContainsString('target="hotspot_status"', $success);
    }
}
