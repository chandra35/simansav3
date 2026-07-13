<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card Siswa - {{ $setting->nama_sekolah ?? 'SIMANSA' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 4.5mm 5mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 7pt;
            line-height: 1.3;
            color: #333333;
            background: #ffffff;
        }
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: avoid; }

        /* === GRID: front+back side by side === */
        .card-grid {
            border-collapse: separate;
            border-spacing: 1.35mm;
            margin: 0 auto;
        }
        .card-grid td {
            vertical-align: top;
            padding: 0;
        }
        .pair-gap {
            width: 8mm;
        }

        /* === CARD SHELL: standard vertical ID card 54mm x 86mm === */
        .id-card {
            width: 54mm;
            height: 86mm;
            overflow: hidden;
            background-size: 54mm 86mm;
            background-repeat: no-repeat;
            background-position: center;
            border-radius: 3mm;
        }

        /* ====== FRONT (ASN-style: white top, gradient bottom) ====== */
        .f-header {
            text-align: center;
            padding: 2.3mm 3mm 1mm 3mm;
        }
        .f-header-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .f-header-tbl td {
            vertical-align: middle;
            padding: 0;
        }
        .f-logo {
            width: 9mm;
            text-align: center;
        }
        .f-logo img {
            height: 8mm;
            width: auto;
        }
        .f-htext {
            text-align: center;
            padding: 0 1mm;
        }
        .f-ministry {
            font-size: 5.3pt;
            color: #6f7a86;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        .f-school {
            font-size: 7.4pt;
            font-weight: bold;
            color: #143545;
            text-transform: uppercase;
            letter-spacing: 0;
            line-height: 1.15;
        }
        .f-addr {
            font-size: 4.4pt;
            color: #8a97a4;
            line-height: 1.15;
        }
        .f-separator {
            border: none;
            margin: 0;
            height: 0;
        }

        /* Large photo area — borderless, fills white zone */
        .f-foto-area {
            text-align: center;
            padding: 2.2mm 0 0 0;
        }
        .f-foto-frame {
            width: 34mm;
            height: 34mm;
            overflow: hidden;
            text-align: center;
            margin: 0 auto;
            background: #f4f8fb;
            border: 0.55mm solid #ffffff;
            outline: 0.25mm solid #d8e7ee;
            border-radius: 2mm;
        }
        .f-foto-frame img {
            width: 34mm;
            height: 34mm;
        }
        .f-foto-placeholder {
            padding-top: 14.5mm;
            font-size: 5.5pt;
            color: #8a97a4;
            height: 34mm;
        }

        /* Bottom section on gradient */
        .f-bottom {
            padding: 4.2mm 3.8mm 0 3.8mm;
        }
        .f-bottom-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .f-bottom-tbl td {
            vertical-align: middle;
            padding: 0;
        }
        .f-info {
            text-align: left;
        }
        .f-name {
            font-size: 7.5pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0;
            padding-bottom: 0.9mm;
            line-height: 1.18;
            min-height: 8.4mm;
        }
        .f-id-number {
            font-size: 5.1pt;
            color: rgba(255,255,255,0.86);
            letter-spacing: 0;
            line-height: 1.35;
        }
        .f-qr-cell {
            width: 14.5mm;
            text-align: right;
        }
        .f-qr-box {
            width: 13mm;
            height: 13mm;
            padding: 0.6mm;
            background: #ffffff;
            border-radius: 1.5mm;
        }
        .f-qr-box img {
            width: 11.8mm;
            height: 11.8mm;
        }

        /* ====== BACK ====== */
        .b-header {
            text-align: center;
            padding: 3mm 3mm 1.4mm 3mm;
        }
        .b-header-title {
            font-size: 5.3pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        .b-header-sub {
            font-size: 3.6pt;
            color: rgba(255,255,255,0.7);
            padding-top: 0.3mm;
        }
        .b-id-number {
            text-align: right;
            padding: 0 3mm;
            font-size: 4.3pt;
            font-weight: bold;
            color: rgba(255,255,255,0.8);
            letter-spacing: 0;
        }
        .b-body {
            padding: 1.1mm 3mm 0.8mm 3mm;
        }
        .b-section-title {
            font-size: 4.8pt;
            font-weight: bold;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            letter-spacing: 0;
            border-bottom: 0.4px solid rgba(255,255,255,0.3);
            padding-bottom: 0.3mm;
            margin-bottom: 0.5mm;
        }
        .b-detail-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1mm;
        }
        .b-detail-tbl td {
            font-size: 4.7pt;
            padding: 0.25mm 0;
            vertical-align: top;
            color: rgba(255,255,255,0.9);
        }
        .b-detail-tbl .lbl {
            width: 12mm;
            color: rgba(255,255,255,0.6);
        }
        .b-detail-tbl .sep {
            width: 2mm;
            text-align: center;
            color: rgba(255,255,255,0.4);
        }
        .b-rules-title {
            font-size: 4.8pt;
            font-weight: bold;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            letter-spacing: 0;
            border-bottom: 0.4px solid rgba(255,255,255,0.3);
            padding-bottom: 0.3mm;
            margin-bottom: 0.5mm;
            margin-top: 0.7mm;
        }
        .b-rules ol {
            margin: 0;
            padding-left: 3.2mm;
            font-size: 4.4pt;
            color: rgba(255,255,255,0.8);
            line-height: 1.35;
        }
        .b-sig-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.4mm;
        }
        .b-sig-tbl td {
            text-align: center;
            vertical-align: bottom;
            font-size: 4.4pt;
            width: 50%;
            color: rgba(255,255,255,0.8);
            padding: 0 1.4mm;
        }
        .b-sig-line {
            border-bottom: 0.4px solid rgba(255,255,255,0.5);
            margin-top: 4.3mm;
            margin-bottom: 0.3mm;
        }
        .b-sig-name {
            font-weight: bold;
            font-size: 4.4pt;
            color: #ffffff;
        }
        .b-footer {
            text-align: center;
            padding: 1mm 2mm 0.8mm 2mm;
        }
        .b-footer-text {
            font-size: 4.4pt;
            font-weight: bold;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0;
        }
        .b-footer-sub {
            font-size: 2.8pt;
            color: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
@php
    $allSiswa = collect();
    foreach ($kelasList as $kelas) {
        foreach ($kelas->siswas as $siswa) {
            $siswa->kelas_nama = $kelas->nama_lengkap;
            $allSiswa->push($siswa);
        }
    }
    // 2 pairs per row × 2 rows = 4 persons per A4 landscape page
    $chunks = $allSiswa->chunk(4);
@endphp

@foreach($chunks as $chunkIndex => $chunk)
<div class="{{ !$loop->last ? 'page-break' : '' }}">
    <table class="card-grid">
    @foreach($chunk->chunk(2) as $row)
        <tr>
        @foreach($row as $rowIdx => $siswa)
            @if($rowIdx > 0)
            <td class="pair-gap"></td>
            @endif
            {{-- FRONT (ASN-style: white + gradient bottom) --}}
            <td>
                <div class="id-card" style="background-image: url('{{ $bgFrontBase64 }}');">
                    <div class="f-header">
                        <table class="f-header-tbl">
                            <tr>
                                @if($logoKemenagBase64)
                                <td class="f-logo">
                                    <img src="{{ $logoKemenagBase64 }}" alt="">
                                </td>
                                @endif
                                <td class="f-htext">
                                    <div class="f-ministry">Kementerian Agama RI</div>
                                    <div class="f-school">{{ $setting->nama_sekolah ?? 'Nama Sekolah' }}</div>
                                    <div class="f-addr">{{ Illuminate\Support\Str::limit($setting->alamat ?? '', 55) }}</div>
                                </td>
                                @if($logoSekolahBase64)
                                <td class="f-logo">
                                    <img src="{{ $logoSekolahBase64 }}" alt="">
                                </td>
                                @endif
                            </tr>
                        </table>
                    </div>
                    <hr class="f-separator">

                    {{-- Large photo --}}
                    <div class="f-foto-area">
                        <div class="f-foto-frame">
                            @if($siswa->foto_base64)
                                <img src="{{ $siswa->foto_base64 }}" alt="">
                            @else
                                <div class="f-foto-placeholder">Foto 3&times;4</div>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom: Name + NISN + QR --}}
                    <div class="f-bottom">
                        <table class="f-bottom-tbl">
                            <tr>
                                <td class="f-info">
                                    <div class="f-name">{{ strtoupper($siswa->nama_lengkap) }}</div>
                                    <div class="f-id-number">NISN {{ $siswa->nisn ?? '-' }}</div>
                                    <div class="f-id-number">KELAS {{ $siswa->kelas_nama ?? '-' }}</div>
                                </td>
                                @if($siswa->qr_base64)
                                <td class="f-qr-cell">
                                    <div class="f-qr-box">
                                        <img src="{{ $siswa->qr_base64 }}" alt="">
                                    </div>
                                </td>
                                @endif
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            {{-- BACK (full gradient) --}}
            <td>
                <div class="id-card" style="background-image: url('{{ $bgBackBase64 }}');">
                    <div class="b-header">
                        <div class="b-header-title">{{ $setting->nama_sekolah ?? 'Nama Sekolah' }}</div>
                        <div class="b-header-sub">Kementerian Agama Republik Indonesia</div>
                    </div>
                    <div class="b-id-number">NISN: {{ $siswa->nisn ?? '' }}</div>
                    <div class="b-body">
                        <div class="b-section-title">Data Pribadi</div>
                        <table class="b-detail-tbl">
                            <tr>
                                <td class="lbl">NIK</td>
                                <td class="sep">:</td>
                                <td>{{ $siswa->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">NISN</td>
                                <td class="sep">:</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Agama</td>
                                <td class="sep">:</td>
                                <td>{{ $siswa->agama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Alamat</td>
                                <td class="sep">:</td>
                                <td>{{ \Illuminate\Support\Str::limit($siswa->alamat_siswa ?? '-', 35) }}</td>
                            </tr>
                        </table>
                        <div class="b-rules-title">Ketentuan</div>
                        <div class="b-rules">
                            <ol>
                                <li>Kartu ini wajib dibawa saat di lingkungan sekolah</li>
                                <li>Tidak dapat dipindahtangankan</li>
                                <li>Jika hilang segera melapor ke sekolah</li>
                                <li>Berlaku selama menjadi siswa aktif</li>
                            </ol>
                        </div>
                        <table class="b-sig-tbl">
                            <tr>
                                <td>
                                    Pemegang Kartu
                                    <div class="b-sig-line"></div>
                                </td>
                                <td>
                                    Kepala Madrasah
                                    <div class="b-sig-line"></div>
                                    <div class="b-sig-name">{{ $setting->nama_kepala_sekolah ?? '' }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="b-footer">
                        @if($setting->website)
                            <div class="b-footer-text">{{ $setting->website }}</div>
                        @endif
                        <div class="b-footer-sub">SIMANSA v3 &bull; {{ $setting->nama_sekolah ?? '' }}</div>
                    </div>
                </div>
            </td>
        @endforeach
        {{-- Fill empty cells if odd number --}}
        @if($row->count() < 2)
            <td class="pair-gap"></td>
            <td></td>
            <td></td>
        @endif
        </tr>
    @endforeach
    </table>
</div>
@endforeach
</body>
</html>
