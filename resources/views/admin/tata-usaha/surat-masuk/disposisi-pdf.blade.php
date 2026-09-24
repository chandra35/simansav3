<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 8mm 15mm 10mm; }
        body { color:#111; font-family: Arial, Helvetica, sans-serif; font-size:11px; margin:0; }
        .kop { border-bottom:3px double #111; height:82px; padding:0 0 5px; position:relative; text-align:center; }
        .kop-logo { height:82px; object-fit:contain; position:absolute; top:0; width:82px; }
        .kop-logo.left { left:0; }
        .kop-logo.right { right:0; }
        .kop-title { line-height:1.12; margin:0 62px; }
        .kop-title div { font-size:13px; }
        .kop-title .line-3 { font-size:14px; }
        .kop-meta { font-size:7.5px; line-height:1.25; margin:3px 32px 0; }
        .title { font-size:20px; font-weight:bold; margin:22px 0 28px; text-align:center; text-decoration:underline; }
        table.disposition { border:2px solid #222; border-collapse:collapse; width:100%; table-layout:fixed; }
        table.disposition td { border:1px solid #444; padding:4px 5px; vertical-align:top; }
        table.disposition .label { white-space:nowrap; width:18%; }
        table.disposition .colon { text-align:center; width:7%; }
        table.disposition .content { width:42%; }
        table.disposition .forward { width:33%; }
        table.disposition .index td { height:22px; }
        table.disposition .file td { height:13px; }
        table.disposition .date-number td { height:27px; }
        table.disposition .origin td { height:78px; }
        table.disposition .summary td { height:105px; }
        table.disposition .received td { height:18px; }
        table.disposition .completion td { height:18px; }
        table.disposition .disposition-area td { height:185px; }
        table.disposition .return-row td { height:16px; }
        table.disposition .signature td { height:13px; }
        .forward-title { height:20px; }
        .forward-item { height:38px; }
        .return-note { font-size:10px; margin:0; padding:3px; text-align:center; }
        .footer-meta { bottom:-5mm; color:#666; font-size:7px; left:0; position:fixed; text-align:left; width:100%; }
    </style>
</head>
<body>
    @php($kopLines = $setting->kop_header_lines)
    <div class="kop">
        @if($logoKemenagDataUri)<img class="kop-logo left" src="{{ $logoKemenagDataUri }}" alt="Logo Kementerian Agama">@endif
        @if($logoSekolahDataUri)<img class="kop-logo right" src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah">@endif
        <div class="kop-title">
            <div>{{ $kopLines[0] }}</div>
            <div>{{ $kopLines[1] }}</div>
            <div class="line-3">{{ $kopLines[2] }}</div>
        </div>
        <div class="kop-meta">
            {{ $setting->kop_identitas }}<br>
            {{ $setting->kop_alamat }}<br>
            <em>Website : <u>{{ $setting->website ?: '-' }}</u>&nbsp;&nbsp; E-mail: {{ $setting->email ?: '-' }}</em>
        </div>
    </div>
    <div class="title">LEMBAR DISPOSISI</div>
    <table class="disposition">
        <colgroup><col style="width:18%"><col style="width:7%"><col style="width:42%"><col style="width:33%"></colgroup>
        <tr class="index"><td class="label">Indeks</td><td class="colon"></td><td class="content" colspan="2"></td></tr>
        <tr class="file"><td class="label">Berkas</td><td class="colon">:</td><td class="content" colspan="2"><strong>{{ $item->nomor_berkas }}</strong></td></tr>
        <tr class="date-number"><td class="label">Tanggal/ Nomor</td><td class="colon">:</td><td class="content" colspan="2">{{ $item->tanggal_nomor }}</td></tr>
        <tr class="origin"><td class="label">Asal</td><td class="colon">:</td><td class="content" colspan="2">{{ $item->asal }}</td></tr>
        <tr class="summary"><td class="label">Isi Ringkasan</td><td class="colon">:</td><td class="content" colspan="2">{{ $item->isi_ringkasan }}</td></tr>
        <tr class="received"><td class="label">Diterima Tanggal</td><td class="colon">:</td><td class="content" colspan="2">{{ $item->diterima_tanggal?->format('d F Y') }}</td></tr>
        <tr class="completion"><td class="label">Tanggal Penyelesaian</td><td class="colon">:</td><td class="content" colspan="2"></td></tr>
        <tr class="disposition-area">
            <td class="label">Isi Disposisi</td><td class="colon">:</td>
            <td class="content"></td>
            <td class="forward"><div class="forward-title">Diteruskan Kepada</div><div class="forward-item">1.</div><div class="forward-item">2.</div><div class="forward-item">3.</div></td>
        </tr>
        <tr class="return-row"><td colspan="4" class="return-note">Sudah digunakan harap segera dikembalikan</td></tr>
        <tr class="signature"><td class="label">Kepada</td><td class="colon">:</td><td class="content" colspan="2"></td></tr>
        <tr class="signature"><td class="label">Tanggal</td><td class="colon">:</td><td class="content" colspan="2"></td></tr>
        <tr class="signature"><td class="label">Nama/ Paraf</td><td class="colon">:</td><td class="content" colspan="2"></td></tr>
    </table>
    <div class="footer-meta">
        Link: {{ url()->current() }} &nbsp;|&nbsp; Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }} WIB
    </div>
</body>
</html>
