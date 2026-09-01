@extends('adminlte::page')

@section('title', 'Riwayat Kehadiran ' . $siswa->nama_lengkap)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-user-clock text-primary mr-2"></i>Riwayat Kehadiran</h1>
        <a href="{{ route('admin.absensi-siswa.analytics') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>
@stop

@section('content')
<div class="student-history pb-4">
    <section class="student-profile-card card card-outline card-primary">
        <div class="student-avatar"><img src="{{ $siswa->foto_profile_url }}" alt="Foto {{ $siswa->nama_lengkap }}"></div>
        <div class="student-title"><div class="eyebrow">ANALITIK HISTORIS SISWA</div><h2>{{ $siswa->nama_lengkap }}</h2><p>NISN {{ $siswa->nisn ?: '-' }} · {{ $siswa->kelasSaatIni ? $siswa->kelasSaatIni->nama_kelas.$siswa->kelasSaatIni->asrama_suffix : 'Tidak berada di kelas aktif' }}</p></div>
        <div class="hero-message"><i class="fas fa-shield-alt"></i><span>Data ini menjadi bahan pertimbangan wali kelas/BK. Keputusan tetap dilakukan oleh petugas berwenang.</span></div>
    </section>

    <div class="history-grid mt-3">
        @forelse($history as $item)
        <article class="history-card">
            <div><span>{{ $item['year_name'] }}</span><strong>Tingkat {{ $item['level'] }}</strong></div>
            <div class="rate-circle {{ $item['rate'] < 85 ? 'risk' : '' }}">{{ number_format($item['rate'], 1) }}%</div>
            <div class="history-meta"><span>{{ $item['total'] }} catatan</span><span>{{ $item['alpa'] }} alpa</span><span>{{ $item['late'] }} terlambat</span></div>
        </article>
        @empty<div class="empty-wide">Belum ada riwayat presensi final.</div>@endforelse
    </div>

    <div class="row mt-3">
        <div class="col-xl-7 mb-3">
            <section class="panel h-100">
                <div class="panel-head"><div><h3>Timeline Kehadiran</h3><p>Snapshot kelas, mapel, dan guru tidak berubah saat siswa naik kelas.</p></div><span>{{ $records->count() }} catatan</span></div>
                <div class="table-responsive timeline-wrap"><table class="table timeline-table"><thead><tr><th>Tanggal</th><th>Konteks</th><th>Status</th><th>Petugas</th></tr></thead><tbody>
                @forelse($records as $record)
                    @php($statusLabels=['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa','dispen'=>'Dispensasi','keluar_awal'=>'Keluar awal'])
                    <tr><td><strong>{{ \Carbon\Carbon::parse($record->tanggal)->translatedFormat('d M Y') }}</strong><small>{{ $record->year_name }} · Semester {{ $record->semester ?: '-' }}</small></td><td><strong>{{ $record->mode === 'mapel' ? ($record->mapel_snapshot ?: 'Mapel') : 'Kehadiran harian' }}</strong><small>{{ $record->kelas_snapshot ?: '-' }} · Tingkat {{ $record->tingkat ?: '-' }}</small></td><td><span class="status status-{{ $record->status }}">{{ $statusLabels[$record->status] ?? ucfirst($record->status) }}</span>@if($record->late_minutes)<small>{{ $record->late_minutes }} menit</small>@endif</td><td>{{ $record->guru_snapshot ?: '-' }}</td></tr>
                @empty<tr><td colspan="4"><div class="empty-wide compact">Belum ada catatan final.</div></td></tr>@endforelse
                </tbody></table></div>
            </section>
        </div>
        <div class="col-xl-5 mb-3">
            <section class="panel h-100">
                <div class="panel-head"><div><h3>Indikator & Tindak Lanjut</h3><p>Semua hasil deteksi beserta status penanganannya.</p></div></div>
                <div class="insight-list">
                @forelse($alerts as $alert)
                    <article class="insight severity-{{ $alert->severity }}"><div class="insight-top"><span>{{ strtoupper($alert->severity) }}</span><span>{{ strtoupper($alert->status) }}</span></div><h4>{{ $alert->title }}</h4><p>{{ $alert->explanation }}</p><small>{{ $alert->tahunPelajaran?->nama }} · terdeteksi {{ $alert->last_detected_at?->translatedFormat('d M Y H:i') }}</small>@if($alert->review_notes)<div class="review-note"><strong>Catatan tindak lanjut</strong>{{ $alert->review_notes }}</div>@endif</article>
                @empty<div class="empty-wide compact"><i class="fas fa-check-circle"></i> Tidak ada indikator tersimpan.</div>@endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="panel mb-3">
        <div class="panel-head">
            <div><h3>Catatan Wali Kelas</h3><p>Catatan pembinaan melengkapi data kehadiran agar tindak lanjut memiliki konteks.</p></div>
            <div class="d-flex align-items-center"><span class="mr-2">{{ $notes->count() }} catatan</span>@if($isWaliScope)<a href="{{ route('admin.gtk.wali.catatan.index', ['siswa_id' => $siswa->id]) }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Tulis Catatan</a>@endif</div>
        </div>
        <div class="note-grid">
            @forelse($notes as $note)
                <article class="student-note {{ $note->is_penting ? 'important' : '' }}"><div class="note-meta"><strong>{{ $note->tanggal?->translatedFormat('d M Y') }}</strong><span>{{ $note->kategori ?: 'Umum' }} · {{ $note->kelas?->nama_kelas ?: '-' }}</span></div><div class="note-content">{!! $note->catatan_html !!}</div><small>{{ $note->penulis?->name ?: 'Wali kelas' }}@if($note->is_penting) · <b class="text-danger">Penting</b>@endif</small></article>
            @empty<div class="empty-wide compact">Belum ada catatan wali kelas untuk siswa ini.</div>@endforelse
        </div>
    </section>

    @can('view-attendance-audit')
    <section class="panel">
        <div class="panel-head"><div><h3>Jejak Audit</h3><p>Siapa, kapan, dan alasan perubahan presensi tercatat permanen.</p></div><span>{{ $audits->count() }} aktivitas terbaru</span></div>
        <div class="audit-list">
        @forelse($audits as $audit)
            <div class="audit-item"><div class="audit-icon"><i class="fas fa-history"></i></div><div><strong>{{ str_replace('_',' ',ucfirst($audit->action)) }}</strong><p>{{ $audit->actor?->name ?: 'Sistem' }} · {{ $audit->created_at?->translatedFormat('d M Y H:i:s') }}</p>@if($audit->reason)<span>Alasan: {{ $audit->reason }}</span>@endif</div><button type="button" class="btn btn-outline-secondary btn-sm audit-detail" data-before="{{ base64_encode(json_encode($audit->before_values ?? [])) }}" data-after="{{ base64_encode(json_encode($audit->after_values ?? [])) }}">Lihat perubahan</button></div>
        @empty<div class="empty-wide compact">Belum ada aktivitas audit untuk siswa ini.</div>@endforelse
        </div>
    </section>
    @endcan
