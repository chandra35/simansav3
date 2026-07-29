<?php

namespace App\Menu\Filters;

use App\Services\StudentGraduationAccessService;
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

        if (! app(StudentGraduationAccessService::class)->canAccessLulusanData($user->siswa)) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
