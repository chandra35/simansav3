<?php

namespace App\Http\Controllers\Admin\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BukuNomorSurat;
use App\Models\Gtk;
use App\Models\SuratKeterangan;
use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Services\SuratNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BukuNomorSuratController extends Controller
{
    protected SuratNumberService $suratNumberService;

    public function __construct(SuratNumberService $suratNumberService)
    {
        $this->suratNumberService = $suratNumberService;
    }

    public function index(Request $request)
    {
        $this->ensureAccess('view-layanan-surat');

        if ($request->ajax()) {
            $query = BukuNomorSurat::with(['config', 'penandatangan', 'createdBy', 'dibatalkanOleh'])
                ->select('buku_nomor_surat.*');

            if ($request->filled('tahun')) {
                $query->where('tahun', (int) $request->tahun);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('kode_klasifikasi')) {
                $query->where('kode_klasifikasi', $request->kode_klasifikasi);
            }

            if ($request->filled('modul_asal')) {
                $query->where('modul_asal', $request->modul_asal);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('nomor_surat', function ($row) {
                    $copyBtn = '<button type="button" class="btn btn-xs btn-outline-secondary ml-1 btn-copy-nomor" data-nomor="' . e($row->nomor_surat) . '" title="Salin Nomor"><i class="fas fa-copy"></i></button>';
                    $isBatal = $row->status === 'dibatalkan' ? ' <span class="badge badge-danger">BATAL</span>' : '';
                    return '<strong>' . e($row->nomor_surat) . '</strong>' . $copyBtn . $isBatal;
                })
                ->editColumn('tanggal_surat', function ($row) {
                    return $row->tanggal_surat ? $row->tanggal_surat->translatedFormat('d/m/Y') : '-';
                })
                ->addColumn('klasifikasi', function ($row) {
                    $namaConfig = $row->config?->nama ?? $row->kode_klasifikasi;
                    return '<span class="badge badge-info">' . e($row->kode_klasifikasi) . '</span><br><small class="text-muted">' . e(Str::limit($namaConfig, 25)) . '</small>';
                })
                ->editColumn('perihal', function ($row) {
                    $html = '<div><strong>' . e($row->perihal) . '</strong></div>';
                    if ($row->tujuan) {
                        $html .= '<small class="text-muted"><i class="fas fa-paper-plane mr-1"></i>Tujuan: ' . e($row->tujuan) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('penandatangan_nama', function ($row) {
                    $nama = $row->nama_penandatangan ?: ($row->penandatangan?->nama_lengkap ?? '-');
                    $jabatan = $row->jabatan_penandatangan ?: 'Kepala Madrasah';
                    return e($nama) . '<br><small class="text-muted">' . e($jabatan) . '</small>';
                })
                ->addColumn('modul_badge', function ($row) {
                    $badgeColors = [
                        'manual_tu' => 'secondary',
                        'surat_keterangan' => 'primary',
                        'mutasi_siswa' => 'warning',
                        'surat_tugas' => 'teal',
                        'sk' => 'purple',
                        'undangan' => 'info',
                    ];
                    $color = $badgeColors[$row->modul_asal] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . e($row->modul_asal_label) . '</span>';
                })
                ->editColumn('status', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . e($row->status_label) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group btn-group-sm">';
                    $btn .= '<button type="button" class="btn btn-info btn-detail" data-id="' . $row->id . '" title="Lihat Detail"><i class="fas fa-eye"></i></button>';

                    // Link to source document if applicable
                    if ($row->suratable_type === SuratKeterangan::class && $row->suratable_id) {
                        $btn .= '<a href="' . route('admin.surat-keterangan.print', $row->suratable_id) . '" target="_blank" class="btn btn-primary" title="Cetak Surat Keterangan"><i class="fas fa-print"></i></a>';
                    }

                    if ($row->status === 'aktif' && auth()->user()->can('manage-layanan-surat')) {
                        $btn .= '<button type="button" class="btn btn-danger btn-cancel-nomor" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_surat) . '" title="Batalkan Nomor"><i class="fas fa-ban"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_surat', 'klasifikasi', 'perihal', 'penandatangan_nama', 'modul_badge', 'status', 'action'])
                ->make(true);
        }

        $activeYear = $this->suratNumberService->year();
        $selectedYear = (int) $request->input('tahun', $activeYear);

        $stats = [
            'total' => BukuNomorSurat::where('tahun', $selectedYear)->count(),
            'aktif' => BukuNomorSurat::where('tahun', $selectedYear)->where('status', 'aktif')->count(),
            'dibatalkan' => BukuNomorSurat::where('tahun', $selectedYear)->where('status', 'dibatalkan')->count(),
            'counter' => SuratNomorCounter::where('tahun', $selectedYear)->value('nomor_terakhir') ?? 0,
        ];

        $configs = SuratNomorConfig::where('is_active', true)->orderBy('nama')->get();
        $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();

        $years = BukuNomorSurat::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun')->toArray();
        if (!in_array($activeYear, $years)) {
            array_unshift($years, $activeYear);
        }

        $previewNomor = $this->suratNumberService->preview('PP');
        $setting = AppSetting::getInstance();

        return view('admin.tata-usaha.buku-nomor-surat.index', compact(
            'stats',
            'configs',
            'gtks',
            'years',
            'selectedYear',
            'activeYear',
            'previewNomor',
            'setting'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAccess('manage-layanan-surat');

        $validated = $request->validate([
            'kode' => 'required|string|exists:surat_nomor_configs,kode',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'nullable|string|max:255',
            'penandatangan_id' => 'nullable|exists:gtks,id',
            'nama_penandatangan' => 'nullable|string|max:255',
            'jabatan_penandatangan' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $gtk = !empty($validated['penandatangan_id']) ? Gtk::find($validated['penandatangan_id']) : null;
        $namaPenandatangan = $gtk ? $gtk->nama_lengkap : ($validated['nama_penandatangan'] ?? null);
        $jabatanPenandatangan = $validated['jabatan_penandatangan'] ?: 'Kepala Madrasah';

        $buku = $this->suratNumberService->issue(
            $validated['kode'],
            Carbon::parse($validated['tanggal_surat']),
            [
                'perihal' => $validated['perihal'],
                'tujuan' => $validated['tujuan'] ?? null,
                'modul_asal' => 'manual_tu',
                'penandatangan_id' => $gtk?->id,
                'nama_penandatangan' => $namaPenandatangan,
                'jabatan_penandatangan' => $jabatanPenandatangan,
                'created_by' => Auth::id(),
                'keterangan' => $validated['keterangan'] ?? null,
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'nomor_surat' => $buku->nomor_surat,
                'message' => "Nomor {$buku->nomor_surat} berhasil diterbitkan dan dicatat dalam Buku Agenda Surat!",
                'data' => $buku,
            ]);
        }

        return redirect()->route('admin.tata-usaha.buku-nomor-surat.index')
            ->with('success', "Nomor {$buku->nomor_surat} berhasil diterbitkan!");
    }

    public function show($id)
    {
        $this->ensureAccess('view-layanan-surat');

        $buku = BukuNomorSurat::with(['config', 'penandatangan', 'createdBy', 'dibatalkanOleh', 'suratable'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $buku->id,
                'nomor_surat' => $buku->nomor_surat,
                'nomor_urut' => $buku->nomor_urut,
                'tahun' => $buku->tahun,
                'tanggal_surat' => $buku->tanggal_surat ? $buku->tanggal_surat->translatedFormat('d F Y') : '-',
                'kode_klasifikasi' => $buku->kode_klasifikasi,
                'nama_klasifikasi' => $buku->config?->nama ?? $buku->kode_klasifikasi,
                'perihal' => $buku->perihal,
                'tujuan' => $buku->tujuan ?: '-',
                'modul_asal' => $buku->modul_asal_label,
                'penandatangan' => $buku->nama_penandatangan ?: ($buku->penandatangan?->nama_lengkap ?? '-'),
                'jabatan_penandatangan' => $buku->jabatan_penandatangan ?: 'Kepala Madrasah',
                'status' => $buku->status_label,
                'status_raw' => $buku->status,
                'alasan_dibatalkan' => $buku->alasan_dibatalkan ?: '-',
                'dibatalkan_oleh' => $buku->dibatalkanOleh?->name ?: '-',
                'dibatalkan_at' => $buku->dibatalkan_at ? $buku->dibatalkan_at->translatedFormat('d F Y H:i') : '-',
                'created_by' => $buku->createdBy?->name ?: 'Sistem',
                'created_at' => $buku->created_at ? $buku->created_at->translatedFormat('d F Y H:i') : '-',
                'keterangan' => $buku->keterangan ?: '-',
                'suratable_type' => $buku->suratable_type,
                'suratable_id' => $buku->suratable_id,
                'surat_keterangan_print_url' => ($buku->suratable_type === SuratKeterangan::class && $buku->suratable_id)
                    ? route('admin.surat-keterangan.print', $buku->suratable_id)
                    : null,
            ],
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $this->ensureAccess('manage-layanan-surat');

        $validated = $request->validate([
            'alasan_dibatalkan' => 'required|string|min:3|max:500',
        ]);

        $buku = BukuNomorSurat::findOrFail($id);

        if ($buku->status === 'dibatalkan') {
            return response()->json([
                'success' => false,
                'message' => 'Nomor surat sudah dalam status dibatalkan.',
            ], 422);
        }

        $this->suratNumberService->cancel($buku, $validated['alasan_dibatalkan'], Auth::id());

        return response()->json([
            'success' => true,
            'message' => "Nomor surat {$buku->nomor_surat} berhasil dibatalkan.",
        ]);
    }

    public function preview(Request $request)
    {
        $this->ensureAccess('view-layanan-surat');
        $kode = $request->query('kode', 'PP');
        $tanggal = $request->query('tanggal') ? Carbon::parse($request->query('tanggal')) : now();

        try {
            $preview = $this->suratNumberService->preview($kode, $tanggal);
            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function printAgenda(Request $request)
    {
        $this->ensureAccess('view-layanan-surat');

        $tahun = (int) $request->input('tahun', $this->suratNumberService->year());
        $status = $request->input('status');
        $kodeKlasifikasi = $request->input('kode_klasifikasi');

        $query = BukuNomorSurat::with(['config', 'penandatangan', 'createdBy'])
            ->where('tahun', $tahun)
            ->orderBy('nomor_urut', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($kodeKlasifikasi) {
            $query->where('kode_klasifikasi', $kodeKlasifikasi);
        }

        $items = $query->get();
        $setting = AppSetting::getInstance();
        $logoKemenagDataUri = $this->imageDataUri(
            $setting->logo_kemenag_path,
            public_path('vendor/adminlte/dist/img/logo-kemenag.png')
        );
        $logoSekolahDataUri = $this->imageDataUri(
            $setting->logo_sekolah_path,
            public_path('vendor/adminlte/dist/img/logo-sekolah.png')
        );

        return view('admin.tata-usaha.buku-nomor-surat.print-agenda', compact(
            'items',
            'tahun',
            'status',
            'kodeKlasifikasi',
            'setting',
            'logoKemenagDataUri',
            'logoSekolahDataUri'
        ));
    }

    private function imageDataUri(?string $configuredPath, string $fallbackPath): string
    {
        $paths = array_filter([$configuredPath ? storage_path('app/public/'.$configuredPath) : null, $fallbackPath]);
        foreach ($paths as $path) {
            if (!is_file($path)) continue;
            $image = @imagecreatefromstring((string) file_get_contents($path));
            if ($image === false) continue;

            $width = imagesx($image);
            $height = imagesy($image);
            $maxHeight = 110;
            $newHeight = min($height, $maxHeight);
            $newWidth = max(1, (int) round(($width / max(1, $height)) * $newHeight));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagepng($resized, null, 6);
            $content = ob_get_clean();
            imagedestroy($image);
            imagedestroy($resized);

            return 'data:image/png;base64,'.base64_encode($content);
        }

        return '';
    }

    private function ensureAccess(string $permission): void
    {
        $user = request()->user();
        abort_unless(
            $user && ($user->isStaffTu() || $user->can($permission) || $user->can('view-tata-usaha') || $user->hasAnyRole(['Super Admin', 'Admin'])),
            403
        );
    }
}
