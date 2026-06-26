<?php

namespace App\Services;

use App\Helpers\StorageHelper;
use App\Models\DokumenSiswa;
use App\Models\Kelas;
use App\Models\Ortu;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PpdbMatrikulasiImportService
{
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
            ->map(fn ($row) => $this->decorateCandidate($row));
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
            ->map(fn ($row) => $this->decorateCandidate($row));
    }

    public function import(array $calonSiswaIds, string $kelasId, bool $includeDocuments, ?string $tahunPelajaranId = null): array
    {
        $kelas = Kelas::with('tahunPelajaran')->findOrFail($kelasId);

        if (($kelas->jenis_kelas ?? 'reguler') !== 'matrikulasi') {
            throw new RuntimeException('Kelas tujuan harus berjenis matrikulasi.');
        }

        if ($tahunPelajaranId && $kelas->tahun_pelajaran_id !== $tahunPelajaranId) {
            throw new RuntimeException('Tahun pelajaran pilihan tidak sama dengan tahun pelajaran kelas matrikulasi.');
        }

        $targetTahun = $kelas->tahunPelajaran;
        if (!$targetTahun) {
            throw new RuntimeException('Kelas matrikulasi belum terhubung ke tahun pelajaran.');
        }

        $candidates = $this->preview($calonSiswaIds, $targetTahun->id);
        $results = [
            'success' => 0,
            'failed' => 0,
            'documents_copied' => 0,
            'items' => [],
        ];

        foreach ($candidates as $candidate) {
            try {
                DB::beginTransaction();

                $this->assertYearMatches($candidate, $targetTahun);
                $this->assertImportable($candidate);

                $siswa = $this->upsertSiswa($candidate, $targetTahun);
                $this->upsertOrtu($siswa, $candidate);
                $this->assignToMatrikulasi($siswa, $kelas);

                $copied = $includeDocuments
                    ? $this->syncDocuments($siswa, $candidate, $targetTahun, $kelas)
                    : 0;

                DB::commit();

                $results['success']++;
                $results['documents_copied'] += $copied;
                $results['items'][] = [
                    'status' => 'success',
                    'nisn' => $candidate->nisn,
                    'nama' => $candidate->nama_lengkap,
                    'message' => 'Berhasil disinkronkan ke matrikulasi.',
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

    public function matrikulasiClasses(?string $tahunPelajaranId = null): Collection
    {
        return Kelas::query()
            ->with('tahunPelajaran')
            ->where('jenis_kelas', 'matrikulasi')
            ->when($tahunPelajaranId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->orderByDesc('tahun_pelajaran_id')
            ->orderBy('nama_kelas')
            ->get();
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

    private function decorateCandidate(object $row): object
    {
        $row->existing_siswa = Siswa::query()
            ->where(function ($q) use ($row) {
                $q->where('ppdb_id', $row->id)
                    ->orWhere('nisn', $row->nisn);

                if (!empty($row->nik)) {
                    $q->orWhere('nik', $row->nik);
                }
            })
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
        if (!$candidate->existing_siswa) {
            return 'baru';
        }

        if ($candidate->existing_siswa->ppdb_id && (string) $candidate->existing_siswa->ppdb_id !== (string) $candidate->id) {
            return 'konflik';
        }

        return 'sudah_ada';
    }

    private function assertImportable(object $candidate): void
    {
        if ($candidate->import_status === 'konflik') {
            throw new RuntimeException('NISN/NIK sudah dipakai oleh siswa lain dengan asal PPDB berbeda.');
        }

        if (empty($candidate->nisn) || !preg_match('/^\d{10}$/', (string) $candidate->nisn)) {
            throw new RuntimeException('NISN tidak valid untuk dibuat sebagai akun siswa.');
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

    private function upsertSiswa(object $candidate, TahunPelajaran $targetTahun): Siswa
    {
        $existing = Siswa::query()
            ->where('ppdb_id', $candidate->id)
            ->orWhere('nisn', $candidate->nisn)
            ->when($candidate->nik, fn ($q) => $q->orWhere('nik', $candidate->nik))
            ->first();

        $user = $existing?->user ?: User::where('username', $candidate->nisn)->first();
        if (!$user) {
            $user = User::create([
                'name' => $candidate->nama_lengkap,
                'username' => $candidate->nisn,
                'email' => $this->studentEmail($candidate->nisn),
                'password' => Hash::make($candidate->nisn),
                'role' => 'siswa',
                'is_first_login' => true,
            ]);
            $user->readable_password = $candidate->nisn;
            $user->save();
            $user->assignRole('Siswa');
        } else {
            $user->forceFill([
                'name' => $candidate->nama_lengkap,
            ])->save();
        }

        $payload = [
            'user_id' => $user->id,
            'nisn' => $candidate->nisn,
            'nik' => $candidate->nik,
            'nama_lengkap' => $candidate->nama_lengkap,
            'jenis_kelamin' => $candidate->jenis_kelamin ?: 'L',
            'tempat_lahir' => $candidate->tempat_lahir,
            'tanggal_lahir' => $candidate->tanggal_lahir,
            'agama' => $candidate->agama,
            'jumlah_saudara' => $candidate->jumlah_saudara,
            'anak_ke' => $candidate->anak_ke,
            'hobi' => $candidate->hobi,
            'cita_cita' => $candidate->cita_cita,
            'nomor_hp' => $candidate->nomor_hp,
            'alamat_sama_ortu' => (bool) ($candidate->alamat_sama_ortu ?? true),
            'jenis_tempat_tinggal' => $candidate->jenis_tempat_tinggal,
            'alamat_siswa' => $candidate->alamat_siswa,
            'rt_siswa' => $candidate->rt_siswa,
            'rw_siswa' => $candidate->rw_siswa,
            'provinsi_id_siswa' => $candidate->provinsi_id_siswa,
            'kabupaten_id_siswa' => $candidate->kabupaten_id_siswa,
            'kecamatan_id_siswa' => $candidate->kecamatan_id_siswa,
            'kelurahan_id_siswa' => $candidate->kelurahan_id_siswa,
            'kodepos_siswa' => $candidate->kodepos_siswa,
            'npsn_asal_sekolah' => $candidate->npsn_asal_sekolah,
            'data_diri_completed' => (bool) ($candidate->data_diri_completed ?? false),
            'data_ortu_completed' => (bool) ($candidate->data_ortu_completed ?? false),
            'tahun_masuk' => $targetTahun->tahun_mulai,
            'asal_siswa' => 'ppdb',
            'status_siswa' => 'aktif',
            'ppdb_id' => $candidate->id,
            'ppdb_imported_at' => now(),
        ];

        if ($existing) {
            $existing->fill($payload)->save();
            return $existing;
        }

        return Siswa::create($payload);
    }

    private function upsertOrtu(Siswa $siswa, object $candidate): void
    {
        $ortu = DB::connection('mysql_ppdb')
            ->table('calon_ortus')
            ->where('calon_siswa_id', $candidate->id)
            ->whereNull('deleted_at')
            ->first();

        $payload = [
            'siswa_id' => $siswa->id,
        ];

        if ($ortu) {
            $payload += [
                'no_kk' => $ortu->no_kk,
                'status_ayah' => $ortu->status_ayah,
                'nik_ayah' => $ortu->nik_ayah,
                'nama_ayah' => $ortu->nama_ayah,
                'pekerjaan_ayah' => $ortu->pekerjaan_ayah,
                'penghasilan_ayah' => $ortu->penghasilan_ayah,
                'hp_ayah' => $ortu->hp_ayah,
                'status_ibu' => $ortu->status_ibu,
                'nik_ibu' => $ortu->nik_ibu,
                'nama_ibu' => $ortu->nama_ibu,
                'pekerjaan_ibu' => $ortu->pekerjaan_ibu,
                'penghasilan_ibu' => $ortu->penghasilan_ibu,
                'hp_ibu' => $ortu->hp_ibu,
                'alamat_ortu' => $ortu->alamat_ortu,
                'rt_ortu' => $ortu->rt_ortu,
                'rw_ortu' => $ortu->rw_ortu,
                'provinsi_id' => $ortu->provinsi_id,
                'kabupaten_id' => $ortu->kabupaten_id,
                'kecamatan_id' => $ortu->kecamatan_id,
                'kelurahan_id' => $ortu->kelurahan_id,
                'kodepos' => $ortu->kodepos,
            ];
        }

        Ortu::updateOrCreate(['siswa_id' => $siswa->id], $payload);
    }

    private function assignToMatrikulasi(Siswa $siswa, Kelas $kelas): void
    {
        $exists = $siswa->kelas()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->wherePivot('kelas_id', $kelas->id)
            ->wherePivot('status', 'aktif')
            ->exists();

        if (!$exists) {
            $lastAbsen = $kelas->siswas()
                ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->max('siswa_kelas.nomor_urut_absen') ?? 0;

            $kelas->siswas()->attach($siswa->id, [
                'id' => (string) Str::uuid(),
                'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                'tanggal_masuk' => now()->toDateString(),
                'status' => 'aktif',
                'nomor_urut_absen' => $lastAbsen + 1,
                'catatan_perpindahan' => 'Import matrikulasi dari PPDB',
            ]);
        }

        $siswa->forceFill(['kelas_saat_ini_id' => $kelas->id])->save();
    }

    private function syncDocuments(Siswa $siswa, object $candidate, TahunPelajaran $targetTahun, Kelas $kelas): int
    {
        $documents = DB::connection('mysql_ppdb')
            ->table('calon_dokumen')
            ->where('calon_siswa_id', $candidate->id)
            ->whereNull('deleted_at')
            ->orderBy('jenis_dokumen')
            ->get();

        $count = 0;
        foreach ($documents as $document) {
            if (DokumenSiswa::where('siswa_id', $siswa->id)->where('ppdb_calon_dokumen_id', $document->id)->exists()) {
                continue;
            }

            $content = $this->readPpdbDocumentContent($document);
            if ($content === null) {
                continue;
            }

            $disk = StorageHelper::getDokumenDisk();
            $extension = $this->extensionFor($document);
            $path = 'ppdb/' . $targetTahun->tahun_mulai . '/' . $siswa->id . '/' . $document->id . '.' . $extension;
            Storage::disk($disk)->put($path, $content);

            DokumenSiswa::create([
                'siswa_id' => $siswa->id,
                'ppdb_calon_dokumen_id' => $document->id,
                'ppdb_jenis_dokumen' => $document->jenis_dokumen,
                'ppdb_source_disk' => $document->storage_disk,
                'ppdb_source_url' => $this->sourceUrl($document),
                'ppdb_imported_at' => now(),
                'jenis_dokumen' => $this->mapDocumentType($document->jenis_dokumen),
                'nama_file' => $document->nama_file ?: basename((string) $document->file_path),
                'file_path' => $path,
                'file_size' => strlen($content),
                'mime_type' => $document->mime_type ?: 'application/octet-stream',
                'keterangan' => trim(($document->nama_dokumen ?: $document->jenis_dokumen) . ' - import PPDB'),
                'file_uuid' => (string) Str::uuid(),
                'original_name' => $document->nama_file ?: basename((string) $document->file_path),
                'storage_disk' => $disk,
                'tahun_pelajaran' => $targetTahun->nama,
                'kelas_id' => $kelas->id,
                'uploaded_by_role' => 'admin',
                'status' => $document->status_verifikasi === 'valid' ? 'approved' : 'pending',
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

    private function mapDocumentType(?string $type): string
    {
        return match ($type) {
            'kk' => 'kk',
            'ijazah', 'skl', 'surat_keterangan', 'surat_lulus' => 'ijazah_smp',
            'kip', 'pip' => 'kip',
            'sktm' => 'sktm',
            default => 'lainnya',
        };
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

    private function studentEmail(string $nisn): string
    {
        $email = strtolower($nisn) . '@student.man1metro.sch.id';
        if (!User::where('email', $email)->exists()) {
            return $email;
        }

        return strtolower($nisn) . '+' . Str::lower(Str::random(6)) . '@student.man1metro.sch.id';
    }

    private function normalizeYearName(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', (string) $value);
    }
}
