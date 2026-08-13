<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanKonseling;
use App\Models\CatatanWaliKelas;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class CatatanKonselingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-catatan-konseling')->only(['index', 'records', 'show', 'searchStudents']);
        $this->middleware('permission:create-catatan-konseling')->only(['create', 'store']);
        $this->middleware('permission:edit-catatan-konseling')->only(['edit', 'update']);
        $this->middleware('permission:delete-catatan-konseling')->only('destroy');
        $this->middleware('permission:report-catatan-konseling')->only('reportSiswa');
    }

    public function index(Request $request)
    {
        $visibleIds = $this->visibleQuery()->select('catatan_konseling.id');
        $students = Siswa::query()
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasTahunAktif')
            ->with([
                'kelasTahunAktif:id,nama_kelas,tingkat',
                'catatanKonseling' => fn ($q) => $q->whereIn('catatan_konseling.id', clone $visibleIds)
                    ->latest('tanggal_konseling'),
            ])
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas(
                'kelasTahunAktif', fn ($kelas) => $kelas->where('kelas.tingkat', $request->integer('tingkat'))
            ))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas(
                'kelasTahunAktif', fn ($kelas) => $kelas->where('kelas.id', $request->kelas_id)
            ))
            ->when($request->status_pendampingan === 'belum', fn ($q) => $q->whereDoesntHave(
                'catatanKonseling', fn ($record) => $record->whereIn('catatan_konseling.id', clone $visibleIds)
            ))
            ->when($request->status_pendampingan === 'aktif', fn ($q) => $q->whereHas(
                'catatanKonseling', fn ($record) => $record->whereIn('catatan_konseling.id', clone $visibleIds)
                    ->whereIn('status', ['baru', 'dalam_proses', 'perlu_rujukan'])
            ))
            ->when($request->status_pendampingan === 'tindak_lanjut', fn ($q) => $q->whereHas(
                'catatanKonseling', fn ($record) => $record->whereIn('catatan_konseling.id', clone $visibleIds)
                    ->whereNotNull('tanggal_tindak_lanjut')->where('status', '!=', 'selesai')
            ));

        if ($request->ajax()) {
            return DataTables::eloquent($students)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $keyword = trim((string) data_get($request->input('search'), 'value'));
                    if ($keyword !== '') {
                        $query->where(function ($search) use ($keyword) {
                            $search->where('nama_lengkap', 'like', "%{$keyword}%")
                                ->orWhere('nisn', 'like', "%{$keyword}%")
                                ->orWhere('nis_lokal', 'like', "%{$keyword}%");
                        });
                    }
                })
                ->addColumn('foto', fn ($student) => '<img src="'.e($student->foto_profile_url).'" alt="Foto '.e($student->nama_lengkap).'" class="counseling-avatar">')
                ->addColumn('identitas', function ($student) {
                    $nisLokal = $student->nis_lokal ? '<br><small class="text-info">NIS Lokal '.e($student->nis_lokal).'</small>' : '';

                    return '<strong class="text-dark">'.e($student->nama_lengkap).'</strong><br><small class="text-muted">NISN '.e($student->nisn ?: '-').'</small>'.$nisLokal;
                })
                ->addColumn('jk', fn ($student) => $student->jenis_kelamin === 'L'
                    ? '<span class="badge counseling-jk male"><i class="fas fa-mars"></i> L</span>'
                    : '<span class="badge counseling-jk female"><i class="fas fa-venus"></i> P</span>')
                ->addColumn('rombel', function ($student) {
                    $class = $student->kelasTahunAktif->first();
                    if (! $class) {
                        return '<span class="text-muted">Belum ada rombel aktif</span>';
                    }

                    return '<strong>'.e($class->nama_kelas).'</strong><br><small class="text-muted">Tingkat '.e($class->tingkat).'</small>';
                })
                ->addColumn('riwayat', function ($student) {
                    $records = $student->catatanKonseling;
                    $last = $records->first();
                    if (! $last) {
                        return '<span class="badge badge-light border">Belum ada layanan</span>';
                    }

                    return '<strong>'.$records->count().' layanan</strong><br><small class="text-muted">Terakhir '.$last->tanggal_konseling?->format('d/m/Y').'</small>';
                })
                ->addColumn('status_bk', function ($student) {
                    $records = $student->catatanKonseling;
                    $overdue = $records->first(fn ($record) => $record->tindak_lanjut_terlambat);
                    if ($overdue) {
                        return '<span class="badge badge-danger"><i class="fas fa-clock"></i> Tindak lanjut terlambat</span>';
                    }
                    $active = $records->first(fn ($record) => in_array($record->status, ['baru', 'dalam_proses', 'perlu_rujukan'], true));
                    if ($active) {
                        return '<span class="badge badge-'.$active->status_badge.'">'.e($active->status_label).'</span>';
                    }

                    return $records->isEmpty()
                        ? '<span class="badge badge-secondary">Belum ditangani</span>'
                        : '<span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>';
                })
                ->addColumn('action', fn ($student) => $this->studentActionButtons($student))
                ->rawColumns(['foto', 'identitas', 'jk', 'rombel', 'riwayat', 'status_bk', 'action'])
                ->toJson();
        }

        $activeClasses = Kelas::query()->where('is_active', true)
            ->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'tingkat']);
        $visibleRecords = $this->visibleQuery();
        $activeStudents = Siswa::query()->where('status_siswa', 'aktif')->whereHas('kelasTahunAktif');
        $handledStudentIds = (clone $visibleRecords)->distinct()->pluck('siswa_id');
        $followUpStudentIds = (clone $visibleRecords)->perluTindakLanjut()->distinct()->pluck('siswa_id');
        $stats = [
            'siswa_aktif' => (clone $activeStudents)->count(),
            'pernah_dilayani' => $handledStudentIds->count(),
            'belum_dilayani' => (clone $activeStudents)->whereNotIn('id', $handledStudentIds)->count(),
            'tindak_lanjut' => $followUpStudentIds->count(),
        ];

        return view('admin.catatan-konseling.index', compact('activeClasses', 'stats'));
    }

    public function records(Request $request)
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

        return view('admin.catatan-konseling.records', [
            'status' => CatatanKonseling::STATUS,
            'kategori' => CatatanKonseling::KATEGORI_MASALAH,
            'stats' => $stats,
        ]);
    }

    public function searchStudents(Request $request)
    {
        $term = trim((string) $request->input('q'));

        $students = $this->availableStudentsQuery()
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

    public function create(Request $request)
    {
        $selectedStudent = $request->filled('siswa_id')
            ? $this->availableStudentsQuery()->with('kelasTahunAktif')->findOrFail($request->siswa_id)
            : null;

        return view('admin.catatan-konseling.create', $this->formData(new CatatanKonseling, $selectedStudent));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_confidential'] = $request->boolean('is_confidential');
        $data['share_with_teachers'] = $request->boolean('share_with_teachers');
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
        $data = $this->validated($request, $catatanKonseling);
        $data['is_confidential'] = $request->boolean('is_confidential');
        $data['share_with_teachers'] = $request->boolean('share_with_teachers');
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
        $student = $request->filled('siswa_id') ? Siswa::with([
            'user',
            'kelasTahunAktif.waliKelas',
            'ortu',
        ])->findOrFail($request->siswa_id) : null;
        $records = $student
            ? $this->visibleQuery()->with(['konselor', 'tahunPelajaran'])->where('siswa_id', $student->id)->latest('tanggal_konseling')->get()
            : collect();

        return view('admin.catatan-konseling.report-siswa', compact('student', 'records'));
    }

    private function validated(Request $request, ?CatatanKonseling $record = null): array
    {
        $isAdmin = $this->isCounselingAdmin();
        $data = $request->validate([
            'siswa_id' => ['required', Rule::exists('siswa', 'id')->where('status_siswa', 'aktif')],
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'konselor_id' => [$isAdmin ? 'required' : 'nullable', 'exists:gtks,id'],
            'tanggal_konseling' => ['required', 'date'],
            'kategori_masalah' => ['required', Rule::in(array_keys(CatatanKonseling::KATEGORI_MASALAH))],
            'permasalahan' => ['required', 'string', 'max:10000'],
            'hasil_konseling' => ['nullable', 'string', 'max:10000'],
            'rekomendasi' => ['nullable', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
            'tanggal_tindak_lanjut' => ['nullable', 'date', 'after_or_equal:tanggal_konseling'],
            'status' => ['required', Rule::in(array_keys(CatatanKonseling::STATUS))],
            'rujukan_ke' => ['nullable', 'required_if:status,perlu_rujukan', 'string', 'max:255'],
            'is_confidential' => ['nullable', 'boolean'],
            'share_with_teachers' => ['nullable', 'boolean'],
            'teacher_notice' => ['nullable', 'required_if:share_with_teachers,1', 'string', 'max:1000'],
        ]);

        if (! $record || $data['siswa_id'] !== $record->siswa_id) {
            if (! $this->availableStudentsQuery()->whereKey($data['siswa_id'])->exists()) {
                throw ValidationException::withMessages([
                    'siswa_id' => 'Siswa tidak termasuk dalam rombel aktif yang Anda ampu pada jadwal pelajaran.',
                ]);
            }
        }

        if ($isAdmin) {
            if (! $this->counselorsQuery()->whereKey($request->konselor_id)->exists()) {
                throw ValidationException::withMessages([
                    'konselor_id' => 'Konselor harus memiliki role BK atau profil GTK berjenis Guru BK.',
                ]);
            }
            $data['konselor_id'] = $request->konselor_id;
        } elseif ($record) {
            $data['konselor_id'] = $record->konselor_id;
        } else {
            $gtk = $request->user()->gtk;
            if (! $gtk || (! $request->user()->hasRole('BK') && $gtk->jenis_ptk !== 'Guru BK')) {
                throw ValidationException::withMessages([
                    'konselor_id' => 'Akun BK belum terhubung dengan profil GTK berjenis Guru BK.',
                ]);
            }
            $data['konselor_id'] = $gtk->id;
        }

        if (! $record) {
            $data['jenis_konseling'] = 'individual';
            $data['waktu_mulai'] = null;
            $data['waktu_selesai'] = null;
        }

        return $data;
    }

    private function formData(CatatanKonseling $record, ?Siswa $selectedStudent = null): array
    {
        $isAdmin = $this->isCounselingAdmin();
        $counselors = $this->counselorsQuery()
            ->when(! $isAdmin, fn ($q) => $q->whereKey($record->konselor_id ?: auth()->user()->gtk?->id))
            ->orderBy('nama_lengkap')->get();

        return [
            'catatanKonseling' => $record,
            'tahunPelajaran' => TahunPelajaran::latest('tahun_mulai')->get(),
            'konselor' => $counselors,
            'kategori' => CatatanKonseling::KATEGORI_MASALAH,
            'status' => CatatanKonseling::STATUS,
            'selectedStudent' => $selectedStudent,
            'studentContext' => $selectedStudent ? $this->studentContext($selectedStudent) : null,
            'isCounselingAdmin' => $isAdmin,
        ];
    }

    private function availableStudentsQuery(): Builder
    {
        $query = Siswa::query()->where('status_siswa', 'aktif')->whereHas('kelasTahunAktif');
        if ($this->isCounselingAdmin()) {
            return $query;
        }

        $classIds = JadwalPelajaran::query()
            ->where('gtk_id', auth()->user()->gtk?->id)
            ->where('is_active', true)
            ->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))
            ->select('kelas_id');

        return $query->whereHas('kelasTahunAktif', fn ($classes) => $classes->whereIn('kelas.id', $classIds));
    }

    private function counselorsQuery(): Builder
    {
        return Gtk::query()
            ->active()
            ->whereHas('user', fn ($users) => $users->where('is_active', true))
            ->where(fn ($counselors) => $counselors
                ->where('jenis_ptk', 'Guru BK')
                ->orWhereHas('user.roles', fn ($roles) => $roles->where('name', 'BK')));
    }

    private function isCounselingAdmin(): bool
    {
        $user = auth()->user();

        return $user->hasAnyRole(['Super Admin', 'Admin'])
            || in_array($user->role, ['super_admin', 'admin'], true);
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

    private function studentActionButtons(Siswa $student): string
    {
        $buttons = '<div class="btn-group btn-group-sm">';
        if (auth()->user()->can('report-catatan-konseling')) {
            $buttons .= '<a href="'.route('admin.catatan-konseling.report-siswa', ['siswa_id' => $student->id]).'" class="btn btn-info" title="Lihat riwayat"><i class="fas fa-history"></i></a>';
        }
        if (auth()->user()->can('create-catatan-konseling')) {
            $buttons .= '<a href="'.route('admin.catatan-konseling.create', ['siswa_id' => $student->id]).'" class="btn btn-primary" title="Catat konseling"><i class="fas fa-plus"></i> Catat</a>';
        }

        return $buttons.'</div>';
    }

    private function studentContext(Siswa $student): array
    {
        $student->loadMissing(['user', 'ortu', 'kelasTahunAktif.waliKelas']);
        $yearId = TahunPelajaran::query()->active()->value('id');
        $subject = DB::table('absensi_siswa_records as records')
            ->join('absensi_siswa_sessions as sessions', 'sessions.id', '=', 'records.session_id')
            ->whereNull('records.deleted_at')->whereNull('sessions.deleted_at')
            ->where('sessions.status', 'final')->where('sessions.tahun_pelajaran_id', $yearId)
            ->where('records.siswa_id', $student->id)
            ->selectRaw('COUNT(*) total, SUM(records.status = ?) hadir, SUM(records.status = ?) sakit, SUM(records.status = ?) izin, SUM(records.status = ?) alpa, SUM(records.status = ?) terlambat', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])
            ->first();
        $daily = DB::table('absensis')->whereNull('deleted_at')->where('user_type', 'siswa')
            ->where('tahun_pelajaran_id', $yearId)->where('user_id', $student->user_id)
            ->selectRaw('COUNT(*) total, SUM(status = ?) hadir, SUM(status = ?) sakit, SUM(status = ?) izin, SUM(status = ?) alpa, SUM(status = ?) terlambat', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])
            ->first();

        return [
            'subjectAttendance' => $subject,
            'dailyAttendance' => $daily,
            'waliNotes' => CatatanWaliKelas::with(['penulis', 'kelas'])->where('siswa_id', $student->id)
                ->where('tahun_pelajaran_id', $yearId)->latest('tanggal')->limit(8)->get(),
        ];
    }
}
