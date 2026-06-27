<?php

namespace App\Http\Controllers\Matrikulasi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $peserta = $user->matrikulasiPeserta()
            ->with(['periode.tahunPelajaran', 'kelompok', 'dokumens'])
            ->first();

        if (!$peserta) {
            abort(403, 'Akun ini belum terhubung dengan peserta matrikulasi.');
        }

        return view('matrikulasi.dashboard', compact('user', 'peserta'));
    }
}
