<?php

namespace App\Menu\Filters;

use App\Models\SiswaKelas;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class LulusanMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (!isset($item['key']) || $item['key'] !== 'siswa-lulusan') {
            return $item;
        }

        $user = auth()->user();

        if (!$user || !$user->siswa) {
            $item['restricted'] = true;
            return $item;
        }

        $siswaId = $user->siswa->id;

        $hasKelas12History = SiswaKelas::where('siswa_id', $siswaId)
            ->whereNull('deleted_at')
            ->whereHas('kelas', function ($query) {
                $query->where('tingkat', 12);
            })
            ->exists();

        if (!$hasKelas12History) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
