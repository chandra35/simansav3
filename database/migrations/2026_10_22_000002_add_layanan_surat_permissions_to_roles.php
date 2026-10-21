<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $viewPerm = Permission::firstOrCreate(['name' => 'view-layanan-surat', 'guard_name' => 'web']);
        $managePerm = Permission::firstOrCreate(['name' => 'manage-layanan-surat', 'guard_name' => 'web']);

        $roles = Role::whereIn('name', ['Super Admin', 'Admin', 'STAFF PTSP'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo([$viewPerm, $managePerm]);
        }
    }

    public function down(): void
    {
        $viewPerm = Permission::where('name', 'view-layanan-surat')->first();
        $managePerm = Permission::where('name', 'manage-layanan-surat')->first();

        if ($viewPerm && $managePerm) {
            $staffPtsp = Role::where('name', 'STAFF PTSP')->first();
            if ($staffPtsp) {
                $staffPtsp->revokePermissionTo([$viewPerm, $managePerm]);
            }
        }
    }
};
