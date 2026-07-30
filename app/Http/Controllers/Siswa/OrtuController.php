<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ortu;
use App\Services\ActivityLogService;
use App\Support\UppercaseInputNormalizer;
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
        $validated = $request->validate([
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

        $validated = UppercaseInputNormalizer::normalize($validated, [
            'nama_ayah',
            'nama_ibu',
            'alamat_ortu',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = Auth::user();
                $siswa = $user->siswa;

                // Prepare data untuk update/create ortu
                $ortuData = [
                    'no_kk' => $validated['no_kk'] ?? null,
                    'status_ayah' => $validated['status_ayah'] ?? null,
                    'nama_ayah' => $validated['nama_ayah'] ?? null,
                    'status_ibu' => $validated['status_ibu'] ?? null,
                    'nama_ibu' => $validated['nama_ibu'] ?? null,
                    'alamat_ortu' => $validated['alamat_ortu'] ?? null,
                    'rt_ortu' => $validated['rt_ortu'] ?? null,
                    'rw_ortu' => $validated['rw_ortu'] ?? null,
                    'kodepos' => $validated['kodepos'] ?? null,
                    'provinsi_id' => $validated['provinsi_id'] ?? null,
                    'kabupaten_id' => $validated['kabupaten_id'] ?? null,
                    'kecamatan_id' => $validated['kecamatan_id'] ?? null,
                    'kelurahan_id' => $validated['kelurahan_id'] ?? null,
                ];

                // Tambahkan data ayah jika masih hidup
                if (($validated['status_ayah'] ?? null) === 'masih_hidup') {
                    $ortuData = array_merge($ortuData, [
                        'nik_ayah' => $validated['nik_ayah'] ?? null,
                        'hp_ayah' => $validated['hp_ayah'] ?? null,
                        'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                        'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
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
                if (($validated['status_ibu'] ?? null) === 'masih_hidup') {
                    $ortuData = array_merge($ortuData, [
                        'nik_ibu' => $validated['nik_ibu'] ?? null,
                        'hp_ibu' => $validated['hp_ibu'] ?? null,
                        'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                        'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
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
                $oldData = $ortu ? $ortu->toArray() : [];
                
                if ($ortu) {
                    $ortu->update($ortuData);
                } else {
                    $ortuData['siswa_id'] = $siswa->id;
                    $ortu = Ortu::create($ortuData);
                }

                // Update status kelengkapan data ortu
                $siswa->update(['data_ortu_completed' => true]);

                // Enhanced activity log with change tracking
                if (!empty($oldData)) {
                    ActivityLogService::logChanges(
                        'update_data_ortu',
                        $ortu,
                        $oldData,
                        $ortuData,
                        'Memperbarui data orangtua'
                    );
                } else {
                    ActivityLogService::log([
                        'activity_type' => 'create_data_ortu',
                        'model_type' => Ortu::class,
                        'model_id' => $ortu->id,
                        'description' => 'Menambahkan data orangtua',
                        'new_values' => $ortuData,
                    ]);
                }
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
