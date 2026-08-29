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
                'description' => 'Read-only data contract for LMS MANSA synchronization. Use an API token with the lms:read ability.',
            ],
            'servers' => [['url' => url('/api/v1')]],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'Sanctum'],
                ],
                'schemas' => [
                    'Student' => [
                        'type' => 'object',
                        'required' => ['id', 'nama_lengkap', 'updated_at'],
                        'properties' => [
                            'id' => ['type' => 'string', 'description' => 'ID internal SIMANSA.'],
                            'user_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'ID akun SIMANSA yang terhubung, jika tersedia.'],
                            'nisn' => ['type' => 'string', 'nullable' => true, 'description' => 'Nomor Induk Siswa Nasional.'],
                            'nama_lengkap' => ['type' => 'string', 'example' => 'Ahmad Pratama'],
                            'jenis_kelamin' => ['type' => 'string', 'nullable' => true, 'description' => 'Nilai sumber SIMANSA, umumnya L atau P.'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'Teacher' => [
                        'type' => 'object',
                        'required' => ['id', 'nama_lengkap', 'updated_at'],
                        'properties' => [
                            'id' => ['type' => 'string', 'format' => 'uuid', 'description' => 'UUID internal GTK.'],
                            'user_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'ID akun SIMANSA yang terhubung, jika tersedia.'],
                            'nama_lengkap' => ['type' => 'string', 'example' => 'Siti Rahmawati, S.Pd.'],
                            'nip' => ['type' => 'string', 'nullable' => true],
                            'nik' => ['type' => 'string', 'nullable' => true, 'description' => 'Dikirim hanya untuk integrasi internal yang berwenang.'],
                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true],
                            'jenis_ptk' => ['type' => 'string', 'nullable' => true],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'ValidationError' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/lms/students' => $this->collectionPath('Daftar siswa aktif untuk sinkronisasi LMS.', 'Student'),
                '/lms/teachers' => $this->collectionPath('Daftar GTK aktif untuk sinkronisasi LMS.', 'Teacher'),
            ],
        ]);
    }

    private function collectionPath(string $summary, string $itemSchema): array
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
                    '200' => [
                        'description' => 'Koleksi terhalaman.',
                        'content' => ['application/json' => ['schema' => $this->paginationSchema($itemSchema)]],
                    ],
                    '401' => ['description' => 'Bearer token tidak valid atau tidak tersedia.', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
                    '403' => ['description' => 'Token tidak memiliki kemampuan lms:read.', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
                    '422' => ['description' => 'Parameter permintaan tidak valid.', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']]]],
                ],
            ],
        ];
    }

    private function paginationSchema(string $itemSchema): array
    {
        return [
            'type' => 'object',
            'required' => ['current_page', 'data', 'per_page', 'total'],
            'properties' => [
                'current_page' => ['type' => 'integer', 'example' => 1],
                'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/'.$itemSchema]],
                'first_page_url' => ['type' => 'string', 'nullable' => true],
                'from' => ['type' => 'integer', 'nullable' => true],
                'last_page' => ['type' => 'integer', 'example' => 1],
                'last_page_url' => ['type' => 'string', 'nullable' => true],
                'links' => ['type' => 'array', 'items' => ['type' => 'object']],
                'next_page_url' => ['type' => 'string', 'nullable' => true],
                'path' => ['type' => 'string'],
                'per_page' => ['type' => 'integer', 'example' => 100],
                'prev_page_url' => ['type' => 'string', 'nullable' => true],
                'to' => ['type' => 'integer', 'nullable' => true],
                'total' => ['type' => 'integer', 'example' => 1340],
            ],
        ];
    }
}
