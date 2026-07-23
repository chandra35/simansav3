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
        Schema::create('jadwal_guru_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('source', 40)->default('jadwal_excel');
            $table->string('external_code', 20);
            $table->string('external_name');
            $table->string('normalized_name')->nullable();
            $table->uuid('gtk_id')->nullable();
            $table->decimal('confidence', 5, 2)->default(0);
            $table->string('match_method', 40)->nullable();
            $table->enum('status', ['pending', 'suggested', 'verified', 'rejected'])->default('pending');
            $table->string('context', 80)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->foreign('gtk_id')->references('id')->on('gtks')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tahun_pelajaran_id', 'source', 'external_code'], 'jadwal_guru_alias_code_unique');
            $table->index(['tahun_pelajaran_id', 'status']);
        });

        Schema::create('jadwal_mapel_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('source', 40)->default('jadwal_excel');
            $table->string('external_code', 20);
            $table->string('external_name');
            $table->string('normalized_name')->nullable();
            $table->uuid('mata_pelajaran_id')->nullable();
            $table->decimal('confidence', 5, 2)->default(0);
            $table->string('match_method', 40)->nullable();
            $table->enum('status', ['pending', 'suggested', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tahun_pelajaran_id', 'source', 'external_code'], 'jadwal_mapel_alias_code_unique');
            $table->index(['tahun_pelajaran_id', 'status']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(['view-jadwal-mapping', 'manage-jadwal-mapping'])
            ->map(fn (string $name) => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]));

        Role::query()->whereIn('name', ['Super Admin', 'Admin'])->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app(\App\Services\JadwalAliasMappingService::class)->synchronize();
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_mapel_aliases');
        Schema::dropIfExists('jadwal_guru_aliases');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()
            ->whereIn('name', ['view-jadwal-mapping', 'manage-jadwal-mapping'])
            ->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
