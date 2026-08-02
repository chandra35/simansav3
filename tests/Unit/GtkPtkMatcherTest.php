<?php

namespace Tests\Unit;

use App\Services\GtkPtkMatcher;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class GtkPtkMatcherTest extends TestCase
{
    public function test_it_ignores_titles_and_matches_abbreviated_name_with_birth_date(): void
    {
        $matcher = new GtkPtkMatcher;
        $gtks = new Collection([
            (object) ['id' => '1', 'nama_lengkap' => 'Drs. MUHAMMAD ALI, M.Pd.', 'nik' => null, 'nip' => null, 'nuptk' => null, 'tanggal_lahir' => '1980-01-02'],
            (object) ['id' => '2', 'nama_lengkap' => 'MUHAMMAD ARI', 'nik' => null, 'nip' => null, 'nuptk' => null, 'tanggal_lahir' => '1981-01-02'],
        ]);

        $result = $matcher->match(['nama' => 'MUHAMMAD ALI', 'tanggal_lahir' => '1980-01-02'], $gtks);

        $this->assertSame('matched', $result['status']);
        $this->assertSame('1', $result['gtk']->id);
        $this->assertSame(100.0, $result['score']);
    }

    public function test_official_identity_wins_when_name_is_compatible(): void
    {
        $matcher = new GtkPtkMatcher;
        $gtks = new Collection([
            (object) ['id' => '1', 'nama_lengkap' => 'OKI SAHRONI, S.Si', 'nik' => '1807051710890002', 'nip' => null, 'nuptk' => null, 'tanggal_lahir' => '1989-10-17'],
        ]);

        $result = $matcher->match(['nama' => 'OKI SAHRONI', 'nik' => '1807051710890002', 'tanggal_lahir' => '1989-10-17'], $gtks);

        $this->assertSame('matched', $result['status']);
        $this->assertSame('NIK', $result['method']);
    }

    public function test_conflicting_official_identifiers_are_never_auto_matched(): void
    {
        $matcher = new GtkPtkMatcher;
        $gtks = new Collection([
            (object) ['id' => '1', 'nama_lengkap' => 'SITI AMINAH', 'nik' => '111', 'nip' => '900', 'nuptk' => null, 'tanggal_lahir' => null],
            (object) ['id' => '2', 'nama_lengkap' => 'SITI AMINAH', 'nik' => '222', 'nip' => '800', 'nuptk' => null, 'tanggal_lahir' => null],
        ]);

        $result = $matcher->match(['nama' => 'SITI AMINAH', 'nik' => '111', 'nip' => '800'], $gtks);

        $this->assertSame('ambiguous', $result['status']);
        $this->assertSame('identity_conflict', $result['method']);
    }
}
