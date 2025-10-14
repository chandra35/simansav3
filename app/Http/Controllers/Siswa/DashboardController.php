<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            // If siswa record doesn't exist, create one
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nisn' => $user->username,
                'nama_lengkap' => $user->name,
                'jenis_kelamin' => 'L', // Default, will be updated in profile
            ]);
        }

        // Check if user needs to complete profile
        if ($user->is_first_login) {
            return redirect()->route('siswa.profile.password')->with('info', 'Silakan ganti password Anda terlebih dahulu.');
        }

        if (!$siswa->data_ortu_completed) {
            return redirect()->route('siswa.profile.ortu')->with('info', 'Silakan lengkapi data orangtua terlebih dahulu.');
        }

        if (!$siswa->data_diri_completed) {
            return redirect()->route('siswa.profile.diri')->with('info', 'Silakan lengkapi data diri Anda.');
        }

        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();

        return view('siswa.dashboard', compact('siswa', 'tahunPelajaranAktif'));
    }
}
