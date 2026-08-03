<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polling_targets', function (Blueprint $table) {
            $table->enum('scope_type', [
                'all', 'tingkat', 'kelas', 'jenis_ptk', 'role', 'kategori_ptk', 'gtk',
            ])->change();
        });
    }

    public function down(): void
    {
        DB::table('polling_targets')->whereIn('scope_type', ['kategori_ptk', 'gtk'])->delete();

        Schema::table('polling_targets', function (Blueprint $table) {
            $table->enum('scope_type', ['all', 'tingkat', 'kelas', 'jenis_ptk', 'role'])->change();
        });
    }
};
