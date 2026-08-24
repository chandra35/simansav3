<?php

namespace App\Menu\Filters;

use App\Models\OsisElection;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class OsisElectionMenuFilter implements FilterInterface
{
    private const PARTICIPANT_MENU_KEYS = [
        'siswa-osis-election',
        'gtk-osis-election',
    ];

    public function transform($item)
    {
        if (! isset($item['key']) || ! in_array($item['key'], self::PARTICIPANT_MENU_KEYS, true)) {
            return $item;
        }

        $user = auth()->user();
        $hasVisibleElection = $user && OsisElection::query()
            ->whereHas('tahunPelajaran', fn ($year) => $year->where('is_active', true))
            ->whereHas('voters', fn ($voter) => $voter->where('user_id', $user->id))
            ->where('status', 'published')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->exists();

        if (! $hasVisibleElection) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
