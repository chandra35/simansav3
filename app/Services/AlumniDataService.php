<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\SiswaLulusan;

class AlumniDataService
{
    public function syncLegacyGraduates(): array
    {
        $result = ['created' => 0, 'updated' => 0];

        SiswaKelas::query()->with([
            'siswa.user',
            'siswa.dataLulusan.snbpRegistration',
            'siswa.dataLulusan.spanPtkinRegistration',
            'tahunPelajaran',
        ])
            ->where('status', 'lulus')->orderBy('tanggal_keluar')->cursor()
            ->each(function (SiswaKelas $record) use (&$result) {
                $result[$this->syncRecord($record) ? 'updated' : 'created']++;
            });

        return $result;
    }

    public function syncStudent(Siswa $siswa): void
    {
        $record = SiswaKelas::query()->with([
            'siswa.user',
            'siswa.dataLulusan.snbpRegistration',
            'siswa.dataLulusan.spanPtkinRegistration',
            'tahunPelajaran',
        ])->where('siswa_id', $siswa->id)
            ->where('status', 'lulus')
            ->orderByDesc('tanggal_keluar')
            ->first();

        if ($record) {
            $this->syncRecord($record);
        }
    }

    private function syncRecord(SiswaKelas $record): bool
    {
        $siswa = $record->siswa;
        if (!$siswa) return false;

        $existing = AlumniProfile::where('siswa_id', $siswa->id)->first();
        $dataLulusan = $siswa->dataLulusan
            ->firstWhere('tahun_pelajaran_id', $record->tahun_pelajaran_id)
            ?? $siswa->dataLulusan->sortByDesc('created_at')->first();
        $tracking = $this->buildGraduationTracking($dataLulusan);

        $payload = [
                    'angkatan' => $record->tahunPelajaran?->nama,
                    'tahun_lulus' => $record->tahunPelajaran?->tahun_selesai,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'nik' => $siswa->nik,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'tempat_lahir' => $siswa->tempat_lahir,
                    'tanggal_lahir' => $siswa->tanggal_lahir,
                    'nomor_hp' => $siswa->nomor_hp,
                    'email' => $siswa->user?->email,
                    'alamat' => $siswa->alamat_siswa,
                    'status_setelah_lulus' => $existing?->status_setelah_lulus ?? 'belum_terdata',
                    'status_verifikasi' => $existing?->status_verifikasi ?? 'belum_diverifikasi',
                    'sumber_data' => 'simansa',
                    'referensi_sumber' => 'Sinkronisasi riwayat kelulusan SIMANSA',
                    'tracking_lulusan' => $tracking,
                    'tracking_lulusan_updated_at' => $tracking ? now() : null,
        ];

        // Profil alumni adalah kondisi terkini. Data tracker hanya menjadi nilai awal
        // jika belum diisi, sehingga perubahan manual tidak tertimpa saat sinkronisasi.
        if ($tracking) {
            if (!$existing || blank($existing->institusi_lanjutan)) {
                $payload['institusi_lanjutan'] = $tracking['nama_universitas'];
            }
            if (!$existing || blank($existing->program_studi)) {
                $payload['program_studi'] = $tracking['program_studi'];
            }
            if (!$existing || $existing->status_setelah_lulus === 'belum_terdata') {
                $payload['status_setelah_lulus'] = 'kuliah';
            }
        }

        AlumniProfile::updateOrCreate(['siswa_id' => $siswa->id], $payload);
        return (bool) $existing;
    }

    private function buildGraduationTracking(?SiswaLulusan $dataLulusan): ?array
    {
        if (!$dataLulusan) return null;

        $snbpStatus = $dataLulusan->snbpRegistration?->check_status;
        $spanStatus = $dataLulusan->spanPtkinRegistration?->check_status;
        $checkerStatus = $snbpStatus === 'lulus' || $spanStatus === 'lulus'
            ? 'lulus'
            : ($snbpStatus ?: $spanStatus);

        return [
            'siswa_lulusan_id' => $dataLulusan->id,
            'tahun_pelajaran_id' => $dataLulusan->tahun_pelajaran_id,
            'jalur_masuk' => $dataLulusan->jalur_masuk,
            'nama_universitas' => $dataLulusan->nama_universitas_manual ?: $dataLulusan->nama_universitas,
            'jurusan_fakultas' => $dataLulusan->jurusan_fakultas,
            'program_studi' => $dataLulusan->program_studi_manual ?: $dataLulusan->program_studi,
            'keterangan' => $dataLulusan->keterangan,
            'status_checker' => $checkerStatus,
            'status_snbp' => $snbpStatus,
            'status_span_ptkin' => $spanStatus,
            'terakhir_diperbarui' => optional($dataLulusan->updated_at)->toIso8601String(),
        ];
    }
}
