<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaExport;
use App\Exports\SiswaPerRombelExport;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\DokumenSiswa;
use App\Models\Sekolah;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-siswa');

        $siswaQuery = $this->baseSiswaQuery();
        $this->applyStatisticsDrilldownFilters($siswaQuery, $request);

        // Statistics (dengan filter role)
        $stats = [
            'total_siswa' => (clone $siswaQuery)->count(),
            'laki_laki' => (clone $siswaQuery)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $siswaQuery)->where('jenis_kelamin', 'P')->count(),
            'data_lengkap' => (clone $siswaQuery)->where('data_diri_completed', true)->where('data_ortu_completed', true)->count(),
        ];

        // Filter options
        $tingkatOptions = [
            10 => 'Kelas X',
            11 => 'Kelas XI',
            12 => 'Kelas XII',
        ];

        $contextScope = $this->buildStatisticsContext($request);
        $contextQuery = collect($request->only([
            'school_npsn',
            'school_name',
            'education_form',
            'school_city_name',
            'school_province_name',
            'address_scope',
            'address_name',
            'province_name',
            'status',
            'login_status',
            'npsn_status',
            'tingkat',
            'kelas_id',
        ]))->filter(fn ($value) => filled($value))->all();

        return view('admin.siswa.index', compact('stats', 'tingkatOptions', 'contextScope', 'contextQuery'));
    }

    /**
     * Get filtered stats for cards (AJAX)
     */
    public function stats(Request $request)
    {
        $this->authorize('view-siswa');

        $query = $this->baseSiswaQuery();
        $this->applyStatisticsDrilldownFilters($query, $request);

        // Apply same filters as data()
        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }
        if ($request->filled('tingkat')) {
            if ($request->tingkat === 'tanpa_rombel') {
                $query->whereDoesntHave('kelasAktif');
            } else {
                $query->whereHas('kelasAktif', fn($q) => $q->where('kelas.tingkat', $request->tingkat));
            }
        }
        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasAktif', fn($q) => $q->where('kelas.id', $request->kelas_id));
        }
        if ($request->filled('status')) {
            if ($request->status == 'lengkap') {
                $query->where('data_diri_completed', true)->where('data_ortu_completed', true);
            } elseif ($request->status == 'belum') {
                $query->where(fn($q) => $q->where('data_diri_completed', false)->orWhere('data_ortu_completed', false));
            }
        }
        if ($request->filled('emis_status')) {
            $query->where('emis_registered', $request->emis_status === 'sudah');
        }

        return response()->json([
            'total_siswa' => (clone $query)->count(),
            'laki_laki' => (clone $query)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $query)->where('jenis_kelamin', 'P')->count(),
            'data_lengkap' => (clone $query)->where('data_diri_completed', true)->where('data_ortu_completed', true)->count(),
        ]);
    }

    /**
     * Export siswa data to Excel
     */
    public function export(Request $request)
    {
        $this->authorize('view-siswa');

        $query = $this->baseSiswaQuery();
        $this->applyStatisticsDrilldownFilters($query, $request);

        // Apply same filters as DataTable
        if ($request->filled('jenis_kelamin')) {
            $query->where('siswa.jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('tingkat') && $request->tingkat !== 'tanpa_rombel') {
            $query->whereHas('kelasAktif', function ($q) use ($request) {
                $q->where('kelas.tingkat', $request->tingkat);
            });
        } elseif ($request->tingkat === 'tanpa_rombel') {
            $query->whereDoesntHave('kelasAktif');
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasAktif', function ($q) use ($request) {
                $q->where('kelas.id', $request->kelas_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'lengkap') {
                $query->where('siswa.data_diri_completed', true)->where('siswa.data_ortu_completed', true);
            } elseif ($request->status === 'belum') {
                $query->where(function ($q) {
                    $q->where('siswa.data_diri_completed', false)->orWhere('siswa.data_ortu_completed', false);
                });
            }
        }
        if ($request->filled('emis_status')) {
            $query->where('siswa.emis_registered', $request->emis_status === 'sudah');
        }

        $rows = $query->with(['user', 'ortu', 'kelasAktif'])->get();

        if (!$request->filled('kelas_id') && $request->tingkat !== 'tanpa_rombel') {
            $tingkatLabel = $request->filled('tingkat') ? 'tingkat-' . $request->tingkat : 'semua-tingkat';
            $filename = 'data-siswa-' . $tingkatLabel . '-per-rombel-' . now()->format('Ymd-His') . '.xlsx';
            $tingkat = $request->filled('tingkat') ? (int) $request->tingkat : null;

            return Excel::download(new SiswaPerRombelExport($rows, 'Data Siswa', $tingkat), $filename);
        }

        $filename = 'data-siswa-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new SiswaExport($rows), $filename);
    }

    /**
     * Get siswa data for DataTables
     */
    public function data(Request $request)
    {
        $this->authorize('view-siswa');
        
        $siswa = Siswa::with(['user', 'ortu', 'kelasAktif'])
            ->select(['id', 'nisn', 'nomor_tes', 'nama_lengkap', 'jenis_kelamin', 'foto_profile', 'user_id', 'data_ortu_completed', 'data_diri_completed', 'verval_ijazah', 'verval_ijazah_at', 'emis_registered', 'emis_registered_at', 'created_at']);

        $this->applyRoleScope($siswa);
        $this->applyStatisticsDrilldownFilters($siswa, $request);

        // Filter by Jenis Kelamin
        if ($request->filled('jenis_kelamin')) {
            $siswa->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter by Tingkat (through kelas aktif)
        if ($request->filled('tingkat')) {
            if ($request->tingkat === 'tanpa_rombel') {
                // Filter siswa yang tidak punya kelas aktif
                $siswa->whereDoesntHave('kelasAktif');
            } else {
                // Filter by tingkat normal
                $siswa->whereHas('kelasAktif', function($q) use ($request) {
                    $q->where('kelas.tingkat', $request->tingkat);
                });
            }
        }

        // Filter by Kelas
        if ($request->filled('kelas_id')) {
            $siswa->whereHas('kelasAktif', function($q) use ($request) {
                $q->where('kelas.id', $request->kelas_id);
            });
        }

        // Filter by Status Data
        if ($request->filled('status')) {
            if ($request->status == 'lengkap') {
                $siswa->where('data_diri_completed', true)
                      ->where('data_ortu_completed', true);
            } elseif ($request->status == 'belum') {
                $siswa->where(function($q) {
                    $q->where('data_diri_completed', false)
                      ->orWhere('data_ortu_completed', false);
                });
            }
        }

        // Filter by EMIS registration flag
        if ($request->filled('emis_status')) {
            $siswa->where('emis_registered', $request->emis_status === 'sudah');
        }

        // Filter by Login Status
        if ($request->filled('login_status')) {
            if ($request->login_status === 'sudah') {
                $siswa->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.user_id', 'siswa.user_id')
                        ->where('activity_logs.activity_type', 'login');
                });
            } elseif ($request->login_status === 'belum') {
                $siswa->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.user_id', 'siswa.user_id')
                        ->where('activity_logs.activity_type', 'login');
                });
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $siswa->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nomor_tes', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%");
            });
        }

        $totalRecords = (clone $this->baseSiswaQuery())->count();
        $filteredRecords = $siswa->count();
        
        // Pagination
        if ($request->has('start') && $request->has('length')) {
            $length = max(10, min((int) $request->length, 100));
            $siswa->skip(max(0, (int) $request->start))->take($length);
        }

        // Ordering
        if ($request->has('order')) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            
            // Map column index to actual column names
            // Columns: 0=foto, 1=nama_nisn, 2=jk, 3=kelas, 4=status_ortu, 5=status_diri, 6=verval, 7=emis, 8=created_at, 9=actions
            $columns = [
                1 => 'nama_lengkap',
                2 => 'jenis_kelamin',
                8 => 'siswa.created_at',
            ];

            // Handle Kelas ordering (index 3, needs join)
            if ($orderColumnIndex == 3) {
                $siswa->leftJoin('siswa_kelas', function($join) {
                    $join->on('siswa.id', '=', 'siswa_kelas.siswa_id')
                         ->where('siswa_kelas.status', '=', 'aktif')
                         ->whereNull('siswa_kelas.deleted_at');
                })
                ->leftJoin('kelas', 'siswa_kelas.kelas_id', '=', 'kelas.id')
                ->orderBy('kelas.nama_kelas', $orderDirection)
                ->select('siswa.*')
                ->distinct();
            }
            // Standard columns
            elseif (isset($columns[$orderColumnIndex])) {
                $siswa->orderBy($columns[$orderColumnIndex], $orderDirection);
            } else {
                $siswa->latest();
            }
        } else {
            $siswa->latest();
        }

        $data = $siswa->get()->map(function($item) {
            // Get kelas aktif
            $kelasAktif = $item->kelasAktif()->first();
            $aktifRecord = $kelasAktif ? null : $item->siswaKelasRecords()
                ->where('status', 'aktif')
                ->latest('created_at')
                ->first();
            $kelasNama = $kelasAktif
                ? $kelasAktif->nama_kelas
                : '<span class="text-muted small">' . ($aktifRecord?->tingkat ? 'Tingkat ' . e($aktifRecord->tingkat) . ' - ' : '') . 'Tanpa Rombel</span>';

            $jk = $item->jenis_kelamin;
            $jkBadge = $jk === 'L'
                ? '<span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.78rem;"><i class="fas fa-mars"></i></span>'
                : '<span class="badge" style="background:#fce7f3;color:#be185d;font-size:.78rem;"><i class="fas fa-venus"></i></span>';

            $namaNisn = '<div class="font-weight-600 text-dark" style="font-size:.88rem;line-height:1.3;">'
                . e($item->nama_lengkap)
                . '</div><small class="text-muted" style="font-size:.78rem;">NISN ' . e($item->nisn) . '</small>'
                . ($item->nomor_tes ? '<br><small class="text-primary" style="font-size:.75rem;">No. Tes ' . e($item->nomor_tes) . '</small>' : '');

            return [
                'id' => $item->id,
                'foto' => $this->getFotoColumn($item),
                'nama_nisn' => $namaNisn,
                'jenis_kelamin' => $jkBadge,
                'kelas' => $kelasNama,
                'status_ortu' => $this->getStatusOrtu($item),
                'status_diri' => $item->data_diri_completed
                    ? '<span class="badge badge-success">Lengkap</span>'
                    : '<span class="badge badge-danger">Belum</span>',
                'verval_ijazah' => $this->getVervalIjazahBadge($item),
                'emis_registered' => $this->getEmisRegisteredBadge($item),
                'created_at' => $item->created_at->format('d/m/Y'),
                'actions' => $this->getActionButtons($item)
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    private function getVervalIjazahBadge(Siswa $siswa): string
    {
        $toggleUrl = route('admin.siswa.toggle-verval-ijazah', $siswa);

        if ($siswa->verval_ijazah) {
            $tgl = $siswa->verval_ijazah_at ? $siswa->verval_ijazah_at->format('d/m/Y') : '';
            $title = "Sudah Verval" . ($tgl ? " ({$tgl})" : "") . " — Klik untuk batalkan";
            return '<button class="btn btn-success btn-xs btn-toggle-verval" 
                data-url="' . $toggleUrl . '" 
                title="' . e($title) . '">'
                . '<i class="fas fa-check-circle"></i> Sudah</button>';
        }

        return '<button class="btn btn-outline-secondary btn-xs btn-toggle-verval" 
            data-url="' . $toggleUrl . '" 
            title="Klik untuk tandai sudah verval ijazah">'
            . '<i class="far fa-circle"></i> Belum</button>';
    }

    /**
     * Toggle verval ijazah status
     */
    public function toggleVervalIjazah(Siswa $siswa)
    {
        $this->authorize('edit-siswa');

        $siswa->verval_ijazah = !$siswa->verval_ijazah;
        $siswa->verval_ijazah_at = $siswa->verval_ijazah ? now() : null;
        $siswa->verval_ijazah_by = $siswa->verval_ijazah ? Auth::id() : null;
        $siswa->save();

        return response()->json([
            'success' => true,
            'verval_ijazah' => $siswa->verval_ijazah,
            'badge' => $this->getVervalIjazahBadge($siswa),
        ]);
    }

    private function getEmisRegisteredBadge(Siswa $siswa): string
    {
        $toggleUrl = route('admin.siswa.toggle-emis-registered', $siswa);

        if ($siswa->emis_registered) {
            $tgl = $siswa->emis_registered_at ? $siswa->emis_registered_at->format('d/m/Y H:i') : '';
            $title = "Sudah masuk EMIS" . ($tgl ? " ({$tgl})" : "") . " - Klik untuk batalkan";

            return '<button class="btn btn-success btn-xs btn-toggle-emis"
                data-url="' . e($toggleUrl) . '"
                title="' . e($title) . '">'
                . '<i class="fas fa-check-circle"></i> Sudah</button>';
        }

        return '<button class="btn btn-outline-secondary btn-xs btn-toggle-emis"
            data-url="' . e($toggleUrl) . '"
            title="Klik jika siswa sudah diinput/masuk ke EMIS">'
            . '<i class="far fa-circle"></i> Belum</button>';
    }

    public function toggleEmisRegistered(Siswa $siswa)
    {
        $this->authorize('edit-siswa');

        $siswa->emis_registered = !$siswa->emis_registered;
        $siswa->emis_registered_at = $siswa->emis_registered ? now() : null;
        $siswa->emis_registered_by = $siswa->emis_registered ? Auth::id() : null;
        $siswa->save();

        return response()->json([
            'success' => true,
            'emis_registered' => $siswa->emis_registered,
            'badge' => $this->getEmisRegisteredBadge($siswa),
        ]);
    }

    private function getStatusOrtu(Siswa $siswa): string
    {
        $ortu = $siswa->ortu;

        // Tidak ada record ortu sama sekali → Belum Lengkap
        if (!$ortu) {
            return '<span class="badge badge-danger">Belum Lengkap</span>';
        }

        // Data "benar-benar lengkap": sudah diverifikasi admin/siswa via flag
        if ($siswa->data_ortu_completed) {
            // Cek apakah field kritis benar-benar terisi (bukan cuma nama)
            $fullyFilled = !empty($ortu->status_ayah)
                && !empty($ortu->status_ibu)
                && !empty($ortu->alamat_ortu)
                && !empty($ortu->kodepos);

            if ($fullyFilled) {
                return '<span class="badge badge-success">Lengkap</span>';
            }
            // Flag true tapi field kritis kosong → dari import EMIS (hanya nama)
            return '<span class="badge badge-warning text-dark">Sebagian</span>';
        }

        // Flag false tapi ada nama ayah/ibu → sebagian terisi (import EMIS)
        if (!empty($ortu->nama_ayah) || !empty($ortu->nama_ibu)) {
            return '<span class="badge badge-warning text-dark">Sebagian</span>';
        }

        return '<span class="badge badge-danger">Belum</span>';
    }

    private function getFotoColumn(Siswa $siswa): string
    {
        $fallbackUrl = e($this->buildFallbackAvatar($siswa));
        $studentName = e($siswa->nama_lengkap);

        if (!$siswa->foto_profile) {
            return '<img src="' . $fallbackUrl . '" class="img-circle" alt="' . $studentName . '"
                style="width:36px;height:36px;object-fit:cover;opacity:.7;">';
        }

        $previewUrl = e($siswa->foto_profile_url);
        $downloadUrl = e(route('admin.siswa.download-foto', $siswa));

        return '<button type="button" class="btn btn-link p-0 js-preview-foto border-0"
            data-preview-url="' . $previewUrl . '"
            data-download-url="' . $downloadUrl . '"
            data-student-name="' . $studentName . '"
            title="Klik untuk preview foto">
            <img src="' . $previewUrl . '" alt="Foto ' . $studentName . '"
                class="img-circle shadow-sm"
                onerror="this.onerror=null;this.src=\'' . $fallbackUrl . '\';"    
                style="width:36px;height:36px;object-fit:cover;">
        </button>';
    }

    private function getActionButtons($item)
    {
        $user = auth()->user();
        $buttons = '<div class="btn-group" role="group">';
        
        // View button - always shown if can view siswa
        if ($user->can('view-siswa')) {
            $buttons .= '
                <button type="button" class="btn btn-info btn-sm" onclick="showSiswa(\''.$item->id.'\')">
                    <i class="fas fa-eye"></i>
                </button>';
        }
        
        // Edit button
        if ($user->can('edit-siswa')) {
            $buttons .= '
                <button type="button" class="btn btn-warning btn-sm" onclick="editSiswa(\''.$item->id.'\')">
                    <i class="fas fa-edit"></i>
                </button>';
        }
        
        // Reset Password button
        if ($user->can('reset-password-siswa')) {
            $buttons .= '
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(\''.$item->id.'\')">
                    <i class="fas fa-key"></i>
                </button>';
        }
        
        // Delete button
        if ($user->can('delete-siswa')) {
            $buttons .= '
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSiswa(\''.$item->id.'\')">
                    <i class="fas fa-trash"></i>
                </button>';
        }
        
        $buttons .= '</div>';
        
        return $buttons;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-siswa');

        try {
            // Log incoming request for debugging
            Log::info('Attempting to create siswa', [
                'request_data' => $request->all()
            ]);

            $request->validate([
                'nisn' => 'required|string|unique:siswa,nisn',
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:L,P',
            ]);

            DB::beginTransaction();
            
            // Default password is NISN
            $defaultPassword = $request->nisn;
            
            // Create user account for siswa
            $user = User::create([
                'name' => $request->nama_lengkap,
                'username' => $request->nisn,
                'email' => $request->nisn . '@student.man1metro.sch.id',
                'password' => Hash::make($defaultPassword),
                'role' => 'siswa',
                'is_first_login' => true,
            ]);
            
            // Save readable password (encrypted)
            $user->readable_password = $defaultPassword;
            $user->save();

            Log::info('User created successfully', ['user_id' => $user->id]);

            // Create siswa record
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            Log::info('Siswa created successfully', ['siswa_id' => $siswa->id]);

            // Create empty ortu record so siswa can fill it later
            \App\Models\Ortu::create([
                'siswa_id' => $siswa->id,
            ]);

            Log::info('Empty ortu record created');

            DB::commit();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'create',
                'model_type' => 'App\\Models\\Siswa',
                'model_id' => $siswa->id,
                'description' => "Membuat data siswa baru: {$request->nama_lengkap} (NISN: {$request->nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Siswa creation completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil ditambahkan',
                'data' => $siswa
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating siswa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $this->authorize('view-siswa');

        $siswa->load([
            'user', 
            'ortu.provinsi', 
            'ortu.kabupaten', 
            'ortu.kecamatan', 
            'ortu.kelurahan',
            'creator', 
            'updater', 
            'sekolahAsal',
            'kelasAktif',
            'dokumen' => fn($query) => $query->latest(),
        ]);

        $riwayatPerubahan = $this->getStudentActivityLogs($siswa);
        
        // Check if request wants JSON (AJAX) or HTML (direct access)
        if (request()->wantsJson() || request()->ajax()) {
            // Format data for display
            $data = $siswa->toArray();
            $data['created_by_name'] = $siswa->creator ? $siswa->creator->name : 'System';
            $data['updated_by_name'] = $siswa->updater ? $siswa->updater->name : '-';
            
            // Add readable password for admin (encrypted in database)
            if ($siswa->user) {
                $data['user']['readable_password'] = $siswa->user->readable_password;
            }
            
            // Ensure nested relations are properly serialized
            if ($siswa->ortu) {
                $data['ortu'] = [
                    ...$data['ortu'],
                    'provinsi' => $siswa->ortu->provinsi ? $siswa->ortu->provinsi->toArray() : null,
                    'kabupaten' => $siswa->ortu->kabupaten ? $siswa->ortu->kabupaten->toArray() : null,
                    'kecamatan' => $siswa->ortu->kecamatan ? $siswa->ortu->kecamatan->toArray() : null,
                    'kelurahan' => $siswa->ortu->kelurahan ? $siswa->ortu->kelurahan->toArray() : null,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
        
        // Return HTML view for direct browser access
        return view('admin.siswa.show', compact('siswa', 'riwayatPerubahan'));
    }

    /**
     * Get quick detail for modal display
     */
    public function quickDetail(Siswa $siswa)
    {
        $this->authorize('view-siswa');

        return response()->json([
            'success' => true,
            'siswa' => [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'nomor_tes' => $siswa->nomor_tes,
                'nis' => $siswa->nis,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'tempat_lahir' => $siswa->tempat_lahir,
                'tanggal_lahir_formatted' => $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d F Y') : null,
                'nomor_hp' => $siswa->nomor_hp,
                'email' => $siswa->user?->email,
                'alamat_siswa' => $siswa->alamat_siswa,
                'nama_sekolah_asal' => $siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal,
                'foto_profile_url' => $siswa->foto_profile_url,
            ]
        ]);
    }

    public function downloadFoto(Siswa $siswa)
    {
        $this->authorize('view-siswa');

        $normalizedPath = StorageHelper::normalizePublicPath($siswa->foto_profile);

        if (!$normalizedPath || !Storage::disk('public')->exists($normalizedPath)) {
            return redirect()->route('admin.siswa.index')
                ->with('error', 'Foto siswa tidak ditemukan atau belum diunggah.');
        }

        $extension = pathinfo($normalizedPath, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'foto-siswa-' . $siswa->nisn . '-' . \Illuminate\Support\Str::slug($siswa->nama_lengkap) . '.' . $extension;

        return Storage::disk('public')->download($normalizedPath, $filename);
    }

    private function buildFallbackAvatar(Siswa $siswa): string
    {
        $name = urlencode($siswa->nama_lengkap ?? 'Siswa');
        $background = $siswa->jenis_kelamin === 'L' ? '3498db' : 'e83e8c';

        return "https://ui-avatars.com/api/?name={$name}&size=100&background={$background}&color=FFFFFF&font-size=0.45&bold=true";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $this->authorize('edit-siswa');

        $request->validate([
            'nisn' => ['required', 'string', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        DB::beginTransaction();
        try {
            $oldSnapshot = [
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'username' => $siswa->user?->username,
            ];

            // Update siswa
            $siswa->update([
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            // Update user
            $siswa->user->update([
                'name' => $request->nama_lengkap,
                'username' => $request->nisn,
            ]);

            $newSnapshot = [
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'username' => $request->nisn,
            ];

            DB::commit();

            ActivityLogService::logChanges(
                'admin_update_siswa',
                $siswa,
                $oldSnapshot,
                $newSnapshot,
                "Admin memperbarui data siswa: {$request->nama_lengkap} (NISN: {$request->nisn})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diperbarui',
                'data' => $siswa
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $this->authorize('delete-siswa');

        DB::beginTransaction();
        try {
            $nama = $siswa->nama_lengkap;
            $nisn = $siswa->nisn;
            $siswaId = $siswa->id;

            // Keluarkan dari semua kelas aktif
            \App\Models\SiswaKelas::where('siswa_id', $siswaId)
                ->where('status', 'aktif')
                ->update([
                    'status' => 'keluar',
                    'tanggal_keluar' => now()->toDateString(),
                    'catatan_perpindahan' => 'Siswa dihapus dari sistem',
                ]);

            // Clear kelas_saat_ini_id
            $siswa->kelas_saat_ini_id = null;
            $siswa->save();

            // Soft-delete user jika ada
            if ($siswa->user) {
                $siswa->user->delete();
            }

            // Soft-delete siswa
            $siswa->delete();

            // Log activity (di dalam transaksi agar rollback jika gagal)
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'delete',
                'model_type' => 'App\\Models\\Siswa',
                'model_id' => $siswaId,
                'description' => "Menghapus data siswa: {$nama} (NISN: {$nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password siswa
     */
    public function resetPassword(Siswa $siswa)
    {
        $this->authorize('edit-siswa');

        try {
            $user = $siswa->user;
            $defaultPassword = $siswa->nisn;

            $user->password = Hash::make($defaultPassword);
            $user->is_first_login = true;
            $user->password_reset_at = now();
            $user->password_reset_by = Auth::user()->name;
            $user->readable_password = $defaultPassword;
            $user->save();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'reset_password',
                'model_type' => 'App\\Models\\Siswa',
                'model_id' => $siswa->id,
                'description' => "Reset password siswa: {$siswa->nama_lengkap} (NISN: {$siswa->nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password siswa berhasil direset ke NISN',
                'default_password' => $defaultPassword,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dokumen siswa
     */
    public function getDokumen(Siswa $siswa)
    {
        $this->authorize('view-siswa');

        try {
            $dokumen = $siswa->dokumen()->latest()->get()->map(function($dok) use ($siswa) {
                return [
                    'id' => $dok->id,
                    'jenis_dokumen' => $dok->jenis_dokumen,
                    'jenis_dokumen_label' => $dok->getJenisDokumenLabel(),
                    'nama_file' => $dok->nama_file,
                    'file_url' => $dok->getFileUrl(),
                    'mime_type' => $dok->mime_type,
                    'file_size' => $dok->file_size,
                    'file_size_formatted' => $dok->getFileSizeFormatted(),
                    'keterangan' => $dok->keterangan,
                    'created_at' => $dok->created_at,
                    'nama_siswa' => $siswa->nama_lengkap,
                    'download_jpg_url' => route('admin.siswa.dokumen.download-jpg', ['siswaId' => $siswa->id, 'dokumenId' => $dok->id]),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $dokumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download dokumen siswa as JPG
     */
    public function downloadDokumenAsJpg(string $siswaId, string $dokumenId)
    {
        $this->authorize('view-siswa');

        $dokumen = DokumenSiswa::where('id', $dokumenId)
            ->where('siswa_id', $siswaId)
            ->firstOrFail();

        // Gunakan withTrashed agar tetap bisa ambil nama meski siswa sudah di-soft-delete
        $siswa = \App\Models\Siswa::withTrashed()->find($dokumen->siswa_id);
        if (!$siswa) {
            abort(404, 'Data siswa tidak ditemukan');
        }

        // Gunakan disk yang sama seperti preview — bukan getSecureFilePath()
        $disk = $dokumen->storage_disk ?? \App\Helpers\StorageHelper::getDiskFromPath($dokumen->file_path);

        if (!Storage::disk($disk)->exists($dokumen->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        $namaSlug = strtoupper(str_replace([' ', '/'], ['_', '_'], $siswa->nama_lengkap));
        $jenisSlug = $dokumen->jenis_dokumen;
        $filename = "{$namaSlug}-{$jenisSlug}.jpg";

        $mime = $dokumen->mime_type ?? 'application/octet-stream';

        // Tulis ke temp file agar bisa diproses pdftoppm / GD
        $tmpInput = tempnam(sys_get_temp_dir(), 'simansa_in_');
        file_put_contents($tmpInput, Storage::disk($disk)->get($dokumen->file_path));

        // Helper: flush buffer + stream file as attachment, then cleanup
        $streamAndExit = function (string $outPath) use ($filename) {
            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: image/jpeg');
            header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
            header('Content-Length: ' . filesize($outPath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            readfile($outPath);
            @unlink($outPath);
            exit;
        };

        if (str_contains($mime, 'pdf')) {
            $tmpPrefix = sys_get_temp_dir() . '/simansa_' . uniqid();
            $cmd = sprintf(
                'pdftoppm -jpeg -r 150 -f 1 -l 1 %s %s',
                escapeshellarg($tmpInput),
                escapeshellarg($tmpPrefix)
            );
            exec($cmd, $output, $retCode);
            @unlink($tmpInput);

            $generated = glob($tmpPrefix . '*.jpg');
            if (empty($generated)) {
                Log::error('PDF to JPG conversion failed', ['output' => $output, 'retCode' => $retCode]);
                abort(500, 'Gagal konversi PDF ke JPG');
            }

            $streamAndExit($generated[0]);
        }

        if (in_array($mime, ['image/png', 'image/webp', 'image/gif', 'image/bmp'])) {
            $image = @imagecreatefromstring(file_get_contents($tmpInput));
            @unlink($tmpInput);
            if (!$image) {
                abort(422, 'Format gambar tidak dapat diproses');
            }
            $tmpOut = tempnam(sys_get_temp_dir(), 'simansa_img_') . '.jpg';
            imagejpeg($image, $tmpOut, 90);
            imagedestroy($image);

            $streamAndExit($tmpOut);
        }

        // Already JPEG — rename temp dan serve
        $tmpJpg = $tmpInput . '.jpg';
        rename($tmpInput, $tmpJpg);
        $streamAndExit($tmpJpg);
    }

    /**
     * Get kelas by tingkat (for filter)
     */
    public function getKelasByTingkat(Request $request)
    {
        $tingkat = $request->get('tingkat');
        
        if (!$tingkat) {
            return response()->json([]);
        }

        $kelas = \App\Models\Kelas::where('tingkat', $tingkat)
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'kode_kelas'])
            ->map(function($k) {
                return [
                    'id' => $k->id,
                    'text' => $k->nama_lengkap
                ];
            });

        return response()->json($kelas);
    }

    private function getStudentActivityLogs(Siswa $siswa)
    {
        return ActivityLog::with('user.roles')
            ->where(function ($activityQuery) use ($siswa) {
                $activityQuery->where(function ($studentQuery) use ($siswa) {
                    $studentQuery->where('model_type', Siswa::class)
                        ->where('model_id', $siswa->id);
                });

                if ($siswa->ortu) {
                    $activityQuery->orWhere(function ($ortuQuery) use ($siswa) {
                        $ortuQuery->where('model_type', Ortu::class)
                            ->where('model_id', $siswa->ortu->id);
                    });
                }

                if ($siswa->user_id) {
                    $activityQuery->orWhere(function ($userQuery) use ($siswa) {
                        $userQuery->where('model_type', User::class)
                            ->where('model_id', $siswa->user_id);
                    });
                }
            })
            ->latest()
            ->limit(40)
            ->get();
    }

    private function baseSiswaQuery()
    {
        $query = Siswa::query();
        $this->applyRoleScope($query);

        return $query;
    }

    private function applyRoleScope($query)
    {
        $user = Auth::user();

        if ($user->hasRole('Wali Kelas') && !$user->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah'])) {
            $kelasIds = \App\Models\Kelas::where('wali_kelas_id', $user->id)->pluck('id');

            if ($kelasIds->isNotEmpty()) {
                $query->whereHas('kelasAktif', function ($q) use ($kelasIds) {
                    $q->whereIn('kelas.id', $kelasIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    private function applyStatisticsDrilldownFilters($query, Request $request)
    {
        if ($request->input('npsn_status') === 'kosong') {
            $query->where(function ($npsnQuery) {
                $npsnQuery->whereNull('siswa.npsn_asal_sekolah')
                    ->orWhereRaw("TRIM(COALESCE(siswa.npsn_asal_sekolah, '')) = ''");
            });
        }

        if ($request->filled('school_npsn') || $request->filled('school_name') || $request->filled('school_city_name') || $request->filled('school_province_name') || $request->filled('education_form')) {
            $query->leftJoin('sekolah as sekolah_asal_filter', 'sekolah_asal_filter.npsn', '=', 'siswa.npsn_asal_sekolah');

            if ($request->filled('school_npsn')) {
                $query->where('siswa.npsn_asal_sekolah', $request->school_npsn);
            }

            if ($request->filled('school_name')) {
                $query->where(function ($schoolQuery) use ($request) {
                    $schoolQuery->where('sekolah_asal_filter.nama', $request->school_name);

                    if ($request->filled('school_npsn')) {
                        $schoolQuery->orWhere('siswa.npsn_asal_sekolah', $request->school_npsn);
                    }
                });
            }

            if ($request->filled('school_city_name')) {
                $query->where('sekolah_asal_filter.kabupaten_kota', $request->school_city_name);
            }

            if ($request->filled('school_province_name')) {
                $query->where('sekolah_asal_filter.provinsi', $request->school_province_name);
            }

            if ($request->filled('education_form')) {
                $query->where('sekolah_asal_filter.bentuk_pendidikan', $request->education_form);
            }
        }

        if ($request->filled('address_scope') && $request->filled('address_name')) {
            $query->leftJoin('ortu as ortu_siswa_filter', 'ortu_siswa_filter.siswa_id', '=', 'siswa.id');

            if ($request->address_scope === 'province') {
                $query->leftJoin('indonesia_provinces as siswa_prov_filter', 'siswa_prov_filter.code', '=', 'siswa.provinsi_id_siswa')
                    ->leftJoin('indonesia_provinces as ortu_prov_filter', 'ortu_prov_filter.code', '=', 'ortu_siswa_filter.provinsi_id')
                    ->whereRaw(
                        'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_prov_filter.name ELSE siswa_prov_filter.name END = ?',
                        [$request->address_name]
                    );
            }

            if ($request->address_scope === 'city') {
                $query->leftJoin('indonesia_cities as siswa_city_filter', 'siswa_city_filter.code', '=', 'siswa.kabupaten_id_siswa')
                    ->leftJoin('indonesia_cities as ortu_city_filter', 'ortu_city_filter.code', '=', 'ortu_siswa_filter.kabupaten_id')
                    ->whereRaw(
                        'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_city_filter.name ELSE siswa_city_filter.name END = ?',
                        [$request->address_name]
                    );
            }

            if ($request->address_scope === 'district') {
                $query->leftJoin('indonesia_districts as siswa_district_filter', 'siswa_district_filter.code', '=', 'siswa.kecamatan_id_siswa')
                    ->leftJoin('indonesia_districts as ortu_district_filter', 'ortu_district_filter.code', '=', 'ortu_siswa_filter.kecamatan_id')
                    ->whereRaw(
                        'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_district_filter.name ELSE siswa_district_filter.name END = ?',
                        [$request->address_name]
                    );
            }

            if ($request->filled('province_name') && in_array($request->address_scope, ['city', 'district'], true)) {
                $query->leftJoin('indonesia_provinces as siswa_prov_scope_filter', 'siswa_prov_scope_filter.code', '=', 'siswa.provinsi_id_siswa')
                    ->leftJoin('indonesia_provinces as ortu_prov_scope_filter', 'ortu_prov_scope_filter.code', '=', 'ortu_siswa_filter.provinsi_id')
                    ->whereRaw(
                        'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_prov_scope_filter.name ELSE siswa_prov_scope_filter.name END = ?',
                        [$request->province_name]
                    );
            }
        }

        return $query->select('siswa.*')->distinct();
    }

    private function buildStatisticsContext(Request $request): ?array
    {
        if ($request->input('npsn_status') === 'kosong') {
            return [
                'title' => 'Filter Statistik: NPSN Asal Sekolah',
                'description' => 'Siswa yang NPSN asal sekolahnya masih kosong',
            ];
        }

        if ($request->filled('login_status')) {
            $label = $request->login_status === 'sudah' ? 'Sudah Pernah Login' : 'Belum Pernah Login';
            return [
                'title' => 'Filter Statistik: Status Login',
                'description' => $label,
            ];
        }

        if ($request->filled('status')) {
            $label = $request->status === 'lengkap' ? 'Data Lengkap' : 'Belum Lengkap';
            return [
                'title' => 'Filter Statistik: Status Data',
                'description' => $label,
            ];
        }

        if ($request->filled('school_npsn') || $request->filled('school_name')) {
            $sekolah = $request->filled('school_npsn')
                ? Sekolah::find($request->school_npsn)
                : null;
            $schoolName = $sekolah?->nama ?: $request->school_name;

            return [
                'title' => 'Filter Statistik: Sekolah Asal',
                'description' => $schoolName,
                'meta' => [
                    'npsn' => $sekolah?->npsn ?: $request->school_npsn,
                    'nsm' => $sekolah?->nsm,
                ],
                'detail' => trim(collect([
                    $sekolah?->bentuk_pendidikan ?: $request->education_form,
                    $request->school_city_name,
                    $request->school_province_name,
                ])->filter()->implode(' | ')),
            ];
        }

        if ($request->filled('address_scope') && $request->filled('address_name')) {
            $scopeLabels = [
                'province' => 'Provinsi',
                'city' => 'Kabupaten / Kota',
                'district' => 'Kecamatan',
            ];

            return [
                'title' => 'Filter Statistik: Sebaran Alamat',
                'description' => ($scopeLabels[$request->address_scope] ?? 'Wilayah') . ' - ' . $request->address_name . ($request->filled('province_name') ? ' | ' . $request->province_name : ''),
            ];
        }

        if ($request->filled('school_city_name')) {
            return [
                'title' => 'Filter Statistik: Wilayah Asal Sekolah',
                'description' => trim(collect([$request->school_city_name, $request->school_province_name])->filter()->implode(', ')),
            ];
        }

        if ($request->filled('education_form')) {
            return [
                'title' => 'Filter Statistik: Bentuk Pendidikan',
                'description' => $request->education_form,
            ];
        }

        return null;
    }
}
