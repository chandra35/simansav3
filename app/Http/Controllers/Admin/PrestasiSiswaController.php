<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PrestasiSiswaController extends Controller
{
    private const TINGKAT = ['sekolah', 'kabupaten', 'provinsi', 'nasional', 'internasional'];
    private const TIPE = ['individu', 'tim'];

    public function index(Request $request)
    {
        $query = PrestasiSiswa::query()->with(['peserta:id,nama_lengkap,nisn', 'siswa:id,nama_lengkap,nisn'])
            ->orderByDesc('tahun')->latest('created_at');

        if ($request->ajax()) {
            $query->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
                ->when($request->filled('nama_prestasi'), fn ($q) => $q->where('nama_prestasi', $request->nama_prestasi))
                ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->tingkat))
                ->when($request->filled('tipe_peserta'), fn ($q) => $q->where('tipe_peserta', $request->tipe_peserta));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filter(function ($builder) use ($request) {
                    $keyword = trim((string) data_get($request->input('search'), 'value'));
                    if ($keyword !== '') {
                        $builder->where(function ($search) use ($keyword) {
                            $search->where('nama_prestasi', 'like', "%{$keyword}%")
                                ->orWhere('bidang', 'like', "%{$keyword}%")
                                ->orWhere('peringkat_nama', 'like', "%{$keyword}%")
                                ->orWhere('perolehan_prestasi', 'like', "%{$keyword}%")
                                ->orWhere('nama_siswa_manual', 'like', "%{$keyword}%")
                                ->orWhereHas('peserta', fn ($siswa) => $siswa->where('nama_lengkap', 'like', "%{$keyword}%")->orWhere('nisn', 'like', "%{$keyword}%"));
                        });
                    }
                })
                ->addColumn('peserta_label', function (PrestasiSiswa $prestasi) {
                    $names = $prestasi->peserta->pluck('nama_lengkap')->filter()->unique()->values();
                    $name = $names->isNotEmpty() ? $names->implode(', ') : ($prestasi->nama_siswa_manual ?: 'Peserta belum dicatat');
                    return '<strong>'.e($name).'</strong><br><small class="text-muted">'.e($prestasi->tipe_peserta === 'tim' ? 'Tim' : 'Individu').'</small>';
                })
                ->addColumn('perlombaan_label', fn (PrestasiSiswa $prestasi) => '<strong>'.e($prestasi->nama_prestasi).'</strong><br><small class="text-muted">'.e($prestasi->bidang ?: 'Bidang belum diisi').'</small>')
                ->addColumn('tingkat_label', fn (PrestasiSiswa $prestasi) => '<span class="badge badge-light border">'.e($prestasi->tingkat_label).'</span>')
                ->addColumn('prestasi_label', fn (PrestasiSiswa $prestasi) => '<strong>'.e(implode(' · ', array_filter([$prestasi->peringkat_label, $prestasi->perolehan_prestasi]))).'</strong>')
                ->addColumn('action', fn (PrestasiSiswa $prestasi) => $this->actionButtons($prestasi))
                ->rawColumns(['peserta_label', 'perlombaan_label', 'tingkat_label', 'prestasi_label', 'action'])
                ->toJson();
        }

        $years = PrestasiSiswa::query()->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
        $yearlyStats = PrestasiSiswa::query()->selectRaw('tahun, COUNT(*) as total')->whereNotNull('tahun')->groupBy('tahun')->orderByDesc('tahun')->limit(6)->get();
        $stats = [
            'total' => PrestasiSiswa::count(), 'tahun_ini' => PrestasiSiswa::where('tahun', now()->year)->count(),
            'tim' => PrestasiSiswa::where('tipe_peserta', 'tim')->count(), 'tahun_tercatat' => $years->count(),
        ];

        return view('admin.prestasi-siswa.index', compact('years', 'yearlyStats', 'stats'));
    }

    public function data(PrestasiSiswa $prestasiSiswa): JsonResponse
    {
        $prestasiSiswa->load('peserta:id,nama_lengkap,nisn');
        return response()->json(['data' => [
            'id' => $prestasiSiswa->id, 'tahun' => $prestasiSiswa->tahun, 'nama_prestasi' => $prestasiSiswa->nama_prestasi,
            'tingkat' => $prestasiSiswa->tingkat, 'bidang' => $prestasiSiswa->bidang, 'tipe_peserta' => $prestasiSiswa->tipe_peserta,
            'perolehan_prestasi' => $prestasiSiswa->perolehan_prestasi, 'peringkat_nama' => $prestasiSiswa->peringkat_label,
            'nama_siswa_manual' => $prestasiSiswa->nama_siswa_manual,
            'peserta' => $prestasiSiswa->peserta->map(fn ($siswa) => ['id' => $siswa->id, 'text' => $siswa->nama_lengkap.' · NISN '.($siswa->nisn ?: '-')]),
        ]]);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));
        $students = Siswa::query()->where('status_siswa', 'aktif')
            ->when($term !== '', fn ($query) => $query->where(fn ($q) => $q->where('nama_lengkap', 'like', "%{$term}%")->orWhere('nisn', 'like', "%{$term}%")))
            ->orderBy('nama_lengkap')->limit(20)->get(['id', 'nama_lengkap', 'nisn']);
        return response()->json(['results' => $students->map(fn ($siswa) => ['id' => $siswa->id, 'text' => $siswa->nama_lengkap.' · NISN '.($siswa->nisn ?: '-')])]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        abort_unless(in_array($request->field, ['perolehan_prestasi', 'peringkat_nama'], true), 422);
        $values = PrestasiSiswa::query()->whereNotNull($request->field)->where($request->field, '!=', '')
            ->select($request->field)->distinct()->orderBy($request->field)->limit(80)->pluck($request->field)->values();
        return response()->json(['values' => $values]);
    }

    public function store(Request $request): JsonResponse
    {
        $prestasi = PrestasiSiswa::create($this->payload($request));
        $prestasi->peserta()->sync($request->input('siswa_ids', []));
        return response()->json(['message' => 'Prestasi siswa berhasil disimpan.']);
    }

    public function update(Request $request, PrestasiSiswa $prestasiSiswa): JsonResponse
    {
        $payload = $this->payload($request);
        unset($payload['created_by']);
        $prestasiSiswa->update($payload);
        $prestasiSiswa->peserta()->sync($request->input('siswa_ids', []));
        return response()->json(['message' => 'Prestasi siswa berhasil diperbarui.']);
    }

    public function destroy(PrestasiSiswa $prestasiSiswa): JsonResponse
    {
        $prestasiSiswa->delete();
        return response()->json(['message' => 'Prestasi siswa berhasil dihapus.']);
    }

    private function payload(Request $request): array
    {
        $request->validate([
            'tahun' => ['required', 'integer', 'between:2000,'.(now()->year + 1)], 'nama_prestasi' => ['required', 'string', 'max:255'],
            'nama_prestasi_manual' => ['nullable', 'string', 'max:255', 'required_if:nama_prestasi,lainnya'],
            'tingkat' => ['required', Rule::in(self::TINGKAT)], 'bidang' => ['required', 'string', 'max:255'],
            'tipe_peserta' => ['required', Rule::in(self::TIPE)], 'perolehan_prestasi' => ['nullable', 'string', 'max:100'],
            'peringkat_nama' => ['required', 'string', 'max:100'], 'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['uuid', 'exists:siswa,id', 'distinct'], 'nama_siswa_manual' => ['nullable', 'string', 'max:2000', 'required_without:siswa_ids'],
        ], ['nama_siswa_manual.required_without' => 'Pilih peserta siswa atau isi nama peserta manual.']);
        $rank = trim((string) $request->peringkat_nama);
        $legacyRank = collect(['juara_1' => 'Juara 1', 'juara_2' => 'Juara 2', 'juara_3' => 'Juara 3', 'harapan_1' => 'Harapan 1', 'harapan_2' => 'Harapan 2', 'harapan_3' => 'Harapan 3', 'peserta' => 'Peserta', 'finalis' => 'Finalis'])
            ->search(fn ($label) => mb_strtolower($label) === mb_strtolower($rank));
        return [
            'siswa_id' => $request->input('siswa_ids.0'), 'tahun_pelajaran_id' => TahunPelajaran::query()->active()->value('id'),
            'tahun' => $request->integer('tahun'), 'nama_prestasi' => trim($request->nama_prestasi === 'lainnya' ? $request->nama_prestasi_manual : $request->nama_prestasi), 'jenis' => 'lainnya',
            'tingkat' => $request->tingkat, 'bidang' => trim($request->bidang), 'tipe_peserta' => $request->tipe_peserta,
            'peringkat' => $legacyRank ?: 'lainnya', 'peringkat_nama' => $rank,
            'perolehan_prestasi' => $request->filled('perolehan_prestasi') ? trim($request->perolehan_prestasi) : null,
            'nama_siswa_manual' => $request->filled('nama_siswa_manual') ? trim($request->nama_siswa_manual) : null,
            'penyelenggara' => '-', 'tanggal_prestasi' => $request->integer('tahun').'-01-01', 'created_by' => Auth::id(),
        ];
    }

    private function actionButtons(PrestasiSiswa $prestasi): string
    {
        $buttons = '<div class="btn-group btn-group-sm">';
        if (auth()->user()->can('edit-prestasi-siswa')) $buttons .= '<button class="btn btn-outline-primary btn-edit" data-id="'.e($prestasi->id).'" title="Edit"><i class="fas fa-pen"></i></button>';
        if (auth()->user()->can('delete-prestasi-siswa')) $buttons .= '<button class="btn btn-outline-danger btn-delete" data-id="'.e($prestasi->id).'" title="Hapus"><i class="fas fa-trash"></i></button>';
        return $buttons.'</div>';
    }
}
