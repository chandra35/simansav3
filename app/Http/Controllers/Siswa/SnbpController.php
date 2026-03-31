<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SnbpMenu;
use App\Models\SnbpRegistration;
use Illuminate\Http\Request;
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

        $registration = null;
        $linkedLulusan = null;

        if ($status === true) {
            $registration = SnbpRegistration::with('lulusan')
                ->firstOrNew([
                    'snbp_menu_id' => $snbpMenu->id,
                    'siswa_id' => $siswa->id,
                    'tahun_pelajaran_id' => $snbpMenu->tahun_pelajaran_id,
                ]);

            $linkedLulusan = $registration->lulusan;
        }

        return view('siswa.snbp.index', [
            'snbpMenu' => $snbpMenu,
            'siswa' => $siswa,
            'status' => $status,
            'content' => $content,
            'registration' => $registration,
            'linkedLulusan' => $linkedLulusan,
        ]);
    }

    public function storeRegistration(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $snbpMenu = SnbpMenu::getActiveMenu();
        if (!$snbpMenu) {
            return redirect()->route('siswa.snbp.index')
                ->with('error', 'Menu SNBP belum tersedia untuk tahun pelajaran aktif.');
        }

        $status = $snbpMenu->getSiswaStatus($siswa->id);
        if ($status !== true) {
            return redirect()->route('siswa.snbp.index')
                ->with('error', 'Nomor pendaftaran SNBP hanya dapat diisi oleh siswa yang berstatus eligible.');
        }

        $validated = $request->validate([
            'nomor_pendaftaran' => ['required', 'string', 'min:8', 'max:50', 'regex:/^[0-9A-Za-z\\-\\/]+$/'],
        ], [
            'nomor_pendaftaran.required' => 'Nomor pendaftaran SNBP wajib diisi.',
            'nomor_pendaftaran.regex' => 'Nomor pendaftaran hanya boleh berisi huruf, angka, garis miring, atau tanda hubung.',
        ]);

        SnbpRegistration::updateOrCreate(
            [
                'snbp_menu_id' => $snbpMenu->id,
                'siswa_id' => $siswa->id,
                'tahun_pelajaran_id' => $snbpMenu->tahun_pelajaran_id,
            ],
            [
                'nomor_pendaftaran' => trim($validated['nomor_pendaftaran']),
            ]
        );

        return redirect()->route('siswa.snbp.index')
            ->with('success', 'Nomor pendaftaran SNBP berhasil disimpan.');
    }
}
