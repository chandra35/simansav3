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
            ->where(function ($election) {
                $election->where(function ($open) {
                    $open->whereIn('status', ['published', 'paused'])
                        ->where('starts_at', '<=', now())
                        ->where('ends_at', '>=', now());
                })->orWhere(function ($results) {
                    $results->where('status', 'closed')
                        ->whereNotNull('result_published_at');
                });
            })
            ->exists();

        if (! $hasVisibleElection) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
