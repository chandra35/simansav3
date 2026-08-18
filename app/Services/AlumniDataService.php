<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\SiswaKelas;

class AlumniDataService
{
    public function syncLegacyGraduates(): array
    {
        $result = ['created' => 0, 'updated' => 0];

        SiswaKelas::query()->with(['siswa.user', 'tahunPelajaran'])
            ->where('status', 'lulus')->orderBy('tanggal_keluar')->cursor()
            ->each(function (SiswaKelas $record) use (&$result) {
                $siswa = $record->siswa;
                if (!$siswa) return;

                $existing = AlumniProfile::where('siswa_id', $siswa->id)->first();
                AlumniProfile::updateOrCreate(['siswa_id' => $siswa->id], [
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
                ]);
                $result[$existing ? 'updated' : 'created']++;
            });

        return $result;
    }
}
