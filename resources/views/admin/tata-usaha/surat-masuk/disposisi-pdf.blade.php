<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 8mm 15mm 10mm; }
        body { color:#111; font-family: Arial, Helvetica, sans-serif; font-size:13px; margin:0; }
        .kop { border-bottom:3px double #111; height:82px; padding:0 0 5px; position:relative; text-align:center; }
        .kop-logo { height:82px; object-fit:contain; position:absolute; top:0; width:82px; }
        .kop-logo.left { width:98px; }
        .kop-logo.left { left:0; }
        .kop-logo.right { right:0; }
        .kop-title { line-height:1.12; margin:0 62px; }
        .kop-title div { font-size:15px; }
        .kop-title .line-3 { font-size:16px; }
        .kop-meta { font-size:9px; line-height:1.15; margin:3px 32px 0; }
        .title { font-size:24px; font-weight:bold; margin:15px 0 20px; text-align:center; text-decoration:underline; }
        table.disposition { border:2px solid #222; border-collapse:collapse; width:100%; table-layout:fixed; }
        table.disposition td { border:1px solid #444; padding:3px 5px; vertical-align:top; font-size:13px; }
        table.disposition .label { white-space:nowrap; width:25%; }
        table.disposition .colon { width:5%; text-align:center; }
        table.disposition .content { width:70%; }
        table.disposition .index td { height:22px; }
        table.disposition .file td { height:13px; }
        table.disposition .date-number td { height:27px; }
        table.disposition .origin td { height:70px; }
        table.disposition .summary td { height:95px; }
        table.disposition .received td { height:18px; }
        table.disposition .completion td { height:18px; }
        table.disposition .disposition-area > td { height:170px; }
        table.disposition .return-row td { height:16px; }
        table.disposition .signature td { height:13px; }
        .forward-title { height:18px; }
        .forward-item { height:35px; }
        .free-field-row { height:18px; padding:3px 5px !important; white-space:nowrap; }
        .free-field-row .free-label { display:inline-block; width:25%; }
        .free-field-row .free-colon { display:inline-block; width:5%; text-align:center; }
        .free-field-row .free-value { display:inline-block; width:70%; }
        .disposition-cell { height:170px; padding:0 !important; position:relative; }
        .disposition-label { left:0; padding:4px 5px; position:absolute; top:0; width:23%; }
        .disposition-writing { height:100%; left:23%; position:absolute; top:0; width:47%; }
        .disposition-forward { height:100%; padding:4px 5px; position:absolute; right:0; top:0; width:30%; }
        .return-note { font-size:10px; margin:0; padding:3px; text-align:center; }
        .footer-meta { bottom:-1mm; color:#666; font-size:7px; left:0; position:fixed; text-align:left; width:100%; }
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
        <colgroup><col style="width:25%"><col style="width:5%"><col style="width:70%"></colgroup>
        <tr class="index"><td class="label">Indeks</td><td class="colon"></td><td class="content"></td></tr>
        <tr class="file"><td class="label">Berkas</td><td class="colon">:</td><td class="content"><strong>{{ $item->nomor_berkas }}</strong></td></tr>
        <tr class="date-number"><td class="label">Tanggal/ Nomor</td><td class="colon">:</td><td class="content">{{ $item->tanggal_nomor }}</td></tr>
        <tr class="origin"><td class="label">Asal</td><td class="colon">:</td><td class="content">{{ $item->asal }}</td></tr>
        <tr class="summary"><td class="label">Isi Ringkasan</td><td class="colon">:</td><td class="content">{{ $item->isi_ringkasan }}</td></tr>
        <tr class="received"><td class="label">Diterima Tanggal</td><td class="colon">:</td><td class="content">{{ $item->diterima_tanggal?->format('d F Y') }}</td></tr>
        <tr class="completion"><td colspan="3" class="free-field-row"><span class="free-label">Tanggal Penyelesaian</span><span class="free-colon">:</span><span class="free-value"></span></td></tr>
        <tr class="disposition-area">
            <td colspan="3" class="disposition-cell">
                <div class="disposition-label">Isi Disposisi :</div>
                <div class="disposition-writing"></div>
                <div class="disposition-forward"><div class="forward-title">Diteruskan Kepada</div><div class="forward-item">1.</div><div class="forward-item">2.</div><div class="forward-item">3.</div></div>
            </td>
        </tr>
        <tr class="return-row"><td colspan="3" class="return-note">Sudah digunakan harap segera dikembalikan</td></tr>
        <tr class="signature"><td colspan="3" class="free-field-row"><span class="free-label">Kepada</span><span class="free-colon">:</span><span class="free-value"></span></td></tr>
        <tr class="signature"><td colspan="3" class="free-field-row"><span class="free-label">Tanggal</span><span class="free-colon">:</span><span class="free-value"></span></td></tr>
        <tr class="signature"><td colspan="3" class="free-field-row"><span class="free-label">Nama/ Paraf</span><span class="free-colon">:</span><span class="free-value"></span></td></tr>
    </table>
    <div class="footer-meta">
        Link: {{ url()->current() }} &nbsp;|&nbsp; Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }} WIB
    </div>
</body>
</html>
