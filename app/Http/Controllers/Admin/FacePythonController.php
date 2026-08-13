<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class FacePythonController extends Controller
{
    public function index(): View
    {
        $token = $this->ensureToken();

        return view('admin.absensi.face-python', [
            'deviceToken' => $token,
            'apiBaseUrl' => url('/face-python-api'),
            'deviceStatus' => $this->statusForToken($token),
        ]);
    }

    public function status(): JsonResponse
    {
        $token = $this->ensureToken();

        return response()->json([
            'success' => true,
            'status' => $this->statusForToken($token),
        ])->header('Cache-Control', 'no-store, private, max-age=0');
    }

    public function rotateToken(): RedirectResponse
    {
        $setting = AbsensiSetting::firstOrCreate(
            ['key' => 'face_python_device_token'],
            $this->tokenSetting(Str::random(64))
        );
        $setting->update(['value' => Str::random(64)]);

        return redirect()->route('admin.absensi.face-python')
            ->with('success', 'Token Face Python berhasil dirotasi. Agent dengan token lama langsung ditolak.');
    }

    public function downloadAgent(): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 503, 'Ekstensi ZIP belum tersedia pada server.');

        $source = base_path('tools/face-python-agent');
        abort_unless(is_dir($source), 404, 'Paket Face Python belum tersedia.');

        $target = tempnam(sys_get_temp_dir(), 'simansa-face-python-');
        $zip = new ZipArchive();
        abort_unless($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Paket agent gagal dibuat.');

        $filenames = ['agent.py', 'requirements.txt', 'config.example.json', 'PANDUAN.txt'];
        foreach ($filenames as $filename) {
            $path = $source.DIRECTORY_SEPARATOR.$filename;
            if (! is_file($path) || ! $zip->addFile($path, 'simansa-face-python/'.$filename)) {
                $zip->close();
                @unlink($target);
                throw new RuntimeException("File paket {$filename} gagal ditambahkan.");
            }
        }
        if (! $zip->close()) {
            @unlink($target);
            throw new RuntimeException('Paket Face Python gagal disimpan.');
        }

        $check = new ZipArchive();
        $checkResult = $check->open($target, ZipArchive::CHECKCONS);
        if ($checkResult !== true || $check->numFiles !== count($filenames)) {
            if ($checkResult === true) {
                $check->close();
            }
            @unlink($target);
            throw new RuntimeException('Paket Face Python gagal melewati pemeriksaan integritas.');
        }
        $check->close();

        return response()->download($target, 'simansa-face-python-agent.zip', [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store, private, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    private function ensureToken(): string
    {
        $setting = AbsensiSetting::firstOrCreate(
            ['key' => 'face_python_device_token'],
            $this->tokenSetting(Str::random(64))
        );

        if (strlen((string) $setting->value) < 32) {
            $setting->update(['value' => Str::random(64)]);
        }

        return (string) $setting->fresh()->value;
    }

    private function tokenSetting(string $token): array
    {
        return [
            'value' => $token,
            'type' => 'string',
            'group' => 'kiosk',
            'label' => 'Token Perangkat Face Python',
            'description' => 'Token rahasia untuk pairing Python Edge Agent pada PC kamera gerbang.',
        ];
    }

    private function statusForToken(string $token): array
    {
        $status = Cache::get('face-python:device:'.hash('sha256', $token));
        if (! is_array($status)) {
            return ['online' => false, 'message' => 'Agent belum pernah terhubung.'];
        }

        $lastSeen = isset($status['last_seen']) ? Carbon::parse($status['last_seen']) : null;
        $status['online'] = ($lastSeen?->greaterThan(now()->subSeconds(35)) ?? false)
            && ! in_array($status['state'] ?? null, ['stopped', 'error'], true);
        $status['last_seen_human'] = $lastSeen?->diffForHumans() ?? '-';

        return $status;
    }
}
