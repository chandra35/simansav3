<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\ActivityLog;
use App\Services\KemendikbudApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class ProfileController extends Controller
{
    public function password()
    {
        $user = Auth::user();
        
        if (!$user->is_first_login) {
            return redirect()->route('siswa.dashboard');
        }

        return view('siswa.profile.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        User::logCustomActivity('first_login_password_change', 'Password pertama kali berhasil diubah');

        return redirect()->route('siswa.profile.ortu')->with('success', 'Password berhasil diubah. Silakan lengkapi data orangtua.');
    }

    public function ortu()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $ortu = $siswa->ortu;

        $provinces = Province::all();
        
        return view('siswa.profile.ortu', compact('siswa', 'ortu', 'provinces'));
    }

    public function updateOrtu(Request $request)
    {
        $request->validate([
            'no_kk' => 'nullable|string|max:20',
            'status_ayah' => 'required|in:masih_hidup,meninggal',
            'nama_ayah' => 'required|string|max:255',
            'nik_ayah' => 'nullable|string|max:20',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'penghasilan_ayah' => 'nullable|string|max:255',
            'hp_ayah' => 'nullable|string|max:20',
            'status_ibu' => 'required|in:masih_hidup,meninggal',
            'nama_ibu' => 'required|string|max:255',
            'nik_ibu' => 'nullable|string|max:20',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'penghasilan_ibu' => 'nullable|string|max:255',
            'hp_ibu' => 'nullable|string|max:20',
            'alamat_ortu' => 'required|string',
            'rt_ortu' => 'required|string|max:5',
            'rw_ortu' => 'required|string|max:5',
            'provinsi_id' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id' => 'required|exists:indonesia_cities,code',
            'kecamatan_id' => 'required|exists:indonesia_districts,code',
            'kelurahan_id' => 'required|exists:indonesia_villages,code',
            'kodepos' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        $ortu = Ortu::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $request->all()
        );

        $siswa->update(['data_ortu_completed' => true]);

        User::logCustomActivity('ortu_data_update', 'Data orangtua berhasil diperbarui');

        return redirect()->route('siswa.profile.diri')->with('success', 'Data orangtua berhasil disimpan. Silakan lengkapi data diri.');
    }

    public function diri()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa->data_ortu_completed) {
            return redirect()->route('siswa.profile.ortu')->with('error', 'Silakan lengkapi data orangtua terlebih dahulu.');
        }

        // Load sekolah relation if exists
        $siswa->load('sekolahAsal');

        $provinces = Province::all();
        
        return view('siswa.profile.diri', compact('siswa', 'provinces'));
    }

    public function updateDiri(Request $request)
    {
        $request->validate([
            'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'npsn_asal_sekolah' => 'required|digits:8|exists:sekolah,npsn',
            'nik' => 'required|string|max:20',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'jumlah_saudara' => 'required|integer|min:0',
            'anak_ke' => 'required|integer|min:1',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            'nomor_hp' => 'nullable|string|max:20',
            'alamat_sama_ortu' => 'required|boolean',
            'alamat_siswa' => 'required_if:alamat_sama_ortu,0|string',
            'rt_siswa' => 'required_if:alamat_sama_ortu,0|string|max:5',
            'rw_siswa' => 'required_if:alamat_sama_ortu,0|string|max:5',
            'provinsi_id_siswa' => 'required_if:alamat_sama_ortu,0|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required_if:alamat_sama_ortu,0|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required_if:alamat_sama_ortu,0|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required_if:alamat_sama_ortu,0|exists:indonesia_villages,code',
            'kodepos_siswa' => 'required_if:alamat_sama_ortu,0|string|max:10',
        ], [
            'npsn_asal_sekolah.required' => 'NPSN Asal Sekolah wajib diisi',
            'npsn_asal_sekolah.digits' => 'NPSN harus 8 digit angka',
            'npsn_asal_sekolah.exists' => 'NPSN tidak ditemukan. Silakan klik tombol "Cari" terlebih dahulu untuk memvalidasi NPSN.',
            'nik.required' => 'NIK wajib diisi',
            'nik.max' => 'NIK maksimal 20 karakter',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'agama.required' => 'Agama wajib dipilih',
            'jumlah_saudara.required' => 'Jumlah saudara wajib diisi',
            'anak_ke.required' => 'Anak ke berapa wajib diisi',
            'alamat_sama_ortu.required' => 'Pilihan alamat wajib dipilih',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        $validated = $request->except(['foto_profile']);

        // Convert tanggal_lahir to proper format if needed
        if (!empty($validated['tanggal_lahir'])) {
            try {
                $validated['tanggal_lahir'] = \Carbon\Carbon::parse($validated['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to parse tanggal_lahir', ['value' => $validated['tanggal_lahir']]);
            }
        }

        // Handle foto upload
        if ($request->hasFile('foto_profile')) {
            $validated['foto_profile'] = $this->handleFotoUpload($request->file('foto_profile'), $siswa);
        }

        // If alamat sama dengan ortu, clear siswa address fields
        if ($request->alamat_sama_ortu) {
            $validated['alamat_siswa'] = null;
            $validated['rt_siswa'] = null;
            $validated['rw_siswa'] = null;
            $validated['provinsi_id_siswa'] = null;
            $validated['kabupaten_id_siswa'] = null;
            $validated['kecamatan_id_siswa'] = null;
            $validated['kelurahan_id_siswa'] = null;
            $validated['kodepos_siswa'] = null;
        }

        try {
            // Update siswa data
            $validated['data_diri_completed'] = true;
            $siswa->update($validated);

            // Update user email if provided
            if ($request->filled('email')) {
                $user->email = $request->email;
                $user->save();
            }

            // Activity log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'update',
                'model_type' => Siswa::class,
                'model_id' => $siswa->id,
                'description' => 'Memperbarui data diri siswa',
            ]);

            return redirect()->route('siswa.dashboard')->with('success', '✅ Data diri berhasil disimpan! Profil Anda sudah lengkap.');
        } catch (\Exception $e) {
            Log::error('Error updating siswa data diri: ' . $e->getMessage(), [
                'siswa_id' => $siswa->id,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Handle foto profile upload with square resize using native GD
     */
    protected function handleFotoUpload($file, $siswa)
    {
        // Delete old foto if exists
        if ($siswa->foto_profile) {
            Storage::disk('public')->delete($siswa->foto_profile);
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = $siswa->id . '_' . time() . '.' . $extension;
        $path = 'foto-profile/' . $filename;

        // Check if GD is available
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            Log::warning('GD extension not available, saving without resize', [
                'siswa_id' => $siswa->id
            ]);
            // Fallback: Save without resize
            return $file->storeAs('foto-profile', $filename, 'public');
        }

        $fullPath = storage_path('app/public/' . $path);

        // Create directory if not exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            // Get original image info
            $tmpPath = $file->getRealPath();
            list($width, $height, $type) = \getimagesize($tmpPath);

            // Create image resource based on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = \imagecreatefromjpeg($tmpPath);
                    break;
                case IMAGETYPE_PNG:
                    $source = \imagecreatefrompng($tmpPath);
                    break;
                case IMAGETYPE_GIF:
                    $source = \imagecreatefromgif($tmpPath);
                    break;
                default:
                    throw new \Exception('Unsupported image type');
            }

            // Calculate crop dimensions for square (center crop)
            $size = min($width, $height);
            $x = ($width - $size) / 2;
            $y = ($height - $size) / 2;

            // Create square canvas 400x400
            $canvas = \imagecreatetruecolor(400, 400);

            // Crop and resize
            \imagecopyresampled(
                $canvas, $source,
                0, 0, $x, $y,
                400, 400, $size, $size
            );

            // Save as JPEG with 85% quality
            \imagejpeg($canvas, $fullPath, 85);

            // Free memory
            \imagedestroy($source);
            \imagedestroy($canvas);

        } catch (\Exception $e) {
            Log::error('GD resize failed, saving without resize', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage()
            ]);
            // Fallback: Save without resize
            return $file->storeAs('foto-profile', $filename, 'public');
        }

        return $path;
    }

    /**
     * AJAX: Search sekolah by NPSN
     */
    public function searchSekolah(Request $request)
    {
        $request->validate([
            'npsn' => 'required|digits:8'
        ]);

        $apiService = new KemendikbudApiService();
        $result = $apiService->getSekolah($request->npsn);

        if ($result['success']) {
            $sekolah = $result['data'];

            return response()->json([
                'success' => true,
                'source' => $result['source'],
                'data' => [
                    'npsn' => $sekolah->npsn,
                    'nama' => $sekolah->nama,
                    'status' => $sekolah->status,
                    'bentuk_pendidikan' => $sekolah->bentuk_pendidikan,
                    'alamat_jalan' => $sekolah->alamat_jalan,
                    'desa_kelurahan' => $sekolah->desa_kelurahan,
                    'kecamatan' => $sekolah->kecamatan,
                    'kabupaten_kota' => $sekolah->kabupaten_kota,
                    'provinsi' => $sekolah->provinsi,
                    'alamat_lengkap' => $sekolah->alamat_lengkap,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 404);
    }

    // API endpoints for address dropdown
    public function getCities($provinceCode)
    {
        $cities = City::where('province_code', $provinceCode)->get();
        return response()->json($cities);
    }

    public function getDistricts($cityCode)
    {
        $districts = District::where('city_code', $cityCode)->get();
        return response()->json($districts);
    }

    public function getVillages($districtCode)
    {
        $villages = Village::where('district_code', $districtCode)->get();
        return response()->json($villages);
    }

    public function loadAlamatOrtu()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $ortu = $siswa->ortu;

        if (!$ortu) {
            return response()->json(['error' => 'Data orangtua belum tersedia'], 404);
        }

        return response()->json([
            'alamat_ortu' => $ortu->alamat_ortu,
            'rt_ortu' => $ortu->rt_ortu,
            'rw_ortu' => $ortu->rw_ortu,
            'provinsi_id' => $ortu->provinsi_id,
            'kabupaten_id' => $ortu->kabupaten_id,
            'kecamatan_id' => $ortu->kecamatan_id,
            'kelurahan_id' => $ortu->kelurahan_id,
            'kodepos' => $ortu->kodepos,
        ]);
    }
}
