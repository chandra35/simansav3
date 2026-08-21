<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $legger = Permission::firstOrCreate(['name' => 'view-nilai-legger', 'guard_name' => 'web']);
        $rdmRecap = Permission::firstOrCreate(['name' => 'view-nilai-rdm', 'guard_name' => 'web']);

        // Legger tetap hanya terbuka untuk pimpinan/tim akademik yang diberi hak ini.
        foreach (['Super Admin', 'Kepala Madrasah', 'WAKA'] as $roleName) {
            Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($legger);
        }

        $waliKelas = Role::query()->where('name', 'Wali Kelas')->where('guard_name', 'web')->first();
        $waliKelas?->givePermissionTo($rdmRecap);
        $waliKelas?->revokePermissionTo(['view-nilai', 'input-nilai']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $waliKelas = Role::query()->where('name', 'Wali Kelas')->where('guard_name', 'web')->first();
        $waliKelas?->revokePermissionTo('view-nilai-rdm');
        $waliKelas?->givePermissionTo(['view-nilai', 'input-nilai']);

        Permission::whereIn('name', ['view-nilai-legger', 'view-nilai-rdm'])->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
