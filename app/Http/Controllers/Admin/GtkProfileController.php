<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GtkProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $this->authorize('edit-gtk-profile');

        $user = Auth::user();
        $gtk = $user->gtk;

        if (!$gtk) {
            return redirect()->route('admin.gtk.dashboard')
                ->with('error', 'Data GTK tidak ditemukan.');
        }

        $provinsiList = Province::orderBy('name')->get();
        $statusKepegOptions = ['PNS', 'PPPK', 'GTT', 'GTY'];
        $jabatanOptions = ['Guru Mapel', 'Guru BK', 'Kepala Sekolah', 'Wakil Kepala Sekolah', 'Staff TU', 'Tenaga Kependidikan'];

        return view('admin.gtk.profile.index', compact('gtk', 'provinsiList', 'statusKepegOptions', 'jabatanOptions'));
    }

    /**
     * Show change password page
     */
    public function password()
    {
        $this->authorize('change-password-gtk');

        $user = Auth::user();
        $gtk = $user->gtk;

        return view('admin.gtk.profile.password', compact('gtk'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $this->authorize('change-password-gtk');

        $user = Auth::user();

        // Validate
        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password lama tidak sesuai.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        return redirect()->route('admin.gtk.dashboard')
            ->with('success', 'Password berhasil diubah. Silakan gunakan password baru untuk login berikutnya.');
    }

    /**
     * Update profile data diri
     */
    public function updateDiri(Request $request)
    {
        $this->authorize('edit-gtk-profile');

        $user = Auth::user();
        $gtk = $user->gtk;

        if (!$gtk) {
            return back()->with('error', 'Data GTK tidak ditemukan.');
        }

        // Validate
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:gtks,nik,' . $gtk->id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'provinsi_id' => 'nullable|exists:indonesia_provinces,code',
            'kabupaten_id' => 'nullable|exists:indonesia_cities,code',
            'kecamatan_id' => 'nullable|exists:indonesia_districts,code',
            'kelurahan_id' => 'nullable|exists:indonesia_villages,code',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:3',
            'rw' => 'nullable|string|max:3',
            'kodepos' => 'nullable|string|max:5',
        ]);

        // Update GTK data
        $gtk->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'provinsi_id' => $request->provinsi_id,
            'kabupaten_id' => $request->kabupaten_id,
            'kecamatan_id' => $request->kecamatan_id,
            'kelurahan_id' => $request->kelurahan_id,
            'alamat' => $request->alamat,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'kodepos' => $request->kodepos,
            'data_diri_completed' => !empty($request->nama_lengkap) && 
                                     !empty($request->nik) && 
                                     !empty($request->jenis_kelamin) && 
                                     !empty($request->tempat_lahir) && 
                                     !empty($request->tanggal_lahir) && 
                                     !empty($request->provinsi_id) && 
                                     !empty($request->kabupaten_id) && 
                                     !empty($request->kecamatan_id) && 
                                     !empty($request->kelurahan_id) && 
                                     !empty($request->alamat),
            'updated_by' => $user->id,
        ]);

        // Update user name and username
        $user->update([
            'name' => $request->nama_lengkap,
            'username' => $request->nik,
        ]);

        // Return JSON response for AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data diri berhasil disimpan!'
            ]);
        }

        return back()->with('success', 'Data diri berhasil diperbarui.');
    }

    /**
     * Update profile data kepegawaian
     */
    public function updateKepeg(Request $request)
    {
        $this->authorize('edit-gtk-profile');

        $user = Auth::user();
        $gtk = $user->gtk;

        if (!$gtk) {
            return back()->with('error', 'Data GTK tidak ditemukan.');
        }

        // Validate
        $request->validate([
            'nuptk' => 'nullable|string|size:16',
            'nip' => 'nullable|string|max:18',
            'status_kepegawaian' => 'nullable|in:PNS,PPPK,GTT,GTY',
            'jabatan' => 'nullable|string|max:100',
            'tmt_kerja' => 'nullable|date',
        ]);

        // Update GTK data
        $gtk->update([
            'nuptk' => $request->nuptk,
            'nip' => $request->nip,
            'status_kepegawaian' => $request->status_kepegawaian,
            'jabatan' => $request->jabatan,
            'tmt_kerja' => $request->tmt_kerja,
            'data_kepegawaian_completed' => !empty($request->status_kepegawaian) && !empty($request->jabatan),
            'updated_by' => $user->id,
        ]);

        // Return JSON response for AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data kepegawaian berhasil disimpan!'
            ]);
        }

        return back()->with('success', 'Data kepegawaian berhasil diperbarui.');
    }

    /**
     * Get cities by province (AJAX)
     */
    public function getCities($provinsiId)
    {
        $cities = City::where('province_code', $provinsiId)
            ->orderBy('name')
            ->get(['code', 'name']);

        return response()->json($cities);
    }

    /**
     * Get districts by city (AJAX)
     */
    public function getDistricts($kabupatenId)
    {
        $districts = District::where('city_code', $kabupatenId)
            ->orderBy('name')
            ->get(['code', 'name']);

        return response()->json($districts);
    }

    /**
     * Get villages by district (AJAX)
     */
    public function getVillages($kecamatanId)
    {
        $villages = Village::where('district_code', $kecamatanId)
            ->orderBy('name')
            ->get(['code', 'name']);

        return response()->json($villages);
    }
}
