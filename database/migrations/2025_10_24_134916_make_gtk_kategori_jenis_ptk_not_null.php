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
        // Double check: ensure no NULL values exist before applying constraint
        $nullCount = DB::table('gtks')
            ->whereNull('kategori_ptk')
            ->orWhereNull('jenis_ptk')
            ->count();
            
        if ($nullCount > 0) {
            throw new \Exception(
                "Cannot make columns NOT NULL: {$nullCount} records still have NULL values. " .
                "Run 'php artisan migrate:rollback --step=1' and 'php artisan migrate' to populate data first."
            );
        }

        // Modify columns to NOT NULL
        Schema::table('gtks', function (Blueprint $table) {
            $table->enum('kategori_ptk', ['Pendidik', 'Tenaga Kependidikan'])
                ->nullable(false)
                ->change()
                ->comment('Kategori PTK: Pendidik (Guru) atau Tenaga Kependidikan (Staff TU, dll) - REQUIRED');
            
            $table->enum('jenis_ptk', [
                // PENDIDIK (Guru)
                'Guru Mapel',
                'Guru BK',
                // TENAGA KEPENDIDIKAN (Non-Guru)
                'Kepala TU',
                'Staff TU',
                'Bendahara',
                'Laboran',
                'Pustakawan',
                'Cleaning Service',
                'Satpam',
                'Lainnya'
            ])->nullable(false)
                ->change()
                ->comment('Jenis PTK detail sesuai kategori - REQUIRED');
        });

        echo "\n✓ Columns 'kategori_ptk' and 'jenis_ptk' are now NOT NULL\n";
        echo "  All new GTK records MUST have these fields filled.\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Make columns nullable again
        Schema::table('gtks', function (Blueprint $table) {
            $table->enum('kategori_ptk', ['Pendidik', 'Tenaga Kependidikan'])
                ->nullable()
                ->change()
                ->comment('Kategori PTK: Pendidik (Guru) atau Tenaga Kependidikan (Staff TU, dll)');
            
            $table->enum('jenis_ptk', [
                'Guru Mapel',
                'Guru BK',
                'Kepala TU',
                'Staff TU',
                'Bendahara',
                'Laboran',
                'Pustakawan',
                'Cleaning Service',
                'Satpam',
                'Lainnya'
            ])->nullable()
                ->change()
                ->comment('Jenis PTK detail sesuai kategori');
        });
    }
};
