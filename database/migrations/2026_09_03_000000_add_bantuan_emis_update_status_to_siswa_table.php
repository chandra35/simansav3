<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->boolean('bantuan_emis_updated')->default(false)->after('emis_registered_by');
            $table->timestamp('bantuan_emis_updated_at')->nullable()->after('bantuan_emis_updated');
            $table->uuid('bantuan_emis_updated_by')->nullable()->after('bantuan_emis_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn([
                'bantuan_emis_updated',
                'bantuan_emis_updated_at',
                'bantuan_emis_updated_by',
            ]);
        });
    }
};
