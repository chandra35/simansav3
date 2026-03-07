<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi - {{ $setting->nama_sekolah ?? 'SIMANSA' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #44989e 0%, #76589e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .verify-card {
            max-width: 450px;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .verify-header {
            background: #1a3a4a;
            color: #fff;
            text-align: center;
            padding: 24px 20px 16px;
        }
        .verify-header h5 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .verify-header small {
            opacity: 0.7;
            font-size: 11px;
        }
        .verify-body {
            background: #fff;
            padding: 30px 24px;
        }
        .status-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .status-icon .icon-valid {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #d4edda;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #28a745;
        }
        .status-icon .icon-invalid {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #f8d7da;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #dc3545;
        }
        .status-text {
            text-align: center;
            margin-bottom: 20px;
        }
        .status-text h4 {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .status-text p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        .data-table {
            width: 100%;
        }
        .data-table tr td {
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-table .label {
            color: #6c757d;
            width: 100px;
        }
        .data-table .sep {
            width: 15px;
            text-align: center;
            color: #ccc;
        }
        .data-table .value {
            font-weight: 600;
            color: #333;
        }
        .verify-footer {
            background: #f8f9fa;
            text-align: center;
            padding: 14px 20px;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #eee;
        }
        .badge-tipe {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-gtk { background: #e3f2fd; color: #1565c0; }
        .badge-siswa { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <h5>{{ $setting->nama_sekolah ?? 'SIMANSA' }}</h5>
            <small>Sistem Verifikasi Identitas</small>
        </div>
        <div class="verify-body">
            @if($valid)
                <div class="status-icon">
                    <div class="icon-valid">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="status-text">
                    <h4 class="text-success">Terverifikasi</h4>
                    <p>Data {{ $tipe }} terdaftar dan valid</p>
                    <span class="badge-tipe {{ $tipe === 'GTK' ? 'badge-gtk' : 'badge-siswa' }}">{{ $tipe }}</span>
                </div>
                <table class="data-table">
                    @foreach($data as $label => $value)
                    <tr>
                        <td class="label">{{ ucfirst($label) }}</td>
                        <td class="sep">:</td>
                        <td class="value">{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            @else
                <div class="status-icon">
                    <div class="icon-invalid">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                <div class="status-text">
                    <h4 class="text-danger">Tidak Valid</h4>
                    <p>Data {{ $tipe }} tidak ditemukan dalam sistem</p>
                </div>
                <div class="text-center text-muted" style="font-size: 13px;">
                    <i class="fas fa-info-circle"></i>
                    Pastikan QR Code yang di-scan berasal dari kartu identitas resmi {{ $setting->nama_sekolah ?? 'SIMANSA' }}.
                </div>
            @endif
        </div>
        <div class="verify-footer">
            <i class="fas fa-shield-alt"></i>
            {{ $setting->nama_sekolah ?? 'SIMANSA' }} &mdash; Kementerian Agama RI
            <br>Verifikasi pada {{ now()->format('d M Y, H:i') }} WIB
        </div>
    </div>
</body>
</html>
