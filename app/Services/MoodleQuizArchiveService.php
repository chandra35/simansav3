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

        // Tentukan GTK jika user adalah guru
        $gtkId = $payload['gtk_id'] ?? null;
        if (!$gtkId && $user->gtk) {
            $gtkId = $user->gtk->id;
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
                'statistics' => $payload['statistics'] ?? null,
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

        // Filter berdasarkan siswa di kelas target jika ditentukan
        $siswaIdsInKelas = [];
        if ($kelasId) {
            $siswaIdsInKelas = Siswa::whereHas('kelasTahunAktif', fn($q) => $q->where('kelas.id', $kelasId))
                ->pluck('id')
                ->toArray();
        }

        $synced = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $scores, $siswaIdsInKelas, $archive, $kelasId, $mapelId,
            $kategori, $kodePenilaian, $namaPenilaian, $user, &$synced, &$skipped
        ) {
            foreach ($scores as $sc) {
                // Bila filter kelas aktif dan siswa bukan anggota kelas target, skip
                if (!empty($siswaIdsInKelas) && !in_array($sc->siswa_id, $siswaIdsInKelas)) {
                    $skipped++;
                    continue;
                }

                $targetKelasId = $kelasId ?: $sc->siswa?->kelasTahunAktif?->first()?->id;
                if (!$targetKelasId) {
                    $skipped++;
                    continue;
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
                        'nilai_akhir' => $sc->grade,
                        'keterangan' => 'Disinkronkan dari CBT Moodle (' . $archive->moodle_quiz_name . ')',
                        'updated_by' => $user->id,
                    ]
                );
                $synced++;
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
        $archive->load(['gtk', 'tahunPelajaran', 'mataPelajaran', 'kelas', 'scores.siswa']);

        $stats = $archive->statistics ?? [];
        $items = $archive->items_analysis ?? [];
        $scores = $archive->scores;

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
            'archive', 'stats', 'items', 'scores', 'distribusi', 'kesukaranDist'
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
