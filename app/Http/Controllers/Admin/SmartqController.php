<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartqPeriode;
use App\Models\SmartqKomponenNilai;
use App\Models\SmartqPeserta;
use App\Models\SmartqNilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ortu;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SmartqKelulusanTemplateExport;
use App\Exports\NilaiCbtExport;
use Yajra\DataTables\Facades\DataTables;

class SmartqController extends Controller
{
    // ==================== PERIODE ====================

    public function index()
    {
        $periodes = SmartqPeriode::with(['tahunPelajaran', 'pesertas', 'komponenNilais'])
            ->withCount('pesertas', 'pesertaLulus')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.smartq.index', compact('periodes'));
    }

    public function create()
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        return view('admin.smartq.create', compact('tahunPelajarans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kuota' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'deskripsi' => 'nullable|string',
            'moodle_base_url' => 'nullable|url',
        ]);

        $periode = SmartqPeriode::create($request->only([
            'nama', 'tahun_pelajaran_id', 'kuota', 'tanggal_mulai',
            'tanggal_selesai', 'deskripsi', 'moodle_base_url',
        ]));

        // Create default komponen nilai
        $defaults = [
            ['nama' => 'Tes CBT (Moodle)', 'kode' => 'cbt', 'bobot' => 40, 'sumber' => 'moodle', 'urutan' => 1],
            ['nama' => 'Tahfidz Al-Quran', 'kode' => 'tahfidz', 'bobot' => 25, 'sumber' => 'manual', 'urutan' => 2],
            ['nama' => 'Psikotes', 'kode' => 'psikotes', 'bobot' => 20, 'sumber' => 'manual', 'urutan' => 3],
            ['nama' => 'Wawancara', 'kode' => 'wawancara', 'bobot' => 15, 'sumber' => 'manual', 'urutan' => 4],
        ];

        foreach ($defaults as $d) {
            $periode->komponenNilais()->create($d + ['nilai_maksimal' => 100]);
        }

