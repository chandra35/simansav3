<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\ActivityLog;
use App\Services\KemendikbudApiService;
use App\Services\ActivityLogService;
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

    /**
     * Upload foto profile only (AJAX)
     */
    public function uploadFoto(Request $request)
    {
        // Enhanced validation with strict rules
        $request->validate([
            'foto_profile' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            ]
        ], [
            'foto_profile.required' => 'Foto profil wajib dipilih',
            'foto_profile.image' => 'File harus berupa gambar',
            'foto_profile.mimes' => 'Format gambar hanya: JPG, JPEG, atau PNG',
            'foto_profile.max' => 'Ukuran file maksimal 2MB',
            'foto_profile.dimensions' => 'Dimensi gambar minimal 100x100 pixel',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        try {
            // Additional security check: Verify it's actually an image
            $file = $request->file('foto_profile');
            $imageInfo = @getimagesize($file->getRealPath());
            
            if ($imageInfo === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'File bukan gambar yang valid!'
                ], 400);
            }

            // Check MIME type from actual file content (not just extension)
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($imageInfo['mime'], $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format gambar tidak didukung. Hanya JPG, JPEG, dan PNG.'
                ], 400);
            }

            // Upload and process image
            $path = $this->handleFotoUpload($file, $siswa);
            
            // Get old path before update
            $oldFoto = $siswa->foto_profile;
            
            // Update siswa record
            $siswa->update(['foto_profile' => $path]);

            // Enhanced activity log
            ActivityLogService::log([
                'activity_type' => 'upload_foto',
                'model_type' => Siswa::class,
                'model_id' => $siswa->id,
                'description' => 'Mengupload foto profil',
                'old_values' => ['foto_profile' => $oldFoto],
                'new_values' => ['foto_profile' => $path],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload!',
                'foto_url' => $siswa->foto_profile_url,
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading foto profile', [
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDiri(Request $request)
    {
        // Foto profile is handled separately via uploadFoto() method
        $request->validate([
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
            'jenis_tempat_tinggal' => 'required_if:alamat_sama_ortu,0|in:Asrama,Kost/Kontrakan,Saudara',
            'alamat_siswa' => 'nullable|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'nullable|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'nullable|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'nullable|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'nullable|exists:indonesia_villages,code',
            'kodepos_siswa' => 'nullable|string|max:10',
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
            'jenis_tempat_tinggal.required_if' => 'Jenis tempat tinggal wajib dipilih untuk alamat berbeda',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        // Custom validation for Kost/Saudara - must have complete address
        if (!$request->alamat_sama_ortu && in_array($request->jenis_tempat_tinggal, ['Kost/Kontrakan', 'Saudara'])) {
            $request->validate([
                'alamat_siswa' => 'required|string',
                'rt_siswa' => 'required|string|max:5',
                'rw_siswa' => 'required|string|max:5',
                'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
                'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
                'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
                'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
                'kodepos_siswa' => 'required|string|max:10',
            ], [
                'alamat_siswa.required' => 'Alamat lengkap wajib diisi untuk ' . $request->jenis_tempat_tinggal,
                'rt_siswa.required' => 'RT wajib diisi',
                'rw_siswa.required' => 'RW wajib diisi',
                'provinsi_id_siswa.required' => 'Provinsi wajib dipilih',
                'kabupaten_id_siswa.required' => 'Kabupaten/Kota wajib dipilih',
                'kecamatan_id_siswa.required' => 'Kecamatan wajib dipilih',
                'kelurahan_id_siswa.required' => 'Kelurahan/Desa wajib dipilih',
                'kodepos_siswa.required' => 'Kode pos wajib diisi',
            ]);
        }

        // Foto profile is handled separately, exclude from this update
        $validated = $request->except(['foto_profile', '_token', '_method']);

        // Convert tanggal_lahir to proper format if needed
        if (!empty($validated['tanggal_lahir'])) {
            try {
                $validated['tanggal_lahir'] = \Carbon\Carbon::parse($validated['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to parse tanggal_lahir', ['value' => $validated['tanggal_lahir']]);
            }
        }

        // Foto profile is now handled separately via AJAX upload
        // No need to process it here

        // If alamat sama dengan ortu, clear siswa address fields and set jenis_tempat_tinggal
        if ($request->alamat_sama_ortu) {
            $validated['jenis_tempat_tinggal'] = 'Bersama Orang Tua';
            $validated['alamat_siswa'] = null;
            $validated['rt_siswa'] = null;
            $validated['rw_siswa'] = null;
            $validated['provinsi_id_siswa'] = null;
            $validated['kabupaten_id_siswa'] = null;
            $validated['kecamatan_id_siswa'] = null;
            $validated['kelurahan_id_siswa'] = null;
            $validated['kodepos_siswa'] = null;
        } 
        // If Asrama selected, use school address
        elseif ($request->jenis_tempat_tinggal === 'Asrama') {
            $sekolah = $siswa->sekolahAsal;
            
            if ($sekolah) {
                $validated['alamat_siswa'] = 'Asrama ' . $sekolah->nama . ', ' . $sekolah->alamat_jalan;
                $validated['rt_siswa'] = null;
                $validated['rw_siswa'] = null;
                $validated['kodepos_siswa'] = null;
                // You can add province/city mapping if available in sekolah table
                $validated['provinsi_id_siswa'] = null;
                $validated['kabupaten_id_siswa'] = null;
                $validated['kecamatan_id_siswa'] = null;
                $validated['kelurahan_id_siswa'] = null;
            } else {
                $validated['alamat_siswa'] = 'Asrama Sekolah';
            }
        }

        try {
            // Get old data before update
            $oldData = $siswa->toArray();
            
            // Update siswa data
            $validated['data_diri_completed'] = true;
            $siswa->update($validated);

            // Update user email if provided
            if ($request->filled('email')) {
                $user->email = $request->email;
                $user->save();
            }

            // Enhanced activity log with change tracking
            ActivityLogService::logChanges(
                'update_data_diri',
                $siswa,
                $oldData,
                $validated,
                'Memperbarui data diri siswa'
            );

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

            // Create square canvas 400x400 with white background
            $canvas = \imagecreatetruecolor(400, 400);
            $white = \imagecolorallocate($canvas, 255, 255, 255);
            \imagefill($canvas, 0, 0, $white);

            // Calculate resize dimensions to fit in 400x400 without cropping
            $ratio = min(400 / $width, 400 / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Calculate position to center image
            $x = (int)((400 - $newWidth) / 2);
            $y = (int)((400 - $newHeight) / 2);

            // Resize and place image in center
            \imagecopyresampled(
                $canvas, $source,
                $x, $y, 0, 0,
                $newWidth, $newHeight, $width, $height
            );

            // Save as JPEG with 90% quality
            \imagejpeg($canvas, $fullPath, 90);

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
