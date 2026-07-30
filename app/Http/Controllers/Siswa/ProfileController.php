<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\ActivityLog;
use App\Services\KemendikbudApiService;
use App\Services\ActivityLogService;
use App\Services\EmailService;
use App\Support\UppercaseInputNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function password()
    {
        $user = Auth::user();
        
        if (!$user->is_first_login) {
            // Redirect to change password page instead
            return redirect()->route('siswa.profile.change-password');
        }

        return view('siswa.profile.password');
    }

    /**
     * Show change password form (for non-first login)
     */
    public function changePassword()
    {
        return view('siswa.profile.change-password');
    }

    /**
     * Update password (for non-first login)
     */
    public function updateChangePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai']);
        }

        $user->password = Hash::make($request->password);
        $user->readable_password = $request->password;
        $user->save();

        User::logCustomActivity('password_change', 'Password berhasil diubah');

        // Send email notification if user has email
        if ($user->email) {
            try {
                $emailService = new EmailService();
                if ($emailService->isConfigured()) {
                    $emailService->sendPasswordChanged($user->email, $user->name);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send password changed email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return back()->with('success', 'Password berhasil diubah');
    }

    /**
     * Show force setup form (password + email)
     */
    public function forceSetup(Request $request)
    {
        if ($request->attributes->has('impersonation')) {
            return redirect()->route('siswa.dashboard');
        }

        $user = Auth::user();
        
        if (!$user->is_first_login) {
            return redirect()->route('siswa.dashboard');
        }

        $isAdminReset = !empty($user->password_reset_at);
        $resetBy = $user->password_reset_by;
        $resetAt = $user->password_reset_at;

        // Email harus diganti jika: (a) masih default/kosong, atau (b) bukan admin reset
        $emailMustChange = !$isAdminReset || $this->isDefaultEmail($user->email);

        return view('siswa.profile.force-setup', compact('user', 'isAdminReset', 'resetBy', 'resetAt', 'emailMustChange'));
    }

    /**
     * Cek apakah email masih email default sistem atau kosong.
     */
    private function isDefaultEmail(?string $email): bool
    {
        if (empty($email)) return true;
        return str_ends_with(strtolower($email), '@siswa.simansa.sch.id');
    }

    /**
     * Update force setup (password + email)
     */
    public function updateForceSetup(Request $request)
    {
        $user = Auth::user();

        $isAdminReset = !empty($user->password_reset_at);
        $emailMustChange = !$isAdminReset || $this->isDefaultEmail($user->email);

        // Aturan validasi email bergantung pada mode:
        // - emailMustChange: email wajib diisi dan harus unik (kecuali user sendiri)
        // - tidak emailMustChange: email boleh kosong (dipertahankan) atau diisi baru yang unik
        $emailRule = $emailMustChange
            ? 'required|email|unique:users,email,' . Auth::id()
            : 'nullable|email|unique:users,email,' . Auth::id();

        $request->validate([
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
            'email' => $emailRule,
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        $currentEmail = strtolower(trim((string) $user->email));
        $newEmail = strtolower(trim((string) $request->email));

        // Validasi email harus berbeda dari email lama — HANYA berlaku jika emailMustChange
        if ($emailMustChange && $currentEmail !== '' && $newEmail === $currentEmail) {
            return back()
                ->withErrors(['email' => 'Email wajib diganti. Gunakan email aktif milik Anda yang berbeda dari email lama.'])
                ->withInput();
        }

        // Force password replacement; new password cannot be same as current one.
        if (Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Password baru harus berbeda dari password sebelumnya.'])
                ->withInput();
        }

        // Tentukan email final: pakai input baru jika diisi, atau pertahankan email lama
        $finalEmail = ($newEmail !== '') ? $newEmail : $currentEmail;

        $user->password = Hash::make($request->password);
        $user->email = $finalEmail;
        $user->is_first_login = false;
        $user->password_reset_at = null;
        $user->password_reset_by = null;
        $user->readable_password = $request->password;
        $user->save();

        $activityLabel = $emailMustChange ? 'Setup awal berhasil: password dan email diperbarui' : 'Ganti password pasca-reset admin berhasil';
        User::logCustomActivity('first_login_setup', $activityLabel);

        // Send email notification
        if ($finalEmail) {
            try {
                $emailService = new EmailService();
                if ($emailService->isConfigured()) {
                    $emailService->sendPasswordChanged($finalEmail, $user->name);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send password changed email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $successMsg = $isAdminReset
            ? 'Password berhasil diganti. Akun Anda telah diamankan kembali.'
            : 'Password dan email berhasil disimpan. Selamat datang!';

        return redirect()->route('siswa.dashboard')->with('success', $successMsg);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->is_first_login = false;
        $user->readable_password = $request->password;
        $user->save();

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
        $validated = $request->validate([
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

        $validated = UppercaseInputNormalizer::normalize($validated, [
            'nama_ayah',
            'nama_ibu',
            'alamat_ortu',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        $ortu = Ortu::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $validated
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
     * Upload foto profile only (AJAX) - supports both file and cropped base64
     */
    public function uploadFoto(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        // Check if siswa exists
        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan. Silakan hubungi administrator.'
            ], 404);
        }

        try {
            // Check if it's a cropped image (base64)
            if ($request->has('cropped_image')) {
                $path = $this->handleCroppedImageUpload($request->cropped_image, $siswa);
            } else {
                // Enhanced validation with strict rules for file upload
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
            }
            
            // Get old path before update
            $oldFoto = $siswa->foto_profile;
            
            // Update siswa record and refresh relation-backed accessors
            $siswa->update(['foto_profile' => $path]);
            $siswa->refresh();

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

    /**
     * Handle cropped image upload (base64)
     */
    protected function handleCroppedImageUpload($base64Image, $siswa)
    {
        // Delete old foto if exists
        $oldFotoPath = StorageHelper::normalizePublicPath($siswa->foto_profile);
        if ($oldFotoPath) {
            Storage::disk('public')->delete($oldFotoPath);
        }

        // Decode base64 image
        $imageData = $base64Image;
        if (strpos($base64Image, 'data:image') === 0) {
            list($type, $imageData) = explode(';', $base64Image);
            list(, $imageData) = explode(',', $imageData);
        }
        
        $imageData = base64_decode($imageData);
        
        if ($imageData === false) {
            throw new \Exception('Invalid base64 image data');
        }

        // Generate unique filename
        $filename = $siswa->id . '_' . time() . '.jpg';
        $path = 'foto-profile/' . $filename;
        $fullPath = storage_path('app/public/' . $path);

        // Create directory if not exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create image from string and resize to 400x400
        $source = @imagecreatefromstring($imageData);
        if ($source === false) {
            throw new \Exception('Failed to create image from data');
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // Create 400x400 canvas
        $canvas = imagecreatetruecolor(400, 400);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // Calculate dimensions to fit in 400x400
        $ratio = min(400 / $width, 400 / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        $x = (int)((400 - $newWidth) / 2);
        $y = (int)((400 - $newHeight) / 2);

        // Resize and place
        imagecopyresampled($canvas, $source, $x, $y, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save as JPEG
        imagejpeg($canvas, $fullPath, 90);

        // Free memory
        imagedestroy($source);
        imagedestroy($canvas);

        return $path;
    }

    public function updateDiri(Request $request)
    {
        // Foto profile is handled separately via uploadFoto() method
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'npsn_asal_sekolah' => ['required', 'size:8', 'regex:/^[A-Za-z0-9]+$/', 'exists:sekolah,npsn'],
            'nik' => 'required|string|max:20',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date_format:Y-m-d|before:today',
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
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter',
            'npsn_asal_sekolah.required' => 'NPSN Asal Sekolah wajib diisi',
            'npsn_asal_sekolah.size' => 'NPSN harus 8 karakter',
            'npsn_asal_sekolah.regex' => 'NPSN hanya boleh berisi huruf dan angka',
            'npsn_asal_sekolah.exists' => 'NPSN tidak ditemukan. Silakan klik tombol "Cari" terlebih dahulu untuk memvalidasi NPSN.',
            'nik.required' => 'NIK wajib diisi',
            'nik.max' => 'NIK maksimal 20 karakter',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date_format' => 'Format tanggal lahir tidak valid',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'agama.required' => 'Agama wajib dipilih',
            'jumlah_saudara.required' => 'Jumlah saudara wajib diisi',
            'anak_ke.required' => 'Anak ke berapa wajib diisi',
            'alamat_sama_ortu.required' => 'Pilihan alamat wajib dipilih',
            'jenis_tempat_tinggal.required_if' => 'Jenis tempat tinggal wajib dipilih untuk alamat berbeda',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan oleh akun lain',
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

        // Convert tanggal_lahir to proper format if needed
        if (!empty($validated['tanggal_lahir'])) {
            try {
                $validated['tanggal_lahir'] = Carbon::createFromFormat('Y-m-d', $validated['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['tanggal_lahir' => 'Format tanggal lahir tidak valid. Silakan pilih ulang dari kalender.']);
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

        $validated = UppercaseInputNormalizer::normalize($validated, [
            'nama_lengkap',
            'npsn_asal_sekolah',
            'tempat_lahir',
            'hobi',
            'cita_cita',
            'alamat_siswa',
        ]);

        $email = $validated['email'] ?? null;
        unset($validated['email']);

        try {
            // Get old data before update
            $oldData = $siswa->toArray();
            
            $validated['data_diri_completed'] = true;

            DB::transaction(function () use ($siswa, $user, $validated, $email, $oldData): void {
                $siswa->update($validated);

                $userData = ['name' => $validated['nama_lengkap']];
                if ($email !== null && $email !== '') {
                    $userData['email'] = $email;
                }
                $user->update($userData);

                ActivityLogService::logChanges(
                    'update_data_diri',
                    $siswa,
                    $oldData,
                    $validated,
                    'Memperbarui data diri siswa'
                );
            });

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
        $oldFotoPath = StorageHelper::normalizePublicPath($siswa->foto_profile);
        if ($oldFotoPath) {
            Storage::disk('public')->delete($oldFotoPath);
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
            'npsn' => ['required', 'size:8', 'regex:/^[A-Za-z0-9]+$/']
        ], [
            'npsn.required' => 'NPSN wajib diisi',
            'npsn.size' => 'NPSN harus 8 karakter',
            'npsn.regex' => 'NPSN hanya boleh berisi huruf dan angka'
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
