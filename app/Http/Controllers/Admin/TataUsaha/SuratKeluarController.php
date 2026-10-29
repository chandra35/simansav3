<?php

namespace App\Http\Controllers\Admin\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use App\Models\SuratKeluar;
use App\Models\Gtk;
use App\Models\AppSetting;
use App\Models\TahunPelajaran;
use App\Services\SuratNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratKeluarController extends Controller
{
    protected SuratNumberService $suratNumberService;

    public function __construct(SuratNumberService $suratNumberService)
    {
        $this->suratNumberService = $suratNumberService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeluar::with(['template', 'penandatangan', 'createdBy'])->latest('tanggal_surat');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor', function ($row) {
                    return $row->nomor_surat ?? '-';
                })
                ->addColumn('perihal', function ($row) {
                    return Str::limit($row->perihal, 50);
                })
                ->addColumn('tujuan', function ($row) {
                    return $row->tujuan ? Str::limit($row->tujuan, 40) : '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_surat?->translatedFormat('d/m/Y') ?? '-';
                })
                ->addColumn('template', function ($row) {
                    return $row->template?->nama ?? '-';
                })
                ->addColumn('penandatangan', function ($row) {
                    return $row->nama_penandatangan ?? ($row->penandatangan?->nama_lengkap ?? '-');
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.tata-usaha.surat-keluar.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.tata-usaha.surat-keluar.print', $row->id) . '" class="btn btn-sm btn-primary" target="_blank" title="Cetak PDF"><i class="fas fa-print"></i></a>';
                    if (auth()->user()->can('manage-layanan-surat') || Auth::id() === $row->created_by) {
                        if ($row->status !== 'selesai') {
                            $btn .= '<a href="' . route('admin.tata-usaha.surat-keluar.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                        }
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['perihal', 'tujuan', 'status_badge', 'action'])
                ->make(true);
        }

        $status = SuratKeluar::STATUS;
        $templates = TemplateSurat::active()->get();

        return view('admin.tata-usaha.surat-keluar.index', compact('status', 'templates'));
    }

    // Wizard Step 1: Choose type/template
    public function create(Request $request)
    {
        $step = $request->query('step', 1);
        $templates = TemplateSurat::active()->get();
        $selectedTemplate = null;

        if ($request->filled('template_id')) {
            $selectedTemplate = TemplateSurat::find($request->input('template_id'));
        }

        if ($step == 1) {
            return view('admin.tata-usaha.surat-keluar.wizard-step1', compact('templates'));
        } elseif ($step == 2 && $selectedTemplate) {
            $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();
            $setting = AppSetting::getInstance();

            return view('admin.tata-usaha.surat-keluar.wizard-step2', compact('selectedTemplate', 'gtks', 'setting'));
        } elseif ($step == 3 && $selectedTemplate) {
            $stepData = $request->only([
                'tanggal_surat', 'perihal', 'tujuan', 'penandatangan_id',
                'nama_penandatangan', 'jabatan_penandatangan',
            ]);

            $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();

            return view('admin.tata-usaha.surat-keluar.wizard-step3', compact('selectedTemplate', 'stepData', 'gtks'));
        }

        return redirect()->route('admin.tata-usaha.surat-keluar.create', ['step' => 1]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'nullable|string|max:255',
            'konten_generate' => 'required|string',
            'penandatangan_id' => 'required|exists:gtks,id',
            'nama_penandatangan' => 'nullable|string|max:255',
            'jabatan_penandatangan' => 'nullable|string|max:255',
            'status' => 'required|in:draft,menunggu_ttd',
            'data_tambahan' => 'nullable|array',
        ]);

        $template = TemplateSurat::findOrFail($validated['template_surat_id']);
        $penandatangan = Gtk::findOrFail($validated['penandatangan_id']);

        $tanggalSurat = Carbon::parse($validated['tanggal_surat']);

        $surat = SuratKeluar::create([
            'template_surat_id' => $validated['template_surat_id'],
            'nomor_surat' => null,
            'tanggal_surat' => $tanggalSurat->toDateString(),
            'perihal' => $validated['perihal'],
            'tujuan' => $validated['tujuan'],
            'konten_generate' => $validated['konten_generate'],
            'penandatangan_id' => $penandatangan->id,
            'nama_penandatangan' => $validated['nama_penandatangan'] ?? $penandatangan->nama_lengkap,
            'jabatan_penandatangan' => $validated['jabatan_penandatangan'] ?? 'Kepala Madrasah',
            'status' => $validated['status'],
            'data_tambahan' => $validated['data_tambahan'],
            'created_by' => Auth::id(),
        ]);

        if ($validated['status'] === 'menunggu_ttd') {
            $bukuNomor = $this->suratNumberService->issue(
                $template->kode_klasifikasi,
                $tanggalSurat,
                [
                    'perihal' => $surat->perihal,
                    'tujuan' => $surat->tujuan,
                    'modul_asal' => 'surat_keluar',
                    'suratable_type' => SuratKeluar::class,
                    'suratable_id' => $surat->id,
                    'penandatangan_id' => $penandatangan->id,
                    'nama_penandatangan' => $surat->nama_penandatangan,
                    'jabatan_penandatangan' => $surat->jabatan_penandatangan,
                    'created_by' => Auth::id(),
                ]
            );
            $surat->update(['nomor_surat' => $bukuNomor->nomor_surat]);
        }

        return redirect()->route('admin.tata-usaha.surat-keluar.show', $surat->id)
            ->with('success', $surat->nomor_surat
                ? 'Surat keluar berhasil dibuat dengan nomor: ' . $surat->nomor_surat
                : 'Draft surat keluar berhasil disimpan tanpa nomor surat.');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['template', 'penandatangan', 'createdBy']);

        return view('admin.tata-usaha.surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status === 'selesai' && !Auth::user()->can('manage-layanan-surat')) {
            abort(403, 'Tidak dapat mengedit surat yang sudah selesai.');
        }

        $suratKeluar->load(['template', 'penandatangan']);
        $templates = TemplateSurat::active()->get();
        $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();
        $setting = AppSetting::getInstance();

        return view('admin.tata-usaha.surat-keluar.edit', compact('suratKeluar', 'templates', 'gtks', 'setting'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status === 'selesai' && !Auth::user()->can('manage-layanan-surat')) {
            abort(403, 'Tidak dapat mengubah surat yang sudah selesai.');
        }

        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'nullable|string|max:255',
            'konten_generate' => 'required|string',
            'penandatangan_id' => 'required|exists:gtks,id',
            'nama_penandatangan' => 'nullable|string|max:255',
            'jabatan_penandatangan' => 'nullable|string|max:255',
            'status' => 'required|in:draft,menunggu_ttd,selesai,dibatalkan',
            'data_tambahan' => 'nullable|array',
        ]);

        $penandatangan = Gtk::findOrFail($validated['penandatangan_id']);

        $suratKeluar->update([
            'template_surat_id' => $validated['template_surat_id'],
            'tanggal_surat' => Carbon::parse($validated['tanggal_surat'])->toDateString(),
            'perihal' => $validated['perihal'],
            'tujuan' => $validated['tujuan'],
            'konten_generate' => $validated['konten_generate'],
            'penandatangan_id' => $penandatangan->id,
            'nama_penandatangan' => $validated['nama_penandatangan'] ?? $penandatangan->nama_lengkap,
            'jabatan_penandatangan' => $validated['jabatan_penandatangan'] ?? 'Kepala Madrasah',
            'status' => $validated['status'],
            'data_tambahan' => $validated['data_tambahan'],
        ]);

        return redirect()->route('admin.tata-usaha.surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Surat keluar berhasil diperbarui');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        if (Auth::id() !== $suratKeluar->created_by && !Auth::user()->can('manage-layanan-surat')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus surat ini'], 403);
        }

        $suratKeluar->delete();

        return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dihapus']);
    }

    public function print(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['template', 'penandatangan']);

        $html = view('admin.tata-usaha.surat-keluar.print', compact('suratKeluar'))->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);

        return $pdf->stream('surat-keluar-' . $suratKeluar->nomor_surat . '.pdf');
    }
}
