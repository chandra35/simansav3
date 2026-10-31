@extends('adminlte::page')
@section('title', 'Jurnal Guru')
@section('content_header') @stop
@section('content')
<div class="journal-hero shadow-sm">
 <div><span class="hero-kicker"><i class="fas fa-graduation-cap mr-1"></i> Pusat dokumentasi guru</span><h1>Jurnal mengajar, lebih terarah.</h1><p>Catat kegiatan, pantau kontribusi, dan siapkan dokumen EKIN/SKP dalam satu tempat.</p></div>
 <div class="hero-actions"><a href="#composer" class="btn btn-light btn-lg js-compose"><i class="fas fa-plus-circle mr-1"></i> Tulis jurnal</a><a class="btn btn-outline-light" href="{{ route('admin.gtk.journals.export', request()->query()) }}"><i class="fas fa-file-pdf mr-1"></i> PDF EKIN/SKP</a></div>
</div>
<div class="row journal-stats">
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-book text-primary"></i><div><span>Total entri</span><strong>{{ $summary['total'] }}</strong><small>pada periode dipilih</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-chalkboard-teacher text-info"></i><div><span>Mengajar</span><strong>{{ $summary['teaching'] }}</strong><small>catatan pembelajaran</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-users text-warning"></i><div><span>Wali kelas</span><strong>{{ $summary['homeroom'] }}</strong><small>pendampingan siswa</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-clock text-success"></i><div><span>Waktu tercatat</span><strong>{{ intdiv($summary['minutes'], 60) }}j {{ $summary['minutes'] % 60 }}m</strong><small>akumulasi durasi</small></div></div></div>
</div>
<div class="card filter-card border-0 shadow-sm"><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="filter-icon"><i class="fas fa-sliders-h"></i></div><div><strong>Periode laporan</strong><div class="small text-muted">Sesuaikan riwayat dan ringkasan jurnal.</div></div></div><form method="GET" class="row align-items-end"><div class="col-md-3 form-group"><label>Jenis jurnal</label><select name="jenis" class="custom-select"><option value="">Semua jurnal</option><option value="mengajar" @selected($jenis === 'mengajar')>Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas" @selected($jenis === 'wali_kelas')>Jurnal wali kelas</option>@endif</select></div><div class="col-md-3 form-group"><label>Dari tanggal</label><input type="date" name="dari" value="{{ $dari }}" class="form-control"></div><div class="col-md-3 form-group"><label>Sampai tanggal</label><input type="date" name="sampai" value="{{ $sampai }}" class="form-control"></div><div class="col-md-3 form-group"><button class="btn btn-primary mr-1"><i class="fas fa-check mr-1"></i>Terapkan</button><a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light">Reset</a></div></form></div></div>
<div class="row"><div class="col-xl-5" id="composer"><div class="card compose-card border-0 shadow-sm"><div class="card-header bg-white border-0 pt-4 px-4"><span class="text-success font-weight-bold"><i class="fas fa-pen-nib mr-1"></i> ENTRI BARU</span><h3 class="mt-1 mb-1">Tulis jurnal hari ini</h3><p class="text-muted small mb-0">Lengkapi informasi inti, lalu simpan sebagai bukti aktivitas.</p></div><form method="POST" action="{{ route('admin.gtk.journals.store') }}"><div class="card-body px-4">@csrf
<div class="row"><div class="col-md-6 form-group"><label>Jenis jurnal</label><select name="jenis" id="journal-type" class="custom-select" required><option value="mengajar">Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas">Jurnal wali kelas</option>@endif</select></div><div class="col-md-6 form-group"><label>Tanggal kegiatan</label><input type="date" name="tanggal" id="journal-date" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required></div></div>
<div id="teaching-fields"><div class="form-group"><label>Jadwal mengajar</label><select name="jadwal_pelajaran_id" id="journal-schedule" class="custom-select"><option value="">Pilih jadwal mengajar</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->mataPelajaran?->nama_mapel }} · {{ $schedule->kelas?->nama_kelas }} · {{ $schedule->hari_label }} jam {{ $schedule->jam_ke }}</option>@endforeach</select><small>Gunakan jadwal agar mata pelajaran dan kelas tercatat otomatis.</small></div></div>
@if($waliClasses->isNotEmpty())<div id="homeroom-fields" class="d-none"><div class="form-group"><label>Kelas wali</label><select name="kelas_id" id="journal-homeroom" class="custom-select"><option value="">Pilih kelas wali</option>@foreach($waliClasses as $kelas)<option value="{{ $kelas->id }}">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</option>@endforeach</select></div></div>@endif

