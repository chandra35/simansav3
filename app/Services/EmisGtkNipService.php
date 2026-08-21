<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmisGtkNipService
{
    public const CREDENTIAL_NAME = 'emisgtk_session_cookie';

    private const ENDPOINT = 'https://emisgtk.kemenag.go.id/kepegawaian/verval-kepegawaian/ptk/asn/preview-simpeg/';

    private const REFERER = 'https://emisgtk.kemenag.go.id/kepegawaian/verval-kepegawaian/ptk/asn/ubah-nip/';

    private const ALLOWED_COOKIES = ['cookiesession1', 'csrftoken', 'emisSSO', 'sessionid'];

    private ?string $sessionCookie;

    public function __construct(?string $sessionCookie = null)
    {
        $this->sessionCookie = $sessionCookie ?? $this->loadCredential();
    }

    public function isConfigured(): bool
    {
        return filled($this->sessionCookie);
    }

    public static function normalizeCookieHeader(string $header): string
    {
        $header = trim(preg_replace('/^cookie\s*:\s*/i', '', trim($header)) ?? '');
        $cookies = [];

        foreach (explode(';', $header) as $part) {
            [$name, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($value !== null && in_array($name, self::ALLOWED_COOKIES, true) && trim($value) !== '') {
                $cookies[$name] = trim($value);
            }
        }

        if (! isset($cookies['emisSSO'], $cookies['sessionid'])) {
            throw new \InvalidArgumentException('Cookie harus memuat emisSSO dan sessionid dari sesi EMIS GTK yang masih aktif.');
        }

        return collect(self::ALLOWED_COOKIES)
            ->filter(fn (string $name) => isset($cookies[$name]))
            ->map(fn (string $name) => $name.'='.$cookies[$name])
            ->implode('; ');
    }

    public function check(string $nip): array
    {
        if (! preg_match('/^\d{18}$/', $nip)) {
            return $this->failure('invalid_nip', 'NIP harus terdiri dari tepat 18 digit.');
        }

        if (! $this->isConfigured()) {
            return $this->failure('credential_missing', 'Sesi EMIS GTK belum dikonfigurasi. Perbarui melalui menu Update API Token.');
        }

        try {
            $request = Http::acceptJson()
                ->connectTimeout(8)
                ->timeout(25)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders([
                    'Cookie' => $this->sessionCookie,
                    'Referer' => self::REFERER,
                    'X-Requested-With' => 'XMLHttpRequest',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',
                ]);

            if (! app()->environment('production')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get(self::ENDPOINT, ['nip' => $nip]);
            $contentType = strtolower((string) $response->header('Content-Type'));

            Log::info('EMIS GTK NIP response', [
                'nip' => self::maskNip($nip),
                'status' => $response->status(),
                'content_type' => $contentType,
            ]);

            if ($response->redirect() || in_array($response->status(), [401, 403, 419], true)
                || str_contains($contentType, 'text/html')) {
                return $this->failure('session_expired', 'Sesi EMIS GTK sudah kedaluwarsa atau tidak diterima. Salin ulang Cookie dari sesi EMIS GTK yang aktif.');
            }

            if ($response->status() === 429) {
                return $this->failure('rate_limited', 'Permintaan ke EMIS GTK terlalu banyak. Tunggu beberapa saat lalu coba kembali.');
            }

            if ($response->serverError()) {
                return $this->failure('upstream_error', 'Layanan EMIS GTK sedang bermasalah. Silakan coba lagi nanti.');
            }

            if (! $response->successful()) {
                return $this->failure('request_rejected', 'Permintaan ditolak oleh EMIS GTK (HTTP '.$response->status().').');
            }

            $payload = $response->json();
            if (! is_array($payload) || ! isset($payload['validation'], $payload['simpeg_data'])) {
                return $this->failure('invalid_response', 'Respons EMIS GTK tidak sesuai format yang dikenali.');
            }

            return [
                'success' => true,
                'code' => 'found',
                'message' => 'Data NIP berhasil diambil dari EMIS GTK / SIMPEG.',
                'data' => $this->normalizePayload($payload),
            ];
        } catch (ConnectionException) {
            return $this->failure('connection_error', 'Tidak dapat terhubung ke EMIS GTK. Silakan coba lagi.');
        } catch (Throwable $exception) {
            Log::error('EMIS GTK NIP check failed', [
                'nip' => self::maskNip($nip),
                'exception' => $exception::class,
            ]);

            return $this->failure('unexpected_error', 'Terjadi kesalahan saat memeriksa NIP melalui EMIS GTK.');
        }
    }

    public static function maskNip(string $nip): string
    {
        return strlen($nip) >= 8 ? substr($nip, 0, 4).str_repeat('*', strlen($nip) - 8).substr($nip, -4) : '***';
    }

    private function loadCredential(): ?string
    {
        $encrypted = DB::table('api_tokens')->where('name', self::CREDENTIAL_NAME)->value('token');
        if (! filled($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            Log::warning('Kredensial sesi EMIS GTK tidak dapat didekripsi.');

            return null;
        }
    }

    private function normalizePayload(array $payload): array
    {
        $validation = $payload['validation'];
        $simpeg = $payload['simpeg_data'];

        return [
            'nip' => (string) ($payload['nip'] ?? ''),
            'validation' => [
                'is_valid' => (bool) ($validation['is_valid'] ?? false),
                'name_match' => (bool) ($validation['name_match'] ?? false),
                'can_confirm_name' => (bool) ($validation['can_confirm_name'] ?? false),
                'name_similarity' => (float) ($validation['name_similarity'] ?? 0),
                'birth_date_match' => (bool) ($validation['birth_date_match'] ?? false),
                'can_claim_birth_date' => (bool) ($validation['can_claim_birth_date'] ?? false),
                'can_continue_with_confirmation' => (bool) ($validation['can_continue_with_confirmation'] ?? false),
                'nama_ptk' => $validation['nama_ptk'] ?? null,
                'nama_simpeg' => $validation['nama_simpeg'] ?? null,
                'tgl_lahir_ptk' => $validation['tgl_lahir_ptk'] ?? null,
                'tgl_lahir_simpeg' => $validation['tgl_lahir_simpeg'] ?? null,
                'tgl_lahir_simpeg_display' => $validation['tgl_lahir_simpeg_display'] ?? null,
            ],
            'simpeg' => [
                'status_pegawai' => $simpeg['status_pegawai'] ?? null,
                'golongan' => $simpeg['golongan'] ?? null,
                'tmt_golongan' => $simpeg['tmt_golongan'] ?? null,
                'mk_golongan' => (int) ($simpeg['mk_golongan'] ?? 0),
                'mk_golongan_bulan' => (int) ($simpeg['mk_golongan_bulan'] ?? 0),
                'gaji_pokok' => (float) ($simpeg['gaji_pokok'] ?? 0),
                'gaji_pokok_source' => $simpeg['gaji_pokok_source'] ?? null,
                'gaji_pokok_source_label' => $simpeg['gaji_pokok_source_label'] ?? null,
                'unit_kerja' => $simpeg['unit_kerja'] ?? null,
                'jabatan' => $simpeg['jabatan'] ?? null,
                'pendidikan' => $simpeg['pendidikan'] ?? null,
                'jenjang_pendidikan' => $simpeg['jenjang_pendidikan'] ?? null,
            ],
        ];
    }

    private function failure(string $code, string $message): array
    {
        return ['success' => false, 'code' => $code, 'message' => $message, 'data' => null];
    }
}
