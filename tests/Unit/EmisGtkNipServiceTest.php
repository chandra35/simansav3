<?php

namespace Tests\Unit;

use App\Services\EmisGtkNipService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmisGtkNipServiceTest extends TestCase
{
    public function test_it_normalizes_cookie_and_discards_analytics_cookies(): void
    {
        $normalized = EmisGtkNipService::normalizeCookieHeader(
            'Cookie: _ga=tracking; cookiesession1=session-value; csrftoken=csrf-value; emisSSO=sso-value; other=ignored'
        );

        $this->assertSame('cookiesession1=session-value; csrftoken=csrf-value; emisSSO=sso-value', $normalized);
        $this->assertStringNotContainsString('_ga', $normalized);
    }

    public function test_it_maps_a_successful_simpeg_response_without_returning_raw_payload(): void
    {
        Http::fake([
            'emisgtk.kemenag.go.id/*' => Http::response([
                'nip' => '198909092025211087',
                'validation' => [
                    'is_valid' => true,
                    'name_match' => true,
                    'name_similarity' => 87.2,
                    'birth_date_match' => true,
                    'can_continue_with_confirmation' => true,
                    'nama_ptk' => 'CANDRA HUDA BUANA',
                    'nama_simpeg' => 'CANDRA HUDA BUANA, A.Md',
                    'tgl_lahir_ptk' => '09-09-1989',
                    'tgl_lahir_simpeg' => '09-09-1989',
                ],
                'simpeg_data' => [
                    'status_pegawai' => 'PPPK',
                    'golongan' => 'VII',
                    'gaji_pokok' => 2858800,
                    'unit_kerja' => 'MAN 1 Metro',
                ],
                'unneeded_sensitive_field' => 'must-not-leak',
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $result = (new EmisGtkNipService('emisSSO=sso; cookiesession1=session'))->check('198909092025211087');

        $this->assertTrue($result['success']);
        $this->assertSame('PPPK', $result['data']['simpeg']['status_pegawai']);
        $this->assertSame(87.2, $result['data']['validation']['name_similarity']);
        $this->assertArrayNotHasKey('raw_data', $result);
        $this->assertStringNotContainsString('must-not-leak', json_encode($result));
    }

    public function test_it_reports_an_expired_session_when_emis_returns_html(): void
    {
        Http::fake([
            'emisgtk.kemenag.go.id/*' => Http::response('<html>Login</html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $result = (new EmisGtkNipService('emisSSO=sso; cookiesession1=session'))->check('198909092025211087');

        $this->assertFalse($result['success']);
        $this->assertSame('session_expired', $result['code']);
    }

    public function test_it_rejects_a_non_numeric_nip_before_calling_emis(): void
    {
        Http::fake();

        $result = (new EmisGtkNipService('emisSSO=sso; cookiesession1=session'))->check('1989ABC');

        $this->assertSame('invalid_nip', $result['code']);
        Http::assertNothingSent();
    }
}
