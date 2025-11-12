<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DokumenSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\ActivityLogService;
use App\Helpers\StorageHelper;
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

            // Ensure storage exists
            StorageHelper::ensureStorageExists();
            
            // Get writable disk
            $disk = StorageHelper::getDokumenDisk();

            // For non-lainnya dokumen, check if already exists and replace
            if ($request->jenis_dokumen !== 'lainnya') {
                $existing = DokumenSiswa::where('siswa_id', $siswa->id)
                    ->where('jenis_dokumen', $request->jenis_dokumen)
                    ->first();

                if ($existing) {
                    // Delete old file (check all possible disks for backward compatibility)
                    $oldDisk = $existing->storage_disk ?? StorageHelper::getDiskFromPath($existing->file_path);
                    
                    try {
                        if (Storage::disk($oldDisk)->exists($existing->file_path)) {
                            Storage::disk($oldDisk)->delete($existing->file_path);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old file', [
                            'file_path' => $existing->file_path,
                            'disk' => $oldDisk,
                            'error' => $e->getMessage(),
                        ]);
                    }
                    
                    // Delete old record
                    $existing->delete();
                }
            }

            // Generate UUID for secure filename
            $file = $request->file('file');
            $uuid = Str::uuid()->toString();
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            
            // Secure filename: {UUID}.ext
            $fileName = "{$uuid}.{$extension}";
            
            // Store in new storage: {NISN}/{UUID}.ext
            $nisn = $siswa->nisn;
            $filePath = "{$nisn}/{$fileName}";
            
            Storage::disk($disk)->put($filePath, file_get_contents($file));
            
            $fileSize = round($file->getSize() / 1024, 2); // Convert to KB

            // Get active tahun pelajaran
            $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();
            
            // Get current kelas from siswa_kelas
            $currentKelas = $siswa->kelasAktif->first();

            // Create dokumen record with security fields
            $dokumen = DokumenSiswa::create([
                'siswa_id' => $siswa->id,
                'file_uuid' => $uuid,
                'jenis_dokumen' => $request->jenis_dokumen,
                'nama_file' => $fileName,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'keterangan' => $request->keterangan,
                'tahun_pelajaran' => $tahunPelajaran ? $tahunPelajaran->nama : null,
                'kelas_id' => $currentKelas ? $currentKelas->id : null,
                'uploaded_by_role' => 'siswa',
                'status' => 'pending',
                'storage_disk' => $disk, // Track which disk used
            ]);

            // Enhanced activity log
            ActivityLogService::log([
                'activity_type' => 'upload_dokumen',
                'model_type' => DokumenSiswa::class,
                'model_id' => $dokumen->id,
                'description' => "Upload dokumen: " . ($request->jenis_dokumen === 'lainnya' ? $request->nama_dokumen : $request->jenis_dokumen),
                'new_values' => [
                    'jenis_dokumen' => $request->jenis_dokumen,
                    'original_name' => $originalName,
                    'file_uuid' => $uuid,
                    'file_size' => $fileSize . ' KB',
                    'status' => 'pending',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'dokumen' => [
                    'id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'nama_file' => $dokumen->original_name ?? $dokumen->nama_file,
                    'file_size' => $dokumen->file_size,
                    'status' => $dokumen->status,
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
            $disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);
            
            try {
                if (Storage::disk($disk)->exists($dokumen->file_path)) {
                    Storage::disk($disk)->delete($dokumen->file_path);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete file from storage', [
                    'file_path' => $dokumen->file_path,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
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

    /**
     * Preview dokumen with authentication
     */
    public function preview($id)
    {
        try {
            $dokumen = DokumenSiswa::findOrFail($id);
            
            // Check ownership or admin permission
            $user = Auth::user();
            $isOwner = $dokumen->siswa->user_id == $user->id;
            $isAdmin = $user->can('view-siswa');
            
            if (!$isOwner && !$isAdmin) {
                abort(403, 'Anda tidak memiliki akses untuk melihat dokumen ini');
            }

            // Update audit trail
            $dokumen->increment('access_count');
            $dokumen->update(['accessed_at' => now()]);

            // Get disk and file path
            $disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);
            
            if (!Storage::disk($disk)->exists($dokumen->file_path)) {
                abort(404, 'File dokumen tidak ditemukan');
            }

            // Get file content
            $fileContent = Storage::disk($disk)->get($dokumen->file_path);
            $fileName = $dokumen->original_name ?? $dokumen->nama_file;
            
            return response($fileContent, 200, [
                'Content-Type' => $dokumen->mime_type,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing dokumen', [
                'dokumen_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Gagal menampilkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen with authentication
     */
    public function download($id)
    {
        try {
            $dokumen = DokumenSiswa::findOrFail($id);
            
            // Check ownership or admin permission
            $user = Auth::user();
            $isOwner = $dokumen->siswa->user_id == $user->id;
            $isAdmin = $user->can('view-siswa');
            
            if (!$isOwner && !$isAdmin) {
                abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini');
            }

            // Update audit trail
            $dokumen->increment('access_count');
            $dokumen->update(['accessed_at' => now()]);

            // Get disk and file path
            $disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);
            
            if (!Storage::disk($disk)->exists($dokumen->file_path)) {
                abort(404, 'File dokumen tidak ditemukan');
            }

            // Download file with original name
            $fileName = $dokumen->original_name ?? $dokumen->nama_file;
            $fileContent = Storage::disk($disk)->get($dokumen->file_path);
            
            return response($fileContent, 200, [
                'Content-Type' => $dokumen->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading dokumen', [
                'dokumen_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }
}
