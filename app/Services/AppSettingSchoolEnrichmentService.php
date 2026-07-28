<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Sekolah;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class AppSettingSchoolEnrichmentService
{
    public function __construct(
        private readonly SekolahDataEnrichmentService $enrichment
    ) {
    }

    public function fetch(AppSetting $setting, string $npsn): array
    {
        $school = Sekolah::query()->firstOrCreate(
            ['npsn' => $npsn],
            ['nama' => $setting->nama_sekolah ?: "Sekolah {$npsn}"]
        );
        $result = $this->enrichment->enrich($school);
        $school = $school->fresh();

        if (! ($result['success'] ?? false) || blank($school->nama)) {
            return $result;
        }

        $province = $this->findRegion(Province::query(), $school->provinsi);
        $city = $this->findRegion(
            City::query()->when($province, fn ($query) => $query->where('province_code', $province->code)),
            $school->kabupaten_kota
        );
        $district = $this->findRegion(
            District::query()->when($city, fn ($query) => $query->where('city_code', $city->code)),
            $school->kecamatan
        );
        $village = $this->findRegion(
            Village::query()->when($district, fn ($query) => $query->where('district_code', $district->code)),
            $school->desa_kelurahan
        );

        $payload = collect([
            'npsn' => $school->npsn,
            'nsm' => $school->nsm,
            'nama_sekolah' => $school->nama,
            'alamat' => $school->alamat_jalan,
            'rt' => $school->rt,
            'rw' => $school->rw,
            'provinsi_code' => $province?->code,
            'kota_code' => $city?->code,
            'kecamatan_code' => $district?->code,
            'kelurahan_code' => $village?->code,
            'kode_pos' => $school->kode_pos,
            'telepon' => $school->telepon,
            'email' => $school->email,
            'website' => $this->normalizeUrl($school->website),
            'school_data_source' => $school->sumber_data_sekolah ?: implode(' + ', $result['sources'] ?? []),
            'school_data_fetched_at' => now(),
        ])->reject(fn ($value) => blank($value))->all();

        $setting->forceFill($payload)->save();

        activity()
            ->performedOn($setting)
            ->causedBy(auth()->user())
            ->withProperties([
                'npsn' => $setting->npsn,
                'nsm' => $setting->nsm,
                'sumber' => $setting->school_data_source,
            ])
            ->log('Autofill identitas sekolah dari referensi pemerintah');

        return array_merge($result, [
            'success' => true,
            'complete' => filled($setting->nsm),
            'partial' => blank($setting->nsm),
            'message' => filled($setting->nsm)
                ? 'Identitas sekolah berhasil dilengkapi dari referensi pemerintah.'
                : ($result['message'] ?? 'Data sekolah berhasil diambil, tetapi NSM masih perlu dilengkapi.'),
            'data' => [
                'npsn' => $setting->npsn,
                'nsm' => $setting->nsm,
                'nama_sekolah' => $setting->nama_sekolah,
                'alamat' => $setting->alamat,
                'rt' => $setting->rt,
                'rw' => $setting->rw,
                'provinsi_code' => $setting->provinsi_code,
                'kota_code' => $setting->kota_code,
                'kecamatan_code' => $setting->kecamatan_code,
                'kelurahan_code' => $setting->kelurahan_code,
                'kode_pos' => $setting->kode_pos,
                'telepon' => $setting->telepon,
                'email' => $setting->email,
                'website' => $setting->website,
                'source' => $setting->school_data_source,
            ],
        ]);
    }

    private function findRegion($query, ?string $name)
    {
        $name = $this->normalizeRegionName($name);
        if ($name === '') {
            return null;
        }

        return $query->get()->map(function ($region) use ($name) {
            $candidate = $this->normalizeRegionName($region->name);
            similar_text($name, $candidate, $score);

            return ['region' => $region, 'score' => $candidate === $name ? 200 : $score];
        })->sortByDesc('score')
            ->first(fn (array $match) => $match['score'] >= 80)['region'] ?? null;
    }

    private function normalizeRegionName(?string $name): string
    {
        $name = mb_strtoupper(trim((string) $name));
        $name = preg_replace('/\b(PROVINSI|KABUPATEN|KAB\.|KOTA|KECAMATAN|KEC\.|DESA|KELURAHAN)\b/u', '', $name);

        return trim(preg_replace('/[^\pL\pN]+/u', ' ', $name));
    }

    private function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        return preg_match('#^https?://#i', $url) ? $url : "https://{$url}";
    }
}
