@extends('adminlte::page')

@section('title', 'Analitik Kehadiran Siswa')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line text-primary mr-2"></i>Analitik Kehadiran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Kelas Saya</li>
                <li class="breadcrumb-item active">Analitik Kehadiran</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="attendance-analytics-page pb-4">
    <div class="card bg-gradient-primary text-white attendance-hero mb-3">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-kicker mb-1"><i class="fas fa-user-check mr-1"></i> KEHADIRAN & PEMBINAAN SISWA</div>
                    <h2 class="hero-title mb-1">Analitik Kehadiran Siswa</h2>
                    <p class="hero-description mb-0">Pantau kehadiran, keterlambatan, indikator risiko, dan catatan wali kelas dalam satu ringkasan rombel.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="hero-summary">
                        <div>
                            <small>Tahun pelajaran</small>
                            <strong>{{ $year->nama }}</strong>
                            <span><i class="fas fa-circle mr-1"></i>{{ $activeYear && $activeYear->id === $year->id ? 'Tahun aktif' : 'Arsip historis' }}</span>
                        </div>
                        <div class="hero-period">
                            <small>Periode analisis</small>
                            <strong>{{ $start->translatedFormat('d M') }} – {{ $end->translatedFormat('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary filter-card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter text-primary mr-2"></i>Filter Analitik</h3>
            <div class="card-tools">
                <a href="{{ route('admin.absensi-siswa.analytics') }}" class="btn btn-default btn-sm"><i class="fas fa-undo mr-1"></i> Reset</a>
            </div>
        </div>
        <form method="GET">
            <div class="card-body pb-2">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="attendance-year">Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" id="attendance-year" class="form-control">
                                @foreach($years as $item)
                                    <option value="{{ $item->id }}" @selected($item->id === $year->id)>{{ $item->nama }}{{ $item->is_active ? ' · Aktif' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($isWaliScope)
                        <div class="col-lg-5 col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-lock mr-1"></i>Cakupan Rombel</label>
                                <div class="attendance-scope-lock">
                                    <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
                                    <span>{{ $classes->map(fn ($class) => $class->nama_kelas.$class->asrama_suffix)->implode(', ') }}</span>
                                </div>
                                <small class="text-muted">Ditentukan dari penugasan wali kelas aktif Anda.</small>
                            </div>
                        </div>
                    @else
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="attendance-level">Tingkat</label>
                                <select name="tingkat" id="attendance-level" class="form-control">
                                    <option value="">Semua tingkat</option>
                                    @foreach([10, 11, 12] as $level)
                                        <option value="{{ $level }}" @selected($tingkat === $level)>Kelas {{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label for="attendance-class">Kelas</label>
                                <select name="kelas_id" id="attendance-class" class="form-control">
                                    <option value="">Semua kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" data-level="{{ $class->tingkat }}" @selected($classId === $class->id)>{{ $class->nama_kelas }}{{ $class->asrama_suffix }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-4 col-md-6">
                        <label>Rentang Tanggal</label>
                        <div class="row no-gutters date-range">
                            <div class="col date-start"><input type="date" name="start_date" value="{{ $start->toDateString() }}" class="form-control" aria-label="Tanggal mulai"></div>
                            <div class="col date-end"><input type="date" name="end_date" value="{{ $end->toDateString() }}" class="form-control" aria-label="Tanggal selesai"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between">
                <small class="text-muted mb-2 mb-sm-0"><i class="fas fa-info-circle mr-1"></i>Analisis hanya memakai sesi yang sudah difinalkan.</small>
                <div class="filter-actions">
                    @if($isWaliScope)
                        <a href="{{ route('admin.gtk.wali.absensi.index') }}" class="btn btn-default"><i class="fas fa-clipboard-check mr-1"></i> Absensi Harian</a>
                    @endif
                    <button class="btn btn-primary"><i class="fas fa-chart-bar mr-1"></i> Terapkan Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="metric-grid mb-3">
        <article class="metric-card">
            <div class="metric-icon primary"><i class="fas fa-book-open"></i></div>
            <div><span>Sesi mapel final</span><strong>{{ number_format($kpi['subject_sessions']) }}</strong><small>Dari {{ number_format($kpi['sessions']) }} sesi final</small></div>
        </article>
        <article class="metric-card">
            <div class="metric-icon success"><i class="fas fa-percentage"></i></div>
            <div><span>Kehadiran mapel</span><strong>{{ number_format($kpi['attendance_rate'], 1) }}%</strong><small>{{ number_format($kpi['records']) }} catatan kehadiran</small></div>
        </article>
        <article class="metric-card">
            <div class="metric-icon info"><i class="fas fa-camera"></i></div>
            <div><span>Presensi harian</span><strong>{{ number_format($kpi['daily_records']) }}</strong><small>{{ number_format($kpi['daily_attendance_rate'], 1) }}% hadir/terlambat</small></div>
        </article>
        <article class="metric-card">
            <div class="metric-icon purple"><i class="fas fa-sticky-note"></i></div>
            <div><span>Catatan wali kelas</span><strong>{{ number_format($kpi['student_notes']) }}</strong><small>{{ $kpi['students_with_notes'] }} siswa · {{ $kpi['important_notes'] }} penting</small></div>
        </article>
        <article class="metric-card">
            <div class="metric-icon warning"><i class="fas fa-exclamation-triangle"></i></div>
            <div><span>Perlu ditindaklanjuti</span><strong>{{ number_format($kpi['active_alerts']) }}</strong><small>{{ $kpi['high_alerts'] }} prioritas tinggi</small></div>
        </article>
    </div>

    <div class="row">
        <div class="col-xl-5 mb-3">
            <div class="card card-outline card-primary h-100 analysis-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie text-primary mr-2"></i>Distribusi Status</h3>
                    <div class="card-tools"><span class="badge badge-light">{{ number_format($kpi['records']) }} catatan</span></div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Akumulasi status kehadiran pada filter aktif.</p>
                    @php($statusMeta = ['hadir' => ['Hadir', 'success'], 'terlambat' => ['Terlambat', 'warning'], 'izin' => ['Izin', 'primary'], 'sakit' => ['Sakit', 'purple'], 'alpa' => ['Alpa', 'danger'], 'dispen' => ['Dispensasi', 'info'], 'keluar_awal' => ['Keluar awal', 'orange']])
                    <div class="status-list">
                        @foreach($statusMeta as $code => [$label, $tone])
                            @php($count = (int) ($statusCounts[$code] ?? 0))
                            @php($percentage = $kpi['records'] > 0 ? round(($count / $kpi['records']) * 100, 1) : 0)
                            <div class="status-item">
                                <div class="status-label"><i class="status-dot {{ $tone }}"></i><span>{{ $label }}</span></div>
                                <div class="status-value"><strong>{{ number_format($count) }}</strong><small>{{ number_format($percentage, 1) }}%</small></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="context-note"><i class="fas fa-info-circle"></i><span>Hadir, terlambat, dan keluar awal dihitung sebagai kehadiran. Keterlambatan tetap dianalisis terpisah.</span></div>
                </div>
            </div>
        </div>
        <div class="col-xl-7 mb-3">
            <div class="card card-outline card-primary h-100 analysis-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb text-warning mr-2"></i>Smart Suggestion</h3>
                    <div class="card-tools">
                        @can('manage-attendance-alerts')
                            <button id="generate-insights" class="btn btn-primary btn-sm"><i class="fas fa-magic mr-1"></i> Analisis Ulang</button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Indikasi berbasis pola kehadiran dan bukti pendukung, bukan keputusan otomatis.@if($lastAnalysis) Terakhir diperbarui {{ $lastAnalysis->created_at->diffForHumans() }}.@endif</p>
                    <div class="alert-list">
                        @forelse($alerts as $alert)
                            <article class="smart-alert severity-{{ $alert->severity }}">
                                <div class="alert-main">
                                    <div class="alert-badges"><span>{{ strtoupper($alert->severity) }}</span><span>{{ strtoupper($alert->status) }}</span></div>
                                    <h4>{{ $alert->siswa?->nama_lengkap }}</h4>
                                    <strong>{{ $alert->title }}</strong>
                                    <p>{{ $alert->explanation }}</p>
                                </div>
                                <div class="alert-actions">
                                    <a href="{{ route('admin.absensi-siswa.analytics.student', $alert->siswa_id) }}" class="btn btn-default btn-sm">Detail</a>
                                    @can('manage-attendance-alerts')
                                        <button class="btn btn-primary btn-sm review-alert" data-id="{{ $alert->id }}" data-status="{{ $alert->status }}" data-notes="{{ e($alert->review_notes) }}">Tindak lanjut</button>
                                    @endcan
                                </div>
                            </article>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-chart-line"></i></div>
                                <strong>Belum ada indikator aktif</strong>
                                <span>Indikator akan muncul setelah sesi presensi difinalkan dan data dianalisis.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary student-summary-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users text-primary mr-2"></i>Ringkasan per Siswa</h3>
            <div class="card-tools"><span class="badge badge-light">Maksimal 100 siswa</span></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover attendance-table mb-0">
                    <thead><tr><th>Siswa</th><th>Catatan Kehadiran</th><th>Kehadiran</th><th>Alpa</th><th>Terlambat</th><th>Catatan Wali</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($studentRows as $student)
                            <tr>
                                <td><strong>{{ $student->nama_lengkap }}</strong><small>NISN {{ $student->nisn ?: '-' }}</small></td>
                                <td>{{ $student->total_records }}</td>
                                <td><span class="rate-pill {{ $student->attendance_rate < 85 ? 'risk' : '' }}">{{ number_format($student->attendance_rate, 1) }}%</span></td>
                                <td>{{ $student->alpa }}</td>
                                <td>{{ $student->terlambat }}</td>
                                <td><strong>{{ $student->note_count }}</strong>@if($student->important_note_count)<small class="text-danger">{{ $student->important_note_count }} penting</small>@elseif($student->latest_note_date)<small>Terakhir {{ \Carbon\Carbon::parse($student->latest_note_date)->translatedFormat('d M Y') }}</small>@endif</td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        @if($isWaliScope)<a class="btn btn-success" href="{{ route('admin.gtk.wali.catatan.index', ['kelas_id' => $student->kelas_id, 'siswa_id' => $student->id]) }}"><i class="fas fa-sticky-note mr-1"></i> Catatan</a>@endif
                                        <a class="btn btn-primary" href="{{ route('admin.absensi-siswa.analytics.student', $student->id) }}"><i class="fas fa-chart-line mr-1"></i> Riwayat</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state compact"><div class="empty-icon"><i class="fas fa-users"></i></div><strong>Belum ada data siswa</strong><span>Sesuaikan filter atau finalkan sesi presensi terlebih dahulu.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.attendance-analytics-page{color:#172033}.attendance-analytics-page .attendance-hero{border:0;border-radius:16px;box-shadow:0 10px 24px rgba(37,99,235,.14)}.attendance-analytics-page .hero-kicker{font-size:.75rem;font-weight:700;letter-spacing:.04em}.attendance-analytics-page .hero-title{font-size:1.55rem;font-weight:700}.attendance-analytics-page .hero-description{font-size:.95rem;opacity:.92}.attendance-analytics-page .hero-summary{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:12px 14px;border:1px solid rgba(255,255,255,.28);border-radius:12px;background:rgba(255,255,255,.12)}.attendance-analytics-page .hero-summary small,.attendance-analytics-page .hero-summary strong,.attendance-analytics-page .hero-summary span{display:block}.attendance-analytics-page .hero-summary small{font-size:.72rem;opacity:.85}.attendance-analytics-page .hero-summary strong{font-size:1rem}.attendance-analytics-page .hero-summary span{font-size:.72rem}.attendance-analytics-page .hero-summary span i{font-size:.5rem;color:#5cf08b}.attendance-analytics-page .filter-card,.attendance-analytics-page .analysis-card,.attendance-analytics-page .student-summary-card{border-radius:14px;box-shadow:0 8px 20px rgba(15,23,42,.05)}.attendance-analytics-page .filter-card .card-header,.attendance-analytics-page .analysis-card .card-header,.attendance-analytics-page .student-summary-card .card-header{background:#fff;border-radius:14px 14px 0 0}.attendance-analytics-page .filter-card label{font-size:.75rem;font-weight:700;text-transform:uppercase;color:#52627a}.attendance-analytics-page .date-range .form-control{min-width:0}.attendance-analytics-page .metric-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.attendance-analytics-page .metric-card{display:flex;align-items:center;gap:12px;min-height:112px;padding:16px;background:#fff;border:1px solid #dce5f2;border-radius:14px;box-shadow:0 7px 18px rgba(15,23,42,.04)}.attendance-analytics-page .metric-icon{display:grid;place-items:center;width:42px;height:42px;flex:0 0 42px;border-radius:11px;font-size:1rem}.attendance-analytics-page .metric-icon.primary{color:#2563eb;background:#eaf0ff}.attendance-analytics-page .metric-icon.success{color:#168451;background:#e7f8ef}.attendance-analytics-page .metric-icon.info{color:#0f766e;background:#e5f7f5}.attendance-analytics-page .metric-icon.purple{color:#7548c8;background:#f1ebff}.attendance-analytics-page .metric-icon.warning{color:#b96a00;background:#fff2df}.attendance-analytics-page .metric-card span,.attendance-analytics-page .metric-card small{display:block;color:#64748b}.attendance-analytics-page .metric-card span{font-size:.8rem;font-weight:600}.attendance-analytics-page .metric-card strong{display:block;font-size:1.55rem;line-height:1.15;color:#111827}.attendance-analytics-page .metric-card small{font-size:.72rem;line-height:1.35}.attendance-analytics-page .analysis-card{min-height:365px}.attendance-analytics-page .status-list{display:grid;grid-template-columns:1fr 1fr;column-gap:22px}.attendance-analytics-page .status-item{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #edf1f6}.attendance-analytics-page .status-label{display:flex;align-items:center}.attendance-analytics-page .status-dot{width:9px;height:9px;margin-right:9px;border-radius:50%}.attendance-analytics-page .status-dot.success{background:#28a56a}.attendance-analytics-page .status-dot.warning{background:#e99a00}.attendance-analytics-page .status-dot.primary{background:#3b82f6}.attendance-analytics-page .status-dot.purple{background:#8b5cf6}.attendance-analytics-page .status-dot.danger{background:#dc3545}.attendance-analytics-page .status-dot.info{background:#0f9e9a}.attendance-analytics-page .status-dot.orange{background:#e97835}.attendance-analytics-page .status-value{display:flex;align-items:center;gap:7px}.attendance-analytics-page .status-value small{min-width:38px;text-align:right;color:#8795aa}.attendance-analytics-page .context-note{display:flex;gap:9px;margin-top:14px;padding:10px 12px;border-radius:9px;background:#eef5ff;color:#3b5685;font-size:.78rem}.attendance-analytics-page .alert-list{max-height:315px;overflow:auto}.attendance-analytics-page .smart-alert{display:flex;justify-content:space-between;gap:12px;padding:13px;margin-bottom:9px;border:1px solid #e4eaf2;border-left:4px solid #e5a000;border-radius:10px}.attendance-analytics-page .smart-alert.severity-high{border-left-color:#dc3545}.attendance-analytics-page .smart-alert.severity-low{border-left-color:#28a56a}.attendance-analytics-page .alert-main h4{font-size:1rem;font-weight:700;margin:5px 0 2px}.attendance-analytics-page .alert-main>strong{font-size:.85rem}.attendance-analytics-page .alert-main p{font-size:.8rem;color:#65758d;margin:2px 0}.attendance-analytics-page .alert-badges span{display:inline-block;padding:2px 7px;margin-right:4px;border-radius:12px;background:#f1f4f8;font-size:.62rem;font-weight:700}.attendance-analytics-page .alert-actions{display:flex;gap:6px;align-items:center;white-space:nowrap}.attendance-analytics-page .empty-state{display:flex;min-height:230px;flex-direction:column;align-items:center;justify-content:center;padding:20px;text-align:center;color:#718096}.attendance-analytics-page .empty-state.compact{min-height:145px}.attendance-analytics-page .empty-icon{display:grid;place-items:center;width:46px;height:46px;margin-bottom:10px;border-radius:50%;background:#eef3ff;color:#3970e6;font-size:1.1rem}.attendance-analytics-page .empty-state span{max-width:420px;font-size:.83rem}.attendance-analytics-page .attendance-table thead th{border-top:0;background:#f6f8fc;color:#53647e;font-size:.7rem;text-transform:uppercase;white-space:nowrap}.attendance-analytics-page .attendance-table td{vertical-align:middle}.attendance-analytics-page .attendance-table td small{display:block;color:#8795aa}.attendance-analytics-page .rate-pill{display:inline-block;padding:4px 9px;border-radius:14px;background:#e8f8ef;color:#168451;font-size:.78rem;font-weight:700}.attendance-analytics-page .rate-pill.risk{background:#fff0e5;color:#bd6200}
.attendance-analytics-page .filter-card .card-footer{align-items:center}.attendance-analytics-page .filter-actions{display:flex;gap:6px}.attendance-analytics-page .date-start{padding-right:4px}.attendance-analytics-page .date-end{padding-left:4px}.attendance-analytics-page .attendance-scope-lock{display:flex;align-items:center;min-height:38px;gap:9px;padding:8px 11px;border:1px solid #cfe0fd;border-radius:.25rem;background:#f7faff;color:#244a86;font-size:.88rem;font-weight:600;line-height:1.25}.attendance-analytics-page .attendance-scope-lock i{color:#2563eb}.attendance-analytics-page .filter-card .form-group small{display:block;margin-top:4px;font-size:.72rem}
@media(max-width:1199px){.attendance-analytics-page .metric-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.attendance-analytics-page .analysis-card{min-height:auto}}
@media(max-width:767px){.attendance-analytics-page .hero-summary{grid-template-columns:1fr}.attendance-analytics-page .metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.attendance-analytics-page .status-list{grid-template-columns:1fr}.attendance-analytics-page .smart-alert{flex-direction:column}.attendance-analytics-page .alert-actions{justify-content:flex-end}.attendance-analytics-page .filter-card .card-footer{align-items:stretch;flex-direction:column}.attendance-analytics-page .filter-actions{display:flex}.attendance-analytics-page .filter-card .card-footer .btn{flex:1}}
@media(max-width:480px){.attendance-analytics-page .metric-grid{grid-template-columns:1fr}.attendance-analytics-page .metric-card{min-height:96px}.attendance-analytics-page .date-range{display:block}.attendance-analytics-page .date-range .col{padding:0;margin-bottom:8px}.attendance-analytics-page .filter-actions{flex-direction:column}.attendance-analytics-page .filter-card .card-footer .btn{width:100%}.attendance-analytics-page .btn-group{display:flex;flex-direction:column}.attendance-analytics-page .btn-group .btn{margin-bottom:4px}}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const level = document.getElementById('attendance-level');
    const classSelect = document.getElementById('attendance-class');
    const filterClasses = () => {
        if (!level || !classSelect) return;
        const selected = level.value;
        Array.from(classSelect.options).forEach((option, index) => {
            if (index > 0) option.hidden = Boolean(selected) && option.dataset.level !== selected;
        });
        if (classSelect.selectedOptions[0]?.hidden) classSelect.value = '';
    };
    level?.addEventListener('change', filterClasses);
    filterClasses();

    const csrf = '{{ csrf_token() }}';
    document.getElementById('generate-insights')?.addEventListener('click', async function () {
        const result = await Swal.fire({title:'Analisis ulang kehadiran?',text:'Sistem membaca sesi final tahun aktif dan memperbarui indikator berbasis aturan.',icon:'question',showCancelButton:true,confirmButtonText:'Ya, analisis',cancelButtonText:'Batal'});
        if (!result.isConfirmed) return;
        Swal.fire({title:'Menganalisis data...',allowOutsideClick:false,didOpen:() => Swal.showLoading()});
        try {
            const response = await fetch('{{ route('admin.absensi-siswa.analytics.generate') }}', {method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
            const json = await response.json();
            if (!response.ok) throw new Error(json.message || 'Gagal menganalisis data.');
            await Swal.fire({icon:'success',title:'Analisis selesai',text:json.message,timer:1600,showConfirmButton:false});
            location.reload();
        } catch (error) {
            Swal.fire('Gagal', error.message, 'error');
        }
    });

    document.querySelectorAll('.review-alert').forEach(button => button.addEventListener('click', async function () {
        const notes = this.dataset.notes || '';
        const result = await Swal.fire({title:'Tindak lanjut indikator',html:`<select id="alert-status" class="swal2-select"><option value="new">Baru</option><option value="reviewed">Sudah ditinjau</option><option value="monitoring">Dalam pemantauan</option><option value="resolved">Selesai</option><option value="dismissed">Diabaikan</option></select><textarea id="alert-notes" class="swal2-textarea" placeholder="Catatan tindak lanjut">${notes.replace(/</g, '&lt;')}</textarea>`,showCancelButton:true,confirmButtonText:'Simpan',cancelButtonText:'Batal',didOpen:() => document.getElementById('alert-status').value = this.dataset.status,preConfirm:() => ({status:document.getElementById('alert-status').value,review_notes:document.getElementById('alert-notes').value})});
        if (!result.isConfirmed) return;
        try {
            const response = await fetch(`{{ url('/admin/absensi-siswa/analitik/alert') }}/${this.dataset.id}`, {method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(result.value)});
            const json = await response.json();
            if (!response.ok) throw new Error(json.message || 'Gagal menyimpan.');
            await Swal.fire({icon:'success',title:'Tersimpan',text:json.message,timer:1300,showConfirmButton:false});
            location.reload();
        } catch (error) {
            Swal.fire('Gagal', error.message, 'error');
        }
    }));
});
</script>
@stop
