<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaRapor;

class AsramaRaporService
{
    private const ARABIC_UNITS = [
        0 => 'صفر',
        1 => 'واحد',
        2 => 'اثنان',
        3 => 'ثلاث',
        4 => 'أربع',
        5 => 'خمس',
        6 => 'ست',
        7 => 'سبع',
        8 => 'ثماني',
        9 => 'تسع',
        10 => 'عشر',
    ];

    public function scoreInArabic(float|int|string|null $score): string
    {
        if ($score === null || $score === '') {
            return '-';
        }

        $value = round((float) $score, 2);
        $whole = (int) floor($value);
        $fraction = round($value - $whole, 2);
        $wholeText = self::ARABIC_UNITS[$whole] ?? $this->digitsToArabic($whole);

        if ($fraction === 0.0) {
            return $wholeText;
        }

        if ($fraction === 0.5) {
            return $wholeText.' ونصف';
        }

        if ($fraction === 0.25) {
            return $wholeText.' وربع';
        }

        if ($fraction === 0.75) {
            return $wholeText.' وثلاثة أرباع';
        }

        return $wholeText.' فاصلة '.$this->digitsToArabic((int) round($fraction * 100));
    }

    public function descriptor(float|int|string|null $score): string
    {
        if ($score === null || $score === '') {
            return '-';
        }
        $score = (float) $score;
        if ($score >= 9) {
            return 'ممتاز';
        }
        if ($score >= 8) {
            return 'جيد جدا';
        }
        if ($score >= 7) {
            return 'جيد';
        }
        if ($score >= 6) {
            return 'مقبول';
        }

        return 'ناقص';
    }

    public function build(AsramaKelasSantri $membership, string $semester): array
    {
        $membership->loadMissing([
            'santri.siswa',
            'santri.asrama.kepala',
            'kelas.tahunPelajaran',
            'kelas.wali.gtk',
            'nilai.pengampu.mapel',
        ]);

        $scores = $membership->nilai
            ->filter(fn ($nilai) => $nilai->pengampu?->semester === $semester)
            ->sortBy(fn ($nilai) => sprintf(
                '%05d|%s',
                $nilai->pengampu?->mapel?->urutan ?? 0,
                $nilai->pengampu?->mapel?->nama_latin ?? ''
            ))
            ->map(fn ($nilai) => [
                'kode' => $nilai->pengampu?->mapel?->kode,
                'nama_latin' => $nilai->pengampu?->mapel?->nama_latin,
                'nama_arab' => $nilai->pengampu?->mapel?->nama_arab,
                'nilai' => $nilai->nilai === null ? null : (float) $nilai->nilai,
                'nilai_arab' => $this->scoreInArabic($nilai->nilai),
            ])->values();

        $numeric = $scores->pluck('nilai')->filter(fn ($value) => $value !== null);
        $setting = AppSetting::getInstance();

        return [
            'identity' => [
                'nama' => $membership->santri->siswa->nama_lengkap,
                'nisn' => $membership->santri->siswa->nisn,
                'nomor_induk_asrama' => $membership->santri->nomor_induk_asrama,
                'kelas' => $membership->kelas->nama_kelas,
                'kelas_arab' => $membership->kelas->nama_arab,
                'tahun_pelajaran' => $membership->kelas->tahunPelajaran->nama,
                'semester' => $semester,
            ],
            'institution' => [
                'nama_sekolah' => $setting->nama_sekolah,
                'alamat' => $setting->alamat_lengkap,
                'telepon' => $setting->telepon,
                'email' => $setting->email,
                'website' => $setting->website,
                'logo_sekolah' => $setting->logo_sekolah_url,
                'logo_kemenag' => $setting->logo_kemenag_url,
                'nama_asrama' => $membership->santri->asrama->nama,
                'kepala_asrama' => $membership->santri->asrama->kepala?->nama_lengkap,
                'nip_kepala_asrama' => $membership->santri->asrama->kepala?->nip,
                'wali_kelas' => $membership->kelas->wali?->gtk?->nama_lengkap,
                'nip_wali_kelas' => $membership->kelas->wali?->gtk?->nip,
            ],
            'scores' => $scores->all(),
            'summary' => [
                'jumlah' => round((float) $numeric->sum(), 2),
                'rata_rata' => $numeric->isEmpty() ? null : round((float) $numeric->avg(), 2),
                'jumlah_mapel' => $numeric->count(),
            ],
        ];
    }

    public function publishSnapshot(AsramaRapor $rapor): array
    {
        return $this->build($rapor->kelasSantri, $rapor->semester);
    }

    private function digitsToArabic(int $number): string
    {
        return strtr((string) $number, [
            '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
            '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
        ]);
    }
}
