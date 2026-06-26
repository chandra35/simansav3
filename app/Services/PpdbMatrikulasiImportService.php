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

    public function searchCandidates(?string $term, ?string $tahunPelajaranId = null, int $limit = 20): Collection
    {
        return $this->baseCandidateQuery($tahunPelajaranId)
            ->when($term, function ($query) use ($term) {
                $like = '%' . str_replace(' ', '%', trim($term)) . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('cs.nama_lengkap', 'like', $like)
                        ->orWhere('cs.nisn', 'like', $like)
                        ->orWhere('cs.nomor_registrasi', 'like', $like)
                        ->orWhere('cs.nomor_tes', 'like', $like);
                });
            })
            ->orderBy('cs.nama_lengkap')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->decorateCandidate($row, $tahunPelajaranId));
    }

    public function preview(array $calonSiswaIds, ?string $tahunPelajaranId = null): Collection
    {
        if (empty($calonSiswaIds)) {
            return collect();
        }

        return $this->baseCandidateQuery($tahunPelajaranId)
            ->whereIn('cs.id', $calonSiswaIds)
            ->orderBy('cs.nama_lengkap')
            ->get()
            ->map(fn ($row) => $this->decorateCandidate($row, $tahunPelajaranId));
    }

    public function import(array $calonSiswaIds, string $kelompokId, bool $includeDocuments, string $tahunPelajaranId): array
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        $kelompok = MatrikulasiKelompok::where('matrikulasi_periode_id', $periode->id)->findOrFail($kelompokId);

        $candidates = $this->preview($calonSiswaIds, $tahun->id);
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

    private function baseCandidateQuery(?string $tahunPelajaranId = null)
    {
        $ppdbYear = null;
        if ($tahunPelajaranId) {
            $targetYear = TahunPelajaran::find($tahunPelajaranId);
            $ppdbYear = $targetYear ? $this->findPpdbYearFor($targetYear) : null;
        }

        return DB::connection('mysql_ppdb')
            ->table('calon_siswas as cs')
            ->join('kelulusan as k', 'k.calon_siswa_id', '=', 'cs.id')
            ->join('registrasis as r', function ($join) {
                $join->on('r.calon_siswa_id', '=', 'cs.id')
                    ->whereNull('r.deleted_at');
            })
            ->leftJoin('tahun_pelajarans as tp', 'tp.id', '=', 'cs.tahun_pelajaran_id')
            ->leftJoin('jalur_pendaftaran as jp', 'jp.id', '=', 'cs.jalur_pendaftaran_id')
            ->leftJoin('gelombang_pendaftaran as gp', 'gp.id', '=', 'cs.gelombang_pendaftaran_id')
            ->whereNull('cs.deleted_at')
            ->where('k.status', 'lulus')
            ->when($ppdbYear, fn ($q) => $q->where('cs.tahun_pelajaran_id', $ppdbYear->id))
            ->when($tahunPelajaranId && !$ppdbYear, fn ($q) => $q->whereRaw('1 = 0'))
            ->select([
                'cs.*',
                'tp.nama as ppdb_tahun_nama',
                'tp.tahun_mulai as ppdb_tahun_mulai',
                'tp.tahun_selesai as ppdb_tahun_selesai',
                'jp.nama as jalur_nama',
                'gp.nama as gelombang_nama',
                'r.jurusan_awal',
                'r.jurusan_final',
                'r.pindah_jurusan',
                'r.tanggal_registrasi as tanggal_registrasi_komite',
                'k.tanggal_kelulusan',
            ])
            ->distinct();
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

        $row->documents_count = DB::connection('mysql_ppdb')
            ->table('calon_dokumen')
            ->where('calon_siswa_id', $row->id)
            ->whereNull('deleted_at')
            ->count();

        $row->import_status = $this->candidateStatus($row);
        $row->label = trim("{$row->nama_lengkap} - {$row->nisn} - {$row->nomor_registrasi}");

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

    private function findPpdbYearFor(TahunPelajaran $targetYear): ?object
    {
        return DB::connection('mysql_ppdb')
            ->table('tahun_pelajarans')
            ->where(function ($q) use ($targetYear) {
                $q->whereRaw('REPLACE(nama, " ", "") = ?', [str_replace(' ', '', (string) $targetYear->nama)])
                    ->orWhere(function ($yearQ) use ($targetYear) {
                        $yearQ->where('tahun_mulai', $targetYear->tahun_mulai)
                            ->where('tahun_selesai', $targetYear->tahun_selesai);
                    });
            })
            ->first();
    }

    private function upsertPeserta(object $candidate, MatrikulasiPeriode $periode, MatrikulasiKelompok $kelompok): MatrikulasiPeserta
    {
        $ortu = DB::connection('mysql_ppdb')
            ->table('calon_ortus')
            ->where('calon_siswa_id', $candidate->id)
            ->whereNull('deleted_at')
            ->first();

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
            'data_ortu' => $ortu ? $this->objectToArray($ortu) : null,
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
        $documents = DB::connection('mysql_ppdb')
            ->table('calon_dokumen')
            ->where('calon_siswa_id', $candidate->id)
            ->whereNull('deleted_at')
            ->orderBy('jenis_dokumen')
            ->get();

        $count = 0;
        foreach ($documents as $document) {
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
                'ppdb_source_url' => $this->sourceUrl($document),
                'status_verifikasi' => $document->status_verifikasi ?: 'pending',
                'imported_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function readPpdbDocumentContent(object $document): ?string
    {
        if (($document->storage_disk ?? 'public') === 'public' && $document->file_path) {
            $basePath = rtrim((string) env('PPDB_PUBLIC_STORAGE_PATH', ''), DIRECTORY_SEPARATOR);
            if ($basePath) {
                $path = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($document->file_path, '/\\'));
                if (is_file($path) && is_readable($path)) {
                    return file_get_contents($path) ?: null;
                }
            }
        }

        $url = $this->sourceUrl($document);
        if (!$url) {
            return null;
        }

        $response = Http::timeout(20)->get($url);
        return $response->successful() ? $response->body() : null;
    }

    private function sourceUrl(object $document): ?string
    {
        if (($document->storage_disk ?? null) === 'gdrive' && $document->remote_file_id) {
            return 'https://drive.google.com/uc?export=download&id=' . $document->remote_file_id;
        }

        if (!empty($document->remote_file_url)) {
            return $document->remote_file_url;
        }

        if (!empty($document->file_path) && env('PPDB_PUBLIC_STORAGE_URL')) {
            return rtrim((string) env('PPDB_PUBLIC_STORAGE_URL'), '/') . '/' . ltrim($document->file_path, '/');
        }

        return null;
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

    private function objectToArray(object $value): array
    {
        return json_decode(json_encode($value), true) ?: [];
    }

    private function normalizeYearName(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', (string) $value);
    }
}
