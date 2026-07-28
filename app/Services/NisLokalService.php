<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Kelas;
use App\Models\NisLokalSequence;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class NisLokalService
{
    private const PREVIEW_TTL_SECONDS = 1800;

    public function dashboard(): array
    {
        $setting = AppSetting::getInstance();
        $activeYear = TahunPelajaran::query()->active()->first();

        return [
            'setting' => $setting,
            'activeYear' => $activeYear,
            'generator' => $activeYear ? $this->generatorPreview($activeYear, false) : null,
        ];
    }

    public function generatorPreview(TahunPelajaran $year, bool $store = true): array
    {
        $setting = AppSetting::getInstance();
        $nsm = $this->validatedNsm($setting);
        $classes = Kelas::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->where('tingkat', 10)
            ->where('is_active', true)
            ->get()
            ->sort(fn (Kelas $left, Kelas $right) => $this->compareClasses($left, $right))
            ->values();

        $lastNumber = max(
            (int) NisLokalSequence::query()
                ->where('nsm', $nsm)
                ->where('tahun_masuk', $year->tahun_mulai)
                ->value('nomor_terakhir'),
            (int) Siswa::query()
                ->where('nis_lokal_tahun', $year->tahun_mulai)
                ->max('nis_lokal_urutan')
        );

        $nextNumber = $lastNumber;
        $rows = [];
        $classSummaries = [];
        $seenStudents = [];

        foreach ($classes as $classIndex => $class) {
            $records = $this->orderedClassRecords($class);
            $missing = 0;

            foreach ($records as $attendanceIndex => $record) {
                $student = $record->siswa;
                if (isset($seenStudents[$student->id])) {
                    throw new RuntimeException("{$student->nama_lengkap} tercatat aktif pada lebih dari satu rombel tingkat 10.");
                }
                $seenStudents[$student->id] = true;
                $proposed = $student->nis_lokal;
                if (blank($proposed)) {
                    $nextNumber++;
                    if ($nextNumber > 9999) {
                        throw new RuntimeException('Nomor urut NIS Lokal telah melewati kapasitas 9999.');
                    }
                    $proposed = $this->formatNis($nsm, (int) $year->tahun_mulai, $nextNumber);
                    $missing++;
                }

                $rows[] = [
                    'student_id' => $student->id,
                    'pivot_id' => $record->id,
                    'class_id' => $class->id,
                    'class_name' => $class->nama_lengkap,
                    'class_order' => $classIndex + 1,
                    'attendance_number' => $attendanceIndex + 1,
                    'nisn' => $student->nisn,
                    'name' => $student->nama_lengkap,
                    'current_nis' => $student->nis_lokal,
                    'proposed_nis' => $proposed,
                    'sequence' => blank($student->nis_lokal) ? $nextNumber : $student->nis_lokal_urutan,
                    'will_generate' => blank($student->nis_lokal),
                ];
            }

            $classSummaries[] = [
                'id' => $class->id,
                'name' => $class->nama_lengkap,
                'total' => $records->count(),
                'missing' => $missing,
            ];
        }

        $payload = [
            'nsm' => $nsm,
            'year_id' => $year->id,
            'year' => (int) $year->tahun_mulai,
            'last_number' => $lastNumber,
            'rows' => $rows,
            'classes' => $classSummaries,
            'total' => count($rows),
            'missing' => collect($rows)->where('will_generate', true)->count(),
            'already_assigned' => collect($rows)->where('will_generate', false)->count(),
        ];

        if ($store) {
            $payload['token'] = $this->storePreview('generator', $payload);
        }

        return $payload;
    }

    public function confirmGenerator(string $token): array
    {
        $preview = $this->getPreview('generator', $token);
        $setting = AppSetting::getInstance();
        $nsm = $this->validatedNsm($setting);

        if ($preview['nsm'] !== $nsm) {
            throw new RuntimeException('NSM berubah setelah preview dibuat. Muat ulang preview.');
        }

        $generated = DB::transaction(function () use ($preview, $nsm) {
            $sequence = NisLokalSequence::query()->firstOrCreate(
                ['nsm' => $nsm, 'tahun_masuk' => $preview['year']],
                ['nomor_terakhir' => $preview['last_number']]
            );
            $sequence = NisLokalSequence::query()->lockForUpdate()->findOrFail($sequence->id);

            if ((int) $sequence->nomor_terakhir !== (int) $preview['last_number']) {
                throw new RuntimeException('Urutan NIS berubah karena proses lain. Buat preview baru.');
            }

            $generated = 0;
            foreach (collect($preview['rows'])->where('will_generate', true) as $row) {
                $student = Siswa::query()->lockForUpdate()->findOrFail($row['student_id']);
                $eligible = SiswaKelas::query()
                    ->whereKey($row['pivot_id'])
                    ->where('siswa_id', $student->id)
                    ->where('tahun_pelajaran_id', $preview['year_id'])
                    ->where('tingkat', 10)
                    ->where('status', 'aktif')
                    ->whereNull('deleted_at')
                    ->exists();
                if (! $eligible) {
                    throw new RuntimeException("Status rombel {$student->nama_lengkap} berubah. Buat preview baru.");
                }
                if (filled($student->nis_lokal)) {
                    throw new RuntimeException("{$student->nama_lengkap} sudah memiliki NIS Lokal. Buat preview baru.");
                }

                $sequence->nomor_terakhir++;
                $expected = $this->formatNis($nsm, (int) $preview['year'], (int) $sequence->nomor_terakhir);
                if ($expected !== $row['proposed_nis']) {
                    throw new RuntimeException('Urutan preview tidak lagi konsisten. Buat preview baru.');
                }

                $student->forceFill([
                    'nis_lokal' => $expected,
                    'nis_lokal_tahun' => $preview['year'],
                    'nis_lokal_urutan' => $sequence->nomor_terakhir,
                    'nis_lokal_generated_at' => now(),
                    'nis_lokal_generated_by' => auth()->id(),
                    'tahun_masuk' => $student->tahun_masuk ?: $preview['year'],
                ])->save();
                $generated++;
            }

            foreach (collect($preview['rows'])->groupBy('class_id') as $classRows) {
                foreach ($classRows as $row) {
                    SiswaKelas::query()->whereKey($row['pivot_id'])->update([
                        'nomor_urut_absen' => $row['attendance_number'],
                    ]);
                }
            }

            $sequence->save();

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'tahun_masuk' => $preview['year'],
                    'nsm' => $nsm,
                    'jumlah_generate' => $generated,
                    'jumlah_rombel' => count($preview['classes']),
                ])
                ->log('Generate NIS Lokal tingkat 10 dan sinkronisasi nomor absen');

            return $generated;
        });

        Cache::forget($this->cacheKey('generator', $token));

        return ['generated' => $generated, 'total' => count($preview['rows'])];
    }

    public function importPreview(UploadedFile $file): array
    {
        $setting = AppSetting::getInstance();
        $nsm = $this->validatedNsm($setting);
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $data = $sheet->toArray(null, true, true, false);

        if (count($data) < 2) {
            throw new RuntimeException('File tidak berisi data siswa.');
        }
        if (count($data) > 5001) {
            throw new RuntimeException('File melebihi batas 5.000 baris. Pecah file menjadi beberapa bagian.');
        }

        $headers = collect($data[0])->map(fn ($value) => $this->normalizeHeader($value))->all();
        $required = ['nislokal', 'nisn', 'namalengkap'];
        foreach ($required as $header) {
            if (! in_array($header, $headers, true)) {
                throw new RuntimeException("Kolom {$header} tidak ditemukan. Gunakan template resmi.");
            }
        }

        $headerMap = array_flip($headers);
        $eligible = $this->eligibleImportStudents();
        $byNisn = $eligible->filter(fn ($student) => filled($student->nisn))->keyBy(
            fn ($student) => trim((string) $student->nisn)
        );
        $seenNis = [];
        $seenStudents = [];
        $rows = [];

        foreach (array_slice($data, 1) as $offset => $values) {
            $excelRow = $offset + 2;
            $nis = trim((string) ($values[$headerMap['nislokal']] ?? ''));
            $nisn = preg_replace('/\D+/', '', (string) ($values[$headerMap['nisn']] ?? ''));
            $name = trim((string) ($values[$headerMap['namalengkap']] ?? ''));

            if ($nis === '' && $nisn === '' && $name === '') {
                continue;
            }

            $result = [
                'row' => $excelRow,
                'input_nis' => $nis,
                'input_nisn' => $nisn,
                'input_name' => $name,
                'student_id' => null,
                'matched_nisn' => null,
                'matched_name' => null,
                'class_name' => null,
                'current_nis' => null,
                'score' => null,
                'match_method' => null,
                'action' => null,
                'status' => 'error',
                'message' => null,
            ];

            if (! preg_match('/^\d{18}$/', $nis)) {
                $result['message'] = 'NIS Lokal harus tepat 18 digit dan disimpan sebagai teks di Excel.';
                $rows[] = $result;
                continue;
            }
            if (! str_starts_with($nis, $nsm)) {
                $result['message'] = "NIS Lokal tidak menggunakan NSM {$nsm}.";
                $rows[] = $result;
                continue;
            }
            if (substr($nis, -4) === '0000') {
                $result['message'] = 'Nomor urut NIS Lokal tidak boleh 0000.';
                $rows[] = $result;
                continue;
            }
            if (isset($seenNis[$nis])) {
                $result['message'] = "NIS Lokal duplikat dengan baris {$seenNis[$nis]}.";
                $rows[] = $result;
                continue;
            }
            $seenNis[$nis] = $excelRow;

            $student = $nisn !== '' ? $byNisn->get($nisn) : null;
            $method = $student ? 'NISN' : null;
            $score = $student ? $this->nameSimilarityScore($name, $student->nama_lengkap) : null;

            if ($student && $score < 70) {
                $result['matched_nisn'] = $student->nisn;
                $result['matched_name'] = $student->nama_lengkap;
                $result['score'] = $score;
                $result['message'] = 'NISN ditemukan, tetapi nama terlalu berbeda. Periksa data sumber.';
                $rows[] = $result;
                continue;
            }

            if (! $student) {
                $ranked = $eligible->map(fn ($candidate) => [
                    'student' => $candidate,
                    'score' => $this->nameSimilarityScore($name, $candidate->nama_lengkap),
                ])->sortByDesc('score')->values();
                $best = $ranked->get(0);
                $second = $ranked->get(1);

                if ($best && $best['score'] >= 85 && ($best['score'] - ($second['score'] ?? 0)) >= 5) {
                    $student = $best['student'];
                    $score = $best['score'];
                    $method = 'Nama pintar';
                }
            }

            if (! $student) {
                $result['message'] = 'Siswa tingkat 11/12 tidak ditemukan secara meyakinkan.';
                $rows[] = $result;
                continue;
            }

            if (isset($seenStudents[$student->id])) {
                $result['matched_nisn'] = $student->nisn;
                $result['matched_name'] = $student->nama_lengkap;
                $result['score'] = $score;
                $result['message'] = "Siswa yang sama sudah dipetakan pada baris {$seenStudents[$student->id]}.";
                $rows[] = $result;
                continue;
            }
            $seenStudents[$student->id] = $excelRow;

            $duplicate = Siswa::query()
                ->where('nis_lokal', $nis)
                ->where('id', '!=', $student->id)
                ->first();
            if ($duplicate) {
                $result['message'] = "NIS Lokal sudah digunakan oleh {$duplicate->nama_lengkap}.";
                $rows[] = $result;
                continue;
            }

            $result = array_merge($result, [
                'student_id' => $student->id,
                'matched_nisn' => $student->nisn,
                'matched_name' => $student->nama_lengkap,
                'class_name' => $student->class_name,
                'current_nis' => $student->nis_lokal,
                'score' => round((float) $score, 2),
                'match_method' => $method,
                'action' => blank($student->nis_lokal) ? 'Isi' : ($student->nis_lokal === $nis ? 'Tetap' : 'Perbarui'),
                'status' => 'ready',
                'message' => $method === 'Nama pintar'
                    ? 'Cocok melalui deteksi nama pintar; pastikan hasil preview benar.'
                    : 'Siap disimpan.',
            ]);
            $rows[] = $result;
        }

        $payload = [
            'nsm' => $nsm,
            'file_name' => $file->getClientOriginalName(),
            'rows' => $rows,
            'ready' => collect($rows)->where('status', 'ready')->count(),
            'errors' => collect($rows)->where('status', 'error')->count(),
        ];
        $payload['token'] = $this->storePreview('import', $payload);

        return $payload;
    }

    public function confirmImport(string $token): array
    {
        $preview = $this->getPreview('import', $token);
        $nsm = $this->validatedNsm(AppSetting::getInstance());
        if ($preview['nsm'] !== $nsm) {
            throw new RuntimeException('NSM berubah setelah preview dibuat. Upload ulang file.');
        }

        $readyRows = collect($preview['rows'])->where('status', 'ready')->values();
        $updated = DB::transaction(function () use ($readyRows, $preview) {
            $eligibleIds = $this->eligibleImportStudents()->pluck('id')->all();
            $updated = 0;

            foreach ($readyRows as $row) {
                if (! in_array($row['student_id'], $eligibleIds, true)) {
                    throw new RuntimeException("{$row['matched_name']} tidak lagi aktif di tingkat 11/12.");
                }

                $student = Siswa::query()->lockForUpdate()->findOrFail($row['student_id']);
                $conflict = Siswa::query()
                    ->where('nis_lokal', $row['input_nis'])
                    ->where('id', '!=', $student->id)
                    ->exists();
                if ($conflict) {
                    throw new RuntimeException("NIS Lokal {$row['input_nis']} sudah digunakan siswa lain.");
                }

                if ($student->nis_lokal !== $row['input_nis']) {
                    $student->forceFill([
                        'nis_lokal' => $row['input_nis'],
                        'nis_lokal_tahun' => 2000 + (int) substr($row['input_nis'], 12, 2),
                        'nis_lokal_urutan' => (int) substr($row['input_nis'], -4),
                        'nis_lokal_generated_at' => now(),
                        'nis_lokal_generated_by' => auth()->id(),
                    ])->save();
                    $updated++;
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'nama_file' => $preview['file_name'],
                    'baris_siap' => $readyRows->count(),
                    'jumlah_diperbarui' => $updated,
                ])
                ->log('Impor NIS Lokal siswa tingkat 11/12');

            return $updated;
        });

        Cache::forget($this->cacheKey('import', $token));

        return ['updated' => $updated, 'ready' => $readyRows->count()];
    }

    public function formatNis(string $nsm, int $year, int $sequence): string
    {
        return $nsm . substr((string) $year, -2) . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function nameSimilarityScore(string $input, string $candidate): float
    {
        return $this->nameScore($input, $candidate);
    }

    public function classOrderNumber(?string $name): int
    {
        return $this->classNumber($name);
    }

    private function eligibleImportStudents(): Collection
    {
        $activeYear = TahunPelajaran::query()->active()->firstOrFail();

        return Siswa::query()
            ->join('siswa_kelas as sk', 'sk.siswa_id', '=', 'siswa.id')
            ->join('kelas as k', 'k.id', '=', 'sk.kelas_id')
            ->where('sk.tahun_pelajaran_id', $activeYear->id)
            ->where('sk.status', 'aktif')
            ->whereNull('sk.deleted_at')
            ->whereIn('sk.tingkat', [11, 12])
            ->select('siswa.*', 'k.nama_kelas as class_name')
            ->get()
            ->unique('id')
            ->values();
    }

    private function orderedClassRecords(Kelas $class): Collection
    {
        return SiswaKelas::query()
            ->with('siswa')
            ->where('kelas_id', $class->id)
            ->where('tahun_pelajaran_id', $class->tahun_pelajaran_id)
            ->where('status', 'aktif')
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (SiswaKelas $record) => $record->siswa)
            ->sortBy(fn (SiswaKelas $record) => mb_strtoupper(trim($record->siswa->nama_lengkap)), SORT_NATURAL)
            ->values();
    }

    private function compareClasses(Kelas $left, Kelas $right): int
    {
        $leftOrder = $this->classNumber($left->nama_kelas);
        $rightOrder = $this->classNumber($right->nama_kelas);

        return $leftOrder === $rightOrder
            ? strnatcasecmp((string) $left->nama_kelas, (string) $right->nama_kelas)
            : $leftOrder <=> $rightOrder;
    }

    private function classNumber(?string $name): int
    {
        preg_match_all('/\d+/', (string) $name, $matches);

        return (int) (collect($matches[0] ?? [])->last() ?? PHP_INT_MAX);
    }

    private function validatedNsm(AppSetting $setting): string
    {
        $nsm = preg_replace('/\D+/', '', (string) $setting->nsm);
        if (! preg_match('/^\d{12}$/', $nsm)) {
            throw new RuntimeException('NSM madrasah harus diisi 12 digit pada Pengaturan Aplikasi.');
        }

        return $nsm;
    }

    private function normalizeHeader(mixed $header): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $header)));
    }

    private function nameScore(string $input, string $candidate): float
    {
        $left = $this->nameTokens($input);
        $right = $this->nameTokens($candidate);
        if ($left === [] || $right === []) {
            return 0;
        }

        $leftText = implode(' ', $left);
        $rightText = implode(' ', $right);
        if ($leftText === $rightText) {
            return 100;
        }

        similar_text($leftText, $rightText, $characterScore);
        $shorter = count($left) <= count($right) ? $left : $right;
        $longer = count($left) <= count($right) ? $right : $left;
        $matched = 0;

        foreach ($shorter as $token) {
            $found = collect($longer)->contains(function ($candidateToken) use ($token) {
                return $token === $candidateToken
                    || (mb_strlen($token) === 1 && str_starts_with($candidateToken, $token))
                    || (mb_strlen($candidateToken) === 1 && str_starts_with($token, $candidateToken));
            });
            $matched += $found ? 1 : 0;
        }

        $coverage = $matched / max(1, count($shorter)) * 100;
        $firstNameBonus = (
            $left[0] === $right[0]
            || str_starts_with($left[0], $right[0])
            || str_starts_with($right[0], $left[0])
        ) ? 5 : 0;

        return min(100, round(($characterScore * .45) + ($coverage * .55) + $firstNameBonus, 2));
    }

    private function nameTokens(string $name): array
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/\b(muhammad|mohammad|muhamad)\b/u', 'muh', $name);
        $name = preg_replace('/[^\pL\pN]+/u', ' ', $name);

        return array_values(array_filter(explode(' ', trim(preg_replace('/\s+/u', ' ', $name)))));
    }

    private function storePreview(string $type, array $payload): string
    {
        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($type, $token), [
            'user_id' => auth()->id(),
            'payload' => $payload,
        ], self::PREVIEW_TTL_SECONDS);

        return $token;
    }

    private function getPreview(string $type, string $token): array
    {
        $cached = Cache::get($this->cacheKey($type, $token));
        if (! $cached || $cached['user_id'] !== auth()->id()) {
            throw new RuntimeException('Preview sudah kedaluwarsa atau bukan milik pengguna ini.');
        }

        return $cached['payload'];
    }

    private function cacheKey(string $type, string $token): string
    {
        return "nis-lokal:{$type}:{$token}";
    }
}
