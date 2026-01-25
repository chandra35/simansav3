<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">{{ $setting->nama_sekolah ?? 'SIMANSA' }}</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0;">Sistem Informasi Manajemen Sekolah</p>
    </div>
    
    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;">
        <h2 style="color: #667eea; margin-top: 0;">Reset Password</h2>
        
        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        
        <p>Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah ini untuk melanjutkan:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetLink }}" 
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                      color: white; 
                      padding: 15px 40px; 
                      text-decoration: none; 
                      border-radius: 5px; 
                      display: inline-block;
                      font-weight: bold;">
                Reset Password
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            Atau salin link berikut ke browser Anda:<br>
            <a href="{{ $resetLink }}" style="color: #667eea; word-break: break-all;">{{ $resetLink }}</a>
        </p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        
        <p style="color: #999; font-size: 12px;">
            <strong>Catatan:</strong>
            <ul style="color: #999; font-size: 12px;">
                <li>Link ini akan expired dalam 60 menit.</li>
                <li>Jika Anda tidak meminta reset password, abaikan email ini.</li>
                <li>Jangan bagikan link ini kepada siapapun.</li>
            </ul>
        </p>
        
        <p style="color: #999; font-size: 12px; margin-bottom: 0;">
            Salam,<br>
            <strong>{{ $setting->nama_sekolah ?? 'SIMANSA' }}</strong>
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        &copy; {{ date('Y') }} {{ $setting->nama_sekolah ?? 'SIMANSA' }}. All rights reserved.
    </div>
</body>
</html>
