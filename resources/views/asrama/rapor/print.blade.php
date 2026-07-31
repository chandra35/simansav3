<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor Asrama - {{ $report['identity']['nama'] }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
<style>
@page{size:A4 portrait;margin:8mm}
*{box-sizing:border-box}body{margin:0;color:#111;background:#eceff2;font-family:Arial,sans-serif;font-size:11px}
.toolbar{position:sticky;top:0;z-index:2;padding:10px;text-align:center;background:#173d5d}.toolbar button{padding:8px 18px;border:0;border-radius:6px;color:#fff;background:#168b87;cursor:pointer}
.sheet{position:relative;width:210mm;min-height:297mm;margin:12px auto;padding:8mm;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.16);overflow:hidden}
.watermark{position:absolute;top:39%;left:50%;width:88mm;opacity:.07;transform:translate(-50%,-50%);z-index:0}.content{position:relative;z-index:1}
.header{display:grid;grid-template-columns:22mm 1fr 22mm;align-items:center;border-bottom:3px double #111;padding-bottom:4px;text-align:center}.header img{max-width:19mm;max-height:20mm}.header h1{font-size:16px;margin:0 0 3px}.header p{margin:2px 0;font-size:9px}
.title{text-align:center;font-family:"Noto Naskh Arabic","Amiri","Traditional Arabic",serif;font-size:22px;font-weight:bold;margin:12px 0 8px;direction:rtl}
table{width:100%;border-collapse:collapse}.identity td{padding:4px 6px;border:1px solid #111}.identity .label{width:18%;font-weight:bold}.rtl{direction:rtl;text-align:right;font-family:"Noto Naskh Arabic","Amiri","Traditional Arabic",serif;font-size:14px}
.grades{margin-top:2px;table-layout:fixed}.grades th,.grades td{border:1px solid #111;padding:3px 4px;vertical-align:middle}.grades th{text-align:center}.grades .no{width:5%;text-align:center}.grades .arab{width:17%;direction:rtl;text-align:right;font-family:"Noto Naskh Arabic","Amiri","Traditional Arabic",serif;font-size:13px}.grades .latin{width:16%;font-weight:600}.grades .score{width:7%;text-align:center;font-weight:bold}.grades .words{width:14%;direction:rtl;text-align:right;font-family:"Noto Naskh Arabic","Amiri","Traditional Arabic",serif;font-size:13px}
.summary td{border:1px solid #111;padding:4px}.summary .main{font-size:15px;font-weight:bold;text-align:center}.meta{margin-top:3px}.meta td{border:1px solid #111;padding:4px}.decision{margin-top:5px;padding:4px 0;border-top:1px solid #111;border-bottom:1px solid #111;line-height:1.8;text-align:center}
.signatures{display:grid;grid-template-columns:1fr 1fr;gap:32mm;margin-top:15mm;text-align:center}.sign-name{margin-top:23mm;font-weight:bold;text-decoration:underline}.notes{margin-top:6px;font-size:10px}
@media print{body{background:#fff}.toolbar{display:none}.sheet{width:auto;min-height:auto;margin:0;padding:0;box-shadow:none}}
</style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
<main class="sheet">
@if(!empty($report['institution']['logo_sekolah']))<img class="watermark" src="{{ $report['institution']['logo_sekolah'] }}" alt="">@endif
<div class="content">
<header class="header">
<div>@if(!empty($report['institution']['logo_kemenag']))<img src="{{ $report['institution']['logo_kemenag'] }}" alt="Kemenag">@endif</div>
<div><h1>{{ strtoupper($report['institution']['nama_sekolah']) }}</h1><strong>{{ $report['institution']['nama_asrama'] }}</strong><p>{{ $report['institution']['alamat'] }}</p><p>Website: {{ $report['institution']['website'] }} · Email: {{ $report['institution']['email'] }}</p></div>
<div>@if(!empty($report['institution']['logo_sekolah']))<img src="{{ $report['institution']['logo_sekolah'] }}" alt="Logo">@endif</div>
</header>
<div class="title" lang="ar">كشف الدرجات</div>
<table class="identity">
<tr><td class="label rtl" lang="ar">الاسم</td><td><strong>{{ $report['identity']['nama'] }}</strong></td><td class="label rtl" lang="ar">الفصل</td><td><strong>{{ $report['identity']['kelas'] }}</strong> <span class="rtl">{{ $report['identity']['kelas_arab'] }}</span></td></tr>
<tr><td class="label rtl" lang="ar">السنة الدراسية</td><td>{{ $report['identity']['tahun_pelajaran'] }} · {{ $report['identity']['semester'] }}</td><td class="label rtl" lang="ar">رقم دفتر القيد</td><td><strong>{{ $report['identity']['nomor_induk_asrama'] }}</strong></td></tr>
</table>
@php
    $scores=collect($report['scores']);$half=(int)ceil($scores->count()/2);
    $left=$scores->take($half)->values();$right=$scores->slice($half)->values();$rows=max($left->count(),$right->count());
@endphp
<table class="grades">
<thead><tr><th colspan="5">Mata Pelajaran / <span class="rtl">المواد</span></th><th colspan="5">Mata Pelajaran / <span class="rtl">المواد</span></th></tr><tr><th class="no">No</th><th class="arab">Arab</th><th class="latin">Latin</th><th class="score">Nilai</th><th class="words">الدرجة</th><th class="no">No</th><th class="arab">Arab</th><th class="latin">Latin</th><th class="score">Nilai</th><th class="words">الدرجة</th></tr></thead>
<tbody>
@for($i=0;$i<$rows;$i++)@php($a=$left->get($i))@php($b=$right->get($i))<tr>
@if($a)<td class="no">{{ $i+1 }}</td><td class="arab" lang="ar">{{ $a['nama_arab'] }}</td><td class="latin">{{ $a['nama_latin'] }}</td><td class="score">{{ $a['nilai']??'-' }}</td><td class="words" lang="ar">{{ $a['nilai_arab'] }}</td>@else<td colspan="5"></td>@endif
@if($b)<td class="no">{{ $half+$i+1 }}</td><td class="arab" lang="ar">{{ $b['nama_arab'] }}</td><td class="latin">{{ $b['nama_latin'] }}</td><td class="score">{{ $b['nilai']??'-' }}</td><td class="words" lang="ar">{{ $b['nilai_arab'] }}</td>@else<td colspan="5"></td>@endif
</tr>@endfor
</tbody></table>
<table class="summary">
<tr><td width="20%" class="rtl" lang="ar">مجموع النتائج</td><td width="15%" class="main">{{ $report['summary']['jumlah'] }}</td><td>Jumlah Nilai</td><td width="20%" class="rtl" lang="ar">المعدل</td><td width="15%" class="main">{{ $report['summary']['rata_rata']??'-' }}</td><td>Rata-rata</td></tr>
<tr><td class="rtl" lang="ar">النظافة</td><td class="main">{{ $rapor->nilai_kebersihan??'-' }}</td><td>Kebersihan</td><td class="rtl" lang="ar">السلوك</td><td class="main">{{ $rapor->nilai_kelakuan??'-' }}</td><td>Kelakuan</td></tr>
<tr><td class="rtl" lang="ar">المواظبة</td><td class="main">{{ $rapor->nilai_kerajinan??'-' }}</td><td>Kerajinan</td><td class="rtl" lang="ar">التقدير</td><td colspan="2"><strong>{{ $rapor->predikat??'-' }}</strong></td></tr>
</table>
<table class="meta"><tr><td><strong>Sakit / مرض:</strong> {{ $rapor->sakit }}</td><td><strong>Izin / استئذان:</strong> {{ $rapor->izin }}</td><td><strong>Lain-lain / آخر:</strong> {{ $rapor->lain_lain }}</td></tr></table>
<div class="decision"><strong>Keputusan / <span class="rtl">التقرير</span>:</strong> {{ $rapor->keputusan??'-' }} &nbsp; | &nbsp; <strong>Tanggal:</strong> {{ $rapor->tanggal_rapor?->translatedFormat('d F Y')??'-' }} <span class="rtl" lang="ar">{{ $rapor->tanggal_hijriah }}</span></div>
@if($rapor->catatan_wali)<div class="notes"><strong>Catatan pengasuh:</strong> {{ $rapor->catatan_wali }}</div>@endif
<div class="signatures"><div><span>Mengetahui,<br>Kepala Asrama</span><div class="sign-name">{{ $report['institution']['kepala_asrama']??'................................' }}</div><div>NIP. {{ $report['institution']['nip_kepala_asrama']??'-' }}</div></div><div><span>Pengasuh Rombel<br><span class="rtl" lang="ar">مشرف الفصل</span></span><div class="sign-name">{{ $report['institution']['pengasuh']??'................................' }}</div><div>NIP. {{ $report['institution']['nip_pengasuh']??'-' }}</div></div></div>
</div></main>
</body></html>
