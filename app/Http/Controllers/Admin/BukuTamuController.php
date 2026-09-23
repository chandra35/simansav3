<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BukuTamuExport;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BukuTamu;
use App\Models\BukuTamuQrToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BukuTamuController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request)->with(['provinsi', 'kota', 'kecamatan', 'kelurahan']);
        $stats = $this->statistics($request);
        $qrToken = BukuTamuQrToken::active()->latest('created_at')->first()
            ?: BukuTamuQrToken::create(['token' => Str::random(48), 'created_by' => auth()->id()]);
        $publicUrl = route('public.buku-tamu.token', $qrToken);
        $qrSvg = QrCode::format('svg')->size(220)->margin(1)->errorCorrection('M')->generate($publicUrl);

        return view('admin.buku-tamu.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'stats' => $stats,
            'maxTypeCount' => max(1, max($stats['types'])),
            'publicUrl' => $publicUrl,
            'qrSvg' => $qrSvg,
            'qrToken' => $qrToken,
            'setting' => AppSetting::getInstance(),
        ]);
    }

    public function show(BukuTamu $bukuTamu)
    {
        $bukuTamu->load(['provinsi', 'kota', 'kecamatan', 'kelurahan', 'qrToken']);
        $agent = new Agent();
        $agent->setUserAgent((string) $bukuTamu->user_agent);

        return response()->json([
            'id' => $bukuTamu->id,
            'tanggal' => $bukuTamu->tanggal_kunjungan?->format('d-m-Y'),
            'waktu' => $bukuTamu->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB',
            'jenis' => ucfirst((string) ($bukuTamu->jenis_tamu ?: 'individu')),
            'nama' => $bukuTamu->nama,
            'asal' => $bukuTamu->alamat_instansi ?: '-',
            'nomor_hp' => $bukuTamu->nomor_hp,
            'alamat' => $bukuTamu->alamat_lengkap ?: '-',
            'keperluan' => $bukuTamu->keperluan,
            'ip_address' => $bukuTamu->ip_address ?: '-',
            'device' => $agent->deviceType() ?: 'Unknown',
            'platform' => $agent->platform() ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'user_agent' => $bukuTamu->user_agent ?: '-',
            'qr_token' => $bukuTamu->qrToken?->token ?: '-',
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new BukuTamuExport($request->input('tanggal_mulai'), $request->input('tanggal_selesai')),
            'laporan-buku-tamu-'.now('Asia/Jakarta')->format('Ymd-His').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $setting = AppSetting::getInstance();
        $items = $this->filteredQuery($request)
            ->with(['provinsi', 'kota', 'kecamatan', 'kelurahan', 'qrToken'])
            ->get();
        $stats = $this->statistics($request);

        return Pdf::loadView('admin.buku-tamu.report-pdf', [
            'items' => $items,
            'summary' => ['total' => $stats['total'], ...$stats['types']],
            'setting' => $setting,
            'periodLabel' => $this->periodLabel($request),
            'printedAt' => now('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB',
            'logoDataUri' => $this->logoDataUri($setting),
        ])->setPaper('a4', 'landscape')->download('laporan-buku-tamu-'.now('Asia/Jakarta')->format('Ymd-His').'.pdf');
    }

    public function regenerateQr()
    {
        BukuTamuQrToken::active()->update(['revoked_at' => now('Asia/Jakarta')]);
        BukuTamuQrToken::create(['token' => Str::random(48), 'created_by' => auth()->id()]);

        return back()->with('success', 'QR Code baru berhasil dibuat. Gunakan QR Code terbaru untuk cetakan berikutnya.');
    }

    public function destroy(BukuTamu $bukuTamu)
    {
        $bukuTamu->delete();

        return back()->with('success', 'Data kunjungan berhasil diarsipkan.');
    }

    private function filteredQuery(Request $request): Builder
    {
        return BukuTamu::query()
            ->when($request->filled('tanggal_mulai'), fn (Builder $query) => $query->whereDate('tanggal_kunjungan', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn (Builder $query) => $query->whereDate('tanggal_kunjungan', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('q'), function (Builder $query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(function (Builder $builder) use ($term) {
                    $builder->where('nama', 'like', "%{$term}%")
                        ->orWhere('alamat_instansi', 'like', "%{$term}%")
                        ->orWhere('alamat', 'like', "%{$term}%")
                        ->orWhere('keperluan', 'like', "%{$term}%");
                });
            })
            ->latest('tanggal_kunjungan')->latest('created_at');
    }

    private function statistics(Request $request): array
    {
        $base = $this->filteredQuery($request);
        $types = ['instansi' => 0, 'lembaga' => 0, 'individu' => 0];
        $grouped = (clone $base)->reorder()->select('jenis_tamu', DB::raw('COUNT(*) as total'))->groupBy('jenis_tamu')->pluck('total', 'jenis_tamu');
        foreach ($types as $type => $value) $types[$type] = (int) ($grouped[$type] ?? 0);

        return ['total' => array_sum($types), 'types' => $types];
    }

    private function periodLabel(Request $request): string
    {
        if ($request->filled('tanggal_mulai') || $request->filled('tanggal_selesai')) {
            return ($request->input('tanggal_mulai') ?: 'awal') . ' s.d. ' . ($request->input('tanggal_selesai') ?: 'akhir');
        }

        return 'Semua data';
    }

    private function logoDataUri(AppSetting $setting): string
    {
        $paths = array_filter([
            $setting->logo_sekolah_path ? storage_path('app/public/'.$setting->logo_sekolah_path) : null,
            public_path('vendor/adminlte/dist/img/logo-sekolah.png'),
        ]);
        foreach ($paths as $path) {
            if (is_file($path)) return 'data:'.(mime_content_type($path) ?: 'image/png').';base64,'.base64_encode(file_get_contents($path));
        }

        return '';
    }
}
