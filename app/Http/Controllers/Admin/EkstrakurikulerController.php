<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerAnggota;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Gtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class EkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Ekstrakurikuler::with(['tahunPelajaran', 'pembina']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('pembina_nama', function ($row) {
                    return $row->pembina?->nama ?? '-';
                })
                ->addColumn('jumlah_anggota', function ($row) {
                    return $row->jumlah_anggota . ' / ' . ($row->kuota_max ?? '∞');
                })
                ->addColumn('jadwal', function ($row) {
                    return $row->hari_kegiatan . ' ' . $row->waktu_kegiatan;
                })
                ->addColumn('status', function ($row) {
                    return $row->is_aktif
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.ekstrakurikuler.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.ekstrakurikuler.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<a href="' . route('admin.ekstrakurikuler.anggota', $row->id) . '" class="btn btn-sm btn-primary" title="Anggota"><i class="fas fa-users"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.ekstrakurikuler.index');
    }

    public function create()
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $pembina = Gtk::aktif()->orderBy('nama')->get();

        return view('admin.ekstrakurikuler.create', compact('tahunPelajaran', 'pembina'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:gtk,id',
            'hari_kegiatan' => 'nullable|string|max:50',
            'waktu_kegiatan' => 'nullable|string|max:50',
            'tempat' => 'nullable|string|max:255',
            'kuota_max' => 'nullable|integer|min:1',
            'biaya' => 'nullable|numeric|min:0',
            'is_wajib' => 'boolean',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_wajib'] = $request->boolean('is_wajib');
        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        Ekstrakurikuler::create($validated);

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil ditambahkan');
    }

    public function show(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->load(['tahunPelajaran', 'pembina', 'anggotaAktif.siswa']);

        return view('admin.ekstrakurikuler.show', compact('ekstrakurikuler'));
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $pembina = Gtk::aktif()->orderBy('nama')->get();

        return view('admin.ekstrakurikuler.edit', compact('ekstrakurikuler', 'tahunPelajaran', 'pembina'));
    }

    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:gtk,id',
            'hari_kegiatan' => 'nullable|string|max:50',
            'waktu_kegiatan' => 'nullable|string|max:50',
            'tempat' => 'nullable|string|max:255',
            'kuota_max' => 'nullable|integer|min:1',
            'biaya' => 'nullable|numeric|min:0',
            'is_wajib' => 'boolean',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_wajib'] = $request->boolean('is_wajib');
        $validated['is_aktif'] = $request->boolean('is_aktif');

        $ekstrakurikuler->update($validated);

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil diperbarui');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->delete();

        return response()->json(['success' => true, 'message' => 'Ekstrakurikuler berhasil dihapus']);
    }

    // Anggota Management
    public function anggota(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        if ($request->ajax()) {
            $query = EkstrakurikulerAnggota::with(['siswa', 'tahunPelajaran'])
                ->where('ekstrakurikuler_id', $ekstrakurikuler->id);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('siswa_nama', function ($row) {
                    return $row->siswa?->nama ?? '-';
                })
                ->addColumn('siswa_nis', function ($row) {
                    return $row->siswa?->nis ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'aktif' => 'success',
                        'tidak_aktif' => 'secondary',
                        'keluar' => 'danger',
                    ];
                    $badge = $badges[$row->status] ?? 'secondary';
                    return '<span class="badge badge-' . $badge . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
                })
                ->addColumn('nilai_predikat', function ($row) {
                    if ($row->nilai_ekskul) {
                        return $row->nilai_ekskul . ' (' . $row->predikat . ')';
                    }
                    return '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-warning btn-edit-anggota" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete-anggota" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $siswa = Siswa::aktif()
            ->whereNotIn('id', function ($query) use ($ekstrakurikuler) {
                $query->select('siswa_id')
                    ->from('ekstrakurikuler_anggota')
                    ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
                    ->whereNull('deleted_at');
            })
            ->orderBy('nama')
            ->get();

        $tahunPelajaranAktif = TahunPelajaran::where('is_aktif', true)->first();

        return view('admin.ekstrakurikuler.anggota', compact('ekstrakurikuler', 'siswa', 'tahunPelajaranAktif'));
    }

    public function storeAnggota(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'tanggal_bergabung' => 'required|date',
            'jabatan' => 'nullable|string|max:50',
        ]);

        // Check if already exists
        $exists = EkstrakurikulerAnggota::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah terdaftar di ekstrakurikuler ini',
            ], 422);
        }

        // Check kuota
        if ($ekstrakurikuler->kuota_max && $ekstrakurikuler->sisa_kuota <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota ekstrakurikuler sudah penuh',
            ], 422);
        }

        $validated['ekstrakurikuler_id'] = $ekstrakurikuler->id;
        $validated['status'] = 'aktif';
        $validated['created_by'] = Auth::id();

        EkstrakurikulerAnggota::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil ditambahkan',
        ]);
    }

    public function updateAnggota(Request $request, EkstrakurikulerAnggota $anggota)
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,tidak_aktif,keluar',
            'jabatan' => 'nullable|string|max:50',
            'tanggal_keluar' => 'nullable|date',
            'nilai_ekskul' => 'nullable|integer|min:0|max:100',
            'predikat' => 'nullable|string|max:5',
            'catatan' => 'nullable|string',
        ]);

        // Auto calculate predikat
        if (isset($validated['nilai_ekskul']) && !isset($validated['predikat'])) {
            $validated['predikat'] = EkstrakurikulerAnggota::hitungPredikat($validated['nilai_ekskul']);
        }

        $anggota->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diperbarui',
        ]);
    }

    public function destroyAnggota(EkstrakurikulerAnggota $anggota)
    {
        $anggota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil dihapus',
        ]);
    }
}
