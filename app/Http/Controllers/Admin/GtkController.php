<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            ->select(['id', 'nama_lengkap', 'nik', 'nuptk', 'nip', 'jenis_kelamin', 'kategori_ptk', 'jenis_ptk', 'status_kepegawaian', 'jabatan', 'user_id', 'data_diri_completed', 'data_kepegawaian_completed', 'created_at']);

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
                  ->orWhere('nip', 'like', "%{$search}%");
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
            $columns = ['id', 'nama_lengkap', 'nik', 'nuptk', 'nip', 'jenis_kelamin', 'kategori_ptk', 'jenis_ptk', 'status_kepegawaian', 'created_at'];
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
                    '<span class="badge badge-warning">Belum Lengkap</span>',
                'status_kepeg' => $item->data_kepegawaian_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-warning">Belum Lengkap</span>',
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
            $buttons .= '
                <button type="button" class="btn btn-warning btn-sm" onclick="editGtk(\''.$item->id.'\')">
                    <i class="fas fa-edit"></i>
                </button>';
        }
        
        // Reset Password button
        if ($user->can('reset-password-gtk')) {
            $buttons .= '
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(\''.$item->id.'\')">
                    <i class="fas fa-key"></i>
                </button>';
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $gtk = Gtk::findOrFail($id);

            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'nik' => ['required', 'string', 'size:16', Rule::unique('gtks', 'nik')->ignore($gtk->id)],
                'nuptk' => ['nullable', 'string', 'size:16', Rule::unique('gtks', 'nuptk')->ignore($gtk->id)],
                'nip' => ['nullable', 'string', 'max:18', Rule::unique('gtks', 'nip')->ignore($gtk->id)],
                'jenis_kelamin' => 'required|in:L,P',
                'kategori_ptk' => 'required|in:Pendidik,Tenaga Kependidikan',
                'jenis_ptk' => 'required|in:Guru Mapel,Guru BK,Kepala TU,Staff TU,Bendahara,Laboran,Pustakawan,Cleaning Service,Satpam,Lainnya',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'email' => 'nullable|email|max:255',
                'nomor_hp' => 'nullable|string|max:15',
                'status_kepegawaian' => 'nullable|in:PNS,PPPK,GTY,PTY,Honorer',
                'jabatan' => 'nullable|string|max:255',
                'tmt_kerja' => 'nullable|date',
                // Alamat fields
                'alamat' => 'nullable|string',
                'rt' => 'nullable|string|max:3',
                'rw' => 'nullable|string|max:3',
                'provinsi_id' => 'nullable|exists:indonesia_provinces,code',
                'kabupaten_id' => 'nullable|exists:indonesia_cities,code',
                'kecamatan_id' => 'nullable|exists:indonesia_districts,code',
                'kelurahan_id' => 'nullable|exists:indonesia_villages,code',
                'kodepos' => 'nullable|string|max:5',
            ]);

            DB::beginTransaction();

            // Update GTK data
            $validated['updated_by'] = Auth::id();
            $gtk->update($validated);

            // Update user name if changed
            if ($gtk->user && $gtk->nama_lengkap !== $gtk->user->name) {
                $gtk->user->update(['name' => $gtk->nama_lengkap]);
            }

            // Check completion status
            $dataLengkap = !empty($gtk->tempat_lahir) && !empty($gtk->tanggal_lahir) && 
                          !empty($gtk->alamat) && !empty($gtk->nomor_hp);
            $gtk->update(['data_diri_completed' => $dataLengkap]);

            $kepegLengkap = !empty($gtk->status_kepegawaian) && !empty($gtk->jabatan) && !empty($gtk->tmt_kerja);
            $gtk->update(['data_kepegawaian_completed' => $kepegLengkap]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data GTK berhasil diperbarui',
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
            Log::error('Error updating GTK: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
}
