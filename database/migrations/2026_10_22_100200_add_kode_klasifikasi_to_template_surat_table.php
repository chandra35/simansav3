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
        if (!Schema::hasColumn('template_surat', 'kode_klasifikasi')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->string('kode_klasifikasi', 50)->nullable()->after('jenis')
                    ->comment('Kode klasifikasi surat Kemenag (e.g. PP.00.6, PP.01.1, KU.01.1)');
            });
        }

        // Set default kode_klasifikasi for existing templates based on jenis
        DB::table('template_surat')->whereNull('kode_klasifikasi')->orWhere('kode_klasifikasi', '')->update([
            'kode_klasifikasi' => DB::raw("CASE 
                WHEN jenis = 'sk_lulus' THEN 'PP.01.1'
                WHEN jenis = 'sk_tidak_mampu' THEN 'KU.01.1'
                WHEN jenis = 'sk_izin' THEN 'KS.01.2'
                ELSE 'PP.00.6'
            END")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('template_surat', 'kode_klasifikasi')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->dropColumn('kode_klasifikasi');
            });
        }
    }
};
