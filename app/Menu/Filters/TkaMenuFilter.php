<?php

namespace App\Menu\Filters;

use App\Models\TkaPeserta;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class TkaMenuFilter implements FilterInterface
{
    /**
     * Filter TKA menu visibility - only for Kelas 12 students or registered TKA participants
     */
    public function transform($item)
    {
        if (isset($item['key']) && $item['key'] === 'siswa-kartu-tka') {
            $user = auth()->user();

            if (!$user) {
                $item['restricted'] = true;
                return $item;
            }

            $siswa = $user->siswa;

            if (!$siswa) {
                $item['restricted'] = true;
                return $item;
            }

            // Check if student has a TKA card record
            $hasTka = TkaPeserta::where(function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id)
                  ->orWhere('nisn', $siswa->nisn);
            })->where('is_active', true)->exists();

            if ($hasTka) {
                return $item;
            }

            // Otherwise check if student is currently in Kelas 12
            $kelas = $siswa->kelasSaatIni;
            if (!$kelas) {
                $item['restricted'] = true;
                return $item;
            }

            $tingkat = $kelas->tingkat ?? null;
            $namaKelas = (string) ($kelas->nama_kelas ?? '');

            $isKelas12 = ($tingkat == 12) || str_contains($namaKelas, '12') || str_starts_with(strtoupper($namaKelas), 'XII');

            if (!$isKelas12) {
                $item['restricted'] = true;
                return $item;
            }
        }

        return $item;
    }
}
