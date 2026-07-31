<?php

namespace App\Imports;

use App\Models\AsramaSantri;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AsramaNomorIndukImport implements ToCollection, WithHeadingRow
{
    protected array $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $nisn = trim((string) ($row['nisn'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $nomorInduk = trim((string) ($row['nomor_induk'] ?? ''));

            if ($nisn === '' && $nomorInduk === '') {
                continue;
            }

            if ($nisn === '') {
                $this->fail($rowNumber, $nisn, $nama, 'NISN wajib diisi');

                continue;
            }

            if ($nomorInduk === '') {
                $this->fail($rowNumber, $nisn, $nama, 'Nomor induk wajib diisi');

                continue;
            }

            $santri = AsramaSantri::whereHas('siswa', fn ($q) => $q->where('nisn', $nisn))->first();

            if (! $santri) {
                $this->fail($rowNumber, $nisn, $nama, 'Santri dengan NISN ini belum diaktifkan di Asrama');

                continue;
            }

            $validator = Validator::make(['nomor_induk' => $nomorInduk], [
                'nomor_induk' => [Rule::unique('asrama_santri', 'nomor_induk_asrama')->ignore($santri->id)],
            ], [], ['nomor_induk' => 'nomor induk']);

            if ($validator->fails()) {
                $this->fail($rowNumber, $nisn, $nama, 'Nomor induk "'.$nomorInduk.'" sudah dipakai santri lain');

                continue;
            }

            $santri->update(['nomor_induk_asrama' => $nomorInduk]);
            $this->results['success']++;
        }
    }

    protected function fail(int $row, string $nisn, string $nama, string $error): void
    {
        $this->results['failed']++;
        $this->results['errors'][] = [
            'row' => $row,
            'nisn' => $nisn ?: '-',
            'nama' => $nama ?: '-',
            'error' => $error,
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
