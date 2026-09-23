<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->string('jenis_tamu', 20)->default('individu')->after('tanggal_kunjungan')->index();
            $table->text('alamat')->nullable()->after('nomor_hp');
            $table->string('provinsi_code', 10)->nullable()->after('alamat')->index();
            $table->string('kota_code', 10)->nullable()->after('provinsi_code')->index();
            $table->string('kecamatan_code', 10)->nullable()->after('kota_code')->index();
            $table->string('kelurahan_code', 10)->nullable()->after('kecamatan_code')->index();
            $table->uuid('buku_tamu_qr_token_id')->nullable()->after('kelurahan_code')->index();

            $table->foreign('buku_tamu_qr_token_id')->references('id')->on('buku_tamu_qr_tokens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropForeign(['buku_tamu_qr_token_id']);
            $table->dropColumn([
                'jenis_tamu', 'alamat', 'provinsi_code', 'kota_code',
                'kecamatan_code', 'kelurahan_code', 'buku_tamu_qr_token_id',
            ]);
        });
    }
};
