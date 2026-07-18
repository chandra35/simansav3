<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class EmisInstitutionTokenService
{
    public function status(): array
    {
        $record = DB::table('api_tokens')->where('name', 'emis_institusi_token')->first();

        if (! $record || blank($record->token)) {
            return $this->unavailable('missing', 'Token EMIS Lembaga belum dikonfigurasi.');
        }

        $payload = $this->decodePayload($record->token);
        if (! $payload) {
            return $this->unavailable('invalid', 'Format token EMIS Lembaga tidak valid.');
        }

        $institutionId = $payload['institution_id']
            ?? $payload['institutionId']
            ?? $payload['lembaga_id']
            ?? $payload['identifiable_id']
            ?? null;
        $expiresAt = isset($payload['exp'])
            ? CarbonImmutable::createFromTimestamp((int) $payload['exp'], config('app.timezone'))
            : null;

        if (! $institutionId) {
            return $this->unavailable('invalid', 'Institution ID tidak ditemukan pada token EMIS Lembaga.');
        }

        if (! $expiresAt) {
            return $this->unavailable('invalid', 'Waktu kedaluwarsa tidak ditemukan pada token EMIS Lembaga.');
        }

        $now = CarbonImmutable::now();
        $expired = $expiresAt->isPast();
        $minutesLeft = max(0, $now->diffInMinutes($expiresAt, false));

        return [
            'state' => $expired ? 'expired' : ($minutesLeft <= 30 ? 'expiring' : 'active'),
            'usable' => ! $expired,
            'message' => $expired
                ? 'Token EMIS Lembaga sudah kedaluwarsa.'
                : ($minutesLeft <= 30 ? 'Token EMIS Lembaga akan segera kedaluwarsa.' : 'Token EMIS Lembaga aktif.'),
            'token' => $record->token,
            'institution_id' => (int) $institutionId,
            'expires_at' => $expiresAt,
            'minutes_left' => $minutesLeft,
            'updated_at' => $record->updated_at ? CarbonImmutable::parse($record->updated_at) : null,
        ];
    }

    private function decodePayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return null;
        }

        $data = json_decode($decoded, true);

        return is_array($data) ? $data : null;
    }

    private function unavailable(string $state, string $message): array
    {
        return [
            'state' => $state,
            'usable' => false,
            'message' => $message,
            'token' => null,
            'institution_id' => null,
            'expires_at' => null,
            'minutes_left' => 0,
            'updated_at' => null,
        ];
    }
}
