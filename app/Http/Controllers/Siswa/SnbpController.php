<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SnbpMenu;
use Illuminate\Support\Facades\Auth;

class SnbpController extends Controller
{
    /**
     * Show SNBP eligibility status for the logged in student
     */
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        // Check if user has siswa profile
        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        // Load kelasSaatIni with jurusan
        $siswa->load(['kelasSaatIni.jurusan']);

        // Check if siswa is in kelas 12
        $kelasSaatIni = $siswa->kelasSaatIni;
        $isKelas12 = false;
        
        if ($kelasSaatIni) {
            // Check if kelas name contains "12" or tingkat is 12
            $isKelas12 = str_contains($kelasSaatIni->nama_kelas, '12') || 
                         (isset($kelasSaatIni->tingkat) && $kelasSaatIni->tingkat == 12);
        }

        // Get active SNBP menu
        $snbpMenu = SnbpMenu::getActiveMenu();

        // If not kelas 12 or no active menu, show not applicable message
        if (!$isKelas12) {
            return view('siswa.snbp.not-applicable', [
                'reason' => 'not_kelas_12',
                'siswa' => $siswa,
                'kelasSaatIni' => $kelasSaatIni
            ]);
        }

        if (!$snbpMenu) {
            return view('siswa.snbp.not-applicable', [
                'reason' => 'no_menu',
                'siswa' => $siswa,
                'kelasSaatIni' => $kelasSaatIni
            ]);
        }

        // Load tahun pelajaran for snbp menu
        $snbpMenu->load('tahunPelajaran');

        // Check siswa's eligibility status
        $status = $snbpMenu->getSiswaStatus($siswa->id);

        // Get the appropriate content based on status
        $content = null;
        if ($status === true) {
            $content = $snbpMenu->konten_eligible;
        } elseif ($status === false) {
            $content = $snbpMenu->konten_not_eligible;
        }

        return view('siswa.snbp.index', [
            'snbpMenu' => $snbpMenu,
            'siswa' => $siswa,
            'status' => $status,
            'content' => $content
        ]);
    }
}
