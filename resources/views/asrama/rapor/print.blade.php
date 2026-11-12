<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>كشف الدرجات - {{ $is_bulk ?? false ? ($kelas->nama_kelas . ' - ' . ($kelas->is_tahfidz ? 'Tahfizh' : 'Reguler')) : ($report['identity']['nama'] ?? 'Rapor Asrama') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
<style>
@page {
    size: A4 portrait;
    margin: 10mm 12mm 10mm 12mm;
}
* {
    box-sizing: border-box;
}
body {
    margin: 0;
    padding: 0;
    color: #000;
    background: #eef2f5;
    font-family: 'Amiri', 'Noto Naskh Arabic', 'Traditional Arabic', 'Times New Roman', serif;
    font-size: 13.5px;
    direction: rtl;
    text-align: right;
}
.toolbar {
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 12px;
    text-align: center;
    background: #1e3a5f;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    direction: ltr;
}
.toolbar button {
    padding: 9px 24px;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: bold;
    font-size: 14px;
    background: #10b981;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    margin: 0 5px;
}
.toolbar button.btn-back {
    background: #64748b;
}
.sheet {
    position: relative;
    width: 210mm;
    min-height: 297mm;
    max-height: 297mm;
    margin: 12px auto;
    padding: 12mm 14mm 12mm 14mm;
    background: #fff;
    box-shadow: 0 4px 18px rgba(0,0,0,0.12);
    box-sizing: border-box;
    page-break-after: always;
}
.sheet:last-child {
    page-break-after: avoid;
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 12px;
}
.header-title {
    font-size: 26px;
    font-weight: bold;
    margin: 0;
    line-height: 1.3;
}
.header-subtitle {
    font-size: 15px;
    margin: 4px 0 2px 0;
    line-height: 1.4;
}
.header-class {
    font-size: 16px;
    font-weight: bold;
    margin: 2px 0 0 0;
}

/* Student Bio Bar */
.bio-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    margin-bottom: 8px;
    font-size: 15px;
    font-weight: bold;
    padding: 0 4px;
}
.bio-bar .nama-santri {
    text-align: right;
}
.bio-bar .nomor-santri {
    text-align: left;
    direction: ltr;
}

/* 16-Row Rapor Table */
.rapor-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 14px;
}
.rapor-table th,
.rapor-table td {
    border: 1.5px solid #000;
    padding: 2.2px 4px;
    vertical-align: middle;
    line-height: 1.25;
}
.rapor-table th {
    text-align: center;
    font-weight: bold;
    font-size: 13.5px;
    background-color: #f8fafc;
}

/* Column Widths (matching dual 16-row reference) */
.col-no { width: 5.5%; text-align: center; font-size: 13px; }
.col-mapel { width: 22%; text-align: right; padding-right: 6px; font-size: 13.5px; }
.col-score { width: 9%; text-align: center; font-weight: bold; font-size: 13.5px; }
.col-words { width: 13.5%; text-align: right; padding-right: 5px; font-size: 13px; }

/* Special Rows */
.row-summary {
    font-weight: bold;
}
.row-summary .col-mapel {
    text-align: right;
    font-size: 14px;
}
.row-summary .col-score {
    font-size: 14px;
}
.row-blank td {
    height: 18px;
    border-color: #000;
}
.row-character .col-mapel {
    font-size: 13.5px;
}
.row-character .col-words {
    text-align: center;
}

/* Signatures */
.signatures {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-top: 18px;
    padding: 0 16px;
    font-size: 14.5px;
}
.sig-box {
    text-align: center;
    width: 44%;
}
.sig-space {
    height: 52px;
}
.sig-name {
    font-weight: bold;
    border-bottom: 1px solid #000;
    display: inline-block;
    padding-bottom: 2px;
    min-width: 140px;
}
.sig-nip {
    font-size: 12.5px;
    margin-top: 3px;
    direction: ltr;
}

@media print {
    body {
        background: #fff;
    }
    .toolbar {
        display: none !important;
    }
    .sheet {
        margin: 0;
        padding: 6mm 10mm 6mm 10mm;
        box-shadow: none;
        width: 100%;
        max-height: none;
        page-break-after: always;
    }
    .sheet:last-child {
        page-break-after: avoid;
    }
}
</style>
</head>
<body>

<div class="toolbar">
    <button onclick="window.print()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    <button class="btn-back" onclick="window.history.back()">Kembali</button>
</div>

@php
    $studentItems = isset($is_bulk) && $is_bulk ? $items : [['rapor' => $rapor, 'report' => $report]];
@endphp

@foreach($studentItems as $item)
@php
    $rep = $item['report'];
    $rap = $item['rapor'];
    $right = $rep['panels']['right'];
    $left = $rep['panels']['left'];
    $semesterText = ($rep['identity']['semester'] === 'Genap') ? 'الثاني' : 'الأول';
    $thnPelajaran = $rep['identity']['tahun_pelajaran'];

    // Format Hijri & Gregorian Dates
    $tglRapor = $rep['rapor_info']['tanggal_rapor'] ?? null;
    $tglHijri = $rep['rapor_info']['tanggal_hijriah'] ?? null;

    $dateGregorianStr = $tglRapor ? $tglRapor->translatedFormat('d F Y') . ' م' : now()->translatedFormat('d F Y') . ' م';
    $dateHijriStr = $tglHijri ?: '';
