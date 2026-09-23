<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_masuk')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                if (Schema::hasColumn('surat_masuk', 'asal_id')) {
                    $table->dropForeign(['asal_id']);
                    $table->dropColumn('asal_id');
                }
            });
        }

        Schema::dropIfExists('surat_masuk_asal');
    }

    public function down(): void
    {
        // Master asal tidak dipulihkan karena data utama tetap berada di surat_masuk.asal.
    }
};
