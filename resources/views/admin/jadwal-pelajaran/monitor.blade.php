@extends('adminlte::page')

@section('title', 'Monitor Jadwal Belajar')

@section('content_header')
    <div class="row mb-2 monitor-header">
        <div class="col-sm-7"><h1><i class="fas fa-tv text-primary"></i> Monitor Jadwal Belajar</h1></div>
        <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
            <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Jadwal Pelajaran</a>
            <button type="button" id="monitorFullscreen" class="btn btn-primary btn-sm"><i class="fas fa-expand"></i> Layar Penuh</button>
        </div>
    </div>
@stop

@section('content')
<div class="jadwal-monitor" id="jadwalMonitor">
    <section class="jadwal-monitor__hero">
        <div>
            <div class="jadwal-monitor__eyebrow"><span class="jadwal-monitor__pulse"></span> MONITOR PEMBELAJARAN LANGSUNG</div>
            <h2 id="monitorDay">Memuat jadwal…</h2>
            <p>{{ $tahun?->nama ?? 'Tahun pelajaran belum aktif' }} · Semester {{ $semester }}</p>
        </div>
        <div class="jadwal-monitor__clock"><strong id="monitorClock">--:--:--</strong><span>WIB · Asia/Jakarta</span></div>
    </section>

    <section class="jadwal-monitor__focus" id="monitorFocus" aria-live="polite"></section>
    <section class="jadwal-monitor__section">
        <div class="jadwal-monitor__section-title"><i class="fas fa-chalkboard-teacher"></i> Kegiatan kelas <span id="monitorCount" class="badge badge-primary ml-1">0</span></div>
        <div class="jadwal-monitor__classes" id="monitorClasses"></div>
    </section>
    <section class="jadwal-monitor__timeline" id="monitorTimeline" aria-label="Urutan jam pelajaran hari ini"></section>
</div>
@stop

