<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ortu;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class OrtuController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $ortu = $siswa->ortu ?? new Ortu();
        $provinces = Province::all();
        $pekerjaan = config('simansa.pekerjaan_ortu');
        $penghasilan = config('simansa.penghasilan_ortu');

        return view('siswa.profile.ortu', compact('ortu', 'provinces', 'pekerjaan', 'penghasilan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'no_kk' => 'nullable|string|max:20',
            'status_ayah' => 'nullable|in:masih_hidup,meninggal',
            'nama_ayah' => 'nullable|string|max:255',
            'nik_ayah' => 'nullable|string|max:16',
            'hp_ayah' => 'nullable|string|max:15',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'status_ibu' => 'nullable|in:masih_hidup,meninggal',
            'nama_ibu' => 'nullable|string|max:255',
            'nik_ibu' => 'nullable|string|max:16',
            'hp_ibu' => 'nullable|string|max:15',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'alamat_ortu' => 'nullable|string',
            'rt_ortu' => 'nullable|string|max:5',
            'rw_ortu' => 'nullable|string|max:5',
            'kodepos' => 'nullable|string|max:10',
            'provinsi_id' => 'nullable|string|exists:indonesia_provinces,code',
            'kabupaten_id' => 'nullable|string|exists:indonesia_cities,code',
            'kecamatan_id' => 'nullable|string|exists:indonesia_districts,code',
            'kelurahan_id' => 'nullable|string|exists:indonesia_villages,code',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Auth::user();
                $siswa = $user->siswa;

                // Prepare data untuk update/create ortu
                $ortuData = [
                    'no_kk' => $request->no_kk,
                    'status_ayah' => $request->status_ayah,
                    'nama_ayah' => $request->nama_ayah,
                    'status_ibu' => $request->status_ibu,
                    'nama_ibu' => $request->nama_ibu,
                    'alamat_ortu' => $request->alamat_ortu,
                    'rt_ortu' => $request->rt_ortu,
                    'rw_ortu' => $request->rw_ortu,
                    'kodepos' => $request->kodepos,
                    'provinsi_id' => $request->provinsi_id,
                    'kabupaten_id' => $request->kabupaten_id,
                    'kecamatan_id' => $request->kecamatan_id,
                    'kelurahan_id' => $request->kelurahan_id,
                ];

                // Tambahkan data ayah jika masih hidup
                if ($request->status_ayah === 'masih_hidup') {
                    $ortuData = array_merge($ortuData, [
                        'nik_ayah' => $request->nik_ayah,
                        'hp_ayah' => $request->hp_ayah,
                        'pekerjaan_ayah' => $request->pekerjaan_ayah,
                        'penghasilan_ayah' => $request->penghasilan_ayah,
                    ]);
                } else {
                    // Clear data ayah jika meninggal
                    $ortuData = array_merge($ortuData, [
                        'nik_ayah' => null,
                        'hp_ayah' => null,
                        'pekerjaan_ayah' => null,
                        'penghasilan_ayah' => null,
                    ]);
                }

                // Tambahkan data ibu jika masih hidup
                if ($request->status_ibu === 'masih_hidup') {
                    $ortuData = array_merge($ortuData, [
                        'nik_ibu' => $request->nik_ibu,
                        'hp_ibu' => $request->hp_ibu,
                        'pekerjaan_ibu' => $request->pekerjaan_ibu,
                        'penghasilan_ibu' => $request->penghasilan_ibu,
                    ]);
                } else {
                    // Clear data ibu jika meninggal
                    $ortuData = array_merge($ortuData, [
                        'nik_ibu' => null,
                        'hp_ibu' => null,
                        'pekerjaan_ibu' => null,
                        'penghasilan_ibu' => null,
                    ]);
                }

                // Update atau create data ortu
                $ortu = $siswa->ortu;
                if ($ortu) {
                    $ortu->update($ortuData);
                } else {
                    $ortuData['siswa_id'] = $siswa->id;
                    Ortu::create($ortuData);
                }

                // Update status kelengkapan data ortu
                $siswa->update(['data_ortu_completed' => true]);

                // Log activity
                \App\Models\ActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => 'update',
                    'model_type' => 'App\\Models\\Ortu',
                    'model_id' => $siswa->ortu->id,
                    'description' => 'Berhasil memperbarui data orangtua',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            });

            return redirect()->route('siswa.profile.diri')->with('success', 'Data orangtua berhasil disimpan! Silakan lengkapi data diri Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    // API Methods untuk data Indonesia
    public function getCities($provinceCode)
    {
        $cities = City::where('province_code', $provinceCode)->get();
        return response()->json($cities);
    }

    public function getDistricts($cityCode)
    {
        $districts = District::where('city_code', $cityCode)->get();
        return response()->json($districts);
    }

    public function getVillages($districtCode)
    {
        $villages = Village::where('district_code', $districtCode)->get();
        return response()->json($villages);
    }
}