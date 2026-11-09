<?php

namespace App\Services;

use App\Models\MoodleQuizArchive;
use App\Models\MoodleQuizArchiveScore;
use App\Models\PenilaianSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class MoodleQuizArchiveService
{
    /**
     * Simpan / Perbarui Snapshot Kuis Moodle ke Arsip Lokal SIMANSA.
     */
    public function saveArchive(array $payload, User $user): MoodleQuizArchive
    {
        $quizId = (int)($payload['quiz_id'] ?? 0);
        if (!$quizId) {
            throw new \InvalidArgumentException('Parameter quiz_id wajib disertakan.');
        }

        // Tentukan Tahun Pelajaran & Semester
        $tahunId = $payload['tahun_pelajaran_id'] ?? null;
        if (!$tahunId) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahunId = $tahunAktif?->id;
            $semester = $tahunAktif?->semester_aktif === 'Genap' ? 2 : 1;
        } else {
            $tp = TahunPelajaran::find($tahunId);
            $semester = (int)($payload['semester'] ?? ($tp?->semester_aktif === 'Genap' ? 2 : 1));
        }

        // Tentukan GTK jika dioper langsung di payload atau user adalah guru
        $gtkId = $payload['gtk_id'] ?? null;
        if (!$gtkId && $user->gtk) {
            $gtkId = $user->gtk->id;
        }

        // Jika masih null, coba deteksi dari jadwal pelajaran mapel & rombel
        if (!$gtkId && !empty($payload['mata_pelajaran_id'])) {
            $mapelId = $payload['mata_pelajaran_id'];
            $kelasId = $payload['kelas_id'] ?? null;
            $gtkId = \App\Models\JadwalPelajaran::where('mapel_id', $mapelId)
                ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
                ->value('gtk_id');

            if (!$gtkId) {
                $gtkId = \App\Models\JadwalPelajaran::where('mapel_id', $mapelId)
                    ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                    ->value('gtk_id');
            }
        }

        return DB::transaction(function () use ($payload, $user, $quizId, $tahunId, $semester, $gtkId) {
            // Cek apakah arsip untuk kuis ini pada semester & GTK ini sudah ada
            $existing = MoodleQuizArchive::where('moodle_quiz_id', $quizId)
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->when($gtkId, fn($q) => $q->where('gtk_id', $gtkId))
                ->first();

            if ($existing && $existing->is_locked) {
                throw new \DomainException('Arsip kuis ini telah DIKUNCI (locked). Silakan buka kunci terlebih dahulu jika ingin memperbarui.');
            }

            $stats = $payload['statistics'] ?? [];
            if (isset($payload['kelas_ids']) && is_array($payload['kelas_ids'])) {
                $stats['target_kelas_ids'] = array_values(array_filter($payload['kelas_ids']));
            }

            $archive = $existing ?? new MoodleQuizArchive();
            $archive->fill([
                'gtk_id' => $gtkId,
                'user_id' => $user->id,
                'tahun_pelajaran_id' => $tahunId,
                'semester' => $semester,
                'mata_pelajaran_id' => $payload['mata_pelajaran_id'] ?? null,
                'kelas_id' => $payload['kelas_id'] ?? null,
                'moodle_course_id' => (int)($payload['course_id'] ?? 0),
                'moodle_course_name' => $payload['course_name'] ?? 'Kursus Moodle',
                'moodle_category_id' => isset($payload['category_id']) ? (int)$payload['category_id'] : null,
                'moodle_category_name' => $payload['category_name'] ?? null,
                'moodle_quiz_id' => $quizId,
                'moodle_quiz_name' => $payload['quiz_name'] ?? ('Kuis #' . $quizId),
                'kategori_penilaian' => $payload['kategori_penilaian'] ?? 'harian',
                'nama_penilaian' => $payload['nama_penilaian'] ?? ($payload['quiz_name'] ?? 'Penilaian CBT'),
                'statistics' => !empty($stats) ? $stats : null,
                'items_analysis' => $payload['items_analysis'] ?? null,
                'catatan' => $payload['catatan'] ?? null,
            ]);
            $archive->save();

            // Simpan / update daftar skor siswa
            $scores = $payload['scores'] ?? [];
            if (!empty($scores)) {
                // Preload matching siswa di SIMANSA by NISN
                $usernames = collect($scores)->pluck('username')->filter()->unique()->values()->all();
                $siswaMap = Siswa::whereIn('nisn', $usernames)
                    ->with('kelasTahunAktif')
                    ->get()
                    ->keyBy('nisn');

                // Hapus skor lama jika ada
                MoodleQuizArchiveScore::where('archive_id', $archive->id)->delete();

                $scoreRows = [];
                foreach ($scores as $s) {
                    $uName = $s['username'] ?? '';
                    $matchedSiswa = $siswaMap->get($uName);
                    $kelasNama = $matchedSiswa?->kelasTahunAktif?->first()?->nama_kelas ?? ($s['simansa']['rombel'] ?? null);

                    $scoreRows[] = [
                        'id' => (string)\Illuminate\Support\Str::uuid(),
                        'archive_id' => $archive->id,
                        'moodle_userid' => isset($s['userid']) ? (int)$s['userid'] : null,
                        'moodle_username' => $uName,
                        'siswa_id' => $matchedSiswa?->id ?? ($s['simansa']['id'] ?? null),
                        'nama_siswa' => $matchedSiswa?->nama_lengkap ?? ($s['fullname'] ?? 'Siswa'),
                        'kelas_nama' => $kelasNama,
                        'grade' => (float)($s['normalized_100'] ?? ($s['grade'] ?? 0)),
                        'max_grade' => 100.00,
                        'time_spent_seconds' => isset($s['time_spent_seconds']) ? (int)$s['time_spent_seconds'] : null,
                        'time_finished' => !empty($s['finished_at']) ? date('Y-m-d H:i:s', strtotime($s['finished_at'])) : null,
                        'responses_summary' => !empty($s['responses']) ? json_encode($s['responses']) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                foreach (array_chunk($scoreRows, 200) as $chunk) {
                    MoodleQuizArchiveScore::insert($chunk);
                }
            }

            return $archive->load(['gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'scores']);
        });
    }

    /**
     * Kunci arsip kuis agar data beku secara permanen.
     */
    public function lockArchive(MoodleQuizArchive $archive, User $user): MoodleQuizArchive
    {
        $archive->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $user->id,
        ]);

        return $archive;
    }

    /**
     * Buka kunci arsip kuis.
     */
    public function unlockArchive(MoodleQuizArchive $archive, User $user): MoodleQuizArchive
    {
        $archive->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return $archive;
    }

    /**
     * Perbarui metadata arsip kuis (Khusus Super Admin).
     */
    public function updateArchiveMetadata(MoodleQuizArchive $archive, array $data, User $user): MoodleQuizArchive
    {
        $gtkId = $data['gtk_id'] ?? null;
        $tahunId = $data['tahun_pelajaran_id'] ?? $archive->tahun_pelajaran_id;
        $kelasId = $data['kelas_id'] ?? null;

        // Auto-resolve GTK jika belum diisi tapi mapel dipilih
        if (!$gtkId && !empty($data['mata_pelajaran_id'])) {
            $gtkId = \App\Models\JadwalPelajaran::where('mapel_id', $data['mata_pelajaran_id'])
                ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
                ->value('gtk_id');

            if (!$gtkId) {
                $gtkId = \App\Models\JadwalPelajaran::where('mapel_id', $data['mata_pelajaran_id'])
                    ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
                    ->value('gtk_id');
            }
        }

        $stats = $archive->statistics ?? [];
        if (isset($data['kelas_ids']) && is_array($data['kelas_ids'])) {
            $stats['target_kelas_ids'] = array_values(array_filter($data['kelas_ids']));
        }

        $archive->fill([
            'nama_penilaian' => $data['nama_penilaian'] ?? $archive->nama_penilaian,
            'tahun_pelajaran_id' => $tahunId,
            'semester' => isset($data['semester']) ? (int)$data['semester'] : $archive->semester,
            'kategori_penilaian' => $data['kategori_penilaian'] ?? $archive->kategori_penilaian,
            'mata_pelajaran_id' => array_key_exists('mata_pelajaran_id', $data) ? ($data['mata_pelajaran_id'] ?: null) : $archive->mata_pelajaran_id,
            'gtk_id' => array_key_exists('gtk_id', $data) ? ($data['gtk_id'] ?: null) : ($gtkId ?: $archive->gtk_id),
            'kelas_id' => array_key_exists('kelas_id', $data) ? ($data['kelas_id'] ?: null) : $archive->kelas_id,
            'statistics' => !empty($stats) ? $stats : null,
            'catatan' => array_key_exists('catatan', $data) ? $data['catatan'] : $archive->catatan,
        ]);
        $archive->save();

        return $archive;
    }

    /**
     * Hapus arsip kuis beserta seluruh rekaman skornya (Khusus Super Admin).
     */
    public function deleteArchive(MoodleQuizArchive $archive, User $user): bool
    {
        return DB::transaction(function () use ($archive) {
            $archive->scores()->delete();
            return (bool)$archive->delete();
        });
    }

    /**
     * Sinkronisasikan skor dari arsip ke Buku Nilai Siswa (penilaian_siswa).
     */
    public function syncToPenilaian(MoodleQuizArchive $archive, array $options, User $user): array
    {
        $kelasId = $options['kelas_id'] ?? $archive->kelas_id;
        $mapelId = $options['mata_pelajaran_id'] ?? $archive->mata_pelajaran_id;
        $kategori = $options['kategori'] ?? $archive->kategori_penilaian ?? 'harian';
        $kodePenilaian = $options['kode_penilaian'] ?? null;
        $namaPenilaian = $options['nama_penilaian'] ?? $archive->nama_penilaian ?? 'Nilai CBT Moodle';

        if (!$mapelId) {
            throw new \InvalidArgumentException('Mata Pelajaran wajib dipilih untuk sinkronisasi ke Buku Nilai.');
        }

        $scores = $archive->scores()->with(['siswa.kelasTahunAktif'])->whereNotNull('siswa_id')->get();
        if ($scores->isEmpty()) {
            throw new \RuntimeException('Tidak ada data siswa yang cocok dengan SIMANSA pada arsip ini.');
        }

        $targetKelasIds = !empty($options['kelas_ids'])
            ? array_values(array_filter((array)$options['kelas_ids']))
            : (!empty($archive->statistics['target_kelas_ids'])
                ? array_values(array_filter((array)$archive->statistics['target_kelas_ids']))
                : ($kelasId ? [$kelasId] : []));

        // Filter berdasarkan siswa di kelas target jika ditentukan
        $siswaIdsInKelas = [];
        if (!empty($targetKelasIds)) {
            $siswaIdsInKelas = Siswa::whereHas('kelasTahunAktif', fn($q) => $q->whereIn('kelas.id', $targetKelasIds))
                ->pluck('id')
                ->toArray();
        }

        $synced = 0;
        $skipped = 0;
        $remedialScores = $options['remedial_scores'] ?? [];

        DB::transaction(function () use (
            $scores, $siswaIdsInKelas, $archive, $kelasId, $targetKelasIds, $mapelId,
            $kategori, $kodePenilaian, $namaPenilaian, $user, $remedialScores, &$synced, &$skipped
        ) {
            foreach ($scores as $sc) {
                // Bila filter kelas aktif dan siswa bukan anggota kelas target, skip
                if (!empty($siswaIdsInKelas) && !in_array($sc->siswa_id, $siswaIdsInKelas)) {
                    $skipped++;
                    continue;
                }

                $targetKelasId = (count($targetKelasIds) === 1 ? $targetKelasIds[0] : null)
                    ?: $sc->siswa?->kelasTahunAktif?->first()?->id
                    ?: $kelasId;

                if (!$targetKelasId) {
                    $skipped++;
                    continue;
                }

                $remItem = $remedialScores[$sc->siswa_id] ?? $remedialScores[$sc->moodle_username] ?? null;
                $remScore = null;
                $finalScore = $sc->grade;
                $ket = 'Disinkronkan dari CBT Moodle (' . $archive->moodle_quiz_name . ')';

                if ($remItem) {
                    if (isset($remItem['remedial']) && $remItem['remedial'] !== '' && $remItem['remedial'] !== null) {
                        $remScore = (float)$remItem['remedial'];
                    }
                    if (isset($remItem['nilai_akhir']) && $remItem['nilai_akhir'] !== '' && $remItem['nilai_akhir'] !== null) {
                        $finalScore = (float)$remItem['nilai_akhir'];
                    }
                    if (!empty($remItem['keterangan'])) {
                        $ket = $remItem['keterangan'];
                    }
                }

                PenilaianSiswa::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $archive->tahun_pelajaran_id,
                        'semester' => $archive->semester,
                        'kelas_id' => $targetKelasId,
                        'mata_pelajaran_id' => $mapelId,
                        'siswa_id' => $sc->siswa_id,
                        'kategori' => $kategori,
                        'kode_penilaian' => $kodePenilaian,
                    ],
                    [
                        'gtk_id' => $archive->gtk_id,
                        'nama_penilaian' => $namaPenilaian,
                        'moodle_quiz_id' => $archive->moodle_quiz_id,
                        'moodle_quiz_archive_id' => $archive->id,
                        'nilai' => $sc->grade,
                        'remedial' => $remScore,
                        'nilai_akhir' => $finalScore,
                        'keterangan' => $ket,
                        'updated_by' => $user->id,
                    ]
                );
                $synced++;
            }

            if (!empty($remedialScores)) {
                $currStats = $archive->statistics ?? [];
                $currStats['remedial_settings'] = [
                    'kkm' => $options['kkm'] ?? 75,
                    'calc_rule' => $options['calc_rule'] ?? 'kkm_cap',
                    'updated_at' => now()->toIso8601String(),
                ];
                $archive->statistics = $currStats;
                $archive->save();
            }
        });

        return [
            'synced' => $synced,
            'skipped' => $skipped,
            'total' => $scores->count(),
        ];
    }

    /**
     * Render Laporan Analisis Butir Soal & Nilai Siswa ke DomPDF.
     */
    public function generatePdf(MoodleQuizArchive $archive)
    {
        $archive->load(['gtk', 'user.gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'scores.siswa']);

        $stats = $archive->statistics ?? [];
        $items = $archive->items_analysis ?? [];
        $scores = $archive->scores;

        // Auto-resolve GTK jika belum terisi di arsip
        $gtk = $archive->gtk;
        if (!$gtk && $archive->mata_pelajaran_id) {
            $resolvedGtkId = \App\Models\JadwalPelajaran::where('mapel_id', $archive->mata_pelajaran_id)
                ->when($archive->tahun_pelajaran_id, fn($q) => $q->where('tahun_pelajaran_id', $archive->tahun_pelajaran_id))
                ->when($archive->kelas_id, fn($q) => $q->where('kelas_id', $archive->kelas_id))
                ->value('gtk_id');

            if (!$resolvedGtkId) {
                $resolvedGtkId = \App\Models\JadwalPelajaran::where('mapel_id', $archive->mata_pelajaran_id)
                    ->when($archive->tahun_pelajaran_id, fn($q) => $q->where('tahun_pelajaran_id', $archive->tahun_pelajaran_id))
                    ->value('gtk_id');
            }

            if ($resolvedGtkId) {
                $gtk = \App\Models\Gtk::find($resolvedGtkId);
            }
        }

        // Fallback jika pembuat arsip adalah GTK
        if (!$gtk && $archive->user?->gtk) {
            $gtk = $archive->user->gtk;
        }

        // Ambil data madrasah & kepala madrasah
        $appSetting = \App\Models\AppSetting::first();
        $kepalaSekolah = $appSetting?->getKepalaSekolah();

        // Distribusi Nilai
        $distribusi = [
            'sangat_tinggi' => $scores->where('grade', '>=', 85)->count(),
            'tinggi' => $scores->whereBetween('grade', [70, 84.99])->count(),
            'sedang' => $scores->whereBetween('grade', [55, 69.99])->count(),
            'kurang' => $scores->where('grade', '<', 55)->count(),
        ];

        // Distribusi Kesukaran
        $itemsCollection = collect($items);
        $kesukaranDist = [
            'mudah' => $itemsCollection->where('kesukaran', 'Mudah')->count(),
            'sedang' => $itemsCollection->where('kesukaran', 'Sedang')->count(),
            'sukar' => $itemsCollection->where('kesukaran', 'Sukar')->count(),
        ];

        $pdf = Pdf::loadView('admin.moodle-sync.quiz-report-pdf', compact(
            'archive', 'stats', 'items', 'scores', 'distribusi', 'kesukaranDist', 'gtk', 'kepalaSekolah', 'appSetting'
        ));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf;
    }
}
