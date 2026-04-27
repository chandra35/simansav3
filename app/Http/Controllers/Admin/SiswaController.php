<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-siswa');

        $user = Auth::user();
        
        // Base query
        $siswaQuery = Siswa::query();
        
        // FILTER BY ROLE: Wali Kelas hanya lihat siswa di kelasnya
        if ($user->hasRole('Wali Kelas') && !$user->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah'])) {
            $kelasIds = \App\Models\Kelas::where('wali_kelas_id', $user->id)->pluck('id');
            
            if ($kelasIds->isNotEmpty()) {
                $siswaQuery->whereHas('kelasAktif', function($q) use ($kelasIds) {
                    $q->whereIn('kelas.id', $kelasIds);
                });
            } else {
                // Jika tidak punya kelas, set count ke 0
                $siswaQuery->whereRaw('1 = 0');
            }
        }

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

        return view('admin.siswa.index', compact('stats', 'tingkatOptions'));
    }

    /**
     * Get filtered stats for cards (AJAX)
     */
    public function stats(Request $request)
    {
        $this->authorize('view-siswa');

        $user = Auth::user();
        $query = Siswa::query();

        // Role-based filter
        if ($user->hasRole('Wali Kelas') && !$user->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah'])) {
            $kelasIds = \App\Models\Kelas::where('wali_kelas_id', $user->id)->pluck('id');
            if ($kelasIds->isNotEmpty()) {
                $query->whereHas('kelasAktif', fn($q) => $q->whereIn('kelas.id', $kelasIds));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

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

        return response()->json([
            'total_siswa' => (clone $query)->count(),
            'laki_laki' => (clone $query)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $query)->where('jenis_kelamin', 'P')->count(),
            'data_lengkap' => (clone $query)->where('data_diri_completed', true)->where('data_ortu_completed', true)->count(),
        ]);
    }

    /**
     * Get siswa data for DataTables
     */
    public function data(Request $request)
    {
        $this->authorize('view-siswa');
        
        $user = Auth::user();
        $siswa = Siswa::with(['user', 'ortu', 'kelasAktif'])
            ->select(['id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'foto_profile', 'user_id', 'data_ortu_completed', 'data_diri_completed', 'created_at']);

        // FILTER BY ROLE: Wali Kelas hanya lihat siswa di kelasnya
        if ($user->hasRole('Wali Kelas') && !$user->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah'])) {
            // Get kelas yang di-wali oleh user ini
            $kelasIds = \App\Models\Kelas::where('wali_kelas_id', $user->id)->pluck('id');
            
            if ($kelasIds->isEmpty()) {
                // Jika tidak punya kelas, return empty
                $siswa->whereRaw('1 = 0'); // Force empty result
            } else {
                // Filter hanya siswa di kelas yang di-wali
                $siswa->whereHas('kelasAktif', function($q) use ($kelasIds) {
                    $q->whereIn('kelas.id', $kelasIds);
                });
            }
        }

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

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $siswa->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%");
            });
        }

        $totalRecords = Siswa::count();
        $filteredRecords = $siswa->count();
        
        // Pagination
        if ($request->has('start') && $request->has('length')) {
            $length = $request->length;
            // Handle "All" option (-1)
            if ($length != -1) {
                $siswa->skip($request->start)->take($length);
            }
            // If length is -1, don't apply skip/take (load all data)
        }

        // Ordering
        if ($request->has('order')) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            
            // Map column index to actual column names
            $columns = [
                1 => 'nisn',
                2 => 'nama_lengkap', 
                3 => 'jenis_kelamin',
                4 => 'kelas_nama', // Kelas column (from join)
                5 => 'username', // Will handle separately
                8 => 'siswa.created_at'
            ];
            
            // Handle Kelas ordering (needs join)
            if ($orderColumnIndex == 3) {
                // Join with kelas table for ordering
                $siswa->leftJoin('siswa_kelas', function($join) {
                    $join->on('siswa.id', '=', 'siswa_kelas.siswa_id')
                         ->where('siswa_kelas.status', '=', 'aktif')
                         ->whereNull('siswa_kelas.deleted_at');
                })
                ->leftJoin('kelas', 'siswa_kelas.kelas_id', '=', 'kelas.id')
                ->orderBy('kelas.nama_kelas', $orderDirection)
                ->select('siswa.*')
                ->distinct(); // Avoid duplicates from join
            } 
            // Handle Username ordering (needs user join)
            elseif ($orderColumnIndex == 4) {
                $siswa->leftJoin('users', 'siswa.user_id', '=', 'users.id')
                      ->orderBy('users.username', $orderDirection)
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
            $kelasNama = $kelasAktif ? $kelasAktif->nama_kelas : 'Tanpa Rombel';
            
            return [
                'id' => $item->id,
                'foto' => $this->getFotoColumn($item),
                'nisn' => $item->nisn,
                'nama_lengkap' => $item->nama_lengkap,
                'jenis_kelamin' => $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'kelas' => $kelasNama,
                'username' => $item->user->username ?? '-',
                'status_ortu' => $this->getStatusOrtu($item),
                'status_diri' => $item->data_diri_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-danger">Belum Lengkap</span>',
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
            return '<span class="badge badge-warning text-dark">Terisi, Belum Lengkap</span>';
        }

        // Flag false tapi ada nama ayah/ibu → sebagian terisi (import EMIS)
        if (!empty($ortu->nama_ayah) || !empty($ortu->nama_ibu)) {
            return '<span class="badge badge-warning text-dark">Terisi, Belum Lengkap</span>';
        }

        return '<span class="badge badge-danger">Belum Lengkap</span>';
    }

    private function getFotoColumn(Siswa $siswa): string
    {
        if (!$siswa->foto_profile) {
            return '<span class="badge badge-light border text-muted">Belum ada</span>';
        }

        $previewUrl = e($siswa->foto_profile_url);
        $downloadUrl = e(route('admin.siswa.download-foto', $siswa));
        $studentName = e($siswa->nama_lengkap);
        $fallbackUrl = e($this->buildFallbackAvatar($siswa));

        return '
            <div class="d-flex align-items-center">
                <button type="button"
                    class="btn btn-link p-0 mr-2 js-preview-foto"
                    data-preview-url="' . $previewUrl . '"
                    data-download-url="' . $downloadUrl . '"
                    data-student-name="' . $studentName . '"
                    title="Preview foto">
                    <img src="' . $previewUrl . '"
                        alt="Foto ' . $studentName . '"
                        class="img-circle border"
                        onerror="this.onerror=null;this.src=\'' . $fallbackUrl . '\';"
                        style="width:40px;height:40px;object-fit:cover;">
                </button>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button"
                        class="btn btn-outline-info js-preview-foto"
                        data-preview-url="' . $previewUrl . '"
                        data-download-url="' . $downloadUrl . '"
                        data-student-name="' . $studentName . '"
                        title="Preview foto">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <a href="' . $downloadUrl . '" class="btn btn-outline-success" title="Download foto asli">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        ';
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
            'kelasAktif'
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

            // Delete user (will cascade delete siswa and ortu)
            $siswa->user->delete();

            DB::commit();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'delete',
                'model_type' => 'App\\Models\\Siswa',
                'model_id' => $siswa->id,
                'description' => "Menghapus data siswa: {$nama} (NISN: {$nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

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
            $dokumen = $siswa->dokumen()->latest()->get()->map(function($dok) {
                return [
                    'id' => $dok->id,
                    'jenis_dokumen' => $dok->jenis_dokumen,
                    'jenis_dokumen_label' => $dok->getJenisDokumenLabel(),
                    'nama_file' => $dok->nama_file,
                    'file_url' => $dok->getFileUrl(),
                    'file_size' => $dok->file_size,
                    'file_size_formatted' => $dok->getFileSizeFormatted(),
                    'keterangan' => $dok->keterangan,
                    'created_at' => $dok->created_at,
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
}
