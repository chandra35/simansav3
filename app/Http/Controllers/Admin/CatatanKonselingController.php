<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanKonseling;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CatatanKonselingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-catatan-konseling')->only(['index', 'show', 'searchStudents']);
        $this->middleware('permission:create-catatan-konseling')->only(['create', 'store']);
        $this->middleware('permission:edit-catatan-konseling')->only(['edit', 'update']);
        $this->middleware('permission:delete-catatan-konseling')->only('destroy');
        $this->middleware('permission:report-catatan-konseling')->only('reportSiswa');
    }

    public function index(Request $request)
    {
        $query = $this->visibleQuery()->with(['siswa.kelasTahunAktif', 'konselor', 'tahunPelajaran'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori_masalah', $request->kategori))
            ->when($request->filled('siswa_id'), fn ($q) => $q->where('siswa_id', $request->siswa_id));

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('siswa_nama', function ($q, $keyword) {
                    $q->whereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$keyword}%")
                        ->orWhere('nisn', 'like', "%{$keyword}%"));
                })
                ->filterColumn('konselor_nama', function ($q, $keyword) {
                    $q->whereHas('konselor', fn ($gtk) => $gtk->where('nama_lengkap', 'like', "%{$keyword}%"));
                })
                ->addColumn('siswa_nama', function ($row) {
                    $kelas = $row->siswa?->kelasTahunAktif->first()?->nama_kelas ?? '-';

                    return '<strong>'.e($row->siswa?->nama_lengkap ?? '-').'</strong><br><small class="text-muted">NISN '.e($row->siswa?->nisn ?? '-').' · '.e($kelas).'</small>';
                })
                ->addColumn('konselor_nama', fn ($row) => e($row->konselor?->nama_lengkap ?? '-'))
                ->editColumn('tanggal_konseling', fn ($row) => $row->tanggal_konseling?->format('d/m/Y') ?? '-')
                ->addColumn('layanan', fn ($row) => e($row->jenis_label).'<br><small class="text-muted">'.e($row->kategori_label).'</small>')
                ->addColumn('status_badge', function ($row) {
                    $due = $row->tindak_lanjut_terlambat ? '<br><small class="text-danger"><i class="fas fa-clock"></i> Terlambat</small>' : '';

                    return '<span class="badge badge-'.$row->status_badge.'">'.e($row->status_label).'</span>'.$due;
                })
                ->addColumn('kerahasiaan', fn ($row) => $row->is_confidential
                    ? '<span class="badge badge-dark"><i class="fas fa-lock"></i> Rahasia</span>'
                    : '<span class="badge badge-light">Internal</span>')
                ->addColumn('action', fn ($row) => $this->actionButtons($row))
                ->rawColumns(['siswa_nama', 'layanan', 'status_badge', 'kerahasiaan', 'action'])
                ->toJson();
        }

        $base = $this->visibleQuery();
        $stats = [
            'total' => (clone $base)->count(),
            'aktif' => (clone $base)->whereIn('status', ['baru', 'dalam_proses'])->count(),
            'tindak_lanjut' => (clone $base)->perluTindakLanjut()->count(),
            'selesai' => (clone $base)->where('status', 'selesai')->count(),
        ];

        return view('admin.catatan-konseling.index', [
            'status' => CatatanKonseling::STATUS,
            'kategori' => CatatanKonseling::KATEGORI_MASALAH,
            'stats' => $stats,
        ]);
    }

    public function searchStudents(Request $request)
    {
        $term = trim((string) $request->input('q'));

        $students = Siswa::query()->where('status_siswa', 'aktif')
            ->with('kelasTahunAktif:id,nama_kelas')
            ->when($term !== '', fn ($q) => $q->where(function ($sub) use ($term) {
                $sub->where('nama_lengkap', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%")
                    ->orWhere('nis_lokal', 'like', "%{$term}%");
            }))
            ->orderBy('nama_lengkap')->limit(20)->get();

        return response()->json(['results' => $students->map(fn ($student) => [
            'id' => $student->id,
            'text' => $student->nama_lengkap,
            'nisn' => $student->nisn,
            'kelas' => $student->kelasTahunAktif->first()?->nama_kelas ?? 'Belum ada rombel aktif',
        ])]);
    }

    public function create()
    {
        return view('admin.catatan-konseling.create', $this->formData(new CatatanKonseling));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_confidential'] = $request->boolean('is_confidential');
        $data['created_by'] = auth()->id();
        $record = CatatanKonseling::create($data);

        return redirect()->route('admin.catatan-konseling.show', $record)
            ->with('success', 'Catatan konseling berhasil disimpan.');
    }

    public function show(CatatanKonseling $catatanKonseling)
    {
        $this->ensureVisible($catatanKonseling);
        $catatanKonseling->load(['siswa.kelasTahunAktif', 'konselor', 'tahunPelajaran', 'pembuat']);
        $riwayatKonseling = $this->visibleQuery()->with('konselor')
            ->where('siswa_id', $catatanKonseling->siswa_id)
            ->whereKeyNot($catatanKonseling->id)
            ->latest('tanggal_konseling')->limit(5)->get();

        return view('admin.catatan-konseling.show', compact('catatanKonseling', 'riwayatKonseling'));
    }

    public function edit(CatatanKonseling $catatanKonseling)
    {
        $this->ensureVisible($catatanKonseling);
        $catatanKonseling->load(['siswa.kelasTahunAktif', 'konselor']);

        return view('admin.catatan-konseling.edit', $this->formData($catatanKonseling));
    }

    public function update(Request $request, CatatanKonseling $catatanKonseling)
    {
        $this->ensureVisible($catatanKonseling);
        $data = $this->validated($request);
        $data['is_confidential'] = $request->boolean('is_confidential');
        $catatanKonseling->update($data);

        return redirect()->route('admin.catatan-konseling.show', $catatanKonseling)
            ->with('success', 'Catatan konseling berhasil diperbarui.');
    }

    public function destroy(CatatanKonseling $catatanKonseling)
    {
        $this->ensureVisible($catatanKonseling);
        $catatanKonseling->delete();

        return response()->json(['success' => true, 'message' => 'Catatan konseling berhasil dihapus.']);
    }

    public function reportSiswa(Request $request)
    {
        $student = $request->filled('siswa_id') ? Siswa::with('kelasTahunAktif')->findOrFail($request->siswa_id) : null;
        $records = $student
            ? $this->visibleQuery()->with(['konselor', 'tahunPelajaran'])->where('siswa_id', $student->id)->latest('tanggal_konseling')->get()
            : collect();

        return view('admin.catatan-konseling.report-siswa', compact('student', 'records'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'siswa_id' => ['required', Rule::exists('siswa', 'id')->where('status_siswa', 'aktif')],
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'konselor_id' => ['required', Rule::exists('gtks', 'id')->where('jenis_ptk', 'Guru BK')],
            'tanggal_konseling' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after:waktu_mulai'],
            'jenis_konseling' => ['required', Rule::in(array_keys(CatatanKonseling::JENIS_KONSELING))],
            'kategori_masalah' => ['required', Rule::in(array_keys(CatatanKonseling::KATEGORI_MASALAH))],
            'permasalahan' => ['required', 'string', 'max:10000'],
            'hasil_konseling' => ['nullable', 'string', 'max:10000'],
            'rekomendasi' => ['nullable', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
            'tanggal_tindak_lanjut' => ['nullable', 'date', 'after_or_equal:tanggal_konseling'],
            'status' => ['required', Rule::in(array_keys(CatatanKonseling::STATUS))],
            'rujukan_ke' => ['nullable', 'required_if:status,perlu_rujukan', 'string', 'max:255'],
            'is_confidential' => ['nullable', 'boolean'],
        ]);
    }

    private function formData(CatatanKonseling $record): array
    {
        $counselors = Gtk::query()->where('jenis_ptk', 'Guru BK')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->when($record->konselor_id, fn ($q) => $q->orWhereKey($record->konselor_id))
            ->orderBy('nama_lengkap')->get();

        return [
            'catatanKonseling' => $record,
            'tahunPelajaran' => TahunPelajaran::latest('tahun_mulai')->get(),
            'konselor' => $counselors,
            'jenis' => CatatanKonseling::JENIS_KONSELING,
            'kategori' => CatatanKonseling::KATEGORI_MASALAH,
            'status' => CatatanKonseling::STATUS,
        ];
    }

    private function visibleQuery(): Builder
    {
        $user = auth()->user();
        $gtkId = $user->gtk?->id;

        return CatatanKonseling::query()->when(! $user->can('view-confidential-catatan-konseling'), function ($q) use ($user, $gtkId) {
            $q->where(function ($visible) use ($user, $gtkId) {
                $visible->where('is_confidential', false)->orWhere('created_by', $user->id);
                if ($gtkId) {
                    $visible->orWhere('konselor_id', $gtkId);
                }
            });
        });
    }

    private function ensureVisible(CatatanKonseling $record): void
    {
        abort_unless($this->visibleQuery()->whereKey($record->id)->exists(), 403, 'Anda tidak memiliki akses ke catatan rahasia ini.');
    }

    private function actionButtons(CatatanKonseling $record): string
    {
        $buttons = '<div class="btn-group btn-group-sm">';
        $buttons .= '<a href="'.route('admin.catatan-konseling.show', $record).'" class="btn btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
        if (auth()->user()->can('edit-catatan-konseling')) {
            $buttons .= '<a href="'.route('admin.catatan-konseling.edit', $record).'" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        if (auth()->user()->can('delete-catatan-konseling')) {
            $buttons .= '<button type="button" class="btn btn-danger btn-delete" data-id="'.e($record->id).'" title="Hapus"><i class="fas fa-trash"></i></button>';
        }

        return $buttons.'</div>';
    }
}
