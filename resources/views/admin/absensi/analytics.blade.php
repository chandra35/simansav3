@extends('adminlte::page')

@section('title', 'Analitik Kehadiran Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-chart-line text-primary mr-2"></i>Analitik Kehadiran</h1>
        <a href="{{ route('admin.absensi-siswa.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-clipboard-check mr-1"></i> Input Presensi</a>
    </div>
@stop

@section('content')
<div class="attendance-page pb-4">
    <section class="attendance-hero">
        <div>
            <div class="hero-kicker"><i class="fas fa-history mr-1"></i> RIWAYAT LINTAS TINGKAT</div>
            <h2>Analitik Kehadiran Siswa</h2>
            <p>Rekam jejak kelas X–XII tetap tersimpan per tahun pelajaran. Analisis hanya memakai sesi yang sudah difinalkan.</p>
        </div>
        <div class="hero-side">
            <span>Tahun dipilih</span><strong>{{ $year->nama }}</strong>
            @if($activeYear && $activeYear->id === $year->id)<small><i class="fas fa-circle"></i> Tahun aktif</small>@else<small>Arsip historis</small>@endif
        </div>
    </section>

    <form method="GET" class="filter-panel mt-3">
        <div class="form-group"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control">@foreach($years as $item)<option value="{{ $item->id }}" @selected($item->id === $year->id)>{{ $item->nama }}{{ $item->is_active ? ' · Aktif' : '' }}</option>@endforeach</select></div>
        <div class="form-group"><label>Tingkat</label><select name="tingkat" id="attendance-level" class="form-control"><option value="">Semua tingkat</option>@foreach([10,11,12] as $level)<option value="{{ $level }}" @selected($tingkat === $level)>Kelas {{ $level }}</option>@endforeach</select></div>
        <div class="form-group"><label>Kelas</label><select name="kelas_id" id="attendance-class" class="form-control"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" data-level="{{ $class->tingkat }}" @selected($classId === $class->id)>{{ $class->nama_kelas }}</option>@endforeach</select></div>
        <div class="form-group"><label>Mulai</label><input type="date" name="start_date" value="{{ $start->toDateString() }}" class="form-control"></div>
        <div class="form-group"><label>Sampai</label><input type="date" name="end_date" value="{{ $end->toDateString() }}" class="form-control"></div>
        <button class="btn btn-primary filter-button"><i class="fas fa-filter mr-1"></i> Terapkan</button>
    </form>

    <div class="metric-grid mt-3">
        <div class="metric blue"><span>Sesi mapel final</span><strong>{{ number_format($kpi['subject_sessions']) }}</strong><small>Guru menandai secara manual</small></div>
        <div class="metric green"><span>Kehadiran mapel</span><strong>{{ number_format($kpi['attendance_rate'], 1) }}%</strong><small>Dari {{ number_format($kpi['records']) }} catatan mapel</small></div>
        <div class="metric cyan"><span>Presensi wajah harian</span><strong>{{ number_format($kpi['daily_records']) }}</strong><small>{{ number_format($kpi['daily_attendance_rate'], 1) }}% hadir/terlambat</small></div>
        <div class="metric amber"><span>Perlu ditindaklanjuti</span><strong>{{ number_format($kpi['active_alerts']) }}</strong><small>{{ $kpi['high_alerts'] }} prioritas tinggi</small></div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-4 mb-3">
            <section class="content-card h-100">
                <div class="section-head"><div><h3>Distribusi Status</h3><p>Akumulasi seluruh catatan pada filter.</p></div></div>
                @php($statusMeta = ['hadir'=>['Hadir','#25a56a'],'terlambat'=>['Terlambat','#f0a000'],'izin'=>['Izin','#3b82f6'],'sakit'=>['Sakit','#8b5cf6'],'alpa'=>['Alpa','#e5484d'],'dispen'=>['Dispensasi','#0ea5a8'],'keluar_awal'=>['Keluar awal','#ef6c35']])
                @forelse($statusMeta as $code => [$label,$color])
                    @php($count=(int)($statusCounts[$code] ?? 0))
                    <div class="status-row"><span><i style="background:{{ $color }}"></i>{{ $label }}</span><strong>{{ number_format($count) }}</strong></div>
                @empty @endforelse
                <div class="info-note"><i class="fas fa-info-circle"></i><span>Hadir, terlambat, dan keluar awal dihitung hadir, tetapi keterlambatan tetap dianalisis terpisah.</span></div>
            </section>
        </div>
        <div class="col-xl-8 mb-3">
            <section class="content-card h-100">
                <div class="section-head"><div><h3>Smart Suggestion</h3><p>Indikasi berbasis aturan dan bukti, bukan keputusan otomatis.@if($lastAnalysis) Terakhir: {{ $lastAnalysis->created_at->translatedFormat('d M Y H:i') }} oleh {{ $lastAnalysis->actor?->name ?: 'sistem terjadwal' }}.@endif</p></div>@can('manage-attendance-alerts')<button id="generate-insights" class="btn btn-primary btn-sm"><i class="fas fa-magic mr-1"></i> Analisis Ulang</button>@endcan</div>
                <div class="alert-list">
                    @forelse($alerts as $alert)
                    <article class="smart-alert severity-{{ $alert->severity }}">
                        <div class="alert-main"><div class="alert-badges"><span>{{ strtoupper($alert->severity) }}</span><span>{{ strtoupper($alert->status) }}</span></div><h4>{{ $alert->siswa?->nama_lengkap }}</h4><strong>{{ $alert->title }}</strong><p>{{ $alert->explanation }}</p></div>
                        <div class="alert-actions"><a href="{{ route('admin.absensi-siswa.analytics.student', $alert->siswa_id) }}" class="btn btn-outline-primary btn-sm">Detail</a>@can('manage-attendance-alerts')<button class="btn btn-outline-secondary btn-sm review-alert" data-id="{{ $alert->id }}" data-status="{{ $alert->status }}" data-notes="{{ e($alert->review_notes) }}">Tindak lanjut</button>@endcan</div>
                    </article>
                    @empty<div class="empty-state"><i class="fas fa-circle-check"></i><strong>Belum ada indikator aktif</strong><span>Jalankan analisis setelah presensi final tersedia.</span></div>@endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="content-card">
        <div class="section-head"><div><h3>Ringkasan per Siswa</h3><p>Urutan memprioritaskan alpa dan keterlambatan agar mudah ditinjau wali kelas/BK.</p></div><span class="record-pill">Maks. 100 siswa</span></div>
        <div class="table-responsive"><table class="table attendance-table"><thead><tr><th>Siswa</th><th>Total Catatan</th><th>Kehadiran</th><th>Alpa</th><th>Terlambat</th><th>Sakit / Izin</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse($studentRows as $student)<tr><td><strong>{{ $student->nama_lengkap }}</strong><small>NISN {{ $student->nisn ?: '-' }}</small></td><td>{{ $student->total_records }}</td><td><span class="rate-pill {{ $student->attendance_rate < 85 ? 'risk' : '' }}">{{ number_format($student->attendance_rate,1) }}%</span></td><td>{{ $student->alpa }}</td><td>{{ $student->terlambat }}</td><td>{{ $student->sakit }} / {{ $student->izin }}</td><td class="text-right"><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.absensi-siswa.analytics.student', $student->id) }}"><i class="fas fa-chart-line mr-1"></i> Riwayat</a></td></tr>
        @empty<tr><td colspan="7"><div class="empty-state compact">Belum ada sesi final pada rentang ini.</div></td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>
@stop

@section('css')
<style>
.metric.cyan{border-top-color:#0ea5a8}
.attendance-page{color:#15213a}.attendance-hero{display:flex;justify-content:space-between;gap:24px;align-items:center;padding:26px;border-radius:20px;color:#fff;background:linear-gradient(120deg,#356df3,#557ff0 65%,#368fa0);box-shadow:0 15px 34px rgba(51,94,180,.18)}.hero-kicker{font-size:13px;font-weight:800}.attendance-hero h2{margin:8px 0 3px;font-weight:800}.attendance-hero p{margin:0;opacity:.93}.hero-side{min-width:190px;padding:14px 18px;border:1px solid rgba(255,255,255,.3);border-radius:14px;background:rgba(255,255,255,.12)}.hero-side span,.hero-side small{display:block}.hero-side strong{font-size:21px}.hero-side small i{font-size:8px;color:#4ade80}.filter-panel,.content-card{background:#fff;border:1px solid #dce5f2;border-radius:18px;box-shadow:0 12px 28px rgba(25,54,104,.07)}.filter-panel{display:grid;grid-template-columns:1.25fr .75fr 1.1fr .9fr .9fr auto;gap:12px;align-items:end;padding:18px}.form-group{margin:0}.form-group label{font-size:12px;text-transform:uppercase;color:#53647e}.filter-button{height:38px}.metric-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.metric{position:relative;overflow:hidden;padding:19px;border-radius:16px;background:#fff;border:1px solid #dce5f2;border-top:4px solid}.metric span,.metric small{display:block;color:#64748b}.metric strong{font-size:28px;line-height:1.25}.metric.blue{border-top-color:#3973f6}.metric.green{border-top-color:#29b36e}.metric.amber{border-top-color:#e99a00}.metric.purple{border-top-color:#8958ef}.content-card{padding:20px}.section-head{display:flex;justify-content:space-between;gap:15px;align-items:start;margin-bottom:15px}.section-head h3{font-size:19px;font-weight:800;margin:0}.section-head p{color:#65758d;margin:3px 0 0}.status-row{display:flex;justify-content:space-between;padding:10px 2px;border-bottom:1px solid #edf1f6}.status-row i{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:9px}.info-note{display:flex;gap:9px;margin-top:15px;padding:12px;border-radius:12px;background:#eef5ff;color:#3b5685;font-size:13px}.alert-list{max-height:420px;overflow:auto}.smart-alert{display:flex;justify-content:space-between;gap:12px;padding:14px;margin-bottom:10px;border:1px solid #e4eaf2;border-left:4px solid #e5a000;border-radius:12px}.smart-alert.severity-high{border-left-color:#e23c4b}.smart-alert.severity-low{border-left-color:#2baf71}.alert-main h4{font-size:16px;font-weight:800;margin:5px 0 2px}.alert-main>strong{font-size:14px}.alert-main p{font-size:13px;color:#65758d;margin:2px 0}.alert-badges span{font-size:10px;font-weight:800;padding:3px 7px;border-radius:20px;background:#f2f5fa;margin-right:4px}.alert-actions{display:flex;gap:6px;align-items:center;white-space:nowrap}.empty-state{display:flex;min-height:150px;flex-direction:column;align-items:center;justify-content:center;color:#718096}.empty-state i{font-size:28px;color:#3eb878;margin-bottom:8px}.empty-state.compact{min-height:80px}.attendance-table{margin:0}.attendance-table thead th{background:#f6f8fc;color:#53647e;font-size:12px;text-transform:uppercase;border-top:0}.attendance-table td{vertical-align:middle}.attendance-table td small{display:block;color:#8795aa}.rate-pill,.record-pill{display:inline-block;padding:5px 10px;border-radius:20px;background:#e8f8ef;color:#168451;font-weight:700}.rate-pill.risk{background:#fff0e5;color:#c26300}.record-pill{background:#eef3ff;color:#3865de}@media(max-width:1199px){.filter-panel{grid-template-columns:repeat(3,1fr)}.metric-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.attendance-hero{align-items:flex-start;flex-direction:column}.hero-side{width:100%}.filter-panel{grid-template-columns:1fr}.metric-grid{grid-template-columns:1fr 1fr}.smart-alert{flex-direction:column}.alert-actions{justify-content:flex-end}}@media(max-width:480px){.metric-grid{grid-template-columns:1fr}}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const level = document.getElementById('attendance-level'), classSelect = document.getElementById('attendance-class');
    function filterClasses(){const selected=level.value;Array.from(classSelect.options).forEach((option,index)=>{if(index===0)return;option.hidden=!!selected&&option.dataset.level!==selected});if(classSelect.selectedOptions[0]?.hidden)classSelect.value=''}
    level.addEventListener('change',filterClasses);filterClasses();
    const csrf='{{ csrf_token() }}';
    document.getElementById('generate-insights')?.addEventListener('click', async function(){
        const result=await Swal.fire({title:'Analisis ulang kehadiran?',text:'Sistem membaca sesi final tahun aktif dan memperbarui indikator berbasis aturan.',icon:'question',showCancelButton:true,confirmButtonText:'Ya, analisis',cancelButtonText:'Batal'});if(!result.isConfirmed)return;
        Swal.fire({title:'Menganalisis data...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        try{const response=await fetch('{{ route('admin.absensi-siswa.analytics.generate') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});const json=await response.json();if(!response.ok)throw new Error(json.message||'Gagal menganalisis data.');await Swal.fire({icon:'success',title:'Analisis selesai',text:json.message,timer:1600,showConfirmButton:false});location.reload()}catch(error){Swal.fire('Gagal',error.message,'error')}
    });
    document.querySelectorAll('.review-alert').forEach(button=>button.addEventListener('click',async function(){
        const current=this.dataset.status, notes=this.dataset.notes||'';
        const result=await Swal.fire({title:'Tindak lanjut indikator',html:`<select id="alert-status" class="swal2-select"><option value="new">Baru</option><option value="reviewed">Sudah ditinjau</option><option value="monitoring">Dalam pemantauan</option><option value="resolved">Selesai</option><option value="dismissed">Diabaikan</option></select><textarea id="alert-notes" class="swal2-textarea" placeholder="Catatan tindak lanjut">${notes.replace(/</g,'&lt;')}</textarea>`,showCancelButton:true,confirmButtonText:'Simpan',cancelButtonText:'Batal',didOpen:()=>document.getElementById('alert-status').value=current,preConfirm:()=>({status:document.getElementById('alert-status').value,review_notes:document.getElementById('alert-notes').value})});if(!result.isConfirmed)return;
        try{const response=await fetch(`{{ url('/admin/absensi-siswa/analitik/alert') }}/${this.dataset.id}`,{method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(result.value)});const json=await response.json();if(!response.ok)throw new Error(json.message||'Gagal menyimpan.');await Swal.fire({icon:'success',title:'Tersimpan',text:json.message,timer:1300,showConfirmButton:false});location.reload()}catch(error){Swal.fire('Gagal',error.message,'error')}
    }));
});
</script>
@stop
