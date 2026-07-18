<?php

namespace App\Services;

use App\Models\EmisStudentSnapshot;
use App\Models\EmisStudentSync;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
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

    // Endpoint EMIS lembaga terbukti lebih stabil dalam satu request besar.
    // Pagination loop tetap dipertahankan jika batas API berubah di kemudian hari.
    private const PER_PAGE = 1300;

    public function __construct(
        private readonly EmisInstitutionTokenService $tokenService,
        private readonly SmartStudentComparator $comparator,
    ) {}

    public function sync(User $user): EmisStudentSync
    {
        return $this->process($this->start($user));
    }

    public function syncStudent(Siswa $siswa, User $user): EmisStudentSnapshot
    {
        $activeYear = TahunPelajaran::query()->active()->first();
        if (! $activeYear) {
            throw new RuntimeException('Tahun pelajaran aktif belum ditetapkan. Sinkronisasi EMIS tidak dapat disimpan tanpa periode yang jelas.');
        }

        $latestCompletedSync = EmisStudentSync::query()
            ->where('tahun_pelajaran_id', $activeYear->id)
            ->where('status', 'completed')
            ->latest('finished_at')
            ->first();
        if (! $latestCompletedSync) {
            throw new RuntimeException("Snapshot awal untuk {$activeYear->nama} belum tersedia. Jalankan Sinkronkan Data terlebih dahulu sebelum memperbarui satu siswa.");
        }

        $nisn = trim((string) $siswa->nisn);
        if ($nisn === '') {
            throw new RuntimeException('NISN siswa kosong sehingga pencarian ke EMIS tidak dapat dilakukan.');
        }

        $tokenStatus = $this->tokenService->status();
        if (! $tokenStatus['usable']) {
            throw new RuntimeException($tokenStatus['message']);
        }

        $lock = Cache::lock('emis-student-comparison-sync', 90);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi EMIS lain masih berjalan. Silakan tunggu sampai selesai.');
        }

        try {
            $response = Http::timeout(60)
                ->withToken($tokenStatus['token'])
                ->acceptJson()
                ->get(self::API_URL."/students/institution/{$tokenStatus['institution_id']}/student/list", [
                    'q' => $nisn,
                    'student_status_id' => 1,
                    'page' => 1,
                    'per_page' => 10,
                ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Token EMIS Lembaga ditolak atau sudah kedaluwarsa. Perbarui token sebelum mencoba lagi.');
            }

            if (! $response->successful()) {
                throw new RuntimeException("API EMIS gagal merespons (HTTP {$response->status()}). Snapshot lama tetap aman.");
            }

            $body = $response->json();
            if (! is_array($body) || ($body['success'] ?? false) !== true || ! is_array($body['results'] ?? null)) {
                throw new RuntimeException('Struktur respons API EMIS tidak dikenali. Snapshot lama tetap aman.');
            }

            $row = collect($body['results'])->first(
                fn ($candidate) => is_array($candidate)
                    && trim((string) ($candidate['nisn'] ?? '')) === $nisn
            );

            if (! $row || empty($row['id'])) {
                throw new RuntimeException("Siswa dengan NISN {$nisn} tidak ditemukan sebagai siswa aktif di EMIS. Snapshot lama tidak diubah.");
            }

            $siswa->loadMissing('kelasSaatIni:id,nama_kelas,tingkat');
            $emisStudentId = (int) $row['id'];
            $target = EmisStudentSnapshot::query()
                ->where('tahun_pelajaran_id', $activeYear->id)
                ->where('emis_student_id', $emisStudentId)
                ->first()
                ?? EmisStudentSnapshot::query()
                    ->where('tahun_pelajaran_id', $activeYear->id)
                    ->where('siswa_id', $siswa->id)
                    ->latest('synced_at')
                    ->first();
            $attributes = $this->buildSnapshot($row, $siswa, $latestCompletedSync->id, $activeYear->id, now());
            $attributes['comparison_details'] = json_decode($attributes['comparison_details'], true) ?: [];
            $attributes['simansa_data'] = json_decode((string) $attributes['simansa_data'], true);

            $snapshot = DB::transaction(function () use ($attributes, $target, $siswa, $nisn, $activeYear) {
                $duplicateQuery = EmisStudentSnapshot::query()
                    ->where('tahun_pelajaran_id', $activeYear->id)
                    ->where(fn ($query) => $query->where('siswa_id', $siswa->id)->orWhere('nisn', $nisn));
                if ($target) {
                    $duplicateQuery->where($target->getKeyName(), '<>', $target->getKey());
                }
                $duplicateQuery->delete();

                if ($target) {
                    $target->fill(collect($attributes)->except(['id', 'created_at', 'updated_at'])->all())->save();

                    return $target->fresh();
                }

                return EmisStudentSnapshot::create(
                    collect($attributes)->except(['created_at', 'updated_at'])->all()
                );
            });

            Log::info('Sinkronisasi pembanding EMIS per siswa selesai', [
                'siswa_id' => $siswa->id,
                'snapshot_id' => $snapshot->id,
                'synced_by' => $user->id,
                'comparison_status' => $snapshot->comparison_status,
            ]);

            return $snapshot;
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Koneksi ke API EMIS timeout. Snapshot lama tetap aman; silakan coba lagi.', previous: $exception);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::warning('Sinkronisasi pembanding EMIS per siswa gagal', [
                'siswa_id' => $siswa->id,
                'synced_by' => $user->id,
                'error_type' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Sinkronisasi siswa gagal diproses. Snapshot lama tetap aman; silakan coba lagi.', previous: $exception);
        } finally {
            $lock->release();
        }
    }

    public function start(User $user): EmisStudentSync
    {
        $activeYear = TahunPelajaran::query()->active()->first();
        if (! $activeYear) {
            throw new RuntimeException('Tahun pelajaran aktif belum ditetapkan. Tetapkan periode aktif sebelum sinkronisasi EMIS.');
        }

        $tokenStatus = $this->tokenService->status();
        if (! $tokenStatus['usable']) {
            throw new RuntimeException($tokenStatus['message']);
        }

        $lock = Cache::lock('emis-student-comparison-start', 10);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi EMIS lain masih berjalan. Pantau proses yang aktif sampai selesai.');
        }

        try {
            EmisStudentSync::query()
                ->whereIn('status', ['queued', 'running'])
                ->where('created_at', '<', now()->subMinutes(10))
                ->update([
                    'status' => 'failed',
                    'stage' => 'failed',
                    'progress_message' => 'Proses lama dihentikan karena melewati batas waktu.',
                    'error_message' => 'Proses sinkronisasi tidak selesai dalam 10 menit.',
                    'finished_at' => now(),
                ]);

            if (EmisStudentSync::query()->whereIn('status', ['queued', 'running'])->exists()) {
                throw new RuntimeException('Sinkronisasi EMIS lain masih berjalan. Pantau proses yang aktif sampai selesai.');
            }

            return EmisStudentSync::create([
                'tahun_pelajaran_id' => $activeYear->id,
                'institution_id' => $tokenStatus['institution_id'],
                'status' => 'queued',
                'progress_percent' => 5,
                'stage' => 'queued',
                'progress_message' => 'Permintaan sinkronisasi diterima.',
                'synced_by' => $user->id,
                'started_at' => now(),
            ]);
        } finally {
            $lock->release();
        }
    }

    public function process(EmisStudentSync $sync): EmisStudentSync
    {
        $lock = Cache::lock('emis-student-comparison-sync', 300);
        if (! $lock->get()) {
            throw new RuntimeException('Sinkronisasi EMIS lain masih berjalan. Silakan tunggu sampai selesai.');
        }

        try {
            if ($sync->status === 'completed') {
                return $sync;
            }

            if (! in_array($sync->status, ['queued', 'failed'], true)) {
                throw new RuntimeException('Status proses sinkronisasi tidak dapat dijalankan.');
            }

            if (! $sync->tahun_pelajaran_id) {
                throw new RuntimeException('Periode sinkronisasi tidak ditemukan. Buat permintaan sinkronisasi baru dari halaman Cek Data EMIS.');
            }

            $tokenStatus = $this->tokenService->status();
            if (! $tokenStatus['usable']) {
                throw new RuntimeException($tokenStatus['message']);
            }

            $sync->update([
                'institution_id' => $tokenStatus['institution_id'],
                'status' => 'running',
                'progress_percent' => 12,
                'stage' => 'connecting',
                'progress_message' => 'Menghubungkan ke API EMIS Lembaga...',
                'error_message' => null,
            ]);

            $rows = $this->fetchAllStudents($tokenStatus, $sync);
            if ($rows === []) {
                throw new RuntimeException('API EMIS mengembalikan daftar siswa kosong. Snapshot lama tidak diganti untuk mencegah kehilangan hasil sinkronisasi.');
            }

            $sync->update([
                'progress_percent' => 68,
                'stage' => 'comparing',
                'progress_message' => 'Mencocokkan NISN dan membandingkan setiap field...',
            ]);
            $snapshots = $this->buildSnapshots($rows, $sync);

            $sync->update([
                'progress_percent' => 86,
                'stage' => 'saving',
                'progress_message' => 'Menyimpan snapshot dan hasil pembandingan...',
            ]);
            DB::transaction(function () use ($snapshots, $sync) {
                EmisStudentSnapshot::query()
                    ->where('tahun_pelajaran_id', $sync->tahun_pelajaran_id)
                    ->delete();
                foreach (array_chunk($snapshots, 250) as $chunk) {
                    DB::table('emis_student_snapshots')->insert($chunk);
                }
            });

            $matched = collect($snapshots)->whereNotNull('siswa_id');
            $different = $matched->whereIn('comparison_status', ['different', 'similar'])->count();

            $sync->update([
                'status' => 'completed',
                'progress_percent' => 100,
                'stage' => 'completed',
                'progress_message' => 'Sinkronisasi selesai dan snapshot siap digunakan.',
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
            $sync->update([
                'status' => 'failed',
                'stage' => 'failed',
                'progress_message' => $this->friendlyError($exception),
                'error_message' => Str::limit($this->friendlyError($exception), 2000),
                'finished_at' => now(),
            ]);

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
            $response = Http::timeout(120)
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
                'progress_percent' => min(60, 15 + (int) round(($page / $lastPage) * 45)),
                'stage' => 'fetching',
                'progress_message' => "Mengambil halaman {$page} dari {$lastPage}: ".number_format(count($allRows)).' siswa diterima.',
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
            $snapshots[] = $this->buildSnapshot($row, $siswa, $sync->id, $sync->tahun_pelajaran_id, $now);
        }

        return $snapshots;
    }

    private function buildSnapshot(array $row, ?Siswa $siswa, ?string $syncId, string $tahunPelajaranId, mixed $now): array
    {
        $nisn = trim((string) ($row['nisn'] ?? '')) ?: null;
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
                'kelas' => $siswa->kelasSaatIni?->nama_kelas,
            ], $emisData)
            : ['status' => 'only_emis', 'name_similarity' => null, 'details' => [], 'different_fields' => []];

        $simansaData = $siswa ? [
            'nama_lengkap' => $siswa->nama_lengkap,
            'nisn' => $siswa->nisn,
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir?->format('Y-m-d'),
            'kelas' => $siswa->kelasSaatIni?->nama_kelas,
            'tingkat' => $siswa->kelasSaatIni?->tingkat,
        ] : null;

        return [
            'id' => (string) Str::uuid(),
            'sync_id' => $syncId,
            'tahun_pelajaran_id' => $tahunPelajaranId,
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
            'simansa_data' => $simansaData ? json_encode($simansaData, JSON_UNESCAPED_UNICODE) : null,
            'comparison_status' => $comparison['status'],
            'name_similarity' => $comparison['name_similarity'],
            'comparison_details' => json_encode($comparison['details'], JSON_UNESCAPED_UNICODE),
            'synced_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
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
