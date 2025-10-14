<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.siswa.index');
    }

    /**
     * Get siswa data for DataTables
     */
    public function data(Request $request)
    {
        $siswa = Siswa::with(['user', 'ortu'])
            ->select(['id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'user_id', 'data_ortu_completed', 'data_diri_completed', 'created_at']);

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
            $siswa->skip($request->start)->take($request->length);
        }

        // Ordering
        if ($request->has('order')) {
            $columns = ['id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'created_at'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
            $orderDirection = $request->order[0]['dir'];
            $siswa->orderBy($orderColumn, $orderDirection);
        } else {
            $siswa->latest();
        }

        $data = $siswa->get()->map(function($item) {
            return [
                'id' => $item->id,
                'nisn' => $item->nisn,
                'nama_lengkap' => $item->nama_lengkap,
                'jenis_kelamin' => $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'username' => $item->user->username ?? '-',
                'status_ortu' => $item->data_ortu_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-warning">Belum Lengkap</span>',
                'status_diri' => $item->data_diri_completed ? 
                    '<span class="badge badge-success">Lengkap</span>' : 
                    '<span class="badge badge-warning">Belum Lengkap</span>',
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

    private function getActionButtons($item)
    {
        return '
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info btn-sm" onclick="showSiswa(\''.$item->id.'\')">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="editSiswa(\''.$item->id.'\')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(\''.$item->id.'\')">
                    <i class="fas fa-key"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSiswa(\''.$item->id.'\')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
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
            
            // Create user account for siswa
            $user = User::create([
                'name' => $request->nama_lengkap,
                'username' => $request->nisn,
                'email' => $request->nisn . '@student.man1metro.sch.id',
                'password' => Hash::make($request->nisn), // Default password is NISN
                'role' => 'siswa',
                'is_first_login' => true,
            ]);

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
        $siswa->load(['user', 'ortu', 'creator', 'updater']);
        
        // Format data for display
        $data = $siswa->toArray();
        $data['created_by_name'] = $siswa->creator ? $siswa->creator->name : 'System';
        $data['updated_by_name'] = $siswa->updater ? $siswa->updater->name : '-';
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nisn' => ['required', 'string', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        DB::beginTransaction();
        try {
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

            DB::commit();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'update',
                'model_type' => 'App\\Models\\Siswa',
                'model_id' => $siswa->id,
                'description' => "Memperbarui data siswa: {$request->nama_lengkap} (NISN: {$request->nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

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
        try {
            $siswa->user->update([
                'password' => Hash::make($siswa->nisn),
                'is_first_login' => true,
            ]);

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
                'message' => 'Password siswa berhasil direset ke NISN'
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
}
