<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\JadwalGuruAlias;
use App\Models\JadwalMapelAlias;
use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Services\JadwalAliasMappingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalMappingController extends Controller
{
    public function __construct(private readonly JadwalAliasMappingService $mappingService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('view-jadwal-mapping');

        $tahunList = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get();
        $tahun = $request->filled('tahun_pelajaran_id')
            ? TahunPelajaran::findOrFail($request->tahun_pelajaran_id)
            : $this->mappingService->referenceYear();

        $guruAliases = collect();
        $mapelAliases = collect();
        if ($tahun) {
            $guruAliases = JadwalGuruAlias::with(['gtk', 'verifier'])
                ->where('tahun_pelajaran_id', $tahun->id)
                ->orderByRaw('CAST(external_code AS UNSIGNED)')
                ->get();
            $mapelAliases = JadwalMapelAlias::with(['mataPelajaran', 'verifier'])
                ->where('tahun_pelajaran_id', $tahun->id)
                ->orderBy('external_code')
                ->get();
        }

        $gtkOptions = Gtk::query()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nip', 'nuptk', 'jenis_ptk', 'jabatan', 'foto_profile', 'kode_gtk'])
            ->map(fn (Gtk $gtk) => [
                'id' => $gtk->id,
                'nama' => $gtk->nama_lengkap,
                'nip' => $gtk->nip,
                'nuptk' => $gtk->nuptk,
                'jenis_ptk' => $gtk->jenis_ptk,
                'jabatan' => $gtk->jabatan,
                'foto' => $gtk->foto_profile_url,
                'kode' => $gtk->kode_gtk,
            ])->values();

        $mapelOptions = MataPelajaran::query()
            ->where('is_active', true)
            ->when($tahun?->kurikulum_id, fn ($query) => $query->where('kurikulum_id', $tahun->kurikulum_id))
            ->where(function ($query) {
                $query->whereJsonContains('tingkat', 10)
                    ->orWhereJsonContains('tingkat', 11)
                    ->orWhereJsonContains('tingkat', 12);
            })
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'kode_jadwal', 'nama_mapel', 'struktur_fase_e', 'struktur_fase_f'])
            ->map(fn (MataPelajaran $mapel) => [
                'id' => $mapel->id,
                'kode' => $mapel->kode_tampil_jadwal,
                'kode_internal' => $mapel->kode_mapel,
                'nama' => $mapel->nama_mapel,
                'fase' => $mapel->fase_text,
            ])->values();

        $stats = [
            'guru_total' => $guruAliases->count(),
            'guru_verified' => $guruAliases->where('status', 'verified')->count(),
            'guru_review' => $guruAliases->whereIn('status', ['pending', 'suggested'])->count(),
            'mapel_total' => $mapelAliases->count(),
            'mapel_verified' => $mapelAliases->where('status', 'verified')->count(),
        ];

        return view('admin.jadwal-mapping.index', compact(
            'tahunList',
            'tahun',
            'guruAliases',
            'mapelAliases',
            'gtkOptions',
            'mapelOptions',
            'stats'
        ));
    }

    public function refresh(Request $request)
    {
        $this->authorize('manage-jadwal-mapping');
        $request->validate(['tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id']]);

        $tahun = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);
        try {
            $result = $this->mappingService->synchronize($tahun, $request->user());
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('jadwal-mapping')
            ->causedBy($request->user())
            ->withProperties(['tahun_pelajaran_id' => $tahun->id, 'result' => $result])
            ->log('Menyinkronkan referensi kode guru dan mapel jadwal');

        return redirect()
            ->route('admin.jadwal-mapping.index', ['tahun_pelajaran_id' => $tahun->id])
            ->with('success', "Referensi diperbarui: {$result['guru']} kode guru dan {$result['mapel']} kode mapel.");
    }

    public function updateGuru(Request $request, JadwalGuruAlias $alias)
    {
        $this->authorize('manage-jadwal-mapping');
        $data = $request->validate([
            'external_name' => ['required', 'string', 'max:255'],
            'gtk_id' => ['nullable', 'exists:gtks,id'],
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'verified' && empty($data['gtk_id'])) {
            return back()->with('error', 'Pilih GTK SIMANSA sebelum memverifikasi alias.');
        }

        if (!empty($data['gtk_id'])) {
            $used = JadwalGuruAlias::query()
                ->where('tahun_pelajaran_id', $alias->tahun_pelajaran_id)
                ->where('gtk_id', $data['gtk_id'])
                ->where('status', 'verified')
                ->where('id', '!=', $alias->id)
                ->first();
            if ($used) {
                return back()->with('error', "GTK tersebut sudah dipakai oleh kode {$used->external_code}.");
            }
        }

        $before = $alias->only(['external_name', 'gtk_id', 'status', 'notes']);
        $oldGtkId = $alias->gtk_id;
        $alias->fill($data);
        $alias->normalized_name = $this->mappingService->normalizePersonName($data['external_name']);
        $alias->confidence = $data['status'] === 'verified' ? 100 : $alias->confidence;
        $alias->match_method = $data['status'] === 'verified' ? 'manual_verified' : $alias->match_method;
        $alias->verified_by = $data['status'] === 'verified' ? $request->user()->id : null;
        $alias->verified_at = $data['status'] === 'verified' ? now() : null;
        $alias->save();

        if ($oldGtkId && ($oldGtkId !== $alias->gtk_id || $alias->status !== 'verified')) {
            Gtk::query()->whereKey($oldGtkId)->where('kode_gtk', $alias->external_code)->update(['kode_gtk' => null]);
        }
        $this->mappingService->applyVerifiedGtkCode($alias);

        activity('jadwal-mapping')
            ->performedOn($alias)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $before,
                'new' => $alias->only(['external_name', 'gtk_id', 'status', 'notes']),
                'external_code' => $alias->external_code,
            ])
            ->log('Memperbarui mapping kode guru jadwal');

        return back()->with('success', "Mapping guru kode {$alias->external_code} berhasil diperbarui.");
    }

    public function updateMapel(Request $request, JadwalMapelAlias $alias)
    {
        $this->authorize('manage-jadwal-mapping');
        $data = $request->validate([
            'external_name' => ['required', 'string', 'max:255'],
            'mata_pelajaran_id' => ['nullable', 'exists:mata_pelajaran,id'],
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'verified' && empty($data['mata_pelajaran_id'])) {
            return back()->with('error', 'Pilih mata pelajaran SIMANSA sebelum memverifikasi alias.');
        }

        $before = $alias->only(['external_name', 'mata_pelajaran_id', 'status', 'notes']);
        $alias->fill($data);
        $alias->normalized_name = $this->mappingService->normalizeText($data['external_name']);
        $alias->confidence = $data['status'] === 'verified' ? 100 : $alias->confidence;
        $alias->match_method = $data['status'] === 'verified' ? 'manual_verified' : $alias->match_method;
        $alias->verified_by = $data['status'] === 'verified' ? $request->user()->id : null;
        $alias->verified_at = $data['status'] === 'verified' ? now() : null;
        $alias->save();

        activity('jadwal-mapping')
            ->performedOn($alias)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $before,
                'new' => $alias->only(['external_name', 'mata_pelajaran_id', 'status', 'notes']),
                'external_code' => $alias->external_code,
            ])
            ->log('Memperbarui alias mata pelajaran jadwal');

        return back()->with('success', "Alias mapel {$alias->external_code} berhasil diperbarui.");
    }
}