@endphp

<main class="sheet">
    <!-- Header -->
    <div class="header">
        <h1 class="header-title">كشف الدرجات</h1>
        <div class="header-subtitle">
            لامتحان نهاية الفصل {{ $semesterText }} للعام الدراسي {{ $thnPelajaran }}
        </div>
        <div class="header-class">
            لطلبة {{ $rep['identity']['kelas_arab'] }}{{ $rep['is_tahfidz'] ? ' تحفيظ' : '' }}
        </div>
    </div>

    <!-- Student Bio -->
    <div class="bio-bar">
        <div class="nama-santri">
            الاسم : <span>{{ $rep['identity']['nama'] }}</span>
        </div>
        <div class="nomor-santri">
            <span style="direction: rtl; display: inline-block;">رقم القيد :</span>
            <strong>{{ $rep['identity']['nomor_induk_asrama'] ?: ($rep['identity']['nisn'] ?: '-') }}</strong>
        </div>
    </div>

    <!-- 16-Row Dual Panel Table -->
    <table class="rapor-table">
        <thead>
            <tr>
                <!-- Right Panel Header (RTL First Group) -->
                <th class="col-no">الرقم</th>
                <th class="col-mapel">المواد</th>
                <th class="col-score">الدرجة رقما</th>
                <th class="col-words">الدرجة تحريرا</th>

                <!-- Left Panel Header (RTL Second Group) -->
                <th class="col-no">الرقم</th>
                <th class="col-mapel">المواد</th>
                <th class="col-score">الدرجة رقما</th>
                <th class="col-words">الدرجة تحريرا</th>
            </tr>
        </thead>
        <tbody>
            @for($r = 0; $r < 16; $r++)
                @php
                    $rRow = $right[$r] ?? null;
                    $lRow = $left[$r] ?? null;
                @endphp
                <tr class="{{ isset($lRow['is_blank']) ? 'row-blank' : (isset($lRow['is_summary']) ? 'row-summary' : (isset($lRow['is_character']) ? 'row-character' : '')) }}">
                    <!-- Right Panel Cell ($r + 1) -->
                    <td class="col-no">{{ $r + 1 }}</td>
                    <td class="col-mapel">{{ $rRow['nama_arab'] ?? '' }}</td>
                    <td class="col-score">{{ $rRow['nilai_formatted'] ?? '' }}</td>
                    <td class="col-words">{{ $rRow['nilai_arab'] ?? '' }}</td>

                    <!-- Left Panel Cell ($r + 1) -->
                    @if(isset($lRow['is_blank']) && $lRow['is_blank'])
                        <td class="col-no"></td>
                        <td class="col-mapel"></td>
                        <td class="col-score"></td>
                        <td class="col-words"></td>
                    @elseif(isset($lRow['is_summary']) && $lRow['is_summary'])
                        <td class="col-no" style="font-weight:bold;">{{ $lRow['no'] }}</td>
                        <td class="col-mapel" style="font-weight:bold;">{{ $lRow['nama_arab'] }}</td>
                        <td class="col-score" style="font-weight:bold;">{{ $lRow['nilai_formatted'] }}</td>
                        <td class="col-words" style="font-weight:bold;">{{ $lRow['nilai_arab'] }}</td>
                    @elseif(isset($lRow['is_character']) && $lRow['is_character'])
                        <td class="col-no">{{ $lRow['no'] }}</td>
                        <td class="col-mapel">{{ $lRow['nama_arab'] }}</td>
                        <td class="col-score">{{ $lRow['nilai_formatted'] }}</td>
                        <td class="col-words" style="text-align:center;">{{ $lRow['predikat_arab'] }}</td>
                    @else
                        <td class="col-no">{{ $r + 1 }}</td>
                        <td class="col-mapel">{{ $lRow['nama_arab'] ?? '' }}</td>
                        <td class="col-score">{{ $lRow['nilai_formatted'] ?? '' }}</td>
                        <td class="col-words">{{ $lRow['nilai_arab'] ?? '' }}</td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="signatures">
        <!-- Murabbi / Pengasuh (Right side in RTL) -->
        <div class="sig-box">
            <div>مربي الفصل</div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $rep['institution']['pengasuh'] ?: '................................' }}</div>
            @if(!empty($rep['institution']['nip_pengasuh']))
                <div class="sig-nip">NIP. {{ $rep['institution']['nip_pengasuh'] }}</div>
            @endif
        </div>

        <!-- Mudir / Kepala Asrama (Left side in RTL) -->
        <div class="sig-box">
            <div>مترو، {{ $dateGregorianStr }}</div>
            @if($dateHijriStr)
                <div style="font-size:13px; margin-top:2px;">{{ $dateHijriStr }}</div>
            @endif
            <div style="margin-top: 4px;">مدير معهد دار الأرقم</div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $rep['institution']['kepala_asrama'] ?: '................................' }}</div>
            @if(!empty($rep['institution']['nip_kepala_asrama']))
                <div class="sig-nip">NIP. {{ $rep['institution']['nip_kepala_asrama'] }}</div>
            @endif
        </div>
    </div>
</main>
@endforeach

</body>
</html>
