<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassListAutomaticFilterTest extends TestCase
{
    public function test_all_class_filters_reload_the_table_automatically(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/index.blade.php');

        $this->assertStringContainsString(
            "$('#filter_tahun_pelajaran, #filter_tingkat, #filter_kurikulum, #filter_jurusan').on('change'",
            $view
        );
        $this->assertStringContainsString('table.ajax.reload();', $view);
        $this->assertStringNotContainsString('id="btn-filter"', $view);
        $this->assertStringNotContainsString("$('#btn-filter').on('click'", $view);
    }
}
