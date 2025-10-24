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
        // Populate NULL values with default values
        // Strategy: Set all NULL records to 'Pendidik' (Guru Mapel) as default
        // Admin can update manually later via UI
        
        DB::table('gtks')
            ->whereNull('kategori_ptk')
            ->orWhereNull('jenis_ptk')
            ->update([
                'kategori_ptk' => DB::raw("COALESCE(kategori_ptk, 'Pendidik')"),
                'jenis_ptk' => DB::raw("COALESCE(jenis_ptk, 'Guru Mapel')"),
                'updated_at' => now()
            ]);

        // Log the update
        $affected = DB::table('gtks')
            ->where('kategori_ptk', 'Pendidik')
            ->where('jenis_ptk', 'Guru Mapel')
            ->count();
            
        if ($affected > 0) {
            echo "\n✓ Populated {$affected} GTK records with default values (Pendidik - Guru Mapel)\n";
            echo "  Admin can update these values manually via GTK Management UI.\n\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Set default values back to NULL
        DB::table('gtks')
            ->where('kategori_ptk', 'Pendidik')
            ->where('jenis_ptk', 'Guru Mapel')
            ->update([
                'kategori_ptk' => null,
                'jenis_ptk' => null,
                'updated_at' => now()
            ]);
    }
};
