<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiDocumentationTest extends TestCase
{
    public function test_interactive_documentation_ui_is_available(): void
    {
        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('SIMANSA API Reference');
    }

    public function test_api_v1_landing_redirects_browsers_to_docs(): void
    {
        $this->get('/api/v1', ['Accept' => 'text/html,application/xhtml+xml'])
            ->assertRedirect('/docs/api');

        $this->get('/api', ['Accept' => 'text/html,application/xhtml+xml'])
            ->assertRedirect('/docs/api');
    }

    public function test_api_v1_landing_returns_json_discovery_for_api_clients(): void
    {
        $this->getJson('/api/v1')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('version', '1.0.0')
            ->assertJsonStructure([
                'success',
                'message',
                'version',
                'documentation',
                'openapi',
                'endpoints' => [
                    'auth',
                    'siswa',
                    'gtk',
                    'jadwal',
                    'presensi',
                    'keuangan',
                    'surat',
                    'lms',
                ],
            ]);
    }

    public function test_openapi_document_is_available(): void
    {
        $this->getJson('/api/v1/openapi.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.3')
            ->assertJsonPath('info.version', '1.0.0')
            ->assertJsonStructure([
                'components' => ['schemas' => ['Student', 'Teacher', 'LmsIdentity', 'ValidationError']],
                'paths' => [
                    '/lms/students',
                    '/lms/teachers',
                    '/lms/authenticate',
                ],
            ]);
    }
}
