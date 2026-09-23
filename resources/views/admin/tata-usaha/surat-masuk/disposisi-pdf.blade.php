<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 13mm 13mm; }
        body { color:#111; font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; margin:0; }
        .kop { border-bottom: 3px double #111; min-height:77px; padding:0 2px 7px; position:relative; text-align:center; }
        .kop-logo { height:62px; object-fit:contain; position:absolute; top:1px; width:62px; }
        .kop-logo.left { left:0; }
        .kop-logo.right { right:0; }
        .kop-title { font-weight:bold; line-height:1.18; margin:0 67px; }
        .kop-title .line-1, .kop-title .line-2 { font-size:12px; }
        .kop-title .line-3 { font-size:14px; }
        .kop-meta { font-size:8px; line-height:1.35; margin:2px 45px 0; }
        .title { font-size:14px; font-weight:bold; margin:12px 0 9px; text-align:center; }
        table.disposition { border-collapse:collapse; width:100%; }
        table.disposition td { border:1px solid #222; padding:4px 5px; vertical-align:top; }
        table.disposition .label { font-weight:bold; width:18%; }
        table.disposition .colon { text-align:center; width:4%; }
        table.disposition .value { width:38%; }
        table.disposition .side-label { font-weight:bold; width:18%; }
        table.disposition .blank-short { height:18px; }
        table.disposition .blank-medium { height:34px; }
        table.disposition .blank-large { height:64px; }
        table.disposition .blank-xl { height:82px; }
        table.disposition .number { text-align:center; width:5%; }
        .return-note { font-size:9px; margin:7px 0 0; }
    </style>
</head>
<body>
    <div class="kop">
        @if($logoKemenagDataUri)<img class="kop-logo left" src="{{ $logoKemenagDataUri }}" alt="Logo Kementerian Agama">@endif
        @if($logoSekolahDataUri)<img class="kop-logo right" src="{{ $logoSekolahDataUri }}" alt="Logo Madrasah">@endif
        <div class="kop-title">
            <div class="line-1">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
            <div class="line-2">KEMENTERIAN AGAMA KOTA METRO</div>
            <div class="line-3">MADRASAH ALIYAH NEGERI 1 METRO</div>
        </div>
        <div class="kop-meta">
            NSM: 131118720001 &nbsp;&nbsp; NPSN: 10648374 &nbsp;&nbsp; AKREDITASI: A<br>
            Jl. Ki Hajar Dewantara No. 110 Kampus 15A Iringmulyo Metro Timur<br>
            Website: www.man1metro.sch.id &nbsp;&nbsp; E-mail: man1kotametro@gmail.com
        </div>
    </div>

    <div class="title">LEMBAR DISPOSISI</div>

    <table class="disposition">
        <tr><td class="label">Indeks</td><td class="colon"></td><td class="value" colspan="3"></td></tr>
        <tr><td class="label">Berkas</td><td class="colon">:</td><td class="value" colspan="3"><strong>{{ $item->nomor_berkas }}</strong></td></tr>
        <tr><td class="label">Tanggal / Nomor</td><td class="colon">:</td><td class="value" colspan="3">{{ $item->tanggal_nomor }}</td></tr>
        <tr><td class="label">Asal</td><td class="colon">:</td><td class="value" colspan="3">{{ $item->asal }}</td></tr>
        <tr><td class="label">Isi Ringkasan</td><td class="colon">:</td><td class="value" colspan="3">{{ $item->isi_ringkasan }}</td></tr>
        <tr><td class="label">Diterima Tanggal</td><td class="colon">:</td><td class="value" colspan="3">{{ $item->diterima_tanggal?->format('d-m-Y') }}</td></tr>
        <tr><td class="label">Tanggal Penyelesaian</td><td class="colon">:</td><td class="value blank-short" colspan="3"></td></tr>
        <tr><td class="label">Isi Disposisi</td><td class="colon">:</td><td class="side-label">Diteruskan Kepada</td><td class="number">1.</td><td class="value blank-large"></td></tr>
        <tr><td class="label"></td><td class="colon"></td><td class="side-label"></td><td class="number">2.</td><td class="value blank-medium"></td></tr>
        <tr><td class="label"></td><td class="colon"></td><td class="side-label"></td><td class="number">3.</td><td class="value blank-medium"></td></tr>
        <tr><td class="label"></td><td class="colon"></td><td class="value blank-xl" colspan="3"></td></tr>
        <tr><td class="label">Kepada</td><td class="colon">:</td><td class="value" colspan="3"></td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td><td class="value" colspan="3"></td></tr>
        <tr><td class="label">Nama / Paraf</td><td class="colon">:</td><td class="value blank-large" colspan="3"></td></tr>
    </table>

    <p class="return-note">Sudah digunakan harap segera dikembalikan.</p>
</body>
</html>
