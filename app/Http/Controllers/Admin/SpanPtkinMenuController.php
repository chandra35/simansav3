<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiswaKelas;
use App\Models\SpanPtkinMenu;
use App\Models\SpanPtkinRegistration;
use App\Models\TahunPelajaran;
use App\Services\SpanPtkinAnnouncementService;
use App\Services\SpanPtkinPdfImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SpanPtkinMenuController extends Controller
{
    private const PREVIEW_SESSION_KEY = 'span_ptkin_import_preview';

    public function __construct(
        private readonly SpanPtkinPdfImportService $pdfImportService,
        private readonly SpanPtkinAnnouncementService $announcementService
    ) {
    }

    public function index()
    {
        $menus = SpanPtkinMenu::with('tahunPelajaran')
            ->latest()
            ->get();

        $activeTahun = TahunPelajaran::where('is_active', true)->first();

        return view('admin.span-ptkin.index', compact('menus', 'activeTahun'));
    }

    public function create()
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $activeTahun = TahunPelajaran::where('is_active', true)->first();
        $existingMenu = $activeTahun
            ? SpanPtkinMenu::where('tahun_pelajaran_id', $activeTahun->id)->first()
            : null;

        return view('admin.span-ptkin.create', compact('tahunPelajaranList', 'activeTahun', 'existingMenu'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id|unique:span_ptkin_menus,tahun_pelajaran_id',
            'konten_informasi' => 'nullable|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tahun = TahunPelajaran::findOrFail($validated['tahun_pelajaran_id']);
        if (!$tahun->is_active) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Menu SPAN-PTKIN hanya dapat dibuat pada tahun pelajaran aktif.');
        }

        SpanPtkinMenu::create([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.span-ptkin-menu.index')
            ->with('success', 'Menu SPAN-PTKIN berhasil dibuat.');
    }

    public function show(SpanPtkinMenu $spanPtkinMenu)
    {
        $spanPtkinMenu->load('tahunPelajaran');

        $students = $this->kelas12Students($spanPtkinMenu);
        $registrationMap = SpanPtkinRegistration::with(['lulusan.referensiPerguruanTinggi', 'lulusan.referensiProgramStudi'])
            ->where('span_ptkin_menu_id', $spanPtkinMenu->id)
            ->get()
            ->keyBy('siswa_id');

        $monitoring = $students->map(function ($siswa) use ($registrationMap) {
            $siswa->setRelation('spanPtkinRegistration', $registrationMap->get($siswa->id));

            return $siswa;
        });

        $summary = [
            'kelas_12_total' => $monitoring->count(),
            'sudah_terimport' => $monitoring->filter(fn ($siswa) => filled(optional($siswa->spanPtkinRegistration)->nomor_pendaftaran))->count(),
            'belum_terimport' => $monitoring->filter(fn ($siswa) => blank(optional($siswa->spanPtkinRegistration)->nomor_pendaftaran))->count(),
            'terhubung_lulusan' => $monitoring->filter(fn ($siswa) => optional($siswa->spanPtkinRegistration)->lulusan !== null)->count(),
            'lulus' => $monitoring->filter(fn ($siswa) => optional($siswa->spanPtkinRegistration)->check_status === 'lulus')->count(),
            'tidak_lulus' => $monitoring->filter(fn ($siswa) => optional($siswa->spanPtkinRegistration)->check_status === 'tidak_lulus')->count(),
            'gagal_cek' => $monitoring->filter(fn ($siswa) => optional($siswa->spanPtkinRegistration)->check_status === 'gagal_cek')->count(),
            'belum_dicek' => $monitoring->filter(fn ($siswa) => (optional($siswa->spanPtkinRegistration)->check_status ?? 'belum_dicek') === 'belum_dicek')->count(),
        ];

        $previewImport = $this->getPreviewImport($spanPtkinMenu);

        return view('admin.span-ptkin.show', compact('spanPtkinMenu', 'monitoring', 'summary', 'previewImport'));
    }

    public function checkAnnouncement(SpanPtkinMenu $spanPtkinMenu, SpanPtkinRegistration $registration)
    {
        abort_unless($registration->span_ptkin_menu_id === $spanPtkinMenu->id, 404);

        $registration->load(['siswa', 'lulusan.referensiPerguruanTinggi', 'lulusan.referensiProgramStudi']);

        try {
            $result = $this->announcementService->checkRegistration($registration);

            return response()->json([
                'success' => true,
                'registration_id' => $registration->id,
                'siswa_id' => $registration->siswa_id,
                'result' => $result,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('SPAN-PTKIN announcement check failed', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);

            $registration->forceFill([
                'check_status' => 'gagal_cek',
                'last_checked_at' => now(),
                'last_check_message' => $exception->getMessage(),
                'last_check_payload' => [
                    '_meta' => [
                        'checked_at' => now()->toIso8601String(),
                    ],
                ],
            ])->save();

            return response()->json([
                'success' => false,
                'registration_id' => $registration->id,
                'siswa_id' => $registration->siswa_id,
                'message' => $exception->getMessage(),
                'result' => [
                    'status' => 'gagal_cek',
                    'status_label' => $registration->fresh()->check_status_label,
                    'message' => $exception->getMessage(),
                    'checked_at' => $registration->last_checked_at?->format('d-m-Y H:i:s'),
                    'source_url' => null,
                    'payload' => $registration->last_check_payload,
                ],
            ], 500);
        }
    }

    public function edit(SpanPtkinMenu $spanPtkinMenu)
    {
        if (!$spanPtkinMenu->isEditable()) {
            return redirect()->route('admin.span-ptkin-menu.show', $spanPtkinMenu)
                ->with('warning', 'Menu ini tidak dapat diedit karena tahun pelajaran sudah tidak aktif.');
        }

        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();

        return view('admin.span-ptkin.edit', compact('spanPtkinMenu', 'tahunPelajaranList'));
    }

    public function update(Request $request, SpanPtkinMenu $spanPtkinMenu)
    {
        if (!$spanPtkinMenu->isEditable()) {
            return redirect()->route('admin.span-ptkin-menu.show', $spanPtkinMenu)
                ->with('error', 'Menu ini tidak dapat diedit karena tahun pelajaran sudah tidak aktif.');
        }

        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'konten_informasi' => 'nullable|string',
            'is_active' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $spanPtkinMenu->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.span-ptkin-menu.index')
            ->with('success', 'Menu SPAN-PTKIN berhasil diperbarui.');
    }

    public function destroy(SpanPtkinMenu $spanPtkinMenu)
    {
        if (!$spanPtkinMenu->isEditable()) {
            return redirect()->route('admin.span-ptkin-menu.index')
                ->with('error', 'Menu ini tidak dapat dihapus karena tahun pelajaran sudah tidak aktif.');
        }

        $spanPtkinMenu->delete();

        return redirect()->route('admin.span-ptkin-menu.index')
            ->with('success', 'Menu SPAN-PTKIN berhasil dihapus.');
    }

    public function importPdf(Request $request, SpanPtkinMenu $spanPtkinMenu)
    {
        if (!$spanPtkinMenu->isEditable()) {
            return $this->respondAfterAction(
                $request,
                $spanPtkinMenu,
                'error',
                'Import PDF hanya tersedia pada tahun pelajaran aktif.',
                422
            );
        }

        $validated = $request->validate([
            'pdf_file' => 'required|file|mimetypes:application/pdf|max:5120',
        ], [
            'pdf_file.required' => 'Pilih file PDF daftar siswa SPAN-PTKIN terlebih dahulu.',
            'pdf_file.mimetypes' => 'File yang diunggah harus berupa PDF resmi.',
        ]);

        $preview = $this->pdfImportService->previewImport(
            $spanPtkinMenu,
            $validated['pdf_file']->getRealPath(),
            $validated['pdf_file']->getClientOriginalName()
        );

        Session::put($this->previewSessionKey($spanPtkinMenu), $preview);

        return $this->respondAfterAction(
            $request,
            $spanPtkinMenu,
            'success',
            'Preview import berhasil dibuat. Periksa hasil pencocokan sebelum menyimpan ke database.'
        );
    }

    public function confirmImport(Request $request, SpanPtkinMenu $spanPtkinMenu)
    {
        if (!$spanPtkinMenu->isEditable()) {
            return $this->respondAfterAction(
                $request,
                $spanPtkinMenu,
                'error',
                'Konfirmasi import hanya tersedia pada tahun pelajaran aktif.',
                422
            );
        }

        $preview = $this->getPreviewImport($spanPtkinMenu);
        if (!$preview) {
            return $this->respondAfterAction(
                $request,
                $spanPtkinMenu,
                'error',
                'Preview import tidak ditemukan. Upload PDF terlebih dahulu.',
                422
            );
        }

        $result = $this->pdfImportService->confirmImport($spanPtkinMenu, $preview);
        Session::forget($this->previewSessionKey($spanPtkinMenu));

        $message = sprintf(
            'Import selesai. %d baris dibaca, %d cocok, %d data baru, %d data diperbarui.',
            $result['total_rows'],
            $result['matched'],
            $result['created'],
            $result['updated']
        );

        if ($result['unmatched']->isNotEmpty()) {
            $previewText = $result['unmatched']
                ->take(5)
                ->map(fn (array $row) => $row['nisn'] . ' - ' . $row['nama_siswa'])
                ->implode(', ');

            $message .= ' Belum cocok: ' . $previewText;

            if ($result['unmatched']->count() > 5) {
                $message .= ' dan ' . ($result['unmatched']->count() - 5) . ' lainnya.';
            }
        }

        return $this->respondAfterAction(
            $request,
            $spanPtkinMenu,
            $result['unmatched']->isEmpty() ? 'success' : 'warning',
            $message
        );
    }

    public function cancelPreview(Request $request, SpanPtkinMenu $spanPtkinMenu)
    {
        Session::forget($this->previewSessionKey($spanPtkinMenu));

        return $this->respondAfterAction(
            $request,
            $spanPtkinMenu,
            'success',
            'Preview import dibatalkan.'
        );
    }

    private function kelas12Students(SpanPtkinMenu $menu)
    {
        return SiswaKelas::query()
            ->with(['siswa', 'kelas'])
            ->where('tahun_pelajaran_id', $menu->tahun_pelajaran_id)
            ->where('tingkat', 12)
            ->whereIn('status', ['aktif', 'lulus'])
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (SiswaKelas $siswaKelas) => $siswaKelas->siswa !== null)
            ->unique('siswa_id')
            ->map(function (SiswaKelas $siswaKelas) {
                $siswa = $siswaKelas->siswa;
                $siswa->setRelation('kelasTahunMenu', $siswaKelas->kelas);

                return $siswa;
            })
            ->sortBy('nama_lengkap')
            ->values();
    }

    private function previewSessionKey(SpanPtkinMenu $menu): string
    {
        return self::PREVIEW_SESSION_KEY . '.' . $menu->id;
    }

    private function getPreviewImport(SpanPtkinMenu $menu): ?array
    {
        $preview = Session::get($this->previewSessionKey($menu));

        return is_array($preview) ? $preview : null;
    }

    private function respondAfterAction(
        Request $request,
        SpanPtkinMenu $menu,
        string $status,
        string $message,
        int $httpStatus = 200
    ) {
        $redirectUrl = route('admin.span-ptkin-menu.show', $menu);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ], $httpStatus);
        }

        return redirect($redirectUrl)->with($status, $message);
    }
}
