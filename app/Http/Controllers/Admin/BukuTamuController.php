<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BukuTamu;
use App\Models\BukuTamuQrToken;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BukuTamuController extends Controller
{
    public function index(Request $request)
    {
        $query = BukuTamu::with(['provinsi', 'kota', 'kecamatan', 'kelurahan'])
            ->latest('tanggal_kunjungan')->latest('created_at');

        if ($request->filled('tanggal_mulai')) $query->whereDate('tanggal_kunjungan', '>=', $request->date('tanggal_mulai'));
        if ($request->filled('tanggal_selesai')) $query->whereDate('tanggal_kunjungan', '<=', $request->date('tanggal_selesai'));
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('nama', 'like', "%{$term}%")
                    ->orWhere('alamat_instansi', 'like', "%{$term}%")
                    ->orWhere('alamat', 'like', "%{$term}%")
                    ->orWhere('keperluan', 'like', "%{$term}%");
            });
        }

        $today = BukuTamu::whereDate('tanggal_kunjungan', now('Asia/Jakarta')->toDateString())->count();
        $month = BukuTamu::whereBetween('tanggal_kunjungan', [now('Asia/Jakarta')->startOfMonth(), now('Asia/Jakarta')->endOfMonth()])->count();
        $qrToken = BukuTamuQrToken::active()->latest('created_at')->first()
            ?: BukuTamuQrToken::create(['token' => Str::random(48), 'created_by' => auth()->id()]);
        $publicUrl = route('public.buku-tamu.token', $qrToken);
        $qrSvg = QrCode::format('svg')->size(220)->margin(1)->errorCorrection('M')->generate($publicUrl);

        return view('admin.buku-tamu.index', [
            'items' => $query->paginate(20)->withQueryString(), 'todayCount' => $today, 'monthCount' => $month,
            'publicUrl' => $publicUrl, 'qrSvg' => $qrSvg, 'qrToken' => $qrToken, 'setting' => AppSetting::getInstance(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = BukuTamu::with(['provinsi', 'kota', 'kecamatan', 'kelurahan'])->latest('tanggal_kunjungan')->latest('created_at');
        if ($request->filled('tanggal_mulai')) $query->whereDate('tanggal_kunjungan', '>=', $request->date('tanggal_mulai'));
        if ($request->filled('tanggal_selesai')) $query->whereDate('tanggal_kunjungan', '<=', $request->date('tanggal_selesai'));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jenis', 'Nama', 'Instansi/Lembaga/Individu', 'Nomor HP', 'Alamat', 'Provinsi', 'Kabupaten/Kota', 'Kecamatan', 'Kelurahan/Desa', 'Keperluan']);
            $query->chunkById(500, function ($items) use ($handle) {
                foreach ($items as $item) {
                    fputcsv($handle, [$item->tanggal_kunjungan?->format('d-m-Y'), $item->jenis_tamu, $item->nama, $item->alamat_instansi, $item->nomor_hp, $item->alamat, $item->provinsi?->name, $item->kota?->name, $item->kecamatan?->name, $item->kelurahan?->name, $item->keperluan]);
                }
            });
            fclose($handle);
        }, 'buku-tamu-'.now('Asia/Jakarta')->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
}
