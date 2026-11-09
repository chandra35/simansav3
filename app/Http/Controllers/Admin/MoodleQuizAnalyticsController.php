<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleIntegration;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MoodleQuizAnalyticsController extends Controller
{
    private function getMoodleEndpoint(): string
    {
        $integration = MoodleIntegration::current();
        return rtrim($integration->base_url, '/') . '/converter/smartq/index.php';
    }

    private function fetchFromMoodle(array $params)
    {
        $url = $this->getMoodleEndpoint();
        $response = Http::timeout(45)->withoutVerifying()->get($url, $params);

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal menghubungi endpoint Moodle: HTTP ' . $response->status());
        }

        $json = $response->json();
        if (!$json || empty($json['success'])) {
            throw new \RuntimeException($json['error'] ?? 'Respon Moodle tidak valid.');
        }

        return $json;
    }

    public function index()
    {
        $integration = MoodleIntegration::current();
        return view('admin.moodle-sync.quizzes', compact('integration'));
    }

    public function getCategories()
    {
        try {
            $data = $this->fetchFromMoodle(['action' => 'categories']);
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getCourses(Request $request)
    {
        $categoryId = (int)$request->query('category', 0);
        if (!$categoryId) {
            return response()->json(['success' => false, 'error' => 'Parameter category diperlukan'], 422);
        }

        try {
            $data = $this->fetchFromMoodle([
                'action' => 'courses',
                'category' => $categoryId,
            ]);
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getQuizzes(Request $request)
    {
        $courseId = (int)$request->query('course', 0);
        if (!$courseId) {
            return response()->json(['success' => false, 'error' => 'Parameter course diperlukan'], 422);
        }

        try {
            $data = $this->fetchFromMoodle([
                'action' => 'quizzes',
                'course' => $courseId,
            ]);
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getScores(Request $request)
    {
        $quizId = (int)$request->query('quiz', 0);
        if (!$quizId) {
            return response()->json(['success' => false, 'error' => 'Parameter quiz diperlukan'], 422);
        }

        try {
            $res = $this->fetchFromMoodle([
                'action' => 'scores',
                'quiz' => $quizId,
            ]);

            $scores = $res['data'] ?? [];
            if (!empty($scores)) {
                $nisns = collect($scores)->pluck('username')->filter()->unique()->values()->all();

                $siswaMap = Siswa::whereIn('nisn', $nisns)
                    ->with(['kelasTahunAktif'])
                    ->get()
                    ->keyBy('nisn');

                foreach ($scores as &$row) {
                    $nisn = $row['username'] ?? '';
                    $siswa = $siswaMap->get($nisn);

                    if ($siswa) {
                        $rombel = $siswa->kelasTahunAktif->first()?->nama_kelas ?? '-';
                        $row['simansa'] = [
                            'found' => true,
                            'id' => $siswa->id,
                            'nama' => $siswa->nama_lengkap,
                            'rombel' => $rombel,
                        ];
                    } else {
                        $row['simansa'] = [
                            'found' => false,
                            'id' => null,
                            'nama' => null,
                            'rombel' => '-',
                        ];
                    }
                }
                unset($row);
                $res['data'] = $scores;
            }

            return response()->json($res);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getAnalysis(Request $request)
    {
        $quizId = (int)$request->query('quiz', 0);
        $refresh = $request->boolean('refresh');

        if (!$quizId) {
            return response()->json(['success' => false, 'error' => 'Parameter quiz diperlukan'], 422);
        }

        try {
            $params = [
                'action' => 'statistics',
                'quiz' => $quizId,
            ];
            if ($refresh) {
                $params['refresh'] = 1;
            }

            $data = $this->fetchFromMoodle($params);
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
