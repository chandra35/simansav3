<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatrikulasiPeserta;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\EmisNisnService;
use App\Services\SekolahDataEnrichmentService;

class SiswaStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-statistik-siswa');

        $activeYear = TahunPelajaran::query()->active()->first();
        $tingkat = in_array((int) $request->get('tingkat'), [10, 11, 12], true)
            ? (int) $request->get('tingkat') : null;
        $classes = Kelas::query()
            ->where('is_active', true)
            ->when($activeYear, fn ($query) => $query->where('tahun_pelajaran_id', $activeYear->id))
            ->when(! $activeYear, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat', 'tahun_pelajaran_id']);
        $kelasId = (string) $request->get('kelas_id', '');
        if ($kelasId !== '' && ! $classes->contains(fn (Kelas $class) => $class->id === $kelasId && (! $tingkat || (int) $class->tingkat === $tingkat))) {
            $kelasId = '';
        }

        $baseQuery = $this->applyDashboardFilters($this->baseSiswaQuery(), $activeYear, $tingkat, $kelasId);

        $totalSiswa = (clone $baseQuery)->count();
        $dataLengkap = (clone $baseQuery)
            ->where('siswa.data_diri_completed', true)
            ->where('siswa.data_ortu_completed', true)
            ->count();

        $sudahLogin = $this->countStudentsWhoHaveLoggedIn(clone $baseQuery);
        $belumPernahLogin = max($totalSiswa - $sudahLogin, 0);
        $belumLengkap = max($totalSiswa - $dataLengkap, 0);
        $npsnKosong = $this->applyMissingNpsnScope(clone $baseQuery)->count();
        $npsnTerisi = max($totalSiswa - $npsnKosong, 0);
        $lakiLaki = (clone $baseQuery)->where('siswa.jenis_kelamin', 'L')->count();
        $perempuan = (clone $baseQuery)->where('siswa.jenis_kelamin', 'P')->count();

        $kpi = [
            'total_siswa' => $totalSiswa,
            'data_lengkap' => $dataLengkap,
            'belum_lengkap' => $belumLengkap,
            'sudah_login' => $sudahLogin,
            'belum_pernah_login' => $belumPernahLogin,
            'npsn_kosong' => $npsnKosong,
            'npsn_terisi' => $npsnTerisi,
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
            'persen_lengkap' => $totalSiswa ? round(($dataLengkap / $totalSiswa) * 100, 1) : 0,
            'persen_login' => $totalSiswa ? round(($sudahLogin / $totalSiswa) * 100, 1) : 0,
            'persen_npsn_kosong' => $totalSiswa ? round(($npsnKosong / $totalSiswa) * 100, 1) : 0,
        ];

        $originSchools = $this->originSchools(clone $baseQuery);
        $missingNpsnStudents = $this->missingNpsnStudents(clone $baseQuery, $activeYear, $tingkat);
        $educationSpread = $this->educationSpread(clone $baseQuery);
        $addressProvinceSpread = $this->addressSpreadByLevel(clone $baseQuery, 'province');
        $addressCitySpread = $this->addressSpreadByLevel(clone $baseQuery, 'city');
        $addressDistrictSpread = $this->addressSpreadByLevel(clone $baseQuery, 'district');
        $schoolCitySpread = $this->schoolSpreadByCity(clone $baseQuery);

        $mapAddressPoints = $addressCitySpread
            ->take(20)
            ->values()
            ->map(function ($item) {
                return [
                    'label' => $item['name'],
                    'count' => $item['count'],
                    'location_query' => $item['location_query'],
                    'type' => 'alamat',
                ];
            });

        $mapSchoolPoints = $originSchools
            ->take(15)
            ->values()
            ->map(function ($item) {
                $query = collect([
                    $item['school_name'],
                    $item['city_name'],
                    $item['province_name'],
                    'Indonesia',
                ])->filter()->implode(', ');

                return [
                    'label' => $item['school_name'],
                    'count' => $item['count'],
                    'location_query' => $query,
                    'type' => 'sekolah',
                ];
            });

        return view('admin.siswa.statistics', [
            'kpi' => $kpi,
            'originSchools' => $originSchools,
            'missingNpsnStudents' => $missingNpsnStudents,
            'educationSpread' => $educationSpread,
            'addressProvinceSpread' => $addressProvinceSpread,
            'addressCitySpread' => $addressCitySpread,
            'addressDistrictSpread' => $addressDistrictSpread,
            'schoolCitySpread' => $schoolCitySpread,
            'mapAddressPoints' => $mapAddressPoints,
            'mapSchoolPoints' => $mapSchoolPoints,
            'activeYear' => $activeYear,
            'classes' => $classes,
            'tingkat' => $tingkat,
            'kelasId' => $kelasId,
            'filterQuery' => array_filter(['tingkat' => $tingkat, 'kelas_id' => $kelasId]),
        ]);
    }

    public function checkNpsnFromPpdb(Request $request, Siswa $siswa)
    {
        $this->authorize('edit-siswa');

        $nisn = preg_replace('/\D+/', '', (string) $siswa->nisn);
        if ($nisn === '') {
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum memiliki NISN, sehingga data asal sekolah tidak bisa dicek otomatis.',
            ], 422);
        }

        $candidate = $this->findNisnCheckerCandidate($nisn) ?: $this->findPpdbCandidateForSiswa($siswa);
        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'NPSN belum ditemukan dari checker NISN SIMANSA maupun data PPDB.',
            ], 404);
        }

        $npsn = $this->normalizeNpsn($this->pick($candidate, ['npsn_asal_sekolah', 'npsn']));
        if (!$npsn) {
            return response()->json([
                'success' => false,
                'message' => 'Data ditemukan, tetapi NPSN asal sekolah masih kosong atau tidak valid.',
            ], 422);
        }

        $schoolPayload = [
            'nama' => $this->pick($candidate, ['nama_sekolah_asal', 'asal_sekolah']) ?: 'Sekolah asal PPDB ' . $npsn,
            'status' => $this->pick($candidate, ['status_sekolah_asal']),
            'bentuk_pendidikan' => $this->pick($candidate, ['bentuk_sekolah_asal']) ?: 'SMP/MTs',
            'alamat_jalan' => $this->pick($candidate, ['alamat_sekolah_asal']),
            'kecamatan' => $this->pick($candidate, ['kecamatan_sekolah_asal']),
            'kabupaten_kota' => $this->pick($candidate, ['kabupaten_sekolah_asal']),
            'provinsi' => $this->pick($candidate, ['provinsi_sekolah_asal']),
            'last_fetched_at' => now(),
        ];

        if ($nsm = $this->pick($candidate, ['nsm_asal_sekolah', 'nsm', 'institution_nsm'])) {
            $schoolPayload['nsm'] = $nsm;
        }

        Sekolah::updateOrCreate(['npsn' => $npsn], $schoolPayload);

        $siswa->forceFill(['npsn_asal_sekolah' => $npsn])->save();

        return response()->json([
            'success' => true,
            'message' => 'NPSN asal sekolah berhasil diisi.',
            'npsn' => $npsn,
            'school_name' => $this->pick($candidate, ['nama_sekolah_asal', 'asal_sekolah']) ?: 'Sekolah asal PPDB ' . $npsn,
            'source' => $candidate['_source'] ?? 'PPDB',
        ]);
    }

    public function checkSchoolNsm(Request $request, Sekolah $sekolah)
    {
        $this->authorize('edit-siswa');

        $result = app(SekolahDataEnrichmentService::class)->enrich($sekolah);

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Data sekolah belum berhasil dilengkapi.',
            ], 422);
        }

        $sekolah = $result['data'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'npsn' => $sekolah->npsn,
            'nsm' => $sekolah->nsm,
            'school_name' => $sekolah->nama,
            'education_form' => $sekolah->bentuk_pendidikan,
            'city_name' => $sekolah->kabupaten_kota,
            'province_name' => $sekolah->provinsi,
            'sources' => $result['sources'] ?? [],
            'warnings' => $result['warnings'] ?? [],
        ]);
    }

    private function baseSiswaQuery()
    {
        return $this->applyRoleScope(Siswa::query()->from('siswa'));
    }

    private function applyDashboardFilters($query, ?TahunPelajaran $activeYear, ?int $tingkat, string $kelasId)
    {
        if ($kelasId !== '') {
            return $query->where('siswa.kelas_saat_ini_id', $kelasId);
        }
        if ($tingkat) {
            $query->whereHas('kelasSaatIni', fn ($class) => $class
                ->where('tingkat', $tingkat)
                ->when($activeYear, fn ($activeClass) => $activeClass->where('tahun_pelajaran_id', $activeYear->id))
                ->when(! $activeYear, fn ($activeClass) => $activeClass->whereRaw('1 = 0')));
        }

        return $query;
    }

    private function applyMissingNpsnScope($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('siswa.npsn_asal_sekolah')
                ->orWhereRaw("TRIM(COALESCE(siswa.npsn_asal_sekolah, '')) = ''");
        });
    }

    private function applyKelasTingkatScope($query, int $tingkat, ?string $tahunPelajaranId = null)
    {
        return $query->whereExists(function ($subQuery) use ($tingkat, $tahunPelajaranId) {
            $subQuery->select(DB::raw(1))
                ->from('siswa_kelas')
                ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                ->whereNull('siswa_kelas.deleted_at')
                ->where('siswa_kelas.status', 'aktif')
                ->where('siswa_kelas.tingkat', $tingkat)
                ->when($tahunPelajaranId, fn ($activeClass) => $activeClass->where('siswa_kelas.tahun_pelajaran_id', $tahunPelajaranId));
        });
    }

    private function missingNpsnStudents($baseQuery, ?TahunPelajaran $activeYear, ?int $selectedTingkat)
    {
        $query = $this->applyMissingNpsnScope($baseQuery);
        if (! $selectedTingkat) {
            $query = $this->applyKelasTingkatScope($query, 10, $activeYear?->id);
        }

        return $query->with(['kelasSaatIni'])
            ->orderBy('siswa.nama_lengkap')
            ->limit(50)
            ->get(['siswa.id', 'siswa.nisn', 'siswa.nama_lengkap', 'siswa.nomor_tes'])
            ->map(function (Siswa $siswa) use ($selectedTingkat) {
                return [
                    'id' => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'nomor_tes' => $siswa->nomor_tes,
                    'kelas' => $siswa->kelasSaatIni?->nama_kelas ?: ($selectedTingkat ? 'Tingkat '.$selectedTingkat : 'Tanpa rombel'),
                ];
            });
    }

    private function applyRoleScope($query)
    {
        $user = Auth::user();

        if ($user->hasRole('Wali Kelas') && !$user->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah'])) {
            $kelasIds = \App\Models\Kelas::where('wali_kelas_id', $user->id)->pluck('id');

            if ($kelasIds->isNotEmpty()) {
                $query->whereHas('kelasAktif', function ($q) use ($kelasIds) {
                    $q->whereIn('kelas.id', $kelasIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    private function countStudentsWhoHaveLoggedIn($baseQuery): int
    {
        return (clone $baseQuery)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.user_id', 'siswa.user_id')
                    ->where('activity_logs.activity_type', 'login');
            })
            ->count();
    }

    private function findNisnCheckerCandidate(string $nisn): ?array
    {
        try {
            $result = app(EmisNisnService::class)->cekNisn($nisn);
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengecek NPSN asal sekolah dari checker NISN SIMANSA', [
                'nisn' => $nisn,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (!($result['success'] ?? false) || empty($result['data'])) {
            return null;
        }

        $kemdikbud = (array) ($result['data']['kemdikbud'] ?? []);
        $kemenag = (array) ($result['data']['kemenag'] ?? []);
        $npsn = $this->normalizeNpsn($kemdikbud['npsn'] ?? ($kemenag['npsn'] ?? null));

        if (!$npsn) {
            return null;
        }

        return [
            'npsn_asal_sekolah' => $npsn,
            'nsm_asal_sekolah' => $kemenag['institution_nsm'] ?? null,
            'nama_sekolah_asal' => $kemdikbud['sekolah'] ?? ($kemenag['institution_name'] ?? null),
            'status_sekolah_asal' => null,
            'bentuk_sekolah_asal' => null,
            'alamat_sekolah_asal' => null,
            'kecamatan_sekolah_asal' => null,
            'kabupaten_sekolah_asal' => null,
            'provinsi_sekolah_asal' => null,
            '_source' => 'checker NISN SIMANSA',
        ];
    }

    private function findPpdbCandidateForSiswa(Siswa $siswa): ?array
    {
        $fromMatrikulasi = MatrikulasiPeserta::query()
            ->where(function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);

                if ($siswa->nisn) {
                    $query->orWhere('nisn', $siswa->nisn);
                }

                if ($siswa->ppdb_id) {
                    $query->orWhere('ppdb_calon_siswa_id', $siswa->ppdb_id);
                }
            })
            ->latest('updated_at')
            ->first();

        if ($fromMatrikulasi) {
            $data = (array) ($fromMatrikulasi->data_ppdb ?: $fromMatrikulasi->data_siswa ?: []);
            $data['npsn_asal_sekolah'] = $this->pick($data, ['npsn_asal_sekolah', 'npsn'])
                ?: $this->pick((array) $fromMatrikulasi->data_siswa, ['npsn_asal_sekolah', 'npsn']);
            $data['nama_sekolah_asal'] = $this->pick($data, ['nama_sekolah_asal', 'asal_sekolah'])
                ?: $this->pick((array) $fromMatrikulasi->data_siswa, ['nama_sekolah_asal', 'asal_sekolah']);
            $data['_source'] = 'staging matrikulasi';

            if ($this->normalizeNpsn($this->pick($data, ['npsn_asal_sekolah', 'npsn']))) {
                return $data;
            }
        }

        return $this->fetchPpdbCandidateFromApi($siswa);
    }

    private function fetchPpdbCandidateFromApi(Siswa $siswa): ?array
    {
        $baseUrl = rtrim((string) config('services.ppdb_sync.base_url'), '/');
        $token = (string) config('services.ppdb_sync.token');

        if ($baseUrl === '' || $token === '') {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout((int) config('services.ppdb_sync.timeout', 30))
                ->get($baseUrl . '/api/internal/simansa/pendaftar', [
                    'q' => $siswa->nisn,
                    'scope' => 'all',
                    'smart' => 0,
                    'limit' => 5,
                    'per_page' => 5,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $nisn = preg_replace('/\D+/', '', (string) $siswa->nisn);
            $candidate = collect($response->json('data', []))
                ->map(fn ($row) => (array) $row)
                ->first(fn ($row) => preg_replace('/\D+/', '', (string) ($row['nisn'] ?? '')) === $nisn);

            if ($candidate) {
                $candidate['_source'] = 'API PPDB';
            }

            return $candidate;
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengecek NPSN asal sekolah dari API PPDB', [
                'siswa_id' => $siswa->id,
                'nisn' => $siswa->nisn,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizeNpsn($value): ?string
    {
        $npsn = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));

        return preg_match('/^[A-Z0-9]{8}$/', $npsn) ? $npsn : null;
    }

    private function pick(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && filled($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    private function originSchools($baseQuery)
    {
        return $baseQuery
            ->leftJoin('sekolah as sekolah_asal', 'sekolah_asal.npsn', '=', 'siswa.npsn_asal_sekolah')
            ->whereNotNull('siswa.npsn_asal_sekolah')
            ->groupBy(
                'siswa.npsn_asal_sekolah',
                'sekolah_asal.nsm',
                'sekolah_asal.nama',
                'sekolah_asal.status',
                'sekolah_asal.bentuk_pendidikan',
                'sekolah_asal.akreditasi',
                'sekolah_asal.kementerian_pembina',
                'sekolah_asal.kecamatan',
                'sekolah_asal.kabupaten_kota',
                'sekolah_asal.provinsi',
                'sekolah_asal.last_fetched_at'
            )
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'siswa.npsn_asal_sekolah as npsn',
                'sekolah_asal.nsm as nsm',
                DB::raw('COALESCE(sekolah_asal.nama, "Sekolah Tidak Dikenal") as school_name'),
                DB::raw('COALESCE(sekolah_asal.status, "Tidak diketahui") as school_status'),
                DB::raw('COALESCE(sekolah_asal.bentuk_pendidikan, "Tidak diketahui") as education_form'),
                DB::raw('COALESCE(sekolah_asal.akreditasi, "-") as accreditation'),
                DB::raw('COALESCE(sekolah_asal.kementerian_pembina, "-") as ministry'),
                DB::raw('COALESCE(sekolah_asal.kecamatan, "") as district_name'),
                DB::raw('COALESCE(sekolah_asal.kabupaten_kota, "") as city_name'),
                DB::raw('COALESCE(sekolah_asal.provinsi, "") as province_name'),
                'sekolah_asal.last_fetched_at as last_fetched_at',
                DB::raw('COUNT(*) as count'),
            ])
            ->map(function ($item) {
                return [
                    'npsn' => $item->npsn,
                    'nsm' => $item->nsm,
                    'school_name' => $item->school_name,
                    'school_status' => $item->school_status,
                    'education_form' => $item->education_form,
                    'accreditation' => $item->accreditation,
                    'ministry' => $item->ministry,
                    'district_name' => $item->district_name,
                    'city_name' => $item->city_name,
                    'province_name' => $item->province_name,
                    'last_fetched_at' => $item->last_fetched_at,
                    'count' => (int) $item->count,
                ];
            });
    }

    private function educationSpread($baseQuery)
    {
        return $baseQuery
            ->leftJoin('sekolah as sekolah_asal', 'sekolah_asal.npsn', '=', 'siswa.npsn_asal_sekolah')
            ->whereNotNull('siswa.npsn_asal_sekolah')
            ->groupBy('sekolah_asal.bentuk_pendidikan')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                DB::raw('COALESCE(sekolah_asal.bentuk_pendidikan, "Tidak diketahui") as label'),
                DB::raw('COUNT(*) as count'),
            ])
            ->map(function ($item) {
                return [
                    'label' => $item->label,
                    'count' => (int) $item->count,
                ];
            });
    }

    private function schoolSpreadByCity($baseQuery)
    {
        return $baseQuery
            ->leftJoin('sekolah as sekolah_asal', 'sekolah_asal.npsn', '=', 'siswa.npsn_asal_sekolah')
            ->whereNotNull('siswa.npsn_asal_sekolah')
            ->groupBy('sekolah_asal.kabupaten_kota', 'sekolah_asal.provinsi')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(15)
            ->get([
                DB::raw('COALESCE(sekolah_asal.kabupaten_kota, "Wilayah tidak diketahui") as name'),
                DB::raw('COALESCE(sekolah_asal.provinsi, "") as province_name'),
                DB::raw('COUNT(*) as count'),
            ])
            ->map(function ($item) {
                $query = collect([$item->name, $item->province_name, 'Indonesia'])
                    ->filter()
                    ->implode(', ');

                return [
                    'name' => $item->name,
                    'province_name' => $item->province_name,
                    'count' => (int) $item->count,
                    'location_query' => $query,
                ];
            });
    }

    private function addressSpreadByLevel($baseQuery, string $level)
    {
        $base = $baseQuery
            ->leftJoin('ortu as ortu_siswa', 'ortu_siswa.siswa_id', '=', 'siswa.id');

        if ($level === 'province') {
            $base->leftJoin('indonesia_provinces as siswa_prov', 'siswa_prov.code', '=', 'siswa.provinsi_id_siswa')
                ->leftJoin('indonesia_provinces as ortu_prov', 'ortu_prov.code', '=', 'ortu_siswa.provinsi_id');

            $nameExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_prov.name ELSE siswa_prov.name END';
            $provinceExpr = $nameExpr;
            $queryExpr = "CONCAT($nameExpr, ', Indonesia')";
        } elseif ($level === 'city') {
            $base->leftJoin('indonesia_cities as siswa_city', 'siswa_city.code', '=', 'siswa.kabupaten_id_siswa')
                ->leftJoin('indonesia_cities as ortu_city', 'ortu_city.code', '=', 'ortu_siswa.kabupaten_id')
                ->leftJoin('indonesia_provinces as siswa_prov', 'siswa_prov.code', '=', 'siswa.provinsi_id_siswa')
                ->leftJoin('indonesia_provinces as ortu_prov', 'ortu_prov.code', '=', 'ortu_siswa.provinsi_id');

            $nameExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_city.name ELSE siswa_city.name END';
            $provinceExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_prov.name ELSE siswa_prov.name END';
            $queryExpr = "CONCAT($nameExpr, ', ', $provinceExpr, ', Indonesia')";
        } else {
            $base->leftJoin('indonesia_districts as siswa_district', 'siswa_district.code', '=', 'siswa.kecamatan_id_siswa')
                ->leftJoin('indonesia_districts as ortu_district', 'ortu_district.code', '=', 'ortu_siswa.kecamatan_id')
                ->leftJoin('indonesia_cities as siswa_city', 'siswa_city.code', '=', 'siswa.kabupaten_id_siswa')
                ->leftJoin('indonesia_cities as ortu_city', 'ortu_city.code', '=', 'ortu_siswa.kabupaten_id')
                ->leftJoin('indonesia_provinces as siswa_prov', 'siswa_prov.code', '=', 'siswa.provinsi_id_siswa')
                ->leftJoin('indonesia_provinces as ortu_prov', 'ortu_prov.code', '=', 'ortu_siswa.provinsi_id');

            $nameExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_district.name ELSE siswa_district.name END';
            $cityExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_city.name ELSE siswa_city.name END';
            $provinceExpr = 'CASE WHEN siswa.alamat_sama_ortu = 1 THEN ortu_prov.name ELSE siswa_prov.name END';
            $queryExpr = "CONCAT($nameExpr, ', ', $cityExpr, ', ', $provinceExpr, ', Indonesia')";
        }

        $locationBase = $base
            ->selectRaw("$nameExpr as name")
            ->selectRaw("$provinceExpr as province_name")
            ->selectRaw("$queryExpr as location_query")
            ->whereNotNull(DB::raw($nameExpr));

        return DB::query()
            ->fromSub($locationBase, 'location_points')
            ->select('name', 'province_name', 'location_query')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('name', 'province_name', 'location_query')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'province_name' => $item->province_name,
                    'location_query' => $item->location_query,
                    'count' => (int) $item->count,
                ];
            });
    }
}
