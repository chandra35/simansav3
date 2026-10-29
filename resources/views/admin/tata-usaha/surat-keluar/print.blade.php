<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $suratKeluar->perihal }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            margin: 0 auto;
            padding: 20px;
        }
        .kop-surat {
            margin-bottom: 20px;
        }
        .content {
            min-height: 400px;
            margin-bottom: 50px;
        }
        .ttd-section {
            margin-top: 60px;
            text-align: right;
        }
        .ttd-table {
            width: 100%;
            margin-top: 30px;
        }
        .ttd-table td {
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Render Kop Surat --}}
        <div class="kop-surat">
            {!! renderKopSurat(true) !!}
        </div>

        {{-- Metadata & Content --}}
        <div class="content">
            <table style="width: 100%; font-size: 11pt; margin-bottom: 15px;">
                <tr>
                    <td style="width: 150px;">Nomor</td>
                    <td>: {{ $suratKeluar->nomor_surat }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $suratKeluar->tanggal_surat?->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>: {{ $suratKeluar->perihal }}</td>
                </tr>
                @if ($suratKeluar->tujuan)
                <tr>
                    <td>Tujuan</td>
                    <td>: {{ $suratKeluar->tujuan }}</td>
                </tr>
                @endif
            </table>

            <hr style="margin: 20px 0; border: none; border-top: 1px solid #999;">

            {{-- Main Content --}}
            <div style="font-size: 11pt; line-height: 1.8; text-align: justify; margin-bottom: 30px;">
                {!! nl2br(htmlspecialchars($suratKeluar->konten_generate)) !!}
            </div>
        </div>

        {{-- TTD Section --}}
        <div class="ttd-section">
            <table class="ttd-table" style="margin-left: auto;">
                <tr>
                    <td style="width: 250px; text-align: center;">
                        <p style="margin-bottom: 60px;">Mengetahui,</p>
                        @if ($suratKeluar->penandatangan)
                            <p style="margin-bottom: 2px; font-weight: bold;">
                                <u>{{ $suratKeluar->penandatangan->nama_lengkap }}</u>
                            </p>
                            <p style="margin: 0;">NIP. {{ $suratKeluar->penandatangan->nip ?? '_______________' }}</p>
                        @else
                            <p style="margin-bottom: 2px; font-weight: bold;"><u>( _________________________ )</u></p>
                            <p style="margin: 0;">NIP. _____________________</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Metadata Footer --}}
        <div style="margin-top: 40px; font-size: 9pt; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
            <p style="margin: 2px 0;">
                Dicetak oleh: {{ auth()->user()->name ?? 'Sistem' }} |
                Tanggal cetak: {{ now()->translatedFormat('d F Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
