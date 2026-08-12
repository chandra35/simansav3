<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = [
        'view-penugasan-gtk',
        'create-penugasan-gtk',
        'edit-penugasan-gtk',
        'end-penugasan-gtk',
        'delete-penugasan-gtk',
        'manage-jenis-penugasan-gtk',
        'view-beban-kerja-gtk',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        foreach (['Super Admin', 'Admin'] as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo(self::PERMISSIONS);
        }
        Role::where('name', 'Operator')->where('guard_name', 'web')->first()?->givePermissionTo([
            'view-penugasan-gtk',
            'view-beban-kerja-gtk',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('guard_name', 'web')->whereIn('name', self::PERMISSIONS)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
