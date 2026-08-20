<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\RdmMapelMapping;
use App\Models\TahunPelajaran;
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
            $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('tahun_mulai', 'desc')->get();
            $tahunAktif = $tahunPelajarans->firstWhere('is_active', true);
            $kurikulumAktifId = $tahunAktif?->kurikulum_id;

            $catalogQuery = MataPelajaran::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    foreach ([10, 11, 12] as $tingkat) {
                        // Mendukung data lama yang pernah tersimpan sebagai string JSON.
                        $query->orWhereJsonContains('tingkat', $tingkat)
                            ->orWhereJsonContains('tingkat', (string) $tingkat);
                    }
                });

            $catalogIds = (clone $catalogQuery)->pluck('id');
            $stats = [
                'total' => $catalogIds->count(),
                'ready' => (clone $catalogQuery)->where('is_schedulable', true)->count(),
                'mapped' => RdmMapelMapping::whereIn('mata_pelajaran_id', $catalogIds)
                    ->distinct('mata_pelajaran_id')
                    ->count('mata_pelajaran_id'),
                'scheduled' => DB::table('jadwal_pelajaran')
                    ->whereIn('mapel_id', $catalogIds)
                    ->whereNull('deleted_at')
                    ->distinct('mapel_id')
                    ->count('mapel_id'),
            ];
            
            \Log::info('MapelController@index - Data loaded', [
                'kurikulums' => $kurikulums->count(),
                'jurusans' => $jurusans->count(),
                'tahunPelajarans' => $tahunPelajarans->count()
            ]);
            
            return view('admin.mapel.index', compact(
                'kurikulums',
                'jurusans',
                'tahunPelajarans',
                'tahunAktif',
                'kurikulumAktifId',
                'stats'
            ));
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
        
        $query = MataPelajaran::with(['kurikulum', 'jurusan', 'tahunPelajaran'])
            ->withCount(['rdmMappings', 'jadwalPelajaran']);
        
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
            $query->where(function ($filter) use ($request) {
                $filter->where('struktur_fase_e', $request->kelompok)
                    ->orWhere('struktur_fase_f', $request->kelompok);
            });
        }

        // Filter by tingkat
        if ($request->tingkat) {
            $tingkat = (int) $request->tingkat;
            $query->where(function ($levels) use ($tingkat) {
                $levels->whereJsonContains('tingkat', $tingkat)
                    ->orWhereJsonContains('tingkat', (string) $tingkat);
            });
        } else {
            $query->where(function ($levels) {
                foreach ([10, 11, 12] as $tingkat) {
                    $levels->orWhereJsonContains('tingkat', $tingkat)
                        ->orWhereJsonContains('tingkat', (string) $tingkat);
                }
            });
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
            ->editColumn('kode_mapel', function ($mapel) {
                if (! $mapel->kode_jadwal) {
                    return e($mapel->kode_mapel);
                }

                return '<span class="badge badge-primary mr-1">'.$mapel->kode_jadwal.'</span><code>'.e($mapel->kode_mapel).'</code>';
            })
            ->addColumn('tahun_pelajaran_display', function ($mapel) {
                return $mapel->tahunPelajaran ? $mapel->tahunPelajaran->nama_tahun_pelajaran : '-';
            })
            ->addColumn('kelompok_badge', function ($mapel) {
                return $mapel->kelompok_badge;
            })
            ->addColumn('fase_display', function ($mapel) {
                return $mapel->fase_text;
            })
            ->addColumn('rumpun_display', function ($mapel) {
                return $mapel->rumpun
                    ? ucfirst(str_replace('_', ' & ', $mapel->rumpun))
                    : '-';
            })
            ->addColumn('integrasi_badge', function ($mapel) {
                $rdm = $mapel->rdm_mappings_count > 0
                    ? '<span class="badge badge-success"><i class="fas fa-link"></i> RDM</span>'
                    : '<span class="badge badge-light border"><i class="fas fa-unlink"></i> Belum RDM</span>';
                $jadwal = $mapel->jadwal_pelajaran_count > 0
                    ? '<span class="badge badge-primary ml-1"><i class="fas fa-calendar-check"></i> Terjadwal</span>'
                    : '';

                return $rdm . $jadwal;
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
                $showBtn = '<a href="' . route('admin.mapel.show', $mapel->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i><span> Detail</span></a>';
                $editBtn = auth()->user()->can('edit-mapel')
                    ? '<a href="' . route('admin.mapel.edit', $mapel->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i><span> Edit</span></a>'
                    : '';
                $deleteBtn = auth()->user()->can('delete-mapel')
                    ? '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $mapel->id . '" title="Hapus"><i class="fas fa-trash"></i><span> Hapus</span></button>'
                    : '';
                $duplicateBtn = auth()->user()->can('create-mapel')
                    ? '<button class="btn btn-sm btn-success duplicate-btn" data-id="' . $mapel->id . '" title="Duplikat"><i class="fas fa-copy"></i><span> Duplikat</span></button>'
                    : '';

                $menuItems = '<a class="dropdown-item" href="' . route('admin.mapel.show', $mapel->id) . '"><i class="fas fa-eye text-info"></i> Lihat detail</a>';
                if ($editBtn) {
                    $menuItems .= '<a class="dropdown-item" href="' . route('admin.mapel.edit', $mapel->id) . '"><i class="fas fa-edit text-warning"></i> Edit mapel</a>';
                }
                if ($duplicateBtn) {
                    $menuItems .= '<button type="button" class="dropdown-item duplicate-btn" data-id="' . $mapel->id . '"><i class="fas fa-copy text-success"></i> Duplikat</button>';
                }
                if ($deleteBtn) {
                    $menuItems .= '<div class="dropdown-divider"></div><button type="button" class="dropdown-item text-danger delete-btn" data-id="' . $mapel->id . '"><i class="fas fa-trash"></i> Hapus mapel</button>';
                }

                $desktop = '<div class="dropdown mapel-action-desktop">'
                    . '<button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i> Aksi</button>'
                    . '<div class="dropdown-menu dropdown-menu-right">' . $menuItems . '</div></div>';
                $mobile = '<div class="mapel-action-mobile" role="group" aria-label="Aksi mata pelajaran">'
                    . $showBtn . $editBtn . $duplicateBtn . $deleteBtn . '</div>';

                return $desktop . $mobile;
            })
            ->rawColumns(['kode_mapel', 'kelompok_badge', 'integrasi_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $kurikulums = \App\Models\Kurikulum::where('is_active', true)->get();
        $jurusans = \App\Models\Jurusan::where('is_active', true)->get();
        $tahunPelajarans = \App\Models\TahunPelajaran::orderBy('is_active', 'desc')->orderBy('tahun_mulai', 'desc')->get();
        
        $mapelTemplates = config('mapel_template');
        $mapelTemplates['MERDEKA'] = config('mapel_man');

        return view('admin.mapel.create-dragdrop', compact(
            'kurikulums',
            'jurusans',
            'tahunPelajarans',
            'mapelTemplates'
        ));
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
            $updatedCount = 0;
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

                    $existing = MataPelajaran::withTrashed()
                        ->where('kode_mapel', $mapelData['kode_mapel'])
                        ->where('kurikulum_id', $validated['kurikulum_id'])
                        ->first();

                    $selectedLevels = array_values(array_map('intval', $validated['tingkat']));
                    $allocation = collect($mapelData['alokasi_jp'] ?? [])
                        ->filter(fn ($jp, $level) => in_array((int) $level, $selectedLevels, true))
                        ->map(fn ($jp) => (int) $jp)
                        ->all();
                    $defaultJp = $allocation
                        ? (int) round(array_sum($allocation) / count($allocation))
                        : (int) ($mapelData['jam_pelajaran'] ?? 2);

                    // Prepare data for insert
                    $insertData = [
                        'kurikulum_id' => $validated['kurikulum_id'],
                        'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                        'jurusan_id' => $validated['jurusan_id'],
                        'kode_mapel' => $mapelData['kode_mapel'],
                        'nama_mapel' => $mapelData['nama_mapel'],
                        'kelompok' => $mapelData['kelompok'] ?? null,
                        'kategori' => $mapelData['kategori'] ?? null,
                        'struktur_fase_e' => in_array(10, $selectedLevels, true)
                            ? ($mapelData['struktur_dipilih'] ?? $mapelData['struktur_fase_e'] ?? null)
                            : null,
                        'struktur_fase_f' => (in_array(11, $selectedLevels, true) || in_array(12, $selectedLevels, true))
                            ? ($mapelData['struktur_dipilih'] ?? $mapelData['struktur_fase_f'] ?? null)
                            : null,
                        'rumpun' => $mapelData['rumpun'] ?? null,
                        'kkm' => $kkm !== null && $kkm !== '' ? $kkm : null,
                        'jam_pelajaran' => $defaultJp,
                        'alokasi_jp' => $allocation ?: null,
                        'regulasi' => 'KMA 1503 Tahun 2025',
                        'is_schedulable' => ($mapelData['is_schedulable'] ?? true)
                            && !($mapelData['is_projek_p5'] ?? false),
                        'tingkat' => $selectedLevels,
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

                    if ($existing) {
                        $existing->restore();
                        $existing->update($insertData);
                        $updatedCount++;
                    } else {
                        MataPelajaran::create($insertData);
                        $successCount++;
                    }

                } catch (\Exception $e) {
                    Log::error('Error creating mapel: ' . $e->getMessage());
                    $errors[] = "Error pada mapel {$mapelData['nama_mapel']}: " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "Katalog mapel diperbarui: {$successCount} ditambahkan dan {$updatedCount} diselaraskan tanpa mengubah mapping RDM";
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
        $request->merge([
            'kode_jadwal' => $request->filled('kode_jadwal') ? strtoupper($request->kode_jadwal) : null,
        ]);

        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel',
            'kode_jadwal' => [
                'nullable',
                'string',
                'size:1',
                'regex:/^[A-Z]$/',
                Rule::unique('mata_pelajaran', 'kode_jadwal')
                    ->where(fn ($query) => $query->where('kurikulum_id', $request->kurikulum_id)),
            ],
            'nama_mapel' => 'required|string|max:255',
            'kelompok' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'struktur_fase_e' => 'nullable|in:wajib_umum,pilihan,muatan_lokal,penguatan_program,kokurikuler',
            'struktur_fase_f' => 'nullable|in:wajib_umum,pilihan,muatan_lokal,penguatan_program,kokurikuler',
            'rumpun' => 'nullable|in:pai,mipa,ips,bahasa,teknologi,seni_prakarya,pjok,umum',
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
            'alokasi_jp' => 'nullable|array',
            'alokasi_jp.*' => 'nullable|integer|min:0|max:10',
            'regulasi' => 'nullable|string|max:80',
            'is_schedulable' => 'boolean',
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
        $mapel->load('kurikulum');
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->get();
        
        return view('admin.mapel.edit', compact('mapel', 'kurikulums', 'jurusans'));
    }

    public function update(Request $request, MataPelajaran $mapel)
    {
        $request->merge([
            // Katalog kurikulum adalah identitas mapel. Edit rutin, termasuk
            // koreksi ID EMIS GTK, tidak boleh memindahkan mapel ke kurikulum lain.
            'kurikulum_id' => $mapel->kurikulum_id,
            'kode_jadwal' => $request->filled('kode_jadwal') ? strtoupper($request->kode_jadwal) : null,
            'is_mapel_agama' => $request->boolean('is_mapel_agama'),
            'is_rumpun_pai' => $request->boolean('is_rumpun_pai'),
            'is_bahasa_arab' => $request->boolean('is_bahasa_arab'),
            'is_mapel_pilihan' => $request->boolean('is_mapel_pilihan'),
            'is_projek_p5' => $request->boolean('is_projek_p5'),
            'is_muatan_lokal' => $request->boolean('is_muatan_lokal'),
            'is_schedulable' => $request->boolean('is_schedulable'),
        ]);

        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kode_mapel' => [
                'required',
                'string',
                'max:10',
                Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mapel->id)
            ],
            'kode_jadwal' => [
                'nullable',
                'string',
                'size:1',
                'regex:/^[A-Z]$/',
                Rule::unique('mata_pelajaran', 'kode_jadwal')
                    ->where(fn ($query) => $query->where('kurikulum_id', $request->kurikulum_id))
                    ->ignore($mapel->id),
            ],
            'emisgtk_mapel_ids' => 'nullable|array',
            'emisgtk_mapel_ids.10' => 'nullable|string|max:64',
            'emisgtk_mapel_ids.11' => 'nullable|string|max:64',
            'emisgtk_mapel_ids.12' => 'nullable|string|max:64',
            'nama_mapel' => 'required|string|max:255',
            'kelompok' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'struktur_fase_e' => 'nullable|in:wajib_umum,pilihan,muatan_lokal,penguatan_program,kokurikuler',
            'struktur_fase_f' => 'nullable|in:wajib_umum,pilihan,muatan_lokal,penguatan_program,kokurikuler',
            'rumpun' => 'nullable|in:pai,mipa,ips,bahasa,teknologi,seni_prakarya,pjok,umum',
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
            'alokasi_jp' => 'nullable|array',
            'alokasi_jp.*' => 'nullable|integer|min:0|max:10',
            'regulasi' => 'nullable|string|max:80',
            'is_schedulable' => 'boolean',
            'tingkat' => 'nullable|array',
            'semester' => 'nullable|array',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Kosongkan tingkat yang tidak memiliki ID EMIS GTK agar mapping
        // ekspor tetap eksplisit dan tidak menyimpan nilai kosong.
        $validated['emisgtk_mapel_ids'] = array_filter(
            $validated['emisgtk_mapel_ids'] ?? [],
            fn ($id) => filled($id)
        );
        $validated['tingkat'] = collect($validated['tingkat'] ?? [])
            ->filter(fn ($tingkat) => is_numeric($tingkat))
            ->map(fn ($tingkat) => (int) $tingkat)
            ->unique()
            ->values()
            ->all();
        $validated['semester'] = collect($validated['semester'] ?? [])
            ->filter(fn ($semester) => is_numeric($semester))
            ->map(fn ($semester) => (int) $semester)
            ->unique()
            ->values()
            ->all();

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
            $rdmCount = $mapel->rdmMappings()->count();
            $jadwalCount = $mapel->jadwalPelajaran()->count();

            if ($rdmCount > 0 || $jadwalCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Mapel tidak dihapus karena dipakai {$rdmCount} mapping RDM dan {$jadwalCount} jadwal. Nonaktifkan mapel jika tidak lagi digunakan.",
                ], 422);
            }

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
