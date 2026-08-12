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
            return ['success' => false, 'message' => 'Helper pemutusan RADIUS belum tersedia di server SIMANSA.'];
        }

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

        if ($process->isSuccessful()) {
            return ['success' => true, 'message' => 'Sesi berhasil diputus oleh MikroTik.'];
        }

        $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
        Log::warning('[Hotspot] RADIUS disconnect failed', [
            'username' => $session->username,
            'nas' => $nas->nasname,
            'exit_code' => $process->getExitCode(),
            'error' => $error,
        ]);

        return [
            'success' => false,
            'message' => $this->safeError($error),
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
