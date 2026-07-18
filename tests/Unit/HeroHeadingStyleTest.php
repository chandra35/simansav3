<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HeroHeadingStyleTest extends TestCase
{
    public function test_hero_heading_inherits_its_component_foreground_color(): void
    {
        $css = file_get_contents($this->projectPath('public/css/custom-compact.css'));

        $this->assertStringContainsString('.content-header [class*="-hero"] h1', $css);
        $this->assertStringContainsString('color: inherit !important;', $css);
    }

    public function test_custom_stylesheet_uses_a_cache_busting_version(): void
    {
        $config = file_get_contents($this->projectPath('config/adminlte.php'));
        $pluginView = file_get_contents($this->projectPath('resources/views/vendor/adminlte/plugins.blade.php'));

        $this->assertStringContainsString("'version' => true", $config);
        $this->assertStringContainsString("filemtime(public_path(\$location))", $pluginView);
    }

    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 2).'/'.$path;
    }
}
