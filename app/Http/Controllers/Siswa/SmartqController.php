<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SmartqPeserta;
use Illuminate\Http\Request;

class SmartqController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard');
        }

        $peserta = SmartqPeserta::with(['periode', 'bidangMapel'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['lulus', 'cadangan'])
            ->latest()
            ->first();

        if (!$peserta) {
            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Pengumuman SMART-Q belum tersedia untuk akun Anda.');
        }

        // Track first time the student opens the SMART-Q announcement.
        if (!$peserta->pengumuman_dibuka_at) {
            $peserta->update([
                'pengumuman_dibuka_at' => now(),
                'pengumuman_dibuka_ip' => $request->ip(),
                'pengumuman_dibuka_user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
            $peserta->refresh();
        }

        return view('siswa.smartq.index', compact('user', 'siswa', 'peserta'));
    }
}
