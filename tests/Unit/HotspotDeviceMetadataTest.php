<?php

namespace Tests\Unit;

use App\Models\HotspotDeviceReport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HotspotDeviceMetadataTest extends TestCase
{
    #[Test]
    public function device_label_prefers_marketing_name_then_vendor_and_model(): void
    {
        $known = new HotspotDeviceReport([
            'vendor' => 'Samsung',
            'model' => 'SM-S921B',
            'marketing_name' => 'Samsung Galaxy S24',
            'device_type' => 'mobile',
        ]);
        $raw = new HotspotDeviceReport([
            'vendor' => 'OPPO',
            'model' => 'CPH2669',
            'device_type' => 'mobile',
        ]);

        $this->assertSame('Samsung Galaxy S24', $known->display_label);
        $this->assertSame('OPPO CPH2669', $raw->display_label);
    }

    #[Test]
    public function portal_report_is_bound_to_an_active_radius_session_and_rendered_in_monitoring(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/HotspotDeviceReportController.php');
        $monitoring = file_get_contents($root.'/resources/views/admin/hotspot/online.blade.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $bootstrap = file_get_contents($root.'/bootstrap/app.php');
        $portal = file_get_contents($root.'/tools/mikrotik-hotspot/simansa-hotspot/assets/device-report.js');

        $this->assertStringContainsString('hasMatchingActiveSession', $controller);
        $this->assertStringContainsString("whereNull('acctstoptime')", $controller);
        $this->assertStringContainsString('sessionMac === $mac && $ipMatches', $controller);
        $this->assertStringContainsString("str_starts_with(\$normalized, 'SM-S921')", $controller);
        $this->assertStringContainsString("Route::post('/api/hotspot/device-report'", $routes);
        $this->assertStringContainsString("'api/hotspot/device-report'", $bootstrap);
        $this->assertStringContainsString('getHighEntropyValues', $portal);
        $this->assertStringContainsString("payload.set('model'", $portal);
        $this->assertStringContainsString('s.device?.label', $monitoring);
        $this->assertStringContainsString('Model / Vendor', $monitoring);
        $this->assertStringContainsString('Sistem / Browser', $monitoring);
    }
}
