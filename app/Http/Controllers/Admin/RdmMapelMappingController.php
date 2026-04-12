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
    public function index(Request $request): View
    {
        $rdmMapels = $this->getRdmMapels();

        $simansaMapels = MataPelajaran::query()
            ->where('is_active', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel', 'kelompok', 'kode_mapel']);

        $mappings = RdmMapelMapping::with(['mataPelajaran:id,nama_mapel,kelompok', 'mappedByUser:id,name'])
            ->get()
            ->keyBy('rdm_mapel_id');

        $stats = [
            'total_rdm' => $rdmMapels->count(),
            'mapped' => $mappings->count(),
            'unmapped' => $rdmMapels->count() - $mappings->count(),
            'simansa_total' => $simansaMapels->count(),
        ];

        // Auto-suggest matches based on normalizeText
        $suggestions = $this->generateSuggestions($rdmMapels, $simansaMapels, $mappings);

        return view('admin.rdm-mapel-mapping.index', compact(
            'rdmMapels',
            'simansaMapels',
            'mappings',
            'stats',
            'suggestions',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rdm_mapel_id' => ['required', 'integer'],
            'rdm_mapel_nama' => ['required', 'string', 'max:255'],
            'mata_pelajaran_id' => ['required', 'uuid', 'exists:mata_pelajaran,id'],
        ]);

        RdmMapelMapping::updateOrCreate(
            ['rdm_mapel_id' => $data['rdm_mapel_id']],
            [
                'rdm_mapel_nama' => $data['rdm_mapel_nama'],
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
        $simansaMapels = MataPelajaran::where('is_active', true)->get(['id', 'nama_mapel']);
        $existingMappings = RdmMapelMapping::pluck('rdm_mapel_id')->toArray();

        $simansaIndex = [];
        foreach ($simansaMapels as $mp) {
            $key = $this->normalizeText($mp->nama_mapel);
            if ($key !== '' && !isset($simansaIndex[$key])) {
                $simansaIndex[$key] = $mp->id;
            }
        }

        $autoMapped = 0;
        foreach ($rdmMapels as $rdm) {
            if (in_array($rdm->mapel_id, $existingMappings)) {
                continue;
            }

            $key = $this->normalizeText($rdm->mapel_nama);
            if (isset($simansaIndex[$key])) {
                RdmMapelMapping::create([
                    'rdm_mapel_id' => $rdm->mapel_id,
                    'rdm_mapel_nama' => $rdm->mapel_nama,
                    'mata_pelajaran_id' => $simansaIndex[$key],
                    'mapped_by' => Auth::id(),
                ]);
                $autoMapped++;
            }
        }

        return redirect()
            ->route('admin.rdm-mapel-mapping.index')
            ->with('success', "Auto-mapping selesai: {$autoMapped} mapel berhasil dipetakan otomatis.");
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mappings' => ['required', 'array', 'min:1'],
            'mappings.*.rdm_mapel_id' => ['required', 'integer'],
            'mappings.*.rdm_mapel_nama' => ['required', 'string', 'max:255'],
            'mappings.*.mata_pelajaran_id' => ['required', 'uuid', 'exists:mata_pelajaran,id'],
        ]);

        $saved = 0;
        DB::transaction(function () use ($data, &$saved) {
            foreach ($data['mappings'] as $mapping) {
                RdmMapelMapping::updateOrCreate(
                    ['rdm_mapel_id' => $mapping['rdm_mapel_id']],
                    [
                        'rdm_mapel_nama' => $mapping['rdm_mapel_nama'],
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
            ->table('e_mapel')
            ->select('mapel_id', 'mapel_nama')
            ->orderBy('mapel_nama')
            ->get()
            ->filter(fn ($item) => !str_starts_with($item->mapel_nama, 'Kelompok'))
            ->values();
    }

    private function generateSuggestions($rdmMapels, $simansaMapels, $existingMappings): array
    {
        $simansaIndex = [];
        foreach ($simansaMapels as $mp) {
            $key = $this->normalizeText($mp->nama_mapel);
            if ($key !== '') {
                $simansaIndex[$key] = $mp;
            }
        }

        $suggestions = [];
        foreach ($rdmMapels as $rdm) {
            if ($existingMappings->has($rdm->mapel_id)) {
                continue;
            }

            $key = $this->normalizeText($rdm->mapel_nama);
            if (isset($simansaIndex[$key])) {
                $suggestions[$rdm->mapel_id] = [
                    'simansa_id' => $simansaIndex[$key]->id,
                    'simansa_nama' => $simansaIndex[$key]->nama_mapel,
                    'confidence' => 'exact',
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
