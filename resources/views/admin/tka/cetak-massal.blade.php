<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Kartu Login TKA - Tahun {{ $selectedYear }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            background-color: #f4f6f9;
            color: #111;
            padding: 15px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
            background: #fff;
            padding: 12px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: 1px solid #007bff;
            padding: 8px 20px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* 4 Cards per A4 page */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 15mm auto;
            background: #fff;
            padding: 8mm 6mm;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-content: flex-start;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-item {
            width: 96mm;
            height: 138mm;
            border: 1.5px solid #222;
            padding: 8px 10px;
            margin-bottom: 5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .card-header {
            text-align: center;
            border-bottom: 1px solid #222;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }

        .card-header .title-badge {
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .card-header .main-title {
            font-size: 8.5pt;
            font-weight: bold;
            margin-top: 1px;
        }

        .card-header .sub-title {
            font-size: 8pt;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 4px;
        }

        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .credential-box {
            background: #f8f9fa;
            border: 1px dashed #666;
            padding: 3px 6px;
            margin: 4px 0;
            font-size: 8pt;
        }

        .credential-box table {
            width: 100%;
        }

        .credential-box td {
            padding: 1px 0;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 7pt;
        }

        .schedule-table th, .schedule-table td {
            border: 1px solid #444;
            padding: 2.5px 3px;
            text-align: center;
        }

        .schedule-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .schedule-table td.mapel-cell {
            text-align: left;
            line-height: 1.15;
            font-size: 6.8pt;
        }

        .footer-table {
            width: 100%;
            margin-top: 6px;
            font-size: 7pt;
            text-align: center;
        }

        .footer-table td {
            width: 50%;
            vertical-align: top;
            padding-top: 2px;
        }

        .sign-space {
            height: 28px;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .page-sheet {
                box-shadow: none;
                margin: 0;
                padding: 6mm 5mm;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .card-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div style="margin-bottom: 8px;">
        <strong>Cetak Massal Kartu Login TKA - Tahun {{ $selectedYear }}</strong> (Total: {{ $cards->count() }} Kartu)
    </div>
    <button class="btn" onclick="window.print()">
        🖨️ Mulai Cetak ({{ ceil($cards->count() / 4) }} Lembar A4)
    </button>
</div>

@php
    $chunks = $cards->chunk(4);
@endphp

@foreach($chunks as $chunk)
<div class="page-sheet">
    @foreach($chunk as $tka)
    <div class="card-item">
        <div>
            <div class="card-header">
                <div class="title-badge">KARTU LOGIN</div>
                <div class="main-title">TES KEMAMPUAN AKADEMIK SMA/MA</div>
                <div class="sub-title">TAHUN {{ $tka->tahun }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td style="width: 38%;">Nama Peserta</td>
                    <td style="width: 3%;">:</td>
                    <td style="font-weight: bold;">{{ $tka->nama_peserta }}</td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>:</td>
                    <td>{{ $tka->tempat_tanggal_lahir ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NISN</td>
                    <td>:</td>
                    <td style="font-weight: bold;">{{ $tka->nisn }}</td>
                </tr>
            </table>

            <div class="credential-box">
                <table>
                    <tr>
                        <td style="width: 36%; font-weight: bold;">Username</td>
                        <td style="width: 3%;">:</td>
                        <td style="font-family: monospace; font-size: 8.5pt; font-weight: bold;">{{ $tka->username }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Password</td>
                        <td>:</td>
                        <td style="font-family: monospace; font-size: 8.5pt; font-weight: bold;">{{ $tka->password }}</td>
                    </tr>
                </table>
            </div>

            <table class="info-table" style="margin-top: 3px;">
                <tr>
                    <td style="width: 38%;">ID Proktor / Ruang</td>
                    <td style="width: 3%;">:</td>
                    <td><strong>{{ $tka->id_proktor ?: '-' }} / {{ $tka->ruang ?: '-' }}</strong></td>
                </tr>
                <tr>
                    <td>Gelombang</td>
                    <td>:</td>
                    <td><strong>{{ $tka->gelombang ?: '-' }}</strong></td>
                </tr>
                <tr>
                    <td>Sesi Pelaksanaan</td>
                    <td>:</td>
                    <td><strong>{{ $tka->sesi ?: '-' }}</strong></td>
                </tr>
            </table>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 16%;">Sesi</th>
                        <th style="width: 25%;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($tka->jadwal))
                        @foreach($tka->jadwal as $j)
                        <tr>
                            <td>{{ $j['tanggal'] ?? '-' }}</td>
                            <td class="mapel-cell">
                                @if(isset($j['mapel']) && is_array($j['mapel']))
                                    @foreach($j['mapel'] as $m)
                                        <div>• {{ $m }}</div>
                                    @endforeach
                                @else
                                    {{ $j['mapel'] ?? '-' }}
                                @endif
                            </td>
                            <td>{{ $j['sesi'] ?? $tka->sesi }}</td>
                            <td>{{ $j['waktu'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">Jadwal belum tercatat</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <table class="footer-table">
            <tr>
                <td>
                    Tanda Tangan Peserta,
                    <div class="sign-space"></div>
                    ( <strong>{{ \Illuminate\Support\Str::limit($tka->nama_peserta, 22) }}</strong> )
                </td>
                <td>
                    Proktor / Pengawas,
                    <div class="sign-space"></div>
                    ( ..................................... )
                </td>
            </tr>
        </table>
    </div>
    @endforeach
</div>
@endforeach

</body>
</html>
