<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmailTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'subject',
        'body',
        'description',
        'available_placeholders',
        'is_active',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Available placeholder definitions
     */
    public static function getPlaceholderDefinitions(): array
    {
        return [
            // Siswa placeholders
            'siswa' => [
                '[nama_siswa]' => 'Nama lengkap siswa',
                '[nisn]' => 'NISN siswa',
                '[nis]' => 'NIS siswa',
                '[kelas]' => 'Kelas siswa',
                '[email_siswa]' => 'Email siswa',
                '[jenis_kelamin]' => 'Jenis kelamin (L/P)',
                '[tempat_lahir]' => 'Tempat lahir',
                '[tanggal_lahir]' => 'Tanggal lahir',
                '[alamat_siswa]' => 'Alamat siswa',
            ],
            // Orang Tua placeholders
            'ortu' => [
                '[nama_ayah]' => 'Nama ayah',
                '[nama_ibu]' => 'Nama ibu',
                '[hp_ayah]' => 'No HP ayah',
                '[hp_ibu]' => 'No HP ibu',
                '[alamat_ortu]' => 'Alamat orang tua',
            ],
            // GTK/Guru placeholders
            'gtk' => [
                '[nama_gtk]' => 'Nama GTK/Guru',
                '[nip]' => 'NIP GTK',
                '[nuptk]' => 'NUPTK GTK',
                '[email_gtk]' => 'Email GTK',
                '[jabatan]' => 'Jabatan GTK',
                '[hp_gtk]' => 'No HP GTK',
            ],
            // User/Account placeholders
            'user' => [
                '[nama_user]' => 'Nama user',
                '[username]' => 'Username',
                '[email]' => 'Email user',
                '[role]' => 'Role/Peran user',
            ],
            // System placeholders
            'system' => [
                '[nama_sekolah]' => 'Nama sekolah/madrasah',
                '[logo_sekolah]' => 'Logo sekolah (HTML img)',
                '[alamat_sekolah]' => 'Alamat sekolah',
                '[telepon_sekolah]' => 'Telepon sekolah',
                '[email_sekolah]' => 'Email sekolah',
                '[website_sekolah]' => 'Website sekolah',
                '[tahun_pelajaran]' => 'Tahun pelajaran aktif',
                '[tanggal_sekarang]' => 'Tanggal saat ini',
                '[waktu_sekarang]' => 'Waktu saat ini',
            ],
            // Action placeholders
            'action' => [
                '[reset_link]' => 'Link reset password',
                '[login_link]' => 'Link login',
                '[verification_link]' => 'Link verifikasi',
                '[action_url]' => 'URL aksi khusus',
                '[waktu]' => 'Waktu aksi dilakukan',
            ],
        ];
    }

    /**
     * Get all placeholders as flat array
     */
    public static function getAllPlaceholders(): array
    {
        $all = [];
        foreach (self::getPlaceholderDefinitions() as $group => $placeholders) {
            $all = array_merge($all, $placeholders);
        }
        return $all;
    }

    /**
     * Get template by code
     */
    public static function getByCode(string $code): ?self
    {
        return self::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Replace placeholders in subject/body
     */
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Add system placeholders
        $settings = AppSetting::getInstance();
        
        // Generate logo HTML if exists
        $logoHtml = '';
        if ($settings->logo_sekolah && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings->logo_sekolah)) {
            $logoUrl = url('storage/' . $settings->logo_sekolah);
            $logoHtml = '<img src="' . $logoUrl . '" alt="Logo" style="max-width: 80px; height: auto; margin-bottom: 10px; border-radius: 8px;">';
        }

        $systemData = [
            '[nama_sekolah]' => $settings->nama_sekolah ?? 'SIMANSA',
            '[alamat_sekolah]' => $settings->alamat ?? '',
            '[telepon_sekolah]' => $settings->telepon ?? '',
            '[email_sekolah]' => $settings->email ?? '',
            '[website_sekolah]' => $settings->website ?? '',
            '[tahun_pelajaran]' => $settings->tahunPelajaranAktif?->nama ?? '',
            '[tanggal_sekarang]' => now()->format('d F Y'),
            '[waktu_sekarang]' => now()->format('H:i:s'),
            '[login_link]' => url('/login'),
            '[logo_sekolah]' => $logoHtml,
        ];

        $data = array_merge($systemData, $data);

        // Replace all placeholders
        foreach ($data as $placeholder => $value) {
            $subject = str_replace($placeholder, $value ?? '', $subject);
            $body = str_replace($placeholder, $value ?? '', $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Preview with sample data
     */
    public function preview(): array
    {
        $sampleData = [
            // Siswa
            '[nama_siswa]' => 'Ahmad Fauzi',
            '[nisn]' => '0012345678',
            '[nis]' => '12345',
            '[kelas]' => 'XII IPA 1',
            '[email_siswa]' => 'ahmad.fauzi@student.sch.id',
            '[jenis_kelamin]' => 'Laki-laki',
            '[tempat_lahir]' => 'Metro',
            '[tanggal_lahir]' => '15 Januari 2008',
            '[alamat_siswa]' => 'Jl. Sudirman No. 123, Metro',
            // Ortu
            '[nama_ayah]' => 'Budi Santoso',
            '[nama_ibu]' => 'Siti Aminah',
            '[hp_ayah]' => '081234567890',
            '[hp_ibu]' => '081234567891',
            '[alamat_ortu]' => 'Jl. Sudirman No. 123, Metro',
            // GTK
            '[nama_gtk]' => 'Drs. Hasan Basri, M.Pd',
            '[nip]' => '198001012005011001',
            '[nuptk]' => '1234567890123456',
            '[email_gtk]' => 'hasan.basri@sch.id',
            '[jabatan]' => 'Guru Matematika',
            '[hp_gtk]' => '082345678901',
            // User
            '[nama_user]' => 'John Doe',
            '[username]' => 'johndoe',
            '[email]' => 'john@example.com',
            '[role]' => 'Admin',
            // Action
            '[reset_link]' => url('/reset-password/sample-token'),
            '[verification_link]' => url('/verify/sample-token'),
            '[action_url]' => url('/action/sample'),
        ];

        return $this->render($sampleData);
    }

    /**
     * Creator relationship
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updater relationship
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Seed default templates
     */
    public static function seedDefaults(): void
    {
        $templates = [
            [
                'code' => 'password_reset',
                'name' => 'Reset Password',
                'subject' => 'Reset Password - [nama_sekolah]',
                'body' => self::getDefaultPasswordResetBody(),
                'description' => 'Template email untuk reset password user',
                'available_placeholders' => ['user', 'action', 'system'],
                'is_system' => true,
            ],
            [
                'code' => 'test_email',
                'name' => 'Test Email',
                'subject' => 'Test Email - [nama_sekolah]',
                'body' => self::getDefaultTestEmailBody(),
                'description' => 'Template untuk test konfigurasi SMTP',
                'available_placeholders' => ['system'],
                'is_system' => true,
            ],
            [
                'code' => 'welcome_siswa',
                'name' => 'Selamat Datang Siswa',
                'subject' => 'Selamat Datang di [nama_sekolah]',
                'body' => self::getDefaultWelcomeSiswaBody(),
                'description' => 'Template email selamat datang untuk siswa baru',
                'available_placeholders' => ['siswa', 'user', 'system'],
                'is_system' => false,
            ],
            [
                'code' => 'notification_general',
                'name' => 'Notifikasi Umum',
                'subject' => 'Pemberitahuan dari [nama_sekolah]',
                'body' => self::getDefaultNotificationBody(),
                'description' => 'Template notifikasi umum',
                'available_placeholders' => ['user', 'system'],
                'is_system' => false,
            ],
            [
                'code' => 'password_changed',
                'name' => 'Password Berhasil Diubah',
                'subject' => 'Password Berhasil Diubah - [nama_sekolah]',
                'body' => self::getDefaultPasswordChangedBody(),
                'description' => 'Template notifikasi saat password berhasil diubah',
                'available_placeholders' => ['user', 'system'],
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            self::updateOrCreate(
                ['code' => $template['code']],
                $template
            );
        }
    }

    private static function getDefaultPasswordResetBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; color: #666; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; border: 1px solid #e0e0e0; border-top: none; }
        .btn { display: inline-block; background: #007bff; color: white !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        h1, h2 { margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 [nama_sekolah]</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Reset Password</p>
        </div>
        <div class="content">
            <h2>Halo, [nama_user]!</h2>
            <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
            <p>Klik tombol di bawah ini untuk membuat password baru:</p>
            
            <p style="text-align: center;">
                <a href="[reset_link]" class="btn">Reset Password</a>
            </p>
            
            <div class="warning">
                <strong>⚠️ Perhatian:</strong><br>
                Link ini akan kadaluarsa dalam 60 menit.<br>
                Jika Anda tidak meminta reset password, abaikan email ini.
            </div>
            
            <p style="font-size: 12px; color: #666;">
                Jika tombol tidak berfungsi, copy link berikut ke browser:<br>
                <a href="[reset_link]">[reset_link]</a>
            </p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem [nama_sekolah]</p>
            <p>[alamat_sekolah] | [telepon_sekolah]</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private static function getDefaultTestEmailBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; color: #666; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
        .info-box { background: #e8f4fc; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✉️ [nama_sekolah]</h1>
            <p style="margin: 10px 0 0 0;">Test Email Berhasil!</p>
        </div>
        <div class="content">
            <p style="text-align: center; font-size: 64px; margin: 0;">✅</p>
            <h2 style="text-align: center; color: #28a745;">Konfigurasi SMTP Berhasil!</h2>
            
            <div class="success">
                <strong>Selamat!</strong> Email test ini berhasil dikirim, yang berarti konfigurasi SMTP sudah benar.
            </div>
            
            <div class="info-box">
                <strong>📋 Detail Pengiriman:</strong><br>
                Waktu: [tanggal_sekarang] [waktu_sekarang]
            </div>
            
            <p><strong>Fitur yang dapat menggunakan email:</strong></p>
            <ul>
                <li>Reset Password</li>
                <li>Notifikasi Sistem</li>
                <li>Pengumuman</li>
                <li>Dan lainnya...</li>
            </ul>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem [nama_sekolah]</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private static function getDefaultWelcomeSiswaBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; color: #666; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .info-box { background: #e8f4fc; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; }
        .btn { display: inline-block; background: #28a745; color: white !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Selamat Datang!</h1>
            <p style="margin: 10px 0 0 0;">[nama_sekolah]</p>
        </div>
        <div class="content">
            <h2>Halo, [nama_siswa]!</h2>
            <p>Selamat datang di <strong>[nama_sekolah]</strong>. Akun Anda telah berhasil dibuat.</p>
            
            <div class="info-box">
                <strong>📋 Informasi Akun:</strong><br>
                <strong>NISN:</strong> [nisn]<br>
                <strong>Username:</strong> [username]<br>
                <strong>Email:</strong> [email_siswa]
            </div>
            
            <p>Silakan login ke sistem untuk melengkapi data diri Anda:</p>
            
            <p style="text-align: center;">
                <a href="[login_link]" class="btn">Login Sekarang</a>
            </p>
            
            <p><strong>Langkah selanjutnya:</strong></p>
            <ol>
                <li>Login dengan username dan password default (NISN)</li>
                <li>Ubah password Anda</li>
                <li>Lengkapi data diri dan data orang tua</li>
                <li>Upload dokumen yang diperlukan</li>
            </ol>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem [nama_sekolah]</p>
            <p>[alamat_sekolah]</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private static function getDefaultNotificationBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; color: #666; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Pemberitahuan</h1>
            <p style="margin: 10px 0 0 0;">[nama_sekolah]</p>
        </div>
        <div class="content">
            <h2>Halo, [nama_user]!</h2>
            <p>Ini adalah pemberitahuan dari sistem [nama_sekolah].</p>
            
            <p>Tanggal: [tanggal_sekarang]</p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem [nama_sekolah]</p>
            <p>[alamat_sekolah] | [telepon_sekolah]</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private static function getDefaultPasswordChangedBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .logo { max-width: 80px; height: auto; margin-bottom: 10px; border-radius: 8px; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; color: #666; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .success-box { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        h1, h2 { margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            [logo_sekolah]
            <h1>🔒 Notifikasi Keamanan</h1>
            <p style="margin: 10px 0 0 0;">[nama_sekolah]</p>
        </div>
        <div class="content">
            <p style="text-align: center; font-size: 48px; margin: 20px 0;">✅</p>
            <h2 style="text-align: center; color: #28a745;">Password Berhasil Diubah!</h2>
            
            <p>Halo, <strong>[nama_user]</strong>!</p>
            
            <div class="success-box">
                <strong>✅ Password akun Anda telah berhasil diubah.</strong><br>
                <small>Waktu perubahan: [waktu]</small>
            </div>
            
            <p>Mulai sekarang, gunakan password baru Anda untuk login ke sistem.</p>
            
            <div class="warning-box">
                <strong>⚠️ Bukan Anda yang mengubah password?</strong><br>
                <small>Jika Anda tidak melakukan perubahan ini, segera hubungi administrator sekolah untuk mengamankan akun Anda.</small>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem [nama_sekolah]</p>
            <p>[alamat_sekolah] | [telepon_sekolah]</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
