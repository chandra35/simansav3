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
