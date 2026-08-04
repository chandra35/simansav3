<?php

namespace App\Services;

use App\Models\Polling;
use App\Models\User;
use Illuminate\Support\Collection;

class PollingAudienceService
{
    private const MANAGER_ROLES = ['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'];

    private array $activeCache = [];

    public function respondentContext(User $user): ?array
    {
        if ($user->hasAnyRole(self::MANAGER_ROLES)) return null;

        if (($user->hasRole('Siswa') || $user->role === 'siswa') && $user->siswa) {
            $kelas = $user->siswa->relationLoaded('kelasTahunAktif')
                ? $user->siswa->kelasTahunAktif->first()
                : $user->siswa->kelasTahunAktif()->first();
            return [
                'type' => 'siswa',
                'id' => $user->siswa->id,
                'name' => $user->siswa->nama_lengkap,
                'class_id' => $kelas?->id,
                'class_name' => $kelas?->nama_kelas,
                'grade' => $kelas?->tingkat,
                'gtk_type' => null,
                'roles' => $user->getRoleNames()->all(),
            ];
        }

        if (($user->hasRole('GTK') || $user->role === 'gtk') && $user->gtk && ! $user->siswa) {
            return [
                'type' => 'gtk',
                'id' => $user->gtk->id,
                'name' => $user->gtk->nama_lengkap,
                'class_id' => null,
                'class_name' => null,
                'grade' => null,
                'gtk_type' => $user->gtk->jenis_ptk,
                'gtk_category' => $user->gtk->kategori_ptk,
                'roles' => $user->getRoleNames()->all(),
            ];
        }

        return null;
    }

    public function isEligible(Polling $polling, User $user): bool
    {
        $context = $this->respondentContext($user);
        if (! $context) return false;
        if ($polling->audience !== 'both' && $polling->audience !== $context['type']) return false;

        $targets = $polling->relationLoaded('targets') ? $polling->targets : $polling->targets()->get();
        return $targets->where('audience_type', $context['type'])
            ->contains(fn ($target) => $this->matchesTarget($context, $target));
    }

    public function matchesTarget(array $context, object $target): bool
    {
        return match ($target->scope_type) {
            'all' => true,
            'tingkat' => (string) $context['grade'] === (string) $target->scope_value,
            'kelas' => (string) $context['class_id'] === (string) $target->scope_value,
            'jenis_ptk' => mb_strtolower((string) $context['gtk_type']) === mb_strtolower((string) $target->scope_value),
            'kategori_ptk' => mb_strtolower((string) ($context['gtk_category'] ?? '')) === mb_strtolower((string) $target->scope_value),
            'gtk' => (string) $context['id'] === (string) $target->scope_value,
            'role' => collect($context['roles'])->contains(fn ($role) => mb_strtolower($role) === mb_strtolower((string) $target->scope_value)),
            default => false,
        };
    }

    public function activeForUser(User $user): Collection
    {
        if (isset($this->activeCache[$user->id])) return $this->activeCache[$user->id];
        if (! $this->respondentContext($user)) return $this->activeCache[$user->id] = collect();

        return $this->activeCache[$user->id] = Polling::query()
            ->with(['targets', 'responses' => fn ($query) => $query->where('user_id', $user->id)])
            ->where('status', 'published')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at')
            ->get()
            ->filter(fn (Polling $polling) => $this->isEligible($polling, $user))
            ->values();
    }

    public function pendingForUser(User $user): Collection
    {
        return $this->activeForUser($user)
            ->filter(fn (Polling $polling) => $polling->responses->isEmpty())
            ->values();
    }

    public function targetRespondents(Polling $polling): Collection
    {
        $polling->loadMissing('targets');

        // ponytail: jumlah akun sekolah masih ribuan, jadi satu scan terfilter lebih aman
        // daripada menduplikasi aturan target ke beberapa query SQL; pecah ke query scope bila >50k akun.
        return User::query()
            ->with(['roles:id,name', 'siswa.kelasTahunAktif', 'gtk'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereHas('siswa')->orWhereHas('gtk');
            })
            ->get()
            ->filter(fn (User $user) => $this->isEligible($polling, $user))
            ->map(function (User $user) {
                $context = $this->respondentContext($user);
                return array_merge($context, [
                    'user_id' => $user->id,
                    'username' => $user->username,
                ]);
            })
            ->sortBy(fn ($row) => ($row['class_name'] ?? 'ZZZ').'|'.$row['name'])
            ->values();
    }

    public function respondentRoute(User $user, ?Polling $polling = null): string
    {
        $context = $this->respondentContext($user);
        $route = ($context['type'] ?? null) === 'siswa' ? 'siswa.polling' : 'admin.gtk.polling';
        return route($route.'.'.($polling ? 'show' : 'index'), $polling ? [$polling] : []);
    }
}
