<?php

namespace App\Services;

use App\Models\Asrama;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaSantri;
use App\Models\Kelas;

class AsramaRombelSyncService
{
    /**
     * Samakan mirror asrama_kelas dengan rombel SIMANSA bertanda is_asrama.
     */
    public function sync(?string $userId = null): void
    {
        $flagged = Kelas::where('is_asrama', true)->where('is_active', true)->get();
        $unit = $this->unit();
        foreach ($flagged as $kelas) {
            $this->mirrorFor($kelas, $unit, $userId);
        }

        AsramaKelas::where('is_active', true)->whereNotNull('kelas_id')
            ->whereNotIn('kelas_id', $flagged->pluck('id'))
            ->update(['is_active' => false]);
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
