<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->boolean('verval_ijazah')->default(false)->after('data_diri_completed');
            $table->timestamp('verval_ijazah_at')->nullable()->after('verval_ijazah');
            $table->uuid('verval_ijazah_by')->nullable()->after('verval_ijazah_at');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['verval_ijazah', 'verval_ijazah_at', 'verval_ijazah_by']);
        });
    }
};
