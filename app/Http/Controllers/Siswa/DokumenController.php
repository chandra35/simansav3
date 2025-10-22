<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DokumenSiswa;
use App\Models\Siswa;
use App\Services\ActivityLogService;
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
                'jenis_dokumen' => 'required|in:kk,ijazah_smp,kip,sktm,lainnya',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // max 2MB
                'keterangan' => 'nullable|string|max:500',
                'nama_dokumen' => 'required_if:jenis_dokumen,lainnya|string|max:255',
            ], [
                'file.required' => 'File dokumen wajib diupload',
                'file.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG',
                'file.max' => 'Ukuran file maksimal 2MB',
                'nama_dokumen.required_if' => 'Nama dokumen wajib diisi untuk jenis dokumen lainnya',
            ]);

            $user = Auth::user();
            $siswa = $user->siswa;

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }

            // For non-lainnya dokumen, check if already exists and replace
            if ($request->jenis_dokumen !== 'lainnya') {
                $existing = DokumenSiswa::where('siswa_id', $siswa->id)
                    ->where('jenis_dokumen', $request->jenis_dokumen)
                    ->first();

                if ($existing) {
                    // Delete old file
                    if (Storage::disk('public')->exists($existing->file_path)) {
                        Storage::disk('public')->delete($existing->file_path);
                    }
                    // Delete old record
                    $existing->delete();
                }
            }

            // Generate filename with format: nama-lengkap_jenis-dokumen_nisn.ext
            $file = $request->file('file');
            $namaLengkap = Str::slug($siswa->nama_lengkap, '-');
            $nisn = $siswa->nisn;
            $jenisDokumen = $request->jenis_dokumen;
            
            // For 'lainnya', use custom nama_dokumen
            if ($jenisDokumen === 'lainnya' && $request->nama_dokumen) {
                $jenisDokumen = Str::slug($request->nama_dokumen, '-');
            }
            
            $extension = $file->getClientOriginalExtension();
            $fileName = "{$namaLengkap}_{$jenisDokumen}_{$nisn}.{$extension}";
            
            // Store file in public/storage/dokumen-siswa
            $filePath = $file->storeAs('dokumen-siswa', $fileName, 'public');
            
            $fileSize = round($file->getSize() / 1024, 2); // Convert to KB

            // Create dokumen record
            $dokumen = DokumenSiswa::create([
                'siswa_id' => $siswa->id,
                'jenis_dokumen' => $request->jenis_dokumen,
                'nama_file' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'keterangan' => $request->keterangan,
            ]);

            // Enhanced activity log
            ActivityLogService::log([
                'activity_type' => 'upload_dokumen',
                'model_type' => DokumenSiswa::class,
                'model_id' => $dokumen->id,
                'description' => "Upload dokumen: " . ($request->jenis_dokumen === 'lainnya' ? $request->nama_dokumen : $request->jenis_dokumen),
                'new_values' => [
                    'jenis_dokumen' => $request->jenis_dokumen,
                    'nama_file' => $fileName,
                    'file_size' => $fileSize . ' KB',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'dokumen' => [
                    'id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'nama_file' => $dokumen->nama_file,
                    'file_size' => $dokumen->file_size,
                    'uploaded_at' => $dokumen->created_at->format('d M Y H:i'),
                ]
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
            $oldDokumen = $dokumen->toArray();
            $dokumen->delete();

            // Enhanced activity log
            ActivityLogService::log([
                'activity_type' => 'delete_dokumen',
                'model_type' => DokumenSiswa::class,
                'model_id' => $dokumen->siswa_id,
                'description' => "Menghapus dokumen: " . $jenisDokumen,
                'old_values' => $oldDokumen,
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
