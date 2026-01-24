<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            // Add kategori_ptk column (nullable for migration safety)
            $table->enum('kategori_ptk', ['Pendidik', 'Tenaga Kependidikan'])
                ->nullable()
                ->after('status_kepegawaian')
                ->comment('Kategori PTK: Pendidik (Guru) atau Tenaga Kependidikan (Staff TU, dll)');
            
            // Add jenis_ptk column (nullable for migration safety)
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
            ])->nullable()
                ->after('kategori_ptk')
                ->comment('Jenis PTK detail sesuai kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->dropColumn(['kategori_ptk', 'jenis_ptk']);
        });
    }
};
