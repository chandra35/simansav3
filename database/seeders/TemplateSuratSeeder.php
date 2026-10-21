<?php

namespace Database\Seeders;

use App\Models\TemplateSurat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSuratSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTemplateContent = <<<'HTML'
<p>Yang bertanda tangan di bawah ini :</p>
<table class="table-data">
    <tr>
        <td style="width: 25%;">Nama</td>
        <td style="width: 3%;">:</td>
        <td>{{nama_pejabat}}</td>
    </tr>
    <tr>
        <td>NIP</td>
        <td>:</td>
        <td>{{nip_pejabat}}</td>
    </tr>
    <tr>
        <td>Pangkat / Golongan</td>
        <td>:</td>
        <td>{{pangkat_golongan_pejabat}}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>{{jabatan_pejabat}}</td>
    </tr>
    <tr>
        <td>Instansi</td>
        <td>:</td>
        <td>{{instansi_pejabat}}</td>
    </tr>
</table>

<p style="margin-top: 15px;">Menerangkan dengan sebenarnya bahwa :</p>
<table class="table-data">
    <tr>
        <td style="width: 25%;">Nama Lengkap</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{nama_siswa}}</strong></td>
    </tr>
    <tr>
        <td>Tempat/Tanggal Lahir</td>
        <td>:</td>
        <td>{{tempat_lahir}}, {{tanggal_lahir}}</td>
    </tr>
    <tr>
        <td>NISN</td>
        <td>:</td>
        <td>{{nisn}}</td>
    </tr>
    <tr>
        <td colspan="3">Nama Orang Tua</td>
    </tr>
    <tr>
        <td style="padding-left: 20px;">a. Ayah</td>
        <td>:</td>
        <td>{{nama_ayah}}</td>
    </tr>
    <tr>
        <td style="padding-left: 20px;">b. Ibu</td>
        <td>:</td>
        <td>{{nama_ibu}}</td>
    </tr>
</table>

<p style="margin-top: 15px; text-align: justify;">
    adalah benar-benar siswa {{nama_sekolah}} kelas {{kelas}} tahun pelajaran {{tahun_pelajaran}}.
</p>

<p style="margin-top: 15px; text-align: justify;">
    Surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagai persyaratan kelengkapan berkas {{keperluan}}.
</p>
HTML;

        TemplateSurat::updateOrCreate(
            ['kode' => 'SK-AKTIF'],
            [
                'nama' => 'Surat Keterangan Aktif Siswa',
                'jenis' => 'sk_aktif',
                'konten' => $defaultTemplateContent,
                'is_active' => true,
            ]
        );
    }
}
