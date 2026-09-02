<?php

namespace Tests\Unit;

use App\Services\KemendikdasmenSchoolProfileService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KemendikdasmenSchoolProfileServiceTest extends TestCase
{
    public function test_it_normalizes_school_metadata_from_the_public_profile_api(): void
    {
        Http::fake([
            'sekolah.data.kemendikdasmen.go.id/*' => Http::response([
                'data' => [
                    'sekolah' => [[
                        'nama' => 'UPTD SMP NEGERI 1 BUMI AGUNG',
                        'npsn' => '10805975',
                        'alamat_jalan' => 'Srikaloko',
                        'nama_dusun' => 'SRIKALOKO',
                        'rt' => 8,
                        'rw' => 8,
                        'kecamatan' => 'Kec. Bumi Agung',
                        'kabupaten' => 'Kab. Lampung Timur',
                        'provinsi' => 'Prov. Lampung',
                        'kode_pos' => '34194',
                        'bentuk_pendidikan' => 'SMP',
                        'status_sekolah' => 'NEGERI',
                        'unneeded_field' => 'must-not-leak',
                    ]],
                ],
            ], 200),
        ]);

        $result = (new KemendikdasmenSchoolProfileService)->getProfile('C0A23075-8B18-E111-A307-513A8F6FF639');

        $this->assertTrue($result['success']);
        $this->assertSame('10805975', $result['data']['npsn']);
        $this->assertStringContainsString('Srikaloko', $result['data']['alamat']);
        $this->assertStringNotContainsString('must-not-leak', json_encode($result));
    }

    public function test_it_rejects_invalid_school_id_before_calling_the_reference_service(): void
    {
        Http::fake();

        $result = (new KemendikdasmenSchoolProfileService)->getProfile('not-a-school-id');

        $this->assertSame('invalid_school_id', $result['code']);
        Http::assertNothingSent();
    }
}
