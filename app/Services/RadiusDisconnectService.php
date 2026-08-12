<?php

namespace App\Services;

use App\Models\HotspotRadiusNas;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RadiusDisconnectService
{
    public function disconnect(HotspotRadiusNas $nas, object $session): array
    {
        $helper = config('hotspot.disconnect_helper');
        if (!$helper || !is_file($helper)) {
            return $this->failed('Helper pemutusan RADIUS belum tersedia di server SIMANSA.');
        }

        try {
            $process = new Process([
                'sudo',
                '-n',
                $helper,
                (string) $session->username,
                (string) ($session->framedipaddress ?? ''),
                (string) ($session->callingstationid ?? ''),
                (string) ($session->acctsessionid ?? ''),
                $nas->nasname,
            ]);
            $process->setTimeout((float) config('hotspot.disconnect_timeout', 8));
            $process->run();
        } catch (\Throwable $exception) {
            Log::error('[Hotspot] RADIUS disconnect process unavailable', [
                'username' => $session->username,
                'nas' => $nas->nasname,
                'error' => $exception->getMessage(),
            ]);

            return $this->failed('Layanan pemutusan sesi belum tersedia di server SIMANSA.');
        }

        if ($process->isSuccessful()) {
            return [
                'success' => true,
                'message' => 'Sesi aktif berhasil dihapus dari MikroTik. Pengguna harus login ulang.',
                'reauthentication_required' => true,
                'steps' => [
                    [
                        'key' => 'radius_disconnect',
                        'label' => 'RADIUS Disconnect',
                        'status' => 'success',
                        'detail' => 'MikroTik mengirim Disconnect-ACK.',
                    ],
                    [
                        'key' => 'hotspot_active',
                        'label' => 'Hotspot Active',
                        'status' => 'success',
                        'detail' => 'Sesi runtime dan dynamic queue dihapus oleh MikroTik.',
                    ],
                    [
                        'key' => 'dhcp_lease',
                        'label' => 'DHCP lease',
                        'status' => 'preserved',
                        'detail' => 'Lease dipertahankan agar perangkat segera diarahkan ke portal login.',
                    ],
                ],
            ];
        }

        $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
        Log::warning('[Hotspot] RADIUS disconnect failed', [
            'username' => $session->username,
            'nas' => $nas->nasname,
            'exit_code' => $process->getExitCode(),
            'error' => $error,
        ]);

        return $this->failed($this->safeError($error));
    }

    private function failed(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'reauthentication_required' => false,
            'steps' => [[
                'key' => 'radius_disconnect',
                'label' => 'RADIUS Disconnect',
                'status' => 'failed',
                'detail' => $message,
            ]],
        ];
    }

    private function safeError(string $error): string
    {
        return match (true) {
            str_contains($error, 'session-not-found') => 'Sesi sudah berakhir atau tidak ditemukan di MikroTik.',
            str_contains($error, 'disconnect-nak') => 'MikroTik menolak permintaan pemutusan sesi.',
            str_contains($error, 'radius-timeout') => 'MikroTik tidak merespons permintaan pemutusan sesi.',
            str_contains($error, 'nas-not-found') => 'Shared secret NAS tidak ditemukan di FreeRADIUS.',
            default => 'Sesi belum dapat diputus. Periksa koneksi FreeRADIUS ke MikroTik.',
        };
    }
}
