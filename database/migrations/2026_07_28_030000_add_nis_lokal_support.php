<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'nsm')) {
                $table->string('nsm', 12)->nullable()->after('npsn');
            }
            if (! Schema::hasColumn('app_settings', 'school_data_source')) {
                $table->string('school_data_source')->nullable()->after('nsm');
            }
            if (! Schema::hasColumn('app_settings', 'school_data_fetched_at')) {
                $table->timestamp('school_data_fetched_at')->nullable()->after('school_data_source');
            }
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('nis_lokal', 18)->nullable()->after('nisn')->unique();
            $table->unsignedSmallInteger('nis_lokal_tahun')->nullable()->after('nis_lokal');
            $table->unsignedSmallInteger('nis_lokal_urutan')->nullable()->after('nis_lokal_tahun');
            $table->timestamp('nis_lokal_generated_at')->nullable()->after('nis_lokal_urutan');
            $table->foreignUuid('nis_lokal_generated_by')
                ->nullable()
                ->after('nis_lokal_generated_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->unique(
                ['nis_lokal_tahun', 'nis_lokal_urutan'],
                'siswa_nis_lokal_tahun_urutan_unique'
            );
        });

        Schema::create('nis_lokal_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('nsm', 12);
            $table->unsignedSmallInteger('tahun_masuk');
            $table->unsignedSmallInteger('nomor_terakhir')->default(0);
            $table->timestamps();
            $table->unique(['nsm', 'tahun_masuk']);
        });

        DB::table('app_settings')
            ->where('npsn', '10648374')
            ->whereNull('nsm')
            ->update(['nsm' => '131118720001']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate([
            'name' => 'manage-nis-lokal',
            'guard_name' => 'web',
        ]);
        Role::query()->whereIn('name', ['Super Admin', 'Admin', 'Operator'])->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::query()
            ->where('name', 'manage-nis-lokal')
            ->where('guard_name', 'web')
            ->first();
        if ($permission) {
            Role::query()->get()->each(fn (Role $role) => $role->revokePermissionTo($permission));
            $permission->delete();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('nis_lokal_sequences');

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropUnique('siswa_nis_lokal_tahun_urutan_unique');
            $table->dropForeign(['nis_lokal_generated_by']);
            $table->dropUnique(['nis_lokal']);
            $table->dropColumn([
                'nis_lokal',
                'nis_lokal_tahun',
                'nis_lokal_urutan',
                'nis_lokal_generated_at',
                'nis_lokal_generated_by',
            ]);
        });

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['nsm', 'school_data_source', 'school_data_fetched_at']);
        });
    }
};
