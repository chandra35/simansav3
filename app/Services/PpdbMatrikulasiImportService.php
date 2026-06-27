<?php

namespace App\Services;

use App\Helpers\StorageHelper;
use App\Models\MatrikulasiDokumen;
use App\Models\MatrikulasiKelompok;
use App\Models\MatrikulasiPeriode;
use App\Models\MatrikulasiPeserta;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PpdbMatrikulasiImportService
{
    public function periodeFor(TahunPelajaran $tahunPelajaran): MatrikulasiPeriode
    {
        return MatrikulasiPeriode::firstOrCreate(
            ['tahun_pelajaran_id' => $tahunPelajaran->id],
            [
                'nama' => 'Matrikulasi PPDB ' . $tahunPelajaran->nama,
                'status' => 'aktif',
            ]
        );
    }

    public function kelompokFor(?string $tahunPelajaranId = null): Collection
    {
        if (!$tahunPelajaranId) {
            return collect();
        }

        $tahun = TahunPelajaran::find($tahunPelajaranId);
        if (!$tahun) {
            return collect();
        }

        return $this->periodeFor($tahun)
            ->kelompoks()
            ->withCount('pesertas')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();
    }

    public function storeKelompok(string $tahunPelajaranId, array $payload): MatrikulasiKelompok
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);

        return MatrikulasiKelompok::create([
            'matrikulasi_periode_id' => $periode->id,
            'nama' => $payload['nama'],
            'kode' => $payload['kode'] ?? null,
            'kapasitas' => $payload['kapasitas'] ?? null,
            'status' => 'aktif',
        ]);
    }

    public function searchCandidates(?string $term, ?string $tahunPelajaranId = null, int $limit = 20, bool $includeAll = false): Collection
    {
        $tahun = $tahunPelajaranId ? TahunPelajaran::find($tahunPelajaranId) : null;
        $rows = $this->fetchPpdbCandidates([
            'q' => $term,
            'limit' => $limit,
            'scope' => $includeAll ? 'all' : 'eligible',
        ], $tahun, false);

        return $rows->map(fn ($row) => $this->decorateCandidate($row, $tahunPelajaranId));
    }

    public function preview(array $calonSiswaIds, ?string $tahunPelajaranId = null, bool $includeDocuments = false, bool $includeAll = false): Collection
    {
        if (empty($calonSiswaIds)) {
            return collect();
        }

        $tahun = $tahunPelajaranId ? TahunPelajaran::find($tahunPelajaranId) : null;
        $rows = collect($calonSiswaIds)
            ->filter()
            ->unique()
            ->chunk(100)
            ->flatMap(function (Collection $ids) use ($tahun, $includeDocuments, $includeAll) {
                return $this->fetchPpdbCandidates([
                    'ids' => $ids->implode(','),
                    'limit' => $ids->count(),
                    'scope' => $includeAll ? 'all' : 'eligible',
                ], $tahun, $includeDocuments);
            });

        return $rows->map(fn ($row) => $this->decorateCandidate($row, $tahunPelajaranId));
    }

    public function previewAll(?string $tahunPelajaranId = null): Collection
    {
        $tahun = $tahunPelajaranId ? TahunPelajaran::find($tahunPelajaranId) : null;
        $rows = $this->fetchAllPpdbCandidates($tahun);

        return $rows->map(fn ($row) => $this->decorateCandidate($row, $tahunPelajaranId));
    }

    public function browseCandidates(?string $term, ?string $tahunPelajaranId = null, int $page = 1, int $perPage = 25): array
    {
        $tahun = $tahunPelajaranId ? TahunPelajaran::find($tahunPelajaranId) : null;
        $response = $this->fetchPpdbResponse([
            'q' => $term,
            'scope' => 'all',
            'page' => max(1, $page),
            'per_page' => max(1, min($perPage, 100)),
        ], $tahun, false);

        return [
            'data' => collect($response['data'])
                ->map(fn ($row) => $this->decorateCandidate(json_decode(json_encode($row), false), $tahunPelajaranId))
                ->values(),
            'meta' => $response['meta'],
        ];
    }

    public function import(array $calonSiswaIds, string $kelompokId, bool $includeDocuments, string $tahunPelajaranId, bool $allowUnpaid = false): array
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        $kelompok = MatrikulasiKelompok::where('matrikulasi_periode_id', $periode->id)->findOrFail($kelompokId);

        $candidates = $this->preview($calonSiswaIds, $tahun->id, true, $allowUnpaid);
        $results = [
            'success' => 0,
            'failed' => 0,
            'documents_copied' => 0,
            'items' => [],
        ];

        foreach ($candidates as $candidate) {
            try {
                DB::beginTransaction();

                $this->assertYearMatches($candidate, $tahun);
                $this->assertImportable($candidate);
                $this->assertPaymentAllowed($candidate, $allowUnpaid);

                $peserta = $this->upsertPeserta($candidate, $periode, $kelompok);
                $copied = $includeDocuments ? $this->syncDocuments($peserta, $candidate, $tahun) : 0;

                DB::commit();

                $results['success']++;
                $results['documents_copied'] += $copied;
                $results['items'][] = [
                    'status' => 'success',
                    'nisn' => $candidate->nisn,
                    'nama' => $candidate->nama_lengkap,
                    'message' => 'Berhasil masuk staging matrikulasi.',
                    'documents_copied' => $copied,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                $results['failed']++;
                $results['items'][] = [
                    'status' => 'failed',
                    'nisn' => $candidate->nisn ?? '-',
                    'nama' => $candidate->nama_lengkap ?? '-',
                    'message' => $e->getMessage(),
                    'documents_copied' => 0,
                ];
            }
        }

        return $results;
    }

    public function stats(?string $tahunPelajaranId = null): array
    {
        if (!$tahunPelajaranId) {
            return ['periode' => null, 'total' => 0, 'kelompok' => 0, 'dokumen' => 0];
        }

        $tahun = TahunPelajaran::find($tahunPelajaranId);
        if (!$tahun) {
            return ['periode' => null, 'total' => 0, 'kelompok' => 0, 'dokumen' => 0];
        }

        $periode = $this->periodeFor($tahun);

        return [
            'periode' => $periode,
            'total' => $periode->pesertas()->count(),
            'kelompok' => $periode->kelompoks()->count(),
            'dokumen' => MatrikulasiDokumen::whereHas('peserta', fn ($q) => $q->where('matrikulasi_periode_id', $periode->id))->count(),
        ];
    }

    private function fetchPpdbCandidates(array $params, ?TahunPelajaran $tahun, bool $includeDocuments): Collection
    {
        $baseUrl = rtrim((string) config('services.ppdb_sync.base_url'), '/');
        $token = (string) config('services.ppdb_sync.token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Konfigurasi API PPDB belum lengkap.');
        }

        if ($tahun) {
            $params['tahun_nama'] = $tahun->nama;
            $params['tahun_mulai'] = $tahun->tahun_mulai;
            $params['tahun_selesai'] = $tahun->tahun_selesai;
        }

        if ($includeDocuments) {
            $params['include_documents'] = 1;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout((int) config('services.ppdb_sync.timeout', 30))
            ->get($baseUrl . '/api/internal/simansa/pendaftar', $params);

        if (!$response->successful()) {
            throw new RuntimeException('API PPDB gagal: ' . ($response->json('message') ?: $response->body()));
        }

        return collect($response->json('data', []))
            ->map(fn ($row) => json_decode(json_encode($row), false));
    }

    private function fetchAllPpdbCandidates(?TahunPelajaran $tahun): Collection
    {
        $all = collect();
        $page = 1;
        $lastPage = 1;

        do {
            $response = $this->fetchPpdbResponse([
                'page' => $page,
                'per_page' => 200,
            ], $tahun, false);

            $all = $all->merge($response['data']);
            $lastPage = (int) ($response['meta']['last_page'] ?? $page);
            $page++;
        } while ($page <= $lastPage);

        return $all->map(fn ($row) => json_decode(json_encode($row), false));
    }

    private function fetchPpdbResponse(array $params, ?TahunPelajaran $tahun, bool $includeDocuments): array
    {
        $baseUrl = rtrim((string) config('services.ppdb_sync.base_url'), '/');
        $token = (string) config('services.ppdb_sync.token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Konfigurasi API PPDB belum lengkap.');
        }

        if ($tahun) {
            $params['tahun_nama'] = $tahun->nama;
            $params['tahun_mulai'] = $tahun->tahun_mulai;
            $params['tahun_selesai'] = $tahun->tahun_selesai;
        }

        if ($includeDocuments) {
            $params['include_documents'] = 1;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout((int) config('services.ppdb_sync.timeout', 30))
            ->get($baseUrl . '/api/internal/simansa/pendaftar', $params);

        if (!$response->successful()) {
            throw new RuntimeException('API PPDB gagal: ' . ($response->json('message') ?: $response->body()));
        }

        return [
            'data' => $response->json('data', []),
            'meta' => $response->json('meta', []),
        ];
    }

    private function decorateCandidate(object $row, ?string $tahunPelajaranId): object
    {
        $periode = null;
        if ($tahunPelajaranId && ($tahun = TahunPelajaran::find($tahunPelajaranId))) {
            $periode = $this->periodeFor($tahun);
        }

        $row->existing_matrikulasi = $periode
            ? MatrikulasiPeserta::where('matrikulasi_periode_id', $periode->id)
                ->where('ppdb_calon_siswa_id', $row->id)
                ->first()
            : null;

        $row->existing_siswa = Siswa::query()
            ->where('ppdb_id', $row->id)
            ->orWhere('nisn', $row->nisn)
            ->when($row->nik, fn ($q) => $q->orWhere('nik', $row->nik))
            ->first();

        $row->documents_count = (int) ($row->documents_count ?? 0);
        $row->documents = collect($row->documents ?? []);
        $row->is_lulus = (bool) ($row->is_lulus ?? false);
        $row->has_registrasi_komite = (bool) ($row->has_registrasi_komite ?? false);
        $row->import_status = $this->candidateStatus($row);
        $row->label = trim("{$row->nama_lengkap} - {$row->nisn} - {$row->nomor_tes}");

        return $row;
    }

    private function candidateStatus(object $candidate): string
    {
        if ($candidate->existing_siswa) {
            return 'sudah_jadi_siswa';
        }

        if ($candidate->existing_matrikulasi) {
            return 'sudah_matrikulasi';
        }

        return 'baru';
    }

    private function assertImportable(object $candidate): void
    {
        if ($candidate->import_status === 'sudah_jadi_siswa') {
            throw new RuntimeException('Pendaftar ini sudah menjadi siswa reguler SIMANSA.');
        }

        if (empty($candidate->nama_lengkap)) {
            throw new RuntimeException('Nama pendaftar kosong.');
        }
    }

    private function assertPaymentAllowed(object $candidate, bool $allowUnpaid): void
    {
        if (!$candidate->has_registrasi_komite && !$allowUnpaid) {
            throw new RuntimeException('Pendaftar belum melakukan registrasi komite. Konfirmasi khusus diperlukan.');
        }
    }

    private function assertYearMatches(object $candidate, TahunPelajaran $targetTahun): void
    {
        if (!$candidate->ppdb_tahun_nama && !$candidate->ppdb_tahun_mulai) {
            return;
        }

        $sameName = $candidate->ppdb_tahun_nama
            && $this->normalizeYearName($candidate->ppdb_tahun_nama) === $this->normalizeYearName($targetTahun->nama);

        $sameRange = (int) $candidate->ppdb_tahun_mulai === (int) $targetTahun->tahun_mulai
            && (int) $candidate->ppdb_tahun_selesai === (int) $targetTahun->tahun_selesai;

        if (!$sameName && !$sameRange) {
            throw new RuntimeException("Tahun ajaran PPDB {$candidate->ppdb_tahun_nama} tidak cocok dengan {$targetTahun->nama}.");
        }
    }

    private function upsertPeserta(object $candidate, MatrikulasiPeriode $periode, MatrikulasiKelompok $kelompok): MatrikulasiPeserta
    {
        $payload = [
            'matrikulasi_kelompok_id' => $kelompok->id,
            'ppdb_tahun_pelajaran_id' => $candidate->tahun_pelajaran_id,
            'nomor_registrasi' => $candidate->nomor_registrasi,
            'nomor_tes' => $candidate->nomor_tes,
            'nisn' => $candidate->nisn,
            'nik' => $candidate->nik,
            'nama_lengkap' => $candidate->nama_lengkap,
            'jenis_kelamin' => $candidate->jenis_kelamin,
            'jurusan_awal' => $candidate->jurusan_awal,
            'jurusan_final' => $candidate->jurusan_final,
            'data_siswa' => $this->objectToArray($candidate),
            'data_ortu' => isset($candidate->ortu) ? $this->objectToArray($candidate->ortu) : null,
            'data_ppdb' => [
                'tahun' => $candidate->ppdb_tahun_nama,
                'jalur' => $candidate->jalur_nama,
                'gelombang' => $candidate->gelombang_nama,
                'tanggal_kelulusan' => $candidate->tanggal_kelulusan,
                'tanggal_registrasi_komite' => $candidate->tanggal_registrasi_komite,
            ],
            'status' => 'matrikulasi',
            'imported_at' => now(),
        ];

        return MatrikulasiPeserta::updateOrCreate(
            [
                'matrikulasi_periode_id' => $periode->id,
                'ppdb_calon_siswa_id' => $candidate->id,
            ],
            $payload
        );
    }

    private function syncDocuments(MatrikulasiPeserta $peserta, object $candidate, TahunPelajaran $targetTahun): int
    {
        $count = 0;
        foreach (collect($candidate->documents ?? []) as $document) {
            if (MatrikulasiDokumen::where('matrikulasi_peserta_id', $peserta->id)->where('ppdb_calon_dokumen_id', $document->id)->exists()) {
                continue;
            }

            $content = $this->readPpdbDocumentContent($document);
            if ($content === null) {
                continue;
            }

            $disk = StorageHelper::getDokumenDisk();
            $extension = $this->extensionFor($document);
            $path = 'matrikulasi-ppdb/' . $targetTahun->tahun_mulai . '/' . $peserta->id . '/' . $document->id . '.' . $extension;
            Storage::disk($disk)->put($path, $content);

            MatrikulasiDokumen::create([
                'matrikulasi_peserta_id' => $peserta->id,
                'ppdb_calon_dokumen_id' => $document->id,
                'jenis_dokumen' => $document->jenis_dokumen,
                'nama_dokumen' => $document->nama_dokumen,
                'nama_file' => $document->nama_file ?: basename((string) $document->file_path),
                'file_path' => $path,
                'file_size' => strlen($content),
                'mime_type' => $document->mime_type ?: 'application/octet-stream',
                'storage_disk' => $disk,
                'ppdb_source_disk' => $document->storage_disk,
                'ppdb_source_url' => $document->download_url ?? $document->remote_file_url ?? null,
                'status_verifikasi' => $document->status_verifikasi ?: 'pending',
                'imported_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function readPpdbDocumentContent(object $document): ?string
    {
        $url = $document->download_url ?? null;
        if (!$url) {
            return null;
        }

        $response = Http::withToken((string) config('services.ppdb_sync.token'))
            ->timeout((int) config('services.ppdb_sync.timeout', 30))
            ->get($url);

        return $response->successful() ? $response->body() : null;
    }

    private function extensionFor(object $document): string
    {
        $name = (string) ($document->nama_file ?: $document->file_path);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext) {
            return preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
        }

        return match ($document->mime_type) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }

    private function objectToArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?: [];
    }

    private function normalizeYearName(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', (string) $value);
    }
}
