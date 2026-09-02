<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class KemendikdasmenSchoolProfileService
{
    private const PROFILE_ENDPOINT = 'https://sekolah.data.kemendikdasmen.go.id/v1/sekolah-service/sekolah/full-detail/';

    public function getProfile(string $schoolId): array
    {
        $schoolId = strtoupper(trim($schoolId));

        if (! $this->isUuid($schoolId)) {
            return $this->failure('invalid_school_id', 'ID sekolah tidak valid.');
        }

        try {
            $request = Http::acceptJson()->connectTimeout(6)->timeout(12);
            if (! app()->environment('production')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get(self::PROFILE_ENDPOINT.rawurlencode($schoolId));
            Log::info('Kemendikdasmen school profile response', ['school_id' => $schoolId, 'status' => $response->status()]);

            if ($response->status() === 429) {
                return $this->failure('rate_limited', 'Layanan profil sekolah sedang membatasi permintaan. Silakan coba lagi nanti.');
            }

            if ($response->serverError()) {
                return $this->failure('upstream_error', 'Layanan profil sekolah sedang bermasalah. Silakan coba lagi nanti.');
            }

            if (! $response->successful()) {
                return $this->failure('not_found', 'Profil sekolah tidak tersedia pada sumber referensi.');
            }

            $school = $response->json('data.sekolah.0');
            if (! is_array($school)) {
                return $this->failure('invalid_response', 'Respons profil sekolah tidak sesuai format yang dikenali.');
            }

            return [
                'success' => true,
                'code' => 'found',
                'message' => 'Profil sekolah ditemukan.',
                'data' => [
                    'nama' => $school['nama'] ?? null,
                    'npsn' => $school['npsn'] ?? null,
                    'alamat' => $this->address($school),
                    'bentuk_pendidikan' => $school['bentuk_pendidikan'] ?? null,
                    'status_sekolah' => $school['status_sekolah'] ?? null,
                    'source_url' => 'https://sekolah.data.kemendikdasmen.go.id/profil-sekolah/'.$schoolId,
                ],
            ];
        } catch (ConnectionException) {
            return $this->failure('connection_error', 'Tidak dapat terhubung ke layanan profil sekolah.');
        } catch (Throwable $exception) {
            Log::error('Kemendikdasmen school profile lookup failed', [
                'school_id' => $schoolId,
                'exception' => $exception::class,
            ]);

            return $this->failure('unexpected_error', 'Terjadi kesalahan saat mengambil profil sekolah.');
        }
    }

    private function address(array $school): ?string
    {
        $street = collect([
            $school['alamat_jalan'] ?? null,
            $school['nama_dusun'] ?? null,
            filled($school['rt'] ?? null) || filled($school['rw'] ?? null) ? 'RT '.($school['rt'] ?? '-').'/RW '.($school['rw'] ?? '-') : null,
        ])->filter()->implode(', ');

        $location = collect([
            $school['kecamatan'] ?? null,
            $school['kabupaten'] ?? null,
            $school['provinsi'] ?? null,
            $school['kode_pos'] ?? null,
        ])->filter()->implode(', ');

        return filled($street) && filled($location) ? $street.', '.$location : ($street ?: ($location ?: null));
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/i', $value);
    }

    private function failure(string $code, string $message): array
    {
        return ['success' => false, 'code' => $code, 'message' => $message, 'data' => null];
    }
}
