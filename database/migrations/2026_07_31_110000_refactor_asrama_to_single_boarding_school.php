<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $unitId = DB::table('asrama_units')->whereNull('deleted_at')->value('id');
        if (! $unitId) {
            $unitId = (string) Str::uuid();
            DB::table('asrama_units')->insert([
                'id' => $unitId,
                'kode' => 'ASRAMA',
                'nama' => 'Asrama MAN 1 Metro',
                'jenis' => 'campuran',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('asrama_asatidz', function (Blueprint $table) {
            $table->boolean('dapat_mengasuh_rombel')->default(false)->after('jabatan');
            $table->boolean('dapat_mengasuh_kamar')->default(false)->after('dapat_mengasuh_rombel');
            $table->boolean('dapat_mengampu_mapel')->default(true)->after('dapat_mengasuh_kamar');
        });

        Schema::table('asrama_kelas', function (Blueprint $table) {
            $table->foreignUuid('kelas_id')->nullable()->after('tahun_pelajaran_id')
                ->constrained('kelas')->nullOnDelete();
            $table->unique('kelas_id', 'asrama_kelas_regular_unique');
        });

        DB::table('asrama_kelas')
            ->whereNull('kelas_id')
            ->orderBy('created_at')
            ->get()
            ->each(function ($asramaKelas): void {
                $kelasId = DB::table('kelas')
                    ->where('tahun_pelajaran_id', $asramaKelas->tahun_pelajaran_id)
                    ->where('nama_kelas', $asramaKelas->nama_kelas)
                    ->value('id');
                if ($kelasId && ! DB::table('asrama_kelas')->where('kelas_id', $kelasId)->exists()) {
                    DB::table('asrama_kelas')->where('id', $asramaKelas->id)->update(['kelas_id' => $kelasId]);
                }
            });

        Schema::create('asrama_rombel_pengasuh', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_kelas_id')->constrained('asrama_kelas')->cascadeOnDelete();
            $table->foreignUuid('asrama_asatidz_id')->constrained('asrama_asatidz')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['asrama_kelas_id', 'asrama_asatidz_id'], 'asrama_rombel_pengasuh_unique');
        });

        Schema::create('asrama_pengasuh_santri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_rombel_pengasuh_id')->constrained('asrama_rombel_pengasuh')->cascadeOnDelete();
            $table->foreignUuid('asrama_kelas_santri_id')->constrained('asrama_kelas_santri')->cascadeOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('asrama_kelas_santri_id', 'asrama_pengasuh_santri_member_unique');
        });

        Schema::create('asrama_kamar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();
            $table->string('nama', 120);
            $table->enum('gedung', ['putra', 'putri'])->index();
            $table->string('lantai', 30)->nullable();
            $table->unsignedSmallInteger('kapasitas')->default(8);
            $table->foreignUuid('pengasuh_asatidz_id')->nullable()->constrained('asrama_asatidz')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asrama_kamar_santri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_kamar_id')->constrained('asrama_kamar')->cascadeOnDelete();
            $table->foreignUuid('asrama_santri_id')->constrained('asrama_santri')->cascadeOnDelete();
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'keluar'])->default('aktif')->index();
            $table->foreignUuid('ditetapkan_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['asrama_santri_id', 'status'], 'asrama_kamar_santri_active_index');
        });

        $this->registerOperatorRole();
    }

    private function registerOperatorRole(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $operatorPermissions = [
            'view-asrama',
            'view-asrama-portal',
            'manage-asrama',
            'manage-asrama-santri',
            'manage-asrama-asatidz',
            'manage-asrama-kelas',
            'manage-asrama-kamar',
            'manage-asrama-mapel',
            'manage-asrama-pengampu',
            'input-nilai-asrama',
            'manage-rapor-asrama',
            'publish-rapor-asrama',
            'print-rapor-asrama',
            'asrama-rapor-access',
        ];

        foreach (array_merge($operatorPermissions, ['manage-asrama-operator']) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'Operator Asrama', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::whereIn('name', $operatorPermissions)->get());

        Role::whereIn('name', ['Super Admin', 'Admin'])->get()->each(function (Role $admin): void {
            $admin->givePermissionTo(['manage-asrama-kamar', 'manage-asrama-operator']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('asrama_kamar_santri');
        Schema::dropIfExists('asrama_kamar');
        Schema::dropIfExists('asrama_pengasuh_santri');
        Schema::dropIfExists('asrama_rombel_pengasuh');

        Schema::table('asrama_kelas', function (Blueprint $table) {
            $table->dropUnique('asrama_kelas_regular_unique');
            $table->dropConstrainedForeignId('kelas_id');
        });
        Schema::table('asrama_asatidz', function (Blueprint $table) {
            $table->dropColumn(['dapat_mengasuh_rombel', 'dapat_mengasuh_kamar', 'dapat_mengampu_mapel']);
        });
    }
};
