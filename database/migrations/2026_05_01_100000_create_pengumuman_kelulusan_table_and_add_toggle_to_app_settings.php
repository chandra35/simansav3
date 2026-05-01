<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('graduation_announcement_enabled')
                ->default(false)
                ->after('activity_log_require_location');
        });

        Schema::create('pengumuman_kelulusan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->uuid('siswa_id');
            $table->uuid('kelas_id')->nullable();
            $table->enum('status', ['lulus', 'lulus_bersyarat', 'tidak_lulus']);
            $table->text('catatan')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->string('opened_ip', 45)->nullable();
            $table->text('opened_user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun_pelajaran_id', 'siswa_id'], 'pengumuman_kelulusan_tahun_siswa_unique');
            $table->index(['tahun_pelajaran_id', 'status']);

            $table->foreign('tahun_pelajaran_id')
                ->references('id')
                ->on('tahun_pelajaran')
                ->cascadeOnDelete();

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->cascadeOnDelete();

            $table->foreign('kelas_id')
                ->references('id')
                ->on('kelas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman_kelulusan');

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('graduation_announcement_enabled');
        });
    }
};
