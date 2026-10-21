<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_keterangan') && !Schema::hasColumn('surat_keterangan', 'data_tambahan')) {
            Schema::table('surat_keterangan', function (Blueprint $table) {
                $table->json('data_tambahan')->nullable()->after('jumlah_cetak');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('surat_keterangan') && Schema::hasColumn('surat_keterangan', 'data_tambahan')) {
            Schema::table('surat_keterangan', function (Blueprint $table) {
                $table->dropColumn('data_tambahan');
            });
        }
    }
};
