<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BukuTamu;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BukuTamuController extends Controller
{
    public function index(Request $request)
    {
        $query = BukuTamu::query()->latest('tanggal_kunjungan')->latest('created_at');

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_kunjungan', '>=', $request->date('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_kunjungan', '<=', $request->date('tanggal_selesai'));
        }
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('nama', 'like', "%{$term}%")
                    ->orWhere('alamat_instansi', 'like', "%{$term}%")
                    ->orWhere('keperluan', 'like', "%{$term}%");
            });
        }

        $today = BukuTamu::whereDate('tanggal_kunjungan', today())->count();
        $month = BukuTamu::whereBetween('tanggal_kunjungan', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $publicUrl = route('public.buku-tamu.index');
        $qrSvg = QrCode::format('svg')->size(220)->margin(1)->errorCorrection('M')->generate($publicUrl);

        return view('admin.buku-tamu.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'todayCount' => $today,
            'monthCount' => $month,
            'publicUrl' => $publicUrl,
            'qrSvg' => $qrSvg,
            'setting' => AppSetting::getInstance(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = BukuTamu::query()->latest('tanggal_kunjungan')->latest('created_at');
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_kunjungan', '>=', $request->date('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_kunjungan', '<=', $request->date('tanggal_selesai'));
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Nama', 'Alamat Instansi/Lembaga/Individu', 'Nomor HP', 'Keperluan']);
            $query->chunkById(500, function ($items) use ($handle) {
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->tanggal_kunjungan?->format('d-m-Y'),
                        $item->nama,
                        $item->alamat_instansi,
                        $item->nomor_hp,
                        $item->keperluan,
                    ]);
                }
            });
            fclose($handle);
        }, 'buku-tamu-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(BukuTamu $bukuTamu)
    {
        $bukuTamu->delete();

        return back()->with('success', 'Data kunjungan berhasil diarsipkan.');
    }
}
