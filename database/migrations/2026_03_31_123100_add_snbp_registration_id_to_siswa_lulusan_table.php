<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->foreignUuid('snbp_registration_id')
                ->nullable()
                ->after('tahun_pelajaran_id')
                ->constrained('snbp_registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('snbp_registration_id');
        });
    }
};
