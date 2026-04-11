<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartqPeriode;
use App\Models\SmartqKomponenNilai;
use App\Models\SmartqPeserta;
use App\Models\SmartqNilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

    public function moodleCourses(Request $request, SmartqPeriode $smartq)
    {
        $baseUrl = $smartq->moodle_base_url ?? $request->input('moodle_base_url');
        if (!$baseUrl) {
            return response()->json(['success' => false, 'error' => 'URL Moodle belum dikonfigurasi']);
        }

        try {
            $response = Http::timeout(15)
                ->get(rtrim($baseUrl, '/') . '/converter/smartq/?action=quizzes');

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
            'moodle_course_id' => 'required|integer',
            'moodle_quiz_id' => 'required|integer',
            'moodle_quiz_name' => 'required|string',
        ]);

        $smartq->update($request->only(['moodle_course_id', 'moodle_quiz_id', 'moodle_quiz_name']));

        return response()->json(['success' => true, 'message' => 'Konfigurasi Moodle disimpan']);
    }

    public function syncMoodle(SmartqPeriode $smartq)
    {
        if (!$smartq->moodle_base_url || !$smartq->moodle_quiz_id) {
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Konfigurasi Moodle belum lengkap. Set URL dan Quiz ID terlebih dahulu.');
        }

        // Get all peserta with their siswa NISN (used as Moodle username)
        $pesertas = $smartq->pesertas()->with('siswa')->get();
        $nisns = $pesertas->pluck('siswa.nisn')->filter()->implode(',');

        try {
            $url = rtrim($smartq->moodle_base_url, '/') . '/converter/smartq/';
            $response = Http::timeout(30)->get($url, [
                'action' => 'scores',
                'quiz' => $smartq->moodle_quiz_id,
                'usernames' => $nisns,
            ]);

            if (!$response->successful()) {
                return redirect()->route('admin.smartq.show', $smartq)
                    ->with('error', 'Gagal mengakses Moodle API: HTTP ' . $response->status());
            }

            $result = $response->json();
            if (!($result['success'] ?? false)) {
                return redirect()->route('admin.smartq.show', $smartq)
                    ->with('error', 'Moodle API error: ' . ($result['error'] ?? 'Unknown'));
            }

            // Find the CBT komponen (sumber = moodle)
            $komponenCbt = $smartq->komponenNilais()->where('sumber', 'moodle')->first();
            if (!$komponenCbt) {
                return redirect()->route('admin.smartq.show', $smartq)
                    ->with('error', 'Tidak ada komponen nilai dengan sumber "moodle".');
            }

            $scores = collect($result['data'] ?? []);
            $matched = 0;
            $notFound = 0;

            DB::transaction(function () use ($pesertas, $scores, $komponenCbt, &$matched, &$notFound) {
                foreach ($pesertas as $peserta) {
                    $nisn = $peserta->siswa->nisn ?? '';
                    $scoreData = $scores->firstWhere('username', $nisn);

                    if (!$scoreData) {
                        $notFound++;
                        continue;
                    }

                    $nilaiRaw = $scoreData['normalized_100'];
                    $nilaiKonversi = round(($nilaiRaw / $komponenCbt->nilai_maksimal) * 100, 2);

                    SmartqNilai::updateOrCreate(
                        [
                            'smartq_peserta_id' => $peserta->id,
                            'smartq_komponen_nilai_id' => $komponenCbt->id,
                        ],
                        [
                            'nilai' => $nilaiRaw,
                            'nilai_konversi' => min(100, $nilaiKonversi),
                            'moodle_attempt_id' => $scoreData['attempt_id'],
                            'moodle_username' => $scoreData['username'],
                            'dinilai_oleh' => Auth::id(),
                            'dinilai_pada' => now(),
                            'catatan' => "Sync Moodle: Quiz \"{$scoreData['fullname']}\" - Raw: {$scoreData['raw_score']}/{$scoreData['max_score']}",
                        ]
                    );

                    $matched++;
                }
            });

            // Recalculate ranking
            $smartq->hitungRanking();

            $msg = "Sync Moodle selesai: {$matched} siswa ter-match";
            if ($notFound > 0) {
                $msg .= ", {$notFound} siswa tidak ditemukan di Moodle (NISN tidak cocok)";
            }

            return redirect()->route('admin.smartq.show', $smartq)->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('SMART-Q Moodle sync error', ['error' => $e->getMessage()]);
            return redirect()->route('admin.smartq.show', $smartq)
                ->with('error', 'Error saat sync: ' . $e->getMessage());
        }
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
}
