<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->foreignUuid('referensi_program_studi_id')
                ->nullable()
                ->after('referensi_perguruan_tinggi_id')
                ->constrained('referensi_program_studi')
                ->nullOnDelete();
            $table->string('program_studi_manual')->nullable()->after('program_studi');
        });
    }

    public function down(): void
    {
        Schema::table('siswa_lulusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referensi_program_studi_id');
            $table->dropColumn('program_studi_manual');
        });
    }
};
