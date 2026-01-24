<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CetakController extends Controller
{
    /**
     * Display cetak page
     */
    public function index()
    {
        $this->authorize('view-kelas');
        
        $tahunPelajarans = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->orderBy('urutan')->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];
        
        return view('admin.cetak.index', compact(
            'tahunPelajarans',
            'kurikulums',
            'jurusans',
            'tingkatOptions'
        ));
    }
    
    /**
     * Cetak Absensi Batch (Multiple Kelas)
     */
    public function cetakAbsensiBatch(Request $request)
    {
        $this->authorize('view-kelas');
        
        // Increase memory limit for multiple PDF
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        // Get filter parameters
        $tahunPelajaranId = $request->input('tahun_pelajaran_id');
        $tingkat = $request->input('tingkat');
        $jurusanId = $request->input('jurusan_id');
        $kurikulumId = $request->input('kurikulum_id');
        $kelasIds = $request->input('kelas_ids', []);
        
        // Build query
        $query = Kelas::with([
            'tahunPelajaran',
            'kurikulum',
            'jurusan',
            'waliKelas',
            'siswas' => function($q) use ($tahunPelajaranId) {
                $q->wherePivot('status', 'aktif')
                  ->wherePivot('tahun_pelajaran_id', $tahunPelajaranId)
                  ->orderBy('nama_lengkap');
            }
        ]);
        
        // Apply filters
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }
        
        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }
        
        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }
        
        if ($kurikulumId) {
            $query->where('kurikulum_id', $kurikulumId);
        }
        
        if (!empty($kelasIds)) {
            $query->whereIn('id', $kelasIds);
        }
        
        $kelasList = $query->orderBy('tingkat')->orderBy('nama_kelas')->get();
        
        if ($kelasList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada kelas yang ditemukan dengan filter tersebut.');
        }
        
        // Load app settings
        $setting = AppSetting::first();
        
        // Process logos once
        $logoKemenagBase64 = $this->processLogo($setting, 'logo_kemenag_path', 'logo_kemenag_height');
        $logoSekolahBase64 = $this->processLogo($setting, 'logo_sekolah_path', 'logo_sekolah_height');
        
        $data = [
            'kelasList' => $kelasList,
            'setting' => $setting,
            'logoKemenagBase64' => $logoKemenagBase64,
            'logoSekolahBase64' => $logoSekolahBase64,
        ];
        
        // Generate PDF
        $pdf = \PDF::loadView('admin.cetak.absensi-batch', $data);
        $pdf->setPaper('legal', 'portrait');
        
        $filename = 'Absensi_Batch_Tingkat_' . ($tingkat ?? 'All') . '.pdf';
        
        return $pdf->stream($filename);
    }
    
    /**
     * Process logo (resize and encode to base64)
     */
    private function processLogo($setting, $pathField, $heightField)
    {
        if (!$setting || !$setting->$pathField) {
            return null;
        }
        
        $logoPath = storage_path('app/public/' . $setting->$pathField);
        
        if (!file_exists($logoPath)) {
            return null;
        }
        
        $image = imagecreatefromstring(file_get_contents($logoPath));
        
        if ($image === false) {
            return null;
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        $newHeight = $setting->$heightField ?? 100;
        $newWidth = ($width / $height) * $newHeight;
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        ob_start();
        imagepng($resized, null, 6);
        $imageData = ob_get_clean();
        $base64 = 'data:image/png;base64,' . base64_encode($imageData);
        
        imagedestroy($image);
        imagedestroy($resized);
        
        return $base64;
    }
    
    /**
     * Get kelas by filter (AJAX)
     */
    public function getKelasByFilter(Request $request)
    {
        $this->authorize('view-kelas');
        
        $query = Kelas::with(['tahunPelajaran', 'jurusan'])->withCount('siswaAktif');
        
        if ($request->filled('tahun_pelajaran_id')) {
            $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
        }
        
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }
        
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        
        if ($request->filled('kurikulum_id')) {
            $query->where('kurikulum_id', $request->kurikulum_id);
        }
        
        $kelasList = $query->orderBy('tingkat')->orderBy('nama_kelas')->get();
        
        return response()->json([
            'success' => true,
            'data' => $kelasList->map(function($kelas) {
                return [
                    'id' => $kelas->id,
                    'nama_lengkap' => $kelas->nama_lengkap,
                    'tingkat' => $kelas->tingkat,
                    'tingkat_romawi' => $kelas->tingkat_romawi,
                    'jurusan' => $kelas->jurusan?->nama ?? '-',
                    'siswa_count' => $kelas->siswa_aktif_count,
                ];
            })
        ]);
    }
}
