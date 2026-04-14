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
    .envelope-gate {
        margin-top: 18px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
        padding: 22px;
        box-shadow: 0 10px 24px rgba(15,23,42,.25);
    }
    .envelope-status {
        border-radius: 999px;
        padding: 4px 12px;
        font-size: .78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .envelope-status.closed { background: rgba(248,113,113,.22); color: #fecaca; }
    .envelope-status.opened { background: rgba(74,222,128,.2); color: #bbf7d0; }

    .envelope-scene {
        position: relative;
        width: 250px;
        height: 178px;
        margin: 8px auto 14px;
        cursor: pointer;
        transition: transform .2s ease;
        outline: none;
    }
    .envelope-scene:hover { transform: translateY(-3px) scale(1.02); }
    .envelope-scene:focus-visible {
        box-shadow: 0 0 0 4px rgba(125, 211, 252, .45);
        border-radius: 14px;
    }
    .mail-envelope {
        position: absolute;
        left: 50%;
        top: 40px;
        transform: translateX(-50%);
        width: 220px;
        height: 128px;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,.32));
    }
    .mail-body {
        position: absolute;
        inset: 0;
        border-radius: 12px;
        background: linear-gradient(145deg, #4338ca, #2563eb 55%, #0891b2);
    }
    .mail-flap {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 78px;
        transform-origin: top;
        clip-path: polygon(0 0, 50% 100%, 100% 0);
        background: linear-gradient(145deg, #67e8f9, #3b82f6 55%, #4f46e5);
        transition: transform .9s cubic-bezier(.2,.75,.15,1);
        z-index: 4;
    }
    .mail-letter {
        position: absolute;
        left: 18px;
        right: 18px;
        top: 16px;
        height: 94px;
        background: #fff;
        border-radius: 8px;
        z-index: 2;
        padding: 10px 12px;
        transform: translateY(0);
        transition: transform .9s cubic-bezier(.2,.75,.15,1);
    }
    .mail-line {
        height: 6px;
        border-radius: 99px;
        background: #dbeafe;
        margin-bottom: 7px;
    }
    .mail-line.short { width: 60%; }
    .mail-seal {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #fcd34d, #f59e0b);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        font-size: .85rem;
    }
    .mail-glow {
        position: absolute;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,.65);
        animation: glowPulse 2s ease-in-out infinite;
    }
    .mail-glow.g1 { top: 8px; left: 22px; }
    .mail-glow.g2 { top: 18px; right: 22px; animation-delay: .45s; }
    .mail-glow.g3 { top: 4px; left: 50%; animation-delay: .9s; }
    @keyframes glowPulse {
        0%, 100% { transform: scale(1); opacity: .35; }
        50% { transform: scale(1.45); opacity: .95; }
    }

    .envelope-gate.opening .mail-flap {
        transform: rotateX(180deg);
    }
    .envelope-gate.opening .mail-letter {
        transform: translateY(-58px);
    }
    .envelope-gate.opening .mail-seal {
        opacity: 0;
        transition: opacity .25s ease;
    }
    .announcement-wrapper {
        opacity: 0;
        transform: translateY(16px);
        transition: opacity .55s ease, transform .55s ease;
    }
    .announcement-wrapper.show {
        opacity: 1;
        transform: translateY(0);
    }

    .envelope-gate.fade-out {
        opacity: 0;
        transform: translateY(-8px) scale(.98);
        transition: all .35s ease;
    }
    .envelope-click-hint {
        text-align: center;
        color: #bae6fd;
        font-size: .84rem;
        letter-spacing: .2px;
    }
    .envelope-click-hint .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #38bdf8;
        margin-right: 7px;
        animation: dotBlink 1.3s ease-in-out infinite;
    }
    @keyframes dotBlink {
        0%,100% { opacity: .35; transform: scale(.8); }
        50% { opacity: 1; transform: scale(1.2); }
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
    $isOpened = !empty($peserta->pengumuman_dibuka_at);
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="announcement-wrapper {{ $isOpened ? 'show' : '' }}" id="announcementWrapper" style="display: {{ $isOpened ? 'block' : 'none' }};">
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
                    <span class="icon"><i class="fas {{ $isOpened ? 'fa-envelope-open-text' : 'fa-envelope' }}" id="openStatusIcon"></i></span>
                    <div>
                        <div class="title">Status Amplop Pengumuman</div>
                        <div class="value" id="openStatusText">{{ $isOpened ? 'Pengumuman sudah Anda buka' : 'Amplop belum dibuka' }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="title">Waktu Buka Pertama</div>
                    <div class="value" id="openedAtText">{{ optional($peserta->pengumuman_dibuka_at)->format('d M Y, H:i') ?? '-' }}</div>
                </div>
            </div>

            <div id="announcementContent" style="display: block;">
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

        <div class="envelope-gate" id="envelopeGate" style="display: {{ $isOpened ? 'none' : 'block' }};">
            <div class="d-flex flex-wrap align-items-center justify-content-center" style="gap:12px;">
                <div class="text-center">
                    <div class="envelope-status closed" id="envelopeBadge">
                        <i class="fas fa-envelope"></i> Belum Dibuka
                    </div>
                    <div class="envelope-scene" id="envelopeScene" role="button" tabindex="0" aria-label="Buka amplop pengumuman SMART-Q">
                        <span class="mail-glow g1"></span>
                        <span class="mail-glow g2"></span>
                        <span class="mail-glow g3"></span>
                        <div class="mail-envelope">
                            <div class="mail-body"></div>
                            <div class="mail-letter">
                                <div class="mail-line"></div>
                                <div class="mail-line"></div>
                                <div class="mail-line short"></div>
                            </div>
                            <div class="mail-flap"></div>
                            <div class="mail-seal"><i class="fas fa-star"></i></div>
                        </div>
                    </div>
                    <div class="envelope-click-hint" id="envelopeClickHint">
                        <span class="pulse-dot"></span>Klik amplop untuk membuka pengumuman
                    </div>
                    <h5 class="mt-2 mb-1" style="font-weight:800;">Anda memiliki 1 amplop pengumuman SMART-Q</h5>
                    <p class="mb-0" style="color:#cbd5e1;">Klik amplop untuk membuka pengumuman. Sistem akan menandai pembukaan ini secara otomatis.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var envelopeScene = document.getElementById('envelopeScene');
    var gate = document.getElementById('envelopeGate');
    var wrapper = document.getElementById('announcementWrapper');
    var hint = document.getElementById('envelopeClickHint');
    if (!envelopeScene || !gate || !wrapper) return;

    var isSubmitting = false;

    function startOpenEnvelope() {
        if (isSubmitting) return;
        isSubmitting = true;
        if (hint) {
            hint.innerHTML = '<span class="pulse-dot"></span>Membuka amplop...';
        }
        envelopeScene.style.pointerEvents = 'none';
        gate.classList.add('opening');

        fetch('{{ route('siswa.smartq.open-envelope') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(function (res) { return res.json(); })
        .then(function (res) {
            if (!res.success) throw new Error(res.message || 'Gagal membuka amplop');

            setTimeout(function () {
                gate.classList.add('fade-out');
                wrapper.style.display = 'block';
                requestAnimationFrame(function () {
                    wrapper.classList.add('show');
                });
                setTimeout(function () {
                    gate.style.display = 'none';
                }, 360);
            }, 780);

            document.getElementById('openStatusText').textContent = 'Pengumuman sudah Anda buka';
            document.getElementById('openedAtText').textContent = res.opened_at || '-';
            var icon = document.getElementById('openStatusIcon');
            if (icon) {
                icon.classList.remove('fa-envelope');
                icon.classList.add('fa-envelope-open-text');
            }
        })
        .catch(function (err) {
            alert(err.message || 'Terjadi kesalahan. Silakan coba lagi.');
            isSubmitting = false;
            envelopeScene.style.pointerEvents = 'auto';
            if (hint) {
                hint.innerHTML = '<span class="pulse-dot"></span>Klik amplop untuk membuka pengumuman';
            }
            gate.classList.remove('opening');
        });
    }

    envelopeScene.addEventListener('click', startOpenEnvelope);
    envelopeScene.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            startOpenEnvelope();
        }
    });
});
</script>
@stop
