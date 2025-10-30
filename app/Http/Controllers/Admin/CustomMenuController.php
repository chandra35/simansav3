<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomMenu;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\CustomMenuImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomMenuController extends Controller
{
    /**
     * Display a listing of custom menus
     */
    public function index(Request $request)
    {
        $this->authorize('view-siswa'); // Reuse existing permission

        if ($request->ajax()) {
            $query = CustomMenu::with('createdBy')
                ->withCount('siswaAssigned');

            // Filter by group
            if ($request->has('group') && $request->group != '') {
                $query->where('menu_group', $request->group);
            }

            // Filter by status
            if ($request->has('status') && $request->status != '') {
                $query->where('is_active', $request->status);
            }

            $menus = $query->ordered()->get();

            return datatables()->of($menus)
                ->addIndexColumn() // Add DT_RowIndex for row numbering
                ->addColumn('status_badge', function ($menu) {
                    $badge = $menu->is_active 
                        ? '<span class="badge badge-success">Aktif</span>' 
                        : '<span class="badge badge-secondary">Nonaktif</span>';
                    return $badge;
                })
                ->addColumn('group_badge', function ($menu) {
                    if (!$menu->menu_group) return '-';
                    $color = $menu->getGroupBadgeColor();
                    return '<span class="badge badge-' . $color . '">' . ucfirst($menu->menu_group) . '</span>';
                })
                ->addColumn('type_label', function ($menu) {
                    return $menu->getContentTypeLabel();
                })
                ->addColumn('siswa_count', function ($menu) {
                    return $menu->siswa_assigned_count ?? 0;
                })
                ->addColumn('action', function ($menu) {
                    $toggleIcon = $menu->is_active ? 'fa-eye-slash' : 'fa-eye';
                    $toggleTitle = $menu->is_active ? 'Nonaktifkan' : 'Aktifkan';
                    $toggleClass = $menu->is_active ? 'btn-warning' : 'btn-success';

                    return '
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm ' . $toggleClass . ' toggle-status" 
                                data-id="' . $menu->id . '" 
                                data-status="' . ($menu->is_active ? 1 : 0) . '"
                                title="' . $toggleTitle . '">
                                <i class="fas ' . $toggleIcon . '"></i>
                            </button>
                            <a href="' . route('admin.custom-menu.assign', $menu->id) . '" 
                               class="btn btn-sm btn-info" title="Assign Siswa">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="' . route('admin.custom-menu.edit', $menu->id) . '" 
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-menu" 
                                data-id="' . $menu->id . '" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'group_badge', 'action'])
                ->make(true);
        }

        $groups = CustomMenu::distinct()->pluck('menu_group')->filter();

        return view('admin.custom-menu.index', compact('groups'));
    }

    /**
     * Show the form for creating a new menu
     */
    public function create()
    {
        $this->authorize('create-siswa'); // Reuse existing permission

        return view('admin.custom-menu.create');
    }

    /**
     * Store a newly created menu
     */
    public function store(Request $request)
    {
        $this->authorize('create-siswa');

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'menu_group' => 'nullable|string|max:50',
            'content_type' => 'required|in:general,personal',
            'konten' => 'nullable|string',
            'custom_fields' => 'nullable|json',
            'urutan' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        try {
            // Generate unique slug
            $baseSlug = Str::slug($validated['judul']);
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug exists and append number if needed
            while (CustomMenu::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $slug;
            $validated['created_by'] = Auth::id();
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $menu = CustomMenu::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil dibuat',
                'redirect' => route('admin.custom-menu.assign', $menu->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating custom menu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified menu
     */
    public function edit(CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        return view('admin.custom-menu.edit', compact('customMenu'));
    }

    /**
     * Update the specified menu
     */
    public function update(Request $request, CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'menu_group' => 'nullable|string|max:50',
            'content_type' => 'required|in:general,personal',
            'konten' => 'nullable|string',
            'custom_fields' => 'nullable|json',
            'urutan' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        try {
            // Generate unique slug only if judul changed
            if ($validated['judul'] !== $customMenu->judul) {
                $baseSlug = Str::slug($validated['judul']);
                $slug = $baseSlug;
                $counter = 1;
                
                // Check if slug exists (exclude current menu)
                while (CustomMenu::where('slug', $slug)->where('id', '!=', $customMenu->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $validated['slug'] = $slug;
            }
            
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $customMenu->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil diupdate',
                'redirect' => route('admin.custom-menu.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating custom menu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified menu
     */
    public function destroy(CustomMenu $customMenu)
    {
        $this->authorize('delete-siswa');

        try {
            // Get menu title for response message
            $judul = $customMenu->judul;
            
            // Delete menu (cascade will handle custom_menu_siswa)
            $customMenu->delete();

            Log::info('Custom menu deleted', [
                'menu_id' => $customMenu->id,
                'judul' => $judul,
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Menu '{$judul}' berhasil dihapus"
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting custom menu', [
                'menu_id' => $customMenu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle menu active status
     */
    public function toggleStatus(CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        try {
            $newStatus = $customMenu->toggleStatus();

            return response()->json([
                'success' => true,
                'message' => 'Status menu berhasil diubah',
                'is_active' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show assign siswa form
     */
    public function assign(CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        // Eager load menuSiswa relation with siswa and their active class
        $customMenu->load(['menuSiswa.siswa.kelasAktif']);

        // Add count for assigned siswa
        $customMenu->siswa_assigned_count = $customMenu->menuSiswa->count();

        $kelas = Kelas::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $assignedSiswaIds = $customMenu->menuSiswa->pluck('siswa_id')->toArray();

        return view('admin.custom-menu.assign', compact('customMenu', 'kelas', 'assignedSiswaIds'));
    }

    /**
     * Assign siswa manually
     */
    public function assignSiswa(Request $request, CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        $validated = $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        try {
            $customMenu->assignSiswa($validated['siswa_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil di-assign ke menu'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Excel to assign siswa
     */
    public function uploadExcel(Request $request, CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $importService = new CustomMenuImportService($customMenu);
            $result = $importService->import($file);

            return response()->json([
                'success' => true,
                'message' => 'Import berhasil',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading Excel for custom menu', [
                'menu_id' => $customMenu->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate(CustomMenu $customMenu)
    {
        $this->authorize('view-siswa');

        try {
            $importService = new CustomMenuImportService($customMenu);
            return $importService->downloadTemplate();

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    /**
     * Remove siswa from menu
     */
    public function removeSiswa(Request $request, CustomMenu $customMenu)
    {
        $this->authorize('edit-siswa');

        $validated = $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        try {
            $customMenu->removeSiswa($validated['siswa_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dihapus dari menu'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus siswa: ' . $e->getMessage()
            ], 500);
        }
    }
}