@section('css')
<style>
.jadwal-monitor{--jm-blue:#2563eb;--jm-teal:#0f766e;--jm-ink:#10213d;color:var(--jm-ink)}
.jadwal-monitor__hero{position:relative;overflow:hidden;background:linear-gradient(115deg,#1d4ed8,#2563eb 50%,#0f766e);color:#fff;border-radius:22px;padding:1.45rem 1.7rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;box-shadow:0 14px 32px rgba(37,99,235,.2)}.jadwal-monitor__hero:after{content:'';position:absolute;width:230px;height:230px;right:12%;top:-150px;border:32px solid rgba(255,255,255,.11);border-radius:50%;pointer-events:none}
.jadwal-monitor__eyebrow{font-size:.76rem;font-weight:800;letter-spacing:.08em}.jadwal-monitor__pulse{display:inline-block;width:9px;height:9px;background:#86efac;border-radius:50%;margin-right:.35rem;box-shadow:0 0 0 5px rgba(134,239,172,.2)}
.jadwal-monitor h2{margin:.35rem 0;font-weight:800;font-size:1.65rem}.jadwal-monitor__hero p{margin:0;opacity:.9}.jadwal-monitor__clock{min-width:175px;text-align:right}.jadwal-monitor__clock strong{display:block;font-size:2.1rem;line-height:1;font-variant-numeric:tabular-nums}.jadwal-monitor__clock span{font-size:.77rem;opacity:.84}
.jadwal-monitor__focus{margin:1rem 0;background:linear-gradient(100deg,#fff,#f4f9ff);border:1px solid #c9ddff;border-left:6px solid var(--jm-blue);border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.06);padding:1rem 1.2rem}.jm-focus--break{background:linear-gradient(100deg,#fffaf0,#fff7ed);border-color:#fed7aa;border-left-color:#f59e0b}.jm-focus--break .jm-focus-label{color:#b45309}.jm-focus-label{font-size:.73rem;font-weight:800;letter-spacing:.06em;color:#2563eb}.jm-focus-main{font-size:1.32rem;font-weight:900;margin:.16rem 0;color:#10213d}.jm-focus-meta{color:#64748b;font-size:.9rem}.jm-progress{height:8px;border-radius:999px;background:#dbeafe;margin-top:.75rem;overflow:hidden}.jm-focus--break .jm-progress{background:#ffedd5}.jm-progress span{display:block;height:100%;background:linear-gradient(90deg,#2563eb,#14b8a6);border-radius:999px}.jm-focus--break .jm-progress span{background:linear-gradient(90deg,#f59e0b,#f97316)}
.jadwal-monitor__section,.jadwal-monitor__timeline{background:#fff;border:1px solid #dbe5f2;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.05)}.jadwal-monitor__section{padding:1rem 1.1rem}.jadwal-monitor__section-title{font-weight:800;margin-bottom:.85rem;color:#172554}.jadwal-monitor__classes{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.65rem}.jm-class{position:relative;overflow:hidden;border:1px solid #e2e8f0;border-radius:12px;padding:.7rem .8rem .7rem 3.35rem;background:#fff;transition:transform .15s,box-shadow .15s}.jm-class:hover{transform:translateY(-2px);box-shadow:0 8px 16px rgba(15,23,42,.09)}.jm-class__icon{position:absolute;left:.75rem;top:.78rem;width:28px;height:28px;border-radius:9px;display:flex;align-items:center;justify-content:center}.jm-class__kelas{font-size:.76rem;font-weight:800}.jm-class__mapel{font-weight:900;margin:.12rem 0}.jm-class__guru{font-size:.8rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.jm-class--tone-0{background:linear-gradient(115deg,#fff,#eff6ff);border-color:#bfdbfe}.jm-class--tone-0 .jm-class__icon{background:#dbeafe;color:#2563eb}.jm-class--tone-0 .jm-class__kelas{color:#2563eb}.jm-class--tone-1{background:linear-gradient(115deg,#fff,#ecfdf5);border-color:#a7f3d0}.jm-class--tone-1 .jm-class__icon{background:#d1fae5;color:#059669}.jm-class--tone-1 .jm-class__kelas{color:#059669}.jm-class--tone-2{background:linear-gradient(115deg,#fff,#f5f3ff);border-color:#ddd6fe}.jm-class--tone-2 .jm-class__icon{background:#ede9fe;color:#7c3aed}.jm-class--tone-2 .jm-class__kelas{color:#7c3aed}.jm-class--tone-3{background:linear-gradient(115deg,#fff,#fff7ed);border-color:#fed7aa}.jm-class--tone-3 .jm-class__icon{background:#ffedd5;color:#ea580c}.jm-class--tone-3 .jm-class__kelas{color:#ea580c}.jm-class--tone-4{background:linear-gradient(115deg,#fff,#fdf2f8);border-color:#fbcfe8}.jm-class--tone-4 .jm-class__icon{background:#fce7f3;color:#db2777}.jm-class--tone-4 .jm-class__kelas{color:#db2777}.jm-class--tone-5{background:linear-gradient(115deg,#fff,#ecfeff);border-color:#a5f3fc}.jm-class--tone-5 .jm-class__icon{background:#cffafe;color:#0891b2}.jm-class--tone-5 .jm-class__kelas{color:#0891b2}.jm-empty{color:#64748b;padding:.6rem 0}
.jadwal-monitor__timeline{display:flex;gap:.55rem;overflow-x:auto;padding:.8rem;margin-top:1rem}.jm-slot{min-width:125px;border:1px solid #e2e8f0;border-radius:11px;padding:.55rem .65rem;color:#64748b;background:#f8fafc}.jm-slot--break{background:#fffaf0;border-color:#fed7aa}.jm-slot--current{border-color:#2563eb;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;box-shadow:0 6px 14px rgba(37,99,235,.22)}.jm-slot--current.jm-slot--break{border-color:#f59e0b;background:linear-gradient(135deg,#f59e0b,#f97316)}.jm-slot--current strong{color:#fff}.jm-slot--next{border-color:#14b8a6;background:#f0fdfa}.jm-slot strong{display:block;color:#10213d;font-size:.88rem}.jm-slot small{display:block;font-size:.74rem}
body.monitor-mode .main-sidebar,body.monitor-mode .main-header,body.monitor-mode .content-header,body.monitor-mode .main-footer{display:none!important}body.monitor-mode .content-wrapper{margin-left:0!important;min-height:100vh!important}body.monitor-mode .content{padding:20px!important}body.monitor-mode .jadwal-monitor{max-width:1600px;margin:auto}
@media(max-width:575.98px){.jadwal-monitor__hero{padding:1.1rem;align-items:flex-start;flex-direction:column}.jadwal-monitor__clock{text-align:left}.jadwal-monitor__clock strong{font-size:1.7rem}.jadwal-monitor h2{font-size:1.3rem}.jadwal-monitor__classes{grid-template-columns:1fr}}
</style>
@stop

@section('js')
<script>
(() => {
    const slots = @json($slots);
    const dayNames = {senin:'Senin',selasa:'Selasa',rabu:'Rabu',kamis:'Kamis',jumat:'Jumat',sabtu:'Sabtu'};
    const day = @json($hari);
    const timeParts = () => Object.fromEntries(new Intl.DateTimeFormat('en-GB',{timeZone:'Asia/Jakarta',hour:'2-digit',minute:'2-digit',second:'2-digit',weekday:'long',hourCycle:'h23'}).formatToParts(new Date()).filter(p => p.type !== 'literal').map(p => [p.type,p.value]));
    const toMinutes = (value) => { const [h,m] = value.split(':').map(Number); return h * 60 + m; };
    const escape = (value) => String(value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const tone = (value) => [...String(value)].reduce((total, char) => total + char.charCodeAt(0), 0) % 6;
    const subjectIcon = (value) => {
        const name = String(value).toLowerCase();
        if (name.includes('matematika') || name.includes('fisika') || name.includes('kimia')) return 'fa-square-root-alt';
        if (name.includes('bahasa') || name.includes('sejarah') || name.includes('geografi')) return 'fa-book-open';
        if (name.includes('qur') || name.includes('fiq') || name.includes('aqid') || name.includes('tahfidz')) return 'fa-mosque';
        if (name.includes('informatika')) return 'fa-laptop-code';
        if (name.includes('penjas')) return 'fa-running';
        return 'fa-lightbulb';
    };
    let previousDay = null;
    function render() {
        const now = timeParts(); const minutes = Number(now.hour) * 60 + Number(now.minute);
        document.getElementById('monitorClock').textContent = `${now.hour}:${now.minute}:${now.second}`;
        document.getElementById('monitorDay').textContent = day ? `${dayNames[day]} · Jadwal Hari Ini` : 'Tidak ada jadwal belajar hari ini';
        if (previousDay && previousDay !== now.weekday) location.reload(); previousDay = now.weekday;
        const current = slots.find(slot => minutes >= toMinutes(slot.mulai) && minutes < toMinutes(slot.selesai));
        const next = slots.find(slot => minutes < toMinutes(slot.mulai));
        const nextLesson = slots.find(slot => slot.tipe === 'pelajaran' && minutes < toMinutes(slot.mulai));
        const focus = document.getElementById('monitorFocus'); const classes = document.getElementById('monitorClasses');
        if (current?.tipe !== 'pelajaran') {
            const total = Math.max(toMinutes(current.selesai) - toMinutes(current.mulai), 1); const progress = Math.min(100, Math.max(0, ((minutes - toMinutes(current.mulai)) / total) * 100));
            focus.classList.add('jm-focus--break');
            focus.innerHTML = `<div class="jm-focus-label"><i class="fas fa-coffee"></i> ${escape(current.label).toUpperCase()} · SEDANG BERLANGSUNG</div><div class="jm-focus-main">${current.mulai}–${current.selesai} WIB</div><div class="jm-focus-meta">${nextLesson ? `Berikutnya: Jam ke-${nextLesson.jam_ke}, pukul ${nextLesson.mulai}–${nextLesson.selesai} WIB.` : 'Tidak ada jam pelajaran berikutnya hari ini.'}</div><div class="jm-progress"><span style="width:${progress}%"></span></div>`;
            document.getElementById('monitorCount').textContent = '—'; classes.innerHTML = `<div class="jm-empty"><i class="fas fa-mug-hot text-warning"></i> ${escape(current.label)}. Silakan bersiap untuk pembelajaran berikutnya.</div>`;
        } else if (current) {
            focus.classList.remove('jm-focus--break');
            const total = Math.max(toMinutes(current.selesai) - toMinutes(current.mulai), 1); const progress = Math.min(100, Math.max(0, ((minutes - toMinutes(current.mulai)) / total) * 100));
            focus.innerHTML = `<div class="jm-focus-label"><i class="fas fa-circle text-success"></i> SEDANG BERLANGSUNG · JAM KE-${current.jam_ke}</div><div class="jm-focus-main">${current.mulai}–${current.selesai} WIB</div><div class="jm-focus-meta">${current.kelas.length} kelas sedang mengikuti pembelajaran.</div><div class="jm-progress"><span style="width:${progress}%"></span></div>`;
            document.getElementById('monitorCount').textContent = current.kelas.length;
            classes.innerHTML = current.kelas.length ? current.kelas.map(item => `<article class="jm-class jm-class--tone-${tone(item.mapel)}"><span class="jm-class__icon"><i class="fas ${subjectIcon(item.mapel)}"></i></span><div class="jm-class__kelas">${escape(item.kelas)}</div><div class="jm-class__mapel">${escape(item.mapel)}</div><div class="jm-class__guru"><i class="fas fa-user-tie"></i> ${escape(item.guru)}</div></article>`).join('') : '<div class="jm-empty">Belum ada kelas yang terisi pada jam ini.</div>';
        } else if (next) {
            focus.classList.remove('jm-focus--break');
            focus.innerHTML = `<div class="jm-focus-label"><i class="fas fa-clock text-info"></i> JADWAL BERIKUTNYA · JAM KE-${next.jam_ke}</div><div class="jm-focus-main">${next.mulai}–${next.selesai} WIB</div><div class="jm-focus-meta">Kegiatan belajar berikutnya dimulai pukul ${next.mulai}.</div>`;
            document.getElementById('monitorCount').textContent = 0; classes.innerHTML = '<div class="jm-empty">Belum ada pembelajaran yang sedang berlangsung.</div>';
        } else {
            focus.classList.remove('jm-focus--break');
            focus.innerHTML = '<div class="jm-focus-label"><i class="fas fa-moon text-secondary"></i> DI LUAR JAM PEMBELAJARAN</div><div class="jm-focus-main">Tidak ada sesi aktif saat ini</div><div class="jm-focus-meta">Monitor akan memperbarui status secara otomatis saat jam belajar berlangsung.</div>';
            document.getElementById('monitorCount').textContent = 0; classes.innerHTML = '<div class="jm-empty">Tidak ada pembelajaran yang sedang berlangsung.</div>';
        }
        document.getElementById('monitorTimeline').innerHTML = slots.map(slot => `<div class="jm-slot ${slot.tipe !== 'pelajaran' ? 'jm-slot--break' : ''} ${current === slot ? 'jm-slot--current' : next === slot ? 'jm-slot--next' : ''}"><strong>${slot.tipe === 'pelajaran' ? `Jam ${slot.jam_ke}` : `<i class="fas fa-coffee"></i> ${escape(slot.label)}`}</strong><small>${slot.mulai}–${slot.selesai}</small><small>${slot.tipe === 'pelajaran' ? `${slot.kelas.length} kelas` : 'Waktu jeda'}</small></div>`).join('') || '<span class="text-muted small p-2">Slot jam belum dikonfigurasi untuk hari ini.</span>';
    }
    document.getElementById('monitorFullscreen').addEventListener('click', () => document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen());
    document.addEventListener('fullscreenchange', () => document.body.classList.toggle('monitor-mode', Boolean(document.fullscreenElement)));
    render(); setInterval(render, 1000);
})();
</script>
@stop
