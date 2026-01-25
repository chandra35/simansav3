<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanKonseling;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Gtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class CatatanKonselingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = CatatanKonseling::with(['siswa', 'konselor', 'tahunPelajaran'])
                ->when($request->status, function ($q) use ($request) {
                    return $q->where('status', $request->status);
                })
                ->when($request->kategori, function ($q) use ($request) {
                    return $q->where('kategori_masalah', $request->kategori);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('siswa_nama', function ($row) {
                    return $row->siswa?->nama ?? '-';
                })
                ->addColumn('konselor_nama', function ($row) {
                    return $row->konselor?->nama ?? '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_konseling?->format('d/m/Y') ?? '-';
                })
                ->addColumn('jenis_label', function ($row) {
                    return $row->jenis_label;
                })
                ->addColumn('kategori_label', function ($row) {
                    return $row->kategori_label;
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('rahasia', function ($row) {
                    return $row->is_rahasia
                        ? '<i class="fas fa-lock text-danger" title="Rahasia"></i>'
                        : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.catatan-konseling.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.catatan-konseling.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'rahasia', 'action'])
                ->make(true);
        }

        $status = CatatanKonseling::STATUS;
        $kategori = CatatanKonseling::KATEGORI_MASALAH;

        return view('admin.catatan-konseling.index', compact('status', 'kategori'));
    }

    public function create()
    {
        $siswa = Siswa::aktif()->orderBy('nama')->get();
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $konselor = Gtk::aktif()->orderBy('nama')->get();
        $jenis = CatatanKonseling::JENIS_KONSELING;
        $kategori = CatatanKonseling::KATEGORI_MASALAH;
        $status = CatatanKonseling::STATUS;

        return view('admin.catatan-konseling.create', compact(
            'siswa', 'tahunPelajaran', 'konselor', 'jenis', 'kategori', 'status'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'konselor_id' => 'required|exists:gtk,id',
            'tanggal_konseling' => 'required|date',
            'jenis_konseling' => 'required|in:individual,kelompok,klasikal',
            'kategori_masalah' => 'required|in:akademik,pribadi,sosial,karir,keluarga,lainnya',
            'deskripsi_masalah' => 'required|string',
            'hasil_konseling' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'required|in:proses,selesai,perlu_tindak_lanjut',
            'jadwal_tindak_lanjut' => 'nullable|date|after:tanggal_konseling',
            'is_rahasia' => 'boolean',
        ]);

        $validated['is_rahasia'] = $request->boolean('is_rahasia');

        CatatanKonseling::create($validated);

        return redirect()->route('admin.catatan-konseling.index')
            ->with('success', 'Catatan konseling berhasil ditambahkan');
    }

    public function show(CatatanKonseling $catatanKonseling)
    {
        $catatanKonseling->load(['siswa', 'konselor', 'tahunPelajaran']);
        
        // Get riwayat konseling for same siswa
        $riwayatKonseling = CatatanKonseling::with('konselor')
            ->where('siswa_id', $catatanKonseling->siswa_id)
            ->where('id', '!=', $catatanKonseling->id)
            ->orderBy('tanggal_konseling', 'desc')
            ->limit(5)
            ->get();

        return view('admin.catatan-konseling.show', compact('catatanKonseling', 'riwayatKonseling'));
    }

    public function edit(CatatanKonseling $catatanKonseling)
    {
        $siswa = Siswa::aktif()->orderBy('nama')->get();
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $konselor = Gtk::aktif()->orderBy('nama')->get();
        $jenis = CatatanKonseling::JENIS_KONSELING;
        $kategori = CatatanKonseling::KATEGORI_MASALAH;
        $status = CatatanKonseling::STATUS;

        return view('admin.catatan-konseling.edit', compact(
            'catatanKonseling', 'siswa', 'tahunPelajaran', 'konselor', 'jenis', 'kategori', 'status'
        ));
    }

    public function update(Request $request, CatatanKonseling $catatanKonseling)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'konselor_id' => 'required|exists:gtk,id',
            'tanggal_konseling' => 'required|date',
            'jenis_konseling' => 'required|in:individual,kelompok,klasikal',
            'kategori_masalah' => 'required|in:akademik,pribadi,sosial,karir,keluarga,lainnya',
            'deskripsi_masalah' => 'required|string',
            'hasil_konseling' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'required|in:proses,selesai,perlu_tindak_lanjut',
            'jadwal_tindak_lanjut' => 'nullable|date|after:tanggal_konseling',
            'is_rahasia' => 'boolean',
        ]);

        $validated['is_rahasia'] = $request->boolean('is_rahasia');

        $catatanKonseling->update($validated);

        return redirect()->route('admin.catatan-konseling.index')
            ->with('success', 'Catatan konseling berhasil diperbarui');
    }

    public function destroy(CatatanKonseling $catatanKonseling)
    {
        $catatanKonseling->delete();

        return response()->json(['success' => true, 'message' => 'Catatan konseling berhasil dihapus']);
    }

    // Report per siswa
    public function reportSiswa(Request $request)
    {
        $siswaId = $request->siswa_id;
        $siswa = null;
        $catatan = collect();

        if ($siswaId) {
            $siswa = Siswa::find($siswaId);
            $catatan = CatatanKonseling::with(['konselor', 'tahunPelajaran'])
                ->where('siswa_id', $siswaId)
                ->orderBy('tanggal_konseling', 'desc')
                ->get();
        }

        $siswaList = Siswa::orderBy('nama')->get();

        return view('admin.catatan-konseling.report-siswa', compact('siswaList', 'siswa', 'catatan'));
    }
}
