<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support modifying ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE dokumen_siswa MODIFY COLUMN jenis_dokumen ENUM('kk', 'ijazah_smp', 'kip', 'sktm', 'lainnya') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'lainnya' from enum
        DB::statement("ALTER TABLE dokumen_siswa MODIFY COLUMN jenis_dokumen ENUM('kk', 'ijazah_smp', 'kip', 'sktm') NOT NULL");
    }
};
