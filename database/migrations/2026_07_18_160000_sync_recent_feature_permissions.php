<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'view-statistik-siswa',
            'view-emis-comparison',
            'sync-emis-comparison',
            'view-osis-election',
            'manage-osis-election',
        ])->mapWithKeys(function (string $name) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            return [$name => $permission];
        });

        // Pertahankan akses statistik bagi role yang sebelumnya dapat melihat data siswa.
        Role::query()
            ->whereHas('permissions', fn ($query) => $query->where('name', 'view-siswa'))
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions['view-statistik-siswa']));

        Role::query()->where('name', 'Super Admin')->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions->values()));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'view-statistik-siswa')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
