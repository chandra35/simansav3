<?php

namespace App\Menu\Filters;

use App\Models\SpanPtkinMenu;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class SpanPtkinMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['key']) && $item['key'] === 'siswa-span-ptkin') {
            $user = auth()->user();

            if (!$user || !$user->siswa || !$user->siswa->kelasSaatIni) {
                $item['restricted'] = true;

                return $item;
            }

            $tingkat = $user->siswa->kelasSaatIni->tingkat ?? null;

            if ((int) $tingkat !== 12 || !SpanPtkinMenu::getActiveMenu()) {
                $item['restricted'] = true;
            }
        }

        return $item;
    }
}
