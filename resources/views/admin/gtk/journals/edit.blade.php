@extends('adminlte::page')
@section('title', 'Edit Jurnal Guru')
@section('content_header') @stop
@section('content')
<div class="edit-hero"><div><span><i class="fas fa-pen-nib mr-1"></i> PERBARUI DOKUMENTASI</span><h1>Edit jurnal kegiatan</h1><p>Pastikan rincian aktivitas tetap akurat untuk rekam kerja dan laporan EKIN/SKP.</p></div><a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali ke jurnal</a></div>
<form method="POST" action="{{ route('admin.gtk.journals.update', $journal) }}">@csrf @method('PUT')<div class="row"><div class="col-lg-8"><div class="card edit-card shadow-sm border-0"><div class="card-body p-4"><section><div class="section-title"><i class="fas fa-id-card"></i><div><h5>Identitas jurnal</h5><p>Atur jenis, waktu, dan konteks kegiatan.</p></div></div><div class="row"><div class="col-md-6 form-group"><label>Jenis jurnal</label><select name="jenis" id="journal-type" class="custom-select" required><option value="mengajar" @selected(old('jenis',$journal->jenis)==='mengajar')>Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas" @selected(old('jenis',$journal->jenis)==='wali_kelas')>Jurnal wali kelas</option>@endif</select></div><div class="col-md-6 form-group"><label>Tanggal kegiatan</label><input type="date" name="tanggal" id="journal-date" value="{{ old('tanggal',$journal->tanggal->toDateString()) }}" class="form-control" required></div></div><div id="teaching-fields" class="form-group"><label>Jadwal mengajar</label><select name="jadwal_pelajaran_id" id="journal-schedule" class="custom-select"><option value="">Tidak memilih jadwal</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}" @selected(old('jadwal_pelajaran_id',$journal->jadwal_pelajaran_id)===$schedule->id)>{{ $schedule->mataPelajaran?->nama_mapel }} · {{ $schedule->kelas?->nama_kelas }} · {{ $schedule->hari_label }} jam {{ $schedule->jam_ke }}</option>@endforeach</select></div>@if($waliClasses->isNotEmpty())<div id="homeroom-fields" class="form-group"><label>Kelas wali</label><select name="kelas_id" id="journal-homeroom" class="custom-select"><option value="">Pilih kelas wali</option>@foreach($waliClasses as $kelas)<option value="{{ $kelas->id }}" @selected(old('kelas_id',$journal->kelas_id)===$kelas->id)>{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</option>@endforeach</select></div>@endif</section>

<section>
    <div class="section-title">
        <i class="fas fa-user-check"></i>
        <div>
            <h5>Presensi Kehadiran Siswa</h5>
            <p>Sinkron dengan absensi harian dan presensi sesi mata pelajaran ini.</p>
        </div>
    </div>
    
    <div id="attendance-section" class="attendance-card p-3 rounded border bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="font-weight-bold text-dark" id="att-summary-title">Daftar Kehadiran Siswa</span>
                <div class="small text-muted" id="att-subtitle">Memuat data presensi...</div>
            </div>
            <div id="att-header-actions" class="d-none">
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill font-weight-bold shadow-none py-1 px-3" id="btn-all-present" style="font-size:0.75rem;">
                    <i class="fas fa-check-double mr-1"></i> Semua Hadir
                </button>
            </div>
        </div>
        
        <div id="att-source-alert" class="alert alert-info py-1 px-2 small mb-2 d-none">
            <i class="fas fa-info-circle mr-1"></i> <span id="att-source-text"></span>
        </div>

        <div id="att-stats-pills" class="d-none flex-wrap align-items-center mb-2">
            <span class="att-stat-chip stat-h"><span class="att-stat-dot"></span> Hadir: <strong id="stat-hadir" class="ml-1">0</strong></span>
            <span class="att-stat-chip stat-s"><span class="att-stat-dot"></span> Sakit: <strong id="stat-sakit" class="ml-1">0</strong></span>
            <span class="att-stat-chip stat-i"><span class="att-stat-dot"></span> Izin: <strong id="stat-izin" class="ml-1">0</strong></span>
            <span class="att-stat-chip stat-a"><span class="att-stat-dot"></span> Alpa: <strong id="stat-alpa" class="ml-1">0</strong></span>
            <span class="att-stat-chip stat-d"><span class="att-stat-dot"></span> Dispen: <strong id="stat-dispen" class="ml-1">0</strong></span>
        </div>

        <div id="att-loading" class="text-center py-3 d-none">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="small text-muted ml-2">Menghubungkan presensi siswa...</span>
        </div>

        <div id="att-students-list" style="max-height: 290px; overflow-y: auto;" class="pr-1">
            <div class="text-muted small text-center py-3" id="att-placeholder">
                <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>
                Memuat data presensi siswa...
            </div>
        </div>
    </div>
</section>

<section><div class="section-title"><i class="fas fa-clipboard-list"></i><div><h5>Agenda dan pelaksanaan</h5><p>Jelaskan inti kegiatan yang sudah dilaksanakan.</p></div></div><div class="form-group"><label>Topik / agenda</label><input name="topik" maxlength="255" value="{{ old('topik',$journal->topik) }}" class="form-control" required></div><div class="form-group mb-0"><label>Kegiatan pembelajaran / tugas</label><textarea name="kegiatan" rows="6" class="form-control" required>{{ old('kegiatan',$journal->kegiatan) }}</textarea></div></section>
<section class="mb-0"><div class="section-title"><i class="fas fa-chart-line"></i><div><h5>Evaluasi dan tindak lanjut</h5><p>Tambahkan hasil, rencana berikutnya, atau informasi pelengkap.</p></div></div><div class="row"><div class="col-md-8 form-group"><label>Asesmen / tindak lanjut</label><textarea name="asesmen_tindak_lanjut" rows="3" class="form-control">{{ old('asesmen_tindak_lanjut',$journal->asesmen_tindak_lanjut) }}</textarea></div><div class="col-md-4 form-group"><label>Durasi (menit)</label><input type="number" min="1" max="720" name="durasi_menit" value="{{ old('durasi_menit',$journal->durasi_menit) }}" class="form-control"></div></div><div class="form-group mb-0"><label>Catatan tambahan <span class="text-muted font-weight-normal">(opsional)</span></label><textarea name="catatan" rows="3" class="form-control">{{ old('catatan',$journal->catatan) }}</textarea></div></section></div></div></div><div class="col-lg-4"><div class="card save-card border-0 shadow-sm"><div class="card-body p-4"><div class="save-icon"><i class="fas fa-shield-alt"></i></div><h4>Simpan pembaruan</h4><p>Perubahan akan tersimpan pada jurnal pribadi dan digunakan dalam ekspor laporan.</p><button class="btn btn-primary btn-lg btn-block"><i class="fas fa-save mr-1"></i> Simpan perubahan</button><a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light btn-block mt-2">Batalkan</a></div></div><div class="info-note"><i class="fas fa-info-circle"></i><span>Hanya jurnal milik sendiri yang dapat diperbarui.</span></div></div></div></form>
@stop
@section('css')<style>
.edit-hero{background:linear-gradient(125deg,#173c79,#2871c5);color:#fff;border-radius:16px;padding:1.8rem 2rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem}.edit-hero span{font-size:.75rem;letter-spacing:.08em;font-weight:700;opacity:.85}.edit-hero h1{font-size:1.65rem;font-weight:700;margin:.35rem 0}.edit-hero p{margin:0;opacity:.85}.edit-card,.save-card{border-radius:14px}.edit-card section{padding-bottom:1.6rem;margin-bottom:1.6rem;border-bottom:1px solid #eef1f5}.edit-card section:last-child{border:0}.section-title{display:flex;gap:.75rem;align-items:flex-start;margin-bottom:1.15rem}.section-title>i{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;background:#eaf3ff;color:#1d6fca}.section-title h5{margin:0;color:#34445e;font-weight:700}.section-title p{margin:.15rem 0 0;font-size:.8rem;color:#8792a2}.edit-card label{font-size:.82rem;font-weight:700;color:#4d596c}.save-card{text-align:center}.save-icon{width:58px;height:58px;margin:0 auto 1rem;border-radius:50%;background:#eaf3ff;color:#2672c9;display:grid;place-items:center;font-size:1.45rem}.save-card h4{color:#35445c}.save-card p{color:#7c8899;font-size:.85rem;line-height:1.6}.info-note{display:flex;gap:.6rem;color:#718096;font-size:.8rem;padding:1rem}.info-note i{color:#2c7dd1;margin-top:.15rem}
.attendance-card{background:#f8fafc;border-color:#e2e8f0!important;border-radius:14px}
.student-att-row{transition:all .18s cubic-bezier(.4,0,.2,1);border:1px solid #edf2f7!important;border-radius:10px!important;background:#fff;margin-bottom:6px;padding:8px 12px;box-shadow:0 1px 2px rgba(0,0,0,.02)}
.student-att-row:hover{background:#f8fafc!important;border-color:#cbd5e1!important;box-shadow:0 2px 6px rgba(0,0,0,.04)}
.student-att-num{width:24px;height:24px;min-width:24px;border-radius:6px;background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;margin-right:8px}
.att-badge-prefilled{font-size:.67rem;background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 6px;display:inline-flex;align-items:center}
.att-status-group{display:inline-flex;align-items:center;gap:3px;background:#f1f5f9;padding:3px;border-radius:30px;border:1px solid #e2e8f0}
.att-status-btn{width:28px;height:28px;min-width:28px;padding:0!important;margin:0!important;display:inline-flex;align-items:center;justify-content:center;border-radius:50%!important;font-size:.76rem;font-weight:800;line-height:1;border:none!important;background:transparent;color:#64748b;transition:all .16s cubic-bezier(.4,0,.2,1);cursor:pointer;box-shadow:none!important;position:relative;user-select:none}
.att-status-btn input[type="radio"]{position:absolute;opacity:0;width:0;height:0;pointer-events:none;margin:0}
.att-status-btn:hover{transform:translateY(-1px)}
.att-status-btn.att-h:not(.active):hover{background:#dcfce7;color:#15803d}
.att-status-btn.att-s:not(.active):hover{background:#fef3c7;color:#b45309}
.att-status-btn.att-i:not(.active):hover{background:#e0f2fe;color:#0369a1}
.att-status-btn.att-a:not(.active):hover{background:#fee2e2;color:#b91c1c}
.att-status-btn.att-d:not(.active):hover{background:#ede9fe;color:#6d28d9}
.att-status-btn.att-h.active{background:linear-gradient(135deg,#10b981,#059669)!important;color:#fff!important;box-shadow:0 2px 7px rgba(16,185,129,.45)!important;transform:scale(1.08)}
.att-status-btn.att-s.active{background:linear-gradient(135deg,#f59e0b,#d97706)!important;color:#fff!important;box-shadow:0 2px 7px rgba(245,158,11,.45)!important;transform:scale(1.08)}
.att-status-btn.att-i.active{background:linear-gradient(135deg,#0ea5e9,#0284c7)!important;color:#fff!important;box-shadow:0 2px 7px rgba(14,165,233,.45)!important;transform:scale(1.08)}
.att-status-btn.att-a.active{background:linear-gradient(135deg,#ef4444,#dc2626)!important;color:#fff!important;box-shadow:0 2px 7px rgba(239,68,68,.45)!important;transform:scale(1.08)}
.att-status-btn.att-d.active{background:linear-gradient(135deg,#8b5cf6,#6d28d9)!important;color:#fff!important;box-shadow:0 2px 7px rgba(139,92,246,.45)!important;transform:scale(1.08)}
.att-stat-chip{font-size:.74rem;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;margin-right:5px;margin-bottom:4px;border:1px solid transparent}
.att-stat-chip.stat-h{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
.att-stat-chip.stat-s{background:#fffbeb;color:#92400e;border-color:#fde68a}
.att-stat-chip.stat-i{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.att-stat-chip.stat-a{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.att-stat-chip.stat-d{background:#f5f3ff;color:#5b21b6;border-color:#ddd6fe}
.att-stat-dot{width:6px;height:6px;border-radius:50%;display:inline-block}
.stat-h .att-stat-dot{background:#10b981}
.stat-s .att-stat-dot{background:#f59e0b}
.stat-i .att-stat-dot{background:#0ea5e9}
.stat-a .att-stat-dot{background:#ef4444}
.stat-d .att-stat-dot{background:#8b5cf6}
@media(max-width:767px){.edit-hero{padding:1.35rem;display:block}.edit-hero h1{font-size:1.4rem}.edit-hero .btn{margin-top:1rem}.edit-card .card-body{padding:1.25rem!important}}
</style>@stop
@section('js')<script>
document.addEventListener('DOMContentLoaded',function(){
    const type=document.getElementById('journal-type');
    const teach=document.getElementById('teaching-fields');
    const home=document.getElementById('homeroom-fields');
    const scheduleSelect=document.getElementById('journal-schedule');
    const homeroomSelect=document.getElementById('journal-homeroom');
    const dateInput=document.getElementById('journal-date');
    const attList=document.getElementById('att-students-list');
    const attSubtitle=document.getElementById('att-subtitle');
    const attLoading=document.getElementById('att-loading');
    const attHeaderActions=document.getElementById('att-header-actions');
    const attStatsPills=document.getElementById('att-stats-pills');
    const attSourceAlert=document.getElementById('att-source-alert');
    const attSourceText=document.getElementById('att-source-text');
    const btnAllPresent=document.getElementById('btn-all-present');

    function toggle(){
        const isTeach=type.value==='mengajar';
        teach.classList.toggle('d-none',!isTeach);
        if(home)home.classList.toggle('d-none',isTeach);
    }
    type.addEventListener('change',function(){
        toggle();
        fetchAttendance();
    });
    toggle();

    function updateStats(){
        const statuses={hadir:0,sakit:0,izin:0,alpa:0,dispen:0};
        const rows=attList.querySelectorAll('.student-att-row');
        rows.forEach(row=>{
            const checked=row.querySelector('input[type="radio"]:checked');
            if(checked && statuses.hasOwnProperty(checked.value)){
                statuses[checked.value]++;
            }
        });
        const elH=document.getElementById('stat-hadir');
        const elS=document.getElementById('stat-sakit');
        const elI=document.getElementById('stat-izin');
        const elA=document.getElementById('stat-alpa');
        const elD=document.getElementById('stat-dispen');
        if(elH) elH.textContent=statuses.hadir;
        if(elS) elS.textContent=statuses.sakit;
        if(elI) elI.textContent=statuses.izin;
        if(elA) elA.textContent=statuses.alpa;
        if(elD) elD.textContent=statuses.dispen;
    }
    window.updateAttendanceStats=updateStats;

    if(btnAllPresent){
        btnAllPresent.addEventListener('click',function(){
            const rows=attList.querySelectorAll('.student-att-row');
            rows.forEach(row=>{
                const radioH=row.querySelector('input[value="hadir"]');
                if(radioH){
                    radioH.checked=true;
                    row.querySelectorAll('.att-status-btn').forEach(lbl=>lbl.classList.remove('active'));
                    const parent=radioH.closest('.att-status-btn');
                    if(parent) parent.classList.add('active');
                }
            });
            updateStats();
        });
    }

    if(attList){
        attList.addEventListener('click', function(e){
            const btn=e.target.closest('.att-status-btn');
            if(!btn) return;
            const group=btn.closest('.att-status-group');
            if(group){
                group.querySelectorAll('.att-status-btn').forEach(b=>b.classList.remove('active'));
                btn.classList.add('active');
                const radio=btn.querySelector('input[type="radio"]');
                if(radio){
                    radio.checked=true;
                }
                updateStats();
            }
        });

        attList.addEventListener('change', function(e){
            if(e.target && e.target.type==='radio'){
                const btn=e.target.closest('.att-status-btn');
                const group=e.target.closest('.att-status-group');
                if(btn && group){
                    group.querySelectorAll('.att-status-btn').forEach(b=>b.classList.remove('active'));
                    btn.classList.add('active');
                }
                updateStats();
            }
        });
    }

    let fetchTimer=null;
    function fetchAttendance(){
        if(!dateInput) return;
        const tanggal=dateInput.value;
        const isTeach=type.value==='mengajar';
        const scheduleId=isTeach && scheduleSelect ? scheduleSelect.value : null;
        const kelasId=!isTeach && homeroomSelect ? homeroomSelect.value : null;

        if(!tanggal || (isTeach && !scheduleId) || (!isTeach && !kelasId)){
            if(attHeaderActions) attHeaderActions.classList.add('d-none');
            if(attStatsPills) attStatsPills.classList.remove('d-flex');
            if(attStatsPills) attStatsPills.classList.add('d-none');
            if(attSourceAlert) attSourceAlert.classList.add('d-none');
            if(attSubtitle) attSubtitle.textContent='Pilih jadwal atau kelas untuk memuat daftar siswa.';
            attList.innerHTML='<div class="text-muted small text-center py-3"><i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>Daftar siswa akan otomatis tampil saat jadwal mengajar / kelas dipilih.</div>';
            return;
        }

        clearTimeout(fetchTimer);
        fetchTimer=setTimeout(function(){
            attLoading.classList.remove('d-none');
            attList.classList.add('opacity-50');

            const params=new URLSearchParams();
            params.append('tanggal', tanggal);
            params.append('journal_id', '{{ $journal->id }}');
            if(scheduleId) params.append('jadwal_pelajaran_id', scheduleId);
            if(kelasId) params.append('kelas_id', kelasId);

            fetch('{{ route("admin.gtk.journals.students-attendance") }}?'+params.toString(),{
                headers:{
                    'X-Requested-With':'XMLHttpRequest',
                    'Accept':'application/json'
                }
            })
            .then(res=>{
                if(!res.ok){
                    return res.json().then(errData => {
                        throw new Error(errData.message || ('Server error: HTTP ' + res.status));
                    }).catch(function(){
                        throw new Error('Server error: HTTP ' + res.status);
                    });
                }
                return res.json();
            })
            .then(data=>{
                attLoading.classList.add('d-none');
                attList.classList.remove('opacity-50');

                if(!data.success || !data.students || data.students.length===0){
                    attList.innerHTML='<div class="text-center text-muted small py-3"><i class="fas fa-exclamation-circle mr-1"></i> Tidak ada siswa aktif terdaftar di kelas ini.</div>';
                    attHeaderActions.classList.add('d-none');
                    attStatsPills.classList.add('d-none');
                    attStatsPills.classList.remove('d-flex');
                    attSourceAlert.classList.add('d-none');
                    return;
                }

                attSubtitle.textContent=data.kelas_name + (data.mapel_name ? ' · ' + data.mapel_name : '') + ' (' + data.total + ' siswa)';
                attHeaderActions.classList.remove('d-none');
                attStatsPills.classList.remove('d-none');
                attStatsPills.classList.add('d-flex');

                if(data.has_saved_session){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Tersimpan:</strong> Memuat presensi siswa yang sudah tercatat pada sesi ini.';
                } else if(data.prefilled_count > 0){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Sinkron Presensi Pagi:</strong> ' + data.prefilled_count + ' siswa tidak hadir pada absensi harian pagi disesuaikan otomatis.';
                } else {
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Baru:</strong> Seluruh siswa diset Hadir secara default.';
                }

                let html='';
                data.students.forEach((s, idx)=>{
                    html += `
                    <div class="student-att-row d-flex justify-content-between align-items-center" data-id="${s.id}">
                        <div class="d-flex align-items-center mr-2" style="min-width:0; flex:1;">
                            <span class="student-att-num">${idx+1}</span>
                            <div class="text-truncate">
                                <div class="font-weight-bold text-dark text-truncate" style="font-size:0.84rem; letter-spacing:-0.01em;">${s.nama}</div>
                                <div class="text-muted d-flex align-items-center flex-wrap" style="font-size:0.72rem; gap:4px;">
                                    <span>NISN: ${s.nisn || '-'}</span>
                                    ${s.source === 'harian' ? '<span class="att-badge-prefilled font-weight-bold"><i class="fas fa-clock mr-1"></i>Pagi: ' + (s.harian_status || '').toUpperCase() + '</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="att-status-group flex-shrink-0">
                            <label class="att-status-btn att-h ${s.status === 'hadir' ? 'active' : ''}" title="Hadir">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="hadir" ${s.status === 'hadir' ? 'checked' : ''}>
                                <span>H</span>
                            </label>
                            <label class="att-status-btn att-s ${s.status === 'sakit' ? 'active' : ''}" title="Sakit">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="sakit" ${s.status === 'sakit' ? 'checked' : ''}>
                                <span>S</span>
                            </label>
                            <label class="att-status-btn att-i ${s.status === 'izin' ? 'active' : ''}" title="Izin">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="izin" ${s.status === 'izin' ? 'checked' : ''}>
                                <span>I</span>
                            </label>
                            <label class="att-status-btn att-a ${s.status === 'alpa' ? 'active' : ''}" title="Alpa (Tanpa Keterangan)">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="alpa" ${s.status === 'alpa' ? 'checked' : ''}>
                                <span>A</span>
                            </label>
                            <label class="att-status-btn att-d ${s.status === 'dispen' ? 'active' : ''}" title="Dispensasi">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="dispen" ${s.status === 'dispen' ? 'checked' : ''}>
                                <span>D</span>
                            </label>
                        </div>
                    </div>`;
                });

                attList.innerHTML=html;
                updateStats();
            })
            .catch(err=>{
                console.error('Attendance fetch error:', err);
                attLoading.classList.add('d-none');
                attList.classList.remove('opacity-50');
                attList.innerHTML='<div class="text-center text-danger small py-3"><i class="fas fa-exclamation-triangle mr-1"></i> ' + (err.message || 'Gagal memuat data presensi.') + ' Silakan coba lagi.</div>';
            });
        }, 150);
    }

    if(scheduleSelect) scheduleSelect.addEventListener('change', fetchAttendance);
    if(homeroomSelect) homeroomSelect.addEventListener('change', fetchAttendance);
    if(dateInput) dateInput.addEventListener('change', fetchAttendance);

    // Initial load on edit
    fetchAttendance();
});
</script>@stop
