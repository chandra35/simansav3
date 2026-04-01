<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->foreignUuid('span_ptkin_registration_id')
                ->nullable()
                ->after('snbp_registration_id')
                ->constrained('span_ptkin_registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('span_ptkin_registration_id');
        });
    }
};
