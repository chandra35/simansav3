<?php

namespace App\Menu\Filters;

use App\Services\StudentGraduationAccessService;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class GraduationAnnouncementMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (($item['key'] ?? null) !== 'siswa-kelulusan-pengumuman') {
            return $item;
        }

        $siswa = auth()->user()?->siswa;

        if (! $siswa || ! app(StudentGraduationAccessService::class)->canAccessAnnouncement($siswa)) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
