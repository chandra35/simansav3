<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestasi_siswa', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable()->after('tahun_pelajaran_id')->index();
            $table->string('bidang')->nullable()->after('nama_prestasi');
            $table->enum('tipe_peserta', ['individu', 'tim'])->nullable()->after('tingkat');
            $table->string('perolehan_prestasi')->nullable()->after('peringkat');
            $table->string('peringkat_nama')->nullable()->after('peringkat');
            $table->text('nama_siswa_manual')->nullable()->after('siswa_id');
            $table->uuid('siswa_id')->nullable()->change();
        });

        Schema::create('prestasi_siswa_peserta', function (Blueprint $table) {
            $table->uuid('prestasi_siswa_id');
            $table->uuid('siswa_id');
            $table->timestamps();
            $table->primary(['prestasi_siswa_id', 'siswa_id']);
            $table->foreign('prestasi_siswa_id')->references('id')->on('prestasi_siswa')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
        });

        DB::table('prestasi_siswa')->whereNull('tahun')->update([
            'tahun' => DB::raw('YEAR(tanggal_prestasi)'),
            'peringkat_nama' => DB::raw("CASE peringkat
                WHEN 'juara_1' THEN 'Juara 1' WHEN 'juara_2' THEN 'Juara 2' WHEN 'juara_3' THEN 'Juara 3'
                WHEN 'harapan_1' THEN 'Harapan 1' WHEN 'harapan_2' THEN 'Harapan 2' WHEN 'harapan_3' THEN 'Harapan 3'
                WHEN 'peserta' THEN 'Peserta' WHEN 'finalis' THEN 'Finalis' ELSE NULL END"),
        ]);

        DB::table('prestasi_siswa')->orderBy('id')->each(function ($prestasi) {
            if ($prestasi->siswa_id) {
                DB::table('prestasi_siswa_peserta')->insertOrIgnore([
                    'prestasi_siswa_id' => $prestasi->id, 'siswa_id' => $prestasi->siswa_id,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi_siswa_peserta');
        Schema::table('prestasi_siswa', function (Blueprint $table) {
            $table->dropColumn(['tahun', 'bidang', 'tipe_peserta', 'perolehan_prestasi', 'peringkat_nama', 'nama_siswa_manual']);
        });
    }
};
