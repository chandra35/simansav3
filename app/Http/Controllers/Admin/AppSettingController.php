<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppSettingController extends Controller
{
    /**
     * Show edit form (Singleton - hanya 1 setting)
     */
    public function edit()
    {
        $this->authorize('manage-settings');
        
        $setting = AppSetting::with(['provinsi', 'kota', 'kecamatan', 'kelurahan'])->first();
        
        if (!$setting) {
            $setting = AppSetting::getInstance();
        }
        
        // Load provinsi list untuk dropdown
        $provinsiList = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        // Get Kepala Sekolah aktif dengan tugas tambahan
        $kepalaSekolahData = $setting->getKepalaSekolahWithTugas();
        $kepalaSekolah = $kepalaSekolahData['user'] ?? null;
        $tugasTambahan = $kepalaSekolahData['tugas'] ?? null;
        
        return view('admin.settings.edit', compact('setting', 'provinsiList', 'kepalaSekolah', 'tugasTambahan'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $this->authorize('manage-settings');
        
        $setting = AppSetting::getInstance();
        
        $validator = Validator::make($request->all(), [
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|size:8',
            'alamat' => 'required|string',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'provinsi_code' => 'required|exists:indonesia_provinces,code',
            'kota_code' => 'required|exists:indonesia_cities,code',
            'kecamatan_code' => 'required|exists:indonesia_districts,code',
            'kelurahan_code' => 'required|exists:indonesia_villages,code',
            'kode_pos' => 'required|string|size:5',
            'telepon' => 'required|string|max:20',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'kop_mode' => 'required|in:builder,custom',
            'kop_surat_config' => 'nullable|json',
            'kop_margin_top' => 'nullable|integer|min:0',
            'kop_height' => 'nullable|integer|min:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $setting->update($request->only([
                'nama_sekolah',
                'npsn',
                'alamat',
                'rt',
                'rw',
                'provinsi_code',
                'kota_code',
                'kecamatan_code',
                'kelurahan_code',
                'kode_pos',
                'telepon',
                'email',
                'website',
                'facebook_url',
                'instagram_url',
                'youtube_url',
                'twitter_url',
                'kop_mode',
                'kop_margin_top',
                'kop_height',
            ]));
            
            // Update kop_surat_config if provided
            if ($request->has('kop_surat_config')) {
                $setting->kop_surat_config = json_decode($request->kop_surat_config, true);
                $setting->save();
            }

            // Log activity
            activity()
                ->performedOn($setting)
                ->causedBy(Auth::user())
                ->withProperties([
                    'nama_sekolah' => $setting->nama_sekolah,
                    'npsn' => $setting->npsn,
                    'kop_mode' => $setting->kop_mode,
                ])
                ->log('Update pengaturan aplikasi');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pengaturan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Upload Logo Kemenag (AJAX)
     */
    public function uploadLogoKemenag(Request $request)
    {
        $this->authorize('manage-settings');
        
        $validator = Validator::make($request->all(), [
            'logo_kemenag' => 'required|image|mimes:png,jpg,jpeg|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $setting = AppSetting::getInstance();
            
            // Delete old logo if exists
            if ($setting->logo_kemenag_path) {
                Storage::delete($setting->logo_kemenag_path);
            }
            
            // Store new logo
            $file = $request->file('logo_kemenag');
            $filename = 'logo-kemenag-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings/logo', $filename, 'public');
            
            $setting->logo_kemenag_path = $path;
            $setting->save();
            
            // Log activity
            activity()
                ->performedOn($setting)
                ->causedBy(Auth::user())
                ->log('Upload logo Kemenag');

            return response()->json([
                'success' => true,
                'message' => 'Logo Kemenag berhasil diupload.',
                'url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Logo Sekolah (AJAX)
     */
    public function uploadLogoSekolah(Request $request)
    {
        $this->authorize('manage-settings');
        
        $validator = Validator::make($request->all(), [
            'logo_sekolah' => 'required|image|mimes:png,jpg,jpeg|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $setting = AppSetting::getInstance();
            
            // Delete old logo if exists
            if ($setting->logo_sekolah_path) {
                Storage::delete($setting->logo_sekolah_path);
            }
            
            // Store new logo
            $file = $request->file('logo_sekolah');
            $filename = 'logo-sekolah-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings/logo', $filename, 'public');
            
            $setting->logo_sekolah_path = $path;
            $setting->save();
            
            // Log activity
            activity()
                ->performedOn($setting)
                ->causedBy(Auth::user())
                ->log('Upload logo sekolah');

            return response()->json([
                'success' => true,
                'message' => 'Logo Sekolah berhasil diupload.',
                'url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Kop Surat Custom (AJAX)
     */
    public function uploadKopSurat(Request $request)
    {
        $this->authorize('manage-settings');
        
        $validator = Validator::make($request->all(), [
            'kop_surat_custom' => 'required|image|mimes:png,jpg,jpeg|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $setting = AppSetting::getInstance();
            
            // Delete old kop surat if exists
            if ($setting->kop_surat_custom_path) {
                Storage::delete($setting->kop_surat_custom_path);
            }
            
            // Store new kop surat
            $file = $request->file('kop_surat_custom');
            $filename = 'kop-surat-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings/kop-surat', $filename, 'public');
            
            $setting->kop_surat_custom_path = $path;
            $setting->kop_mode = 'custom'; // Auto switch to custom mode
            $setting->save();
            
            // Log activity
            activity()
                ->performedOn($setting)
                ->causedBy(Auth::user())
                ->log('Upload kop surat custom');

            return response()->json([
                'success' => true,
                'message' => 'Kop Surat berhasil diupload.',
                'url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload kop surat: ' . $e->getMessage()
            ], 500);
        }
    }
}
