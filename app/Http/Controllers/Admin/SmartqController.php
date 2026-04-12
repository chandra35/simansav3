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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $pesertas = SmartqPeserta::with(['siswa', 'kelasAsal', 'nilais.komponenNilai'])
            ->where('smartq_periode_id', $smartq->id)
            ->orderBy('ranking')
            ->orderByDesc('total_nilai')
            ->get();

        $stats = [
            'total' => $pesertas->count(),
            'lulus' => $pesertas->where('status', 'lulus')->count(),
            'tidak_lulus' => $pesertas->where('status', 'tidak_lulus')->count(),
            'terdaftar' => $pesertas->where('status', 'terdaftar')->count(),
            'rata_rata' => $pesertas->avg('total_nilai') ?? 0,
            'tertinggi' => $pesertas->max('total_nilai') ?? 0,
            'terendah' => $pesertas->where('total_nilai', '>', 0)->min('total_nilai') ?? 0,
        ];

        return view('admin.smartq.show', compact('smartq', 'pesertas', 'stats'));
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

                    if (isset($moodleUsersMap[$username])) {
                        $moodleUsersMap[$username]['scores'][] = [
                            'quiz_id' => $qc['quiz_id'],
                            'quiz_name' => $qc['quiz_name'] ?? '',
                            'normalized_100' => $score['normalized_100'],
                            'attempt_id' => $score['attempt_id'],
                            'raw_score' => $score['raw_score'],
                            'max_score' => $score['max_score'],
                        ];
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
                            'scores' => [[
                                'quiz_id' => $qc['quiz_id'],
                                'quiz_name' => $qc['quiz_name'] ?? '',
                                'normalized_100' => $score['normalized_100'],
                                'attempt_id' => $score['attempt_id'],
                                'raw_score' => $score['raw_score'],
                                'max_score' => $score['max_score'],
                            ]],
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

    public function prosesKelulusan(Request $request, SmartqPeriode $smartq)
    {
        $request->validate([
            'metode' => 'required|in:kuota,passing_grade',
            'passing_grade' => 'required_if:metode,passing_grade|nullable|numeric|min:0|max:100',
        ]);

        // Ensure ranking is up-to-date
        $smartq->hitungRanking();

        $pesertas = $smartq->pesertas()
            ->where('status', '!=', 'mengundurkan_diri')
            ->orderBy('ranking')
            ->get();

        DB::transaction(function () use ($request, $pesertas, $smartq) {
            foreach ($pesertas as $peserta) {
                if ($request->metode === 'kuota') {
                    $peserta->status = $peserta->ranking <= $smartq->kuota ? 'lulus' : 'tidak_lulus';
                } else {
                    $peserta->status = $peserta->total_nilai >= $request->passing_grade ? 'lulus' : 'tidak_lulus';
                }
                $peserta->save();
            }
        });

        $lulus = $pesertas->where('status', 'lulus')->count();
        return redirect()->route('admin.smartq.show', $smartq)
            ->with('success', "Proses kelulusan selesai. {$lulus} siswa dinyatakan LULUS.");
    }

    // ==================== EXPORT ====================

    public function exportExcel(SmartqPeriode $smartq)
    {
        $smartq->load('komponenNilais');
        $pesertas = SmartqPeserta::with(['siswa', 'kelasAsal', 'nilais.komponenNilai'])
            ->where('smartq_periode_id', $smartq->id)
            ->orderBy('ranking')
            ->get();

        $filename = 'SMARTQ_' . str_replace(' ', '_', $smartq->nama) . '_' . date('Ymd') . '.csv';

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
        $rows = Cache::get($cacheKey);

        if (!$rows) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Data scan sudah expired. Silakan scan ulang.');
        }

        // Collect all unique quizzes
        $allMapel = collect($rows)->flatMap(fn($r) => $r['scores'] ?? [])->unique('quiz_id')->sortBy('quiz_name')->values();
        $totalStudents = count($rows);

        // Wajib vs Pilihan detection
        $mapelStats = [];
        foreach ($allMapel as $m) {
            $qid = $m['quiz_id'];
            $scores = collect($rows)->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $qid);
            $nonZero = $scores->where('normalized_100', '>', 0);
            $mapelStats[$qid] = [
                'name' => $m['quiz_name'],
                'total_attempts' => $nonZero->count(),
                'avg' => $nonZero->count() > 0 ? round($nonZero->avg('normalized_100'), 1) : 0,
                'max' => $nonZero->count() > 0 ? round($nonZero->max('normalized_100'), 1) : 0,
                'min' => $nonZero->count() > 0 ? round($nonZero->min('normalized_100'), 1) : 0,
            ];
        }
        $mapelWajib = collect($mapelStats)->filter(fn($s) => $s['total_attempts'] > ($totalStudents * 0.5))->keys()->toArray();

        if ($format === 'pdf') {
            return $this->exportScanPdf($rows, $allMapel, $mapelStats, $mapelWajib, $smartq);
        }

        // Excel (CSV with UTF-8 BOM)
        $filename = 'SMARTQ_Scan_' . str_replace(' ', '_', $smartq->nama) . '_' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $allMapel, $mapelWajib, $mapelStats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            $header = ['No', 'Nama Siswa', 'NISN', 'Kelas', 'Status', 'Match'];
            foreach ($allMapel as $m) {
                $label = $m['quiz_name'];
                if (!in_array($m['quiz_id'], $mapelWajib)) $label .= ' (Pilihan)';
                $header[] = $label;
            }
            if ($allMapel->count() > 1) $header[] = 'Rata-rata';
            $header[] = 'Keterangan';
            fputcsv($file, $header);

            foreach ($rows as $i => $row) {
                $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                $statusLabel = match($row['status']) {
                    'ready' => 'Siap Import',
                    'ready_no_score' => 'Tanpa Nilai',
                    'already_registered' => 'Sudah Terdaftar',
                    'no_match' => 'Tidak Ada di SIMANSA',
                    default => '-',
                };

                $r = [
                    $i + 1,
                    $row['moodle_firstname'] ?: $row['moodle_fullname'],
                    $row['moodle_username'],
                    $row['siswa_kelas'] ?? ($row['moodle_lastname'] ?? '-'),
                    $statusLabel,
                    $row['match_method'] ?? '-',
                ];

                $ket = [];
                foreach ($allMapel as $m) {
                    $score = $rowScores->get($m['quiz_id']);
                    if ($score) {
                        $r[] = $score['normalized_100'];
                    } else {
                        $r[] = '-';
                        if (in_array($m['quiz_id'], $mapelWajib)) {
                            $ket[] = $m['quiz_name'] . ': Belum mengerjakan';
                        }
                    }
                }

                if ($allMapel->count() > 1) {
                    $r[] = $row['has_attempt'] ? $row['normalized_100'] : '-';
                }
                $r[] = implode('; ', $ket);
                fputcsv($file, $r);
            }

            // Summary section
            fputcsv($file, []);
            fputcsv($file, ['=== RINGKASAN PER MAPEL ===']);
            fputcsv($file, ['Mapel', 'Tipe', 'Peserta', 'Rata-rata', 'Tertinggi', 'Terendah']);
            foreach ($mapelStats as $qid => $s) {
                fputcsv($file, [
                    $s['name'],
                    in_array($qid, $mapelWajib) ? 'Wajib' : 'Pilihan',
                    $s['total_attempts'],
                    $s['avg'],
                    $s['max'],
                    $s['min'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportScanPdf($rows, $allMapel, $mapelStats, $mapelWajib, $smartq)
    {
        $totalStudents = count($rows);
        $html = '<style>
            body { font-family: sans-serif; font-size: 10px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
            th, td { border: 1px solid #333; padding: 3px 5px; }
            th { background: #333; color: #fff; text-align: center; }
            .badge-s { background: #28a745; color:#fff; padding: 1px 5px; border-radius: 3px; }
            .badge-p { background: #007bff; color:#fff; padding: 1px 5px; border-radius: 3px; }
            .badge-w { background: #ffc107; color:#000; padding: 1px 5px; border-radius: 3px; }
            .badge-d { background: #dc3545; color:#fff; padding: 1px 5px; border-radius: 3px; }
            .text-center { text-align: center; }
            h2, h3 { margin: 5px 0; }
        </style>';
        $html .= '<h2>Laporan Scan Moodle - ' . e($smartq->nama) . '</h2>';
        $html .= '<p>Tanggal: ' . now()->format('d/m/Y H:i') . ' | Total: ' . $totalStudents . ' siswa</p>';

        // Summary table
        $html .= '<h3>Ringkasan per Mapel</h3><table><tr><th>Mapel</th><th>Tipe</th><th>Peserta</th><th>Rata-rata</th><th>Tertinggi</th><th>Terendah</th></tr>';
        foreach ($mapelStats as $qid => $s) {
            $html .= '<tr><td>' . e($s['name']) . '</td><td class="text-center">' . (in_array($qid, $mapelWajib) ? 'Wajib' : 'Pilihan') . '</td>';
            $html .= '<td class="text-center">' . $s['total_attempts'] . '/' . $totalStudents . '</td>';
            $html .= '<td class="text-center">' . $s['avg'] . '</td>';
            $html .= '<td class="text-center">' . $s['max'] . '</td>';
            $html .= '<td class="text-center">' . $s['min'] . '</td></tr>';
        }
        $html .= '</table>';

        // Score table
        $html .= '<h3>Rekap Nilai Seluruh Siswa</h3><table><tr><th>#</th><th>Nama</th><th>NISN</th><th>Kelas</th>';
        foreach ($allMapel as $m) {
            $html .= '<th style="font-size:8px">' . e($m['quiz_name']) . '</th>';
        }
        if ($allMapel->count() > 1) $html .= '<th>Rata²</th>';
        $html .= '<th>Status</th></tr>';

        foreach ($rows as $i => $row) {
            $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
            $statusLabel = match($row['status']) {
                'ready' => 'Siap', 'ready_no_score' => 'Tanpa Nilai',
                'already_registered' => 'Terdaftar', 'no_match' => 'Tidak Ada', default => '-',
            };
            $html .= '<tr><td class="text-center">' . ($i + 1) . '</td>';
            $html .= '<td>' . e($row['moodle_firstname'] ?: $row['moodle_fullname']) . '</td>';
            $html .= '<td>' . e($row['moodle_username']) . '</td>';
            $html .= '<td>' . e($row['siswa_kelas'] ?? ($row['moodle_lastname'] ?? '-')) . '</td>';
            foreach ($allMapel as $m) {
                $score = $rowScores->get($m['quiz_id']);
                if ($score) {
                    $v = $score['normalized_100'];
                    $cls = $v >= 80 ? 'badge-s' : ($v >= 60 ? 'badge-p' : ($v >= 40 ? 'badge-w' : 'badge-d'));
                    $html .= '<td class="text-center"><span class="' . $cls . '">' . $v . '</span></td>';
                } else {
                    $html .= '<td class="text-center">-</td>';
                }
            }
            if ($allMapel->count() > 1) {
                $html .= '<td class="text-center">' . ($row['has_attempt'] ? $row['normalized_100'] : '-') . '</td>';
            }
            $html .= '<td class="text-center">' . $statusLabel . '</td></tr>';
        }
        $html .= '</table>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'sans-serif']);

        $filename = 'SMARTQ_Scan_' . str_replace(' ', '_', $smartq->nama) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}
