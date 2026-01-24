<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return redirect()->route('ppdb.register.step1')
                ->with('info', 'Silakan lengkapi data pendaftaran Anda.');
        }

        $stats = [
            'status_verifikasi' => $calonSiswa->status_verifikasi,
            'nomor_registrasi' => $calonSiswa->nomor_registrasi,
            'tanggal_daftar' => $calonSiswa->created_at,
            'total_dokumen' => $calonSiswa->dokumen()->count(),
            'dokumen_verified' => $calonSiswa->dokumen()->where('status', 'verified')->count(),
        ];

        return view('ppdb.dashboard', compact('calonSiswa', 'stats'));
    }
}
