@extends('adminlte::page')

@section('title', 'Jadwal Mengajar GTK')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-calendar-alt text-primary"></i> Jadwal Mengajar GTK</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.gtk.index') }}">Data GTK</a></li><li class="breadcrumb-item active">Jadwal Mengajar</li></ol></div>
</div>
@stop

@section('content')
<div class="gtk-schedule-page">
    <div class="card bg-gradient-primary text-white mb-3 gtk-schedule-hero"><div class="card-body py-3"><div class="row align-items-center"><div class="col-lg-8"><div class="d-flex align-items-center"><img src="{{ $gtk->foto_profile_url }}" alt="Foto {{ $gtk->nama_lengkap }}" class="gtk-schedule-avatar"><div class="ml-3"><small class="text-white-50 font-weight-bold">JADWAL MENGAJAR</small><h4 class="mb-1">{{ $gtk->nama_lengkap }}</h4><span>{{ $gtk->jenis_ptk ?: 'GTK' }}{{ $gtk->kode_gtk ? ' · '.$gtk->kode_gtk : '' }}{{ $gtk->nip ? ' · NIP '.$gtk->nip : '' }}</span></div></div></div><div class="col-lg-4 mt-3 mt-lg-0"><form method="GET"><label class="small mb-1">Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control" onchange="this.form.submit()">@forelse($years as $year)<option value="{{ $year->id }}" @selected($selectedYear?->id === $year->id)>{{ $year->nama }} · {{ $year->semester_aktif }}{{ $year->is_active ? ' (Aktif)' : '' }}</option>@empty<option>Belum tersedia</option>@endforelse</select></form></div></div></div></div>

    <div class="row">@foreach([['Slot Jadwal',$stats['slots'],'primary','calendar-check'],['Jam Pelajaran',$stats['periods'],'info','clock'],['Mata Pelajaran',$stats['subjects'],'success','book-open'],['Rombel Diampu',$stats['classes'],'warning','school']] as [$label,$value,$color,$icon])<div class="col-6 col-lg-3"><div class="info-box"><span class="info-box-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $label }}</span><span class="info-box-number">{{ number_format($value) }}</span></div></div></div>@endforeach</div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-1"></i> Jadwal {{ $selectedYear?->nama ?? 'Mengajar' }}</h3><div class="card-tools"><a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Data GTK</a></div></div><div class="card-body">
        @if($schedulesByDay->flatten()->isNotEmpty())
        <div class="row">@foreach($dayLabels as $day => $label)<div class="col-md-6 col-xl-4 mb-3"><section class="schedule-day"><header><span>{{ $label }}</span><span class="badge badge-light">{{ ($schedulesByDay[$day] ?? collect())->count() }} slot</span></header><div class="schedule-day-body">@forelse(($schedulesByDay[$day] ?? collect()) as $schedule)<article class="schedule-item"><div class="schedule-time"><strong>Jam {{ $schedule->jam_ke }}</strong><small>{{ $schedule->jam }}</small></div><div class="schedule-detail"><strong>{{ $schedule->mataPelajaran?->nama_mapel ?? 'Mata pelajaran tidak tersedia' }}</strong><span><i class="fas fa-school"></i> {{ $schedule->kelas?->nama_kelas ?? '-' }}</span><span><i class="fas fa-map-marker-alt"></i> {{ $schedule->ruangan ?: ($schedule->kelas?->ruang_kelas ?: '-') }} · Semester {{ $schedule->semester ?: '-' }}</span></div></article>@empty<div class="schedule-empty"><i class="far fa-calendar"></i> Tidak ada jadwal</div>@endforelse</div></section></div>@endforeach</div>
        @else
        <div class="empty-schedule"><i class="fas fa-calendar-times"></i><h5>Belum ada jadwal mengajar</h5><p>GTK ini belum memiliki jadwal aktif pada tahun pelajaran yang dipilih.</p></div>
        @endif
    </div></div>
</div>
@stop

@section('css')
<style>
.gtk-schedule-page .gtk-schedule-hero{border-radius:.7rem;box-shadow:0 .45rem 1rem rgba(37,99,235,.14)}.gtk-schedule-page .gtk-schedule-avatar{width:66px;height:82px;object-fit:cover;border-radius:.55rem;border:2px solid rgba(255,255,255,.85)}.gtk-schedule-page .info-box{min-height:80px}.gtk-schedule-page .info-box-icon{width:64px}.gtk-schedule-page .schedule-day{height:100%;border:1px solid #dbe3ef;border-radius:.6rem;overflow:hidden;background:#fff}.gtk-schedule-page .schedule-day>header{display:flex;justify-content:space-between;align-items:center;padding:.7rem .8rem;background:#eff6ff;color:#1e40af;font-weight:700;border-bottom:1px solid #dbeafe}.gtk-schedule-page .schedule-day-body{padding:.25rem .75rem}.gtk-schedule-page .schedule-item{display:grid;grid-template-columns:92px 1fr;gap:.7rem;padding:.7rem 0;border-bottom:1px solid #eef2f7}.gtk-schedule-page .schedule-item:last-child{border-bottom:0}.gtk-schedule-page .schedule-time strong,.gtk-schedule-page .schedule-time small,.gtk-schedule-page .schedule-detail strong,.gtk-schedule-page .schedule-detail span{display:block}.gtk-schedule-page .schedule-time strong{color:#2563eb;font-size:.78rem}.gtk-schedule-page .schedule-time small,.gtk-schedule-page .schedule-detail span{color:#64748b;font-size:.72rem}.gtk-schedule-page .schedule-detail strong{color:#0f172a;font-size:.82rem;margin-bottom:.2rem}.gtk-schedule-page .schedule-detail span i{width:14px}.gtk-schedule-page .schedule-empty{padding:1.25rem;text-align:center;color:#94a3b8;font-size:.78rem}.gtk-schedule-page .empty-schedule{text-align:center;padding:3rem 1rem;color:#64748b}.gtk-schedule-page .empty-schedule>i{font-size:2.4rem;color:#94a3b8;margin-bottom:.75rem}@media(max-width:575.98px){.gtk-schedule-page .schedule-item{grid-template-columns:78px 1fr}.gtk-schedule-page .gtk-schedule-avatar{width:55px;height:70px}.gtk-schedule-page .gtk-schedule-hero h4{font-size:1.05rem}}
</style>
@stop
