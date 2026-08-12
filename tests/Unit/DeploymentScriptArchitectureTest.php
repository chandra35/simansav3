<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DeploymentScriptArchitectureTest extends TestCase
{
    public function test_deploy_minimizes_maintenance_and_skips_unchanged_work(): void
    {
        $script = file_get_contents(dirname(__DIR__, 2).'/update-simansa.sh');

        $this->assertStringContainsString('run_timed "Git fetch"', $script);
        $this->assertLessThan(
            strpos($script, 'run_artisan down'),
            strpos($script, 'run_timed "Git fetch"')
        );
        $this->assertStringContainsString('COMPOSER_CHANGED=0', $script);
        $this->assertStringContainsString('MIGRATION_CHANGED=0', $script);
        $this->assertStringContainsString('RUNTIME_CHANGED=0', $script);
        $this->assertStringContainsString('run_artisan optimize:clear', $script);
        $this->assertStringContainsString('run_artisan optimize', $script);
        $this->assertStringNotContainsString('chown -R', $script);
        $this->assertStringNotContainsString('find storage bootstrap/cache', $script);
        $this->assertStringContainsString('flock -n 9', $script);
        $this->assertStringContainsString('Maintenance berlangsung', $script);
        $this->assertStringContainsString('SIMANSA_DEPLOY_SNAPSHOT', $script);
    }
}
