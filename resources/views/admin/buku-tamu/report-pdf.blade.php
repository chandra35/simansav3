<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 7.2pt; }
        .kop { width: 100%; border-bottom: 3px solid #0f67b1; padding-bottom: 7px; margin-bottom: 9px; }
        .kop td { vertical-align: middle; }
        .logo { width: 58px; height: 58px; object-fit: contain; }
        .kop-title { font-size: 13pt; font-weight: bold; color: #0f2b4e; text-transform: uppercase; }
        .kop-subtitle { color: #475569; font-size: 8pt; margin-top: 2px; }
        h1 { color: #0f2b4e; font-size: 13pt; margin: 0 0 3px; }
        .period { color: #64748b; font-size: 8pt; margin-bottom: 8px; }
        .summary { width: 100%; margin-bottom: 8px; }
        .summary td { width: 25%; padding: 7px 9px; border: 1px solid #dbeafe; background: #f3f8ff; }
        .summary .label { color: #64748b; font-size: 7pt; }
        .summary .value { color: #0f67b1; font-size: 13pt; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data th { padding: 5px 4px; background: #0f67b1; color: white; font-size: 6.6pt; text-align: left; }
        .data td { padding: 4px; border: 1px solid #cbd5e1; vertical-align: top; word-wrap: break-word; }
        .data tr:nth-child(even) td { background: #f8fafc; }
        .muted { color: #64748b; }
        .footer { margin-top: 7px; color: #64748b; font-size: 6.5pt; }
    </style>
</head>
<body>
    <table class="kop"><tr><td style="width:70px"><img class="logo" src="{{ $logoDataUri }}" alt="Logo"></td><td><div class="kop-title">{{ $setting->nama_sekolah ?? 'SIMANSA' }}</div><div class="kop-subtitle">Laporan Buku Tamu PTSP · {{ $setting->alamat_lengkap ?? '' }}</div></td><td style="width:160px;text-align:right" class="muted">Dicetak<br>{{ $printedAt }}</td></tr></table>
    <h1>Laporan Kunjungan Buku Tamu</h1>
    <div class="period">Periode: <strong>{{ $periodLabel }}</strong> · Semua waktu mengikuti Asia/Jakarta (WIB)</div>
    <table class="summary"><tr><td><div class="label">Total Kunjungan</div><div class="value">{{ $summary['total'] }}</div></td><td><div class="label">Instansi</div><div class="value">{{ $summary['instansi'] }}</div></td><td><div class="label">Lembaga</div><div class="value">{{ $summary['lembaga'] }}</div></td><td><div class="label">Individu</div><div class="value">{{ $summary['individu'] }}</div></td></tr></table>
    <table class="data"><thead><tr><th style="width:7%">Tanggal/Waktu</th><th style="width:5%">Jenis</th><th style="width:10%">Nama</th><th style="width:11%">Instansi/Lembaga</th><th style="width:14%">Alamat</th><th style="width:7%">No. HP</th><th style="width:13%">Keperluan</th><th style="width:7%">IP</th><th style="width:6%">Device</th><th style="width:7%">Platform</th><th style="width:7%">Browser</th><th style="width:6%">QR Token</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }} WIB</td><td>{{ ucfirst($item->jenis_tamu ?? 'individu') }}</td><td>{{ $item->nama }}</td><td>{{ $item->alamat_instansi }}</td><td>{{ $item->alamat_lengkap ?: '-' }}</td><td>{{ $item->nomor_hp }}</td><td>{{ $item->keperluan }}</td><td>{{ $item->ip_address ?: '-' }}</td><td>{{ $item->device_type }}</td><td>{{ $item->platform }}</td><td>{{ $item->browser }}</td><td>{{ $item->qrToken?->token ?: '-' }}</td></tr>@empty<tr><td colspan="12" style="text-align:center">Belum ada data kunjungan.</td></tr>@endforelse</tbody></table>
    <div class="footer">Laporan dibuat dari SIMANSA. User-Agent lengkap tersedia pada export Excel dan detail pengunjung.</div>
</body>
</html>
