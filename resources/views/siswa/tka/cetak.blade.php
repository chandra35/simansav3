<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Login TKA - {{ $tka->nama_peserta }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            background-color: #f4f6f9;
            color: #111;
            padding: 20px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: 1px solid #007bff;
            padding: 8px 18px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .card-container {
            width: 100mm;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #222;
            padding: 12px 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-header {
            text-align: center;
            border-bottom: 1.5px solid #222;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .card-header .title-badge {
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .card-header .main-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .card-header .sub-title {
            font-size: 9.5pt;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .credential-box {
            background: #f8f9fa;
            border: 1px dashed #666;
            padding: 4px 8px;
            margin: 6px 0;
            font-size: 9pt;
        }

        .credential-box table {
            width: 100%;
        }

        .credential-box td {
            padding: 2px 0;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 8pt;
        }

        .schedule-table th, .schedule-table td {
            border: 1px solid #444;
            padding: 4px 5px;
            text-align: center;
        }

        .schedule-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .schedule-table td.mapel-cell {
            text-align: left;
            line-height: 1.25;
        }

        .footer-table {
            width: 100%;
            margin-top: 10px;
            font-size: 8pt;
            text-align: center;
        }

        .footer-table td {
            width: 50%;
            vertical-align: top;
            padding-top: 4px;
        }

        .sign-space {
            height: 38px;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .card-container {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">
        🖨️ Cetak Kartu Peserta
    </button>
</div>

<div class="card-container">
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
                <td style="font-family: monospace; font-size: 10pt; font-weight: bold;">{{ $tka->username }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Password</td>
                <td>:</td>
                <td style="font-family: monospace; font-size: 10pt; font-weight: bold;">{{ $tka->password }}</td>
            </tr>
        </table>
    </div>

    <table class="info-table" style="margin-top: 6px;">
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
                <th style="width: 18%;">Sesi</th>
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

    <table class="footer-table">
        <tr>
            <td>
                Tanda Tangan Peserta,
                <div class="sign-space"></div>
                ( <strong>{{ $tka->nama_peserta }}</strong> )
            </td>
            <td>
                Proktor / Pengawas,
                <div class="sign-space"></div>
                ( ..................................... )
            </td>
        </tr>
    </table>
</div>

</body>
</html>
