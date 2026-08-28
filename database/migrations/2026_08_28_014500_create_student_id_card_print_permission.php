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

        $permission = Permission::firstOrCreate([
            'name' => 'cetak-id-card-siswa',
            'guard_name' => 'web',
        ]);

        // Admin sekolah tetap mempertahankan akses cetak yang sebelumnya
        // melekat melalui view-siswa; GTK harus ditugaskan secara eksplisit.
        Role::query()
            ->whereIn('name', ['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'cetak-id-card-siswa')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
