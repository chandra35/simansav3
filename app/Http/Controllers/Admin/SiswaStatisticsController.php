<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-siswa');

        $baseQuery = $this->baseSiswaQuery();

        $totalSiswa = (clone $baseQuery)->count();
        $dataLengkap = (clone $baseQuery)
            ->where('siswa.data_diri_completed', true)
            ->where('siswa.data_ortu_completed', true)
            ->count();

        $sudahLogin = $this->countStudentsWhoHaveLoggedIn(clone $baseQuery);
        $belumPernahLogin = max($totalSiswa - $sudahLogin, 0);
        $belumLengkap = max($totalSiswa - $dataLengkap, 0);

        $kpi = [
            'total_siswa' => $totalSiswa,
            'data_lengkap' => $dataLengkap,
            'belum_lengkap' => $belumLengkap,
            'sudah_login' => $sudahLogin,
            'belum_pernah_login' => $belumPernahLogin,
        ];

        $topSchools = $this->topOriginSchools();
        $educationSpread = $this->educationSpread();
        $addressProvinceSpread = $this->addressSpreadByLevel('province');
        $addressCitySpread = $this->addressSpreadByLevel('city');
        $addressDistrictSpread = $this->addressSpreadByLevel('district');
        $schoolCitySpread = $this->schoolSpreadByCity();

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

        $mapSchoolPoints = $topSchools
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
            'topSchools' => $topSchools,
            'educationSpread' => $educationSpread,
            'addressProvinceSpread' => $addressProvinceSpread,
            'addressCitySpread' => $addressCitySpread,
            'addressDistrictSpread' => $addressDistrictSpread,
            'schoolCitySpread' => $schoolCitySpread,
            'mapAddressPoints' => $mapAddressPoints,
            'mapSchoolPoints' => $mapSchoolPoints,
        ]);
    }

    private function baseSiswaQuery()
    {
        return $this->applyRoleScope(Siswa::query()->from('siswa'));
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

    private function topOriginSchools()
    {
        return $this->applyRoleScope(Siswa::query()->from('siswa'))
            ->leftJoin('sekolah as sekolah_asal', 'sekolah_asal.npsn', '=', 'siswa.npsn_asal_sekolah')
            ->whereNotNull('siswa.npsn_asal_sekolah')
            ->groupBy(
                'siswa.npsn_asal_sekolah',
                'sekolah_asal.nama',
                'sekolah_asal.bentuk_pendidikan',
                'sekolah_asal.kabupaten_kota',
                'sekolah_asal.provinsi'
            )
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(15)
            ->get([
                'siswa.npsn_asal_sekolah as npsn',
                DB::raw('COALESCE(sekolah_asal.nama, "Sekolah Tidak Dikenal") as school_name'),
                DB::raw('COALESCE(sekolah_asal.bentuk_pendidikan, "Tidak diketahui") as education_form'),
                DB::raw('COALESCE(sekolah_asal.kabupaten_kota, "") as city_name'),
                DB::raw('COALESCE(sekolah_asal.provinsi, "") as province_name'),
                DB::raw('COUNT(*) as count'),
            ])
            ->map(function ($item) {
                return [
                    'npsn' => $item->npsn,
                    'school_name' => $item->school_name,
                    'education_form' => $item->education_form,
                    'city_name' => $item->city_name,
                    'province_name' => $item->province_name,
                    'count' => (int) $item->count,
                ];
            });
    }

    private function educationSpread()
    {
        return $this->applyRoleScope(Siswa::query()->from('siswa'))
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

    private function schoolSpreadByCity()
    {
        return $this->applyRoleScope(Siswa::query()->from('siswa'))
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

    private function addressSpreadByLevel(string $level)
    {
        $base = $this->applyRoleScope(Siswa::query()->from('siswa'))
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
