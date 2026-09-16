<?php

namespace App\Services;

use App\Helpers\StorageHelper;
use App\Models\AppSetting;
use App\Models\PendaftaranPpdb;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class StudentBiodataPdfService
{
    /**
     * Render a self-contained biodata document. The photo is embedded so the
     * PDF remains correct when opened or printed outside the application.
     */
    public function stream(Siswa $siswa)
    {
        $siswa->loadMissing([
            'user',
            'ortu.provinsi',
            'ortu.kabupaten',
            'ortu.kecamatan',
            'ortu.kelurahan',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa',
            'sekolahAsal',
            'kelasTahunAktif.jurusan',
            'dokumen' => fn ($query) => $query->latest(),
        ]);

        $kelasAktif = $siswa->kelasTahunAktif->first();
        $setting = AppSetting::query()->first();
        $filename = 'biodata-'.Str::slug($siswa->nama_lengkap ?: 'siswa').'-'.($siswa->nisn ?: $siswa->id).'.pdf';

        $pdf = Pdf::loadView('pdf.siswa-biodata', [
            'siswa' => $siswa,
            'kelasAktif' => $kelasAktif,
            'fotoBase64' => $this->photoDataUri($siswa->foto_profile),
            'pekerjaanOptions' => PendaftaranPpdb::getPekerjaanOptions(),
            'penghasilanOptions' => PendaftaranPpdb::getPenghasilanOptions(),
            'setting' => $setting,
            'logoBase64' => $this->photoDataUri($setting?->logo_sekolah_path, 520),
            'logoKemenagBase64' => $this->photoDataUri($setting?->logo_kemenag_path, 520),
            'printedBy' => auth()->user()?->name ?? 'Sistem SIMANSA',
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }

    private function photoDataUri(?string $fotoPath, int $maxHeight = 360): ?string
    {
        $path = StorageHelper::publicFilePath($fotoPath);

        if (! $path || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $image = @imagecreatefromstring((string) @file_get_contents($path));
        if ($image === false) {
            return null;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $height = min($sourceHeight, $maxHeight);
        $width = max(1, (int) round(($sourceWidth / max(1, $sourceHeight)) * $height));
        $resized = imagecreatetruecolor($width, $height);
        imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        ob_start();
        imagejpeg($resized, null, 82);
        $contents = ob_get_clean();
        imagedestroy($image);
        imagedestroy($resized);

        return $contents ? 'data:image/jpeg;base64,'.base64_encode($contents) : null;
    }
}
