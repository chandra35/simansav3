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
                <button type="button" class="btn btn-xs btn-outline-success font-weight-bold shadow-none" id="btn-all-present">
                    <i class="fas fa-check-double mr-1"></i> Semua Hadir
                </button>
            </div>
        </div>
        
        <div id="att-source-alert" class="alert alert-info py-1 px-2 small mb-2 d-none">
            <i class="fas fa-info-circle mr-1"></i> <span id="att-source-text"></span>
        </div>

        <div id="att-stats-pills" class="d-none flex-wrap align-items-center mb-2">
            <span class="badge badge-success px-2 py-1 mr-1 mb-1" id="stat-hadir">Hadir: 0</span>
            <span class="badge badge-warning px-2 py-1 mr-1 mb-1" id="stat-sakit">Sakit: 0</span>
            <span class="badge badge-info px-2 py-1 mr-1 mb-1" id="stat-izin">Izin: 0</span>
            <span class="badge badge-danger px-2 py-1 mr-1 mb-1" id="stat-alpa">Alpa: 0</span>
            <span class="badge badge-secondary px-2 py-1 mr-1 mb-1" id="stat-dispen">Dispen: 0</span>
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
.attendance-card{background:#f8fafc;border-color:#e2e8f0!important;border-radius:12px}.student-att-row{transition:background-color .15s ease;border-color:#e2e8f0!important}.student-att-row:hover{background:#f1f5f9!important}.btn-xs{padding:.12rem .4rem;font-size:.72rem;line-height:1.2;border-radius:.2rem}.att-badge-prefilled{font-size:.68rem;background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 5px}
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
        document.getElementById('stat-hadir').textContent='Hadir: '+statuses.hadir;
        document.getElementById('stat-sakit').textContent='Sakit: '+statuses.sakit;
        document.getElementById('stat-izin').textContent='Izin: '+statuses.izin;
        document.getElementById('stat-alpa').textContent='Alpa: '+statuses.alpa;
        document.getElementById('stat-dispen').textContent='Dispen: '+statuses.dispen;
    }
    window.updateAttendanceStats=updateStats;

    if(btnAllPresent){
        btnAllPresent.addEventListener('click',function(){
            const rows=attList.querySelectorAll('.student-att-row');
            rows.forEach(row=>{
                const radioH=row.querySelector('input[value="hadir"]');
                if(radioH){
                    radioH.checked=true;
                    row.querySelectorAll('label').forEach(lbl=>lbl.classList.remove('active'));
                    radioH.closest('label').classList.add('active');
                }
            });
            updateStats();
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
                    <div class="student-att-row p-2 mb-1 bg-white rounded border d-flex justify-content-between align-items-center" data-id="${s.id}">
                        <div class="d-flex align-items-center mr-2" style="min-width:0; flex:1;">
                            <span class="mr-2 text-muted font-weight-bold" style="font-size:0.75rem; min-width:22px;">${idx+1}.</span>
                            <div class="text-truncate">
                                <div class="font-weight-bold text-dark text-truncate" style="font-size:0.83rem;">${s.nama}</div>
                                <div class="text-muted" style="font-size:0.72rem;">
                                    NISN: ${s.nisn || '-'}
                                    ${s.source === 'harian' ? '<span class="att-badge-prefilled ml-1 font-weight-bold"><i class="fas fa-clock mr-1"></i>Pagi: ' + (s.harian_status || '').toUpperCase() + '</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="btn-group btn-group-toggle btn-group-sm flex-shrink-0" data-toggle="buttons">
                            <label class="btn btn-outline-success ${s.status === 'hadir' ? 'active' : ''} btn-xs px-2" title="Hadir">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="hadir" ${s.status === 'hadir' ? 'checked' : ''} onchange="updateAttendanceStats()"> H
                            </label>
                            <label class="btn btn-outline-warning ${s.status === 'sakit' ? 'active' : ''} btn-xs px-2" title="Sakit">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="sakit" ${s.status === 'sakit' ? 'checked' : ''} onchange="updateAttendanceStats()"> S
                            </label>
                            <label class="btn btn-outline-info ${s.status === 'izin' ? 'active' : ''} btn-xs px-2" title="Izin">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="izin" ${s.status === 'izin' ? 'checked' : ''} onchange="updateAttendanceStats()"> I
                            </label>
                            <label class="btn btn-outline-danger ${s.status === 'alpa' ? 'active' : ''} btn-xs px-2" title="Alpa">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="alpa" ${s.status === 'alpa' ? 'checked' : ''} onchange="updateAttendanceStats()"> A
                            </label>
                            <label class="btn btn-outline-secondary ${s.status === 'dispen' ? 'active' : ''} btn-xs px-2" title="Dispensasi">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="dispen" ${s.status === 'dispen' ? 'checked' : ''} onchange="updateAttendanceStats()"> D
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
