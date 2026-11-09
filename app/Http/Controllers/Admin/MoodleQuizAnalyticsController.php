<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\MoodleIntegration;
use App\Models\MoodleQuizArchive;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\MoodleQuizArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isPureGtk = $user->hasRole('GTK') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
        $gtk = $user->gtk;
        $teacherNik = $isPureGtk ? $gtk?->nik : null;

        $activeYear = TahunPelajaran::where('is_active', true)->first();
        $semester = $activeYear?->semester_aktif === 'Genap' ? 2 : 1;
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();

        $kelasList = Kelas::where('tahun_pelajaran_id', $activeYear?->id)
            ->orderBy('nama_kelas')
            ->get();

        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();

        // GTK-assigned classes & mapel if GTK
        $gtkKelasList = collect();
        $gtkMapelList = collect();
        if ($gtk) {
            $jadwals = \App\Models\JadwalPelajaran::where('gtk_id', $gtk->id)
                ->where('tahun_pelajaran_id', $activeYear?->id)
                ->where('semester', $semester)
                ->with(['kelas', 'mataPelajaran'])
                ->get();

            $gtkKelasList = $jadwals->pluck('kelas')->filter()->unique('id')->values();
            $gtkMapelList = $jadwals->pluck('mataPelajaran')->filter()->unique('id')->values();
        }

        $mapels = ($isPureGtk && $gtkMapelList->isNotEmpty()) ? $gtkMapelList : $mapelList;
        $rombels = ($isPureGtk && $gtkKelasList->isNotEmpty()) ? $gtkKelasList : $kelasList;

        return view('admin.moodle-sync.quizzes', compact(
            'integration',
            'user',
            'isPureGtk',
            'gtk',
            'teacherNik',
            'activeYear',
            'semester',
            'tahunPelajaranList',
            'kelasList',
            'mapelList',
            'mapels',
            'rombels',
            'gtkKelasList',
            'gtkMapelList'
        ));
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

        $params = [
            'action' => 'courses',
            'category' => $categoryId,
            'include_hidden' => 1,
        ];

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isPureGtk = $user->hasRole('GTK') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
        if ($isPureGtk && $user->gtk?->nik) {
            $params['teacher_nik'] = $user->gtk->nik;
        } elseif ($request->filled('teacher_nik')) {
            $params['teacher_nik'] = $request->query('teacher_nik');
        }

        try {
            $data = $this->fetchFromMoodle($params);
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

            // Check if there is an existing archive in SIMANSA
            $existingArchive = MoodleQuizArchive::where('moodle_quiz_id', $quizId)
                ->with(['gtk', 'lockedByUser', 'kelas', 'mataPelajaran'])
                ->latest()
                ->first();

            $res['archive'] = $existingArchive ? [
                'id' => $existingArchive->id,
                'is_locked' => $existingArchive->is_locked,
                'locked_at' => $existingArchive->locked_at?->format('d/m/Y H:i'),
                'locked_by' => $existingArchive->lockedByUser?->name,
                'gtk_name' => $existingArchive->gtk?->nama_lengkap,
                'kategori_penilaian' => $existingArchive->kategori_penilaian,
                'nama_penilaian' => $existingArchive->nama_penilaian,
                'kelas_id' => $existingArchive->kelas_id,
                'mata_pelajaran_id' => $existingArchive->mata_pelajaran_id,
                'saved_at' => $existingArchive->updated_at->format('d/m/Y H:i'),
            ] : null;

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

    /**
     * Simpan snapshot kuis Moodle ke Arsip Lokal SIMANSA.
     */
    public function saveArchive(Request $request, MoodleQuizArchiveService $service)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer',
            'quiz_name' => 'required|string',
            'course_id' => 'required|integer',
            'course_name' => 'required|string',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
            'tahun_pelajaran_id' => 'nullable|uuid',
            'semester' => 'nullable|integer',
            'mata_pelajaran_id' => 'nullable|uuid',
            'kelas_id' => 'nullable|uuid',
            'gtk_id' => 'nullable|uuid',
            'kategori_penilaian' => 'required|in:harian,tengah_semester,akhir_semester,lainnya',
            'nama_penilaian' => 'nullable|string|max:150',
            'statistics' => 'nullable|array',
            'items_analysis' => 'nullable|array',
            'scores' => 'nullable|array',
            'catatan' => 'nullable|string',
        ]);

        try {
            $archive = $service->saveArchive($validated, Auth::user());
            return response()->json([
                'success' => true,
                'message' => 'Analisis dan skor kuis berhasil diarsipkan ke SIMANSA.',
                'archive' => [
                    'id' => $archive->id,
                    'is_locked' => (bool)$archive->is_locked,
                    'saved_at' => $archive->updated_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
                ],
                'data' => $archive,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Kunci arsip kuis.
     */
    public function lockArchive(Request $request, $id, MoodleQuizArchiveService $service)
    {
        $archive = MoodleQuizArchive::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validasi kepemilikan / role
        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $archive->user_id !== $user->id && $archive->gtk_id !== $user->gtk?->id) {
            return response()->json(['success' => false, 'error' => 'Anda tidak memiliki hak untuk mengunci arsip ini.'], 403);
        }

        try {
            $service->lockArchive($archive, $user);
            return response()->json([
                'success' => true,
                'message' => 'Arsip kuis berhasil dikunci secara permanen.',
                'locked_at' => $archive->locked_at?->format('d/m/Y H:i'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Buka kunci arsip kuis.
     */
    public function unlockArchive(Request $request, $id, MoodleQuizArchiveService $service)
    {
        $archive = MoodleQuizArchive::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $archive->user_id !== $user->id && $archive->gtk_id !== $user->gtk?->id) {
            return response()->json(['success' => false, 'error' => 'Anda tidak memiliki hak untuk membuka kunci arsip ini.'], 403);
        }

        try {
            $service->unlockArchive($archive, $user);
            return response()->json([
                'success' => true,
                'message' => 'Kunci arsip berhasil dibuka.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sinkronkan skor kuis ke Buku Nilai Siswa (penilaian_siswa).
     */
    public function syncToPenilaian(Request $request, MoodleQuizArchiveService $service)
    {
        $archiveId = $request->input('archive_id') ?: $request->route('id');
        if (!$archiveId) {
            return response()->json(['success' => false, 'error' => 'ID Arsip kuis tidak ditemukan. Silakan simpan arsip terlebih dahulu.'], 422);
        }

        $archive = MoodleQuizArchive::findOrFail($archiveId);

        $validated = $request->validate([
            'kelas_id' => 'nullable|uuid',
            'mata_pelajaran_id' => 'required|uuid',
            'kategori' => 'required|in:harian,tengah_semester,akhir_semester,tugas,proyek',
            'kode_penilaian' => 'nullable|string|max:50',
            'nama_penilaian' => 'required|string|max:100',
        ]);

        try {
            $result = $service->syncToPenilaian($archive, $validated, Auth::user());
            return response()->json([
                'success' => true,
                'message' => "Berhasil menyinkronkan {$result['synced']} nilai ke Buku Nilai Siswa." . ($result['skipped'] > 0 ? " ({$result['skipped']} siswa non-anggota dilewati)." : ''),
                'synced_count' => $result['synced'],
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Unduh / Tampilkan Laporan PDF Analisis Kuis.
     */
    public function exportPdf(Request $request, $id, MoodleQuizArchiveService $service)
    {
        $archive = MoodleQuizArchive::with(['gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'scores'])->findOrFail($id);
        $pdf = $service->generatePdf($archive);
        $filename = 'Laporan-Analisis-' . \Illuminate\Support\Str::slug($archive->moodle_quiz_name) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Daftar Arsip Kuis yang tersimpan di SIMANSA.
     */
    public function archivesList(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isPureGtk = $user->hasRole('GTK') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);

        $query = MoodleQuizArchive::with(['gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'lockedByUser'])
            ->withCount('scores');

        if ($isPureGtk && $user->gtk) {
            $query->where('gtk_id', $user->gtk->id);
        }

        if ($request->filled('quiz_id')) {
            $query->where('moodle_quiz_id', (int)$request->query('quiz_id'));
            $archive = $query->latest()->first();
            return response()->json([
                'success' => true,
                'archive' => $archive,
                'data' => $archive ? [$archive] : [],
            ]);
        }

        if ($request->filled('tahun_pelajaran_id')) {
            $query->where('tahun_pelajaran_id', $request->query('tahun_pelajaran_id'));
        }

        if ($request->filled('semester')) {
            $query->where('semester', (int)$request->query('semester'));
        }

        $archives = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $archives,
        ]);
    }

    /**
     * Ambil detail arsip dari SIMANSA DB (offline Moodle safe).
     */
    public function archiveDetail(Request $request, $id)
    {
        $archive = MoodleQuizArchive::with(['gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'scores.siswa', 'lockedByUser'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'archive' => $archive,
        ]);
    }
}
