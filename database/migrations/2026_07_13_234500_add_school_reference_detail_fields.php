<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $columns = [
                'jenjang_pendidikan' => fn () => $table->string('jenjang_pendidikan', 50)->nullable()->after('bentuk_pendidikan'),
                'kementerian_pembina' => fn () => $table->string('kementerian_pembina', 100)->nullable()->after('jenjang_pendidikan'),
                'npyp' => fn () => $table->string('npyp', 50)->nullable()->after('kementerian_pembina'),
                'no_sk_pendirian' => fn () => $table->string('no_sk_pendirian', 150)->nullable()->after('npyp'),
                'tanggal_sk_pendirian' => fn () => $table->string('tanggal_sk_pendirian', 30)->nullable()->after('no_sk_pendirian'),
                'no_sk_operasional' => fn () => $table->string('no_sk_operasional', 150)->nullable()->after('tanggal_sk_pendirian'),
                'tanggal_sk_operasional' => fn () => $table->string('tanggal_sk_operasional', 30)->nullable()->after('no_sk_operasional'),
                'akreditasi' => fn () => $table->string('akreditasi', 20)->nullable()->after('tanggal_sk_operasional'),
                'luas_tanah' => fn () => $table->string('luas_tanah', 50)->nullable()->after('akreditasi'),
                'akses_internet' => fn () => $table->string('akses_internet', 150)->nullable()->after('luas_tanah'),
                'sumber_listrik' => fn () => $table->string('sumber_listrik', 100)->nullable()->after('akses_internet'),
                'rt' => fn () => $table->string('rt', 10)->nullable()->after('alamat_jalan'),
                'rw' => fn () => $table->string('rw', 10)->nullable()->after('rt'),
                'kode_pos' => fn () => $table->string('kode_pos', 10)->nullable()->after('provinsi'),
                'telepon' => fn () => $table->string('telepon', 50)->nullable()->after('kode_pos'),
                'email' => fn () => $table->string('email', 150)->nullable()->after('telepon'),
                'website' => fn () => $table->string('website', 150)->nullable()->after('email'),
                'operator' => fn () => $table->string('operator', 150)->nullable()->after('website'),
                'lintang' => fn () => $table->decimal('lintang', 11, 8)->nullable()->after('operator'),
                'bujur' => fn () => $table->decimal('bujur', 11, 8)->nullable()->after('lintang'),
                'sumber_data_sekolah' => fn () => $table->string('sumber_data_sekolah', 100)->nullable()->after('bujur'),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('sekolah', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $columns = [
                'jenjang_pendidikan',
                'kementerian_pembina',
                'npyp',
                'no_sk_pendirian',
                'tanggal_sk_pendirian',
                'no_sk_operasional',
                'tanggal_sk_operasional',
                'akreditasi',
                'luas_tanah',
                'akses_internet',
                'sumber_listrik',
                'rt',
                'rw',
                'kode_pos',
                'telepon',
                'email',
                'website',
                'operator',
                'lintang',
                'bujur',
                'sumber_data_sekolah',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('sekolah', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
