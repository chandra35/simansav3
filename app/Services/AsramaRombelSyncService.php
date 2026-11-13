<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\Asrama;
use App\Models\AsramaKamarSantri;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaSantri;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;

class AsramaRombelSyncService
{
    /**
     * Samakan mirror asrama_kelas dengan rombel SIMANSA bertanda is_asrama,
     * lalu selaraskan keanggotaan santri dengan kelas SIMANSA terkini.
     */
    public function sync(?string $userId = null): void
    {
        $flagged = Kelas::where('is_asrama', true)->where('is_active', true)->get();
        $unit = $this->unit();
        $mirrors = [];
        foreach ($flagged as $kelas) {
            $mirrors[$kelas->id] = $this->mirrorFor($kelas, $unit, $userId);
        }

        AsramaKelas::where('is_active', true)->whereNotNull('kelas_id')
            ->whereNotIn('kelas_id', $flagged->pluck('id'))
            ->update(['is_active' => false]);

        $this->reconcileSantri($mirrors, $userId);
    }

    /**
     * Ikuti perpindahan kelas SIMANSA: naik kelas/pindah rombel diikuti otomatis,
     * pindah ke kelas reguler dikeluarkan dari rombel asrama,
     * siswa nonaktif (lulus/mutasi keluar) dinonaktifkan sebagai santri.
     */
    public function reconcileSantri(array $mirrors = [], ?string $userId = null): void
    {
        if (empty($mirrors)) {
            $flagged = Kelas::where('is_asrama', true)->where('is_active', true)->get();
            $unit = $this->unit();
            foreach ($flagged as $kelas) {
                $mirrors[$kelas->id] = $this->mirrorFor($kelas, $unit, $userId);
            }
        }

        AsramaSantri::with(['siswa.kelasTahunAktif', 'kelasAktif.kelas'])
            ->where('status', 'aktif')
            ->chunkById(200, function ($santriList) use ($mirrors, $userId): void {
                foreach ($santriList as $santri) {
                    $siswa = $santri->siswa;
                    if (! $siswa || $siswa->status_siswa !== 'aktif') {
                        $this->deactivateSantri($santri, $userId);
                        continue;
                    }
                    $kelasAktif = $siswa->kelasTahunAktif->first();
                    $mirror = ($kelasAktif && $kelasAktif->is_asrama) ? ($mirrors[$kelasAktif->id] ?? null) : null;
                    if ($mirror) {
                        if ($santri->kelasAktif?->asrama_kelas_id !== $mirror->id) {
                            $this->placeSantri($mirror, $santri, $userId);
                        }
                    } elseif ($santri->kelasAktif) {
                        // Jangan keluarkan santri jika di rombel asrama mandiri (custom)
                        // atau siswa bertanda is_asrama (santri di kelas reguler seperti SmartQ)
                        $currentAsramaKelas = $santri->kelasAktif->kelas;
                        if ($currentAsramaKelas && $currentAsramaKelas->kelas_id === null) {
                            continue;
                        }
                        if ($siswa->is_asrama) {
                            continue;
                        }

                        AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
                            'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
                        ]);
                    }
                }
            });
    }

    /**
     * Aktifkan siswa sebagai santri asrama, beri flag is_asrama, buat/aktifkan record AsramaSantri,
     * serta tempatkan ke rombel asrama target (atau mirror jika kelas regulernya is_asrama).
     */
    public function activateSantri(Siswa $siswa, ?string $userId = null, ?AsramaKelas $targetKelas = null): AsramaSantri
    {
        $userId = $userId ?: auth()->id();

        $siswa->update([
            'is_asrama' => true,
            'is_asrama_at' => $siswa->is_asrama_at ?: now(),
            'is_asrama_by' => $userId,
        ]);

        $unit = $this->unit();
        $santri = AsramaSantri::withTrashed()->firstOrNew(['siswa_id' => $siswa->id]);
        if ($santri->trashed()) {
            $santri->restore();
        }

        $santri->fill([
            'asrama_id' => $unit->id,
            'nomor_induk_asrama' => $santri->nomor_induk_asrama ?: $this->generateSantriNumber($siswa),
            'tanggal_masuk' => $santri->tanggal_masuk ?: now()->toDateString(),
            'tanggal_keluar' => null,
            'status' => 'aktif',
            'created_by' => $santri->created_by ?: $userId,
            'updated_by' => $userId ?: $santri->updated_by,
        ])->save();

        if ($targetKelas) {
            $this->placeSantri($targetKelas, $santri, $userId);
        } else {
            $kelasAktif = $siswa->kelasTahunAktif->first();
            if ($kelasAktif && $kelasAktif->is_asrama) {
                $mirror = $this->mirrorFor($kelasAktif, $unit, $userId);
                $this->placeSantri($mirror, $santri, $userId);
            }
        }

        $user = $siswa->relationLoaded('user') ? $siswa->user : ($siswa->user_id ? User::find($siswa->user_id) : null);
        if ($user) {
            app(AsramaAccessService::class)->syncStudent($user);
        }

        return $santri;
    }

    /**
     * Nonaktifkan santri (karena mutasi keluar, berhenti, atau pindah ke kelas reguler non-asrama).
     */
    public function deactivateSantri(AsramaSantri|Siswa $target, ?string $userId = null, string $status = 'mutasi_keluar'): void
    {
        $tanggal = now()->toDateString();
        $userId = $userId ?: auth()->id();
        $santri = $target instanceof AsramaSantri ? $target : AsramaSantri::where('siswa_id', $target->id)->first();
        $siswa = $target instanceof Siswa ? $target : $santri?->siswa;

        if ($siswa && $siswa->is_asrama) {
            $siswa->update([
                'is_asrama' => false,
                'is_asrama_at' => null,
                'is_asrama_by' => null,
            ]);
        }

        if ($santri) {
            AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
                'status' => in_array($status, ['keluar', 'mutasi_keluar', 'alumni'], true) ? $status : 'keluar',
                'tanggal_keluar' => $tanggal,
                'is_ketua_kelas' => false,
            ]);

            AsramaKamarSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
                'status' => 'keluar',
                'tanggal_keluar' => $tanggal,
            ]);

            $finalSantriStatus = $status === 'alumni' ? 'alumni' : 'nonaktif';
            $santri->update([
                'status' => $finalSantriStatus,
                'tanggal_keluar' => $santri->tanggal_keluar ?: $tanggal,
                'updated_by' => $userId ?: $santri->updated_by,
            ]);

            if ($siswa) {
                $user = $siswa->relationLoaded('user') ? $siswa->user : ($siswa->user_id ? User::find($siswa->user_id) : null);
                if ($user) {
                    app(AsramaAccessService::class)->syncStudent($user);
                }
            }
        }
    }

    /**
     * Proses kelulusan santri ke status alumni:
     * - Lepas kamar & tutup rombel
     * - Update status santri menjadi 'alumni'
     * - Tandai profil alumni sebagai santri asrama
     */
    public function graduateSantri(AsramaSantri|Siswa $target, ?string $userId = null): void
    {
        $santri = $target instanceof AsramaSantri ? $target : AsramaSantri::where('siswa_id', $target->id)->first();
        $siswa = $target instanceof Siswa ? $target : $santri?->siswa;

        $this->deactivateSantri($target, $userId, 'alumni');

        if ($siswa) {
            $alumni = AlumniProfile::where('siswa_id', $siswa->id)->first();
            if ($alumni) {
                $alumni->update([
                    'is_santri_asrama' => true,
                    'nomor_induk_asrama' => $santri?->nomor_induk_asrama ?: $alumni->nomor_induk_asrama,
                ]);
            }
        }
    }

    /**
     * Generate nomor induk santri unik jika belum ada.
     */
    public function generateSantriNumber(Siswa $student): string
    {
        foreach (array_filter([$student->nis_lokal, $student->nisn]) as $candidate) {
            if (! AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists()) {
                return $candidate;
            }
        }
        $sequence = AsramaSantri::withTrashed()->count() + 1;
        do {
            $candidate = 'AST-'.now()->format('y').'-'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists());

        return $candidate;
    }

    public function mirrorFor(Kelas $kelas, ?Asrama $unit = null, ?string $userId = null): AsramaKelas
    {
        $unit ??= $this->unit();
        $mirror = AsramaKelas::withTrashed()->firstOrNew(['kelas_id' => $kelas->id]);
        if ($mirror->trashed()) {
            $mirror->restore();
        }
        $mirror->fill([
            'asrama_id' => $unit->id,
            'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
            'nama_kelas' => $kelas->nama_kelas,
            'tingkat' => $kelas->tingkat,
            'jenis' => $mirror->jenis ?: $this->deriveJenis($kelas),
            'kapasitas' => max($kelas->kapasitas ?? 0, $mirror->kapasitas ?? 0, 1),
            'ruang' => $kelas->ruang_kelas,
            'is_active' => true,
            'created_by' => $mirror->created_by ?: $userId,
            'updated_by' => $userId ?: $mirror->updated_by,
        ])->save();

        return $mirror;
    }

    /**
     * Jadikan santri anggota aktif mirror rombel; keanggotaan rombel lain ditutup.
     */
    public function placeSantri(AsramaKelas $mirror, AsramaSantri $santri, ?string $userId = null): void
    {
        AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')
            ->where('asrama_kelas_id', '!=', $mirror->id)->update([
                'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
            ]);
        $member = AsramaKelasSantri::withTrashed()->firstOrNew([
            'asrama_kelas_id' => $mirror->id, 'asrama_santri_id' => $santri->id,
        ]);
        if ($member->trashed()) {
            $member->restore();
        }
        $member->fill([
            'status' => 'aktif',
            'tanggal_masuk' => $member->tanggal_masuk ?: now()->toDateString(),
            'tanggal_keluar' => null,
            'ditetapkan_by' => $userId,
        ])->save();
    }

    public function unit(): Asrama
    {
        return Asrama::where('is_active', true)->first() ?: Asrama::firstOrCreate(
            ['kode' => 'ASRAMA'],
            ['nama' => 'Asrama MAN 1 Metro', 'jenis' => 'campuran', 'is_active' => true]
        );
    }

    private function deriveJenis(Kelas $kelas): string
    {
        $jk = $kelas->siswaAktif()->pluck('siswa.jenis_kelamin')->filter()->unique();
        if ($jk->count() === 1) {
            return $jk->first() === 'L' ? 'putra' : 'putri';
        }

        return 'campuran';
    }
}
