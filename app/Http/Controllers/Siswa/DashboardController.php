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
            // SECURITY: Don't auto-create siswa if user is not actually a siswa
            // This prevents GTK or other users from accidentally creating duplicate siswa records
            
            // Check if user is actually marked as siswa (not GTK, admin, etc.)
            if (!$user->isSiswa()) {
                abort(403, 'Unauthorized: You are not a siswa. Please contact administrator.');
            }
            
            // Additional validation: Check if username/NISN already exists in GTK
            $existingGtk = \App\Models\Gtk::where('nik', $user->username)->first();
            if ($existingGtk) {
                abort(403, 'Unauthorized: This NIK is registered as GTK. Please contact administrator.');
            }
            
            // Safe to create siswa record
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
