<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\RdmMapelMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RdmMapelMappingController extends Controller
{
    /**
     * RDM kurikulum_id to SIMANSA kurikulum kode mapping.
     */
    private const RDM_KURIKULUM_MAP = [
        1 => 'K13',
        2 => 'MERDEKA',
    ];

    public function index(Request $request): View
    {
        $rdmMapels = $this->getRdmMapels();

        $simansaMapels = MataPelajaran::query()
            ->where('is_active', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel', 'kelompok', 'kode_mapel', 'kurikulum_id']);

        // Preload kurikulum names for display
        $kurikulumMap = \App\Models\Kurikulum::pluck('kode', 'id');

        $mappings = RdmMapelMapping::with(['mataPelajaran:id,nama_mapel,kelompok,kurikulum_id', 'mappedByUser:id,name'])
            ->get()
            ->keyBy('rdm_mapel_id');

        $stats = [
            'total_rdm' => $rdmMapels->count(),
            'mapped' => $mappings->count(),
            'unmapped' => $rdmMapels->count() - $mappings->count(),
            'simansa_total' => $simansaMapels->count(),
        ];

        // Auto-suggest matches based on normalizeText + kurikulum
        $suggestions = $this->generateSuggestions($rdmMapels, $simansaMapels, $mappings, $kurikulumMap);

        return view('admin.rdm-mapel-mapping.index', compact(
            'rdmMapels',
            'simansaMapels',
            'mappings',
            'stats',
            'suggestions',
            'kurikulumMap',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rdm_mapel_id' => ['required', 'integer'],
            'rdm_mapel_nama' => ['required', 'string', 'max:255'],
            'rdm_kurikulum_id' => ['nullable', 'integer'],
            'mata_pelajaran_id' => ['required', 'uuid', 'exists:mata_pelajaran,id'],
        ]);

        RdmMapelMapping::updateOrCreate(
            ['rdm_mapel_id' => $data['rdm_mapel_id']],
            [
                'rdm_mapel_nama' => $data['rdm_mapel_nama'],
                'rdm_kurikulum_id' => $data['rdm_kurikulum_id'] ?? null,
                'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                'mapped_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('admin.rdm-mapel-mapping.index')
            ->with('success', "Mapping \"{$data['rdm_mapel_nama']}\" berhasil disimpan.");
    }

    public function destroy(RdmMapelMapping $mapping): RedirectResponse
    {
        $nama = $mapping->rdm_mapel_nama;
        $mapping->delete();

        return redirect()
            ->route('admin.rdm-mapel-mapping.index')
            ->with('success', "Mapping \"{$nama}\" berhasil dihapus.");
    }

    public function autoMap(Request $request): RedirectResponse
    {
        $rdmMapels = $this->getRdmMapels();
        $simansaMapels = MataPelajaran::where('is_active', true)
            ->with('kurikulum:id,kode')
            ->get(['id', 'nama_mapel', 'kurikulum_id']);
        $existingMappings = RdmMapelMapping::pluck('rdm_mapel_id')->toArray();

        // Build kurikulum-scoped indexes
        $kurikulumIndex = [];
        $genericIndex = [];
        foreach ($simansaMapels as $mp) {
            $key = $this->normalizeText($mp->nama_mapel);
            if ($key === '') continue;

            $kode = strtoupper($mp->kurikulum?->kode ?? 'UNKNOWN');
            if (!isset($kurikulumIndex[$kode][$key])) {
                $kurikulumIndex[$kode][$key] = $mp->id;
            }
            if (!isset($genericIndex[$key])) {
                $genericIndex[$key] = $mp->id;
            }
        }

        $autoMapped = 0;
        foreach ($rdmMapels as $rdm) {
            if (in_array($rdm->mapel_id, $existingMappings)) {
                continue;
            }

            $key = $this->normalizeText($rdm->mapel_nama);
            $rdmKurikulumKode = self::RDM_KURIKULUM_MAP[$rdm->kurikulum_id ?? 0] ?? null;

            // Try kurikulum-scoped match first, then generic
            $simansaId = null;
            if ($rdmKurikulumKode && isset($kurikulumIndex[$rdmKurikulumKode][$key])) {
                $simansaId = $kurikulumIndex[$rdmKurikulumKode][$key];
            } elseif (isset($genericIndex[$key])) {
                $simansaId = $genericIndex[$key];
            }

            if ($simansaId) {
                RdmMapelMapping::create([
                    'rdm_mapel_id' => $rdm->mapel_id,
                    'rdm_mapel_nama' => $rdm->mapel_nama,
                    'rdm_kurikulum_id' => $rdm->kurikulum_id ?? null,
                    'mata_pelajaran_id' => $simansaId,
                    'mapped_by' => Auth::id(),
                ]);
                $autoMapped++;
            }
        }

        return redirect()
            ->route('admin.rdm-mapel-mapping.index')
            ->with('success', "Auto-mapping selesai: {$autoMapped} mapel berhasil dipetakan otomatis (kurikulum-aware).");
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mappings' => ['required', 'array', 'min:1'],
            'mappings.*.rdm_mapel_id' => ['required', 'integer'],
            'mappings.*.rdm_mapel_nama' => ['required', 'string', 'max:255'],
            'mappings.*.rdm_kurikulum_id' => ['nullable', 'integer'],
            'mappings.*.mata_pelajaran_id' => ['required', 'uuid', 'exists:mata_pelajaran,id'],
        ]);

        $saved = 0;
        DB::transaction(function () use ($data, &$saved) {
            foreach ($data['mappings'] as $mapping) {
                RdmMapelMapping::updateOrCreate(
                    ['rdm_mapel_id' => $mapping['rdm_mapel_id']],
                    [
                        'rdm_mapel_nama' => $mapping['rdm_mapel_nama'],
                        'rdm_kurikulum_id' => $mapping['rdm_kurikulum_id'] ?? null,
                        'mata_pelajaran_id' => $mapping['mata_pelajaran_id'],
                        'mapped_by' => Auth::id(),
                    ]
                );
                $saved++;
            }
        });

        return redirect()
            ->route('admin.rdm-mapel-mapping.index')
            ->with('success', "Bulk mapping selesai: {$saved} mapel berhasil disimpan.");
    }

    private function getRdmMapels()
    {
        return DB::connection('mysql_rdm')
            ->table('e_mapel as m')
            ->leftJoin('e_kurikulum as k', 'k.kurikulum_id', '=', 'm.kurikulum_id')
            ->select('m.mapel_id', 'm.mapel_nama', 'm.kurikulum_id', 'k.kurikulum_nama')
            ->orderBy('m.kurikulum_id')
            ->orderBy('m.mapel_nama')
            ->get()
            ->filter(fn ($item) => !str_starts_with($item->mapel_nama, 'Kelompok'))
            ->values();
    }

    private function generateSuggestions($rdmMapels, $simansaMapels, $existingMappings, $kurikulumMap = null): array
    {
        // Build kurikulum-scoped indexes
        $kurikulumIndex = [];
        $genericIndex = [];
        foreach ($simansaMapels as $mp) {
            $key = $this->normalizeText($mp->nama_mapel);
            if ($key === '') continue;

            $kode = $kurikulumMap ? strtoupper($kurikulumMap[$mp->kurikulum_id] ?? 'UNKNOWN') : 'UNKNOWN';
            if (!isset($kurikulumIndex[$kode][$key])) {
                $kurikulumIndex[$kode][$key] = $mp;
            }
            if (!isset($genericIndex[$key])) {
                $genericIndex[$key] = $mp;
            }
        }

        $suggestions = [];
        foreach ($rdmMapels as $rdm) {
            if ($existingMappings->has($rdm->mapel_id)) {
                continue;
            }

            $key = $this->normalizeText($rdm->mapel_nama);
            $rdmKurikulumKode = self::RDM_KURIKULUM_MAP[$rdm->kurikulum_id ?? 0] ?? null;

            // Try kurikulum-scoped match first
            $match = null;
            $confidence = 'generic';
            if ($rdmKurikulumKode && isset($kurikulumIndex[$rdmKurikulumKode][$key])) {
                $match = $kurikulumIndex[$rdmKurikulumKode][$key];
                $confidence = 'exact';
            } elseif (isset($genericIndex[$key])) {
                $match = $genericIndex[$key];
                $confidence = 'name_only';
            }

            if ($match) {
                $suggestions[$rdm->mapel_id] = [
                    'simansa_id' => $match->id,
                    'simansa_nama' => $match->nama_mapel,
                    'confidence' => $confidence,
                ];
            }
        }

        return $suggestions;
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = str_replace(['`', "'", "\u{2019}"], '', $text);
        $text = preg_replace('/[^a-z0-9]+/i', ' ', $text);
        return trim((string) $text);
    }
}
