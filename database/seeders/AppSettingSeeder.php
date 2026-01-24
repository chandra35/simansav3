<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;
use Illuminate\Support\Str;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Kop Surat Config (Builder Mode)
        $defaultKopConfig = [
            'mode' => 'builder',
            'elements' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'content' => 'KEMENTERIAN AGAMA REPUBLIK INDONESIA',
                    'style' => [
                        'fontSize' => '14',
                        'fontWeight' => 'bold',
                        'textAlign' => 'center',
                        'marginBottom' => '2',
                    ],
                    'order' => 1,
                ],
                [
                    'id' => 2,
                    'type' => 'text',
                    'content' => 'KEMENTERIAN AGAMA KOTA METRO',
                    'style' => [
                        'fontSize' => '14',
                        'fontWeight' => 'bold',
                        'textAlign' => 'center',
                        'marginBottom' => '2',
                    ],
                    'order' => 2,
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'content' => 'MADRASAH ALIYAH NEGERI 1',
                    'style' => [
                        'fontSize' => '16',
                        'fontWeight' => 'bold',
                        'textAlign' => 'center',
                        'marginBottom' => '5',
                    ],
                    'order' => 3,
                ],
                [
                    'id' => 4,
                    'type' => 'text',
                    'content' => 'Jl. Ki Hajar Dewantara No.110 Kampus 15A Telp/Fax (0725) 45963',
                    'style' => [
                        'fontSize' => '10',
                        'fontWeight' => 'normal',
                        'textAlign' => 'center',
                        'marginBottom' => '5',
                    ],
                    'order' => 4,
                ],
                [
                    'id' => 5,
                    'type' => 'divider',
                    'style' => [
                        'borderStyle' => 'double',
                        'borderWidth' => '3',
                        'borderColor' => '#000000',
                        'marginTop' => '5',
                        'marginBottom' => '5',
                    ],
                    'order' => 5,
                ],
            ],
        ];

        AppSetting::create([
            'id' => Str::uuid(),
            'nama_sekolah' => 'MTs NEGERI 1 KOTA KUPANG',
            'npsn' => '50303062',
            
            // Logo paths (akan diupload manual via form)
            'logo_kemenag_path' => null,
            'logo_sekolah_path' => null,
            
            // Alamat (Kupang, NTT)
            'alamat' => 'Jl. Timor Raya No. 81',
            'rt' => '001',
            'rw' => '002',
            'kelurahan_code' => '5371010001', // Oesapa (contoh)
            'kecamatan_code' => '5371010', // Kelapa Lima
            'kota_code' => '5371', // Kota Kupang
            'provinsi_code' => '53', // Nusa Tenggara Timur
            'kode_pos' => '85228',
            
            // Kontak
            'telepon' => '0380-8553728',
            'email' => 'mtsn1kotakupang@kemenag.go.id',
            'website' => 'https://mtsn1kotakupang.sch.id',
            
            // Sosial Media (nullable, bisa diisi nanti)
            'facebook_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'twitter_url' => null,
            
            // Kop Surat Configuration
            'kop_mode' => 'builder',
            'kop_surat_config' => $defaultKopConfig,
            'kop_surat_custom_path' => null,
            'kop_margin_top' => 0,
            'kop_height' => 30,
        ]);

        $this->command->info('✓ AppSetting seeded successfully with default kop surat config');
    }
}
