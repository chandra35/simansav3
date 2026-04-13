<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SmartqPeserta;

class SmartqController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $peserta = SmartqPeserta::with(['periode', 'bidangMapel'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['lulus', 'cadangan'])
            ->latest()
            ->first();

        if (!$peserta) {
            abort(404, 'Anda belum terdaftar dalam pengumuman SMART-Q.');
        }

        return view('siswa.smartq.index', compact('user', 'siswa', 'peserta'));
    }
}
