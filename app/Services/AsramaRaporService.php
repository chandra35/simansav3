<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaRapor;

class AsramaRaporService
{
    private const ARABIC_UNITS_FEMININE = [
        0 => 'صفر',
        1 => 'واحدة',
        2 => 'اثنتان',
        3 => 'ثلاث',
        4 => 'أربع',
        5 => 'خمس',
        6 => 'ست',
        7 => 'سبع',
        8 => 'ثماني',
        9 => 'تسع',
        10 => 'عشر',
    ];

    private const ARABIC_UNITS_MASCULINE = [
        0 => 'صفر',
        1 => 'واحد',
        2 => 'اثنان',
        3 => 'ثلاثة',
        4 => 'أربعة',
        5 => 'خمسة',
        6 => 'ستة',
        7 => 'سبعة',
        8 => 'ثمانية',
        9 => 'تسعة',
        10 => 'عشرة',
    ];

    private const ARABIC_TENS = [
        2 => 'عشرون',
        3 => 'ثلاثون',
        4 => 'أربعون',
        5 => 'خمسون',
        6 => 'ستون',
        7 => 'سبعون',
        8 => 'ثمانون',
        9 => 'تسعون',
    ];

    /**
     * Mapel template definition for 16-row Right Panel (RTL, right column).
     */
    public const RIGHT_PANEL_MAPEL = [
        1  => ['kode' => 'ASR-001', 'nama_latin' => 'Bahasa Arab',             'nama_arab' => 'الّلغة العربية'],
        2  => ['kode' => 'ASR-002', 'nama_latin' => 'Muhadatsah',              'nama_arab' => 'المحادثة'],
        3  => ['kode' => 'ASR-003', 'nama_latin' => 'B. Arab Syafahi',         'nama_arab' => 'الّلغة العربية الشفهية'],
        4  => ['kode' => 'ASR-004', 'nama_latin' => 'Imla\'',                  'nama_arab' => 'الإملاء العربيّ'],
        5  => ['kode' => 'ASR-005', 'nama_latin' => 'Insya\'',                 'nama_arab' => 'الإنشاء العربيّ'],
        6  => ['kode' => 'ASR-006', 'nama_latin' => 'Mufrodat',                'nama_arab' => 'المفردات العربيّة'],
        7  => ['kode' => 'ASR-007', 'nama_latin' => 'Nahwu',                   'nama_arab' => 'النحو'],
        8  => ['kode' => 'ASR-008', 'nama_latin' => 'Sharaf',                  'nama_arab' => 'الصرف'],
        9  => ['kode' => 'ASR-009', 'nama_latin' => 'Muthala\'ah',             'nama_arab' => 'المطالعة'],
        10 => ['kode' => 'ASR-010', 'nama_latin' => 'Mahfudzot',               'nama_arab' => 'المحفوظات'],
        11 => ['kode' => 'ASR-011', 'nama_latin' => 'Tauhid / Aqidah',         'nama_arab' => 'التوحيد\\العقيدة'],
        12 => ['kode' => 'ASR-012', 'nama_latin' => 'Tafsir',                  'nama_arab' => 'التفسير'],
        13 => ['kode' => 'ASR-013', 'nama_latin' => 'Hadits',                  'nama_arab' => 'الحديث'],
        14 => ['kode' => 'ASR-014', 'nama_latin' => 'Fiqih',                   'nama_arab' => 'الفقه'],
        15 => ['kode' => 'ASR-015', 'nama_latin' => 'Fiqih Syafahi',           'nama_arab' => 'الفقه الشفهي'],
        16 => ['kode' => 'ASR-016', 'nama_latin' => 'Ushul Fiqh',              'nama_arab' => 'علم أصول الفقه'],
    ];

