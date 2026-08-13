<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'create-pengumuman',
        'edit-pengumuman',
        'delete-pengumuman',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->permissions)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
