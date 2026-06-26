<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DokumenSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\ActivityLogService;
use App\Helpers\StorageHelper;
use App\Helpers\ImageCompressionHelper;
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
     * Update nomor PKH tanpa upload dokumen.
     */
    public function updatePkh(Request $request)
    {
        $request->validate([
            'nomor_pkh' => 'nullable|string|max:50',
        ], [
            'nomor_pkh.max' => 'Nomor PKH maksimal 50 karakter',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan',
            ], 404);
        }

        $oldNomorPkh = $siswa->nomor_pkh;
        $newNomorPkh = trim((string) $request->nomor_pkh);
        $siswa->nomor_pkh = $newNomorPkh !== '' ? $newNomorPkh : null;
        $siswa->save();

        ActivityLogService::log([
            'activity_type' => 'update_nomor_pkh',
            'model_type' => Siswa::class,
            'model_id' => $siswa->id,
            'description' => 'Update nomor PKH siswa',
            'old_values' => ['nomor_pkh' => $oldNomorPkh],
            'new_values' => ['nomor_pkh' => $siswa->nomor_pkh],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor PKH berhasil disimpan',
            'nomor_pkh' => $siswa->nomor_pkh,
        ]);
    }

    /**
     * Upload dokumen
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'jenis_dokumen' => 'required|in:kk,ijazah_smp,kip,sktm,lainnya',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB (akan di-compress otomatis jika image >2MB)
                'keterangan' => 'nullable|string|max:500',
                'nama_dokumen' => 'required_if:jenis_dokumen,lainnya|string|max:255',
            ], [
                'file.required' => 'File dokumen wajib diupload',
                'file.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG',
                'file.max' => 'Ukuran file maksimal 5MB (image akan di-compress otomatis)',
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
            $originalFileSize = $file->getSize();
            
            // Auto-compress image if needed (tidak membebani server, hanya file >2MB)
            // Hanya berlaku untuk image files (JPG, PNG, GIF, WEBP)
            $file = ImageCompressionHelper::compressImage($file);
            
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
            
            // Log compression if happened
            if ($originalFileSize > $file->getSize()) {
                $savedPercentage = round((($originalFileSize - $file->getSize()) / $originalFileSize) * 100, 2);
                Log::info("File compressed on upload", [
                    'original_size' => round($originalFileSize / 1024, 2) . ' KB',
                    'compressed_size' => $fileSize . ' KB',
                    'saved' => $savedPercentage . '%',
                ]);
            }

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
            $isAdmin = $user->can('view-siswa');
            if (!$isAdmin) {
                // Gunakan withTrashed agar tidak null meski siswa sudah dihapus
                $siswaDok = \App\Models\Siswa::withTrashed()->find($dokumen->siswa_id);
                if (!$siswaDok || $siswaDok->user_id != $user->id) {
                    abort(403, 'Anda tidak memiliki akses untuk melihat dokumen ini');
                }
            }

            // Update audit trail
            $dokumen->increment('access_count');
            $dokumen->update(['accessed_at' => now()]);

            // Get disk and file path
            $disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);
            
            if (!Storage::disk($disk)->exists($dokumen->file_path)) {
                abort(404, 'File dokumen tidak ditemukan');
            }

            // Get absolute filesystem path
            $absolutePath = Storage::disk($disk)->path($dokumen->file_path);
            $siswaDok = \App\Models\Siswa::withTrashed()->find($dokumen->siswa_id);
            $fileName = $dokumen->getDownloadFileName($siswaDok?->nama_lengkap);

            // Clear ALL output buffers — PHP startup warnings (e.g. "mbstring already loaded")
            // are buffered before any user code runs; they would corrupt binary streaming.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Stream directly — bypass all Laravel/Symfony response abstractions
            header('Content-Type: ' . $dokumen->mime_type);
            header('Content-Disposition: inline; filename="' . addslashes($fileName) . '"');
            header('Content-Length: ' . filesize($absolutePath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($absolutePath);
            exit;

        } catch (\Throwable $e) {
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
            $siswaDok = \App\Models\Siswa::withTrashed()->find($dokumen->siswa_id);
            $isOwner = $siswaDok && $siswaDok->user_id == $user->id;
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

            $absolutePath = Storage::disk($disk)->path($dokumen->file_path);
            $fileName = $dokumen->getDownloadFileName($siswaDok?->nama_lengkap);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $dokumen->mime_type);
            header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
            header('Content-Length: ' . filesize($absolutePath));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            readfile($absolutePath);
            exit;

        } catch (\Throwable $e) {
            Log::error('Error downloading dokumen', [
                'dokumen_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }
}
