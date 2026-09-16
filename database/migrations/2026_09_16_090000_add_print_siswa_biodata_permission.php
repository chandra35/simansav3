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
            'name' => 'print-siswa-biodata',
            'guard_name' => 'web',
        ]);

        // Admin dapat mencetak sesuai cakupan datanya; Wali Kelas hanya melalui
        // rombel aktif; Siswa hanya melalui data miliknya sendiri.
        Role::query()
            ->whereIn('name', ['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'BK', 'Wali Kelas', 'Siswa'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'print-siswa-biodata')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
