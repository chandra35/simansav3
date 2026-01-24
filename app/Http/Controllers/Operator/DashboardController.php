<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\JurusanPpdb;
use App\Models\PendaftaranPpdb;
use App\Models\PengaturanPpdb;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display operator dashboard.
     */
    public function index()
    {
        // Statistik Pendaftaran
        $stats = [
            'total' => PendaftaranPpdb::count(),
            'draft' => PendaftaranPpdb::draft()->count(),
            'submitted' => PendaftaranPpdb::submitted()->count(),
            'verified' => PendaftaranPpdb::verified()->count(),
            'accepted' => PendaftaranPpdb::accepted()->count(),
            'rejected' => PendaftaranPpdb::where('status', 'rejected')->count(),
        ];

        // Pendaftaran terbaru (menunggu verifikasi)
        $pendingVerifikasi = PendaftaranPpdb::submitted()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Jurusan dengan kuota
        $jurusan = JurusanPpdb::active()->ordered()->get()->map(function ($j) {
            $j->terisi = PendaftaranPpdb::where('diterima_di_jurusan', $j->id)
                ->where('status', 'accepted')
                ->count();
            $j->sisa = max(0, $j->kuota - $j->terisi);
            return $j;
        });

        // Pengaturan
        $pengaturan = PengaturanPpdb::getActive();

        // Chart data - pendaftaran per hari (7 hari terakhir)
        $chartData = PendaftaranPpdb::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return view('operator.dashboard', compact(
            'stats', 
            'pendingVerifikasi', 
            'jurusan', 
            'pengaturan',
            'chartData'
        ));
    }
}
