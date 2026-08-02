<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->string('peg_id', 20)->nullable()->unique()->after('nip');
            $table->string('nrg', 20)->nullable()->unique()->after('peg_id');
            $table->string('npk', 20)->nullable()->unique()->after('nrg');
            $table->string('status_inpassing', 50)->nullable()->after('npk');
            $table->string('status_sertifikasi', 50)->nullable()->after('status_inpassing');
        });
    }

    public function down(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->dropUnique(['peg_id']);
            $table->dropUnique(['nrg']);
            $table->dropUnique(['npk']);
            $table->dropColumn(['peg_id', 'nrg', 'npk', 'status_inpassing', 'status_sertifikasi']);
        });
    }
};
