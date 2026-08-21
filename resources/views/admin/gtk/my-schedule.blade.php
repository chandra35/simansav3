@extends('adminlte::page')

@section('title', 'Jadwal Saya - SIMANSA')

@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-calendar-alt text-primary"></i> Jadwal Saya</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li><li class="breadcrumb-item active">Jadwal Saya</li></ol></div></div>
@stop

@section('content')
<div class="gtk-my-schedule">
    <div class="card bg-gradient-primary text-white mb-4"><div class="card-body py-3"><div class="d-flex flex-wrap align-items-center justify-content-between"><div><small class="text-white-50 font-weight-bold">JADWAL MENGAJAR</small><h3 class="mb-1">{{ $gtk->nama_lengkap }}</h3><span>{{ $tahunAktif?->nama ?? 'Tahun pelajaran belum aktif' }}</span></div><a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-light btn-sm mt-2 mt-sm-0"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a></div></div></div>
    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Jadwal Mingguan</h3></div><div class="card-body">
        @if($schedulesByDay->isNotEmpty())<div class="row">@foreach($dayLabels as $day => $label)<div class="col-md-6 col-xl-4 mb-3"><section class="gtk-my-schedule__day"><header>{{ $label }} <span>{{ ($schedulesByDay[$day] ?? collect())->count() }} slot</span></header><div>@forelse($schedulesByDay[$day] ?? collect() as $schedule)<article><time>{{ $schedule->jam }}</time><div><strong>{{ $schedule->mataPelajaran?->nama_mapel ?? 'Mata pelajaran' }}</strong><span>{{ $schedule->kelas?->nama_kelas ?? '-' }}{{ $schedule->ruangan ?: ($schedule->kelas?->ruang_kelas ? ' · '.$schedule->kelas->ruang_kelas : '') }}</span></div></article>@empty<span class="gtk-my-schedule__empty">Tidak ada jadwal</span>@endforelse</div></section></div>@endforeach</div>@else<div class="text-center text-muted py-5"><i class="far fa-calendar-times fa-3x mb-3 d-block"></i>Belum ada jadwal mengajar aktif pada tahun pelajaran ini.</div>@endif
    </div></div>
</div>
@stop

@section('css')
<style>
.gtk-my-schedule__day{height:100%;overflow:hidden;border:1px solid #dbe3ef;border-radius:.65rem;background:#fff}.gtk-my-schedule__day header{display:flex;justify-content:space-between;padding:.7rem .8rem;background:#eff6ff;color:#1e40af;font-size:.86rem;font-weight:700}.gtk-my-schedule__day header span{font-size:.72rem;font-weight:600}.gtk-my-schedule__day article{display:grid;grid-template-columns:92px minmax(0,1fr);gap:.65rem;padding:.72rem .8rem;border-top:1px solid #eef2f7}.gtk-my-schedule__day time{color:#2563eb;font-size:.76rem;font-weight:700}.gtk-my-schedule__day strong,.gtk-my-schedule__day span{display:block}.gtk-my-schedule__day strong{color:#0f172a;font-size:.83rem}.gtk-my-schedule__day article span,.gtk-my-schedule__empty{color:#64748b;font-size:.74rem}.gtk-my-schedule__empty{display:block;padding:1rem;text-align:center}@media(max-width:575.98px){.gtk-my-schedule__day article{grid-template-columns:78px minmax(0,1fr)}}
</style>
@stop
