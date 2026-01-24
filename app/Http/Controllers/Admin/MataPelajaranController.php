<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        try {
            $kurikulums = Kurikulum::where('is_active', true)->get();
            $jurusans = Jurusan::where('is_active', true)->get();
            $tahunPelajarans = \App\Models\TahunPelajaran::orderBy('is_active', 'desc')->orderBy('tahun_mulai', 'desc')->get();
            
            \Log::info('MapelController@index - Data loaded', [
                'kurikulums' => $kurikulums->count(),
                'jurusans' => $jurusans->count(),
                'tahunPelajarans' => $tahunPelajarans->count()
            ]);
            
            return view('admin.mapel.index', compact('kurikulums', 'jurusans', 'tahunPelajarans'));
        } catch (\Exception $e) {
            \Log::error('MapelController@index Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function data(Request $request)
    {
        \Log::info('MapelController@data called', [
            'filters' => $request->all()
        ]);
        
        $query = MataPelajaran::with(['kurikulum', 'jurusan', 'tahunPelajaran']);
        
        $totalBefore = MataPelajaran::count();
        \Log::info('Total mapel before filters', ['total' => $totalBefore]);

        // Filter by kurikulum
        if ($request->kurikulum_id) {
            $query->where('kurikulum_id', $request->kurikulum_id);
        }

        // Filter by tahun pelajaran
        if ($request->tahun_pelajaran_id) {
            $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
        }

        // Filter by jurusan
        if ($request->jurusan_id) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Filter by kelompok
        if ($request->kelompok) {
            $query->where('kelompok', $request->kelompok);
        }

        // Filter by tingkat
        if ($request->tingkat) {
            $query->whereJsonContains('tingkat', (int) $request->tingkat);
        }

        // Filter by kategori
        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter mapel agama
        if ($request->has('is_mapel_agama')) {
            $query->where('is_mapel_agama', $request->is_mapel_agama);
        }

        // Filter rumpun PAI
        if ($request->has('is_rumpun_pai')) {
            $query->where('is_rumpun_pai', $request->is_rumpun_pai);
        }

        return datatables()->eloquent($query)
            ->addIndexColumn()
            ->addColumn('tahun_pelajaran_display', function ($mapel) {
                return $mapel->tahunPelajaran ? $mapel->tahunPelajaran->nama_tahun_pelajaran : '-';
            })
            ->addColumn('kelompok_badge', function ($mapel) {
                if (!$mapel->kelompok) return '-';
                $colors = [
                    'A' => 'primary',
                    'B' => 'success',
                    'C' => 'warning',
                ];
                $color = $colors[$mapel->kelompok] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . $mapel->kelompok . '</span>';
            })
            ->addColumn('status_badge', function ($mapel) {
                if ($mapel->is_active) {
                    return '<span class="badge badge-success">Aktif</span>';
                }
                return '<span class="badge badge-danger">Nonaktif</span>';
            })
            ->addColumn('tingkat_display', function ($mapel) {
                return $mapel->tingkat_text;
            })
            ->addColumn('action', function ($mapel) {
                $showBtn = '<a href="' . route('admin.mapel.show', $mapel->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                $editBtn = '<a href="' . route('admin.mapel.edit', $mapel->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                $deleteBtn = '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $mapel->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                $duplicateBtn = '<button class="btn btn-sm btn-success duplicate-btn" data-id="' . $mapel->id . '" title="Duplikat"><i class="fas fa-copy"></i></button>';
                
                return $showBtn . ' ' . $editBtn . ' ' . $duplicateBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['kelompok_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $kurikulums = \App\Models\Kurikulum::where('is_active', true)->get();
        $jurusans = \App\Models\Jurusan::where('is_active', true)->get();
        $tahunPelajarans = \App\Models\TahunPelajaran::orderBy('is_active', 'desc')->orderBy('tahun_mulai', 'desc')->get();
        
        return view('admin.mapel.create-dragdrop', compact('kurikulums', 'jurusans', 'tahunPelajarans'));
    }

    public function bulkStore(Request $request)
    {
        // Gunakan tahun_pelajaran_id_actual jika ada (dari hidden input), fallback ke tahun_pelajaran_id
        $tahunPelajaranId = $request->input('tahun_pelajaran_id_actual') ?? $request->input('tahun_pelajaran_id');
        
        // Merge ke request untuk validasi
        $request->merge(['tahun_pelajaran_id' => $tahunPelajaranId]);
        
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'tingkat' => 'required|array|min:1',
            'semester' => 'required|array|min:1',
            'mapel' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($request->mapel as $mapelKey => $mapelJson) {
                try {
                    $mapelData = json_decode($mapelJson, true);
                    
                    // Extract group and index from mapelKey (format: mapel_1_0, mapel_1_1, etc)
                    $parts = explode('_', $mapelKey);
                    $groupIdx = $parts[1] ?? null;
                    $itemIdx = $parts[2] ?? null;
                    
                    // Ambil KKM dari input menggunakan key yang sama
                    $kkmKey = "kkm_{$groupIdx}_{$itemIdx}";
                    $kkm = $request->input($kkmKey, $mapelData['kkm_default'] ?? null);

                    // Cek apakah kode mapel sudah ada untuk kurikulum ini
                    $exists = MataPelajaran::where('kode_mapel', $mapelData['kode_mapel'])
                        ->where('kurikulum_id', $validated['kurikulum_id'])
                        ->exists();

                    if ($exists) {
                        $errors[] = "Kode mapel {$mapelData['kode_mapel']} sudah ada untuk kurikulum ini";
                        $errorCount++;
                        continue;
                    }

                    // Prepare data for insert
                    $insertData = [
                        'kurikulum_id' => $validated['kurikulum_id'],
                        'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                        'jurusan_id' => $validated['jurusan_id'],
                        'kode_mapel' => $mapelData['kode_mapel'],
                        'nama_mapel' => $mapelData['nama_mapel'],
                        'kelompok' => $mapelData['kelompok'] ?? null,
                        'kategori' => $mapelData['kategori'] ?? null,
                        'kkm' => $kkm,
                        'jam_pelajaran' => $mapelData['jam_pelajaran'],
                        'tingkat' => $validated['tingkat'],
                        'semester' => $validated['semester'],
                        'is_mapel_agama' => $mapelData['is_mapel_agama'] ?? false,
                        'jenis_agama' => $mapelData['jenis_agama'] ?? null,
                        'is_rumpun_pai' => $mapelData['is_rumpun_pai'] ?? false,
                        'sub_pai' => $mapelData['sub_pai'] ?? null,
                        'is_bahasa_arab' => $mapelData['is_bahasa_arab'] ?? false,
                        'is_mapel_pilihan' => $mapelData['is_mapel_pilihan'] ?? false,
                        'is_projek_p5' => $mapelData['is_projek_p5'] ?? false,
                        'is_muatan_lokal' => $mapelData['is_muatan_lokal'] ?? false,
                        'capaian_pembelajaran' => $mapelData['capaian_pembelajaran'] ?? null,
                        'is_active' => true,
                    ];

                    MataPelajaran::create($insertData);
                    $successCount++;

                } catch (\Exception $e) {
                    Log::error('Error creating mapel: ' . $e->getMessage());
                    $errors[] = "Error pada mapel {$mapelData['nama_mapel']}: " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "Berhasil menambahkan {$successCount} mata pelajaran";
            if ($errorCount > 0) {
                $message .= ". {$errorCount} mapel gagal ditambahkan.";
            }

            if ($errorCount > 0 && $successCount > 0) {
                return redirect()->route('admin.mapel.index')
                    ->with('warning', $message . ' Detail error: ' . implode(', ', $errors));
            } elseif ($errorCount > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal menambahkan mata pelajaran. ' . implode(', ', $errors));
            }

            return redirect()->route('admin.mapel.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk creating mata pelajaran: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan mata pelajaran: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'kelompok' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'kkm' => 'nullable|integer|min:0|max:100',
            'capaian_pembelajaran' => 'nullable|string',
            'is_mapel_agama' => 'boolean',
            'jenis_agama' => [
                'nullable',
                Rule::requiredIf($request->is_mapel_agama == true),
                'in:islam,kristen,katolik,hindu,buddha,khonghucu'
            ],
            'is_rumpun_pai' => 'boolean',
            'sub_pai' => [
                'nullable',
                Rule::requiredIf($request->is_rumpun_pai == true),
                'in:quran_hadits,akidah_akhlak,fikih,ski'
            ],
            'is_bahasa_arab' => 'boolean',
            'is_mapel_pilihan' => 'boolean',
            'is_projek_p5' => 'boolean',
            'is_muatan_lokal' => 'boolean',
            'jam_pelajaran' => 'required|integer|min:1|max:10',
            'tingkat' => 'nullable|array',
            'semester' => 'nullable|array',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            MataPelajaran::create($validated);

            return redirect()->route('admin.mapel.index')
                ->with('success', 'Mata pelajaran berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating mata pelajaran: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan mata pelajaran: ' . $e->getMessage());
        }
    }

    public function show(MataPelajaran $mapel)
    {
        $mapel->load(['kurikulum', 'jurusan']);
        return view('admin.mapel.show', compact('mapel'));
    }

    public function edit(MataPelajaran $mapel)
    {
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->get();
        
        return view('admin.mapel.edit', compact('mapel', 'kurikulums', 'jurusans'));
    }

    public function update(Request $request, MataPelajaran $mapel)
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kode_mapel' => [
                'required',
                'string',
                'max:10',
                Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mapel->id)
            ],
            'nama_mapel' => 'required|string|max:255',
            'kelompok' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'kkm' => 'nullable|integer|min:0|max:100',
            'capaian_pembelajaran' => 'nullable|string',
            'is_mapel_agama' => 'boolean',
            'jenis_agama' => [
                'nullable',
                Rule::requiredIf($request->is_mapel_agama == true),
                'in:islam,kristen,katolik,hindu,buddha,khonghucu'
            ],
            'is_rumpun_pai' => 'boolean',
            'sub_pai' => [
                'nullable',
                Rule::requiredIf($request->is_rumpun_pai == true),
                'in:quran_hadits,akidah_akhlak,fikih,ski'
            ],
            'is_bahasa_arab' => 'boolean',
            'is_mapel_pilihan' => 'boolean',
            'is_projek_p5' => 'boolean',
            'is_muatan_lokal' => 'boolean',
            'jam_pelajaran' => 'required|integer|min:1|max:10',
            'tingkat' => 'nullable|array',
            'semester' => 'nullable|array',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $mapel->update($validated);

            return redirect()->route('admin.mapel.index')
                ->with('success', 'Mata pelajaran berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Error updating mata pelajaran: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate mata pelajaran: ' . $e->getMessage());
        }
    }

    public function destroy(MataPelajaran $mapel)
    {
        try {
            $mapel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting mata pelajaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mata pelajaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(MataPelajaran $mapel)
    {
        try {
            $mapel->update(['is_active' => !$mapel->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status mata pelajaran berhasil diubah',
                'is_active' => $mapel->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status'
            ], 500);
        }
    }

    public function duplicate(Request $request, MataPelajaran $mapel)
    {
        try {
            $newMapel = $mapel->replicate();
            $newMapel->kode_mapel = $request->kode_mapel ?? $mapel->kode_mapel . '-COPY';
            $newMapel->nama_mapel = $request->nama_mapel ?? $mapel->nama_mapel . ' (Copy)';
            $newMapel->is_active = false; // Set inactive by default
            $newMapel->save();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil diduplikat',
                'id' => $newMapel->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error duplicating mata pelajaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menduplikat mata pelajaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
