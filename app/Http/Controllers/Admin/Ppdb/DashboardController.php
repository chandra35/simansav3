<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use App\Models\Berita;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pendaftar' => CalonSiswa::count(),
            'pending' => CalonSiswa::where('status_verifikasi', 'pending')->count(),
            'verified' => CalonSiswa::where('status_verifikasi', 'verified')->count(),
            'rejected' => CalonSiswa::where('status_verifikasi', 'rejected')->count(),
            'total_berita' => Berita::count(),
            'total_slider' => Slider::count(),
            'dokumen_pending' => CalonDokumen::where('status', 'pending')->count(),
        ];

        // Recent pendaftar
        $recentPendaftar = CalonSiswa::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Chart data - pendaftar per bulan
        $pendaftarPerBulan = CalonSiswa::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('COUNT(*) as jumlah')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('admin.ppdb.dashboard', compact('stats', 'recentPendaftar', 'pendaftarPerBulan'));
    }
}
