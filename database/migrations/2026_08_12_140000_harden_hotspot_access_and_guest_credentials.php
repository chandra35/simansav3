<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotspot_users', 'password_secret')) {
            Schema::table('hotspot_users', function (Blueprint $table) {
                $table->text('password_secret')->nullable()->after('sync_error');
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $view = Permission::firstOrCreate(['name' => 'view-hotspot', 'guard_name' => 'web']);
        $manage = Permission::firstOrCreate(['name' => 'manage-hotspot', 'guard_name' => 'web']);

        Role::query()->whereIn('name', ['Super Admin', 'Admin'])->get()
            ->each(fn (Role $role) => $role->givePermissionTo([$view, $manage]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'manage-hotspot')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (Schema::hasColumn('hotspot_users', 'password_secret')) {
            Schema::table('hotspot_users', fn (Blueprint $table) => $table->dropColumn('password_secret'));
        }
    }
};