    /**
     * Mapel template definition for rows 1-10 of Left Panel (RTL, left column).
     */
    public const LEFT_PANEL_MAPEL = [
        1  => ['kode' => 'ASR-017', 'nama_latin' => 'English Lesson',          'nama_arab' => 'English Lesson'],
        2  => ['kode' => 'ASR-018', 'nama_latin' => 'Conversation',            'nama_arab' => 'Conversation'],
        3  => ['kode' => 'ASR-019', 'nama_latin' => 'Oral Examination',        'nama_arab' => 'Oral Examination'],
        4  => ['kode' => 'ASR-020', 'nama_latin' => 'Vocabularies',           'nama_arab' => 'Vocabularies'],
        5  => ['kode' => 'ASR-021', 'nama_latin' => 'Al-Qur\'an wa Tajwid',    'nama_arab' => 'القرآن والتجويد'],
        6  => ['kode' => 'ASR-022', 'nama_latin' => 'Tahfidzul Qur\'an',       'nama_arab' => 'تحفيظ القرآن'],
        // Row 7 depends on Tahfizh track: 'تحسين القرآن' if is_tahfidz else 'الترجمة'
        7  => ['kode' => 'ASR-023', 'nama_latin' => 'Tarjamah / Tahsin',       'nama_arab' => 'الترجمة / تحسين القرآن'],
        8  => ['kode' => 'ASR-024', 'nama_latin' => 'Khitobah Arabiyah',       'nama_arab' => 'الخطابة العربيّة'],
        9  => ['kode' => 'ASR-025', 'nama_latin' => 'Khitobah Indoneesiyah',   'nama_arab' => 'الخطابة الإندونيسيّة'],
        10 => ['kode' => 'ASR-026', 'nama_latin' => 'English Speech',          'nama_arab' => 'English Speech'],
    ];

    /**
     * Format a score into Arabic decimal format using the Arabic comma '،'.
     * e.g. 7.5 => "7،5", 149.5 => "149،5", 85 => "85".
     */
    public function formatScoreNumber(float|int|string|null $score): string
    {
        if ($score === null || $score === '') {
            return '';
        }

        $val = round((float) $score, 2);
        $whole = (int) floor($val);
        $fraction = round($val - $whole, 2);

        if ($fraction <= 0.001) {
            return (string) $whole;
        }

        // Format decimal part (e.g. 0.5 -> "5", 0.15 -> "15")
        $decStr = rtrim(rtrim(substr((string) $fraction, 2), '0'), '');
        return $whole . '،' . $decStr;
    }

    /**
     * Convert score into cardinal Arabic words matching Madrasah report conventions.
     */
    public function scoreInArabic(float|int|string|null $score): string
    {
        if ($score === null || $score === '') {
            return '';
        }

        $value = round((float) $score, 2);
        $whole = (int) floor($value);
        $fraction = round($value - $whole, 2);

        $wholeText = $this->integerToArabicWords($whole);

        if ($fraction <= 0.001) {
            return $wholeText;
        }

        if (abs($fraction - 0.5) < 0.01) {
            return $wholeText . ' ونصف';
        }

        if (abs($fraction - 0.25) < 0.01) {
            return $wholeText . ' وربع';
        }

        if (abs($fraction - 0.75) < 0.01) {
            return $wholeText . ' وثلاثة أرباع';
        }

        $dec = (int) round($fraction * 10);
        $decText = self::ARABIC_UNITS_MASCULINE[$dec] ?? (string) $dec;

        return $wholeText . ' فاصلة ' . $decText;
    }

    /**
     * Integer to Arabic cardinal wording up to 999.
     */
    public function integerToArabicWords(int $num): string
    {
        if ($num === 0) {
            return 'صفر';
        }

        if ($num <= 10) {
            return self::ARABIC_UNITS_FEMININE[$num];
        }

        if ($num < 20) {
            $rem = $num - 10;
            $prefix = match ($rem) {
                1 => 'إحدى',
                2 => 'اثنتا',
                default => self::ARABIC_UNITS_FEMININE[$rem],
            };
            return $prefix . ' عشرة';
        }

        if ($num < 100) {
            $unit = $num % 10;
            $ten = (int) floor($num / 10);
            $tenText = self::ARABIC_TENS[$ten] ?? '';

            if ($unit === 0) {
                return $tenText;
            }

            return self::ARABIC_UNITS_FEMININE[$unit] . ' و' . $tenText;
        }

        if ($num < 1000) {
            $h = (int) floor($num / 100);
            $rem = $num % 100;
            $hText = match ($h) {
                1 => 'مائة',
                2 => 'مائتان',
                default => self::ARABIC_UNITS_FEMININE[$h] . 'مائة',
            };

            if ($rem === 0) {
                return $hText;
            }

            return $hText . ' و' . $this->integerToArabicWords($rem);
        }

        return (string) $num;
    }

