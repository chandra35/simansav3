<?php

namespace Tests\Unit;

use App\Models\JadwalJamConfig;
use PHPUnit\Framework\TestCase;

class JadwalJamConfigTest extends TestCase
{
    public function test_generator_keeps_every_configured_non_lesson_break_in_timeline(): void
    {
        $rows = JadwalJamConfig::generateRows('07:00', 45, [
            ['setelah_jam' => 2, 'durasi' => 10, 'label' => 'Istirahat Pagi'],
            ['setelah_jam' => 4, 'durasi' => 30, 'label' => 'Salat dan Makan'],
            ['setelah_jam' => 5, 'durasi' => 15, 'label' => 'Jeda Sore'],
        ], '12:30');

        $breaks = array_values(array_filter($rows, fn (array $row) => $row['is_istirahat']));

        $this->assertCount(3, $breaks);
        $this->assertSame([null, null, null], array_column($breaks, 'jam_ke'));
        $this->assertSame(['Istirahat Pagi', 'Salat dan Makan', 'Jeda Sore'], array_column($breaks, 'label'));
    }
}