</div>
@stop

@section('css')
<style>
.note-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.student-note{padding:14px;border:1px solid #e2e8f0;border-left:4px solid #4775ee;border-radius:12px}.student-note.important{border-left-color:#dc3545}.note-meta{display:flex;justify-content:space-between;gap:10px}.note-meta span,.student-note>small{color:#718096;font-size:12px}.note-content{margin:8px 0}.student-history .student-hero{background-image:none}
.student-history{color:#15213a}.student-hero{display:flex;align-items:center;gap:18px;padding:25px;border-radius:20px;background:linear-gradient(120deg,#326af0,#537df0 65%,#3892a1);color:#fff;box-shadow:0 15px 34px rgba(51,94,180,.18)}.student-avatar{display:grid;place-items:center;width:68px;height:68px;flex:0 0 68px;border-radius:20px;background:rgba(255,255,255,.19);font-size:30px;font-weight:900}.eyebrow{font-size:12px;font-weight:800}.student-title h2{font-weight:800;margin:4px 0 1px}.student-title p{margin:0;opacity:.9}.hero-message{display:flex;align-items:center;gap:10px;max-width:390px;margin-left:auto;padding:13px;border-radius:13px;background:rgba(255,255,255,.14);font-size:13px}.history-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.history-card,.panel{border:1px solid #dce5f2;border-radius:17px;background:#fff;box-shadow:0 12px 28px rgba(25,54,104,.07)}.history-card{display:grid;grid-template-columns:1fr auto;gap:12px;padding:17px;border-top:4px solid #4775ee}.history-card span,.history-card strong{display:block}.history-card span{color:#708097;font-size:13px}.history-card strong{font-size:18px}.rate-circle{display:grid;place-items:center;width:60px;height:60px;border-radius:50%;background:#e8f8ef;color:#168451;font-weight:800}.rate-circle.risk{background:#fff0e5;color:#bd6200}.history-meta{display:flex;grid-column:1/-1;gap:8px}.history-meta span{padding:4px 8px;border-radius:15px;background:#f2f5fa;font-size:12px}.panel{padding:20px}.panel-head{display:flex;justify-content:space-between;gap:12px;align-items:start;margin-bottom:14px}.panel-head h3{font-size:19px;font-weight:800;margin:0}.panel-head p{color:#65758d;margin:3px 0}.panel-head>span{padding:5px 10px;border-radius:20px;background:#edf3ff;color:#3e67d9;font-size:12px;font-weight:700}.timeline-wrap,.insight-list{max-height:560px;overflow:auto}.timeline-table th{border-top:0;background:#f6f8fc;color:#596a82;font-size:12px;text-transform:uppercase}.timeline-table td{vertical-align:middle}.timeline-table td small{display:block;color:#8290a4}.status{display:inline-block;border-radius:14px;padding:3px 8px;background:#edf2f7;font-size:12px;font-weight:700}.status-hadir{color:#168451;background:#e7f8ef}.status-terlambat,.status-keluar_awal{color:#ba6500;background:#fff1dd}.status-alpa{color:#c42f3e;background:#ffeaed}.status-izin{color:#3565d7;background:#eaf0ff}.status-sakit{color:#7651c7;background:#f0ebff}.insight{padding:14px;margin-bottom:10px;border:1px solid #e2e8f0;border-left:4px solid #e6a100;border-radius:12px}.insight.severity-high{border-left-color:#df3f50}.insight.severity-low{border-left-color:#2cad70}.insight-top{display:flex;gap:5px}.insight-top span{font-size:10px;font-weight:800;padding:3px 7px;border-radius:13px;background:#f1f4f8}.insight h4{font-size:15px;font-weight:800;margin:7px 0 2px}.insight p{color:#63738a;margin:0}.insight small{display:block;margin-top:7px;color:#8995a7}.review-note{margin-top:9px;padding:9px;border-radius:9px;background:#f4f7fb;font-size:13px}.review-note strong{display:block}.audit-list{display:grid;gap:8px}.audit-item{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid #e8edf4;border-radius:12px}.audit-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:10px;background:#edf3ff;color:#3d68dc}.audit-item>div:nth-child(2){flex:1}.audit-item p{margin:1px 0;color:#718096;font-size:13px}.audit-item span{font-size:12px;color:#4d5f78}.empty-wide{grid-column:1/-1;padding:40px;text-align:center;border:1px dashed #cfd9e8;border-radius:14px;color:#718096}.empty-wide.compact{padding:24px}@media(max-width:991px){.history-grid{grid-template-columns:repeat(2,1fr)}.student-hero{align-items:flex-start;flex-wrap:wrap}.hero-message{max-width:none;width:100%;margin-left:0}}@media(max-width:575px){.history-grid{grid-template-columns:1fr}.student-avatar{width:52px;height:52px;flex-basis:52px}.student-title h2{font-size:22px}.audit-item{align-items:flex-start;flex-wrap:wrap}.audit-item button{margin-left:48px}}
@media(max-width:767px){.note-grid{grid-template-columns:1fr}.note-meta{flex-direction:column;gap:2px}}
.student-history .student-hero{background:linear-gradient(135deg,#4776f4 0%,#4d76e7 52%,#49a49a 100%)}
.student-history .student-hero{min-height:0;padding:20px 22px;border-radius:16px;box-shadow:0 10px 24px rgba(51,94,180,.14)}.student-history .student-avatar{width:54px;height:54px;flex-basis:54px;border-radius:15px;font-size:24px}.student-history .student-title h2{font-size:1.3rem;margin:2px 0}.student-history .student-title p{font-size:.82rem}.student-history .hero-message{max-width:360px;padding:10px 12px;border:1px solid rgba(255,255,255,.14);font-size:.78rem;line-height:1.4}.student-history .history-grid{grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}.student-history .history-card{padding:13px 15px;border-radius:12px;border-top-width:3px;box-shadow:0 6px 16px rgba(25,54,104,.05)}.student-history .history-card strong{font-size:1rem}.student-history .rate-circle{width:52px;height:52px;font-size:.82rem}.student-history .history-meta{gap:6px}.student-history .history-meta span{padding:3px 7px;font-size:.7rem}.student-history .panel{padding:16px;border-radius:14px;box-shadow:0 7px 18px rgba(25,54,104,.05)}.student-history .panel-head{margin-bottom:12px}.student-history .panel-head h3{font-size:1.05rem}.student-history .panel-head p{font-size:.8rem}.student-history .timeline-wrap,.student-history .insight-list{max-height:440px}.student-history .timeline-table th{padding:.65rem .7rem;font-size:.67rem}.student-history .timeline-table td{padding:.7rem}.student-history .timeline-table td strong{font-size:.82rem}.student-history .timeline-table td small{font-size:.7rem}.student-history .insight{padding:12px;margin-bottom:8px;border-radius:10px}.student-history .empty-wide{padding:26px;border-radius:11px;font-size:.84rem}.student-history .empty-wide.compact{padding:20px}.student-history .note-grid{gap:10px}.student-history .student-note{padding:12px;border-radius:10px}.student-history .audit-item{padding:10px;border-radius:10px}.student-history .audit-icon{width:32px;height:32px;border-radius:8px}
.student-history .student-hero{position:relative;display:flex;align-items:center;gap:16px;min-height:132px;overflow:hidden;background:linear-gradient(120deg,#2563eb 0%,#3b82f6 58%,#0f766e 100%)}.student-history .student-hero::before,.student-history .student-hero::after{position:absolute;border-radius:50%;background:rgba(255,255,255,.09);content:''}.student-history .student-hero::before{width:210px;height:210px;right:22%;bottom:-145px}.student-history .student-hero::after{width:108px;height:108px;right:-22px;top:-38px}.student-history .student-avatar{position:relative;z-index:1;width:82px;height:82px;flex:0 0 82px;padding:4px;border:0;border-radius:50%;background:rgba(255,255,255,.28);box-shadow:0 0 0 7px rgba(255,255,255,.12),0 14px 25px rgba(15,23,42,.2);animation:studentPhotoEnter .65s cubic-bezier(.2,.8,.2,1) both,studentPhotoGlow 2.8s ease-in-out .7s infinite}.student-history .student-avatar img{display:block;width:100%;height:100%;border:3px solid #fff;border-radius:50%;object-fit:cover;object-position:center top;background:#e2e8f0}.student-history .student-title{position:relative;z-index:1;min-width:0;flex:1}.student-history .student-title .eyebrow{color:rgba(255,255,255,.78);font-size:.68rem;letter-spacing:.07em}.student-history .student-title h2{color:#fff;font-size:1.35rem}.student-history .student-title p{color:rgba(255,255,255,.88)}.student-history .hero-message{position:relative;z-index:1;flex:0 1 330px;margin-left:0;background:rgba(15,23,42,.12);backdrop-filter:blur(3px)}@keyframes studentPhotoEnter{from{opacity:0;transform:translateY(12px) scale(.82)}to{opacity:1;transform:translateY(0) scale(1)}}@keyframes studentPhotoGlow{50%{box-shadow:0 0 0 10px rgba(255,255,255,.06),0 16px 29px rgba(15,23,42,.24)}}@media(max-width:575px){.student-history .student-hero{align-items:flex-start;min-height:0;padding:17px}.student-history .student-avatar{width:66px;height:66px;flex-basis:66px}.student-history .student-title h2{font-size:1.08rem}.student-history .hero-message{width:100%;flex-basis:100%;margin-top:2px}}@media(prefers-reduced-motion:reduce){.student-history .student-avatar{animation:none}}
.student-history .student-profile-card{display:grid;grid-template-columns:86px minmax(0,1fr) minmax(230px,.65fr);gap:16px;align-items:center;margin:0;padding:18px 20px;border-radius:14px;box-shadow:0 7px 18px rgba(25,54,104,.05)}.student-history .student-profile-card .student-avatar{width:76px;height:76px;flex:0 0 76px;padding:3px;border-radius:14px;background:#eff6ff;box-shadow:none;animation:none}.student-history .student-profile-card .student-avatar img{border:0;border-radius:11px}.student-history .student-profile-card .student-title{min-width:0}.student-history .student-profile-card .student-title .eyebrow{color:#2563eb;font-size:.68rem;letter-spacing:.07em}.student-history .student-profile-card .student-title h2{margin:3px 0;color:#0f172a;font-size:1.2rem}.student-history .student-profile-card .student-title p{margin:0;color:#64748b;font-size:.82rem}.student-history .student-profile-card .hero-message{display:flex;align-items:center;gap:9px;margin:0;padding:10px 12px;border:1px solid #dbeafe;border-radius:10px;background:#f8fbff;color:#47627f;font-size:.76rem;line-height:1.4}.student-history .student-profile-card .hero-message i{color:#2563eb}@media(max-width:767px){.student-history .student-profile-card{grid-template-columns:72px minmax(0,1fr);gap:12px;padding:15px}.student-history .student-profile-card .student-avatar{width:64px;height:64px}.student-history .student-profile-card .hero-message{grid-column:1/-1}.student-history .student-profile-card .student-title h2{font-size:1.04rem}}
</style>
@stop

@section('js')
<script>
document.querySelectorAll('.audit-detail').forEach(button=>button.addEventListener('click',function(){
    let before={},after={};try{before=JSON.parse(atob(this.dataset.before||''))||{};after=JSON.parse(atob(this.dataset.after||''))||{}}catch(e){}
    const pretty=value=>`<pre style="text-align:left;max-height:220px;overflow:auto;background:#f5f7fb;padding:10px;border-radius:8px">${JSON.stringify(value,null,2).replace(/&/g,'&amp;').replace(/</g,'&lt;')}</pre>`;
    Swal.fire({title:'Detail perubahan',width:720,html:`<div class="row"><div class="col-md-6"><strong>Sebelum</strong>${pretty(before)}</div><div class="col-md-6"><strong>Sesudah</strong>${pretty(after)}</div></div>`,confirmButtonText:'Tutup'});
}));
</script>
@stop
