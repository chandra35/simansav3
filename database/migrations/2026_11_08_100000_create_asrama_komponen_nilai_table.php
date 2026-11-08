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
        Schema::create('asrama_komponen_nilai', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('nama', 100);
            $table->decimal('bobot', 5, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun_pelajaran_id', 'kode'], 'asrama_komponen_nilai_unique');
        });

        Schema::table('asrama_nilai', function (Blueprint $table) {
            $table->json('detail_nilai')->nullable()->after('nilai');
        });

        // Register permission
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate([
            'name' => 'manage-asrama-komponen-nilai',
            'guard_name' => 'web',
        ]);

        Role::whereIn('name', ['Super Admin', 'Admin', 'Operator Asrama'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'manage-asrama-komponen-nilai')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::table('asrama_nilai', function (Blueprint $table) {
            $table->dropColumn('detail_nilai');
        });

        Schema::dropIfExists('asrama_komponen_nilai');
    }
};
