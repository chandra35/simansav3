@extends('adminlte::page')

@section('title', 'Ekstrakurikuler Saya')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-futbol text-primary mr-2"></i>Ekstrakurikuler Saya</h1>
            <small class="text-muted">Daftar kegiatan dan keikutsertaan ekstrakurikuler Anda</small>
        </div>
        <span class="badge badge-primary px-3 py-2 mt-2 mt-md-0"><i class="fas fa-users mr-1"></i>{{ $anggota->count() }} Kegiatan</span>
    </div>
@stop

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div><h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ul text-primary mr-2"></i>Keikutsertaan Saya</h5><small class="text-muted">Informasi kegiatan yang sedang diikuti</small></div>
        </div>
        <div class="card-body">
            @forelse ($anggota as $item)
                @php($ekskul = $item->ekstrakurikuler)
                <div class="card border mb-3 shadow-none">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div><h5 class="font-weight-bold text-dark mb-1">{{ $ekskul?->nama ?? '-' }}</h5><span class="badge badge-light text-muted">{{ ucfirst($ekskul?->kategori ?? 'Kegiatan') }}</span></div>
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                        </div>
                        <p class="text-muted small mb-3">{{ $ekskul?->deskripsi ?: 'Tidak ada deskripsi kegiatan.' }}</p>
                        <div class="row small border-top pt-3">
                            <div class="col-md-3 mb-2 mb-md-0"><span class="text-muted d-block"><i class="fas fa-user-tie mr-1"></i>Pembina</span><strong>{{ $ekskul?->pembina?->nama ?? '-' }}</strong></div>
                            <div class="col-md-3 mb-2 mb-md-0"><span class="text-muted d-block"><i class="far fa-calendar-alt mr-1"></i>Jadwal</span><strong>{{ $ekskul?->hari_latihan ?: '-' }}</strong></div>
                            <div class="col-md-3 mb-2 mb-md-0"><span class="text-muted d-block"><i class="fas fa-map-marker-alt mr-1"></i>Tempat</span><strong>{{ $ekskul?->tempat ?: '-' }}</strong></div>
                            <div class="col-md-3"><span class="text-muted d-block"><i class="fas fa-user-tag mr-1"></i>Peran</span><strong>{{ $item->jabatan ?: 'Anggota' }}</strong></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="text-muted mb-3" style="font-size: 3rem;"><i class="fas fa-futbol"></i></div>
                    <h5 class="font-weight-bold text-dark">Belum Ada Ekstrakurikuler</h5>
                    <p class="text-muted mb-0">Data keikutsertaan ekstrakurikuler Anda belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
@stop
