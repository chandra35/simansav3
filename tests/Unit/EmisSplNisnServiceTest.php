<?php

namespace Tests\Unit;

use App\Services\EmisNisnService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmisSplNisnServiceTest extends TestCase
{
    public function test_it_returns_only_the_allowed_spl_fields_from_emis(): void
    {
        Http::fake([
            'api-emis.kemenag.go.id/*' => Http::response([
                'success' => true,
                'results' => [[
                    'nama' => 'GEDE ANUGERAH',
                    'nisn' => '0119577959',
                    'nik' => '1807141807110001',
                    'nama_ibu_kandung' => 'IKA AGUSTIN KUSUMAWATI',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => '2011-06-18T00:00:00Z',
                    'tanggal_keluar' => '2026-07-01T00:00:00Z',
                    'keterangan' => 'data dapat ditarik',
                    'is_disable' => 0,
                    'jenis_keluar_id' => '1',
                    'tingkat_pendidikan_id' => 6,
                    'peserta_didik_id' => '62CFE75A-D908-4B0A-8E58-B2C217FFB514',
                    'sekolah_id' => '005B2571-8B18-E111-8231-49E60FE78C1E',
                    'sekolah_id_reservasi' => '00000000-0000-0000-0000-000000000000',
                    'upstream_internal_token' => 'must-not-leak',
                ]],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $result = (new EmisNisnService('test-token'))->cekNisnSpl('0119577959');

        $this->assertTrue($result['success']);
        $this->assertSame('found', $result['code']);
        $this->assertSame('GEDE ANUGERAH', $result['data']['records'][0]['nama']);
        $this->assertSame('data dapat ditarik', $result['data']['records'][0]['keterangan']);
        $this->assertStringNotContainsString('must-not-leak', json_encode($result));
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api-emis.kemenag.go.id/v1/students/students/check-spl-student-data'
                && $request['type'] === 'nisn'
                && $request['number'] === '0119577959'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_it_rejects_invalid_nisn_before_calling_emis(): void
    {
        Http::fake();

        $result = (new EmisNisnService('test-token'))->cekNisnSpl('01195ABC');

        $this->assertFalse($result['success']);
        $this->assertSame('invalid_nisn', $result['code']);
        Http::assertNothingSent();
    }

    public function test_it_handles_expired_emis_token(): void
    {
        Http::fake([
            'api-emis.kemenag.go.id/*' => Http::response([], 401),
        ]);

        $result = (new EmisNisnService('test-token'))->cekNisnSpl('0119577959');

        $this->assertFalse($result['success']);
        $this->assertSame('token_expired', $result['code']);
    }
}
