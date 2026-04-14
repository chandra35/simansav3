@extends('adminlte::page')

@section('title', 'Pengumuman SMART-Q - SIMANSA')

@section('css')
<style>
    .smartq-hero {
        border-radius: 16px;
        padding: 40px 30px;
        text-align: center;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,.2);
    }
    .smartq-hero.diterima {
        background: linear-gradient(135deg, #28a745, #20c997);
    }
    .smartq-hero.cadangan {
        background: linear-gradient(135deg, #f0ad4e, #e67e22);
    }
    .smartq-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
        animation: heroShimmer 6s ease-in-out infinite;
    }
    @keyframes heroShimmer {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(10%, 10%); }
    }
    .smartq-hero .icon-main {
        font-size: 4rem;
        margin-bottom: 15px;
        animation: heroPulse 2.5s ease-in-out infinite;
    }
    @keyframes heroPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: .85; }
    }
    .smartq-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
        text-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .smartq-hero .subtitle {
        font-size: 1.1rem;
        opacity: .9;
    }
    .bidang-card {
        border-radius: 12px;
        border: 2px solid;
        text-align: center;
        padding: 25px 20px;
        margin-top: 25px;
    }
    .bidang-card.diterima { border-color: #28a745; }
    .bidang-card.cadangan { border-color: #f0ad4e; }
    .bidang-card .bidang-label {
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .bidang-card .bidang-name {
        font-size: 1.6rem;
        font-weight: 800;
    }
    .bidang-card .bidang-code {
        font-size: .9rem;
        font-weight: 600;
        margin-top: 5px;
    }
    .open-track-card {
        margin-top: 16px;
        border: 1px solid #dbeafe;
        background: #f8fbff;
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .open-track-card .icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #dbeafe;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .open-track-card .title {
        font-size: .82rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .open-track-card .value {
        font-weight: 700;
        color: #0f172a;
        font-size: .92rem;
    }
</style>
@stop

@section('content_header')
    <div class="row">
        <div class="col-12">
            <h1 class="m-0"><i class="fas fa-star text-warning"></i> Pengumuman SMART-Q Kelas Unggulan</h1>
        </div>
    </div>
@stop

@section('content')
@php
    $isDiterima = $peserta->status === 'lulus';
    $statusLabel = $isDiterima ? 'DITERIMA' : 'CADANGAN';
    $statusClass = $isDiterima ? 'diterima' : 'cadangan';
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Hero Banner --}}
        <div class="smartq-hero {{ $statusClass }}">
            <div class="icon-main">
                @if($isDiterima)
                    <i class="fas fa-trophy"></i>
                @else
                    <i class="fas fa-hourglass-half"></i>
                @endif
            </div>
            <h1>
                @if($isDiterima)
                    Selamat, {{ $user->name }}!
                @else
                    Halo, {{ $user->name }}
                @endif
            </h1>
            <p class="subtitle">
                @if($isDiterima)
                    Anda dinyatakan <strong>DITERIMA</strong> di SMART-Q Kelas Unggulan
                @else
                    Anda masuk dalam daftar <strong>CADANGAN</strong> SMART-Q Kelas Unggulan
                @endif
            </p>
        </div>

        <div class="open-track-card">
            <div class="d-flex align-items-center" style="gap:10px;">
                <span class="icon"><i class="fas fa-envelope-open-text"></i></span>
                <div>
                    <div class="title">Status Amplop Pengumuman</div>
                    <div class="value">Pengumuman sudah Anda buka</div>
                </div>
            </div>
            <div class="text-right">
                <div class="title">Waktu Buka Pertama</div>
                <div class="value">{{ optional($peserta->pengumuman_dibuka_at)->format('d M Y, H:i') ?? '-' }}</div>
            </div>
        </div>

        {{-- Bidang Card --}}
        @if($peserta->bidangMapel)
            <div class="bidang-card {{ $statusClass }}">
                <div class="bidang-label text-muted">
                    <i class="fas fa-book"></i> Bidang Mapel Pilihan
                </div>
                <div class="bidang-name text-{{ $isDiterima ? 'success' : 'warning' }}">
                    {{ $peserta->bidangMapel->nama_mapel }}
                </div>
                <div class="bidang-code text-muted">
                    Kode: {{ $peserta->bidangMapel->kode_mapel }}
                </div>
            </div>
        @endif

        {{-- Info Card --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted small text-uppercase">Status</div>
                        <div class="mt-1">
                            @if($isDiterima)
                                <span class="badge badge-success px-3 py-2" style="font-size: 1rem;">
                                    <i class="fas fa-check-circle"></i> Diterima
                                </span>
                            @else
                                <span class="badge badge-warning px-3 py-2" style="font-size: 1rem;">
                                    <i class="fas fa-hourglass-half"></i> Cadangan
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small text-uppercase">No. Peserta</div>
                        <div class="mt-1 font-weight-bold" style="font-size: 1.1rem;">{{ $peserta->nomor_peserta }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small text-uppercase">Periode</div>
                        <div class="mt-1 font-weight-bold" style="font-size: 1.1rem;">{{ $peserta->periode->nama ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$isDiterima)
            <div class="alert alert-warning mt-3">
                <i class="fas fa-info-circle"></i>
                Status Anda saat ini adalah <strong>Cadangan</strong>. Jika ada peserta yang diterima mengundurkan diri, Anda berpeluang naik menjadi peserta diterima.
                Pantau terus informasi dari pihak madrasah.
            </div>
        @else
            <div class="alert alert-success mt-3">
                <i class="fas fa-info-circle"></i>
                Selamat atas penerimaan Anda! Silakan pantau informasi selanjutnya dari pihak madrasah mengenai jadwal dan ketentuan kelas unggulan.
            </div>
        @endif
    </div>
</div>
@stop
