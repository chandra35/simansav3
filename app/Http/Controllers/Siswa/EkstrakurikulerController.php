<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\EkstrakurikulerAnggota;
use Illuminate\Support\Facades\Auth;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;

        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $anggota = EkstrakurikulerAnggota::with(['ekstrakurikuler.pembina', 'tahunPelajaran'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'aktif')
            ->latest('tanggal_bergabung')
            ->get();

        return view('siswa.ekstrakurikuler.index', compact('siswa', 'anggota'));
    }
}
