<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\PengumumanKelulusan;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class PengumumanKelulusanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $setting = AppSetting::getInstance();
        if (!$setting->graduation_announcement_enabled) {
            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Pengumuman kelulusan belum dibuka oleh admin.');
        }

        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->first();
        if (!$tahunAktif) {
            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Tahun ajaran aktif belum tersedia.');
        }

        $kelasAktif = SiswaKelas::query()
            ->with(['kelas', 'tahunPelajaran'])
            ->where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('status', 'aktif')
            ->whereNull('deleted_at')
            ->whereHas('kelas', function ($query) {
                $query->where('tingkat', 12);
            })
            ->latest('created_at')
            ->first();

        if (!$kelasAktif) {
            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Menu pengumuman kelulusan hanya tersedia untuk siswa kelas 12 pada tahun ajaran aktif.');
        }

        $announcement = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.kelulusan-pengumuman.index', [
            'siswa' => $siswa,
            'kelasAktif' => $kelasAktif,
            'tahunAktif' => $tahunAktif,
            'announcement' => $announcement,
        ]);
    }

    public function openEnvelope(Request $request)
    {
        $user = auth()->user();
        $siswa = $user?->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 403);
        }

        $setting = AppSetting::getInstance();
        if (!$setting->graduation_announcement_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Pengumuman kelulusan belum dibuka oleh admin.',
            ], 403);
        }

        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->first();
        if (!$tahunAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran aktif belum tersedia.',
            ], 404);
        }

        $announcement = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
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
                'opened_ip' => $request->ip(),
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
