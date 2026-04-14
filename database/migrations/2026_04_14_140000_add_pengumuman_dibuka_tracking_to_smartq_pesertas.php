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
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->timestamp('pengumuman_dibuka_at')->nullable()->after('peringkat_mapel');
            $table->string('pengumuman_dibuka_ip', 45)->nullable()->after('pengumuman_dibuka_at');
            $table->text('pengumuman_dibuka_user_agent')->nullable()->after('pengumuman_dibuka_ip');
            $table->index('pengumuman_dibuka_at', 'smartq_pesertas_pengumuman_dibuka_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smartq_pesertas', function (Blueprint $table) {
            $table->dropIndex('smartq_pesertas_pengumuman_dibuka_at_index');
            $table->dropColumn([
                'pengumuman_dibuka_at',
                'pengumuman_dibuka_ip',
                'pengumuman_dibuka_user_agent',
            ]);
        });
    }
};
