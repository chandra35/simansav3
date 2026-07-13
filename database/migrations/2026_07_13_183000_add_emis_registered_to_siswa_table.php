<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->boolean('emis_registered')->default(false)->after('verval_ijazah_by');
            $table->timestamp('emis_registered_at')->nullable()->after('emis_registered');
            $table->uuid('emis_registered_by')->nullable()->after('emis_registered_at');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['emis_registered', 'emis_registered_at', 'emis_registered_by']);
        });
    }
};

