<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('gtk_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->foreignUuid('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignUuid('jadwal_pelajaran_id')->nullable()->constrained('jadwal_pelajaran')->nullOnDelete();
            $table->string('jenis', 20);
            $table->date('tanggal');
            $table->string('mapel_snapshot')->nullable();
            $table->string('kelas_snapshot')->nullable();
            $table->string('topik');
            $table->text('kegiatan');
            $table->text('asesmen_tindak_lanjut')->nullable();
            $table->unsignedSmallInteger('durasi_menit')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tahun_pelajaran_id', 'tanggal'], 'teacher_journal_owner_date_index');
            $table->index(['tahun_pelajaran_id', 'jenis', 'tanggal'], 'teacher_journal_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_journals');
    }
};
