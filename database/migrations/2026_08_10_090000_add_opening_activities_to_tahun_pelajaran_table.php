<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_pelajaran', function (Blueprint $table) {
            $table->time('jadwal_jam_pulang')->nullable()->after('jumlah_hari_kerja');
            $table->boolean('upacara_senin_aktif')->default(true)->after('jadwal_jam_pulang');
            $table->unsignedSmallInteger('durasi_upacara_senin')->default(30)->after('upacara_senin_aktif');
            $table->boolean('religi_harian_aktif')->default(true)->after('durasi_upacara_senin');
            $table->unsignedSmallInteger('durasi_religi_harian')->default(15)->after('religi_harian_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_pelajaran', function (Blueprint $table) {
            $table->dropColumn([
                'jadwal_jam_pulang',
                'upacara_senin_aktif',
                'durasi_upacara_senin',
                'religi_harian_aktif',
                'durasi_religi_harian',
            ]);
        });
    }
};
