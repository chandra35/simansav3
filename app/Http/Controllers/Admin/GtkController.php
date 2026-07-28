<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\User;
use App\Services\GtkKemenagSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GtkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_gtk' => Gtk::count(),
            'gtk_with_nip' => Gtk::whereNotNull('nip')->where('nip', '!=', '')->count(),
            'laki_laki' => Gtk::where('jenis_kelamin', 'L')->count(),
            'perempuan' => Gtk::where('jenis_kelamin', 'P')->count(),
            'data_lengkap' => Gtk::where('data_diri_completed', true)
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
        $gtk = Gtk::with('user')
            ->select(['id', 'nama_lengkap', 'nik', 'nuptk', 'nip', 'kode_gtk', 'jenis_kelamin', 'kategori_ptk', 'jenis_ptk', 'status_kepegawaian', 'jabatan', 'user_id', 'data_diri_completed', 'data_kepegawaian_completed', 'created_at']);

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
                $gtk->where(function($q) {
                    $q->where('data_diri_completed', false)
                      ->orWhere('data_kepegawaian_completed', false);
                });
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $gtk->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('kode_gtk', 'like', "%{$search}%");
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
            $columns = [null, 'nama_lengkap', 'nik', 'kode_gtk', 'kategori_ptk', 'jenis_ptk', 'status_kepegawaian', 'jabatan', null, null, null, null];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
            $orderDirection = $request->order[0]['dir'];
            $gtk->orderBy($orderColumn, $orderDirection);
        } else {
            $gtk->latest();
        }

        $data = $gtk->get()->map(function($item, $index) use ($request) {
            // Badge color for kategori PTK
            $kategoriColor = $item->kategori_ptk == 'Pendidik' ? 'primary' : 'info';
            
            return [
                'DT_RowIndex' => $request->start + $index + 1,
                'nama_lengkap' => $item->nama_lengkap,
                'nik' => $item->nik,
                'kode_gtk' => $item->kode_gtk
                    ? '<span class="badge badge-primary px-2 py-1">'.$item->kode_gtk.'</span>'
                    : '<span class="text-muted">-</span>',
                'nuptk' => $item->nuptk ?? '-',
                'nip' => $item->nip ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'kategori_ptk' => $item->kategori_ptk ? 
                    '<span class="badge badge-'.$kategoriColor.'">'.$item->kategori_ptk.'</span>' : 
                    '<span class="badge badge-secondary">-</span>',
                'jenis_ptk' => $item->jenis_ptk ?? '-',
                'status_kepegawaian' => $item->status_kepegawaian ?? '-',
                'jabatan' => $item->jabatan ?? '-',
                'username' => $item->user->username ?? '-',
                'status_diri' => $item->data_diri_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-danger">Belum Lengkap</span>',
                'status_kepeg' => $item->data_kepegawaian_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-danger">Belum Lengkap</span>',
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

    /**
     * Get GTK candidates that can be synced with Kemenag safely.
     * Only returns GTK that already have a NIP.
     */
    public function syncKemenagCandidates()
    {
        if (!auth()->user()->can('edit-gtk')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan sinkronisasi massal.'
            ], 403);
        }

        $candidates = Gtk::query()
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
        $buttons = '<div class="btn-group" role="group">';
        
        // View button - always shown if can view gtk
        if ($user->can('view-gtk')) {
            $buttons .= '
                <button type="button" class="btn btn-info btn-sm" onclick="showGtk(\''.$item->id.'\')">
                    <i class="fas fa-eye"></i>
                </button>';
        }
        
        // Edit button
        if ($user->can('edit-gtk')) {
            $editUrl = route('admin.gtk.edit', $item->id);
            $buttons .= '
                <a href="'.$editUrl.'" class="btn btn-warning btn-sm" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>';
        }
        
        // Reset Password button
        if ($user->can('reset-password-gtk')) {
            $buttons .= '
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(\''.$item->id.'\')">
                    <i class="fas fa-key"></i>
                </button>';
        }

        if ($user->can('impersonate-users') && $item->user_id) {
            $buttons .= '
                <form method="POST" action="'.route('admin.impersonation.gtk.start', $item->id).'" target="_blank" class="d-inline">
                    <input type="hidden" name="_token" value="'.csrf_token().'">
                    <button type="submit" class="btn btn-primary btn-sm" title="Login As GTK">
                        <i class="fas fa-user-secret"></i>
                    </button>
                </form>';
        }
        
        // Delete button
        if ($user->can('delete-gtk')) {
            $buttons .= '
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteGtk(\''.$item->id.'\')">
                    <i class="fas fa-trash"></i>
                </button>';
        }
        
        $buttons .= '</div>';
        
        return $buttons;
    }

    /**
     * Store a newly created resource in storage (via modal - hanya nama dan NIK)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'required|string|size:16|unique:gtks,nik',
                'jenis_kelamin' => 'required|in:L,P',
                'kategori_ptk' => 'required|in:Pendidik,Tenaga Kependidikan',
                'jenis_ptk' => 'required|in:Guru Mapel,Guru BK,Kepala TU,Staff TU,Bendahara,Laboran,Pustakawan,Cleaning Service,Satpam,Lainnya',
            ], [
                'nama_lengkap.required' => 'Nama lengkap wajib diisi',
                'nik.required' => 'NIK wajib diisi',
                'nik.size' => 'NIK harus 16 digit',
                'nik.unique' => 'NIK sudah terdaftar',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
                'kategori_ptk.required' => 'Kategori PTK wajib dipilih',
                'jenis_ptk.required' => 'Jenis PTK wajib dipilih',
            ]);

            DB::beginTransaction();

            // Generate username dari NIK
            $username = $validated['nik'];

            // Create user account
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'username' => $username,
                'email' => $username . '@gtk.simansa.sch.id', // Email dummy
                'password' => Hash::make($validated['nik']), // Default password = NIK
                'is_active' => true,
            ]);

            // Assign role GTK (default)
            $user->assignRole('GTK');

            // Create GTK record
            $gtk = Gtk::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'nik' => $validated['nik'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data GTK berhasil ditambahkan. Username: ' . $username . ', Password default: NIK',
                'data' => $gtk
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating GTK: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            'data' => $gtk
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
                    'nik' => 'required|string|size:16|unique:gtks,nik,' . $gtk->id,
                    'nuptk' => 'nullable|string|max:20',
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
                $dataLengkap = !empty($gtk->nik) && !empty($gtk->nama_lengkap) && 
                               !empty($gtk->jenis_kelamin) && !empty($gtk->tempat_lahir) && 
                               !empty($gtk->tanggal_lahir);
                $gtk->update(['data_diri_completed' => $dataLengkap]);
                
                $message = 'Data pribadi berhasil diperbarui';
                
            } elseif ($tab === 'kepeg') {
                // Update Data Kepegawaian
                $validated = $request->validate([
                    'nip' => 'nullable|string|max:20',
                    'kategori_ptk' => 'required|in:Pendidik,Tenaga Kependidikan',
                    'jenis_ptk' => 'required|in:Guru Mapel,Guru BK,Kepala TU,Staff TU,Bendahara,Laboran,Pustakawan,Cleaning Service,Satpam,Lainnya',
                    'status_kepegawaian' => 'nullable|in:PNS,PPPK,GTY,PTY,Honorer',
                    'jabatan' => 'nullable|string|max:100',
                    'tmt_kerja' => 'nullable|date',
                ]);
                
                $gtk->update($validated);
                
                // Check if data kepegawaian is complete
                $kepegLengkap = !empty($gtk->status_kepegawaian) && !empty($gtk->jabatan) && !empty($gtk->tmt_kerja);
                $gtk->update(['data_kepegawaian_completed' => $kepegLengkap]);
                
                $message = 'Data kepegawaian berhasil diperbarui';
                
            } elseif ($tab === 'akun') {
                // Update Akun User
                if (!$gtk->user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'GTK tidak memiliki akun user'
                    ], 404);
                }
                
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'username' => 'required|string|max:255|unique:users,username,' . $gtk->user->id,
                    'email' => 'required|email|max:255|unique:users,email,' . $gtk->user->id,
                    'is_active' => 'required|boolean',
                    'role' => 'required|exists:roles,name',
                ]);
                
                $gtk->user->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'is_active' => $validated['is_active'],
                ]);
                
                // Sync role
                $gtk->user->syncRoles([$validated['role']]);
                
                $message = 'Data akun berhasil diperbarui';
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $gtk->fresh(['user.roles'])
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating GTK: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            'foto_profile' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'foto_profile.required' => 'File foto wajib dipilih.',
            'foto_profile.image' => 'File harus berupa gambar.',
            'foto_profile.mimes' => 'Format yang diizinkan: JPG, JPEG, PNG.',
            'foto_profile.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            // Delete old foto if exists
            if ($gtk->foto_profile) {
                Storage::disk('public')->delete($gtk->foto_profile);
            }

            $path = $request->file('foto_profile')->store('foto_profile/gtk', 'public');
            $gtk->update(['foto_profile' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload.',
                'foto_url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading GTK foto: ' . $e->getMessage());
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
            Log::error('Error deleting GTK foto: ' . $e->getMessage());
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
                'message' => 'Data GTK berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting GTK: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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

            if (!$gtk->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'GTK tidak memiliki akun user'
                ], 404);
            }

            $gtk->user->update([
                'password' => Hash::make($gtk->nik),
                'is_first_login' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset ke NIK'
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting password: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            ->map(function($village) {
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
            if (!auth()->user()->can('edit-gtk')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melakukan sinkronisasi'
                ], 403);
            }

            // Check if GTK has NIP
            if (empty($gtk->nip)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GTK ini tidak memiliki NIP. Sinkronisasi tidak dapat dilakukan.'
                ]);
            }

            // Perform sync
            $result = $syncService->syncGtkData($gtk, auth()->id());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('GtkController: Sync Kemenag error', [
                'gtk_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
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
            if (!auth()->user()->can('edit-gtk')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menerapkan data'
                ], 403);
            }

            // Check if sync data exists
            if (!$gtk->kemenagSync) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data sinkronisasi. Silakan lakukan sinkronisasi terlebih dahulu.'
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
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menerapkan data: ' . $e->getMessage()
            ], 500);
        }
    }
}
