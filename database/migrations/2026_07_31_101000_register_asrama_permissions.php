<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'view-asrama',
        'manage-asrama',
        'manage-asrama-santri',
        'manage-asrama-asatidz',
        'manage-asrama-kelas',
        'manage-asrama-mapel',
        'manage-asrama-pengampu',
        'input-nilai-asrama',
        'manage-rapor-asrama',
        'publish-rapor-asrama',
        'print-rapor-asrama',
        'view-asrama-portal',
        'asrama-rapor-access',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $administrative = Permission::whereIn('name', $this->permissions)->get();
        Role::whereIn('name', ['Super Admin', 'Admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($administrative));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
