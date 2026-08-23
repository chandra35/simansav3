<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NginxNeutralErrorPageTest extends TestCase
{
    public function test_nginx_error_asset_uses_neutral_copy_without_route_enumeration(): void
    {
        $page = file_get_contents(dirname(__DIR__, 2).'/public/_system/layanan-tidak-tersedia.html');

        $this->assertStringContainsString('Permintaan belum dapat diproses.', $page);
        $this->assertStringContainsString('Ke beranda SIMANSA', $page);
        $this->assertStringContainsString('__CLIENT_IP__', $page);
        $this->assertStringNotContainsString('Halaman tidak ditemukan', $page);
        $this->assertStringNotContainsString('Not Found', $page);
    }

    public function test_nginx_error_asset_is_mobile_safe_and_standalone(): void
    {
        $page = file_get_contents(dirname(__DIR__, 2).'/public/_system/layanan-tidak-tersedia.html');

        $this->assertStringContainsString('width=device-width, initial-scale=1', $page);
        $this->assertStringContainsString('@media (max-width: 480px)', $page);
        $this->assertStringContainsString('navigator.geolocation', $page);
        $this->assertStringContainsString('grid-template-columns', $page);
        $this->assertStringNotContainsString('<script src=', $page);
    }
}
