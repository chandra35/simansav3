<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MapelMobileTableUiTest extends TestCase
{
    #[Test]
    public function mapel_table_uses_responsive_columns_without_mobile_horizontal_scroll(): void
    {
        $view = file_get_contents(resource_path('views/admin/mapel/index.blade.php'));

        $this->assertStringContainsString('class="mapel-table-shell"', $view);
        $this->assertStringNotContainsString('class="table-responsive mapel-table-shell"', $view);
        $this->assertStringContainsString('.mapel-table-shell{width:100%;max-width:100%;padding:0 8px 12px;overflow-x:hidden}', $view);
        $this->assertStringContainsString('table-layout:fixed!important', $view);
        $this->assertStringContainsString('responsivePriority: 1', $view);
        $this->assertStringContainsString('responsivePriority: 2', $view);
        $this->assertStringContainsString('responsivePriority: 3', $view);
        $this->assertStringContainsString('responsivePriority: 4', $view);
        $this->assertStringContainsString('justify-content:center;flex-wrap:wrap', $view);
    }
}
