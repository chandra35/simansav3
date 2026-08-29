<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OpenApiController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'SIMANSA LMS Integration API',
                'version' => '1.0.0',
                'description' => 'Read-only data contract for LMS MANSA synchronization.',
            ],
            'servers' => [['url' => url('/api/v1')]],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'Sanctum'],
                ],
            ],
            'paths' => [
                '/lms/students' => $this->collectionPath('Daftar siswa aktif untuk sinkronisasi LMS.'),
                '/lms/teachers' => $this->collectionPath('Daftar GTK aktif untuk sinkronisasi LMS.'),
            ],
        ]);
    }

    private function collectionPath(string $summary): array
    {
        return [
            'get' => [
                'summary' => $summary,
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    ['name' => 'per_page', 'in' => 'query', 'description' => 'Jumlah data per halaman (1-250, default 100).', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 250]],
                    ['name' => 'updated_since', 'in' => 'query', 'description' => 'Filter ISO-8601; hanya data yang berubah setelah waktu ini.', 'schema' => ['type' => 'string', 'format' => 'date-time']],
                ],
                'responses' => [
                    '200' => ['description' => 'Koleksi terhalaman.'],
                    '401' => ['description' => 'Bearer token tidak valid atau tidak tersedia.'],
                    '403' => ['description' => 'Token tidak memiliki kemampuan lms:read.'],
                    '422' => ['description' => 'Parameter permintaan tidak valid.'],
                ],
            ],
        ];
    }
}