        return redirect()->route('admin.smartq.show', $periode)
            ->with('success', 'Periode SMART-Q berhasil dibuat dengan komponen nilai default.');
    }

    public function show(SmartqPeriode $smartq)
    {
        $smartq->load(['tahunPelajaran', 'komponenNilais']);

        $pesertas = SmartqPeserta::where('smartq_periode_id', $smartq->id)->get();

        $stats = [
            'total' => $pesertas->count(),
            'lulus' => $pesertas->where('status', 'lulus')->count(),
            'cadangan' => $pesertas->where('status', 'cadangan')->count(),
            'tidak_lulus' => $pesertas->where('status', 'tidak_lulus')->count(),
            'terdaftar' => $pesertas->where('status', 'terdaftar')->count(),
            'rata_rata' => $pesertas->avg('total_nilai') ?? 0,
            'tertinggi' => $pesertas->max('total_nilai') ?? 0,
            'terendah' => $pesertas->where('total_nilai', '>', 0)->min('total_nilai') ?? 0,
        ];

        // Check if scan data exists (persisted in DB) — avoid loading the huge JSON blob
        $hasScanData = $smartq->last_scan_at !== null;

        return view('admin.smartq.show', compact('smartq', 'stats', 'hasScanData'));
    }

    public function nilaiCbt(SmartqPeriode $smartq)
    {
        if (empty($smartq->last_scan_data)) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('warning', 'Belum ada data scan Moodle. Lakukan Scan Peserta dari Moodle terlebih dahulu.');
        }

        $rows = $smartq->last_scan_data['rows'] ?? [];
        $summary = $smartq->last_scan_data['summary'] ?? [];

        return view('admin.smartq.nilai-cbt', compact('smartq', 'rows', 'summary'));
    }

    public function viewScanCache(SmartqPeriode $smartq)
    {
        $cacheKey = 'smartq_scan_' . $smartq->id . '_' . Auth::id();
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('warning', 'Data scan sudah expired. Silakan scan ulang dari Moodle.');
        }

        $rows = $cached['rows'];
        $summary = $cached['summary'];

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $kelasAvailable = $tahunAktif
            ? Kelas::where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('is_active', true)
                ->with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        return view('admin.smartq.preview-moodle', compact('smartq', 'rows', 'summary', 'cacheKey', 'kelasAvailable'));
    }

    public function edit(SmartqPeriode $smartq)
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $smartq->load('komponenNilais');
        return view('admin.smartq.edit', compact('smartq', 'tahunPelajarans'));
    }

    public function update(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kuota' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:pendaftaran,seleksi,pengumuman,selesai',
            'deskripsi' => 'nullable|string',
            'moodle_base_url' => 'nullable|url',
        ]);

        $smartq->update($request->only([
            'nama', 'tahun_pelajaran_id', 'kuota', 'tanggal_mulai',
            'tanggal_selesai', 'status', 'deskripsi', 'moodle_base_url',
        ]));

        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', 'Periode SMART-Q berhasil diupdate.');
    }

    // ==================== KOMPONEN NILAI ====================

    public function updateKomponen(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'komponen' => 'required|array|min:1',
            'komponen.*.nama' => 'required|string|max:255',
            'komponen.*.kode' => 'required|string|max:50',
            'komponen.*.bobot' => 'required|numeric|min:0|max:100',
            'komponen.*.nilai_maksimal' => 'required|numeric|min:1',
            'komponen.*.sumber' => 'required|in:manual,moodle',
        ]);

        DB::transaction(function () use ($request, $smartq) {
            // Delete removed komponen
            $keepIds = collect($request->komponen)->pluck('id')->filter();
            $smartq->komponenNilais()->whereNotIn('id', $keepIds)->delete();

            foreach ($request->komponen as $i => $k) {
                if (!empty($k['id'])) {
                    SmartqKomponenNilai::where('id', $k['id'])->update([
                        'nama' => $k['nama'],
                        'kode' => $k['kode'],
                        'bobot' => $k['bobot'],
                        'nilai_maksimal' => $k['nilai_maksimal'],
                        'sumber' => $k['sumber'],
                        'urutan' => $i + 1,
                    ]);
                } else {
                    $smartq->komponenNilais()->create([
                        'nama' => $k['nama'],
                        'kode' => $k['kode'],
                        'bobot' => $k['bobot'],
                        'nilai_maksimal' => $k['nilai_maksimal'],
                        'sumber' => $k['sumber'],
                        'urutan' => $i + 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', 'Komponen nilai berhasil diupdate.');
    }

    // ==================== PESERTA ====================

    public function peserta(SmartqPeriode $smartq)
    {
        $smartq->load('komponenNilais');

        // Get siswa kelas 10 & 11 yang belum terdaftar di periode ini
        $kelasAktif = Kelas::with('jurusan')
            ->whereIn('tingkat', [10, 11])
            ->where('is_active', true)
            ->get();

        $siswaIds = $smartq->pesertas()->pluck('siswa_id');

        $siswaAvailable = Siswa::whereHas('kelasAktif', function ($q) {
                $q->whereIn('kelas.tingkat', [10, 11]);
            })
            ->where('status_siswa', 'aktif')
            ->whereNotIn('id', $siswaIds)
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.smartq.peserta', compact('smartq', 'kelasAktif', 'siswaAvailable'));
    }

    public function tambahPeserta(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        $added = 0;
        $lastNumber = $smartq->pesertas()->max(DB::raw("CAST(REPLACE(nomor_peserta, 'SQ-', '') AS UNSIGNED)")) ?? 0;

        DB::transaction(function () use ($request, $smartq, &$added, &$lastNumber) {
            foreach ($request->siswa_ids as $siswaId) {
                $exists = $smartq->pesertas()->where('siswa_id', $siswaId)->exists();
                if ($exists) continue;

                $siswa = Siswa::find($siswaId);
                $kelasAktif = $siswa?->getKelasSekarang();
                $lastNumber++;

                $peserta = SmartqPeserta::create([
                    'smartq_periode_id' => $smartq->id,
                    'siswa_id' => $siswaId,
                    'nomor_peserta' => 'SQ-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT),
                    'kelas_asal_id' => $kelasAktif?->id,
                ]);

                // Create empty nilai for each komponen
                foreach ($smartq->komponenNilais as $komponen) {
                    SmartqNilai::create([
                        'smartq_peserta_id' => $peserta->id,
                        'smartq_komponen_nilai_id' => $komponen->id,
                    ]);
                }

                $added++;
            }
        });

        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', "$added peserta berhasil ditambahkan.");
    }

    public function hapusPeserta(SmartqPeriode $smartq, SmartqPeserta $peserta)
    {
        $peserta->nilais()->delete();
        $peserta->forceDelete();

        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', 'Peserta berhasil dihapus.');
    }

    // ==================== NILAI ====================

    public function inputNilai(SmartqPeriode $smartq)
    {
        $smartq->load('komponenNilais');

        $pesertas = SmartqPeserta::with(['siswa', 'kelasAsal', 'nilais'])
            ->where('smartq_periode_id', $smartq->id)
            ->whereHas('siswa')
            ->orderBy('nomor_peserta')
            ->get();

        return view('admin.smartq.nilai', compact('smartq', 'pesertas'));
    }

    public function simpanNilai(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'nilai' => 'required|array',
        ]);

        DB::transaction(function () use ($request, $smartq) {
            foreach ($request->nilai as $pesertaId => $komponens) {
                foreach ($komponens as $komponenId => $data) {
                    $nilaiRaw = $data['nilai'] ?? null;
                    if ($nilaiRaw === null || $nilaiRaw === '') continue;

                    $komponen = SmartqKomponenNilai::find($komponenId);
                    if (!$komponen) continue;

                    $nilaiRaw = (float) $nilaiRaw;
                    $nilaiKonversi = $komponen->nilai_maksimal > 0
                        ? round(($nilaiRaw / $komponen->nilai_maksimal) * 100, 2)
                        : 0;

                    SmartqNilai::updateOrCreate(
                        [
                            'smartq_peserta_id' => $pesertaId,
                            'smartq_komponen_nilai_id' => $komponenId,
                        ],
                        [
                            'nilai' => $nilaiRaw,
                            'nilai_konversi' => $nilaiKonversi,
                            'catatan' => $data['catatan'] ?? null,
                            'dinilai_oleh' => Auth::id(),
                            'dinilai_pada' => now(),
                        ]
                    );
                }
            }

            // Recalculate ranking
            $smartq->hitungRanking();
        });

        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', 'Nilai berhasil disimpan dan ranking diupdate.');
    }

    // ==================== MOODLE SYNC ====================

    public function moodleConfig(SmartqPeriode $smartq)
    {
        return view('admin.smartq.moodle-config', compact('smartq'));
    }

    public function moodleCategories(Request $request, SmartqPeriode $smartq)
    {
        $baseUrl = $smartq->moodle_base_url ?? $request->input('moodle_base_url');
        if (!$baseUrl) {
            return response()->json(['success' => false, 'error' => 'URL Moodle belum dikonfigurasi']);
        }

        try {
            $response = Http::timeout(15)
                ->get(rtrim($baseUrl, '/') . '/converter/smartq/?action=categories');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['success' => false, 'error' => 'Gagal mengakses Moodle API: ' . $response->status()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }

    public function moodleCourses(Request $request, SmartqPeriode $smartq)
    {
        $baseUrl = $smartq->moodle_base_url ?? $request->input('moodle_base_url');
        $categoryId = $request->input('category_id');

        if (!$baseUrl) {
            return response()->json(['success' => false, 'error' => 'URL Moodle belum dikonfigurasi']);
        }

        try {
            if ($categoryId) {
                // Courses by category
                $response = Http::timeout(15)
                    ->get(rtrim($baseUrl, '/') . "/converter/smartq/?action=courses&category={$categoryId}");
            } else {
                // All courses with quizzes (fallback)
                $response = Http::timeout(15)
                    ->get(rtrim($baseUrl, '/') . '/converter/smartq/?action=quizzes');
            }

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['success' => false, 'error' => 'Gagal mengakses Moodle API: ' . $response->status()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }

    public function moodleQuizzes(Request $request, SmartqPeriode $smartq)
    {
        $baseUrl = $smartq->moodle_base_url;
        $courseId = $request->input('course_id');

        if (!$baseUrl || !$courseId) {
            return response()->json(['success' => false, 'error' => 'Parameter tidak lengkap']);
        }

        try {
            $response = Http::timeout(15)
                ->get(rtrim($baseUrl, '/') . "/converter/smartq/?action=quizzes&course={$courseId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }

    public function moodleSaveCourseQuiz(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'moodle_quizzes' => 'required|array|min:1',
            'moodle_quizzes.*.category_id' => 'nullable|integer',
            'moodle_quizzes.*.category_name' => 'nullable|string',
            'moodle_quizzes.*.course_id' => 'required|integer',
            'moodle_quizzes.*.course_name' => 'nullable|string',
            'moodle_quizzes.*.quiz_id' => 'required|integer',
            'moodle_quizzes.*.quiz_name' => 'required|string',
        ]);

        $quizzes = $request->input('moodle_quizzes');

        // Also update legacy single fields with first quiz for backward compatibility
        $first = $quizzes[0] ?? [];
        $smartq->update([
            'moodle_quizzes' => $quizzes,
            'moodle_category_id' => $first['category_id'] ?? null,
            'moodle_category_name' => $first['category_name'] ?? null,
            'moodle_course_id' => $first['course_id'] ?? null,
            'moodle_course_name' => $first['course_name'] ?? null,
            'moodle_quiz_id' => $first['quiz_id'] ?? null,
            'moodle_quiz_name' => $first['quiz_name'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => count($quizzes) . ' quiz berhasil disimpan']);
    }

    public function syncMoodle(SmartqPeriode $smartq)
    {
        $quizConfigs = $smartq->moodle_quizzes ?? [];

        // Fallback to legacy single quiz
        if (empty($quizConfigs) && $smartq->moodle_quiz_id) {
            $quizConfigs = [[
                'quiz_id' => $smartq->moodle_quiz_id,
                'quiz_name' => $smartq->moodle_quiz_name,
            ]];
        }

        if (!$smartq->moodle_base_url || empty($quizConfigs)) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Konfigurasi Moodle belum lengkap. Set URL dan Quiz terlebih dahulu.');
        }

        $pesertas = $smartq->pesertas()->with('siswa')->get();

        $komponenCbt = $smartq->komponenNilais()->where('sumber', 'moodle')->first();
        if (!$komponenCbt) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Tidak ada komponen nilai dengan sumber "moodle".');
        }

        try {
            $url = rtrim($smartq->moodle_base_url, '/') . '/converter/smartq/';

            // Collect scores from all quizzes (NO username filter — fetch all, match locally)
            $allScores = []; // username => [scores...]
            $allScoresByName = []; // lowercase firstname => [scores...]
            $quizNames = [];

            foreach ($quizConfigs as $qc) {
                $response = Http::timeout(30)->get($url, [
                    'action' => 'scores',
                    'quiz' => $qc['quiz_id'],
                ]);

                if (!$response->successful()) continue;
                $result = $response->json();
                if (!($result['success'] ?? false)) continue;

                $quizNames[] = $qc['quiz_name'] ?? 'Quiz ' . $qc['quiz_id'];

                foreach ($result['data'] ?? [] as $score) {
                    // Skip zero scores — likely wrong elective quiz
                    if (($score['normalized_100'] ?? 0) <= 0) continue;

                    $username = $score['username'];
                    $firstname = strtolower(trim($score['firstname'] ?? ''));

                    $entry = [
                        'normalized_100' => $score['normalized_100'],
                        'attempt_id' => $score['attempt_id'],
                        'raw_score' => $score['raw_score'],
                        'max_score' => $score['max_score'],
                        'quiz_name' => $qc['quiz_name'] ?? '',
                    ];

                    $allScores[$username][] = $entry;

                    if (!empty($firstname)) {
                        $allScoresByName[$firstname][] = $entry;
                    }
                }
            }

            $matched = 0;
            $notFound = 0;

            DB::transaction(function () use ($pesertas, $allScores, $allScoresByName, $komponenCbt, $quizNames, &$matched, &$notFound) {
                foreach ($pesertas as $peserta) {
                    $nisn = $peserta->siswa->nisn ?? '';
                    $namaKey = strtolower(trim($peserta->siswa->nama_lengkap ?? ''));

                    // Match by NISN=username first, then by nama=firstname
                    $scores = $allScores[$nisn] ?? null;
                    $matchBy = 'nisn';

                    if (empty($scores) && !empty($namaKey)) {
                        $scores = $allScoresByName[$namaKey] ?? null;
                        $matchBy = 'nama';
                    }

                    if (!$scores || empty($scores)) {
                        $notFound++;
                        continue;
                    }

                    // Average normalized score across all quizzes
                    $avgScore = collect($scores)->avg('normalized_100');
                    $nilaiRaw = round($avgScore, 2);
                    $nilaiKonversi = round(($nilaiRaw / $komponenCbt->nilai_maksimal) * 100, 2);
                    $bestAttempt = collect($scores)->sortByDesc('normalized_100')->first();

                    $quizDetail = collect($scores)->map(fn($s) => "{$s['quiz_name']}: {$s['normalized_100']}")->implode(', ');

                    SmartqNilai::updateOrCreate(
                        [
                            'smartq_peserta_id' => $peserta->id,
                            'smartq_komponen_nilai_id' => $komponenCbt->id,
                        ],
                        [
                            'nilai' => $nilaiRaw,
                            'nilai_konversi' => min(100, $nilaiKonversi),
                            'moodle_attempt_id' => $bestAttempt['attempt_id'],
                            'moodle_username' => $nisn,
                            'dinilai_oleh' => Auth::id(),
                            'dinilai_pada' => now(),
                            'catatan' => "Sync {$matchBy}: " . count($scores) . " quiz (avg): {$quizDetail}",
                        ]
                    );

                    $matched++;
                }
            });

            $smartq->hitungRanking();

            $msg = "Sync selesai dari " . count($quizConfigs) . " quiz: {$matched} siswa ter-match";
            if ($notFound > 0) {
                $msg .= ", {$notFound} tidak ditemukan di Moodle";
            }

            return redirect()->route('admin.smartq.show', $smartq)->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('SMART-Q Moodle sync error', ['error' => $e->getMessage()]);
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Error saat sync: ' . $e->getMessage());
        }
    }

    // ==================== MOODLE SCAN (Tambah Peserta dari Moodle) ====================

    public function moodleScan(SmartqPeriode $smartq)
    {
        $quizConfigs = $smartq->moodle_quizzes ?? [];
        $courseIds = $smartq->moodle_course_ids; // from accessor

        // Fallback to legacy
        if (empty($quizConfigs) && $smartq->moodle_course_id) {
            $courseIds = [$smartq->moodle_course_id];
            if ($smartq->moodle_quiz_id) {
                $quizConfigs = [[
                    'course_id' => $smartq->moodle_course_id,
                    'quiz_id' => $smartq->moodle_quiz_id,
                    'quiz_name' => $smartq->moodle_quiz_name,
                ]];
            }
        }

        if (!$smartq->moodle_base_url || empty($courseIds)) {
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Konfigurasi Moodle belum lengkap. Set URL dan Course/Quiz terlebih dahulu di Moodle Config.');
        }

        try {
            $url = rtrim($smartq->moodle_base_url, '/') . '/converter/smartq/';

            // 1. Collect enrolled users from all courses
            $moodleUsersMap = []; // username => user data
            foreach ($courseIds as $courseId) {
                $response = Http::timeout(30)->get($url, [
                    'action' => 'enrolled',
                    'course' => $courseId,
                ]);

                if (!$response->successful()) {
                    Log::warning('SMART-Q Moodle enrolled failed', ['course' => $courseId, 'status' => $response->status()]);
                    continue;
                }
                $result = $response->json();
                if (!($result['success'] ?? false)) {
                    Log::warning('SMART-Q Moodle enrolled error', ['course' => $courseId, 'result' => $result]);
                    continue;
                }

                foreach ($result['data'] ?? [] as $mu) {
                    $username = $mu['username'];
                    if (!isset($moodleUsersMap[$username])) {
                        $moodleUsersMap[$username] = [
                            'userid' => $mu['userid'],
                            'username' => $username,
                            'firstname' => $mu['firstname'] ?? '',
                            'lastname' => $mu['lastname'] ?? '',
                            'fullname' => $mu['fullname'],
                            'email' => $mu['email'],
                            'courses' => [],
                            'scores' => [],
                        ];
                    }
                    $moodleUsersMap[$username]['courses'][] = $courseId;
                }
            }

            // 2. Collect scores from all configured quizzes
            $quizIdToCourse = [];
            foreach ($quizConfigs as $qc) {
                $quizIdToCourse[$qc['quiz_id']] = $qc;
            }

            foreach ($quizConfigs as $qc) {
                $response = Http::timeout(30)->get($url, [
                    'action' => 'scores',
                    'quiz' => $qc['quiz_id'],
                ]);

                if (!$response->successful()) continue;
                $result = $response->json();
                if (!($result['success'] ?? false)) continue;

                foreach ($result['data'] ?? [] as $score) {
                    $username = $score['username'];

                    // Skip zero scores — likely entered wrong quiz (elective mismatch)
                    if (($score['normalized_100'] ?? 0) <= 0) continue;

                    $scoreEntry = [
                        'quiz_id' => $qc['quiz_id'],
                        'quiz_name' => $qc['quiz_name'] ?? '',
                        'course_id' => $qc['course_id'] ?? null,
                        'category_name' => $qc['category_name'] ?? null,
                        'normalized_100' => $score['normalized_100'],
                        'attempt_id' => $score['attempt_id'],
                        'raw_score' => $score['raw_score'],
                        'max_score' => $score['max_score'],
                    ];

                    if (isset($moodleUsersMap[$username])) {
                        $moodleUsersMap[$username]['scores'][] = $scoreEntry;
                    } else {
                        // User has score but not found in enrolled list — add them
                        $moodleUsersMap[$username] = [
                            'userid' => $score['userid'],
                            'username' => $username,
                            'firstname' => $score['firstname'] ?? '',
                            'lastname' => $score['lastname'] ?? '',
                            'fullname' => $score['fullname'] ?? $username,
                            'email' => $score['email'] ?? '',
                            'courses' => [],
                            'scores' => [$scoreEntry],
                        ];
                    }
                }
            }

            // 3. Match with SIMANSA students by NISN=username OR nama_lengkap=firstname
            $siswaAll = Siswa::whereHas('kelasAktif', function ($q) {
                    $q->whereIn('kelas.tingkat', [10, 11]);
                })
                ->where('status_siswa', 'aktif')
                ->get();

            // Build lookup indexes for faster matching
            $siswaByNisn = $siswaAll->filter(fn($s) => !empty($s->nisn))->keyBy('nisn');
            $siswaByNama = [];
            foreach ($siswaAll as $s) {
                $namaKey = strtolower(trim($s->nama_lengkap));
                if (!empty($namaKey)) {
                    $siswaByNama[$namaKey] = $s;
                }
            }

            $pesertaExisting = $smartq->pesertas()->with('siswa')->get();
            $registeredSiswaIds = $pesertaExisting->pluck('siswa_id')->toArray();

            $rows = [];
            $matchedSiswaIds = []; // Prevent duplicate matches
            foreach ($moodleUsersMap as $mu) {
                $username = $mu['username'];
                $firstname = strtolower(trim($mu['firstname'] ?? ''));
                $fullname = strtolower(trim($mu['fullname'] ?? ''));

                // Try match: 1) NISN = username, 2) nama_lengkap = firstname, 3) nama_lengkap = fullname
                $matchedSiswa = null;
                $matchMethod = null;

                if (isset($siswaByNisn[$username])) {
                    $matchedSiswa = $siswaByNisn[$username];
                    $matchMethod = 'nisn';
                }

                if (!$matchedSiswa && !empty($firstname) && isset($siswaByNama[$firstname])) {
                    $candidate = $siswaByNama[$firstname];
                    if (!in_array($candidate->id, $matchedSiswaIds)) {
                        $matchedSiswa = $candidate;
                        $matchMethod = 'nama';
                    }
                }

                if (!$matchedSiswa && !empty($fullname) && $fullname !== $firstname && isset($siswaByNama[$fullname])) {
                    $candidate = $siswaByNama[$fullname];
                    if (!in_array($candidate->id, $matchedSiswaIds)) {
                        $matchedSiswa = $candidate;
                        $matchMethod = 'nama';
                    }
                }

                if ($matchedSiswa) {
                    $matchedSiswaIds[] = $matchedSiswa->id;
                }

                $isRegistered = $matchedSiswa && in_array($matchedSiswa->id, $registeredSiswaIds);

                $hasScores = !empty($mu['scores']);
                $avgScore = $hasScores ? round(collect($mu['scores'])->avg('normalized_100'), 2) : null;

                $status = 'no_match';
                if ($matchedSiswa && $isRegistered) {
                    $status = 'already_registered';
                } elseif ($matchedSiswa && !$isRegistered) {
                    $status = $hasScores ? 'ready' : 'ready_no_score';
                }

                $rows[] = [
                    'moodle_userid' => $mu['userid'],
                    'moodle_username' => $username,
                    'moodle_fullname' => $mu['fullname'],
                    'moodle_firstname' => $mu['firstname'] ?? '',
                    'moodle_lastname' => $mu['lastname'] ?? '',
                    'moodle_email' => $mu['email'],
                    'has_attempt' => $hasScores,
                    'normalized_100' => $avgScore,
                    'scores' => $mu['scores'],
                    'attempt_id' => $hasScores ? $mu['scores'][0]['attempt_id'] : null,
                    'courses_count' => count(array_unique($mu['courses'] ?? [])),
                    'siswa_id' => $matchedSiswa?->id,
                    'siswa_nama' => $matchedSiswa?->nama_lengkap,
                    'siswa_nisn' => $matchedSiswa?->nisn,
                    'siswa_kelas' => $matchedSiswa?->getKelasSekarang()?->nama_lengkap,
                    'match_method' => $matchMethod,
                    'status' => $status,
                ];
            }

            // Sort: ready first, then by score
            usort($rows, function ($a, $b) {
                $order = ['ready' => 0, 'ready_no_score' => 1, 'already_registered' => 2, 'no_match' => 3];
                $cmp = ($order[$a['status']] ?? 9) <=> ($order[$b['status']] ?? 9);
                if ($cmp !== 0) return $cmp;
                return ($b['normalized_100'] ?? 0) <=> ($a['normalized_100'] ?? 0);
            });

            $summary = [
                'total_moodle' => count($rows),
                'matched' => collect($rows)->whereIn('status', ['ready', 'ready_no_score'])->count(),
                'already_registered' => collect($rows)->where('status', 'already_registered')->count(),
                'no_match' => collect($rows)->where('status', 'no_match')->count(),
                'with_scores' => collect($rows)->where('has_attempt', true)->whereIn('status', ['ready', 'ready_no_score'])->count(),
                'courses_scanned' => count($courseIds),
                'quizzes_scanned' => count($quizConfigs),
            ];

            $cacheKey = 'smartq_scan_' . $smartq->id . '_' . Auth::id();
            Cache::put($cacheKey, [
                'rows' => $rows,
                'summary' => $summary,
                'scanned_at' => now()->toDateTimeString(),
            ], now()->addMinutes(30));

            // Persist scan data to DB for Nilai CBT feature
            $smartq->update([
                'last_scan_data' => ['rows' => $rows, 'summary' => $summary],
                'last_scan_at' => now(),
            ]);

            // Get available kelas for unmatched users assignment
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $kelasAvailable = $tahunAktif
                ? Kelas::where('tahun_pelajaran_id', $tahunAktif->id)
                    ->where('is_active', true)
                    ->with('jurusan')
                    ->orderBy('tingkat')
                    ->orderBy('nama_kelas')
                    ->get()
                : collect();

            return view('admin.smartq.preview-moodle', compact('smartq', 'rows', 'summary', 'cacheKey', 'kelasAvailable'));

        } catch (\Exception $e) {
            Log::error('SMART-Q Moodle scan error', ['error' => $e->getMessage()]);
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Error saat scan Moodle: ' . $e->getMessage());
        }
    }

    public function confirmMoodleScan(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'cache_key' => 'required|string',
            'selected' => 'required|array|min:1',
            'import_scores' => 'nullable|boolean',
        ]);

        $cached = Cache::get($request->cache_key);
        if (!$cached) {
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Data preview sudah kadaluarsa (30 menit). Silakan scan ulang.');
        }

        $selectedUsernames = $request->selected;
        $importScores = (bool)$request->input('import_scores', true);
        $rows = collect($cached['rows']);

        // Filter only selected & importable rows
        $toImport = $rows->filter(function ($row) use ($selectedUsernames) {
            return in_array($row['moodle_username'], $selectedUsernames)
                && in_array($row['status'], ['ready', 'ready_no_score']);
        });

        if ($toImport->isEmpty()) {
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Tidak ada peserta valid yang dipilih untuk diimport.');
        }

        // Find CBT komponen (sumber = moodle)
        $komponenCbt = $smartq->komponenNilais()->where('sumber', 'moodle')->first();

        $lastNumber = $smartq->pesertas()->max(DB::raw("CAST(REPLACE(nomor_peserta, 'SQ-', '') AS UNSIGNED)")) ?? 0;
        $added = 0;
        $scored = 0;

        DB::transaction(function () use ($toImport, $smartq, $komponenCbt, $importScores, &$lastNumber, &$added, &$scored) {
            foreach ($toImport as $row) {
                // Double-check not already registered
                $exists = $smartq->pesertas()->where('siswa_id', $row['siswa_id'])->exists();
                if ($exists) continue;

                $siswa = Siswa::find($row['siswa_id']);
                if (!$siswa) continue;

                $kelasAktif = $siswa->getKelasSekarang();
                $lastNumber++;

                $peserta = SmartqPeserta::create([
                    'smartq_periode_id' => $smartq->id,
                    'siswa_id' => $row['siswa_id'],
                    'nomor_peserta' => 'SQ-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT),
                    'kelas_asal_id' => $kelasAktif?->id,
                ]);

                // Create empty nilai for each komponen
                foreach ($smartq->komponenNilais as $komponen) {
                    $nilaiData = [
                        'smartq_peserta_id' => $peserta->id,
                        'smartq_komponen_nilai_id' => $komponen->id,
                    ];

                    // If this is the CBT komponen and has score from Moodle, pre-fill
                    if ($importScores && $komponenCbt && $komponen->id === $komponenCbt->id && $row['has_attempt'] && $row['normalized_100'] !== null) {
                        $nilaiRaw = $row['normalized_100'];
                        $nilaiKonversi = $komponen->nilai_maksimal > 0
                            ? round(($nilaiRaw / $komponen->nilai_maksimal) * 100, 2)
                            : 0;

                        $nilaiData['nilai'] = $nilaiRaw;
                        $nilaiData['nilai_konversi'] = min(100, $nilaiKonversi);
                        $nilaiData['moodle_attempt_id'] = $row['attempt_id'];
                        $nilaiData['moodle_username'] = $row['moodle_username'];
                        $nilaiData['dinilai_oleh'] = Auth::id();
                        $nilaiData['dinilai_pada'] = now();
                        $scoresDetail = collect($row['scores'] ?? [])->map(fn($s) => ($s['quiz_name'] ?? '') . ':' . ($s['normalized_100'] ?? 0))->implode(', ');
                        $nilaiData['catatan'] = "Import Moodle Scan: Avg {$nilaiRaw} ({$scoresDetail})";
                        $scored++;
                    }

                    SmartqNilai::create($nilaiData);
                }

                $added++;
            }

            // Recalculate ranking if scores were imported
            if ($scored > 0) {
                $smartq->hitungRanking();
            }
        });

        // Clear cache
        Cache::forget($request->cache_key);

        $msg = "{$added} peserta berhasil diimport dari Moodle.";
        if ($scored > 0) {
            $msg .= " {$scored} nilai CBT otomatis terisi.";
        }

        return redirect()->route('admin.smartq.show', $smartq)->with('success', $msg);
    }

    /**
     * Add unmatched Moodle users to SIMANSA as new students.
     * Parse lastname for kelas/tingkat, create User + Siswa + Ortu + assign to kelas.
     */
    public function addUnmatchedToSimansa(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'cache_key' => 'required|string',
            'selected_unmatched' => 'required|array|min:1',
            'kelas_mapping' => 'required|array',
            'kelas_mapping.*' => 'nullable|string', // kelas_id or empty
        ]);

        $cached = Cache::get($request->cache_key);
        if (!$cached) {
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Data preview sudah kadaluarsa (30 menit). Silakan scan ulang.');
        }

        $selectedUsernames = $request->selected_unmatched;
        $kelasMapping = $request->kelas_mapping; // moodle_lastname => kelas_id
        $rows = collect($cached['rows']);

        $toAdd = $rows->filter(function ($row) use ($selectedUsernames) {
            return in_array($row['moodle_username'], $selectedUsernames)
                && $row['status'] === 'no_match';
        });

        if ($toAdd->isEmpty()) {
            return redirect()->route('admin.smartq.peserta', $smartq)
                ->with('error', 'Tidak ada siswa yang valid untuk ditambahkan.');
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $added = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($toAdd, $kelasMapping, $tahunAktif, &$added, &$skipped, &$errors) {
            foreach ($toAdd as $row) {
                $nisn = $row['moodle_username'];
                $namaLengkap = $row['moodle_firstname'] ?: $row['moodle_fullname'];
                $moodleLastname = $row['moodle_lastname'] ?? '';

                // Skip if NISN already exists in Siswa
                if (Siswa::where('nisn', $nisn)->exists()) {
                    $skipped++;
                    $errors[] = "{$namaLengkap} ({$nisn}): NISN sudah ada di SIMANSA";
                    continue;
                }

                // Skip if username already exists in User
                if (User::where('username', $nisn)->exists()) {
                    $skipped++;
                    $errors[] = "{$namaLengkap} ({$nisn}): Username sudah ada";
                    continue;
                }

                // 1. Create User
                $user = User::create([
                    'name' => $namaLengkap,
                    'username' => $nisn,
                    'email' => $nisn . '@student.man1metro.sch.id',
                    'password' => Hash::make($nisn),
                    'role' => 'siswa',
                    'is_first_login' => true,
                ]);
                $user->readable_password = $nisn;
                $user->save();
                $user->assignRole('Siswa');

                // 2. Create Siswa
                $siswa = Siswa::create([
                    'user_id' => $user->id,
                    'nisn' => $nisn,
                    'nama_lengkap' => $namaLengkap,
                    'status_siswa' => 'aktif',
                    'tahun_masuk' => $tahunAktif?->tahun_mulai ?? date('Y'),
                ]);

                // 3. Create empty Ortu
                Ortu::create(['siswa_id' => $siswa->id]);

                // 4. Assign to Kelas if mapping exists
                $kelasId = $kelasMapping[$moodleLastname] ?? null;
                if ($kelasId && $tahunAktif) {
                    $kelas = Kelas::find($kelasId);
                    if ($kelas) {
                        $lastAbsen = $kelas->siswas()
                            ->wherePivot('tahun_pelajaran_id', $tahunAktif->id)
                            ->max('siswa_kelas.nomor_urut_absen') ?? 0;

                        $kelas->siswas()->attach($siswa->id, [
                            'id' => Str::uuid(),
                            'tahun_pelajaran_id' => $tahunAktif->id,
                            'tanggal_masuk' => now()->format('Y-m-d'),
                            'status' => 'aktif',
                            'nomor_urut_absen' => $lastAbsen + 1,
                        ]);

                        $siswa->update(['kelas_saat_ini_id' => $kelas->id]);
                    }
                }

                $added++;
            }
        });

        // Don't clear cache — admin might still want to import to SMART-Q
        $msg = "{$added} siswa berhasil ditambahkan ke SIMANSA.";
        if ($skipped > 0) {
            $msg .= " {$skipped} dilewati (sudah ada).";
        }
        if (!empty($errors)) {
            $msg .= ' Detail: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.smartq.peserta', $smartq)->with('success', $msg);
    }

    // ==================== RANKING & KEPUTUSAN ====================

    public function rankingData(SmartqPeriode $smartq)
    {
        $smartq->load('komponenNilais');

        $pesertas = SmartqPeserta::with(['siswa', 'kelasAsal', 'bidangMapel', 'nilais.komponenNilai'])
            ->where('smartq_periode_id', $smartq->id)
            ->orderBy('ranking')
            ->orderByDesc('total_nilai')
            ->get();

        $rows = $pesertas->map(function ($p) use ($smartq) {
            $row = [
                'ranking' => $p->ranking,
                'ranking_display' => $p->ranking && $p->ranking <= 3
                    ? '<span class="badge badge-' . ($p->ranking === 1 ? 'warning' : ($p->ranking === 2 ? 'secondary' : 'info')) . '"><i class="fas fa-trophy"></i> ' . $p->ranking . '</span>'
                    : ($p->ranking ?? '-'),
                'nomor_peserta' => '<code>' . e($p->nomor_peserta) . '</code>',
                'nama' => '<strong>' . e($p->siswa->nama_lengkap ?? '-') . '</strong><br><small class="text-muted">' . ($p->siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') . '</small>',
                'nama_sort' => $p->siswa->nama_lengkap ?? '-',
                'nisn' => '<small>' . e($p->siswa->nisn ?? '-') . '</small>',
                'kelas' => e($p->kelasAsal->nama_lengkap ?? '-'),
            ];

            foreach ($smartq->komponenNilais as $k) {
                $nilai = $p->getNilaiKomponen($k->id);
                $row['komponen_' . $k->id] = $nilai && $nilai->nilai !== null
                    ? '<strong>' . number_format($nilai->nilai, 1) . '</strong>' . ($k->isMoodle() && $nilai->moodle_attempt_id ? '<br><small class="text-muted"><i class="fas fa-cloud"></i></small>' : '')
                    : '<span class="text-muted">-</span>';
                $row['komponen_' . $k->id . '_raw'] = $nilai?->nilai ?? 0;
            }

            $row['total'] = $p->total_nilai !== null ? number_format($p->total_nilai, 2) : '-';
            $row['total_raw'] = $p->total_nilai ?? 0;
            $row['status'] = $p->status_badge;
            $row['status_raw'] = $p->status;
            $row['bidang'] = $p->bidangMapel
                ? '<span class="badge badge-info" title="' . e($p->bidangMapel->nama_mapel) . '">' . e($p->bidangMapel->kode_mapel) . '</span>'
                : '<span class="text-muted">-</span>';
            $row['peringkat_mapel'] = $p->peringkat_mapel ?? '-';
            $row['row_class'] = $p->status === 'lulus' ? 'table-success' : ($p->status === 'cadangan' ? 'table-warning' : ($p->status === 'tidak_lulus' ? 'table-danger' : ''));

            return $row;
        });

        return response()->json([
            'data' => $rows->values(),
            'komponen' => $smartq->komponenNilais->map(fn($k) => [
                'id' => $k->id,
                'kode' => $k->kode,
                'nama' => $k->nama,
                'bobot' => rtrim(rtrim(number_format($k->bobot, 2), '0'), '.') . '%',
            ]),
        ]);
    }

    // ==================== EXPORT ====================

    public function exportExcel(SmartqPeriode $smartq)
    {
        $smartq->load('komponenNilais');
        $pesertas = SmartqPeserta::with(['siswa', 'kelasAsal', 'bidangMapel', 'nilais.komponenNilai'])
            ->where('smartq_periode_id', $smartq->id)
            ->orderBy('ranking')
            ->get();

        $safeName = preg_replace('/[\/\\:*?"<>|]/', '-', $smartq->nama);
        $filename = 'SMARTQ_' . str_replace(' ', '_', $safeName) . '_' . date('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pesertas, $smartq) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            // Header row
            $header = ['Ranking', 'No. Peserta', 'NISN', 'Nama Lengkap', 'Kelas Asal'];
            foreach ($smartq->komponenNilais as $k) {
                $header[] = $k->nama . " (Bobot {$k->bobot}%)";
            }
            $header[] = 'Total Nilai';
            $header[] = 'Status';
            $header[] = 'Bidang Mapel';
            $header[] = 'Peringkat Mapel';
            fputcsv($file, $header);

            foreach ($pesertas as $p) {
                $row = [
                    $p->ranking ?? '-',
                    $p->nomor_peserta,
                    $p->siswa->nisn ?? '-',
                    $p->siswa->nama_lengkap ?? '-',
                    $p->kelasAsal->nama_lengkap ?? '-',
                ];

                foreach ($smartq->komponenNilais as $k) {
                    $nilai = $p->getNilaiKomponen($k->id);
                    $row[] = $nilai?->nilai ?? '-';
                }

                $row[] = $p->total_nilai ?? '-';
                $row[] = ucfirst(str_replace('_', ' ', $p->status));
                $row[] = $p->bidangMapel?->nama_mapel ?? '-';
                $row[] = $p->peringkat_mapel ?? '-';
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== EXPORT SCAN REPORT ====================

    public function exportScanReport(Request $request, SmartqPeriode $smartq)
    {
        $cacheKey = $request->query('cache_key');
        $format = $request->query('format', 'excel');
        $onlyHadir = $format === 'excel_hadir';

        // Try cache first, fallback to DB
        $rows = null;
        if ($cacheKey) {
            $cached = Cache::get($cacheKey);
            $rows = $cached['rows'] ?? ($cached ? $cached : null);
        }
        if (!$rows && !empty($smartq->last_scan_data)) {
            $rows = $smartq->last_scan_data['rows'] ?? [];
        }

        if (!$rows) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Belum ada data scan. Lakukan scan dari Moodle terlebih dahulu.');
        }

        // Build quiz→tingkat mapping from category_name
        $quizConfigs = collect($smartq->moodle_quizzes ?? []);
        $quizTingkat = [];
        foreach ($quizConfigs as $qc) {
            $cat = $qc['category_name'] ?? '';
            if (preg_match('/(\d{2})\s*$/', $cat, $m)) {
                $quizTingkat[$qc['quiz_id']] = (int) $m[1];
            }
        }

        // Assign tingkat to each student
        $rowsCollection = collect($rows);
        $parseTingkat = function($kelas) {
            if (preg_match('/\bXII\b|tingkat\s*12|kelas\s*12/i', $kelas)) return 12;
            if (preg_match('/\bXI\b|tingkat\s*11|kelas\s*11/i', $kelas)) return 11;
            if (preg_match('/\bX\b|tingkat\s*10|kelas\s*10/i', $kelas)) return 10;
            return 0;
        };

        $rowsWithTingkat = $rowsCollection->map(function($r) use ($quizTingkat, $parseTingkat) {
            $kelas = $r['siswa_kelas'] ?? $r['moodle_lastname'] ?? '';
            $tkt = $parseTingkat($kelas);
            if (!$tkt && !empty($r['scores'])) {
                $tkt = collect($r['scores'])->map(fn($s) => $quizTingkat[$s['quiz_id']] ?? 0)->filter()->countBy()->sortDesc()->keys()->first() ?? 0;
            }
            $r['_tingkat'] = $tkt;
            return $r;
        });

        $byTingkat = $rowsWithTingkat->groupBy('_tingkat')->sortKeys();

        if ($onlyHadir) {
            $byTingkat = $byTingkat
                ->map(fn($tktRows) => collect($tktRows)->filter(fn($r) => ($r['has_attempt'] ?? false))->values())
                ->filter(fn($tktRows) => $tktRows->count() > 0);
        }

        // Build mapel per tingkat
        $allMapel = $rowsCollection->flatMap(fn($r) => $r['scores'] ?? [])->unique('quiz_id')->sortBy('quiz_name')->values();
        $mapelByTingkat = [];
        foreach ($allMapel as $m) {
            $tkt = $quizTingkat[$m['quiz_id']] ?? 0;
            $mapelByTingkat[$tkt][] = $m;
        }

        // Prioritas urutan mapel: Matematika Wajib, Bahasa Inggris, Bahasa Indonesia, sisanya
        foreach ($mapelByTingkat as $tkt => $mapels) {
            $mapelByTingkat[$tkt] = collect($mapels)
                ->sort(function ($a, $b) {
                    $nameA = strtolower($a['quiz_name'] ?? '');
                    $nameB = strtolower($b['quiz_name'] ?? '');

                    $priorityA = str_contains($nameA, 'matematika wajib') ? 1
                        : (str_contains($nameA, 'bahasa inggris') ? 2
                        : (str_contains($nameA, 'bahasa indonesia') ? 3 : 4));

                    $priorityB = str_contains($nameB, 'matematika wajib') ? 1
                        : (str_contains($nameB, 'bahasa inggris') ? 2
                        : (str_contains($nameB, 'bahasa indonesia') ? 3 : 4));

                    if ($priorityA !== $priorityB) {
                        return $priorityA <=> $priorityB;
                    }

                    return strcmp($nameA, $nameB);
                })
                ->values()
                ->all();
        }

        if ($format === 'pdf') {
            return $this->exportScanPdf($byTingkat, $mapelByTingkat, $quizTingkat, $smartq);
        }

        // Excel (.xlsx) with Maatwebsite/Excel — multi-sheet per tingkat
        ini_set('memory_limit', '512M');
        $safeName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $smartq->nama);
        $filename = ($onlyHadir ? 'SMARTQ_NilaiCBT_HADIR_' : 'SMARTQ_NilaiCBT_') . str_replace(' ', '_', $safeName) . '_' . date('Ymd') . '.xlsx';
        return Excel::download(
            new NilaiCbtExport($byTingkat, $mapelByTingkat, $quizTingkat, $smartq),
            $filename
        );
    }

    private function exportScanPdf($byTingkat, $mapelByTingkat, $quizTingkat, $smartq)
    {
        ini_set('memory_limit', '512M');

        $style = '<style>
            body { font-family: sans-serif; font-size: 9px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
            th, td { border: 1px solid #555; padding: 2px 4px; }
            th { background: #333; color: #fff; text-align: center; font-size: 8px; }
            .bs { background: #28a745; color:#fff; padding: 1px 4px; border-radius: 2px; }
            .bp { background: #007bff; color:#fff; padding: 1px 4px; border-radius: 2px; }
            .bw { background: #ffc107; color:#000; padding: 1px 4px; border-radius: 2px; }
            .bd { background: #dc3545; color:#fff; padding: 1px 4px; border-radius: 2px; }
            .tc { text-align: center; }
            .th { color: #dc3545; font-weight: bold; }
            h2 { margin: 5px 0; font-size: 14px; }
            h3 { margin: 8px 0 4px; font-size: 12px; background: #f0f0f0; padding: 4px; }
            .page-break { page-break-before: always; }
        </style>';

        $totalAll = $byTingkat->flatten(1)->count();
        $html = $style;
        $html .= '<h2>Laporan Nilai CBT Moodle — ' . e($smartq->nama) . '</h2>';
        $html .= '<p>Tanggal: ' . now()->format('d/m/Y H:i') . ' | Total: ' . $totalAll . ' siswa | ' . $byTingkat->count() . ' tingkat</p>';

        $isFirstTingkat = true;
        foreach ($byTingkat as $tkt => $tktRows) {
            $tktLabel = $tkt ? 'Tingkat ' . $tkt : 'Lainnya';
            $tktMapel = collect($mapelByTingkat[$tkt] ?? []);
            $tktTotal = $tktRows->count();
            $tktHadir = $tktRows->filter(fn($r) => ($r['has_attempt'] ?? false))->count();

            // Wajib detection per tingkat
            $tktMapelWajib = [];
            foreach ($tktMapel as $m) {
                $attempts = $tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $m['quiz_id'])->where('normalized_100', '>', 0)->count();
                if ($attempts > ($tktTotal * 0.5)) $tktMapelWajib[] = $m['quiz_id'];
            }

            if (!$isFirstTingkat) $html .= '<div class="page-break"></div>';
            $isFirstTingkat = false;

            $html .= '<h3>' . e($tktLabel) . ' — ' . $tktTotal . ' siswa (' . $tktHadir . ' hadir, ' . ($tktTotal - $tktHadir) . ' tidak hadir) — ' . $tktMapel->count() . ' mapel</h3>';

            // Ringkasan mapel tingkat ini
            $html .= '<table><tr><th>Mapel</th><th>Tipe</th><th>Mengerjakan</th><th>Rata-rata</th><th>Tertinggi</th><th>Terendah</th></tr>';
            foreach ($tktMapel as $m) {
                $scores = $tktRows->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $m['quiz_id'])->where('normalized_100', '>', 0);
                $isW = in_array($m['quiz_id'], $tktMapelWajib);
                $html .= '<tr><td>' . e($m['quiz_name']) . '</td>';
                $html .= '<td class="tc">' . ($isW ? 'Wajib' : 'Pilihan') . '</td>';
                $html .= '<td class="tc">' . $scores->count() . '/' . $tktTotal . '</td>';
                $html .= '<td class="tc">' . ($scores->count() > 0 ? round($scores->avg('normalized_100'), 1) : 0) . '</td>';
                $html .= '<td class="tc">' . ($scores->count() > 0 ? round($scores->max('normalized_100'), 1) : 0) . '</td>';
                $html .= '<td class="tc">' . ($scores->count() > 0 ? round($scores->min('normalized_100'), 1) : 0) . '</td></tr>';
            }
            $html .= '</table>';

            // Rekap nilai per kelas
            $byKelas = $tktRows->groupBy(fn($r) => $r['siswa_kelas'] ?? $r['moodle_lastname'] ?? 'Tanpa Kelas')->sortKeys();
            foreach ($byKelas as $kelasName => $kelasRows) {
                $html .= '<p style="margin:6px 0 2px;font-weight:bold">' . e($kelasName) . ' (' . count($kelasRows) . ' siswa)</p>';
                $html .= '<table><tr><th>#</th><th>Nama</th><th>NISN</th>';
                foreach ($tktMapel as $m) {
                    $html .= '<th>' . e($m['quiz_name']) . '</th>';
                }
                if ($tktMapel->count() > 1) $html .= '<th>Rata²</th>';
                $html .= '<th>Hadir</th></tr>';

                $num = 0;
                foreach (collect($kelasRows)->sortByDesc('normalized_100') as $row) {
                    $num++;
                    $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                    $isHadir = $row['has_attempt'] ?? false;

                    $html .= '<tr' . (!$isHadir ? ' style="background:#ffe0e0"' : '') . '>';
                    $html .= '<td class="tc">' . $num . '</td>';
                    $html .= '<td>' . e(($row['siswa_nama'] ?? '') ?: (($row['moodle_firstname'] ?? '') ?: ($row['moodle_fullname'] ?? '-'))) . '</td>';
                    $html .= '<td>' . e($row['moodle_username'] ?? '-') . '</td>';

                    $scoreValues = [];
                    foreach ($tktMapel as $m) {
                        $score = $rowScores->get($m['quiz_id']);
                        if ($score) {
                            $v = $score['normalized_100'];
                            $cls = $v >= 80 ? 'bs' : ($v >= 60 ? 'bp' : ($v >= 40 ? 'bw' : 'bd'));
                            $html .= '<td class="tc"><span class="' . $cls . '">' . $v . '</span></td>';
                            $scoreValues[] = $v;
                        } else {
                            $html .= '<td class="tc">-</td>';
                        }
                    }

                    if ($tktMapel->count() > 1) {
                        $avg = count($scoreValues) > 0 ? round(array_sum($scoreValues) / count($scoreValues), 1) : '-';
                        $html .= '<td class="tc"><strong>' . $avg . '</strong></td>';
                    }
                    $html .= '<td class="tc">' . ($isHadir ? '✓' : '<span class="th">✗</span>') . '</td></tr>';
                }
                $html .= '</table>';
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'sans-serif']);

        $safeName = preg_replace('/[\/\\:*?"<>|]/', '-', $smartq->nama);
        $filename = 'SMARTQ_NilaiCBT_' . str_replace(' ', '_', $safeName) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    // ==================== IMPORT KELULUSAN ====================

    public function importKelulusanForm(SmartqPeriode $smartq)
    {
        $mapelPilihan = MataPelajaran::where('is_mapel_pilihan', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        return view('admin.smartq.import-kelulusan', compact('smartq', 'mapelPilihan'));
    }

    public function importKelulusanTemplate(SmartqPeriode $smartq)
    {
        $pesertas = SmartqPeserta::where('smartq_periode_id', $smartq->id)
            ->with('siswa.user')
            ->orderBy('ranking')
            ->get();

        $mapelPilihan = MataPelajaran::where('is_mapel_pilihan', true)
            ->where('is_active', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        $safeName = preg_replace('/[\/\\:*?"<>|]/', '-', $smartq->nama);

        return Excel::download(
            new SmartqKelulusanTemplateExport($pesertas, $mapelPilihan),
            "Template_Kelulusan_{$safeName}.xlsx"
        );
    }

    /**
     * Preview: parse file, validate, return match status per row (no save).
     * Store file in temp for confirm step.
     */
    public function importKelulusanPreview(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');

        // Store temp file for confirm step
        $tempPath = $file->store('smartq-import-temp');

        // Parse rows from Excel: NAMA | NISN | PERINGKAT MAPEL | PERINGKAT UMUM | MAPEL | STATUS
        $rows = $this->parseKelulusanExcel(storage_path('app/' . $tempPath));

        if (empty($rows)) {
            Storage::delete($tempPath);
            return response()->json([
                'success' => false,
                'message' => 'File tidak berisi data. Pastikan kolom MAPEL sudah diisi.',
            ], 422);
        }

        // Preload lookup maps
        $mapelMap = MataPelajaran::where('is_mapel_pilihan', true)
            ->get(['id', 'nama_mapel'])
            ->mapWithKeys(fn($m) => [mb_strtolower($m->nama_mapel) => $m->id]);

        $pesertaMap = SmartqPeserta::where('smartq_periode_id', $smartq->id)
            ->with('siswa')
            ->get()
            ->keyBy(fn($p) => $p->siswa?->nisn);

        // Validate each row — build preview with match status
        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // +1 header, +1 zero-index
            $nisn = $row['nisn'];
            $namaMapel = $row['mapel'];
            $status = $row['status'];
            $errors = [];

            // Check status
            $statusValid = in_array($status, ['diterima', 'cadangan']);
            if (!$statusValid) {
                $errors[] = "Status '{$status}' tidak valid";
            }

            // Check mapel
            $mapelKey = mb_strtolower($namaMapel);
            $mapelValid = $mapelMap->has($mapelKey);
            if (!$mapelValid) {
                $errors[] = "Mapel '{$namaMapel}' tidak ditemukan";
            }

            // Check NISN match
            $peserta = $pesertaMap->get($nisn);
            $nisnMatch = $peserta !== null;
            if (!$nisnMatch) {
                $errors[] = 'NISN tidak ditemukan di peserta';
            }

            // Check nama match (fuzzy)
            $namaMatch = false;
            $namaDb = '-';
            if ($peserta) {
                $namaDb = $peserta->siswa?->nama_lengkap ?? '-';
                $namaMatch = mb_strtolower(trim($row['nama'])) === mb_strtolower(trim($namaDb));
            }

            $isValid = empty($errors);
            if ($isValid) $validCount++;
            else $errorCount++;

            $preview[] = [
                'row' => $rowNum,
                'nama_file' => $row['nama'],
                'nama_db' => $namaDb,
                'nama_match' => $namaMatch,
                'nisn' => $nisn,
                'nisn_match' => $nisnMatch,
                'peringkat_mapel' => $row['peringkat_mapel'],
                'peringkat_umum' => $row['peringkat_umum'],
                'mapel' => $namaMapel,
                'mapel_match' => $mapelValid,
                'status' => $status,
                'status_valid' => $statusValid,
                'valid' => $isValid,
                'errors' => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'temp_path' => $tempPath,
            'data' => [
                'total' => count($rows),
                'valid_count' => $validCount,
                'error_count' => $errorCount,
                'rows' => $preview,
            ],
        ]);
    }

    /**
     * Confirm: re-read temp file, save valid rows to database.
     */
    public function importKelulusanConfirm(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'temp_path' => 'required|string',
        ]);

        $tempPath = $request->input('temp_path');
        $fullPath = storage_path('app/' . $tempPath);

        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File sementara tidak ditemukan. Silakan upload ulang.',
            ], 422);
        }

        $rows = $this->parseKelulusanExcel($fullPath);

        // Clean up temp file
        Storage::delete($tempPath);

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak berisi data.',
            ], 422);
        }

        // Preload lookup maps
        $mapelMap = MataPelajaran::where('is_mapel_pilihan', true)
            ->get(['id', 'nama_mapel'])
            ->mapWithKeys(fn($m) => [mb_strtolower($m->nama_mapel) => $m->id]);

        $pesertaMap = SmartqPeserta::where('smartq_periode_id', $smartq->id)
            ->with('siswa')
            ->get()
            ->keyBy(fn($p) => $p->siswa?->nisn);

        $results = ['success' => [], 'errors' => []];
        $rowNum = 1;

        DB::transaction(function () use ($rows, $pesertaMap, $mapelMap, &$results, &$rowNum) {
            foreach ($rows as $row) {
                $rowNum++;
                $nisn = $row['nisn'];
                $namaMapel = $row['mapel'];
                $status = $row['status'];

                // Validate status
                if (!in_array($status, ['diterima', 'cadangan'])) {
                    $results['errors'][] = [
                        'row' => $rowNum,
                        'nisn' => $nisn,
                        'nama' => $row['nama'],
                        'error' => "Status '{$status}' tidak valid.",
                    ];
                    continue;
                }

                // Validate mapel
                $mapelKey = mb_strtolower($namaMapel);
                if (!$mapelMap->has($mapelKey)) {
                    $results['errors'][] = [
                        'row' => $rowNum,
                        'nisn' => $nisn,
                        'nama' => $row['nama'],
                        'error' => "Mapel '{$namaMapel}' tidak ditemukan.",
                    ];
                    continue;
                }

                // Find peserta
                $peserta = $pesertaMap->get($nisn);
                if (!$peserta) {
                    $results['errors'][] = [
                        'row' => $rowNum,
                        'nisn' => $nisn,
                        'nama' => $row['nama'],
                        'error' => 'NISN tidak ditemukan.',
                    ];
                    continue;
                }

                $dbStatus = $status === 'diterima' ? 'lulus' : 'cadangan';

                $peserta->update([
                    'status' => $dbStatus,
                    'bidang_mapel_id' => $mapelMap->get($mapelKey),
                    'ranking' => $row['peringkat_umum'],
                    'peringkat_mapel' => $row['peringkat_mapel'],
                ]);

                $nama = $peserta->siswa?->nama_lengkap ?? $row['nama'];
                $results['success'][] = [
                    'row' => $rowNum,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'status' => $status,
                    'mapel' => $namaMapel,
                    'peringkat_mapel' => $row['peringkat_mapel'],
                    'peringkat_umum' => $row['peringkat_umum'],
                ];
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Import kelulusan selesai.',
            'data' => [
                'success_count' => count($results['success']),
                'failed_count' => count($results['errors']),
                'total' => count($rows),
                'success_rows' => $results['success'],
                'errors' => $results['errors'],
            ],
        ]);
    }

    /**
     * Parse kelulusan Excel file into array of rows.
     */
    private function parseKelulusanExcel(string $filePath): array
    {
        $rows = [];
        $data = Excel::toArray(null, $filePath);
        $sheet = $data[0] ?? [];
        $headerSkipped = false;

        foreach ($sheet as $line) {
            if (!$headerSkipped) { $headerSkipped = true; continue; }
            if (empty(array_filter($line))) continue;

            $namaMapel = trim($line[4] ?? '');
            if ($namaMapel === '') continue;

            $peringkatMapel = trim($line[2] ?? '');
            $peringkatUmum = trim($line[3] ?? '');

            $rows[] = [
                'nama' => trim($line[0] ?? ''),
                'nisn' => trim($line[1] ?? ''),
                'peringkat_mapel' => $peringkatMapel !== '' ? (int) $peringkatMapel : null,
                'peringkat_umum' => $peringkatUmum !== '' ? (int) $peringkatUmum : null,
                'mapel' => $namaMapel,
                'status' => strtolower(trim($line[5] ?? '')),
            ];
        }

        return $rows;
    }
}
