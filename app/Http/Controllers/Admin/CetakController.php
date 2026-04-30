<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        $isRestrictedWaliKelas = $this->isRestrictedWaliKelas(request()->user());
        $defaultTahunPelajaranId = optional($tahunPelajarans->firstWhere('is_active', true))->id
            ?? optional($tahunPelajarans->first())->id;
        
        return view('admin.cetak.index', compact(
            'tahunPelajarans',
            'kurikulums',
            'jurusans',
            'tingkatOptions',
            'isRestrictedWaliKelas',
            'defaultTahunPelajaranId'
        ));
    }

    /**
     * Display ID Card Siswa page
     */
    public function idCardSiswaIndex()
    {
        $this->authorize('view-siswa');

        $tahunPelajarans = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $jurusans = Jurusan::where('is_active', true)->orderBy('urutan')->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];
        $isRestrictedWaliKelas = $this->isRestrictedWaliKelas(request()->user());
        $defaultTahunPelajaranId = optional($tahunPelajarans->firstWhere('is_active', true))->id
            ?? optional($tahunPelajarans->first())->id;

        return view('admin.cetak.id-card-siswa-index', compact(
            'tahunPelajarans',
            'jurusans',
            'tingkatOptions',
            'isRestrictedWaliKelas',
            'defaultTahunPelajaranId'
        ));
    }

    /**
     * Display ID Card GTK page
     */
    public function idCardGtkIndex()
    {
        $this->authorize('view-gtk');

        return view('admin.cetak.id-card-gtk-index');
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
        $this->applyWaliKelasScope($query, $request->user());
        
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
        $this->applyWaliKelasScope($query, $request->user());
        
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

    /**
     * Cetak ID Card Siswa (Batch per Kelas)
     */
    public function cetakIdCardSiswa(Request $request)
    {
        $this->authorize('view-siswa');

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $tahunPelajaranId = $request->input('tahun_pelajaran_id');
        $kelasIds = $request->input('kelas_ids', []);

        if (empty($kelasIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 kelas.');
        }

        $query = Kelas::with([
            'jurusan',
            'tahunPelajaran',
            'siswas' => function ($q) use ($tahunPelajaranId) {
                $q->wherePivot('status', 'aktif')
                  ->wherePivot('tahun_pelajaran_id', $tahunPelajaranId)
                  ->orderBy('nama_lengkap');
            }
        ])->whereIn('id', $kelasIds);
        $this->applyWaliKelasScope($query, $request->user());

        $kelasList = $query->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        if ($kelasList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada kelas yang ditemukan.');
        }

        $setting = AppSetting::first();
        $logoSekolahBase64 = $this->processLogo($setting, 'logo_sekolah_path', 'logo_sekolah_height');
        $logoKemenagBase64 = $this->processLogo($setting, 'logo_kemenag_path', 'logo_kemenag_height');

        // Process foto siswa to base64 for PDF
        foreach ($kelasList as $kelas) {
            foreach ($kelas->siswas as $siswa) {
                $siswa->foto_base64 = $this->processFotoProfile($siswa->foto_profile);
                $siswa->qr_base64 = $this->generateQrCode($siswa->id, 'siswa');
            }
        }

        // Generate card backgrounds
        $sekolahLogoPath = $setting && $setting->logo_sekolah_path ? storage_path('app/public/' . $setting->logo_sekolah_path) : null;
        $bgFrontBase64 = $this->generateCardFrontBg();
        $bgBackBase64 = $this->generateCardGradient(true, $sekolahLogoPath);

        $data = [
            'kelasList' => $kelasList,
            'setting' => $setting,
            'logoSekolahBase64' => $logoSekolahBase64,
            'logoKemenagBase64' => $logoKemenagBase64,
            'bgFrontBase64' => $bgFrontBase64,
            'bgBackBase64' => $bgBackBase64,
        ];

        $pdf = \PDF::loadView('admin.cetak.id-card-siswa', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('ID_Card_Siswa.pdf');
    }

    protected function applyWaliKelasScope($query, $user): void
    {
        if (!$this->isRestrictedWaliKelas($user)) {
            return;
        }

        $kelasIds = $this->getAssignedKelasIds($user);
        if ($kelasIds->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('id', $kelasIds);
    }

    protected function isRestrictedWaliKelas($user): bool
    {
        return $user &&
            $user->hasRole('Wali Kelas') &&
            !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
    }

    protected function getAssignedKelasIds($user)
    {
        return Kelas::query()
            ->where('wali_kelas_id', $user->id)
            ->pluck('id');
    }

    /**
     * Cetak ID Card GTK
     */
    public function cetakIdCardGtk(Request $request)
    {
        $this->authorize('view-gtk');

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $gtkIds = $request->input('gtk_ids', []);
        $kategori = $request->input('kategori_ptk');

        $query = Gtk::query();

        if (!empty($gtkIds)) {
            $query->whereIn('id', $gtkIds);
        }

        if ($kategori) {
            $query->where('kategori_ptk', $kategori);
        }

        $gtkList = $query->orderBy('nama_lengkap')->get();

        if ($gtkList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data GTK yang ditemukan.');
        }

        $setting = AppSetting::first();
        $logoSekolahBase64 = $this->processLogo($setting, 'logo_sekolah_path', 'logo_sekolah_height');
        $logoKemenagBase64 = $this->processLogo($setting, 'logo_kemenag_path', 'logo_kemenag_height');

        // Process foto & Generate QR codes for each GTK
        foreach ($gtkList as $gtk) {
            $gtk->foto_base64 = $this->processFotoProfile($gtk->foto_profile);
            $gtk->qr_base64 = $this->generateQrCode($gtk->id, 'gtk');
        }

        // Generate card backgrounds
        $sekolahLogoPath = $setting && $setting->logo_sekolah_path ? storage_path('app/public/' . $setting->logo_sekolah_path) : null;
        $bgFrontBase64 = $this->generateCardFrontBg();
        $bgBackBase64 = $this->generateCardGradient(true, $sekolahLogoPath);

        $data = [
            'gtkList' => $gtkList,
            'setting' => $setting,
            'logoSekolahBase64' => $logoSekolahBase64,
            'logoKemenagBase64' => $logoKemenagBase64,
            'bgFrontBase64' => $bgFrontBase64,
            'bgBackBase64' => $bgBackBase64,
        ];

        $pdf = \PDF::loadView('admin.cetak.id-card-gtk', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('ID_Card_GTK.pdf');
    }

    /**
     * Get data GTK for AJAX
     */
    public function getGtkByFilter(Request $request)
    {
        $this->authorize('view-gtk');

        $query = Gtk::query();

        if ($request->filled('kategori_ptk')) {
            $query->where('kategori_ptk', $request->kategori_ptk);
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->status_kepegawaian);
        }

        $gtkList = $query->orderBy('nama_lengkap')->get();

        return response()->json([
            'success' => true,
            'data' => $gtkList->map(function ($gtk) {
                return [
                    'id' => $gtk->id,
                    'nama_lengkap' => $gtk->nama_lengkap,
                    'nip' => $gtk->nip ?? '-',
                    'jabatan' => $gtk->jabatan ?? '-',
                    'kategori_ptk' => $gtk->kategori_ptk ?? '-',
                    'status_kepegawaian' => $gtk->status_kepegawaian ?? '-',
                ];
            })
        ]);
    }

    /**
     * Generate QR Code as base64 PNG for PDF embedding
     */
    private function generateQrCode($id, $type)
    {
        $url = url("/verifikasi/{$type}/{$id}");

        $qrSvg = QrCode::format('svg')
            ->size(150)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
    }

    /**
     * Apply rounded corners to a GD image (make corners transparent)
     */
    private function applyRoundedCorners($img, $radius)
    {
        $width = imagesx($img);
        $height = imagesy($img);

        $rounded = imagecreatetruecolor($width, $height);
        imagesavealpha($rounded, true);
        imagealphablending($rounded, false);
        $transparent = imagecolorallocatealpha($rounded, 0, 0, 0, 127);
        imagefill($rounded, 0, 0, $transparent);

        imagealphablending($rounded, true);
        imagecopy($rounded, $img, 0, 0, 0, 0, $width, $height);

        imagealphablending($rounded, false);
        $corners = [
            [0, 0],
            [$width - $radius, 0],
            [0, $height - $radius],
            [$width - $radius, $height - $radius],
        ];

        foreach ($corners as $corner) {
            $cx = $corner[0] + ($corner[0] === 0 ? $radius : 0);
            $cy = $corner[1] + ($corner[1] === 0 ? $radius : 0);

            for ($x = $corner[0]; $x < $corner[0] + $radius; $x++) {
                for ($y = $corner[1]; $y < $corner[1] + $radius; $y++) {
                    $dx = $x - $cx;
                    $dy = $y - $cy;
                    if (($dx * $dx + $dy * $dy) > ($radius * $radius)) {
                        imagesetpixel($rounded, $x, $y, $transparent);
                    }
                }
            }
        }

        imagesavealpha($rounded, true);
        return $rounded;
    }

    /**
     * Generate gradient card background as base64 PNG
     * Creates a diagonal gradient (teal → purple) with decorative circles
     */
    private function generateCardGradient($withWatermark = false, $watermarkLogoPath = null)
    {
        $width = 620;
        $height = 980;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);

        // Diagonal gradient: teal → purple
        $r1 = 68; $g1 = 152; $b1 = 158;   // #44989e teal
        $r2 = 118; $g2 = 88; $b2 = 158;   // #76589e purple

        for ($y = 0; $y < $height; $y++) {
            $ry = $y / $height;
            for ($x = 0; $x < $width; $x++) {
                $rx = $x / $width;
                $ratio = $rx * 0.4 + $ry * 0.6;
                $r = (int)($r1 + ($r2 - $r1) * $ratio);
                $g = (int)($g1 + ($g2 - $g1) * $ratio);
                $b = (int)($b1 + ($b2 - $b1) * $ratio);
                imagesetpixel($img, $x, $y, imagecolorallocate($img, $r, $g, $b));
            }
        }

        // Decorative semi-transparent circles at bottom
        $circle = imagecolorallocatealpha($img, 255, 255, 255, 115);
        imagefilledellipse($img, (int)($width * 0.25), (int)($height * 0.87), (int)($width * 0.45), (int)($width * 0.45), $circle);
        imagefilledellipse($img, (int)($width * 0.72), (int)($height * 0.80), (int)($width * 0.30), (int)($width * 0.30), $circle);

        // Semi-transparent watermark logo
        if ($withWatermark && $watermarkLogoPath && file_exists($watermarkLogoPath)) {
            $logo = @imagecreatefromstring(file_get_contents($watermarkLogoPath));
            if ($logo) {
                $lw = imagesx($logo);
                $lh = imagesy($logo);
                $tw = (int)($width * 0.50);
                $th = (int)($tw * $lh / $lw);
                $scaled = imagecreatetruecolor($tw, $th);
                imagealphablending($scaled, false);
                imagesavealpha($scaled, true);
                imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
                imagecopyresampled($scaled, $logo, 0, 0, 0, 0, $tw, $th, $lw, $lh);

                // Manual alpha-aware merge (imagecopymerge ignores transparency)
                $dstX = (int)(($width - $tw) / 2);
                $dstY = (int)(($height - $th) / 2) - 30;
                $opacity = 0.10; // 10% opacity

                for ($y = 0; $y < $th; $y++) {
                    for ($x = 0; $x < $tw; $x++) {
                        $srcColor = imagecolorat($scaled, $x, $y);
                        $srcA = ($srcColor >> 24) & 0x7F; // 0=opaque, 127=transparent
                        if ($srcA >= 127) continue; // fully transparent, skip

                        $srcR = ($srcColor >> 16) & 0xFF;
                        $srcG = ($srcColor >> 8) & 0xFF;
                        $srcB = $srcColor & 0xFF;

                        $px = $dstX + $x;
                        $py = $dstY + $y;
                        if ($px < 0 || $px >= $width || $py < 0 || $py >= $height) continue;

                        $dstColor = imagecolorat($img, $px, $py);
                        $dstR = ($dstColor >> 16) & 0xFF;
                        $dstG = ($dstColor >> 8) & 0xFF;
                        $dstB = $dstColor & 0xFF;

                        // Combine source alpha with desired opacity
                        $srcAlpha = (1 - $srcA / 127) * $opacity;
                        $newR = (int)($dstR * (1 - $srcAlpha) + $srcR * $srcAlpha);
                        $newG = (int)($dstG * (1 - $srcAlpha) + $srcG * $srcAlpha);
                        $newB = (int)($dstB * (1 - $srcAlpha) + $srcB * $srcAlpha);

                        imagesetpixel($img, $px, $py, imagecolorallocate($img, $newR, $newG, $newB));
                    }
                }

                imagedestroy($scaled);
                imagedestroy($logo);
            }
        }

        // Apply rounded corners (~3mm radius at 10px/mm)
        $rounded = $this->applyRoundedCorners($img, 30);
        imagedestroy($img);

        ob_start();
        imagepng($rounded, null, 9);
        $data = ob_get_clean();
        imagedestroy($rounded);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Generate front card background: white top, gradient bottom (ASN Virtual style)
     */
    private function generateCardFrontBg()
    {
        $width = 620;
        $height = 980;
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, true);

        // Fill entire card with white
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        // Dark navy vertical accent bar at left edge (full height)
        $navyBar = imagecolorallocate($img, 25, 40, 65);
        imagefilledrectangle($img, 0, 0, 8, $height, $navyBar);

        // Bottom gradient section starts at ~65% from top
        $gradStart = (int)($height * 0.65);

        // Smooth fade zone: white → gradient (feather ~30px)
        $fadeZone = 40;
        for ($y = $gradStart - $fadeZone; $y < $gradStart; $y++) {
            $alpha = (int)(127 * (1 - ($y - ($gradStart - $fadeZone)) / $fadeZone));
            for ($x = 9; $x < $width; $x++) {
                $rx = $x / $width;
                $ratio = $rx * 0.3;
                $r = (int)(35 + 55 * $ratio);
                $g = (int)(75 + (-20) * $ratio);
                $b = (int)(95 + 35 * $ratio);
                $col = imagecolorallocatealpha($img, $r, $g, $b, $alpha);
                imagesetpixel($img, $x, $y, $col);
            }
        }

        // Gradient: dark teal → deep purple (bottom section)
        $r1 = 35; $g1 = 75; $b1 = 95;
        $r2 = 85; $g2 = 50; $b2 = 120;

        for ($y = $gradStart; $y < $height; $y++) {
            $ry = ($y - $gradStart) / ($height - $gradStart);
            for ($x = 9; $x < $width; $x++) {
                $rx = $x / $width;
                $ratio = $rx * 0.3 + $ry * 0.7;
                $r = (int)($r1 + ($r2 - $r1) * $ratio);
                $g = (int)($g1 + ($g2 - $g1) * $ratio);
                $b = (int)($b1 + ($b2 - $b1) * $ratio);
                imagesetpixel($img, $x, $y, imagecolorallocate($img, $r, $g, $b));
            }
        }
        // Fill accent bar area in gradient zone too
        for ($y = $gradStart; $y < $height; $y++) {
            for ($x = 0; $x <= 8; $x++) {
                imagesetpixel($img, $x, $y, $navyBar);
            }
        }

        // Large decorative rose/pink circle at bottom-left
        $rose = imagecolorallocatealpha($img, 190, 100, 130, 80);
        imagefilledellipse($img, (int)($width * 0.08), (int)($height * 0.88), (int)($width * 0.55), (int)($width * 0.55), $rose);

        // Subtle light circle at right for depth
        $lightCircle = imagecolorallocatealpha($img, 120, 160, 180, 110);
        imagefilledellipse($img, (int)($width * 0.88), (int)($height * 0.72), (int)($width * 0.30), (int)($width * 0.30), $lightCircle);

        // Small decorative colored bars at bottom-left (= pattern)
        $barY = $height - 60;
        $barX = 25;
        $barColors = [
            imagecolorallocate($img, 190, 65, 65),   // red
            imagecolorallocate($img, 65, 160, 110),   // green
            imagecolorallocate($img, 65, 120, 180),   // blue
        ];
        foreach ($barColors as $i => $color) {
            imagefilledrectangle($img, $barX, $barY + $i * 13, $barX + 22, $barY + $i * 13 + 5, $color);
            imagefilledrectangle($img, $barX + 27, $barY + $i * 13, $barX + 49, $barY + $i * 13 + 5, $color);
        }

        // Apply rounded corners (~3mm radius at 10px/mm)
        $rounded = $this->applyRoundedCorners($img, 30);
        imagedestroy($img);

        ob_start();
        imagepng($rounded, null, 9);
        $data = ob_get_clean();
        imagedestroy($rounded);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Process foto profile to base64 for PDF embedding
     */
    private function processFotoProfile($fotoPath)
    {
        if (!$fotoPath) {
            return null;
        }

        $fullPath = \App\Helpers\StorageHelper::publicFilePath($fotoPath);

        if (!$fullPath || !file_exists($fullPath)) {
            return null;
        }

        $image = @imagecreatefromstring(file_get_contents($fullPath));
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        // Resize to max 400px height for sharp ID card rendering
        $newHeight = 400;
        $newWidth = (int)(($width / $height) * $newHeight);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($resized, null, 95);
        $imageData = ob_get_clean();
        $base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);

        imagedestroy($image);
        imagedestroy($resized);

        return $base64;
    }
}
