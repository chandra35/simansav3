<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaMapel;
use App\Models\AsramaNilai;
use App\Models\AsramaPengampu;
use App\Models\AsramaRapor;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyafahiController extends Controller
{
    public const GROUPS = [
        'arab' => [
            'key' => 'arab',
            'label' => 'Bahasa Arab',
            'label_arab' => 'اللغة العربية',
            'icon' => 'fas fa-language',
            'color' => 'primary',
            'mapel_codes' => ['ASR-006', 'ASR-002', 'ASR-003'],
            'aspects' => [
                'ASR-006' => [
                    'kode' => 'ASR-006',
                    'nama_latin' => 'Mufrodat',
                    'nama_arab' => 'المفردات',
                    'sub' => 'Kosa Kata & Makna',
                ],
                'ASR-002' => [
                    'kode' => 'ASR-002',
                    'nama_latin' => 'Muhadatsah',
                    'nama_arab' => 'المحادثة',
                    'sub' => 'Percakapan & Kelancaran',
                ],
                'ASR-003' => [
                    'kode' => 'ASR-003',
                    'nama_latin' => 'B. Arab Syafahi',
                    'nama_arab' => 'اللّغة العربيّة الشفاهية',
                    'sub' => 'Qawaid & Pemahaman Lisan',
                ],
            ],
        ],
        'inggris' => [
            'key' => 'inggris',
            'label' => 'Bahasa Inggris',
            'label_arab' => 'English Language',
            'icon' => 'fas fa-comments',
            'color' => 'info',
            'mapel_codes' => ['ASR-019', 'ASR-018', 'ASR-020'],
            'aspects' => [
                'ASR-019' => [
                    'kode' => 'ASR-019',
                    'nama_latin' => 'Oral Examination',
                    'nama_arab' => 'Oral Examination',
                    'sub' => 'Comprehension & Response',
                ],
                'ASR-018' => [
                    'kode' => 'ASR-018',
                    'nama_latin' => 'Conversation',
                    'nama_arab' => 'Conversation',
                    'sub' => 'Fluency & Pronunciation',
                ],
                'ASR-020' => [
                    'kode' => 'ASR-020',
                    'nama_latin' => 'Vocabularies',
                    'nama_arab' => 'Vocabularies',
                    'sub' => 'Vocabulary & Grammar',
                ],
            ],
        ],
        'fiqih' => [
            'key' => 'fiqih',
            'label' => "Fiqh & Al-Qur'an",
            'label_arab' => 'الفقه والقرآن',
            'icon' => 'fas fa-quran',
            'color' => 'success',
            'mapel_codes' => ['ASR-015', 'ASR-022', 'ASR-023'],
            'aspects' => [
                'ASR-015' => [
                    'kode' => 'ASR-015',
                    'nama_latin' => 'Fiqih Syafahi',
                    'nama_arab' => 'الفقه الشفاهي',
                    'sub' => 'Praktek Ibadah & Fiqh',
                ],
                'ASR-022' => [
                    'kode' => 'ASR-022',
                    'nama_latin' => "Tahfidzul Qur'an",
                    'nama_arab' => 'تحفيظ القرآن',
                    'sub' => 'Hafalan & Kelancaran',
                ],
                'ASR-023' => [
                    'kode' => 'ASR-023',
                    'nama_latin' => "Tahsin / Tarjamah",
                    'nama_arab' => 'تحسين القرآن / الترجمة',
                    'sub' => 'Tajwid & Terjemah Ayat',
                ],
            ],
        ],
    ];

    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $activeYear = TahunPelajaran::active()->first();
        $defaultYearId = $activeYear?->id ?: TahunPelajaran::orderByDesc('tahun_mulai')->value('id');
        $tahunId = $request->input('tahun_pelajaran_id', $defaultYearId);

        $classQuery = AsramaKelas::with(['asrama', 'tahunPelajaran'])->where('is_active', true);
        if ($tahunId) {
            $classQuery->where('tahun_pelajaran_id', $tahunId);
        }

        if (! $request->user()->can('manage-asrama') && ! $request->user()->can('manage-asrama-pengampu')) {
            $asatidzIds = AsramaAsatidz::where('gtk_id', $request->user()->gtk?->id)
                ->where('is_active', true)->pluck('id');
            $classQuery->where(function ($q) use ($asatidzIds) {
                $q->whereHas('pengasuhRombel', fn ($sub) => $sub->whereIn('asrama_asatidz_id', $asatidzIds))
                  ->orWhereHas('pengampu', fn ($sub) => $sub->whereIn('asrama_asatidz_id', $asatidzIds));
            });
        }

        $classes = $classQuery->orderBy('nama_kelas')->get();
        $kelasId = $request->input('asrama_kelas_id') ?: $classes->first()?->id;
        $selectedKelas = $classes->firstWhere('id', $kelasId);

        $semester = in_array($request->input('semester'), ['Ganjil', 'Genap'], true)
            ? $request->input('semester')
            : ($selectedKelas?->tahunPelajaran?->semester_aktif ?? 'Ganjil');

        $kelompok = $request->input('kelompok', 'arab');
        if (! array_key_exists($kelompok, self::GROUPS) && $kelompok !== 'all') {
            $kelompok = 'arab';
        }

        $members = $selectedKelas
            ? AsramaKelasSantri::with(['santri.siswa'])
                ->where('asrama_kelas_id', $selectedKelas->id)
                ->where('status', 'aktif')
                ->get()
                ->sortBy(fn ($m) => $m->santri?->siswa?->nama_lengkap ?? '')
            : collect();

        // Get Aspect Codes
        $aspectCodes = $this->resolveAspectCodes($kelompok);
        $mapels = AsramaMapel::whereIn('kode', $aspectCodes)->get()->keyBy('kode');

        // Check or find/create pengampu for each aspect
        $pengampuList = [];
        if ($selectedKelas) {
            $pengampuList = $this->ensurePengampuForAspects($selectedKelas, $aspectCodes, $semester, $request->user());
        }

        // Load existing scores
        $scores = [];
        if (! empty($pengampuList) && $members->isNotEmpty()) {
            $pengampuIds = collect($pengampuList)->pluck('id')->filter();
            $nilaiRecords = AsramaNilai::whereIn('asrama_pengampu_id', $pengampuIds)
                ->whereIn('asrama_kelas_santri_id', $members->pluck('id'))
                ->get();

            foreach ($nilaiRecords as $rec) {
                $scores[$rec->asrama_kelas_santri_id][$rec->asrama_pengampu_id] = $rec;
            }
        }

        // Check if report card is published
        $isPublished = false;
        if ($selectedKelas && $members->isNotEmpty()) {
            $isPublished = AsramaRapor::whereIn('asrama_kelas_santri_id', $members->pluck('id'))
                ->where('semester', $semester)
                ->where('status', 'terbit')
                ->exists();
        }

        $years = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $groups = self::GROUPS;

        return view('asrama.syafahi.index', compact(
            'years', 'tahunId', 'activeYear', 'classes', 'selectedKelas',
            'semester', 'kelompok', 'groups', 'members', 'aspectCodes',
            'mapels', 'pengampuList', 'scores', 'isPublished'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'asrama_kelas_id' => ['required', 'uuid', 'exists:asrama_kelas,id'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'kelompok' => ['required', 'string'],
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['nullable', 'array'],
        ]);

        $kelas = AsramaKelas::findOrFail($data['asrama_kelas_id']);
        $aspectCodes = $this->resolveAspectCodes($data['kelompok']);
        $pengampuList = $this->ensurePengampuForAspects($kelas, $aspectCodes, $data['semester'], $request->user());

        $memberIds = $kelas->anggotaAktif()->pluck('id');
        $publishedMemberIds = AsramaRapor::whereIn('asrama_kelas_santri_id', $memberIds)
            ->where('semester', $data['semester'])
            ->where('status', 'terbit')
            ->pluck('asrama_kelas_santri_id')
            ->toArray();

        DB::transaction(function () use ($data, $pengampuList, $publishedMemberIds, $request) {
            foreach ($data['nilai'] ?? [] as $memberId => $aspectScores) {
                if (in_array($memberId, $publishedMemberIds, true)) {
                    continue; // Skip published student
                }

                foreach ($aspectScores as $mapelKode => $scoreVal) {
                    $pengampu = $pengampuList[$mapelKode] ?? null;
                    if (! $pengampu) {
                        continue;
                    }

                    $scoreNumeric = ($scoreVal !== null && $scoreVal !== '') ? (float) str_replace(',', '.', $scoreVal) : null;

                    if ($scoreNumeric !== null) {
                        $skala = (float) ($pengampu->mapel->skala_maksimum ?? 10);
                        if ($scoreNumeric > $skala && $skala == 10 && $scoreNumeric <= 100) {
                            $scoreNumeric = round($scoreNumeric / 10, 2);
                        }
                    }

                    AsramaNilai::updateOrCreate(
                        [
                            'asrama_pengampu_id' => $pengampu->id,
                            'asrama_kelas_santri_id' => $memberId,
                        ],
                        [
                            'nilai' => $scoreNumeric,
                            'input_by' => $request->user()->id,
                            'input_at' => now(),
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Nilai ujian syafahi berhasil disimpan.');
    }

    public function quickSave(Request $request)
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'asrama_kelas_id' => ['required', 'uuid', 'exists:asrama_kelas,id'],
            'asrama_kelas_santri_id' => ['required', 'uuid', 'exists:asrama_kelas_santri,id'],
            'kode_mapel' => ['required', 'string'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'nilai' => ['nullable'],
        ]);

        $kelas = AsramaKelas::findOrFail($data['asrama_kelas_id']);
        $isPublished = AsramaRapor::where('asrama_kelas_santri_id', $data['asrama_kelas_santri_id'])
            ->where('semester', $data['semester'])
            ->where('status', 'terbit')
            ->exists();

        if ($isPublished) {
            return response()->json(['success' => false, 'message' => 'Rapor santri ini sudah terbit.'], 422);
        }

        $pengampuList = $this->ensurePengampuForAspects($kelas, [$data['kode_mapel']], $data['semester'], $request->user());
        $pengampu = $pengampuList[$data['kode_mapel']] ?? null;

        if (! $pengampu) {
            return response()->json(['success' => false, 'message' => 'Mapel tidak ditemukan.'], 404);
        }

        $rawVal = $data['nilai'];
        $scoreNumeric = ($rawVal !== null && $rawVal !== '') ? (float) str_replace(',', '.', $rawVal) : null;

        if ($scoreNumeric !== null) {
            $skala = (float) ($pengampu->mapel->skala_maksimum ?? 10);
            if ($scoreNumeric > $skala && $skala == 10 && $scoreNumeric <= 100) {
                $scoreNumeric = round($scoreNumeric / 10, 2);
            }
        }

        $nilaiRecord = AsramaNilai::updateOrCreate(
            [
                'asrama_pengampu_id' => $pengampu->id,
                'asrama_kelas_santri_id' => $data['asrama_kelas_santri_id'],
            ],
            [
                'nilai' => $scoreNumeric,
                'input_by' => $request->user()->id,
                'input_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Tersimpan',
            'nilai' => $scoreNumeric,
            'nilai_formatted' => $scoreNumeric !== null ? str_replace('.', ',', (string) $scoreNumeric) : '',
        ]);
    }

    public function blanko(Request $request)
    {
        $this->authorizeAccess($request);

        $kelasId = $request->input('asrama_kelas_id');
        $kelas = AsramaKelas::with(['asrama', 'tahunPelajaran'])->findOrFail($kelasId);

        $semester = in_array($request->input('semester'), ['Ganjil', 'Genap'], true)
            ? $request->input('semester')
            : ($kelas->tahunPelajaran?->semester_aktif ?? 'Ganjil');

        $kelompok = $request->input('kelompok', 'arab');
        if (! array_key_exists($kelompok, self::GROUPS) && $kelompok !== 'all') {
            $kelompok = 'arab';
        }

        $mode = $request->input('mode', 'empty'); // 'empty' or 'filled'

        $members = AsramaKelasSantri::with(['santri.siswa'])
            ->where('asrama_kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(fn ($m) => $m->santri?->siswa?->nama_lengkap ?? '');

        $aspectCodes = $this->resolveAspectCodes($kelompok);
        $mapels = AsramaMapel::whereIn('kode', $aspectCodes)->get()->keyBy('kode');

        $pengampuList = $this->ensurePengampuForAspects($kelas, $aspectCodes, $semester, $request->user());

        $scores = [];
        if ($mode === 'filled') {
            $pengampuIds = collect($pengampuList)->pluck('id')->filter();
            $nilaiRecords = AsramaNilai::whereIn('asrama_pengampu_id', $pengampuIds)
                ->whereIn('asrama_kelas_santri_id', $members->pluck('id'))
                ->get();

            foreach ($nilaiRecords as $rec) {
                $scores[$rec->asrama_kelas_santri_id][$rec->asrama_pengampu_id] = $rec;
            }
        }

        $groupMeta = self::GROUPS[$kelompok] ?? [
            'label' => 'Semua Ujian Syafahi (Lisan)',
            'label_arab' => 'جميع الاختبارات الشفاهية',
        ];

        return view('asrama.syafahi.blanko', compact(
            'kelas', 'semester', 'kelompok', 'groupMeta', 'members',
            'aspectCodes', 'mapels', 'pengampuList', 'scores', 'mode'
        ));
    }

    private function resolveAspectCodes(string $kelompok): array
    {
        if ($kelompok === 'all') {
            return array_merge(
                self::GROUPS['arab']['mapel_codes'],
                self::GROUPS['inggris']['mapel_codes'],
                self::GROUPS['fiqih']['mapel_codes']
            );
        }

        return self::GROUPS[$kelompok]['mapel_codes'] ?? self::GROUPS['arab']['mapel_codes'];
    }

    private function ensurePengampuForAspects(AsramaKelas $kelas, array $aspectCodes, string $semester, $user): array
    {
        $mapels = AsramaMapel::whereIn('kode', $aspectCodes)->get()->keyBy('kode');
        $existing = AsramaPengampu::with('mapel')
            ->where('asrama_kelas_id', $kelas->id)
            ->where('semester', $semester)
            ->whereIn('asrama_mapel_id', $mapels->pluck('id'))
            ->get()
            ->keyBy(fn ($p) => $p->mapel->kode);

        $defaultAsatidzId = null;
        if ($user->gtk) {
            $defaultAsatidzId = AsramaAsatidz::where('gtk_id', $user->gtk->id)->value('id');
        }
        if (! $defaultAsatidzId) {
            $defaultAsatidzId = $kelas->pengasuhRombel()->value('asrama_asatidz_id');
        }
        if (! $defaultAsatidzId) {
            $defaultAsatidzId = AsramaAsatidz::where('is_active', true)->value('id');
        }

        $result = [];
        foreach ($aspectCodes as $kode) {
            if ($existing->has($kode)) {
                $result[$kode] = $existing->get($kode);
                continue;
            }

            $mapel = $mapels->get($kode);
            if (! $mapel) {
                continue;
            }

            // Create assignment
            if ($defaultAsatidzId) {
                $newPengampu = AsramaPengampu::create([
                    'asrama_kelas_id' => $kelas->id,
                    'asrama_mapel_id' => $mapel->id,
                    'asrama_asatidz_id' => $defaultAsatidzId,
                    'semester' => $semester,
                    'is_active' => true,
                    'created_by' => $user->id,
                ]);
                $newPengampu->setRelation('mapel', $mapel);
                $result[$kode] = $newPengampu;
            }
        }

        return $result;
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        if ($user->can('manage-asrama') || $user->can('manage-asrama-pengampu') || $user->can('input-nilai-asrama')) {
            return;
        }

        if ($user->gtk) {
            $isAsatidz = AsramaAsatidz::where('gtk_id', $user->gtk->id)->where('is_active', true)->exists();
            if ($isAsatidz) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki hak akses penilaian ujian syafahi.');
    }
}
