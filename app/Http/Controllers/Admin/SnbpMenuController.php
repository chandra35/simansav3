<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SnbpMenu;
use App\Models\SnbpRegistration;
use App\Models\SnbpSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SnbpMenuController extends Controller
{
    /**
     * Display a listing of SNBP menus
     */
    public function index()
    {
        $menus = SnbpMenu::with(['tahunPelajaran'])
                         ->orderBy('created_at', 'desc')
                         ->get();

        $activeTahun = TahunPelajaran::where('is_active', true)->first();

        return view('admin.snbp-menu.index', compact('menus', 'activeTahun'));
    }

    /**
     * Show form to create new menu
     */
    public function create()
    {
        $tahunPelajaranList = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $activeTahun = TahunPelajaran::where('is_active', true)->first();

        // Check if active tahun already has menu
        $existingMenu = null;
        if ($activeTahun) {
            $existingMenu = SnbpMenu::where('tahun_pelajaran_id', $activeTahun->id)->first();
        }

        return view('admin.snbp-menu.create', compact('tahunPelajaranList', 'activeTahun', 'existingMenu'));
    }

    /**
     * Store a new menu
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id|unique:snbp_menus,tahun_pelajaran_id',
            'konten_eligible' => 'nullable|string',
            'konten_not_eligible' => 'nullable|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'tahun_pelajaran_id.unique' => 'Tahun pelajaran ini sudah memiliki menu SNBP.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        // Only allow creating for active tahun pelajaran
        $tahun = TahunPelajaran::find($request->tahun_pelajaran_id);
        if (!$tahun->is_active) {
            return redirect()->back()
                ->with('error', 'Hanya dapat membuat menu untuk tahun pelajaran aktif.');
        }

        SnbpMenu::create([
            'nama_menu' => $request->nama_menu,
            'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
            'konten_eligible' => $request->konten_eligible,
            'konten_not_eligible' => $request->konten_not_eligible,
            'is_active' => $request->has('is_active'),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
        ]);

        return redirect()->route('admin.snbp-menu.index')
            ->with('success', 'Menu SNBP berhasil dibuat.');
    }

    /**
     * Show menu details
     */
    public function show(SnbpMenu $snbpMenu)
    {
        $snbpMenu->load(['tahunPelajaran', 'eligibleSiswa', 'notEligibleSiswa']);

        $registrationMap = SnbpRegistration::query()
            ->with('lulusan')
            ->where('snbp_menu_id', $snbpMenu->id)
            ->get()
            ->keyBy('siswa_id');

        $eligibleSiswa = $snbpMenu->eligibleSiswa->map(function ($siswa) use ($registrationMap) {
            $siswa->setRelation('snbpRegistration', $registrationMap->get($siswa->id));

            return $siswa;
        });

        $summary = [
            'eligible_total' => $eligibleSiswa->count(),
            'sudah_isi' => $eligibleSiswa->filter(fn ($siswa) => filled(optional($siswa->snbpRegistration)->nomor_pendaftaran))->count(),
            'terhubung_lulusan' => $eligibleSiswa->filter(fn ($siswa) => optional($siswa->snbpRegistration)->lulusan !== null)->count(),
        ];
        
        return view('admin.snbp-menu.show', compact('snbpMenu', 'eligibleSiswa', 'summary'));
    }

    /**
     * Show form to edit menu
     */
    public function edit(SnbpMenu $snbpMenu)
    {
        // Check if editable (only active tahun pelajaran)
        if (!$snbpMenu->isEditable()) {
            return redirect()->route('admin.snbp-menu.show', $snbpMenu)
                ->with('warning', 'Menu ini tidak dapat diedit karena tahun pelajaran sudah tidak aktif.');
        }

        $tahunPelajaranList = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();

        return view('admin.snbp-menu.edit', compact('snbpMenu', 'tahunPelajaranList'));
    }

    /**
     * Update menu
     */
    public function update(Request $request, SnbpMenu $snbpMenu)
    {
        // Check if editable
        if (!$snbpMenu->isEditable()) {
            return redirect()->route('admin.snbp-menu.show', $snbpMenu)
                ->with('error', 'Menu ini tidak dapat diedit karena tahun pelajaran sudah tidak aktif.');
        }

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'konten_eligible' => 'nullable|string',
            'konten_not_eligible' => 'nullable|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        $snbpMenu->update([
            'nama_menu' => $request->nama_menu,
            'konten_eligible' => $request->konten_eligible,
            'konten_not_eligible' => $request->konten_not_eligible,
            'is_active' => $request->has('is_active'),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
        ]);

        return redirect()->route('admin.snbp-menu.index')
            ->with('success', 'Menu SNBP berhasil diperbarui.');
    }

    /**
     * Delete menu
     */
    public function destroy(SnbpMenu $snbpMenu)
    {
        // Check if editable
        if (!$snbpMenu->isEditable()) {
            return redirect()->route('admin.snbp-menu.index')
                ->with('error', 'Menu ini tidak dapat dihapus karena tahun pelajaran sudah tidak aktif.');
        }

        $snbpMenu->delete();

        return redirect()->route('admin.snbp-menu.index')
            ->with('success', 'Menu SNBP berhasil dihapus.');
    }

    /**
     * Show form to assign eligible students
     */
    public function assignEligible(SnbpMenu $snbpMenu)
    {
        if (!$snbpMenu->isEditable()) {
            return redirect()->route('admin.snbp-menu.show', $snbpMenu)
                ->with('warning', 'Data readonly karena tahun pelajaran sudah tidak aktif.');
        }

        $snbpMenu->load('eligibleSiswa');
        
        // Get kelas 12 from tahun pelajaran
        $kelas12Ids = Kelas::where('tahun_pelajaran_id', $snbpMenu->tahun_pelajaran_id)
                           ->where('tingkat', 12)
                           ->pluck('id');

        // Get total kelas 12 students count
        $totalKelas12 = Siswa::whereHas('kelas', function($q) use ($kelas12Ids) {
                             $q->whereIn('kelas.id', $kelas12Ids);
                         })
                         ->count();

        return view('admin.snbp-menu.assign-eligible', compact('snbpMenu', 'totalKelas12'));
    }

    /**
     * Store eligible students assignment
     */
    public function storeEligible(Request $request, SnbpMenu $snbpMenu)
    {
        if (!$snbpMenu->isEditable()) {
            return redirect()->back()
                ->with('error', 'Data readonly karena tahun pelajaran sudah tidak aktif.');
        }

        $request->validate([
            'nisn_list' => 'required|string',
        ]);

        // Parse NISNs from textarea (one per line)
        $nisnList = array_filter(
            array_map('trim', explode("\n", $request->nisn_list))
        );

        if (empty($nisnList)) {
            return redirect()->back()
                ->with('error', 'Tidak ada NISN yang valid.');
        }

        // Get kelas 12 students from tahun pelajaran
        $kelas12Ids = Kelas::where('tahun_pelajaran_id', $snbpMenu->tahun_pelajaran_id)
                           ->where('tingkat', 12)
                           ->pluck('id');

        // Find siswa by NISN that are in kelas 12
        $siswaKelas12 = Siswa::whereIn('nisn', $nisnList)
                             ->whereHas('kelas', function($q) use ($kelas12Ids) {
                                 $q->whereIn('kelas.id', $kelas12Ids);
                             })
                             ->get();

        $foundNisns = $siswaKelas12->pluck('nisn')->toArray();
        $notFoundNisns = array_diff($nisnList, $foundNisns);

        DB::beginTransaction();
        try {
            // If clear_existing is checked, remove all previous eligible assignments
            if ($request->has('clear_existing')) {
                SnbpSiswa::where('snbp_menu_id', $snbpMenu->id)
                         ->where('is_eligible', true)
                         ->delete();
            }

            // Create new eligible assignments
            foreach ($siswaKelas12 as $siswa) {
                SnbpSiswa::updateOrCreate(
                    [
                        'snbp_menu_id' => $snbpMenu->id,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'is_eligible' => true,
                    ]
                );
            }

            DB::commit();

            $message = "Berhasil assign {$siswaKelas12->count()} siswa sebagai eligible.";
            if (!empty($notFoundNisns)) {
                $message .= " NISN tidak ditemukan/bukan kelas 12: " . implode(', ', array_slice($notFoundNisns, 0, 10));
                if (count($notFoundNisns) > 10) {
                    $message .= " dan " . (count($notFoundNisns) - 10) . " lainnya.";
                }
            }

            return redirect()->route('admin.snbp-menu.assign-eligible', $snbpMenu)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning eligible students: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal assign siswa: ' . $e->getMessage());
        }
    }

    /**
     * Show form to assign not eligible students
     */
    public function assignNotEligible(SnbpMenu $snbpMenu)
    {
        if (!$snbpMenu->isEditable()) {
            return redirect()->route('admin.snbp-menu.show', $snbpMenu)
                ->with('warning', 'Data readonly karena tahun pelajaran sudah tidak aktif.');
        }

        $snbpMenu->load('notEligibleSiswa');

        // Get kelas 12 from tahun pelajaran
        $kelasList = Kelas::where('tahun_pelajaran_id', $snbpMenu->tahun_pelajaran_id)
                          ->where('tingkat', 12)
                          ->orderBy('nama_kelas')
                          ->get();

        $kelas12Ids = $kelasList->pluck('id');

        // Get all kelas 12 students
        $allKelas12Siswa = Siswa::with(['kelasSaatIni'])
                                ->whereHas('kelas', function($q) use ($kelas12Ids) {
                                    $q->whereIn('kelas.id', $kelas12Ids);
                                })
                                ->orderBy('nama_lengkap')
                                ->get();

        // Get already assigned siswa IDs
        $assignedSiswaIds = SnbpSiswa::where('snbp_menu_id', $snbpMenu->id)
                                      ->pluck('siswa_id')
                                      ->toArray();

        // Filter to get unassigned siswa (available for assignment)
        $availableSiswa = $allKelas12Siswa->filter(function($siswa) use ($assignedSiswaIds) {
            return !in_array($siswa->id, $assignedSiswaIds);
        });

        return view('admin.snbp-menu.assign-not-eligible', compact('snbpMenu', 'availableSiswa', 'kelasList'));
    }

    /**
     * Store not eligible students assignment
     */
    public function storeNotEligible(Request $request, SnbpMenu $snbpMenu)
    {
        if (!$snbpMenu->isEditable()) {
            return redirect()->back()
                ->with('error', 'Data readonly karena tahun pelajaran sudah tidak aktif.');
        }

        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->siswa_ids as $siswaId) {
                SnbpSiswa::updateOrCreate(
                    [
                        'snbp_menu_id' => $snbpMenu->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'is_eligible' => false,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.snbp-menu.assign-not-eligible', $snbpMenu)
                ->with('success', 'Berhasil assign ' . count($request->siswa_ids) . ' siswa sebagai tidak eligible.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning not eligible students: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal assign siswa: ' . $e->getMessage());
        }
    }

    /**
     * Remove siswa assignment
     */
    public function removeAssignment($snbpSiswaId)
    {
        $snbpSiswa = SnbpSiswa::findOrFail($snbpSiswaId);
        $snbpMenu = $snbpSiswa->snbpMenu;
        
        if (!$snbpMenu->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Data readonly karena tahun pelajaran sudah tidak aktif.'
            ], 403);
        }

        $snbpSiswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment berhasil dihapus.'
        ]);
    }

    /**
     * Get DataTable data
     */
    public function data(Request $request)
    {
        $menus = SnbpMenu::with(['tahunPelajaran'])
                         ->orderBy('created_at', 'desc')
                         ->get();

        return datatables()->of($menus)
            ->addIndexColumn()
            ->addColumn('tahun_pelajaran', function($menu) {
                return $menu->tahunPelajaran->nama ?? '-';
            })
            ->addColumn('status', function($menu) {
                $isActive = $menu->is_active;
                $isEditable = $menu->isEditable();
                
                $badges = '';
                if ($isActive) {
                    $badges .= '<span class="badge badge-success">Aktif</span> ';
                } else {
                    $badges .= '<span class="badge badge-secondary">Non-Aktif</span> ';
                }
                
                if (!$isEditable) {
                    $badges .= '<span class="badge badge-warning"><i class="fas fa-lock"></i> Readonly</span>';
                }
                
                return $badges;
            })
            ->addColumn('eligible_count', function($menu) {
                return $menu->eligibleSiswa()->count();
            })
            ->addColumn('not_eligible_count', function($menu) {
                return $menu->notEligibleSiswa()->count();
            })
            ->addColumn('action', function($menu) {
                $isEditable = $menu->isEditable();
                
                $buttons = '<div class="btn-group">';
                $buttons .= '<a href="' . route('admin.snbp-menu.show', $menu) . '" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';
                
                if ($isEditable) {
                    $buttons .= '<a href="' . route('admin.snbp-menu.edit', $menu) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    $buttons .= '<a href="' . route('admin.snbp-menu.assign-eligible', $menu) . '" class="btn btn-success btn-sm" title="Assign Eligible"><i class="fas fa-user-check"></i></a>';
                    $buttons .= '<a href="' . route('admin.snbp-menu.assign-not-eligible', $menu) . '" class="btn btn-secondary btn-sm" title="Assign Not Eligible"><i class="fas fa-user-times"></i></a>';
                }
                
                $buttons .= '</div>';
                
                return $buttons;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
