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
        Schema::table('dokumen_siswa', function (Blueprint $table) {
            // Add storage_disk column to track which disk the file is stored in
            $table->string('storage_disk', 50)->after('file_path')->nullable()->comment('Disk name where file is stored (dokumen, dokumen_fallback, private, public)');
            
            // Add index for better query performance
            $table->index('storage_disk');
        });
        
        // Update existing records to use 'private' disk (old default)
        DB::table('dokumen_siswa')->whereNull('storage_disk')->update([
            'storage_disk' => 'private'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_siswa', function (Blueprint $table) {
            $table->dropIndex(['storage_disk']);
            $table->dropColumn('storage_disk');
        });
    }
};
