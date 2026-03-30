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
        Schema::create('referensi_perguruan_tinggi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->unique();
            $table->string('jenis', 50);
            $table->string('sumber_referensi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        $source = 'kurasi awal resmi';

        $references = [
            ['nama' => 'Universitas Indonesia', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Gadjah Mada', 'jenis' => 'PTN'],
            ['nama' => 'Institut Teknologi Bandung', 'jenis' => 'PTN'],
            ['nama' => 'Institut Pertanian Bogor', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Padjadjaran', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Airlangga', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Diponegoro', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Sebelas Maret', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Brawijaya', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Negeri Yogyakarta', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Negeri Semarang', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Negeri Malang', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Lampung', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Sriwijaya', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Hasanuddin', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Negeri Jakarta', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Jenderal Soedirman', 'jenis' => 'PTN'],
            ['nama' => 'Universitas Andalas', 'jenis' => 'PTN'],
            ['nama' => 'UIN Syarif Hidayatullah Jakarta', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Sunan Kalijaga Yogyakarta', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Maulana Malik Ibrahim Malang', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Raden Intan Lampung', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Raden Fatah Palembang', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Sultan Syarif Kasim Riau', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Sunan Gunung Djati Bandung', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Walisongo Semarang', 'jenis' => 'PTKIN'],
            ['nama' => 'UIN Alauddin Makassar', 'jenis' => 'PTKIN'],
            ['nama' => 'IAIN Metro', 'jenis' => 'PTKIN'],
            ['nama' => 'IAIN Kediri', 'jenis' => 'PTKIN'],
            ['nama' => 'IAIN Curup', 'jenis' => 'PTKIN'],
            ['nama' => 'IAIN Ponorogo', 'jenis' => 'PTKIN'],
            ['nama' => 'STAIN Teungku Dirundeng Meulaboh', 'jenis' => 'PTKIN'],
            ['nama' => 'Poltekkes Kemenkes Jakarta I', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Jakarta II', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Jakarta III', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Bandung', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Semarang', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Yogyakarta', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Surabaya', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Malang', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Palembang', 'jenis' => 'Poltekkes'],
            ['nama' => 'Poltekkes Kemenkes Tanjungkarang', 'jenis' => 'Poltekkes'],
        ];

        DB::table('referensi_perguruan_tinggi')->insert(
            collect($references)->map(fn (array $reference) => [
                'id' => (string) Str::uuid(),
                'nama' => $reference['nama'],
                'jenis' => $reference['jenis'],
                'sumber_referensi' => $source,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('referensi_perguruan_tinggi');
    }
};
