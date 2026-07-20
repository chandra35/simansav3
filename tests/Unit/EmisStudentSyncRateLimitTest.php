<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmisStudentSyncRateLimitTest extends TestCase
{
    public function test_student_sync_uses_dedicated_per_student_limiter(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/AppServiceProvider.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString("RateLimiter::for('emis-student-sync'", $provider);
        $this->assertStringContainsString('emis-student:{$userKey}:{$studentKey}', $provider);
        $this->assertStringContainsString('throttle:emis-student-sync', $routes);
        $this->assertStringNotContainsString("'throttle:10,1'", $this->studentSyncRouteBlock($routes));
    }

    public function test_emis_api_rate_limit_has_a_specific_message(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/EmisStudentSyncService.php');

        $this->assertStringContainsString('if ($response->status() === 429)', $service);
        $this->assertStringContainsString('API EMIS sedang membatasi permintaan', $service);
        $this->assertStringContainsString('snapshot lama tetap aman', $service);
    }

    private function studentSyncRouteBlock(string $routes): string
    {
        $start = strpos($routes, "Route::post('/siswa/{siswa}/sync'");

        return substr($routes, $start, 300);
    }
}
