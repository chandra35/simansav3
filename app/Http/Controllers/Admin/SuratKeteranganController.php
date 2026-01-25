<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use App\Models\SuratKeterangan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratKeteranganController extends Controller
{
    // ==================== TEMPLATE SURAT ====================
    public function template(Request $request)
    {
        if ($request->ajax()) {
            $query = TemplateSurat::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kategori_label', function ($row) {
                    return $row->kategori_label;
                })
                ->addColumn('status', function ($row) {
                    return $row->is_aktif
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.surat-keterangan.template.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-info btn-preview" data-id="' . $row->id . '" title="Preview"><i class="fas fa-eye"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $kategori = TemplateSurat::KATEGORI;

        return view('admin.surat-keterangan.template', compact('kategori'));
    }

    public function createTemplate()
    {
        $kategori = TemplateSurat::KATEGORI;
        $variabel = TemplateSurat::DEFAULT_VARIABEL;

        return view('admin.surat-keterangan.template-create', compact('kategori', 'variabel'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:20|unique:template_surat,kode',
            'kategori' => 'required|in:keterangan_aktif,keterangan_lulus,keterangan_pindah,keterangan_berkelakuan_baik,rekomendasi,lainnya',
            'template_content' => 'required|string',
            'variabel' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        TemplateSurat::create($validated);

        return redirect()->route('admin.surat-keterangan.template')
            ->with('success', 'Template surat berhasil ditambahkan');
    }

    public function editTemplate(TemplateSurat $template)
    {
        $kategori = TemplateSurat::KATEGORI;
        $variabel = TemplateSurat::DEFAULT_VARIABEL;

        return view('admin.surat-keterangan.template-edit', compact('template', 'kategori', 'variabel'));
    }

    public function updateTemplate(Request $request, TemplateSurat $template)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:20|unique:template_surat,kode,' . $template->id,
            'kategori' => 'required|in:keterangan_aktif,keterangan_lulus,keterangan_pindah,keterangan_berkelakuan_baik,rekomendasi,lainnya',
            'template_content' => 'required|string',
            'variabel' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');

        $template->update($validated);

        return redirect()->route('admin.surat-keterangan.template')
            ->with('success', 'Template surat berhasil diperbarui');
    }

    public function destroyTemplate(TemplateSurat $template)
    {
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template surat berhasil dihapus',
        ]);
    }

    // ==================== SURAT KETERANGAN ====================
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeterangan::with(['template', 'siswa', 'createdBy', 'approvedBy']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor', function ($row) {
                    return $row->nomor_surat ?? '-';
                })
                ->addColumn('siswa_nama', function ($row) {
                    return $row->siswa?->nama ?? '-';
                })
                ->addColumn('jenis', function ($row) {
                    return $row->template?->nama ?? '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_surat?->format('d/m/Y') ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.surat-keterangan.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    if ($row->status === 'approved' || $row->status === 'printed') {
                        $btn .= '<a href="' . route('admin.surat-keterangan.print', $row->id) . '" class="btn btn-sm btn-primary" target="_blank" title="Cetak"><i class="fas fa-print"></i></a>';
                    }
                    if ($row->status === 'pending') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $row->id . '" title="Setujui"><i class="fas fa-check"></i></button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $row->id . '" title="Tolak"><i class="fas fa-times"></i></button>';
                    }
                    if ($row->status === 'draft') {
                        $btn .= '<a href="' . route('admin.surat-keterangan.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    }
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $status = SuratKeterangan::STATUS;

        return view('admin.surat-keterangan.index', compact('status'));
    }

    public function create()
    {
        $templates = TemplateSurat::aktif()->get();
        $siswa = Siswa::orderBy('nama')->get();

        return view('admin.surat-keterangan.create', compact('templates', 'siswa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'siswa_id' => 'required|exists:siswa,id',
            'keperluan' => 'required|string|max:255',
            'data_tambahan' => 'nullable|array',
        ]);

        $validated['nomor_surat'] = SuratKeterangan::generateNomorSurat();
        $validated['tanggal_surat'] = now();
        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $surat = SuratKeterangan::create($validated);

        return redirect()->route('admin.surat-keterangan.show', $surat->id)
            ->with('success', 'Surat keterangan berhasil dibuat, menunggu persetujuan');
    }

    public function show(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->load(['template', 'siswa', 'createdBy', 'approvedBy']);

        // Get sekolah settings
        $sekolah = \App\Models\Pengaturan::first();

        return view('admin.surat-keterangan.show', compact('suratKeterangan', 'sekolah'));
    }

    public function edit(SuratKeterangan $suratKeterangan)
    {
        if (!in_array($suratKeterangan->status, ['draft', 'pending'])) {
            return back()->with('error', 'Surat yang sudah disetujui tidak dapat diedit');
        }

        $templates = TemplateSurat::aktif()->get();
        $siswa = Siswa::orderBy('nama')->get();

        return view('admin.surat-keterangan.edit', compact('suratKeterangan', 'templates', 'siswa'));
    }

    public function update(Request $request, SuratKeterangan $suratKeterangan)
    {
        if ($suratKeterangan->status !== 'draft') {
            return back()->with('error', 'Surat yang sudah diajukan tidak dapat diedit');
        }

        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'siswa_id' => 'required|exists:siswa,id',
            'keperluan' => 'required|string|max:255',
            'data_tambahan' => 'nullable|array',
        ]);

        $suratKeterangan->update($validated);

        return redirect()->route('admin.surat-keterangan.show', $suratKeterangan->id)
            ->with('success', 'Surat keterangan berhasil diperbarui');
    }

    public function destroy(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan berhasil dihapus',
        ]);
    }

    public function approve(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan berhasil disetujui',
        ]);
    }

    public function reject(Request $request, SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan ditolak',
        ]);
    }

    public function print(SuratKeterangan $suratKeterangan)
    {
        if (!in_array($suratKeterangan->status, ['approved', 'printed'])) {
            return back()->with('error', 'Surat belum disetujui');
        }

        $suratKeterangan->load(['template', 'siswa']);

        $siswa = $suratKeterangan->siswa;
        $template = $suratKeterangan->template;
        
        $additionalData = array_merge($suratKeterangan->data_tambahan ?? [], [
            '{{nomor_surat}}' => $suratKeterangan->nomor_surat,
            '{{tanggal_surat}}' => $suratKeterangan->tanggal_surat?->format('d F Y'),
            '{{keperluan}}' => $suratKeterangan->keperluan,
        ]);

        $content = $template->generateContent($siswa, $additionalData);

        // Update status to printed
        if ($suratKeterangan->status === 'approved') {
            $suratKeterangan->update(['status' => 'printed']);
        }

        $pdf = Pdf::loadView('admin.surat-keterangan.print', compact('suratKeterangan', 'content'));
        
        return $pdf->stream('surat-' . $suratKeterangan->nomor_surat . '.pdf');
    }
}
