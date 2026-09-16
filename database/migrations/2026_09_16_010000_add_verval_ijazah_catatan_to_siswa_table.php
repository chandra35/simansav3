<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('siswa', 'verval_ijazah_catatan')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->text('verval_ijazah_catatan')->nullable()->after('verval_ijazah_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswa', 'verval_ijazah_catatan')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropColumn('verval_ijazah_catatan');
            });
        }
    }
};
