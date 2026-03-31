<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbsensiSetting;
use Illuminate\Support\Str;

class AbsensiSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // WAKTU
            [
                'key' => 'jam_masuk_gtk',
                'value' => '07:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Masuk GTK',
                'description' => 'Jam masuk GTK (format HH:mm)',
            ],
            [
                'key' => 'jam_pulang_gtk',
                'value' => '16:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Pulang GTK',
                'description' => 'Jam pulang GTK (format HH:mm)',
            ],
            [
                'key' => 'jam_masuk_siswa',
                'value' => '06:45',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Masuk Siswa',
                'description' => 'Jam masuk siswa untuk kiosk/pintu gerbang',
            ],
            [
                'key' => 'jam_pulang_siswa',
                'value' => '15:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Pulang Siswa',
                'description' => 'Jam pulang siswa untuk kiosk/pintu gerbang',
            ],
            [
                'key' => 'toleransi_terlambat',
                'value' => '15',
                'type' => 'integer',
                'group' => 'waktu',
                'label' => 'Toleransi Terlambat (menit)',
                'description' => 'Menit toleransi sebelum dianggap terlambat',
            ],
            [
                'key' => 'batas_absen_masuk',
                'value' => '10:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Batas Akhir Absen Masuk',
                'description' => 'Setelah jam ini, tidak bisa absen masuk (harus input manual)',
            ],

            // FACE RECOGNITION
            [
                'key' => 'face_match_threshold',
                'value' => '0.45',
                'type' => 'float',
                'group' => 'face',
                'label' => 'Face Match Threshold',
                'description' => 'Jarak Euclidean maks untuk cocok (semakin kecil = semakin ketat, rekomendasi: 0.4-0.5)',
            ],
            [
                'key' => 'liveness_detection',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'face',
                'label' => 'Liveness Detection',
                'description' => 'Aktifkan deteksi liveness (anti-foto) saat registrasi wajah',
            ],
            [
                'key' => 'min_face_quality',
                'value' => '0.5',
                'type' => 'float',
                'group' => 'face',
                'label' => 'Minimum Face Quality Score',
                'description' => 'Score minimum deteksi wajah untuk diterima (0.0 - 1.0)',
            ],
            [
                'key' => 'require_face_verification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'face',
                'label' => 'Wajib Verifikasi Admin',
                'description' => 'Apakah data wajah harus diverifikasi admin sebelum bisa digunakan',
            ],

            // KIOSK
            [
                'key' => 'auto_face_detect',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'kiosk',
                'label' => 'Auto Face Detect',
                'description' => 'Deteksi wajah otomatis di mode kiosk (tanpa klik)',
            ],
            [
                'key' => 'detection_interval_ms',
                'value' => '200',
                'type' => 'integer',
                'group' => 'kiosk',
                'label' => 'Detection Interval (ms)',
                'description' => 'Interval deteksi wajah dalam milidetik (semakin kecil = semakin responsif tapi lebih berat)',
            ],
            [
                'key' => 'match_cooldown_ms',
                'value' => '5000',
                'type' => 'integer',
                'group' => 'kiosk',
                'label' => 'Match Cooldown (ms)',
                'description' => 'Jeda waktu antar matching orang yang sama (mencegah duplikat)',
            ],
            [
                'key' => 'capture_photo_on_attendance',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'kiosk',
                'label' => 'Simpan Foto Saat Absensi',
                'description' => 'Capture dan simpan foto wajah saat absensi untuk bukti',
            ],
            [
                'key' => 'play_sound_on_success',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'kiosk',
                'label' => 'Bunyi Saat Berhasil',
                'description' => 'Mainkan suara saat absensi berhasil dicatat',
            ],

            // GENERAL
            [
                'key' => 'absensi_aktif',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Sistem Absensi Aktif',
                'description' => 'Aktif/nonaktifkan seluruh sistem absensi wajah',
            ],
            [
                'key' => 'absen_hari_sabtu',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Absensi Hari Sabtu',
                'description' => 'Izinkan absensi di hari Sabtu',
            ],
            [
                'key' => 'nama_sekolah',
                'value' => 'MAN 1 Lampung Timur',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nama Sekolah',
                'description' => 'Ditampilkan di halaman kiosk dan laporan',
            ],
        ];

        foreach ($settings as $setting) {
            AbsensiSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✓ Absensi settings seeded: ' . count($settings) . ' settings');
    }
}
