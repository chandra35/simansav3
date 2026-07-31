<?php

namespace App\Services;

use App\Models\AsramaAsatidz;
use App\Models\AsramaSantri;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AsramaAccessService
{
    private const PORTAL_PERMISSION = 'view-asrama-portal';

    private const NILAI_PERMISSION = 'input-nilai-asrama';

    public function syncStudent(?User $user): void
    {
        if (! $user || ! Permission::where('name', self::PORTAL_PERMISSION)->exists()) {
            return;
        }

        $active = AsramaSantri::query()
            ->where('status', 'aktif')
            ->whereHas('siswa', fn ($query) => $query->where('user_id', $user->id))
            ->exists();

        $this->syncDirectPermission($user, self::PORTAL_PERMISSION, $active);
    }

    public function syncGtk(?User $user): void
    {
        if (! $user || ! Permission::where('name', self::PORTAL_PERMISSION)->exists()) {
            return;
        }

        $active = AsramaAsatidz::query()
            ->where('is_active', true)
            ->whereHas('gtk', fn ($query) => $query->where('user_id', $user->id))
            ->exists();

        $this->syncDirectPermission($user, self::PORTAL_PERMISSION, $active);
        $this->syncDirectPermission($user, self::NILAI_PERMISSION, $active);
    }

    private function syncDirectPermission(User $user, string $permission, bool $shouldHave): void
    {
        if ($shouldHave) {
            if (! $user->hasDirectPermission($permission)) {
                $user->givePermissionTo($permission);
            }
        } elseif ($user->hasDirectPermission($permission)) {
            $user->revokePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
