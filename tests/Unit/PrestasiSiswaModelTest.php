<?php

namespace Tests\Unit;

use App\Models\PrestasiSiswa;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class PrestasiSiswaModelTest extends TestCase
{
    public function test_custom_rank_and_multiple_students_are_supported(): void
    {
        $prestasi = new PrestasiSiswa(['peringkat_nama' => 'Juara Umum']);

        $this->assertSame('Juara Umum', $prestasi->peringkat_label);
        $this->assertInstanceOf(BelongsToMany::class, $prestasi->peserta());
        $this->assertContains('tahun', $prestasi->getFillable());
        $this->assertContains('nama_siswa_manual', $prestasi->getFillable());
    }
}
