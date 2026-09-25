<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_nomor_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->string('prefix', 20)->default('B');
            $table->string('kode_satuan_kerja', 50)->default('Ma.08.01');
            $table->string('kode_klasifikasi', 50);
            $table->string('format_nomor', 255)->default('{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}');
            $table->unsignedTinyInteger('panjang_nomor')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('surat_nomor_counters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('nomor_terakhir')->default(0);
            $table->timestamps();
            $table->unique('tahun');
        });

        DB::table('surat_nomor_configs')->insert([
            [
                'id' => (string) Str::uuid(), 'kode' => 'PP', 'nama' => 'Surat Keterangan / Surat Biasa',
                'prefix' => 'B', 'kode_satuan_kerja' => 'Ma.08.01', 'kode_klasifikasi' => 'PP.01.1',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(), 'kode' => 'KP', 'nama' => 'Surat Kepegawaian',
                'prefix' => 'B', 'kode_satuan_kerja' => 'Ma.08.01', 'kode_klasifikasi' => 'KP.01.1',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_nomor_counters');
        Schema::dropIfExists('surat_nomor_configs');
    }
};
