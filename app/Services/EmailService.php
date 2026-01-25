<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    protected $settings;
    protected $mailer;

    public function __construct()
    {
        $this->settings = AppSetting::getInstance();
    }

    /**
     * Check if SMTP is configured and enabled
     */
    public function isConfigured(): bool
    {
        return $this->settings->smtp_enabled 
            && !empty($this->settings->smtp_host)
            && !empty($this->settings->smtp_username)
            && !empty($this->settings->smtp_password);
    }

    /**
     * Send email using PHPMailer with database settings
     */
    public function send(string $to, string $subject, string $body, string $type = 'general', bool $isHtml = true): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMTP belum dikonfigurasi atau tidak aktif.'
            ];
        }

        $mailer = new PHPMailer(true);

        try {
            // Server settings
            $mailer->isSMTP();
            $mailer->Host = $this->settings->smtp_host;
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->settings->smtp_username;
            $mailer->Password = $this->settings->smtp_password;
            $mailer->Port = $this->settings->smtp_port ?? 465;
            
            // Set encryption
            if ($this->settings->smtp_encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->settings->smtp_encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mailer->SMTPSecure = false;
                $mailer->SMTPAutoTLS = false;
            }

            // Recipients
            $mailer->setFrom(
                $this->settings->smtp_from_address ?? $this->settings->smtp_username,
                $this->settings->smtp_from_name ?? $this->settings->nama_sekolah ?? 'SIMANSA'
            );
            $mailer->addAddress($to);

            // Content
            $mailer->isHTML($isHtml);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->CharSet = 'UTF-8';

            // Send
            $mailer->send();

            // Log success
            EmailLog::logEmail($to, $subject, $body, $type, 'sent');

            Log::info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject,
                'type' => $type
            ]);

            return [
                'success' => true,
                'message' => 'Email berhasil dikirim ke ' . $to
            ];

        } catch (Exception $e) {
            $errorMessage = $mailer->ErrorInfo;
            
            // Log failure
            EmailLog::logEmail($to, $subject, $body, $type, 'failed', $errorMessage);

            Log::error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $errorMessage
            ];
        }
    }

    /**
     * Send email using template
     */
    public function sendUsingTemplate(string $templateCode, string $to, array $data = [], string $type = null): array
    {
        $template = EmailTemplate::getByCode($templateCode);
        
        if (!$template) {
            return [
                'success' => false,
                'message' => "Template email '{$templateCode}' tidak ditemukan atau tidak aktif."
            ];
        }

        $rendered = $template->render($data);
        
        return $this->send($to, $rendered['subject'], $rendered['body'], $type ?? $templateCode);
    }

    /**
     * Send test email
     */
    public function sendTest(string $to): array
    {
        // Try to use template first
        $template = EmailTemplate::getByCode('test_email');
        
        if ($template) {
            return $this->sendUsingTemplate('test_email', $to, [], 'test');
        }
        
        // Fallback to hardcoded template
        $subject = 'Test Email SIMANSA';
        $body = $this->getTestEmailBody();
        
        return $this->send($to, $subject, $body, 'test');
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(string $to, string $name, string $resetUrl): array
    {
        // Try to use template first
        $template = EmailTemplate::getByCode('password_reset');
        
        if ($template) {
            return $this->sendUsingTemplate('password_reset', $to, [
                '[nama_user]' => $name,
                '[reset_link]' => $resetUrl,
            ], 'password_reset');
        }
        
        // Fallback to hardcoded template
        $subject = 'Reset Password - SIMANSA';
        $body = $this->getPasswordResetBody($name, $resetUrl);
        
        return $this->send($to, $subject, $body, 'password_reset');
    }

    /**
     * Send welcome email to siswa
     */
    public function sendWelcomeSiswa(string $to, array $data): array
    {
        return $this->sendUsingTemplate('welcome_siswa', $to, $data, 'welcome_siswa');
    }

    /**
     * Send general notification
     */
    public function sendNotification(string $to, array $data): array
    {
        return $this->sendUsingTemplate('notification_general', $to, $data, 'notification');
    }

    /**
     * Get test email HTML body
     */
    protected function getTestEmailBody(): string
    {
        $appName = $this->settings->nama_sekolah ?? 'SIMANSA';
        $date = now()->format('d F Y H:i:s');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .footer { background: #333; color: #999; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .success-icon { font-size: 48px; color: #28a745; }
        .info-box { background: #e8f4fc; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✉️ {$appName}</h1>
            <p>Test Email Berhasil!</p>
        </div>
        <div class="content">
            <p style="text-align: center; font-size: 48px;">✅</p>
            <h2 style="text-align: center; color: #28a745;">Konfigurasi SMTP Berhasil!</h2>
            <p>Selamat! Email test ini berhasil dikirim, yang berarti konfigurasi SMTP Anda sudah benar.</p>
            
            <div class="info-box">
                <strong>📋 Detail Pengiriman:</strong><br>
                <small>Waktu: {$date}<br>
                Server: {$this->settings->smtp_host}:{$this->settings->smtp_port}<br>
                Enkripsi: {$this->settings->smtp_encryption}</small>
            </div>
            
            <p>Fitur yang dapat menggunakan email:</p>
            <ul>
                <li>Reset Password</li>
                <li>Notifikasi Sistem</li>
                <li>Pengumuman</li>
            </ul>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {$appName}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get password reset HTML body
     */
    protected function getPasswordResetBody(string $name, string $resetUrl): string
    {
        $appName = $this->settings->nama_sekolah ?? 'SIMANSA';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .footer { background: #333; color: #999; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 {$appName}</h1>
            <p>Reset Password</p>
        </div>
        <div class="content">
            <h2>Halo, {$name}!</h2>
            <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
            <p>Klik tombol di bawah ini untuk membuat password baru:</p>
            
            <p style="text-align: center;">
                <a href="{$resetUrl}" class="btn">Reset Password</a>
            </p>
            
            <div class="warning">
                <strong>⚠️ Perhatian:</strong><br>
                <small>Link ini akan kadaluarsa dalam 60 menit.<br>
                Jika Anda tidak meminta reset password, abaikan email ini.</small>
            </div>
            
            <p><small>Jika tombol tidak berfungsi, copy link berikut ke browser:<br>
            <a href="{$resetUrl}">{$resetUrl}</a></small></p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {$appName}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
