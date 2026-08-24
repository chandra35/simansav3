<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'view-prestasi-siswa',
        'create-prestasi-siswa',
        'edit-prestasi-siswa',
        'delete-prestasi-siswa',
        'verify-prestasi-siswa',
    ];

    public function up(): void
    {
        $permissions = Permission::query()->whereIn('name', $this->permissions)->get();
        Role::query()->whereIn('name', ['Super Admin', 'Admin'])->each(
            fn (Role $role) => $role->givePermissionTo($permissions)
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::query()->whereIn('name', ['Super Admin', 'Admin'])->each(
            fn (Role $role) => $role->revokePermissionTo($this->permissions)
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