<div id="attendance-section" class="attendance-card mt-3 mb-3 p-3 rounded border bg-light">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <span class="font-weight-bold text-dark"><i class="fas fa-user-check text-primary mr-1"></i> Presensi Kehadiran Siswa</span>
            <div class="small text-muted" id="att-subtitle">Pilih jadwal atau kelas untuk memuat daftar siswa.</div>
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

    <div id="att-students-list" style="max-height: 270px; overflow-y: auto;" class="pr-1">
        <div class="text-muted small text-center py-3" id="att-placeholder">
            <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>
            Daftar siswa akan otomatis tampil saat jadwal mengajar / kelas dipilih.
        </div>
    </div>
</div>

<div class="form-group"><label>Topik / agenda</label><input name="topik" value="{{ old('topik') }}" maxlength="255" class="form-control" placeholder="Contoh: Persamaan kuadrat dan penerapannya" required></div><div class="form-group"><label>Kegiatan pembelajaran / tugas</label><textarea name="kegiatan" rows="4" class="form-control" placeholder="Jelaskan kegiatan utama yang dilakukan..." required>{{ old('kegiatan') }}</textarea></div><div class="row"><div class="col-md-7 form-group"><label>Asesmen / tindak lanjut</label><textarea name="asesmen_tindak_lanjut" rows="2" class="form-control">{{ old('asesmen_tindak_lanjut') }}</textarea></div><div class="col-md-5 form-group"><label>Durasi (menit)</label><input type="number" min="1" max="720" name="durasi_menit" value="{{ old('durasi_menit') }}" class="form-control" placeholder="90"></div></div><div class="form-group mb-0"><label>Catatan tambahan <span class="text-muted font-weight-normal">(opsional)</span></label><textarea name="catatan" rows="2" class="form-control">{{ old('catatan') }}</textarea></div></div><div class="card-footer bg-white border-0 px-4 pb-4"><button class="btn btn-success btn-lg btn-block"><i class="fas fa-save mr-1"></i> Simpan jurnal</button></div></form></div></div>
<div class="col-xl-7"><div class="card history-card border-0 shadow-sm"><div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between"><div><span class="text-info font-weight-bold"><i class="fas fa-history mr-1"></i> RIWAYAT</span><h3 class="mt-1">Aktivitas jurnal</h3></div><span class="badge badge-pill badge-info align-self-start p-2">{{ $journals->total() }} entri</span></div>@forelse($journals as $journal)
    <div class="journal-item mx-4">
        <div class="journal-date">
            <strong>{{ $journal->tanggal->format('d') }}</strong>
            <span>{{ $journal->tanggal->translatedFormat('M') }}</span>
            <small>{{ $journal->tanggal->format('Y') }}</small>
        </div>
        <div class="journal-main">
            <div class="d-flex flex-wrap align-items-center mb-1">
                <span class="type-badge {{ $journal->jenis === 'mengajar' ? 'teaching' : 'homeroom' }}">
                    {{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali kelas' }}
                </span>
                @if($journal->durasi_menit)
                    <span class="small text-muted ml-2">
                        <i class="far fa-clock mr-1"></i>{{ $journal->durasi_menit }} menit
                    </span>
                @endif
            </div>
            <strong>{{ $journal->topik }}</strong>
            <p>{{ \Illuminate\Support\Str::limit($journal->kegiatan, 135) }}</p>
            <span class="context">
                <i class="fas fa-school mr-1"></i>{{ $journal->kelas_snapshot ?: 'Kelas tidak dicatat' }}
                @if($journal->mapel_snapshot)
                    · {{ $journal->mapel_snapshot }}
                @endif
            </span>
            @if(!empty($journal->attendance_summary))
                @php $att = $journal->attendance_summary; @endphp
                <div class="att-summary-chip mt-2 d-flex flex-wrap align-items-center">
                    <span class="badge badge-light border text-dark font-weight-bold mr-1">
                        <i class="fas fa-user-check text-success mr-1"></i> Hadir: {{ $att['hadir'] ?? 0 }}/{{ $att['total'] ?? 0 }} ({{ $att['persentase'] ?? 100 }}%)
                    </span>
                    @if(!empty($att['absent_students']))
                        <span class="badge badge-light border text-danger" title="{{ collect($att['absent_students'])->map(fn($s) => $s['nama'].' ('.ucfirst($s['status']).')')->implode(', ') }}">
                            <i class="fas fa-user-times mr-1"></i> {{ count($att['absent_students']) }} tidak hadir
                        </span>
                    @else
                        <span class="badge badge-light border text-success">
                            <i class="fas fa-check-circle mr-1"></i> 100% hadir
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <div class="journal-actions">
            <a class="btn btn-sm btn-light" title="Edit" href="{{ route('admin.gtk.journals.edit', $journal) }}">
                <i class="fas fa-pen text-primary"></i>
            </a>
            <form method="POST" action="{{ route('admin.gtk.journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-light" title="Hapus">
                    <i class="fas fa-trash text-danger"></i>
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-book-reader"></i></div>
        <h4>Belum ada jurnal pada periode ini</h4>
        <p>Mulai dokumentasikan aktivitas mengajar atau tugas wali kelas untuk membangun riwayat EKIN/SKP yang lengkap.</p>
        <a href="#composer" class="btn btn-primary js-compose">
            <i class="fas fa-plus mr-1"></i> Tulis jurnal pertama
        </a>
    </div>
@endforelse

@if($journals->hasPages())
    <div class="card-footer bg-white">
        {{ $journals->withQueryString()->links() }}
    </div>
@endif
</div></div></div>
@stop
@section('css')<style>
.journal-hero{background:linear-gradient(125deg,#183b75,#2365b4 55%,#15a5ad);color:#fff;border-radius:16px;padding:2rem;display:flex;justify-content:space-between;gap:1.5rem;align-items:center;margin-bottom:1.25rem}.hero-kicker{font-size:.78rem;font-weight:700;letter-spacing:.08em;opacity:.85}.journal-hero h1{font-size:1.8rem;font-weight:700;margin:.4rem 0}.journal-hero p{margin:0;opacity:.88;max-width:580px}.hero-actions{display:flex;gap:.5rem;flex-wrap:wrap}.journal-stats .col-sm-6{margin-bottom:1rem}.stat-card{background:#fff;border-radius:13px;padding:1.15rem;display:flex;gap:1rem;align-items:center;box-shadow:0 3px 13px rgba(31,55,90,.08);height:100%}.stat-card>i{font-size:1.55rem;width:45px;height:45px;border-radius:12px;background:#f2f6fc;display:grid;place-items:center}.stat-card span,.stat-card small{display:block;color:#7b8796;font-size:.78rem}.stat-card strong{display:block;font-size:1.25rem;color:#2a3850}.filter-card,.compose-card,.history-card{border-radius:14px}.filter-icon{width:35px;height:35px;display:grid;place-items:center;background:#eaf3ff;color:#1976d2;border-radius:9px;margin-right:.75rem}.compose-card label,.filter-card label{font-size:.82rem;font-weight:700;color:#4a5668}.compose-card small{color:#8491a2;font-size:.74rem}.journal-item{padding:1.05rem 0;border-top:1px solid #eef1f5;display:flex;gap:1rem}.journal-date{width:52px;min-width:52px;text-align:center;padding:.35rem;background:#f4f7fb;border-radius:10px;color:#526174;line-height:1}.journal-date strong{font-size:1.18rem;color:#263c5a;display:block}.journal-date span{font-size:.7rem;text-transform:uppercase;font-weight:700}.journal-date small{font-size:.62rem}.journal-main{flex:1;min-width:0}.journal-main>strong{color:#27364d}.journal-main p{font-size:.83rem;margin:.3rem 0;color:#687587}.context{font-size:.76rem;color:#7d8a9c}.type-badge{font-size:.67rem;font-weight:700;border-radius:20px;padding:.25rem .55rem}.type-badge.teaching{background:#e9f2ff;color:#1763b0}.type-badge.homeroom{background:#fff3dd;color:#b57600}.journal-actions{display:flex;gap:.25rem;align-items:flex-start}.journal-actions form{margin:0}.empty-state{text-align:center;padding:4.5rem 1.5rem;color:#718096}.empty-state h4{color:#35445c;margin-top:1rem}.empty-state p{max-width:420px;margin:.5rem auto 1.25rem;font-size:.9rem}.empty-icon{margin:auto;width:66px;height:66px;border-radius:50%;display:grid;place-items:center;background:#edf5ff;color:#2672c9;font-size:1.7rem}
.attendance-card{background:#f8fafc;border-color:#e2e8f0!important;border-radius:12px}.student-att-row{transition:background-color .15s ease;border-color:#e2e8f0!important}.student-att-row:hover{background:#f1f5f9!important}.btn-xs{padding:.12rem .4rem;font-size:.72rem;line-height:1.2;border-radius:.2rem}.att-badge-prefilled{font-size:.68rem;background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 5px}
@media(max-width:767px){.journal-hero{padding:1.4rem;display:block}.hero-actions{margin-top:1rem}.journal-hero h1{font-size:1.45rem}.journal-item{margin-left:1rem!important;margin-right:1rem!important}.journal-actions{flex-direction:column}.filter-card .form-group{margin-bottom:.8rem}}
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
    const attPlaceholder=document.getElementById('att-placeholder');
    const attHeaderActions=document.getElementById('att-header-actions');
    const attStatsPills=document.getElementById('att-stats-pills');
    const attSourceAlert=document.getElementById('att-source-alert');
    const attSourceText=document.getElementById('att-source-text');
    const btnAllPresent=document.getElementById('btn-all-present');

    function toggle(){
        if(!type)return;
        const isTeach=type.value==='mengajar';
        teach.classList.toggle('d-none',!isTeach);
        if(home)home.classList.toggle('d-none',isTeach);
        fetchAttendance();
    }

    if(type){type.addEventListener('change',toggle);toggle();}

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
        const isTeach=!type || type.value==='mengajar';
        const scheduleId=isTeach && scheduleSelect ? scheduleSelect.value : null;
        const kelasId=!isTeach && homeroomSelect ? homeroomSelect.value : null;

        if(!tanggal || (isTeach && !scheduleId) || (!isTeach && !kelasId)){
            if(attPlaceholder) attPlaceholder.classList.remove('d-none');
            if(attHeaderActions) attHeaderActions.classList.add('d-none');
            if(attStatsPills) attStatsPills.classList.remove('d-flex');
            if(attStatsPills) attStatsPills.classList.add('d-none');
            if(attSourceAlert) attSourceAlert.classList.add('d-none');
            if(attSubtitle) attSubtitle.textContent='Pilih jadwal atau kelas untuk memuat daftar siswa.';
            attList.innerHTML='<div class="text-muted small text-center py-3" id="att-placeholder"><i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>Daftar siswa akan otomatis tampil saat jadwal mengajar / kelas dipilih.</div>';
            return;
        }

        clearTimeout(fetchTimer);
        fetchTimer=setTimeout(function(){
            attLoading.classList.remove('d-none');
            attList.classList.add('opacity-50');

            const params=new URLSearchParams();
            params.append('tanggal', tanggal);
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

                if(data.prefilled_count > 0){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Sinkron Presensi Pagi:</strong> ' + data.prefilled_count + ' siswa tidak hadir pada absensi harian pagi disesuaikan otomatis.';
                } else if(data.has_saved_session){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Mapel Tersimpan:</strong> Memuat data presensi yang sudah dicatat sebelumnya.';
                } else {
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Baru:</strong> Default seluruh siswa diset Hadir. Sesuaikan jika ada yang berhalangan.';
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
        }, 200);
    }

    if(scheduleSelect) scheduleSelect.addEventListener('change', fetchAttendance);
    if(homeroomSelect) homeroomSelect.addEventListener('change', fetchAttendance);
    if(dateInput) dateInput.addEventListener('change', fetchAttendance);

    document.querySelectorAll('.js-compose').forEach(el=>el.addEventListener('click',()=>setTimeout(()=>document.querySelector('#composer input[name=tanggal]').focus(),350)));
});
</script>@stop
