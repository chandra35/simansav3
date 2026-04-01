<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SpanPtkinMenu;
use App\Models\SpanPtkinRegistration;
use Illuminate\Support\Facades\Auth;

class SpanPtkinController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $siswa->load(['kelasSaatIni.jurusan']);
        $kelasSaatIni = $siswa->kelasSaatIni;
        $isKelas12 = $kelasSaatIni && ((int) ($kelasSaatIni->tingkat ?? 0) === 12 || str_contains((string) $kelasSaatIni->nama_kelas, '12'));

        if (!$isKelas12) {
            return view('siswa.span-ptkin.not-applicable', [
                'reason' => 'not_kelas_12',
                'siswa' => $siswa,
                'kelasSaatIni' => $kelasSaatIni,
            ]);
        }

        $spanPtkinMenu = SpanPtkinMenu::getActiveMenu();
        if (!$spanPtkinMenu) {
            return view('siswa.span-ptkin.not-applicable', [
                'reason' => 'no_menu',
                'siswa' => $siswa,
                'kelasSaatIni' => $kelasSaatIni,
            ]);
        }

        $spanPtkinMenu->load('tahunPelajaran');

        $registration = SpanPtkinRegistration::with('lulusan')
            ->firstOrNew([
                'span_ptkin_menu_id' => $spanPtkinMenu->id,
                'siswa_id' => $siswa->id,
                'tahun_pelajaran_id' => $spanPtkinMenu->tahun_pelajaran_id,
            ]);

        return view('siswa.span-ptkin.index', [
            'spanPtkinMenu' => $spanPtkinMenu,
            'siswa' => $siswa,
            'kelasSaatIni' => $kelasSaatIni,
            'registration' => $registration,
            'linkedLulusan' => $registration->lulusan,
        ]);
    }
}
