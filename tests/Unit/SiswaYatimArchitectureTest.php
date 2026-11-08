<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiswaYatimArchitectureTest extends TestCase
{
    #[Test]
    public function sidebar_menu_places_siswa_yatim_directly_under_kip_sktm(): void
    {
        $adminlte = config('adminlte.menu');
        $this->assertIsArray($adminlte);

        $dataMenu = null;
        foreach ($adminlte as $item) {
            if (is_array($item) && ($item['text'] ?? '') === 'Manajemen Data') {
                $dataMenu = $item;
                break;
            }
        }

        $this->assertNotNull($dataMenu, 'Manajemen Data menu should exist');
        $submenu = $dataMenu['submenu'] ?? [];

        $kipIndex = null;
        $yatimIndex = null;

        foreach ($submenu as $idx => $sub) {
            if (is_array($sub)) {
                if (($sub['route'] ?? '') === 'admin.kip-sktm.index') {
                    $kipIndex = $idx;
                }
                if (($sub['route'] ?? '') === 'admin.siswa-yatim.index') {
                    $yatimIndex = $idx;
                }
            }
        }

        $this->assertNotNull($kipIndex, 'KIP/SKTM menu should exist');
        $this->assertNotNull($yatimIndex, 'Siswa Yatim menu should exist');
        $this->assertEquals($kipIndex + 1, $yatimIndex, 'Siswa Yatim menu must be placed directly under KIP/SKTM');
    }

    #[Test]
    public function siswa_yatim_routes_are_registered(): void
    {
        $this->assertTrue(\Route::has('admin.siswa-yatim.index'));
        $this->assertTrue(\Route::has('admin.siswa-yatim.data'));
        $this->assertTrue(\Route::has('admin.siswa-yatim.export'));
        $this->assertTrue(\Route::has('admin.siswa-yatim.detail'));
    }
}
