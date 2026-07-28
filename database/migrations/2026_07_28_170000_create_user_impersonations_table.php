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
        Schema::create('user_impersonations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('impersonator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->char('token_hash', 64)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('ended_reason', 40)->nullable();
            $table->timestamps();

            $table->index(
                ['impersonator_id', 'target_type', 'ended_at'],
                'user_impersonations_active_lookup'
            );
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'impersonate-users',
            'guard_name' => 'web',
        ]);

        Role::query()
            ->whereIn('name', ['Super Admin', 'Admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'impersonate-users')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            Role::query()->get()->each(fn (Role $role) => $role->revokePermissionTo($permission));
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Schema::dropIfExists('user_impersonations');
    }
};
