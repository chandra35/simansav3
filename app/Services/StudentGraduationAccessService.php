<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\PengumumanKelulusan;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;

class StudentGraduationAccessService
{
    public function canAccessAnnouncement(Siswa $siswa): bool
    {
        return $this->resolveAnnouncementEnrollment($siswa) !== null;
    }

    public function resolveAnnouncementEnrollment(Siswa $siswa): ?SiswaKelas
    {
        $setting = AppSetting::getInstance();
        $tahunAktif = TahunPelajaran::query()->active()->first();

        if (
            ! $setting->graduation_announcement_enabled
            || ! $tahunAktif
            || $setting->graduation_announcement_tahun_pelajaran_id !== $tahunAktif->id
            || ($setting->graduation_announcement_starts_at
                && now()->lessThan($setting->graduation_announcement_starts_at))
        ) {
            return null;
        }

        $enrollment = $this->class12Enrollment($siswa->id, $tahunAktif->id, true);

        if (! $enrollment) {
            return null;
        }

        $hasResult = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        return $hasResult ? $enrollment : null;
    }

    public function canAccessLulusanData(Siswa $siswa): bool
    {
        return $this->resolveLulusanEnrollment($siswa) !== null;
    }

    public function resolveLulusanEnrollment(Siswa $siswa): ?SiswaKelas
    {
        if (in_array($siswa->status_siswa, ['lulus', 'alumni'], true)) {
            return $this->latestClass12History($siswa->id);
        }

        $setting = AppSetting::getInstance();
        $tahunId = $setting->lulusan_data_tahun_pelajaran_id;

        if (
            ! $setting->lulusan_data_enabled
            || ! $tahunId
            || ($setting->lulusan_data_starts_at && now()->lessThan($setting->lulusan_data_starts_at))
            || ($setting->lulusan_data_ends_at && now()->greaterThan($setting->lulusan_data_ends_at))
        ) {
            return null;
        }

        return $this->class12Enrollment($siswa->id, $tahunId, true);
    }

    private function class12Enrollment(string $siswaId, string $tahunId, bool $activeOnly): ?SiswaKelas
    {
        return SiswaKelas::query()
            ->with(['kelas.jurusan', 'tahunPelajaran'])
            ->where('siswa_id', $siswaId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->when($activeOnly, fn ($query) => $query->where('status', 'aktif'))
            ->whereNull('deleted_at')
            ->whereHas('kelas', fn ($query) => $query->where('tingkat', 12))
            ->latest('created_at')
            ->first();
    }

    private function latestClass12History(string $siswaId): ?SiswaKelas
    {
        return SiswaKelas::query()
            ->with(['kelas.jurusan', 'tahunPelajaran'])
            ->where('siswa_id', $siswaId)
            ->whereNull('deleted_at')
            ->whereHas('kelas', fn ($query) => $query->where('tingkat', 12))
            ->get()
            ->sortByDesc(fn (SiswaKelas $record) => $record->tahunPelajaran?->tahun_mulai ?? 0)
            ->first();
    }
}
