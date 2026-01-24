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
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('foto_profile')->nullable()->after('user_id')->comment('Path foto profile siswa');
            $table->char('npsn_asal_sekolah', 8)->nullable()->after('foto_profile')->comment('NPSN sekolah asal (MTs/SMP)');
            
            // Foreign key constraint
            $table->foreign('npsn_asal_sekolah')
                  ->references('npsn')
                  ->on('sekolah')
                  ->onDelete('set null');
                  
            $table->index('npsn_asal_sekolah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['npsn_asal_sekolah']);
            $table->dropIndex(['npsn_asal_sekolah']);
            $table->dropColumn(['foto_profile', 'npsn_asal_sekolah']);
        });
    }
};
