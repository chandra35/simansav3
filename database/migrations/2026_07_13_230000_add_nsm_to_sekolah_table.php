<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (!Schema::hasColumn('sekolah', 'nsm')) {
                $table->string('nsm', 20)->nullable()->after('npsn')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('sekolah', 'nsm')) {
                $table->dropIndex(['nsm']);
                $table->dropColumn('nsm');
            }
        });
    }
};
