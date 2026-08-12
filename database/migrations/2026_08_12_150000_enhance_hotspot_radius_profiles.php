<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotspot_radius_profiles', 'mikrotik_group')) {
            Schema::table('hotspot_radius_profiles', function (Blueprint $table) {
                $table->string('mikrotik_group', 80)->nullable()->after('rate_limit');
            });
        }

        foreach (['guru', 'siswa', 'tamu'] as $role) {
            DB::table('hotspot_radius_profiles')
                ->where('code', $role)
                ->whereNull('mikrotik_group')
                ->update(['mikrotik_group' => 'profile-'.$role]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotspot_radius_profiles', 'mikrotik_group')) {
            Schema::table('hotspot_radius_profiles', function (Blueprint $table) {
                $table->dropColumn('mikrotik_group');
            });
        }
    }
};
