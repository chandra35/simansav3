<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#123787">
    <title>Monitor Jadwal Belajar | SIMANSA</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root{--blue:#2563eb;--teal:#0f766e;--ink:#10213d;--muted:#64748b;--line:#dbe5f2}*{box-sizing:border-box}html,body{min-height:100%;margin:0}body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink);background:#eef5ff}.piket-monitor{max-width:1920px;min-height:100dvh;margin:auto;padding:clamp(10px,1.4vw,24px);background:radial-gradient(circle at 92% 0,rgba(45,212,191,.16),transparent 24%),radial-gradient(circle at 0 40%,rgba(59,130,246,.14),transparent 22%)}
        .piket-top{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:clamp(13px,1.3vw,22px);border-radius:20px;background:linear-gradient(115deg,#1d4ed8,#2563eb 50%,#0f766e);color:#fff;box-shadow:0 16px 36px rgba(37,99,235,.24)}.piket-brand{display:flex;align-items:center;gap:11px}.piket-brand img{width:42px;height:42px;padding:4px;border-radius:12px;background:#fff;object-fit:contain}.piket-brand small,.piket-brand strong{display:block}.piket-brand small{font-size:10px;font-weight:800;letter-spacing:.1em}.piket-brand strong{margin-top:2px;font-size:clamp(17px,1.6vw,27px);line-height:1.05}.piket-actions{display:flex;align-items:center;gap:9px}.piket-clock{text-align:right}.piket-clock strong{display:block;font-size:clamp(23px,2.4vw,42px);line-height:.92;font-variant-numeric:tabular-nums}.piket-clock span{font-size:10px;opacity:.86}.piket-fullscreen,.piket-now{height:42px;border:1px solid rgba(255,255,255,.34);border-radius:11px;background:rgba(255,255,255,.13);color:#fff;cursor:pointer;font-size:13px}.piket-fullscreen{width:42px;font-size:16px}.piket-now{display:inline-flex;align-items:center;gap:7px;padding:0 13px;font-weight:800}.piket-fullscreen:hover,.piket-now:hover:not(:disabled){background:rgba(255,255,255,.25)}.piket-now:disabled{cursor:default;opacity:.5}
        .piket-focus{margin:14px 0;padding:clamp(13px,1.2vw,21px);border:1px solid #bfd5ff;border-left:7px solid var(--blue);border-radius:17px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.06)}.piket-focus.is-break{border-color:#fed7aa;border-left-color:#f59e0b;background:#fffaf0}.piket-focus-label{font-size:11px;font-weight:900;letter-spacing:.08em;color:var(--blue)}.is-break .piket-focus-label{color:#b45309}.piket-focus-main{margin:3px 0;font-size:clamp(20px,2vw,32px);font-weight:900}.piket-focus-meta{font-size:clamp(12px,1.1vw,16px);color:var(--muted)}.piket-progress{height:8px;margin-top:12px;overflow:hidden;border-radius:99px;background:#dbeafe}.is-break .piket-progress{background:#ffedd5}.piket-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--blue),#14b8a6)}.is-break .piket-progress span{background:linear-gradient(90deg,#f59e0b,#f97316)}
        .piket-section{padding:clamp(12px,1.2vw,20px);border:1px solid var(--line);border-radius:17px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.05)}.piket-section-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;font-size:clamp(13px,1.1vw,17px);font-weight:900}.piket-count{min-width:28px;padding:3px 8px;border-radius:99px;background:#dbeafe;color:#1d4ed8;text-align:center;font-size:12px}.piket-classes{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:9px}.piket-class{display:grid;width:100%;grid-template-columns:26px minmax(0,1fr) 54px;align-items:center;gap:8px;min-height:82px;overflow:hidden;padding:9px 10px;border:1px solid #e2e8f0;border-radius:12px;background:linear-gradient(120deg,#fff,#eff6ff);color:inherit;font:inherit;text-align:left;cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s}.piket-class:hover{transform:translateY(-2px);border-color:#93c5fd;box-shadow:0 7px 16px rgba(15,23,42,.1)}.piket-class:focus-visible{outline:3px solid rgba(37,99,235,.3);outline-offset:2px}.piket-class>i{align-self:start;margin-top:3px;color:#2563eb}.piket-class__content{min-width:0}.piket-class:nth-child(6n+2){background:linear-gradient(120deg,#fff,#ecfdf5)}.piket-class:nth-child(6n+2)>i,.piket-class:nth-child(6n+2) .piket-kelas{color:#059669}.piket-class:nth-child(6n+3){background:linear-gradient(120deg,#fff,#f5f3ff)}.piket-class:nth-child(6n+3)>i,.piket-class:nth-child(6n+3) .piket-kelas{color:#7c3aed}.piket-class:nth-child(6n+4){background:linear-gradient(120deg,#fff,#fff7ed)}.piket-class:nth-child(6n+4)>i,.piket-class:nth-child(6n+4) .piket-kelas{color:#ea580c}.piket-kelas{font-size:11px;font-weight:900;color:#2563eb}.piket-mapel{display:-webkit-box;overflow:hidden;margin:2px 0;font-size:14px;font-weight:900;line-height:1.18;-webkit-box-orient:vertical;-webkit-line-clamp:2}.piket-guru{overflow:hidden;color:var(--muted);font-size:11px;text-overflow:ellipsis;white-space:nowrap}.piket-guru i{margin-right:3px}.piket-guru-photo{width:54px;height:68px;justify-self:end;overflow:hidden;border:2px solid #fff;border-radius:10px;object-fit:cover;box-shadow:0 4px 10px rgba(15,23,42,.16)}.piket-empty{padding:10px 0;color:var(--muted);font-size:14px}.piket-detail-modal{width:min(92vw,520px);padding:0;border:0;border-radius:18px;color:var(--ink);box-shadow:0 24px 65px rgba(15,23,42,.3)}.piket-detail-modal::backdrop{background:rgba(15,23,42,.55);backdrop-filter:blur(3px)}.piket-detail-modal__body{display:grid;grid-template-columns:130px minmax(0,1fr);gap:1rem;padding:1.25rem}.piket-detail-modal__photo{width:130px;aspect-ratio:4/5;overflow:hidden;border-radius:14px;background:#dbeafe;box-shadow:0 8px 18px rgba(37,99,235,.17)}.piket-detail-modal__photo img{width:100%;height:100%;object-fit:cover}.piket-detail-modal__eyebrow{margin-bottom:.35rem;color:#2563eb;font-size:.7rem;font-weight:900;letter-spacing:.08em}.piket-detail-modal__body h2{margin:0 0 .8rem;font-size:1.25rem}.piket-detail-modal__item{padding:.55rem 0;border-top:1px solid #e2e8f0;color:#475569;font-size:.85rem}.piket-detail-modal__item strong{display:block;color:#10213d;font-size:.95rem}.piket-detail-modal__footer{display:flex;justify-content:flex-end;padding:.75rem 1.25rem;border-top:1px solid #e2e8f0;background:#f8fafc}.piket-detail-modal__close{border:0;border-radius:8px;background:#2563eb;color:#fff;padding:.5rem .8rem;font:inherit;font-weight:800;cursor:pointer}@media(max-width:440px){.piket-detail-modal__body{grid-template-columns:92px minmax(0,1fr);padding:1rem}.piket-detail-modal__photo{width:92px}.piket-detail-modal__body h2{font-size:1.05rem}}
        @media(max-width:600px){.piket-monitor{padding:10px}.piket-top{align-items:flex-start}.piket-brand img{display:none}.piket-brand small{font-size:8px}.piket-brand strong{font-size:16px}.piket-clock strong{font-size:21px}.piket-fullscreen{display:none}.piket-now{width:42px;padding:0;justify-content:center}.piket-now span{display:none}.piket-classes{grid-template-columns:1fr}.piket-focus{margin:10px 0}.piket-timeline{margin-top:10px}}
    </style>
</head>
<body>
<main class="piket-monitor">
    <header class="piket-top">
        <div class="piket-brand"><img src="{{ asset('favicon.ico') }}" alt="Logo SIMANSA"><div><small><i class="fas fa-circle"></i> MONITOR GURU PIKET</small><strong id="monitorDay">Memuat jadwal…</strong></div></div>
        <div class="piket-actions"><div class="piket-clock"><strong id="monitorClock">--:--:--</strong><span>{{ $tahun?->nama ?? 'Tahun pelajaran belum aktif' }} · Semester {{ $semester }} · WIB</span></div><button type="button" class="piket-now" id="currentScheduleButton" title="Kembali ke jadwal saat ini" disabled><i class="fas fa-broadcast-tower"></i><span>Jadwal Saat Ini</span></button><button type="button" class="piket-fullscreen" id="fullscreenButton" title="Layar penuh"><i class="fas fa-expand"></i></button></div>
    </header>
    <section class="piket-focus" id="monitorFocus" aria-live="polite"></section>
    <section class="piket-section"><div class="piket-section-head"><span id="monitorSectionTitle"><i class="fas fa-chalkboard-teacher text-primary"></i> Kegiatan kelas saat ini</span><span class="piket-count" id="monitorCount">0</span></div><div class="piket-classes" id="monitorClasses"></div></section>
    <section class="piket-timeline" id="monitorTimeline" aria-label="Urutan jam pelajaran hari ini"></section>
    <footer class="piket-footer">SIMANSA · MONITOR PEMBELAJARAN LANGSUNG · Halaman ini diperbarui otomatis</footer>
</main>
<dialog class="piket-detail-modal" id="scheduleDetailModal" aria-labelledby="scheduleDetailTitle"><div class="piket-detail-modal__body"><div class="piket-detail-modal__photo"><img id="scheduleDetailPhoto" alt="Foto GTK"></div><div><div class="piket-detail-modal__eyebrow"><i class="fas fa-chalkboard-teacher"></i> DETAIL PEMBELAJARAN</div><h2 id="scheduleDetailTitle">-</h2><div class="piket-detail-modal__item">Kelas<strong id="scheduleDetailClass">-</strong></div><div class="piket-detail-modal__item">Mata pelajaran<strong id="scheduleDetailSubject">-</strong></div><div class="piket-detail-modal__item">Guru pengajar<strong id="scheduleDetailTeacher">-</strong></div></div></div><div class="piket-detail-modal__footer"><button type="button" class="piket-detail-modal__close" id="scheduleDetailClose"><i class="fas fa-times mr-1"></i>Tutup</button></div></dialog>
<script>
(() => {
    const slots = @json($slots);
    const day = @json($hari);
    const days = {senin:'Senin',selasa:'Selasa',rabu:'Rabu',kamis:'Kamis',jumat:'Jumat',sabtu:'Sabtu'};
    const timeline = document.getElementById('monitorTimeline');
    const currentButton = document.getElementById('currentScheduleButton');
    const classes = document.getElementById('monitorClasses');
    const detailModal = document.getElementById('scheduleDetailModal');
    const toMinutes = value => { const [hour, minute] = value.split(':').map(Number); return hour * 60 + minute; };
    const escape = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
    const nowParts = () => Object.fromEntries(new Intl.DateTimeFormat('en-GB', {timeZone:'Asia/Jakarta',hour:'2-digit',minute:'2-digit',second:'2-digit',weekday:'long',hourCycle:'h23'}).formatToParts(new Date()).filter(part => part.type !== 'literal').map(part => [part.type, part.value]));
    const icon = mapel => { const name = String(mapel).toLowerCase(); return name.includes('matematika') || name.includes('fisika') || name.includes('kimia') ? 'fa-square-root-alt' : name.includes('bahasa') || name.includes('sejarah') ? 'fa-book-open' : name.includes('qur') || name.includes('fiq') || name.includes('tahfidz') ? 'fa-mosque' : name.includes('informatika') ? 'fa-laptop-code' : 'fa-lightbulb'; };
    let priorDay = null;
    let selectedIndex = null;

    function render() {
        const now = nowParts();
        const minutes = Number(now.hour) * 60 + Number(now.minute);
        const liveCurrent = slots.find(slot => minutes >= toMinutes(slot.mulai) && minutes < toMinutes(slot.selesai));
        const selected = selectedIndex === null ? null : slots[selectedIndex];
        const displayed = selected ?? liveCurrent;
        const referenceMinutes = selected ? toMinutes(selected.selesai) : minutes;
        const next = slots.find(slot => referenceMinutes < toMinutes(slot.mulai));
        const nextLesson = slots.find(slot => slot.tipe === 'pelajaran' && referenceMinutes <= toMinutes(slot.mulai));
        const focus = document.getElementById('monitorFocus');
        const manualMode = Boolean(selected);

        document.getElementById('monitorClock').textContent = `${now.hour}:${now.minute}:${now.second}`;
        document.getElementById('monitorDay').textContent = day ? `${days[day]} · Jadwal Hari Ini` : 'Tidak ada jadwal belajar hari ini';
        document.getElementById('monitorSectionTitle').innerHTML = `<i class="fas fa-chalkboard-teacher text-primary"></i> ${manualMode ? 'Kegiatan kelas pada jadwal dipilih' : 'Kegiatan kelas saat ini'}`;
        currentButton.disabled = !manualMode;
        if (priorDay && priorDay !== now.weekday) location.reload();
        priorDay = now.weekday;

        if (displayed) {
            const breakTime = displayed.tipe !== 'pelajaran';
            const total = Math.max(toMinutes(displayed.selesai) - toMinutes(displayed.mulai), 1);
            const progress = manualMode ? 100 : Math.min(100, Math.max(0, (minutes - toMinutes(displayed.mulai)) * 100 / total));
            const statusLabel = manualMode ? 'JADWAL DIPILIH' : 'SEDANG BERLANGSUNG';
            focus.classList.toggle('is-break', breakTime);
            focus.innerHTML = `<div class="piket-focus-label"><i class="fas ${manualMode ? 'fa-hand-pointer' : (breakTime ? 'fa-coffee' : 'fa-circle')}"></i> ${breakTime ? `${escape(displayed.label).toUpperCase()} · ${statusLabel}` : `${statusLabel} · JAM KE-${displayed.jam_ke}`}</div><div class="piket-focus-main">${displayed.mulai}–${displayed.selesai} WIB</div><div class="piket-focus-meta">${breakTime ? (nextLesson ? `Berikutnya: Jam ke-${nextLesson.jam_ke}, mulai ${nextLesson.mulai} WIB.` : 'Tidak ada pembelajaran berikutnya hari ini.') : `${displayed.kelas.length} kelas memiliki jadwal pembelajaran pada jam ini.`}</div>${manualMode ? '' : `<div class="piket-progress"><span style="width:${progress}%"></span></div>`}`;
            const data = breakTime ? [] : displayed.kelas;
            document.getElementById('monitorCount').textContent = breakTime ? '—' : data.length;
            classes.innerHTML = data.length ? data.map(item => `<button type="button" class="piket-class" data-schedule-detail data-kelas="${escape(item.kelas)}" data-mapel="${escape(item.mapel)}" data-guru="${escape(item.guru)}" data-foto="${escape(item.foto_guru || '')}" aria-label="Lihat detail jadwal ${escape(item.kelas)}, ${escape(item.mapel)}, guru ${escape(item.guru)}"><i class="fas ${icon(item.mapel)}"></i><div class="piket-class__content"><div class="piket-kelas">${escape(item.kelas)}</div><div class="piket-mapel">${escape(item.mapel)}</div><div class="piket-guru"><i class="fas fa-user-tie"></i>${escape(item.guru)}</div></div>${item.foto_guru ? `<img class="piket-guru-photo" src="${escape(item.foto_guru)}" alt="" onerror="this.remove()">` : '<span></span>'}</button>`).join('') : `<div class="piket-empty">${breakTime ? `${escape(displayed.label)} merupakan kegiatan khusus tanpa jadwal kelas.` : 'Jadwal masih kosong pada jam ini.'}</div>`;
        } else {
            focus.classList.remove('is-break');
            focus.innerHTML = next ? `<div class="piket-focus-label"><i class="fas fa-clock"></i> JADWAL BERIKUTNYA</div><div class="piket-focus-main">${next.mulai}–${next.selesai} WIB</div><div class="piket-focus-meta">${next.tipe === 'pelajaran' ? `Jam ke-${next.jam_ke}` : escape(next.label)} akan dimulai pukul ${next.mulai} WIB.</div>` : '<div class="piket-focus-label"><i class="fas fa-moon"></i> DI LUAR JAM PEMBELAJARAN</div><div class="piket-focus-main">Tidak ada sesi aktif saat ini</div><div class="piket-focus-meta">Monitor akan aktif kembali pada jam belajar berikutnya.</div>';
            document.getElementById('monitorCount').textContent = 0;
            classes.innerHTML = '<div class="piket-empty">Belum ada pembelajaran yang sedang berlangsung.</div>';
        }

        timeline.innerHTML = slots.map((slot, index) => `<button type="button" class="piket-slot ${slot.tipe !== 'pelajaran' ? 'break' : ''} ${manualMode && index === selectedIndex ? 'selected' : (!manualMode && liveCurrent === slot ? 'current' : '')}" data-slot-index="${index}" aria-pressed="${manualMode && index === selectedIndex}"><strong>${slot.tipe === 'pelajaran' ? `Jam ${slot.jam_ke}` : escape(slot.label)}</strong><small>${slot.mulai}–${slot.selesai}</small><small>${slot.tipe === 'pelajaran' ? `${slot.kelas.length} kelas` : 'Kegiatan khusus'}</small></button>`).join('') || '<span class="piket-empty">Slot jam belum dikonfigurasi untuk hari ini.</span>';
    }

    timeline.addEventListener('click', event => {
        const slotButton = event.target.closest('[data-slot-index]');
        if (!slotButton) return;
        selectedIndex = Number(slotButton.dataset.slotIndex);
        render();
        document.getElementById('monitorFocus').scrollIntoView({behavior:'smooth', block:'start'});
    });
    classes.addEventListener('click', event => {
        const card = event.target.closest('[data-schedule-detail]');
        if (!card) return;
        document.getElementById('scheduleDetailTitle').textContent = card.dataset.mapel;
        document.getElementById('scheduleDetailClass').textContent = card.dataset.kelas;
        document.getElementById('scheduleDetailSubject').textContent = card.dataset.mapel;
        document.getElementById('scheduleDetailTeacher').textContent = card.dataset.guru;
        const photo = document.getElementById('scheduleDetailPhoto');
        photo.src = card.dataset.foto;
        photo.alt = `Foto ${card.dataset.guru}`;
        detailModal.showModal();
    });
    document.getElementById('scheduleDetailClose').addEventListener('click', () => detailModal.close());
    detailModal.addEventListener('click', event => { if (event.target === detailModal) detailModal.close(); });
    currentButton.addEventListener('click', () => {
        selectedIndex = null;
        render();
        document.querySelector('.piket-slot.current')?.scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
    });
    document.getElementById('fullscreenButton').addEventListener('click', () => document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen());
    render();
    setInterval(render, 1000);
    setInterval(() => { if (selectedIndex === null) location.reload(); }, 300000);
})();
</script>
</body>
</html>