    /**
     * Arabic predicate descriptor for character and academic assessment.
     */
    public function descriptor(float|int|string|null $score): string
    {
        if ($score === null || $score === '') {
            return '';
        }

        $val = (float) $score;

        return match (true) {
            $val >= 9 => 'ممتاز',
            $val >= 8 => 'جيّد جدّا',
            $val >= 7 => 'جيّد',
            $val >= 6 => 'مقبول',
            default   => 'ناقص',
        };
    }

    /**
     * Build the structured 16-row x 2-panel Arabic report card dataset.
     */
    public function build(AsramaKelasSantri $membership, string $semester): array
    {
        $membership->loadMissing([
            'santri.siswa',
            'santri.asrama.kepala',
            'kelas.tahunPelajaran',
            'kelas.pengasuhRombel.pengasuh.gtk',
            'pengasuhAssignment.rombelPengasuh.pengasuh.gtk',
            'nilai.pengampu.mapel',
            'rapor',
        ]);

        $isTahfidz = (bool) ($membership->kelas->is_tahfidz ?? false);

        // Map existing grades keyed by mapel kode
        $nilaiMap = [];
        $scoresList = [];

        foreach ($membership->nilai as $nilai) {
            if ($nilai->pengampu?->semester !== $semester) {
                continue;
            }
            $kode = $nilai->pengampu?->mapel?->kode;
            if (! $kode) {
                continue;
            }

            $numericVal = $nilai->nilai !== null ? (float) $nilai->nilai : null;
            $nilaiMap[$kode] = [
                'kode' => $kode,
                'nama_latin' => $nilai->pengampu?->mapel?->nama_latin,
                'nama_arab' => $nilai->pengampu?->mapel?->nama_arab,
                'nilai' => $numericVal,
                'nilai_formatted' => $this->formatScoreNumber($numericVal),
                'nilai_arab' => $this->scoreInArabic($numericVal),
                'detail_nilai' => $nilai->detail_nilai,
            ];

            if ($numericVal !== null) {
                $scoresList[] = $nilaiMap[$kode];
            }
        }

        // Build Right Panel (Rows 1 to 16)
        $rightPanel = [];
        $evaluatedScores = [];

        foreach (self::RIGHT_PANEL_MAPEL as $rowNum => $spec) {
            $kode = $spec['kode'];
            $entry = $nilaiMap[$kode] ?? null;
            $val = $entry['nilai'] ?? null;

            $hasScore = ($val !== null);
            if ($hasScore) {
                $evaluatedScores[] = $val;
            }

            $rightPanel[] = [
                'no' => $rowNum,
                'kode' => $kode,
                'nama_arab' => $spec['nama_arab'],
                'nama_latin' => $spec['nama_latin'],
                'nilai' => $val,
                'nilai_formatted' => $hasScore ? $this->formatScoreNumber($val) : '',
                'nilai_arab' => $hasScore ? $this->scoreInArabic($val) : '',
                'is_evaluated' => $hasScore,
            ];
        }

        // Build Left Panel (Rows 1 to 10 for subjects)
        $leftPanelSubjects = [];

        foreach (self::LEFT_PANEL_MAPEL as $rowNum => $spec) {
            $kode = $spec['kode'];
            $namaArab = $spec['nama_arab'];
            $namaLatin = $spec['nama_latin'];

            // Differentiate Row 7 for Tahfizh vs Non-Tahfizh
            if ($rowNum === 7) {
                if ($isTahfidz) {
                    $namaArab = 'تحسين القرآن';
                    $namaLatin = 'Tahsin Al-Qur\'an';
                } else {
                    $namaArab = 'الترجمة';
                    $namaLatin = 'At-Tarjamah';
                }
            }

            $entry = $nilaiMap[$kode] ?? null;
            $val = $entry['nilai'] ?? null;

            $hasScore = ($val !== null);
            if ($hasScore) {
                $evaluatedScores[] = $val;
            }

            $leftPanelSubjects[] = [
                'no' => $rowNum,
                'kode' => $kode,
                'nama_arab' => $namaArab,
                'nama_latin' => $namaLatin,
                'nilai' => $val,
                'nilai_formatted' => $hasScore ? $this->formatScoreNumber($val) : '',
                'nilai_arab' => $hasScore ? $this->scoreInArabic($val) : '',
                'is_evaluated' => $hasScore,
            ];
        }

        // Calculate Totals & Averages based ONLY on evaluated scores
        $totalSum = count($evaluatedScores) > 0 ? round((float) array_sum($evaluatedScores), 2) : null;
        $evalCount = count($evaluatedScores);
        $avgScore = $evalCount > 0 ? round($totalSum / $evalCount, 1) : null;

        // Retrieve existing rapor record for character evaluation and metadata
        $rapor = $membership->rapor->firstWhere('semester', $semester)
            ?? AsramaRapor::where('asrama_kelas_santri_id', $membership->id)
                ->where('semester', $semester)
                ->first();

        $nilaiKebersihan = $rapor?->nilai_kebersihan !== null ? (float) $rapor->nilai_kebersihan : null;
        $nilaiKelakuan = $rapor?->nilai_kelakuan !== null ? (float) $rapor->nilai_kelakuan : null;
        $nilaiKerajinan = $rapor?->nilai_kerajinan !== null ? (float) $rapor->nilai_kerajinan : null;

        // Build Left Panel Full 16 Rows
        $leftPanel = $leftPanelSubjects;

        // Row 11: Total (مجموع النتائج)
        $leftPanel[] = [
            'no' => 11,
            'is_summary' => true,
            'type' => 'total',
            'nama_arab' => 'مجموع النتائج',
            'nama_latin' => 'Jumlah Nilai',
            'nilai' => $totalSum,
            'nilai_formatted' => $this->formatScoreNumber($totalSum),
            'nilai_arab' => $this->scoreInArabic($totalSum),
        ];

        // Row 12: Blank separator row (matching official Word format)
        $leftPanel[] = [
            'no' => 12,
            'is_blank' => true,
            'nama_arab' => '',
            'nama_latin' => '',
            'nilai' => null,
            'nilai_formatted' => '',
            'nilai_arab' => '',
        ];

        // Row 13: Average (المعدل)
        $leftPanel[] = [
            'no' => 13,
            'is_summary' => true,
            'type' => 'average',
            'nama_arab' => 'المعدل',
            'nama_latin' => 'Rata-rata',
            'nilai' => $avgScore,
            'nilai_formatted' => $this->formatScoreNumber($avgScore),
            'nilai_arab' => $this->scoreInArabic($avgScore),
        ];

        // Row 14: Kebersihan (النظافة)
        $leftPanel[] = [
            'no' => 14,
            'is_character' => true,
            'type' => 'kebersihan',
            'nama_arab' => 'النظافة',
            'nama_latin' => 'Kebersihan',
            'nilai' => $nilaiKebersihan,
            'nilai_formatted' => $this->formatScoreNumber($nilaiKebersihan),
            'predikat_arab' => $this->descriptor($nilaiKebersihan),
        ];

        // Row 15: Kelakuan (السلوك)
        $leftPanel[] = [
            'no' => 15,
            'is_character' => true,
            'type' => 'kelakuan',
            'nama_arab' => 'السلوك',
            'nama_latin' => 'Kelakuan',
            'nilai' => $nilaiKelakuan,
            'nilai_formatted' => $this->formatScoreNumber($nilaiKelakuan),
            'predikat_arab' => $this->descriptor($nilaiKelakuan),
        ];

        // Row 16: Kedisiplinan (المواظبة)
        $leftPanel[] = [
            'no' => 16,
            'is_character' => true,
            'type' => 'kerajinan',
            'nama_arab' => 'المواظبة',
            'nama_latin' => 'Kedisiplinan',
            'nilai' => $nilaiKerajinan,
            'nilai_formatted' => $this->formatScoreNumber($nilaiKerajinan),
            'predikat_arab' => $this->descriptor($nilaiKerajinan),
        ];

        $setting = AppSetting::getInstance();
        $assignedCaregiver = $membership->pengasuhAssignment?->rombelPengasuh?->pengasuh;
        $primaryCaregiver = $membership->kelas->pengasuhRombel->firstWhere('is_primary', true)?->pengasuh
            ?? $membership->kelas->pengasuhRombel->first()?->pengasuh;
        $caregiver = $assignedCaregiver ?? $primaryCaregiver;

        // Determine class Arabic name
        $kelasArab = $membership->kelas->nama_arab;
        if (empty($kelasArab)) {
            $tingkat = $membership->kelas->tingkat ?? 10;
            $tingkatArab = match ($tingkat) {
                10 => 'العاشر',
                11 => 'الحادي عشر',
                12 => 'الثاني عشر',
                default => (string) $tingkat,
            };
            $kelasArab = 'الفصل ' . $tingkatArab;
        }

        // Format dates
        $tglRapor = $rapor?->tanggal_rapor;
        $tglHijriah = $rapor?->tanggal_hijriah;

        return [
            'is_tahfidz' => $isTahfidz,
            'identity' => [
                'nama' => $membership->santri->siswa->nama_lengkap,
                'nisn' => $membership->santri->siswa->nisn,
                'nomor_induk_asrama' => $membership->santri->nomor_induk_asrama,
                'kelas' => $membership->kelas->nama_kelas,
                'kelas_arab' => $kelasArab,
                'tingkat' => $membership->kelas->tingkat,
                'tahun_pelajaran' => $membership->kelas->tahunPelajaran->nama,
                'semester' => $semester,
                'is_tahfidz' => $isTahfidz,
            ],
            'institution' => [
                'nama_sekolah' => $setting->nama_sekolah,
                'alamat' => $setting->alamat_lengkap,
                'telepon' => $setting->telepon,
                'email' => $setting->email,
                'website' => $setting->website,
                'logo_sekolah' => $setting->logo_sekolah_url,
                'logo_kemenag' => $setting->logo_kemenag_url,
                'nama_asrama' => $membership->santri->asrama->nama ?? 'Ma\'had Al-Arsyad',
                'kepala_asrama' => $membership->santri->asrama->kepala?->nama_lengkap ?? 'Kepala Asrama',
                'nip_kepala_asrama' => $membership->santri->asrama->kepala?->nip,
                'pengasuh' => $caregiver?->gtk?->nama_lengkap ?? 'Wali Asrama',
                'nip_pengasuh' => $caregiver?->gtk?->nip,
            ],
            'panels' => [
                'right' => $rightPanel,
                'left' => $leftPanel,
            ],
            // Retain flat scores array for backward compatibility
            'scores' => $scoresList,
            'summary' => [
                'jumlah' => $totalSum,
                'jumlah_formatted' => $this->formatScoreNumber($totalSum),
                'jumlah_arab' => $this->scoreInArabic($totalSum),
                'rata_rata' => $avgScore,
                'rata_rata_formatted' => $this->formatScoreNumber($avgScore),
                'rata_rata_arab' => $this->scoreInArabic($avgScore),
                'jumlah_mapel' => $evalCount,
            ],
            'character' => [
                'kebersihan' => [
                    'nilai' => $nilaiKebersihan,
                    'nilai_formatted' => $this->formatScoreNumber($nilaiKebersihan),
                    'predikat_arab' => $this->descriptor($nilaiKebersihan),
                ],
                'kelakuan' => [
                    'nilai' => $nilaiKelakuan,
                    'nilai_formatted' => $this->formatScoreNumber($nilaiKelakuan),
                    'predikat_arab' => $this->descriptor($nilaiKelakuan),
                ],
                'kerajinan' => [
                    'nilai' => $nilaiKerajinan,
                    'nilai_formatted' => $this->formatScoreNumber($nilaiKerajinan),
                    'predikat_arab' => $this->descriptor($nilaiKerajinan),
                ],
            ],
            'attendance' => [
                'sakit' => (int) ($rapor?->sakit ?? 0),
                'izin' => (int) ($rapor?->izin ?? 0),
                'lain_lain' => (int) ($rapor?->lain_lain ?? 0),
            ],
            'rapor_info' => [
                'tanggal_rapor' => $tglRapor,
                'tanggal_hijriah' => $tglHijriah,
                'catatan_wali' => $rapor?->catatan_wali,
                'predikat' => $rapor?->predikat,
                'keputusan' => $rapor?->keputusan,
            ],
        ];
    }

    public function publishSnapshot(AsramaRapor $rapor): array
    {
        return $this->build($rapor->kelasSantri, $rapor->semester);
    }
}
