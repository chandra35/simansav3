<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_radius_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->string('role', 20)->nullable()->index();
            $table->string('rate_limit', 80)->nullable();
            $table->unsignedInteger('session_timeout')->nullable();
            $table->unsignedInteger('idle_timeout')->nullable();
            $table->unsignedInteger('simultaneous_use')->nullable();
            $table->string('framed_pool', 80)->nullable();
            $table->string('address_list', 80)->nullable();
            $table->unsignedInteger('priority')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->foreignId('hotspot_radius_profile_id')
                ->nullable()
                ->after('role')
                ->constrained('hotspot_radius_profiles')
                ->nullOnDelete();
        });

        DB::table('hotspot_radius_profiles')->insert([
            [
                'code' => 'siswa',
                'name' => 'Siswa Reguler',
                'role' => 'siswa',
                'rate_limit' => null,
                'priority' => 1,
                'description' => 'Default siswa. Isi rate limit jika bandwidth akan dikirim dari RADIUS ke MikroTik.',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'guru',
                'name' => 'Guru/GTK',
                'role' => 'guru',
                'rate_limit' => null,
                'priority' => 1,
                'description' => 'Default guru dan tenaga kependidikan.',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tamu',
                'name' => 'Tamu',
                'role' => 'tamu',
                'rate_limit' => null,
                'priority' => 1,
                'description' => 'Default akun tamu atau voucher sementara.',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotspot_radius_profile_id');
        });

        Schema::dropIfExists('hotspot_radius_profiles');
    }
};
