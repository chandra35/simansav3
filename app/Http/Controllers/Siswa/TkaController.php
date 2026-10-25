<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\TkaPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TkaController extends Controller
{
    /**
     * Tampilkan kartu akun TKA siswa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        // Cari data kartu TKA berdasarkan siswa_id atau NISN
        $tka = TkaPeserta::where(function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id)
              ->orWhere('nisn', $siswa->nisn);
        })
        ->where('is_active', true)
        ->orderBy('tahun', 'desc')
        ->first();

        // Validasi apakah siswa kelas 12
        $kelas = $siswa->kelasSaatIni;
        $tingkat = $kelas->tingkat ?? null;
        $namaKelas = (string) ($kelas->nama_kelas ?? '');
        $isKelas12 = ($tingkat == 12) || str_contains($namaKelas, '12') || str_starts_with(strtoupper($namaKelas), 'XII');

        if (!$tka && !$isKelas12) {
            return redirect()->route('siswa.dashboard')->with('error', 'Menu Kartu Akun TKA hanya diperuntukkan bagi siswa Kelas 12.');
        }

        return view('siswa.tka.index', compact('siswa', 'tka', 'kelas', 'isKelas12'));
    }

    /**
     * Cetak kartu TKA format print
     */
    public function cetak(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $tka = TkaPeserta::where(function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id)
              ->orWhere('nisn', $siswa->nisn);
        })
        ->where('is_active', true)
        ->orderBy('tahun', 'desc')
        ->first();

        if (!$tka) {
            return redirect()->route('siswa.tka.index')->with('error', 'Kartu TKA Anda belum tersedia.');
        }

        $kelas = $siswa->kelasSaatIni;

        return view('siswa.tka.cetak', compact('siswa', 'tka', 'kelas'));
    }
}
