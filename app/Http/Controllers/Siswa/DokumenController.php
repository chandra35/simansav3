<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DokumenSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokumenController extends Controller
{
    /**
     * Display dokumen page
     */
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        
        if (!$siswa) {
            abort(403, 'Data siswa tidak ditemukan');
        }

        $dokumen = $siswa->dokumen()->latest()->get();
        
        return view('siswa.dokumen.index', compact('siswa', 'dokumen'));
    }

    /**
     * Upload dokumen
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'jenis_dokumen' => 'required|in:kk,ijazah_smp,kip,sktm',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // max 2MB
                'keterangan' => 'nullable|string|max:500',
            ], [
                'file.required' => 'File dokumen wajib diupload',
                'file.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG',
                'file.max' => 'Ukuran file maksimal 2MB',
            ]);

            $user = Auth::user();
            $siswa = $user->siswa;

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }

            // Check if dokumen already exists
            $existing = DokumenSiswa::where('siswa_id', $siswa->id)
                ->where('jenis_dokumen', $request->jenis_dokumen)
                ->first();

            if ($existing) {
                // Delete old file
                if (Storage::exists($existing->file_path)) {
                    Storage::delete($existing->file_path);
                }
                // Delete old record
                $existing->delete();
            }

            // Upload file
            $file = $request->file('file');
            $fileName = Str::slug($siswa->nama_lengkap) . '_' . $request->jenis_dokumen . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('dokumen-siswa', $fileName, 'public');
            
            $fileSize = round($file->getSize() / 1024, 2); // Convert to KB

            // Create dokumen record
            DokumenSiswa::create([
                'siswa_id' => $siswa->id,
                'jenis_dokumen' => $request->jenis_dokumen,
                'nama_file' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'keterangan' => $request->keterangan,
            ]);

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'upload',
                'model_type' => 'App\\Models\\DokumenSiswa',
                'model_id' => $siswa->id,
                'description' => "Upload dokumen: " . $request->jenis_dokumen,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading dokumen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete dokumen
     */
    public function destroy($id)
    {
        try {
            $dokumen = DokumenSiswa::findOrFail($id);
            
            // Check ownership
            $user = Auth::user();
            if ($dokumen->siswa->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus dokumen ini'
                ], 403);
            }

            // Delete file from storage
            if (Storage::exists($dokumen->file_path)) {
                Storage::delete($dokumen->file_path);
            }

            $jenisDokumen = $dokumen->jenis_dokumen;
            $dokumen->delete();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'delete',
                'model_type' => 'App\\Models\\DokumenSiswa',
                'model_id' => $dokumen->siswa_id,
                'description' => "Menghapus dokumen: " . $jenisDokumen,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting dokumen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
            ], 500);
        }
    }
}
