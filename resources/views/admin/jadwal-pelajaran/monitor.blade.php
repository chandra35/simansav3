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
.jadwal-monitor__hero{background:linear-gradient(115deg,#1d4ed8,#2563eb 50%,#0f766e);color:#fff;border-radius:22px;padding:1.45rem 1.7rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;box-shadow:0 14px 32px rgba(37,99,235,.2)}
.jadwal-monitor__eyebrow{font-size:.76rem;font-weight:800;letter-spacing:.08em}.jadwal-monitor__pulse{display:inline-block;width:9px;height:9px;background:#86efac;border-radius:50%;margin-right:.35rem;box-shadow:0 0 0 5px rgba(134,239,172,.2)}
.jadwal-monitor h2{margin:.35rem 0;font-weight:800;font-size:1.65rem}.jadwal-monitor__hero p{margin:0;opacity:.9}.jadwal-monitor__clock{min-width:175px;text-align:right}.jadwal-monitor__clock strong{display:block;font-size:2.1rem;line-height:1;font-variant-numeric:tabular-nums}.jadwal-monitor__clock span{font-size:.77rem;opacity:.84}
.jadwal-monitor__focus{margin:1rem 0;background:#fff;border:1px solid #dbe7ff;border-left:5px solid var(--jm-blue);border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.06);padding:1rem 1.2rem}.jm-focus-label{font-size:.73rem;font-weight:800;letter-spacing:.06em;color:#64748b}.jm-focus-main{font-size:1.22rem;font-weight:800;margin:.16rem 0}.jm-focus-meta{color:#64748b;font-size:.9rem}.jm-progress{height:8px;border-radius:999px;background:#e8eef8;margin-top:.75rem;overflow:hidden}.jm-progress span{display:block;height:100%;background:linear-gradient(90deg,#2563eb,#14b8a6);border-radius:999px}
.jadwal-monitor__section,.jadwal-monitor__timeline{background:#fff;border:1px solid #dbe5f2;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.05)}.jadwal-monitor__section{padding:1rem 1.1rem}.jadwal-monitor__section-title{font-weight:800;margin-bottom:.85rem}.jadwal-monitor__classes{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.65rem}.jm-class{border:1px solid #e2e8f0;border-radius:12px;padding:.7rem .8rem;background:#fff}.jm-class__kelas{font-size:.76rem;font-weight:800;color:#2563eb}.jm-class__mapel{font-weight:800;margin:.12rem 0}.jm-class__guru{font-size:.8rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.jm-empty{color:#64748b;padding:.6rem 0}
.jadwal-monitor__timeline{display:flex;gap:.55rem;overflow-x:auto;padding:.8rem;margin-top:1rem}.jm-slot{min-width:125px;border:1px solid #e2e8f0;border-radius:11px;padding:.55rem .65rem;color:#64748b}.jm-slot--current{border-color:#2563eb;background:#eff6ff;color:#1d4ed8;box-shadow:0 0 0 2px rgba(37,99,235,.1)}.jm-slot--next{border-color:#14b8a6;background:#f0fdfa}.jm-slot strong{display:block;color:#10213d;font-size:.88rem}.jm-slot small{display:block;font-size:.74rem}
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
    let previousDay = null;
    function render() {
        const now = timeParts(); const minutes = Number(now.hour) * 60 + Number(now.minute);
        document.getElementById('monitorClock').textContent = `${now.hour}:${now.minute}:${now.second}`;
        document.getElementById('monitorDay').textContent = day ? `${dayNames[day]} · Jadwal Hari Ini` : 'Tidak ada jadwal belajar hari ini';
        if (previousDay && previousDay !== now.weekday) location.reload(); previousDay = now.weekday;
        const current = slots.find(slot => minutes >= toMinutes(slot.mulai) && minutes < toMinutes(slot.selesai));
        const next = slots.find(slot => minutes < toMinutes(slot.mulai));
        const focus = document.getElementById('monitorFocus'); const classes = document.getElementById('monitorClasses');
        if (current) {
            const total = Math.max(toMinutes(current.selesai) - toMinutes(current.mulai), 1); const progress = Math.min(100, Math.max(0, ((minutes - toMinutes(current.mulai)) / total) * 100));
            focus.innerHTML = `<div class="jm-focus-label"><i class="fas fa-circle text-success"></i> SEDANG BERLANGSUNG · JAM KE-${current.jam_ke}</div><div class="jm-focus-main">${current.mulai}–${current.selesai} WIB</div><div class="jm-focus-meta">${current.kelas.length} kelas sedang mengikuti pembelajaran.</div><div class="jm-progress"><span style="width:${progress}%"></span></div>`;
            document.getElementById('monitorCount').textContent = current.kelas.length;
            classes.innerHTML = current.kelas.length ? current.kelas.map(item => `<article class="jm-class"><div class="jm-class__kelas">${escape(item.kelas)}</div><div class="jm-class__mapel">${escape(item.mapel)}</div><div class="jm-class__guru"><i class="fas fa-user-tie"></i> ${escape(item.guru)}</div></article>`).join('') : '<div class="jm-empty">Belum ada kelas yang terisi pada jam ini.</div>';
        } else if (next) {
            focus.innerHTML = `<div class="jm-focus-label"><i class="fas fa-clock text-info"></i> JADWAL BERIKUTNYA · JAM KE-${next.jam_ke}</div><div class="jm-focus-main">${next.mulai}–${next.selesai} WIB</div><div class="jm-focus-meta">Kegiatan belajar berikutnya dimulai pukul ${next.mulai}.</div>`;
            document.getElementById('monitorCount').textContent = 0; classes.innerHTML = '<div class="jm-empty">Belum ada pembelajaran yang sedang berlangsung.</div>';
        } else {
            focus.innerHTML = '<div class="jm-focus-label"><i class="fas fa-moon text-secondary"></i> DI LUAR JAM PEMBELAJARAN</div><div class="jm-focus-main">Tidak ada sesi aktif saat ini</div><div class="jm-focus-meta">Monitor akan memperbarui status secara otomatis saat jam belajar berlangsung.</div>';
            document.getElementById('monitorCount').textContent = 0; classes.innerHTML = '<div class="jm-empty">Tidak ada pembelajaran yang sedang berlangsung.</div>';
        }
        document.getElementById('monitorTimeline').innerHTML = slots.map(slot => `<div class="jm-slot ${current?.jam_ke === slot.jam_ke ? 'jm-slot--current' : next?.jam_ke === slot.jam_ke ? 'jm-slot--next' : ''}"><strong>Jam ${slot.jam_ke}</strong><small>${slot.mulai}–${slot.selesai}</small><small>${slot.kelas.length} kelas</small></div>`).join('') || '<span class="text-muted small p-2">Slot jam belum dikonfigurasi untuk hari ini.</span>';
    }
    document.getElementById('monitorFullscreen').addEventListener('click', () => document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen());
    document.addEventListener('fullscreenchange', () => document.body.classList.toggle('monitor-mode', Boolean(document.fullscreenElement)));
    render(); setInterval(render, 1000);
})();
</script>
@stop
