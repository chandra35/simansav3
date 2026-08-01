<?php

namespace App\Services;

use App\Models\Asrama;
use App\Models\AsramaKamarSantri;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaSantri;
use App\Models\Kelas;

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
    private function reconcileSantri(array $mirrors, ?string $userId): void
    {
        AsramaSantri::with(['siswa.kelasTahunAktif', 'kelasAktif'])
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
                        AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
                            'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
                        ]);
                    }
                }
            });
    }

    private function deactivateSantri(AsramaSantri $santri, ?string $userId): void
    {
        $tanggal = now()->toDateString();
        AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
            'status' => 'keluar', 'tanggal_keluar' => $tanggal, 'is_ketua_kelas' => false,
        ]);
        AsramaKamarSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')->update([
            'status' => 'keluar', 'tanggal_keluar' => $tanggal,
        ]);
        $santri->update([
            'status' => 'nonaktif',
            'tanggal_keluar' => $santri->tanggal_keluar ?: $tanggal,
            'updated_by' => $userId ?: $santri->updated_by,
        ]);
        app(AsramaAccessService::class)->syncStudent($santri->siswa?->user);
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
