@extends('adminlte::page')

@section('title', 'Jadwal Kelas — Kelas Saya')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-calendar-alt"></i> Kelas Saya</div>
            <h1 class="simansa-hero__title">Jadwal Kelas</h1>
            <p class="simansa-hero__subtitle">Jadwal pelajaran {{ $kelas->nama_kelas }}@if($hasGtk) &amp; jadwal mengajar Anda @endif.</p>
        </div>
    </div>
@stop

@section('content')
    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.jadwal.index'])

    <div class="card simansa-management-card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard"></i> Jadwal Pelajaran {{ $kelas->nama_kelas }}</h3></div>
        <div class="card-body">
            @php $adaJadwalKelas = $jadwalKelas->flatten()->isNotEmpty(); @endphp
            @if($adaJadwalKelas)
                <div class="row">
                    @foreach($hariList as $key => $label)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <h6 class="font-weight-600 text-primary border-bottom pb-1">{{ $label }}</h6>
                            @php $items = ($jadwalKelas[$key] ?? collect())->sortBy('jam_ke'); @endphp
                            @forelse($items as $j)
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <div>
                                        <span class="badge badge-light">Jam {{ $j->jam_ke }}</span>
                                        {{ optional($j->mataPelajaran)->nama_mapel ?? '—' }}
                                    </div>
                                    <small class="text-muted">{{ optional($j->gtk->user ?? null)->name ?? '' }}</small>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Tidak ada.</p>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <p class="mb-0">Belum ada jadwal pelajaran untuk rombel ini.</p>
                </div>
            @endif
        </div>
    </div>

    @if($hasGtk)
        <div class="card simansa-management-card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Jadwal Mengajar Saya</h3></div>
            <div class="card-body">
                @if($jadwalMengajar->flatten()->isNotEmpty())
                    <div class="row">
                        @foreach($hariList as $key => $label)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <h6 class="font-weight-600 text-primary border-bottom pb-1">{{ $label }}</h6>
                                @php $items = ($jadwalMengajar[$key] ?? collect())->sortBy('jam_ke'); @endphp
                                @forelse($items as $j)
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <div>
                                            <span class="badge badge-light">Jam {{ $j->jam_ke }}</span>
                                            {{ optional($j->mataPelajaran)->nama_mapel ?? '—' }}
                                        </div>
                                        <small class="text-muted">{{ optional($j->kelas)->nama_kelas ?? '' }}</small>
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0">Tidak ada.</p>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada jadwal mengajar tercatat.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
@stop
