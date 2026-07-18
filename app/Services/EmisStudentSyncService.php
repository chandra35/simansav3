<?php

namespace App\Services;

use App\Models\EmisStudentSnapshot;
use App\Models\EmisStudentSync;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmisStudentSyncService
{
    private const API_URL = 'https://api-emis.kemenag.go.id/v1';

    private const PER_PAGE = 200;

    public function __construct(
        private readonly EmisInstitutionTokenService $tokenService,
        private readonly SmartStudentComparator $comparator,
    ) {}

    public function sync(User $user): EmisStudentSync
    {
        $lock = Cache::lock('emis-student-comparison-sync', 300);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi EMIS lain masih berjalan. Silakan tunggu sampai selesai.');
        }

        $sync = null;

        try {
            $tokenStatus = $this->tokenService->status();
            if (! $tokenStatus['usable']) {
                throw new RuntimeException($tokenStatus['message']);
            }

            $sync = EmisStudentSync::create([
                'institution_id' => $tokenStatus['institution_id'],
                'status' => 'running',
                'synced_by' => $user->id,
                'started_at' => now(),
            ]);

            $rows = $this->fetchAllStudents($tokenStatus, $sync);
            if ($rows === []) {
                throw new RuntimeException('API EMIS mengembalikan daftar siswa kosong. Snapshot lama tidak diganti untuk mencegah kehilangan hasil sinkronisasi.');
            }
            $snapshots = $this->buildSnapshots($rows, $sync);

            DB::transaction(function () use ($snapshots) {
                EmisStudentSnapshot::query()->delete();
                foreach (array_chunk($snapshots, 250) as $chunk) {
                    DB::table('emis_student_snapshots')->insert($chunk);
                }
            });

            $matched = collect($snapshots)->whereNotNull('siswa_id');
            $different = $matched->whereIn('comparison_status', ['different', 'similar'])->count();

            $sync->update([
                'status' => 'completed',
                'total_students' => count($snapshots),
                'matched_students' => $matched->count(),
                'different_students' => $different,
                'finished_at' => now(),
            ]);

            Log::info('Sinkronisasi pembanding EMIS selesai', [
                'sync_id' => $sync->id,
                'institution_id' => $sync->institution_id,
                'total_students' => count($snapshots),
                'matched_students' => $matched->count(),
                'different_students' => $different,
            ]);

            return $sync->fresh();
        } catch (Throwable $exception) {
            if ($sync) {
                $sync->update([
                    'status' => 'failed',
                    'error_message' => Str::limit($this->friendlyError($exception), 2000),
                    'finished_at' => now(),
                ]);
            }

            Log::warning('Sinkronisasi pembanding EMIS gagal', [
                'sync_id' => $sync?->id,
                'error_type' => $exception::class,
                'message' => $this->friendlyError($exception),
            ]);

            throw new RuntimeException($this->friendlyError($exception), previous: $exception);
        } finally {
            $lock->release();
        }
    }

    private function fetchAllStudents(array $tokenStatus, EmisStudentSync $sync): array
    {
        $page = 1;
        $lastPage = 1;
        $allRows = [];

        do {
            $response = Http::timeout(45)
                ->withToken($tokenStatus['token'])
                ->acceptJson()
                ->get(self::API_URL."/students/institution/{$tokenStatus['institution_id']}/student/list", [
                    'student_status_id' => 1,
                    'page' => $page,
                    'per_page' => self::PER_PAGE,
                ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Token EMIS Lembaga ditolak atau sudah kedaluwarsa. Perbarui token sebelum mencoba lagi.');
            }

            if (! $response->successful()) {
                throw new RuntimeException("API EMIS gagal pada halaman {$page} (HTTP {$response->status()}).");
            }

            $body = $response->json();
            if (! is_array($body) || ($body['success'] ?? false) !== true || ! is_array($body['results'] ?? null)) {
                throw new RuntimeException("Struktur respons API EMIS tidak dikenali pada halaman {$page}.");
            }

            $pageRows = $body['results'];
            $allRows = array_merge($allRows, $pageRows);
            $pagination = data_get($body, 'metadata.pagination', []);
            $lastPage = max(1, (int) ($pagination['last_page'] ?? 1));

            $sync->update([
                'total_pages' => $lastPage,
                'processed_pages' => $page,
                'total_students' => count($allRows),
            ]);

            $page++;
        } while ($page <= $lastPage);

        return $allRows;
    }

    private function buildSnapshots(array $emisRows, EmisStudentSync $sync): array
    {
        $students = Siswa::query()
            ->with('kelasSaatIni:id,nama_kelas,tingkat')
            ->whereNotNull('nisn')
            ->where('status_siswa', 'aktif')
            ->get(['id', 'nisn', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'kelas_saat_ini_id'])
            ->keyBy(fn (Siswa $siswa) => trim((string) $siswa->nisn));

        $now = now();
        $snapshots = [];

        foreach ($emisRows as $row) {
            if (! is_array($row) || empty($row['id'])) {
                continue;
            }

            $nisn = trim((string) ($row['nisn'] ?? '')) ?: null;
            /** @var Siswa|null $siswa */
            $siswa = $nisn ? $students->get($nisn) : null;
            $activity = is_array($row['learning_activity'] ?? null) ? $row['learning_activity'] : [];
            $studyGroup = is_array($activity['study_group'] ?? null) ? $activity['study_group'] : [];
            $academicYear = is_array($activity['academic_year'] ?? null) ? $activity['academic_year'] : [];
            $major = is_array($activity['m_major'] ?? null) ? $activity['m_major'] : [];
            $status = is_array($activity['student_status'] ?? null) ? $activity['student_status'] : [];
            $statusDescription = is_array($activity['status_description'] ?? null) ? $activity['status_description'] : [];

            $emisData = [
                'nama_lengkap' => $row['full_name'] ?? null,
                'nisn' => $nisn,
                'tempat_lahir' => $row['birth_place'] ?? null,
                'tanggal_lahir' => $row['birth_date'] ?? null,
                'jenis_kelamin' => data_get($row, 'm_gender.name') ?? $row['gender'] ?? null,
                'kelas' => $row['study_group_name'] ?? $studyGroup['name'] ?? null,
            ];

            $comparison = $siswa
                ? $this->comparator->compare([
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'tempat_lahir' => $siswa->tempat_lahir,
                    'tanggal_lahir' => $siswa->tanggal_lahir?->format('Y-m-d'),
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'kelas' => $siswa->kelasSaatIni?->nama_kelas,
                ], $emisData)
                : ['status' => 'only_emis', 'name_similarity' => null, 'details' => [], 'different_fields' => []];

            $snapshots[] = [
                'id' => (string) Str::uuid(),
                'sync_id' => $sync->id,
                'siswa_id' => $siswa?->id,
                'emis_student_id' => (int) $row['id'],
                'learning_activity_id' => isset($row['learning_activity_id']) ? (int) $row['learning_activity_id'] : null,
                'nisn' => $nisn,
                'full_name' => $row['full_name'] ?? null,
                'birth_place' => $row['birth_place'] ?? null,
                'birth_date' => $this->normalizeDate($row['birth_date'] ?? null),
                'gender' => $emisData['jenis_kelamin'],
                'student_status_id' => isset($row['student_status_id']) ? (int) $row['student_status_id'] : null,
                'student_status' => $status['name'] ?? null,
                'status_description' => $statusDescription['name'] ?? null,
                'dukcapil_verification_status_id' => isset($row['dukcapil_verification_status_id']) ? (int) $row['dukcapil_verification_status_id'] : null,
                'valid_nisn' => isset($row['valid_nisn']) ? (bool) $row['valid_nisn'] : null,
                'level_name' => $row['level_name'] ?? data_get($activity, 'm_level.name'),
                'study_group_name' => $emisData['kelas'],
                'major_name' => $major['name'] ?? null,
                'academic_year' => $academicYear['name'] ?? null,
                'academic_year_status' => $row['academic_year_status'] ?? null,
                'comparison_status' => $comparison['status'],
                'name_similarity' => $comparison['name_similarity'],
                'comparison_details' => json_encode($comparison['details'], JSON_UNESCAPED_UNICODE),
                'synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $snapshots;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function friendlyError(Throwable $exception): string
    {
        if ($exception instanceof ConnectionException) {
            return 'Koneksi ke API EMIS timeout. Snapshot lama tetap aman; silakan coba lagi beberapa saat lagi.';
        }

        return $exception->getMessage() ?: 'Sinkronisasi EMIS gagal tanpa pesan kesalahan.';
    }
}
