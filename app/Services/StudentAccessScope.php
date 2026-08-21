<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Server-side scope for GTK whose student access comes from an active assignment.
 * Global staff retain their explicitly assigned school-wide access.
 */
class StudentAccessScope
{
    public function isLimited(User $user): bool
    {
        if ($this->hasGlobalScope($user)) {
            return false;
        }

        $user->loadMissing('gtk');

        return (bool) $user->gtk || $user->hasAnyRole(['GTK', 'Wali Kelas']);
    }

    public function classIds(User $user): ?Collection
    {
        if (! $this->isLimited($user)) {
            return null;
        }

        $activeYearId = TahunPelajaran::query()->active()->value('id');
        if (! $activeYearId) {
            return collect();
        }

        $waliClassIds = Kelas::query()
            ->where('wali_kelas_id', $user->id)
            ->where('tahun_pelajaran_id', $activeYearId)
            ->where('is_active', true)
            ->pluck('id');

        $gtkId = $user->gtk?->id;
        $teachingClassIds = $gtkId
            ? JadwalPelajaran::query()
                ->where('gtk_id', $gtkId)
                ->where('tahun_pelajaran_id', $activeYearId)
                ->where('is_active', true)
                ->pluck('kelas_id')
            : collect();

        return $waliClassIds->merge($teachingClassIds)->filter()->unique()->values();
    }

    public function apply(Builder $query, User $user): Builder
    {
        $classIds = $this->classIds($user);

        if ($classIds === null) {
            return $query;
        }

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('kelasTahunAktif', fn ($classes) => $classes->whereIn('kelas.id', $classIds));
    }

    private function hasGlobalScope(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])
            || in_array($user->role, ['super_admin', 'admin', 'operator'], true);
    }
}
