<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Agenda Surat Keluar Tahun {{ $tahun }} - MAN 1 Metro</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm 12mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .kop {
            position: relative;
            text-align: center;
            border-bottom: 3px double #111;
            padding: 0 0 6px 0;
            margin-bottom: 15px;
            min-height: 80px;
        }
        .kop-logo {
            position: absolute;
            top: 0;
            height: 75px;
            width: auto;
        }
        .kop-logo.left { left: 10px; }
        .kop-logo.right { right: 10px; }
        .kop-title {
            margin: 0 90px;
            line-height: 1.15;
        }
        .kop-title .line-1 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .kop-title .line-2 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .kop-title .line-3 { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-top: 1px; }
        .kop-meta {
            font-size: 8.5px;
            line-height: 1.2;
            margin-top: 4px;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title p {
            font-size: 10.5px;
            margin: 3px 0 0;
            color: #444;
        }
        table.agenda {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.agenda th, table.agenda td {
            border: 1px solid #333;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 10px;
        }
        table.agenda th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge-batal {
            display: inline-block;
            background-color: #dc3545;
            color: #fff;
            padding: 1px 4px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
        }
        .badge-aktif {
            display: inline-block;
            background-color: #28a745;
            color: #fff;
            padding: 1px 4px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
        }
        .signatures {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border: 0;
        }
        .signature-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .no-print {
            display: block;
            background: #e2e8f0;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: right;
        }
        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    @php($kopLines = $setting->kop_header_lines)
    <div class="kop">
        @if(!empty($logoKemenagDataUri))
            <img class="kop-logo left" src="{{ $logoKemenagDataUri }}" alt="Logo Kementerian Agama">
        @endif
        @if(!empty($logoSekolahDataUri))
            <img class="kop-logo right" src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah">
        @endif
        <div class="kop-title">
            <div class="line-1">{{ $kopLines[0] ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA' }}</div>
            <div class="line-2">{{ $kopLines[1] ?? 'KANTOR KEMENTERIAN AGAMA KOTA METRO' }}</div>
            <div class="line-3">{{ $kopLines[2] ?? 'MADRASAH ALIYAH NEGERI 1 METRO' }}</div>
        </div>
        <div class="kop-meta">
            {{ $setting->kop_identitas }}<br>
            {{ $setting->kop_alamat }}<br>
            <em>Website : <u>{{ $setting->website ?: '-' }}</u>&nbsp;&nbsp; E-mail: {{ $setting->email ?: '-' }}</em>
        </div>
    </div>

    <div class="doc-title">
        <h2>BUKU AGENDA SURAT KELUAR</h2>
        <p>Tahun Kalender {{ $tahun }} &bull; Format Tata Naskah Dinas Kementerian Agama</p>
    </div>

    <table class="agenda">
        <thead>
            <tr>
                <th width="35">No. Urut</th>
                <th width="160">Nomor Surat</th>
                <th width="75">Tanggal</th>
                <th width="65">Kode</th>
                <th>Perihal / Isi Ringkas Surat</th>
                <th width="160">Tujuan / Kepada Yth.</th>
                <th width="130">Penandatangan</th>
                <th width="85">Pengolah</th>
                <th width="65">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $row)
                <tr>
                    <td class="text-center font-bold">{{ sprintf('%03d', $row->nomor_urut) }}</td>
                    <td class="font-bold">
                        {{ $row->nomor_surat }}
                    </td>
                    <td class="text-center">{{ $row->tanggal_surat ? $row->tanggal_surat->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $row->kode_klasifikasi }}</td>
                    <td>
                        <strong>{{ $row->perihal }}</strong>
                        @if($row->status === 'dibatalkan' && $row->alasan_dibatalkan)
                            <br><small style="color:#dc3545;"><em>Ket: Dibatalkan - {{ $row->alasan_dibatalkan }}</em></small>
                        @endif
                    </td>
                    <td>{{ $row->tujuan ?: '-' }}</td>
                    <td>
                        {{ $row->nama_penandatangan ?: ($row->penandatangan?->nama_lengkap ?? '-') }}<br>
                        <small style="color:#555;">{{ $row->jabatan_penandatangan ?: 'Kepala Madrasah' }}</small>
                    </td>
                    <td class="text-center">{{ $row->modul_asal_label }}</td>
                    <td class="text-center">
                        @if($row->status === 'dibatalkan')
                            <span class="badge-batal">BATAL</span>
                        @else
                            <span class="badge-aktif">AKTIF</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">
                        <em>Belum ada nomor surat yang tercatat untuk filter tahun {{ $tahun }}.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center" style="font-size: 11px;">
                    Metro, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Kepala Urusan Tata Usaha,
                    <div style="height: 60px;"></div>
                    <strong><u>H. ISMAIL, S.Ag.</u></strong><br>
                    NIP. 197405021998031002
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
