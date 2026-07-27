<?php

namespace App\Services;

use App\Helpers\StorageHelper;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class StudentPhotoArchiveService
{
    public const BATCH_SIZE = 12;

    public function activeYear(): ?TahunPelajaran
    {
        return TahunPelajaran::query()->active()->orderByDesc('tahun_mulai')->first();
    }

    public function classesForLevel(int $level): Collection
    {
        $activeYear = $this->activeYear();

        if (! $activeYear) {
            return collect();
        }

        return Kelas::query()
            ->where('tahun_pelajaran_id', $activeYear->id)
            ->where('tingkat', $level)
            ->where('is_active', true)
            ->withCount([
                'siswas as active_students_count' => fn ($query) => $query
                    ->where('siswa_kelas.status', 'aktif')
                    ->where('siswa_kelas.tahun_pelajaran_id', $activeYear->id),
            ])
            ->orderBy('nama_kelas')
            ->get();
    }

    public function preview(int $level, array $classIds): array
    {
        [$activeYear, $classes] = $this->selectedClasses($level, $classIds);
        $students = $this->studentsFromClasses($classes, $activeYear->id);
        $available = $students->filter(fn ($row) => $row['has_photo']);

        return [
            'year' => $activeYear->nama,
            'level' => $level,
            'classes' => $classes->map(function (Kelas $class) use ($students) {
                $classStudents = $students->where('class_id', $class->id);

                return [
                    'id' => $class->id,
                    'name' => $class->nama_lengkap,
                    'students' => $classStudents->count(),
                    'photos' => $classStudents->where('has_photo', true)->count(),
                    'missing' => $classStudents->where('has_photo', false)->count(),
                ];
            })->values(),
            'summary' => [
                'classes' => $classes->count(),
                'students' => $students->count(),
                'photos' => $available->count(),
                'missing' => $students->where('has_photo', false)->count(),
            ],
            'students' => $students->take(36)->map(fn ($row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'nisn' => $row['nisn'],
                'class_name' => $row['class_name'],
                'has_photo' => $row['has_photo'],
                'photo_url' => $row['photo_url'],
            ])->values(),
            'preview_limited' => $students->count() > 36,
        ];
    }

    public function start(string $userId, int $level, array $classIds): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP belum tersedia pada server.');
        }

        [$activeYear, $classes] = $this->selectedClasses($level, $classIds);
        $students = $this->studentsFromClasses($classes, $activeYear->id);
        $entries = $students
            ->filter(fn ($row) => $row['has_photo'])
            ->map(fn ($row) => [
                'student_id' => $row['id'],
                'source' => $row['photo_path'],
                'archive_name' => $this->archiveName($row),
                'student_name' => $row['name'],
                'class_name' => $row['class_name'],
            ])
            ->values();

        if ($entries->isEmpty()) {
            throw new RuntimeException('Tidak ada file foto asli yang dapat dimasukkan ke ZIP.');
        }

        $this->purgeExpired($userId);

        $token = (string) Str::uuid();
        $directory = $this->directory($userId, $token);
        File::ensureDirectoryExists($directory, 0750, true);

        $state = [
            'token' => $token,
            'user_id' => $userId,
            'level' => $level,
            'year_id' => $activeYear->id,
            'year' => $activeYear->nama,
            'class_ids' => $classes->pluck('id')->values()->all(),
            'class_names' => $classes->pluck('nama_lengkap')->values()->all(),
            'total_students' => $students->count(),
            'total' => $entries->count(),
            'missing' => $students->where('has_photo', false)->count(),
            'processed' => 0,
            'failed' => 0,
            'status' => 'processing',
            'filename' => $this->downloadFilename($level, $classes, $activeYear),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->writeJson($this->manifestPath($userId, $token), $entries->all());
        $this->writeState($userId, $token, $state);

        return $this->publicState($state);
    }

    public function process(string $userId, string $token): array
    {
        $this->assertToken($token);
        $directory = $this->directory($userId, $token);
        $lockHandle = fopen($directory.DIRECTORY_SEPARATOR.'process.lock', 'c+');

        if (! $lockHandle || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }
            throw new RuntimeException('Proses sedang berjalan. Tunggu sebentar.');
        }

        try {
            $state = $this->readState($userId, $token);

            if ($state['status'] === 'completed') {
                return $this->publicState($state);
            }

            $entries = $this->readJson($this->manifestPath($userId, $token));
            $offset = (int) $state['processed'];
            $batch = array_slice($entries, $offset, self::BATCH_SIZE);
            $zip = new ZipArchive;
            $flags = File::exists($this->archivePath($userId, $token))
                ? 0
                : ZipArchive::CREATE;

            if ($zip->open($this->archivePath($userId, $token), $flags) !== true) {
                throw new RuntimeException('File ZIP tidak dapat dibuka untuk ditulis.');
            }

            $lastItem = null;
            foreach ($batch as $entry) {
                $lastItem = $entry;
                if (! File::isFile($entry['source']) || ! $zip->addFile($entry['source'], $entry['archive_name'])) {
                    $state['failed']++;
                } elseif (method_exists($zip, 'setCompressionName')) {
                    $zip->setCompressionName($entry['archive_name'], ZipArchive::CM_STORE);
                }
                $state['processed']++;
            }

            if (! $zip->close()) {
                throw new RuntimeException('File ZIP gagal disimpan.');
            }

            if ($state['processed'] >= $state['total']) {
                $state['status'] = 'completed';
            }

            $state['updated_at'] = now()->toIso8601String();
            $this->writeState($userId, $token, $state);

            return $this->publicState($state) + [
                'current_student' => $lastItem['student_name'] ?? null,
                'current_class' => $lastItem['class_name'] ?? null,
            ];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public function download(string $userId, string $token): array
    {
        $this->assertToken($token);
        $state = $this->readState($userId, $token);
        $archive = $this->archivePath($userId, $token);

        if ($state['status'] !== 'completed' || ! File::isFile($archive)) {
            throw new RuntimeException('ZIP belum selesai diproses.');
        }

        return ['path' => $archive, 'state' => $state];
    }

    private function selectedClasses(int $level, array $classIds): array
    {
        $activeYear = $this->activeYear();
        if (! $activeYear) {
            throw new RuntimeException('Tahun pelajaran aktif belum tersedia.');
        }

        $ids = collect($classIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            throw new RuntimeException('Pilih minimal satu kelas.');
        }

        $classes = Kelas::query()
            ->with('jurusan')
            ->whereIn('id', $ids)
            ->where('tahun_pelajaran_id', $activeYear->id)
            ->where('tingkat', $level)
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();

        if ($classes->count() !== $ids->count()) {
            throw new RuntimeException('Sebagian kelas tidak valid atau bukan kelas tahun aktif.');
        }

        return [$activeYear, $classes];
    }

    private function studentsFromClasses(Collection $classes, string $activeYearId): Collection
    {
        $rows = collect();

        foreach ($classes as $class) {
            $students = $class->siswas()
                ->wherePivot('status', 'aktif')
                ->wherePivot('tahun_pelajaran_id', $activeYearId)
                ->select(['siswa.id', 'siswa.nama_lengkap', 'siswa.nisn', 'siswa.foto_profile'])
                ->orderBy('siswa.nama_lengkap')
                ->get();

            foreach ($students as $student) {
                $normalized = StorageHelper::normalizePublicPath($student->foto_profile);
                $path = StorageHelper::publicFilePath($normalized);
                $hasPhoto = $normalized
                    && ! filter_var($normalized, FILTER_VALIDATE_URL)
                    && $path
                    && File::isFile($path);

                $rows->push([
                    'id' => $student->id,
                    'name' => $student->nama_lengkap,
                    'nisn' => $student->nisn ?: '-',
                    'class_id' => $class->id,
                    'class_name' => $class->nama_lengkap,
                    'has_photo' => $hasPhoto,
                    'photo_path' => $hasPhoto ? $path : null,
                    'photo_url' => $hasPhoto ? StorageHelper::publicFileUrl($normalized) : null,
                ]);
            }
        }

        return $rows->unique('id')->values();
    }

    private function archiveName(array $row): string
    {
        $extension = strtolower(pathinfo($row['photo_path'], PATHINFO_EXTENSION)) ?: 'jpg';
        $class = $this->safeName($row['class_name']);
        $identity = $row['nisn'] !== '-' ? $row['nisn'] : $row['id'];
        $student = $this->safeName($identity.' - '.$row['name']);

        return "{$class}/{$student}.{$extension}";
    }

    private function safeName(string $value): string
    {
        $value = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]/u', '-', trim($value));
        $value = preg_replace('/\s+/u', ' ', $value);

        return Str::limit($value ?: 'Tanpa Nama', 120, '');
    }

    private function downloadFilename(int $level, Collection $classes, TahunPelajaran $year): string
    {
        $scope = $classes->count() === 1
            ? $classes->first()->nama_lengkap
            : 'Tingkat-'.$level;

        return 'Foto-Siswa-'.Str::slug($scope).'-'.Str::slug($year->nama).'-'.now()->format('Ymd-His').'.zip';
    }

    private function publicState(array $state): array
    {
        $total = max(1, (int) $state['total']);

        return [
            'token' => $state['token'],
            'status' => $state['status'],
            'total' => (int) $state['total'],
            'processed' => (int) $state['processed'],
            'failed' => (int) $state['failed'],
            'missing' => (int) $state['missing'],
            'percentage' => min(100, (int) floor(((int) $state['processed'] / $total) * 100)),
            'filename' => $state['filename'],
        ];
    }

    private function baseDirectory(string $userId): string
    {
        return storage_path('app/private/photo-exports/'.$userId);
    }

    private function directory(string $userId, string $token): string
    {
        return $this->baseDirectory($userId).DIRECTORY_SEPARATOR.$token;
    }

    private function manifestPath(string $userId, string $token): string
    {
        return $this->directory($userId, $token).DIRECTORY_SEPARATOR.'manifest.json';
    }

    private function statePath(string $userId, string $token): string
    {
        return $this->directory($userId, $token).DIRECTORY_SEPARATOR.'state.json';
    }

    private function archivePath(string $userId, string $token): string
    {
        return $this->directory($userId, $token).DIRECTORY_SEPARATOR.'photos.zip';
    }

    private function readState(string $userId, string $token): array
    {
        $path = $this->statePath($userId, $token);
        if (! File::isFile($path)) {
            throw new RuntimeException('Proses ZIP tidak ditemukan atau sudah kedaluwarsa.');
        }

        return $this->readJson($path);
    }

    private function writeState(string $userId, string $token, array $state): void
    {
        $this->writeJson($this->statePath($userId, $token), $state);
    }

    private function readJson(string $path): array
    {
        $data = json_decode((string) File::get($path), true);
        if (! is_array($data)) {
            throw new RuntimeException('Data proses ZIP tidak valid.');
        }

        return $data;
    }

    private function writeJson(string $path, array $data): void
    {
        $temporary = $path.'.tmp';
        File::put($temporary, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), true);
        File::move($temporary, $path);
    }

    private function assertToken(string $token): void
    {
        if (! Str::isUuid($token)) {
            throw new RuntimeException('Token proses ZIP tidak valid.');
        }
    }

    private function purgeExpired(string $userId): void
    {
        $base = $this->baseDirectory($userId);
        if (! File::isDirectory($base)) {
            return;
        }

        foreach (File::directories($base) as $directory) {
            if (File::lastModified($directory) < now()->subHours(6)->timestamp) {
                File::deleteDirectory($directory);
            }
        }
    }
}
