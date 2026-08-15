<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Services\GtkKemenagSyncService;
use App\Services\GtkOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

class GtkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Statistics
        $activeGtk = Gtk::query()->active();
        $stats = [
            'total_gtk' => (clone $activeGtk)->count(),
            'nonaktif' => Gtk::where('status_aktif', false)->count(),
            'gtk_with_nip' => (clone $activeGtk)->whereNotNull('nip')->where('nip', '!=', '')->count(),
            'laki_laki' => (clone $activeGtk)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $activeGtk)->where('jenis_kelamin', 'P')->count(),
            'data_lengkap' => (clone $activeGtk)->where('data_diri_completed', true)
                ->where('data_kepegawaian_completed', true)
                ->count(),
        ];

        // Filter options for status kepegawaian
        $statusKepegawaianOptions = [
            'PNS' => 'PNS',
            'PPPK' => 'PPPK',
            'GTY' => 'GTY (Guru Tetap Yayasan)',
            'PTY' => 'PTY (Pegawai Tetap Yayasan)',
            'Honorer' => 'Honorer',
        ];

        return view('admin.gtk.index', compact('stats', 'statusKepegawaianOptions'));
    }

    /**
     * Get GTK data for DataTables
     */
    public function data(Request $request)
    {
        $activeYearId = TahunPelajaran::query()->active()->value('id');
        $gtk = Gtk::with([
            'user',
            'kelasWali' => function ($query) use ($activeYearId) {
                $query->select(['id', 'wali_kelas_id', 'tahun_pelajaran_id', 'nama_kelas', 'tingkat'])
                    ->where('is_active', true)
                    ->when(
                        $activeYearId,
                        fn ($classQuery) => $classQuery->where('tahun_pelajaran_id', $activeYearId),
                        fn ($classQuery) => $classQuery->whereRaw('1 = 0')
                    )
                    ->orderBy('tingkat')
                    ->orderBy('nama_kelas');
            },
            'penugasan' => function ($query) use ($activeYearId) {
                $query->select(['id', 'gtk_id', 'jenis_penugasan_id', 'tahun_pelajaran_id', 'unit_nama'])
                    ->with('jenis:id,nama')
                    ->where('status', 'active')
                    ->when(
                        $activeYearId,
                        fn ($assignmentQuery) => $assignmentQuery->where('tahun_pelajaran_id', $activeYearId),
                        fn ($assignmentQuery) => $assignmentQuery->whereRaw('1 = 0')
                    )
                    ->orderBy('created_at');
            },
        ])->select(['id', 'nama_lengkap', 'nik', 'nuptk', 'nip', 'peg_id', 'status_inpassing', 'status_sertifikasi', 'kode_gtk', 'jenis_kelamin', 'foto_profile', 'kategori_ptk', 'jenis_ptk', 'status_kepegawaian', 'jabatan', 'user_id', 'status_aktif', 'alasan_nonaktif', 'tanggal_status', 'data_diri_completed', 'data_kepegawaian_completed', 'created_at']);

        if ($request->filled('status_aktif')) {
            $gtk->where('status_aktif', $request->status_aktif === '1');
        }

        // Filter by Kategori PTK
        if ($request->filled('kategori_ptk')) {
            $gtk->where('kategori_ptk', $request->kategori_ptk);
        }

        // Filter by Jenis PTK
        if ($request->filled('jenis_ptk')) {
            $gtk->where('jenis_ptk', $request->jenis_ptk);
        }

        // Filter by Jenis Kelamin
        if ($request->filled('jenis_kelamin')) {
            $gtk->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter by Status Kepegawaian
        if ($request->filled('status_kepegawaian')) {
            $gtk->where('status_kepegawaian', $request->status_kepegawaian);
        }

        // Filter by Status Data
        if ($request->filled('status')) {
            if ($request->status == 'lengkap') {
                $gtk->where('data_diri_completed', true)
                    ->where('data_kepegawaian_completed', true);
            } elseif ($request->status == 'belum') {
                $gtk->where(function ($q) {
                    $q->where('data_diri_completed', false)
                        ->orWhere('data_kepegawaian_completed', false);
                });
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $gtk->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('peg_id', 'like', "%{$search}%")
                    ->orWhere('status_inpassing', 'like', "%{$search}%")
                    ->orWhere('status_sertifikasi', 'like', "%{$search}%")
                    ->orWhere('kode_gtk', 'like', "%{$search}%")
                    ->orWhere('kategori_ptk', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        $totalRecords = Gtk::count();
        $filteredRecords = $gtk->count();

        // Pagination - Handle "All" option
        if ($request->has('start') && $request->has('length')) {
            $length = $request->length;
            if ($length != -1) {
                $gtk->skip($request->start)->take($length);
            }
        }

        // Ordering
        if ($request->has('order')) {
            $columns = [null, 'nama_lengkap', 'jenis_ptk', null, null];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
            $orderDirection = $request->order[0]['dir'];
            $gtk->orderBy($orderColumn, $orderDirection);
        } else {
            $gtk->latest();
        }

        $data = $gtk->get()->map(function ($item, $index) use ($request) {
            $jenisPtkClass = $item->kategori_ptk === 'Pendidik'
                ? 'simansa-gtk-meta-badge--primary'
                : 'simansa-gtk-meta-badge--info';
            $nama = e($item->nama_lengkap);
            $nik = e($item->nik ?: '-');
            $kodeGtk = $item->kode_gtk
                ? '<span class="simansa-gtk-identifier"><small>Kode Jadwal</small><code>'.e($item->kode_gtk).'</code></span>'
                : '';
            $jenisPtk = e($item->jenis_ptk ?: '-');
            $pegId = e($item->peg_id ?: '-');
            $avatar = $this->getGtkListAvatar($item);
            $waliKelasNames = $item->kelasWali
                ->pluck('nama_kelas')
                ->filter()
                ->unique()
                ->values();
            $waliKelasBadges = $waliKelasNames->isEmpty()
                ? '<span class="simansa-gtk-role-empty">Bukan wali kelas</span>'
                : $waliKelasNames
                    ->map(fn ($className) => '<span class="simansa-gtk-wali-badge">'.e($className).'</span>')
                    ->implode('');
            $assignmentNames = $item->penugasan
                ->map(fn ($assignment) => trim(($assignment->jenis?->nama ?: '').($assignment->unit_nama ? ' · '.$assignment->unit_nama : '')))
                ->filter()
                ->unique()
                ->values();
            $assignmentBadges = $assignmentNames->isEmpty()
                ? '<span class="simansa-gtk-role-empty">Tidak ada penugasan aktif</span>'
                : $assignmentNames
                    ->map(fn ($assignmentName) => '<span class="simansa-gtk-assignment-badge"><i class="fas fa-briefcase"></i>'.e($assignmentName).'</span>')
                    ->implode('');
            $inpassingText = e($item->status_inpassing ?: 'Belum tercatat');
            $inpassingClass = filled($item->status_inpassing) ? 'is-success' : 'is-muted';
            $sertifikasiText = e($item->status_sertifikasi ?: 'Belum tercatat');
            $sertifikasiClass = str_starts_with(mb_strtolower((string) $item->status_sertifikasi), 'sudah')
                ? 'is-success'
                : (filled($item->status_sertifikasi) ? 'is-warning' : 'is-muted');
            $dataDiriClass = $item->data_diri_completed ? 'is-success' : 'is-danger';
            $dataKepegClass = $item->data_kepegawaian_completed ? 'is-success' : 'is-danger';
            $operationalClass = $item->status_aktif ? 'is-success' : 'is-danger';
            $operationalText = $item->status_aktif ? 'Aktif' : 'Nonaktif';

            return [
                'DT_RowIndex' => $request->start + $index + 1,
                'identity' => '
                    <div class="simansa-gtk-profile">
                        '.$avatar.'
                        <div class="simansa-gtk-profile__content">
                            <div class="simansa-gtk-profile__name">'.$nama.'</div>
                            <div class="simansa-gtk-profile__identifiers">
                                <span class="simansa-gtk-identifier"><small>NIK</small><code>'.$nik.'</code></span>
                                <span class="simansa-gtk-identifier"><small>ID PTK</small><code>'.$pegId.'</code></span>
                                '.$kodeGtk.'
                            </div>
                        </div>
                    </div>',
                'role_summary' => '
                    <div class="simansa-gtk-role-cell">
                        <span class="simansa-gtk-meta-badge '.$jenisPtkClass.'"><i class="fas fa-user-tag"></i>'.$jenisPtk.'</span>
                        <div class="simansa-gtk-role-label">Penugasan aktif</div>
                        <div class="simansa-gtk-assignment-list">'.$assignmentBadges.'</div>
                        <div class="simansa-gtk-role-label">Wali kelas</div>
                        <div class="simansa-gtk-wali-list">'.$waliKelasBadges.'</div>
                    </div>',
                'status_summary' => '
                    <div class="simansa-gtk-status-grid">
                        <span class="simansa-gtk-status-badge '.$inpassingClass.'" data-tooltip="true" title="Status inpassing: '.$inpassingText.'"><i class="fas fa-layer-group"></i><span>Inpassing</span><strong>'.$inpassingText.'</strong></span>
                        <span class="simansa-gtk-status-badge '.$sertifikasiClass.'" data-tooltip="true" title="Status sertifikasi: '.$sertifikasiText.'"><i class="fas fa-certificate"></i><span>Sertifikasi</span><strong>'.$sertifikasiText.'</strong></span>
                        <span class="simansa-gtk-status-badge '.$dataDiriClass.'" data-tooltip="true" title="Kelengkapan data diri"><i class="fas fa-id-card"></i><span>Data diri</span><strong>'.($item->data_diri_completed ? 'Lengkap' : 'Belum lengkap').'</strong></span>
                        <span class="simansa-gtk-status-badge '.$dataKepegClass.'" data-tooltip="true" title="Kelengkapan data kepegawaian"><i class="fas fa-briefcase"></i><span>Kepegawaian</span><strong>'.($item->data_kepegawaian_completed ? 'Lengkap' : 'Belum lengkap').'</strong></span>
                        <span class="simansa-gtk-status-badge '.$operationalClass.'" data-tooltip="true" title="Status operasional GTK"><i class="fas fa-user-check"></i><span>Keaktifan</span><strong>'.$operationalText.'</strong></span>
                    </div>',
                'nuptk' => $item->nuptk ?? '-',
                'nip' => $item->nip ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'jenis_ptk' => $item->jenis_ptk ?? '-',
                'status_kepegawaian' => $item->status_kepegawaian ?? '-',
                'jabatan' => $item->jabatan ?? '-',
                'username' => $item->user->username ?? '-',
                'actions' => $this->getActionButtons($item),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    private function getGtkListAvatar(Gtk $gtk): string
    {
        $isFemale = $gtk->jenis_kelamin === 'P';
        $toneClass = $isFemale ? 'is-female' : 'is-male';
        $label = 'Avatar '.($isFemale ? 'muslimah' : 'muslim').' '.e($gtk->nama_lengkap);

        $svg = $isFemale
            ? '<svg viewBox="0 0 64 64" aria-hidden="true">
                    <circle cx="32" cy="32" r="31" fill="#fdf2f8"/>
                    <path d="M14 56c1-15 7-25 18-27 11 2 17 12 18 27H14z" fill="#be185d"/>
                    <path d="M20 28c1-12 6-19 12-19s11 7 12 19l-4 18H24l-4-18z" fill="#ec4899"/>
                    <ellipse cx="32" cy="28" rx="9" ry="11" fill="#f6c7a5"/>
                    <path d="M23 25c2-10 6-14 9-14 4 0 8 4 10 14-6-3-13-3-19 0z" fill="#db2777"/>
                    <circle cx="29" cy="28" r="1" fill="#334155"/><circle cx="35" cy="28" r="1" fill="#334155"/>
                    <path d="M29 33c2 1 4 1 6 0" fill="none" stroke="#9f1239" stroke-width="1.2" stroke-linecap="round"/>
                    <path d="M22 43c6 5 14 5 20 0" fill="none" stroke="#fbcfe8" stroke-width="2" stroke-linecap="round"/>
                </svg>'
            : '<svg viewBox="0 0 64 64" aria-hidden="true">
                    <circle cx="32" cy="32" r="31" fill="#eff6ff"/>
                    <path d="M13 57c2-14 9-22 19-22s17 8 19 22H13z" fill="#1d4ed8"/>
                    <ellipse cx="32" cy="29" rx="10" ry="12" fill="#d9a679"/>
                    <path d="M21 20h22l-3-10H24l-3 10z" fill="#172554"/>
                    <path d="M23 18c5-2 13-2 18 0v5H23v-5z" fill="#1e3a8a"/>
                    <circle cx="29" cy="29" r="1" fill="#1e293b"/><circle cx="35" cy="29" r="1" fill="#1e293b"/>
                    <path d="M29 34c2 1 4 1 6 0" fill="none" stroke="#7c2d12" stroke-width="1.2" stroke-linecap="round"/>
                    <path d="M25 43l7 5 7-5" fill="none" stroke="#dbeafe" stroke-width="2" stroke-linecap="round"/>
                </svg>';

        $photo = filled($gtk->foto_profile)
            ? '<img src="'.e($gtk->foto_profile_url).'" alt="Foto '.e($gtk->nama_lengkap).'" loading="lazy" onerror="this.remove()">'
            : '';
        $photoClass = $photo !== '' ? ' has-photo' : ' is-placeholder';

        return '<button type="button" class="simansa-gtk-avatar simansa-gtk-avatar-trigger '.$toneClass.$photoClass.'" data-gtk-photo-detail="'.e($gtk->id).'" aria-label="Lihat foto dan profil '.$label.'">'
            .$svg.$photo
            .'</button>';
    }

    /**
     * Get GTK candidates that can be synced with Kemenag safely.
     * Only returns GTK that already have a NIP.
     */
    public function syncKemenagCandidates()
    {
        if (! auth()->user()->can('edit-gtk')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan sinkronisasi massal.',
            ], 403);
        }

        $candidates = Gtk::query()
            ->active()
            ->select(['id', 'nama_lengkap', 'nip'])
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($gtk) {
                return [
                    'id' => $gtk->id,
                    'nama_lengkap' => $gtk->nama_lengkap,
                    'nip' => $gtk->nip,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => $candidates->isEmpty()
                ? 'Belum ada GTK dengan NIP yang bisa disinkronkan.'
                : 'Kandidat sinkronisasi berhasil disiapkan.',
            'total' => $candidates->count(),
            'candidates' => $candidates,
        ]);
    }

    private function getActionButtons($item)
    {
        $user = auth()->user();
        $groups = [[], [], []];

        if ($user->can('view-gtk')) {
            $groups[0][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="view" onclick="handleGtkAction(this)"><i class="fas fa-eye text-info"></i><span>Lihat detail</span></button>';
            $groups[0][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="schedule" onclick="handleGtkAction(this)"><i class="fas fa-calendar-alt text-success"></i><span>Lihat jadwal</span></button>';
        }
        if ($user->can('view-beban-kerja-gtk')) {
            $groups[0][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="workload" onclick="handleGtkAction(this)"><i class="fas fa-balance-scale text-primary"></i><span>Beban kerja & penugasan</span></button>';
        }

        if ($user->can('edit-gtk')) {
            $groups[0][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="edit" onclick="handleGtkAction(this)"><i class="fas fa-edit text-primary"></i><span>Edit data</span></button>';
            $groups[0][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="update-photo" onclick="handleGtkAction(this)"><i class="fas fa-camera text-success"></i><span>Update foto profil</span></button>';
        }

        if ($user->can('manage-status-gtk')) {
            $groups[1][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="mutation" onclick="handleGtkAction(this)"><i class="fas fa-user-clock text-info"></i><span>Mutasi / ubah status</span></button>';
        }

        if ($user->can('reset-password-gtk')) {
            $groups[1][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="reset-password" onclick="handleGtkAction(this)"><i class="fas fa-key text-warning"></i><span>Reset password</span></button>';
        }

        if ($user->can('impersonate-users') && $item->user_id) {
            $groups[1][] = '<button type="button" class="dropdown-item simansa-gtk-action-item" data-action="login-as" onclick="handleGtkAction(this)"><i class="fas fa-user-shield text-success"></i><span>Login sebagai GTK</span></button>';
        }

        if ($user->can('delete-gtk')) {
            $groups[2][] = '<button type="button" class="dropdown-item simansa-gtk-action-item text-danger" data-action="delete" onclick="handleGtkAction(this)"><i class="fas fa-trash-alt"></i><span>Hapus GTK</span></button>';
        }

        $menus = array_values(array_filter($groups));

        if ($menus === []) {
            return '-';
        }

        return '<div class="btn-group simansa-gtk-action-menu"'
            .' data-gtk-id="'.e($item->id).'"'
            .' data-gtk-name="'.e($item->nama_lengkap).'"'
            .' data-photo-url="'.($item->foto_profile ? e($item->foto_profile_url) : '').'"'
            .' data-upload-url="'.e(route('admin.gtk.upload-foto', $item->id)).'"'
            .' data-edit-url="'.e(route('admin.gtk.edit', $item->id)).'"'
            .' data-schedule-url="'.e(route('admin.gtk.schedule', $item->id)).'"'
            .' data-workload-url="'.e(route('admin.penugasan-gtk.workload', ['gtk_id' => $item->id])).'"'
            .' data-mutation-url="'.e(route('admin.mutasi-gtk.index', ['gtk_id' => $item->id])).'"'
            .' data-login-url="'.e(route('admin.impersonation.gtk.start', $item->id)).'">'
            .'<button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle simansa-gtk-action-toggle"'
            .' data-toggle="dropdown" data-boundary="viewport" data-tooltip="true" data-placement="left" title="Pilih aksi untuk '.e($item->nama_lengkap).'"'
            .' aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v mr-1"></i>Aksi</button>'
            .'<div class="dropdown-menu dropdown-menu-right simansa-gtk-action-dropdown">'
            .implode('<div class="dropdown-divider"></div>', array_map(fn ($group) => implode('', $group), $menus))
            .'</div></div>';
    }

    public function schedule(Request $request, Gtk $gtk)
    {
        $yearIds = JadwalPelajaran::query()->where('gtk_id', $gtk->id)
            ->where('is_active', true)->distinct()->pluck('tahun_pelajaran_id');
        $years = TahunPelajaran::query()->whereIn('id', $yearIds)
            ->orWhere('is_active', true)->orderByDesc('tahun_mulai')->get();
        $selectedYear = $request->filled('tahun_pelajaran_id')
            ? $years->firstWhere('id', $request->tahun_pelajaran_id)
            : $years->firstWhere('is_active', true);
        $selectedYear ??= $years->first();

        $schedules = $selectedYear
            ? JadwalPelajaran::query()->with(['mataPelajaran', 'kelas'])
                ->where('gtk_id', $gtk->id)
                ->where('tahun_pelajaran_id', $selectedYear->id)
                ->where('is_active', true)
                ->orderByRaw("FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu')")
                ->orderBy('jam_ke')->get()
            : collect();

        $stats = [
            'slots' => $schedules->count(),
            'periods' => $schedules->sum(fn ($schedule) => count(array_filter(array_map('trim', explode(',', (string) $schedule->jam_ke)))) ?: 1),
            'subjects' => $schedules->pluck('mapel_id')->filter()->unique()->count(),
            'classes' => $schedules->pluck('kelas_id')->filter()->unique()->count(),
        ];

        return view('admin.gtk.schedule', [
            'gtk' => $gtk,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'schedulesByDay' => $schedules->groupBy('hari'),
            'dayLabels' => JadwalPelajaran::HARI,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created resource in storage (via modal - hanya nama dan NIK)
     */
    public function store(Request $request, GtkOnboardingService $onboarding)
    {
        try {
            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'required|string|size:16|unique:gtks,nik|unique:users,username',
                'jenis_kelamin' => 'required|in:L,P',
                'kategori_ptk' => 'required|in:Pendidik,Tenaga Kependidikan',
                'jenis_ptk' => ['required', Rule::in($request->kategori_ptk === 'Pendidik' ? ['Guru Mapel', 'Guru BK'] : ['Kepala TU', 'Staff TU', 'Bendahara', 'Laboran', 'Pustakawan', 'Cleaning Service', 'Satpam', 'Lainnya'])],
                'tanggal_efektif' => 'required|date|before_or_equal:today',
            ], [
                'nama_lengkap.required' => 'Nama lengkap wajib diisi',
                'nik.required' => 'NIK wajib diisi',
                'nik.size' => 'NIK harus 16 digit',
                'nik.unique' => 'NIK sudah terdaftar',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
                'kategori_ptk.required' => 'Kategori PTK wajib dipilih',
                'jenis_ptk.required' => 'Jenis PTK wajib dipilih',
            ]);

            $gtk = $onboarding->create($validated, 'gtk_baru');

            return response()->json([
                'success' => true,
                'message' => 'GTK baru dan histori awal berhasil dibuat. Username dan password default menggunakan NIK.',
                'data' => $gtk,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating GTK: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $gtk = Gtk::with(['user', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => array_merge($gtk->toArray(), [
                'foto_profile_url' => $gtk->foto_profile_url,
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $gtk = Gtk::with(['user.roles', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'kemenagSync.syncedBy'])->findOrFail($id);

        // Get all provinces for dropdown
        $provinces = \Laravolt\Indonesia\Models\Province::all();

        // Get cities, districts, villages based on current data
        $cities = [];
        $districts = [];
        $villages = [];

        if ($gtk->provinsi_id) {
            $cities = \Laravolt\Indonesia\Models\City::where('province_code', $gtk->provinsi_id)->get();
        }

        if ($gtk->kabupaten_id) {
            $districts = \Laravolt\Indonesia\Models\District::where('city_code', $gtk->kabupaten_id)->get();
        }

        if ($gtk->kecamatan_id) {
            $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $gtk->kecamatan_id)->get();
        }

        // Get all roles for dropdown
        $roles = \Spatie\Permission\Models\Role::all();

        return view('admin.gtk.edit', compact('gtk', 'provinces', 'cities', 'districts', 'villages', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $gtk = Gtk::with('user')->findOrFail($id);

        // Determine which tab is being updated
        $tab = $request->input('tab', 'diri');

        try {
            DB::beginTransaction();

            if ($tab === 'diri') {
                // Update Data Pribadi
                $validated = $request->validate([
                    'nama_lengkap' => 'required|string|max:255',
                    'nik' => 'required|string|size:16|unique:gtks,nik,'.$gtk->id,
                    'nuptk' => 'nullable|string|max:20',
                    'peg_id' => 'nullable|string|max:20|unique:gtks,peg_id,'.$gtk->id,
                    'jenis_kelamin' => 'required|in:L,P',
                    'tempat_lahir' => 'nullable|string|max:100',
                    'tanggal_lahir' => 'nullable|date',
                    'nomor_hp' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'alamat' => 'nullable|string',
                    'rt' => 'nullable|string|max:5',
                    'rw' => 'nullable|string|max:5',
                    'provinsi_id' => 'nullable|string',
                    'kabupaten_id' => 'nullable|string',
                    'kecamatan_id' => 'nullable|string',
                    'kelurahan_id' => 'nullable|string',
                    'kodepos' => 'nullable|string|max:10',
                ]);

                $gtk->update($validated);

                // Check if data diri is complete
                $dataLengkap = ! empty($gtk->nik) && ! empty($gtk->nama_lengkap) &&
                               ! empty($gtk->jenis_kelamin) && ! empty($gtk->tempat_lahir) &&
                               ! empty($gtk->tanggal_lahir);
                $gtk->update(['data_diri_completed' => $dataLengkap]);

                $message = 'Data pribadi berhasil diperbarui';

            } elseif ($tab === 'kepeg') {
                // Update Data Kepegawaian
                $request->merge([
                    'kode_gtk' => $request->filled('kode_gtk')
                        ? trim((string) $request->kode_gtk)
                        : null,
                ]);
                $validated = $request->validate([
                    'nip' => 'nullable|string|max:20',
                    // Kode ini dipakai persis seperti pada sheet Kode_GTK_mapel
                    // Wakakur, misalnya 56 pada slot jadwal 56S.
                    'kode_gtk' => [
                        'nullable',
                        'regex:/^\d{1,3}$/',
                        Rule::unique('gtks', 'kode_gtk')->ignore($gtk->id),
                    ],
                    'kategori_ptk' => 'required|in:Pendidik,Tenaga Kependidikan',
                    'jenis_ptk' => 'required|in:Guru Mapel,Guru BK,Kepala TU,Staff TU,Bendahara,Laboran,Pustakawan,Cleaning Service,Satpam,Lainnya',
                    'status_kepegawaian' => 'nullable|in:PNS,PPPK,GTY,PTY,Honorer',
                    'jabatan' => 'nullable|string|max:100',
                    'tmt_kerja' => 'nullable|date',
                ]);

                $gtk->update($validated);

                // Check if data kepegawaian is complete
                $kepegLengkap = ! empty($gtk->status_kepegawaian) && ! empty($gtk->jabatan) && ! empty($gtk->tmt_kerja);
                $gtk->update(['data_kepegawaian_completed' => $kepegLengkap]);

                $message = 'Data kepegawaian berhasil diperbarui';

            } elseif ($tab === 'akun') {
                // Update Akun User
                if (! $gtk->user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'GTK tidak memiliki akun user',
                    ], 404);
                }

                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'username' => 'required|string|max:255|unique:users,username,'.$gtk->user->id,
                    'email' => 'required|email|max:255|unique:users,email,'.$gtk->user->id,
                    'role' => 'required|exists:roles,name',
                ]);

                $gtk->user->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                ]);

                // Sync role
                $gtk->user->syncRoles([$validated['role']]);

                $message = 'Data akun berhasil diperbarui';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $gtk->fresh(['user.roles']),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating GTK: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload foto profile GTK via AJAX
     */
    public function uploadFoto(Request $request, $id)
    {
        $gtk = Gtk::findOrFail($id);

        $request->validate([
            'foto_profile' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ], [
            'foto_profile.required' => 'File foto wajib dipilih.',
            'foto_profile.image' => 'File harus berupa gambar.',
            'foto_profile.mimes' => 'Format yang diizinkan: JPG, JPEG, PNG, atau WEBP.',
            'foto_profile.max' => 'Ukuran file asli maksimal 20MB.',
        ]);

        $newPath = null;

        try {
            $file = $request->file('foto_profile');
            $originalSize = (int) $file->getSize();
            $oldPath = $gtk->foto_profile_path;
            $newPath = 'foto_profile/gtk/'.$gtk->id.'-'.Str::uuid().'.jpg';

            // Normalisasi foto profil menjadi potret 4:5. Hasil akhir kecil dan konsisten
            // walaupun admin memilih file kamera beresolusi tinggi.
            $encoded = Image::read($file->getRealPath())
                ->cover(720, 900)
                ->toJpeg(82);

            if (! Storage::disk('public')->put($newPath, (string) $encoded)) {
                throw new \RuntimeException('Penyimpanan foto profil gagal.');
            }

            $gtk->update(['foto_profile' => $newPath]);

            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil dipotong dan dikompresi.',
                'foto_url' => $gtk->fresh()->foto_profile_url,
                'original_size_kb' => (int) ceil($originalSize / 1024),
                'compressed_size_kb' => (int) ceil(Storage::disk('public')->size($newPath) / 1024),
                'width' => 720,
                'height' => 900,
            ]);
        } catch (\Exception $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            Log::error('Error uploading GTK foto: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto.',
            ], 500);
        }
    }

    /**
     * Delete foto profile GTK via AJAX
     */
    public function deleteFoto($id)
    {
        $gtk = Gtk::findOrFail($id);

        try {
            if ($gtk->foto_profile) {
                Storage::disk('public')->delete($gtk->foto_profile);
                $gtk->update(['foto_profile' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus.',
                'default_url' => $gtk->fresh()->foto_profile_url,
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting GTK foto: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus foto.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $gtk = Gtk::findOrFail($id);

            if ($gtk->asramaAssignments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'GTK masih terdaftar pada tim Asrama. Lepas seluruh tugas dan hapus GTK dari menu Pengasuh & Pengajar Asrama terlebih dahulu.',
                ], 422);
            }

            DB::beginTransaction();

            // Hapus user jika ada
            if ($gtk->user_id) {
                $user = User::find($gtk->user_id);
                if ($user) {
                    $user->delete();
                }
            }

            // Hapus GTK
            $gtk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data GTK berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting GTK: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password GTK (password = NIK)
     */
    public function resetPassword($id)
    {
        try {
            $gtk = Gtk::with('user')->findOrFail($id);

            if (! $gtk->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'GTK tidak memiliki akun user',
                ], 404);
            }

            $gtk->user->update([
                'password' => Hash::make($gtk->nik),
                'is_first_login' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset ke NIK',
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting password: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cities by province
     */
    public function getCities($provinceCode)
    {
        $cities = \Laravolt\Indonesia\Models\City::where('province_code', $provinceCode)->get();

        return response()->json($cities);
    }

    /**
     * Get districts by city
     */
    public function getDistricts($cityCode)
    {
        $districts = \Laravolt\Indonesia\Models\District::where('city_code', $cityCode)->get();

        return response()->json($districts);
    }

    /**
     * Get villages by district
     */
    public function getVillages($districtCode)
    {
        $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $districtCode)
            ->get()
            ->map(function ($village) {
                return [
                    'code' => $village->code,
                    'district_code' => $village->district_code,
                    'name' => $village->name,
                    'meta' => $village->meta_json ? json_decode($village->meta_json, true) : null,
                ];
            });

        return response()->json($villages);
    }

    /**
     * Sync GTK dengan API Kemenag BE-PINTAR
     */
    public function syncKemenag($id, GtkKemenagSyncService $syncService)
    {
        try {
            // Load GTK dengan relasi wilayah untuk comparison alamat
            $gtk = Gtk::with(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
                ->findOrFail($id);

            // Check permission
            if (! auth()->user()->can('edit-gtk')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melakukan sinkronisasi',
                ], 403);
            }

            // Check if GTK has NIP
            if (empty($gtk->nip)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GTK ini tidak memiliki NIP. Sinkronisasi tidak dapat dilakukan.',
                ]);
            }

            // Perform sync
            $result = $syncService->syncGtkData($gtk, auth()->id());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('GtkController: Sync Kemenag error', [
                'gtk_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply data Kemenag ke data lokal GTK
     */
    public function applyKemenagData($id, GtkKemenagSyncService $syncService)
    {
        try {
            $gtk = Gtk::with('kemenagSync')->findOrFail($id);

            // Check permission
            if (! auth()->user()->can('edit-gtk')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menerapkan data',
                ], 403);
            }

            // Check if sync data exists
            if (! $gtk->kemenagSync) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data sinkronisasi. Silakan lakukan sinkronisasi terlebih dahulu.',
                ], 404);
            }

            // Apply data
            $result = $syncService->applyKemenagDataToLocal(
                $gtk->kemenagSync,
                auth()->id()
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('GtkController: Apply Kemenag data error', [
                'gtk_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menerapkan data: '.$e->getMessage(),
            ], 500);
        }
    }
}
