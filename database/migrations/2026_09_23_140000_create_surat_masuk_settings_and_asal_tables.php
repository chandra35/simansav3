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
        Schema::create('surat_masuk_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('nomor_terakhir')->default(360);
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('surat_masuk_settings')->insert([
            'id' => (string) Str::uuid(),
            'nomor_terakhir' => 360,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('surat_masuk_asal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 255)->unique();
            $table->string('jenis', 40)->default('instansi');
            $table->string('sumber', 60)->default('surat_masuk');
            $table->string('referensi_id', 100)->nullable();
            $table->unsignedInteger('jumlah_surat')->default(0);
            $table->timestamps();
            $table->index(['jenis', 'nama']);
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->foreignUuid('asal_id')->nullable()->after('asal')->constrained('surat_masuk_asal')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropForeign(['asal_id']);
            $table->dropColumn('asal_id');
        });
        Schema::dropIfExists('surat_masuk_asal');
        Schema::dropIfExists('surat_masuk_settings');
    }
};
