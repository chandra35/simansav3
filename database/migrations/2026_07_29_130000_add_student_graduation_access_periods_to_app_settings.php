<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->foreignUuid('graduation_announcement_tahun_pelajaran_id')
                ->nullable()
                ->after('graduation_announcement_starts_at')
                ->constrained('tahun_pelajaran')
                ->nullOnDelete();
            $table->boolean('lulusan_data_enabled')
                ->default(false)
                ->after('graduation_announcement_tahun_pelajaran_id');
            $table->timestamp('lulusan_data_starts_at')->nullable()->after('lulusan_data_enabled');
            $table->timestamp('lulusan_data_ends_at')->nullable()->after('lulusan_data_starts_at');
            $table->foreignUuid('lulusan_data_tahun_pelajaran_id')
                ->nullable()
                ->after('lulusan_data_ends_at')
                ->constrained('tahun_pelajaran')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lulusan_data_tahun_pelajaran_id');
            $table->dropConstrainedForeignId('graduation_announcement_tahun_pelajaran_id');
            $table->dropColumn([
                'lulusan_data_enabled',
                'lulusan_data_starts_at',
                'lulusan_data_ends_at',
            ]);
        });
    }
};
