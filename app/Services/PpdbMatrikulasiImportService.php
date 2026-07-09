<?php

namespace App\Services;

use App\Helpers\StorageHelper;
use App\Models\DokumenSiswa;
use App\Models\MatrikulasiDokumen;
use App\Models\MatrikulasiKelompok;
use App\Models\MatrikulasiPeriode;
use App\Models\MatrikulasiPeserta;
use App\Models\Ortu;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Permission\Models\Role;

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
            ->orderBy('jenis_kelompok')
            ->orderBy('tingkat_kelas')
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
            'tingkat_kelas' => $payload['tingkat_kelas'] ?? null,
            'jenis_kelompok' => $payload['jenis_kelompok'] ?? 'reguler',
            'kapasitas' => $payload['kapasitas'] ?? null,
            'status' => 'aktif',
        ]);
    }

    public function pesertaFor(?string $tahunPelajaranId = null)
    {
        if (!$tahunPelajaranId || !$tahun = TahunPelajaran::find($tahunPelajaranId)) {
            return MatrikulasiPeserta::query()->whereRaw('1 = 0');
        }

        $periode = $this->periodeFor($tahun);

        return MatrikulasiPeserta::query()
            ->with(['kelompok', 'user.latestSession'])
            ->withCount('dokumens')
            ->where('matrikulasi_periode_id', $periode->id);
    }

    public function assignKelompok(array $pesertaIds, string $kelompokId, string $tahunPelajaranId): int
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        $kelompok = MatrikulasiKelompok::where('matrikulasi_periode_id', $periode->id)->findOrFail($kelompokId);

        return MatrikulasiPeserta::where('matrikulasi_periode_id', $periode->id)
            ->whereIn('id', $pesertaIds)
            ->update(['matrikulasi_kelompok_id' => $kelompok->id]);
    }

    public function generateAccounts(array $pesertaIds, string $tahunPelajaranId): array
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        Role::firstOrCreate(['name' => 'Matrikulasi', 'guard_name' => 'web']);

        $result = ['created' => 0, 'existing' => 0, 'failed' => 0, 'items' => []];
        $pesertas = MatrikulasiPeserta::where('matrikulasi_periode_id', $periode->id)
            ->whereIn('id', $pesertaIds)
            ->get();

        foreach ($pesertas as $peserta) {
            try {
                DB::beginTransaction();

                if ($peserta->user_id && $peserta->user) {
                    $peserta->user->assignRole('Matrikulasi');
                    $result['existing']++;
                    $result['items'][] = [
                        'status' => 'existing',
                        'nama' => $peserta->nama_lengkap,
                        'username' => $peserta->user->username,
                        'message' => 'Akun sudah ada.',
                    ];
                    DB::commit();
                    continue;
                }

                $username = $this->uniqueMatrikulasiUsername($peserta);
                $password = $this->initialMatrikulasiPassword($peserta);
                $user = User::create([
                    'name' => $peserta->nama_lengkap,
                    'username' => $username,
                    'email' => $username . '@matrikulasi.local',
                    'password' => Hash::make($password),
                    'role' => 'matrikulasi',
                    'is_first_login' => true,
                    'is_active' => true,
                    'phone' => $peserta->data_siswa['nomor_hp'] ?? null,
                ]);
                $user->readable_password = $password;
                $user->save();
                $user->assignRole('Matrikulasi');

                $peserta->update([
                    'user_id' => $user->id,
                    'akun_created_at' => now(),
                    'akun_last_reset_at' => now(),
                ]);

                DB::commit();

                $result['created']++;
                $result['items'][] = [
                    'status' => 'created',
                    'nama' => $peserta->nama_lengkap,
                    'username' => $username,
                    'message' => 'Akun dibuat.',
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $result['failed']++;
                $result['items'][] = [
                    'status' => 'failed',
                    'nama' => $peserta->nama_lengkap,
                    'username' => '-',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
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

    public function import(array $calonSiswaIds, ?string $kelompokId, bool $includeDocuments, string $tahunPelajaranId, bool $allowUnpaid = false): array
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        $kelompok = $kelompokId
            ? MatrikulasiKelompok::where('matrikulasi_periode_id', $periode->id)->findOrFail($kelompokId)
            : null;

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

    private function uniqueMatrikulasiUsername(MatrikulasiPeserta $peserta): string
    {
        $base = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($peserta->nisn ?: $peserta->nomor_tes ?: $peserta->nik));
        $base = $base ?: 'mat' . substr((string) $peserta->id, 0, 8);
        $candidate = $base;

        if ($this->usernameExists($candidate, $peserta->user_id)) {
            $candidate = 'mat' . $base;
        }

        $suffix = 1;
        $unique = $candidate;
        while ($this->usernameExists($unique, $peserta->user_id)) {
            $unique = $candidate . $suffix;
            $suffix++;
        }

        return strtolower($unique);
    }

    private function usernameExists(string $username, ?string $exceptUserId = null): bool
    {
        return User::where('username', $username)
            ->when($exceptUserId, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->exists();
    }

    private function initialMatrikulasiPassword(MatrikulasiPeserta $peserta): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($peserta->nisn ?: $peserta->nomor_tes ?: $peserta->nik));

        return $value ?: substr((string) $peserta->id, 0, 8);
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

    public function promoteToSiswa(array $pesertaIds, string $tahunPelajaranId): array
    {
        $tahun = TahunPelajaran::findOrFail($tahunPelajaranId);
        $periode = $this->periodeFor($tahun);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $result = ['success' => 0, 'existing' => 0, 'failed' => 0, 'items' => []];
        $pesertas = MatrikulasiPeserta::with(['user', 'dokumens'])
            ->where('matrikulasi_periode_id', $periode->id)
            ->whereIn('id', $pesertaIds)
            ->get();

        foreach ($pesertas as $peserta) {
            try {
                DB::beginTransaction();

                if ($peserta->siswa_id || $peserta->status === 'dipromosikan') {
                    $result['existing']++;
                    $result['items'][] = [
                        'status' => 'existing',
                        'nama' => $peserta->nama_lengkap,
                        'nisn' => $peserta->nisn,
                        'message' => 'Peserta sudah menjadi siswa.',
                    ];
                    DB::commit();
                    continue;
                }

                if ($peserta->nisn && Siswa::where('nisn', $peserta->nisn)->exists()) {
                    throw new RuntimeException('NISN sudah ada di data siswa reguler.');
                }

                $user = $this->userForPromotedSiswa($peserta);
                $siswa = Siswa::create($this->siswaPayload($peserta, $tahun, $user));

                $this->syncOrtuToSiswa($peserta, $siswa);
                $this->syncMatrikulasiDocumentsToSiswa($peserta, $siswa, $tahun);

                SiswaKelas::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => null,
                    'tahun_pelajaran_id' => $tahun->id,
                    'tingkat' => 10,
                    'tanggal_masuk' => now()->toDateString(),
                    'status' => 'aktif',
                    'nomor_urut_absen' => null,
                    'catatan_perpindahan' => 'Ditetapkan dari staging matrikulasi PPDB. Menunggu penempatan rombel kelas 10.',
                ]);

                $peserta->update([
                    'siswa_id' => $siswa->id,
                    'user_id' => null,
                    'status' => 'dipromosikan',
                    'promoted_at' => now(),
                    'promoted_by' => auth()->id(),
                ]);

                DB::commit();

                $result['success']++;
                $result['items'][] = [
                    'status' => 'success',
                    'nama' => $peserta->nama_lengkap,
                    'nisn' => $peserta->nisn,
                    'message' => 'Berhasil menjadi siswa aktif tingkat 10 tanpa rombel.',
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                $result['failed']++;
                $result['items'][] = [
                    'status' => 'failed',
                    'nama' => $peserta->nama_lengkap,
                    'nisn' => $peserta->nisn,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function upsertPeserta(object $candidate, MatrikulasiPeriode $periode, ?MatrikulasiKelompok $kelompok): MatrikulasiPeserta
    {
        $payload = [
            'matrikulasi_kelompok_id' => $kelompok?->id,
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

    private function userForPromotedSiswa(MatrikulasiPeserta $peserta): User
    {
        $username = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($peserta->nisn ?: $peserta->nomor_tes ?: $peserta->nik));
        $username = strtolower($username ?: 'siswa' . substr((string) $peserta->id, 0, 8));
        $password = $this->initialMatrikulasiPassword($peserta);

        $user = $peserta->user ?: User::where('username', $username)->first();
        if ($user?->siswa) {
            throw new RuntimeException('Username sudah terhubung ke siswa reguler lain.');
        }

        if (!$user) {
            $user = User::create([
                'name' => $peserta->nama_lengkap,
                'username' => $username,
                'email' => $username . '@student.man1metro.sch.id',
                'password' => Hash::make($password),
                'role' => 'siswa',
                'is_first_login' => true,
                'is_active' => true,
                'phone' => $peserta->data_siswa['nomor_hp'] ?? null,
            ]);
            $user->readable_password = $password;
            $user->save();
        } else {
            $user->update([
                'name' => $peserta->nama_lengkap,
                'role' => 'siswa',
                'is_active' => true,
            ]);
        }

        $user->syncRoles(['Siswa']);

        return $user;
    }

    private function siswaPayload(MatrikulasiPeserta $peserta, TahunPelajaran $tahun, User $user): array
    {
        $data = $peserta->data_siswa ?: [];

        return [
            'user_id' => $user->id,
            'nisn' => $peserta->nisn,
            'nama_lengkap' => $peserta->nama_lengkap,
            'jenis_kelamin' => $this->pick($data, ['jenis_kelamin', 'jk']),
            'nik' => $peserta->nik,
            'tempat_lahir' => $this->pick($data, ['tempat_lahir']),
            'tanggal_lahir' => $this->pick($data, ['tanggal_lahir']),
            'agama' => $this->pick($data, ['agama']),
            'nomor_hp' => $this->pick($data, ['nomor_hp', 'hp', 'no_hp']),
            'alamat_siswa' => $this->pick($data, ['alamat_siswa', 'alamat']),
            'rt_siswa' => $this->pick($data, ['rt_siswa', 'rt']),
            'rw_siswa' => $this->pick($data, ['rw_siswa', 'rw']),
            'kodepos_siswa' => $this->pick($data, ['kodepos_siswa', 'kode_pos', 'kodepos']),
            'tahun_masuk' => $tahun->tahun_mulai,
            'asal_siswa' => 'ppdb',
            'status_siswa' => 'aktif',
            'kelas_saat_ini_id' => null,
            'ppdb_id' => is_numeric($peserta->ppdb_calon_siswa_id) ? $peserta->ppdb_calon_siswa_id : null,
            'ppdb_imported_at' => now(),
        ];
    }

    private function syncOrtuToSiswa(MatrikulasiPeserta $peserta, Siswa $siswa): void
    {
        $ortu = $peserta->data_ortu ?: [];

        Ortu::create([
            'siswa_id' => $siswa->id,
            'no_kk' => $this->pick($ortu, ['no_kk', 'nomor_kk']),
            'nik_ayah' => $this->pick($ortu, ['nik_ayah']),
            'nama_ayah' => $this->pick($ortu, ['nama_ayah']),
            'pekerjaan_ayah' => $this->pick($ortu, ['pekerjaan_ayah']),
            'penghasilan_ayah' => $this->pick($ortu, ['penghasilan_ayah']),
            'hp_ayah' => $this->pick($ortu, ['hp_ayah', 'no_hp_ayah']),
            'nik_ibu' => $this->pick($ortu, ['nik_ibu']),
            'nama_ibu' => $this->pick($ortu, ['nama_ibu']),
            'pekerjaan_ibu' => $this->pick($ortu, ['pekerjaan_ibu']),
            'penghasilan_ibu' => $this->pick($ortu, ['penghasilan_ibu']),
            'hp_ibu' => $this->pick($ortu, ['hp_ibu', 'no_hp_ibu']),
            'alamat_ortu' => $this->pick($ortu, ['alamat_ortu', 'alamat']),
            'rt_ortu' => $this->pick($ortu, ['rt_ortu', 'rt']),
            'rw_ortu' => $this->pick($ortu, ['rw_ortu', 'rw']),
            'kodepos' => $this->pick($ortu, ['kodepos', 'kode_pos']),
        ]);
    }

    private function syncMatrikulasiDocumentsToSiswa(MatrikulasiPeserta $peserta, Siswa $siswa, TahunPelajaran $tahun): void
    {
        foreach ($peserta->dokumens as $dokumen) {
            DokumenSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'ppdb_calon_dokumen_id' => $dokumen->ppdb_calon_dokumen_id,
                ],
                [
                    'jenis_dokumen' => $dokumen->jenis_dokumen ?: $dokumen->nama_dokumen,
                    'ppdb_jenis_dokumen' => $dokumen->jenis_dokumen,
                    'ppdb_source_disk' => $dokumen->ppdb_source_disk,
                    'ppdb_source_url' => $dokumen->ppdb_source_url,
                    'ppdb_imported_at' => $dokumen->imported_at ?: now(),
                    'nama_file' => $dokumen->nama_file,
                    'original_name' => $dokumen->nama_file,
                    'file_path' => $dokumen->file_path,
                    'file_size' => $dokumen->file_size,
                    'mime_type' => $dokumen->mime_type,
                    'storage_disk' => $dokumen->storage_disk,
                    'tahun_pelajaran' => $tahun->nama,
                    'uploaded_by_role' => 'system',
                    'status' => $dokumen->status_verifikasi === 'valid' ? 'approved' : 'pending',
                ]
            );
        }
    }

    private function pick(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return null;
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
