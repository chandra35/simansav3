<?php

namespace App\Services;

use App\Models\Sekolah;
use Illuminate\Support\Facades\Log;

class SekolahDataEnrichmentService
{
    public function __construct(
        protected KemendikbudApiService $kemendikbud,
        protected EmisNisnService $emis
    ) {
    }

    public function enrich(Sekolah $sekolah): array
    {
        $sources = [];
        $warnings = [];

        $referensi = $this->kemendikbud->fetchAndSaveFromReferensi($sekolah->npsn);
        if ($referensi['success'] ?? false) {
            $sources[] = 'Referensi Kemendikdasmen';
            $sekolah = $referensi['data']->fresh();
        } else {
            $warnings[] = $referensi['message'] ?? 'Data Referensi Kemendikdasmen belum berhasil diambil.';
        }

        if ($this->shouldCheckEmis($sekolah)) {
            $emis = $this->emis->lookupInstitutionByNpsn($sekolah->npsn);

            if ($emis['success'] ?? false) {
                $this->fillFromEmis($sekolah, (array) ($emis['data'] ?? []));
                $sources[] = 'EMIS Kemenag';
                $sekolah->refresh();
            } else {
                $warnings[] = $emis['message'] ?? 'Data EMIS belum berhasil diambil.';
            }
        }

        $sekolah->forceFill([
            'sumber_data_sekolah' => implode(' + ', array_unique($sources)) ?: $sekolah->sumber_data_sekolah,
            'last_fetched_at' => now(),
        ])->save();

        return [
            'success' => !empty($sources),
            'message' => !empty($sources)
                ? 'Data sekolah berhasil dilengkapi dari ' . implode(' dan ', array_unique($sources)) . '.'
                : 'Data sekolah belum berhasil dilengkapi.',
            'sources' => array_values(array_unique($sources)),
            'warnings' => $warnings,
            'data' => $sekolah->fresh(),
        ];
    }

    protected function shouldCheckEmis(Sekolah $sekolah): bool
    {
        $text = strtolower(trim(implode(' ', [
            $sekolah->nama,
            $sekolah->bentuk_pendidikan,
            $sekolah->kementerian_pembina,
        ])));

        return str_contains($text, 'mts')
            || str_contains($text, 'madrasah')
            || str_contains($text, 'kementerian agama')
            || str_contains($text, 'mi ')
            || str_contains($text, 'ma ');
    }

    protected function fillFromEmis(Sekolah $sekolah, array $data): void
    {
        $location = (array) ($data['location'] ?? []);
        $type = (array) ($data['type'] ?? []);
        $category = (array) ($data['category'] ?? []);

        $payload = [
            'nsm' => $data['nsm'] ?? ($data['statistic_num'] ?? null),
            'nama' => $data['name'] ?? null,
            'bentuk_pendidikan' => $type['name'] ?? ($type['full_name'] ?? null),
            'jenjang_pendidikan' => $category['name'] ?? null,
            'kementerian_pembina' => 'Kementerian Agama',
            'alamat_jalan' => $location['address'] ?? null,
            'rt' => $location['rt'] ?? null,
            'rw' => $location['rw'] ?? null,
            'desa_kelurahan' => data_get($location, 'sub_district.sub_district') ?? ($location['village_name'] ?? null),
            'kecamatan' => data_get($location, 'district.district'),
            'kabupaten_kota' => data_get($location, 'city.city'),
            'provinsi' => data_get($location, 'province.province'),
            'kode_pos' => $location['postal_code'] ?? null,
            'lintang' => $this->coordinate($location['latitude'] ?? null),
            'bujur' => $this->coordinate($location['longitude'] ?? null),
            'last_fetched_at' => now(),
        ];

        $payload = collect($payload)
            ->reject(fn ($value) => blank($value))
            ->all();

        try {
            $sekolah->fill($payload)->save();
        } catch (\Throwable $exception) {
            Log::warning('Gagal menyimpan data sekolah dari EMIS', [
                'npsn' => $sekolah->npsn,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function coordinate($value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $value = str_replace(',', '.', (string) $value);

        return is_numeric($value) ? (float) $value : null;
    }
}
