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
        Schema::table('surat_nomor_configs', function (Blueprint $table) {
            $table->string('kode', 20)->change();
        });

        DB::table('surat_nomor_configs')->where('kode', 'PP')->update([
            'nama' => 'Surat Keterangan Kesiswaan (PP.00.6)',
            'kode_klasifikasi' => 'PP.00.6',
            'updated_at' => now(),
        ]);

        $defaultConfigs = [
            [
                'kode' => 'PP-UJIAN',
                'nama' => 'Asesmen, Ujian, Ijazah & SKL (PP.01.1)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'PP.01.1',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'KP-CUTI',
                'nama' => 'Izin & Cuti Guru / Pegawai (KP.01.2)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'KP.01.2',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'KS',
                'nama' => 'Dispensasi & Panggilan Siswa (KS.01.2)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'KS.01.2',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'HM',
                'nama' => 'Undangan Rapat & Hubungan Masyarakat (HM.00)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'HM.00',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'KU',
                'nama' => 'Administrasi Keuangan & Bebas Biaya (KU.01.1)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'KU.01.1',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'BA',
                'nama' => 'Berita Acara (BA)',
                'prefix' => 'BA',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'PP.01.1',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/{kode_klasifikasi}/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'SK',
                'nama' => 'Surat Keputusan Kepala Madrasah (SK)',
                'prefix' => 'B',
                'kode_satuan_kerja' => 'Ma.08.01',
                'kode_klasifikasi' => 'KP.01',
                'format_nomor' => '{prefix}-{nomor}/{kode_satuan_kerja}/KP.01/{bulan}/{tahun}',
                'panjang_nomor' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($defaultConfigs as $item) {
            $existing = DB::table('surat_nomor_configs')->where('kode', $item['kode'])->first();
            if (!$existing) {
                DB::table('surat_nomor_configs')->insert(array_merge($item, [
                    'id' => (string) Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('surat_nomor_configs')->whereIn('kode', [
            'PP-UJIAN', 'KP-CUTI', 'KS', 'HM', 'KU', 'BA', 'SK'
        ])->delete();
    }
};
