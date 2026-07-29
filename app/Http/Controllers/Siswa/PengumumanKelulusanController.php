<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\PengumumanKelulusan;
use App\Services\StudentGraduationAccessService;
use App\Support\ClientRequest;
use Illuminate\Http\Request;

class PengumumanKelulusanController extends Controller
{
    public function index(StudentGraduationAccessService $accessService)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $kelasAktif = $accessService->resolveAnnouncementEnrollment($siswa);

        if (!$kelasAktif) {
            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Pengumuman kelulusan belum tersedia untuk akun atau periode ini.');
        }

        $setting = AppSetting::getInstance();
        $tahunAktif = $kelasAktif->tahunPelajaran;
        $startsAt = $setting->graduation_announcement_starts_at;
        $announcement = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.kelulusan-pengumuman.index', [
            'siswa' => $siswa,
            'kelasAktif' => $kelasAktif,
            'tahunAktif' => $tahunAktif,
            'announcement' => $announcement,
            'setting' => $setting,
            'startsAt' => $startsAt,
            'isScheduledOpen' => true,
        ]);
    }

    public function openEnvelope(Request $request, StudentGraduationAccessService $accessService)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 403);
        }

        $kelasAktif = $accessService->resolveAnnouncementEnrollment($siswa);
        if (!$kelasAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Pengumuman kelulusan belum tersedia untuk akun atau periode ini.',
            ], 403);
        }

        $announcement = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $kelasAktif->tahun_pelajaran_id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil kelulusan Anda belum ditetapkan oleh admin.',
            ], 404);
        }

        if (!$announcement->opened_at) {
            $announcement->update([
                'opened_at' => now(),
                'opened_ip' => ClientRequest::ip($request),
                'opened_user_agent' => substr((string) $request->userAgent(), 0, 65535),
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $announcement->status,
            'opened_at' => optional($announcement->fresh()->opened_at)->format('d M Y H:i'),
        ]);
    }
}
