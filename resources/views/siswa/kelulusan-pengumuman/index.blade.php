@extends('adminlte::page')

@section('title', 'Pengumuman Kelulusan')

@section('content_header')
    <div>
        <h1 class="mb-1">Pengumuman Kelulusan</h1>
        <p class="text-muted mb-0">{{ $tahunAktif->nama }} • {{ $kelasAktif->kelas->nama_kelas }}</p>
    </div>
@stop

@section('css')
<style>
    .graduation-stage {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, .25), transparent 30%),
            radial-gradient(circle at top left, rgba(59, 130, 246, .18), transparent 28%),
            linear-gradient(145deg, #0f172a, #1e3a8a 45%, #0f766e 100%);
        color: #fff;
        padding: 32px 24px;
        box-shadow: 0 30px 60px rgba(15, 23, 42, .24);
    }

    .graduation-stage::before,
    .graduation-stage::after {
        content: "";
        position: absolute;
        inset: auto;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        filter: blur(1px);
        animation: stageFloat 10s ease-in-out infinite;
    }

    .graduation-stage::before { top: -60px; right: -50px; }
    .graduation-stage::after { bottom: -90px; left: -50px; animation-delay: -4s; }

    @keyframes stageFloat {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, 18px, 0); }
    }

    .stage-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.16);
        font-weight: 600;
        font-size: .86rem;
        margin-bottom: 18px;
    }

    .stage-copy h2 {
        font-weight: 800;
        margin-bottom: 10px;
        font-size: clamp(1.9rem, 3vw, 2.8rem);
    }

    .stage-copy p {
        max-width: 700px;
        color: rgba(255,255,255,.82);
        margin-bottom: 0;
    }

    .envelope-panel {
        margin-top: 28px;
        border-radius: 22px;
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.14);
        padding: 28px 20px 24px;
        backdrop-filter: blur(10px);
    }

    .envelope-wrapper {
        display: grid;
        place-items: center;
        min-height: 320px;
    }

    .envelope-button {
        position: relative;
        width: min(360px, 100%);
        aspect-ratio: 1.2 / 1;
        background: transparent;
        border: 0;
        padding: 0;
        cursor: pointer;
    }

    .envelope-button:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 4px rgba(125, 211, 252, .45);
        border-radius: 24px;
    }

    .envelope-shell {
        position: absolute;
        inset: 32px 0 0;
        filter: drop-shadow(0 28px 40px rgba(15, 23, 42, .35));
    }

    .envelope-back {
        position: absolute;
        inset: 0;
        border-radius: 26px;
        background: linear-gradient(160deg, #f8fafc 0%, #dbeafe 45%, #bfdbfe 100%);
    }

    .envelope-front {
        position: absolute;
        inset: 0;
        border-radius: 26px;
        background: linear-gradient(145deg, #2563eb 0%, #1d4ed8 52%, #0f766e 100%);
        clip-path: polygon(0 28%, 50% 72%, 100% 28%, 100% 100%, 0 100%);
        z-index: 4;
    }

    .envelope-flap {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 52%;
        transform-origin: top center;
        clip-path: polygon(0 0, 100% 0, 50% 100%);
        border-radius: 26px 26px 0 0;
        background: linear-gradient(145deg, #60a5fa 0%, #3b82f6 52%, #1d4ed8 100%);
        z-index: 6;
        transition: transform 1s cubic-bezier(.22,.77,.21,1);
    }

    .envelope-letter {
        position: absolute;
        left: 9%;
        right: 9%;
        top: 11%;
        height: 72%;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        z-index: 3;
        padding: 18px;
        transform: translateY(0);
        transition: transform 1s cubic-bezier(.22,.77,.21,1);
    }

    .letter-line {
        height: 9px;
        border-radius: 999px;
        background: #dbeafe;
        margin-bottom: 10px;
    }

    .letter-line.short { width: 58%; }
    .letter-line.tiny { width: 40%; }

    .envelope-seal {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 72px;
        height: 72px;
        transform: translate(-50%, -10%);
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #fde68a, #f59e0b 72%);
        display: grid;
        place-items: center;
        color: #fff;
        z-index: 7;
        box-shadow: 0 12px 24px rgba(245, 158, 11, .32);
        transition: opacity .25s ease;
    }

    .envelope-button .spark {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255,255,255,.75);
        animation: sparkle 2.4s ease-in-out infinite;
    }

    .envelope-button .spark.s1 { top: 40px; left: 48px; }
    .envelope-button .spark.s2 { top: 92px; right: 42px; animation-delay: .5s; }
    .envelope-button .spark.s3 { top: 12px; left: 50%; animation-delay: 1s; }

    @keyframes sparkle {
        0%, 100% { transform: scale(.7); opacity: .35; }
        50% { transform: scale(1.35); opacity: .95; }
    }

    .envelope-button.is-opening .envelope-flap,
    .envelope-button.is-opened .envelope-flap {
        transform: rotateX(180deg);
    }

    .envelope-button.is-opening .envelope-letter,
    .envelope-button.is-opened .envelope-letter {
        transform: translateY(-120px);
    }

    .envelope-button.is-opening .envelope-seal,
    .envelope-button.is-opened .envelope-seal {
        opacity: 0;
    }

    .hint-copy {
        text-align: center;
        color: rgba(255,255,255,.80);
        font-size: .92rem;
        margin-top: 18px;
    }

    .announcement-card {
        display: none;
        margin-top: 28px;
        border-radius: 22px;
        background: rgba(255,255,255,.96);
        color: #0f172a;
        padding: 26px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.45);
    }

    .announcement-card.show {
        display: block;
        animation: revealCard .55s ease;
    }

    @keyframes revealCard {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .result-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-radius: 999px;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .result-badge.status-lulus { background: rgba(22, 163, 74, .12); color: #166534; }
    .result-badge.status-lulus_bersyarat { background: rgba(245, 158, 11, .14); color: #92400e; }
    .result-badge.status-tidak_lulus { background: rgba(239, 68, 68, .12); color: #991b1b; }

    .note-card {
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 18px;
        margin-top: 18px;
    }

    .pending-box {
        margin-top: 20px;
        border-radius: 18px;
        padding: 22px;
        background: rgba(255,255,255,.12);
        border: 1px dashed rgba(255,255,255,.22);
        color: rgba(255,255,255,.92);
    }
</style>
@stop

@section('content')
    <div class="graduation-stage">
        <div class="stage-badge">
            <i class="fas fa-star-and-crescent"></i>
            Pengumuman Resmi • {{ $tahunAktif->nama }}
        </div>
        <div class="stage-copy">
            <h2>Bismillah, saatnya membuka hasil kelulusan Anda.</h2>
            <p>
                Silakan buka amplop di bawah ini untuk melihat pengumuman kelulusan resmi kelas 12.
                Semoga hasil terbaik menyertai ikhtiar Anda.
            </p>
        </div>

        @if(!$announcement)
            <div class="pending-box">
                <h4 class="mb-2">Hasil Anda belum diinput admin</h4>
                <p class="mb-0">
                    Menu ini sudah dibuka, tetapi hasil kelulusan untuk akun Anda belum ditetapkan.
                    Silakan cek kembali beberapa saat lagi atau hubungi wali kelas/admin madrasah.
                </p>
            </div>
        @else
            <div class="envelope-panel">
                <div class="envelope-wrapper">
                    <button type="button" class="envelope-button" id="openEnvelopeButton" data-opened="{{ $announcement->opened_at ? '1' : '0' }}">
                        <span class="spark s1"></span>
                        <span class="spark s2"></span>
                        <span class="spark s3"></span>
                        <div class="envelope-shell">
                            <div class="envelope-back"></div>
                            <div class="envelope-letter">
                                <div class="letter-line short"></div>
                                <div class="letter-line"></div>
                                <div class="letter-line"></div>
                                <div class="letter-line tiny"></div>
                            </div>
                            <div class="envelope-front"></div>
                            <div class="envelope-flap"></div>
                            <div class="envelope-seal">
                                <i class="fas fa-mosque"></i>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="hint-copy">
                    <i class="fas fa-hand-pointer mr-1"></i> Ketuk amplop untuk membuka hasil kelulusan Anda.
                </div>

                <div class="announcement-card" id="announcementCard">
                    <div class="result-badge status-{{ $announcement->status }}">
                        <i class="fas {{ $announcement->status === 'lulus' ? 'fa-check-circle' : ($announcement->status === 'lulus_bersyarat' ? 'fa-exclamation-circle' : 'fa-times-circle') }}"></i>
                        {{ $announcement->status_label }}
                    </div>
                    <h3 class="mb-2">
                        @if($announcement->status === 'lulus')
                            Alhamdulillah, Anda dinyatakan lulus.
                        @elseif($announcement->status === 'lulus_bersyarat')
                            Anda dinyatakan lulus bersyarat.
                        @else
                            Hasil pengumuman Anda: Tidak Lulus.
                        @endif
                    </h3>
                    <p class="text-muted mb-0">
                        Nama: <strong>{{ $siswa->nama_lengkap }}</strong><br>
                        NISN: <strong>{{ $siswa->nisn }}</strong><br>
                        Rombel: <strong>{{ $kelasAktif->kelas->nama_kelas }}</strong>
                    </p>

                    @if($announcement->catatan)
                        <div class="note-card">
                            <div class="text-uppercase text-muted small font-weight-bold mb-2">Catatan Admin</div>
                            <div>{{ $announcement->catatan }}</div>
                        </div>
                    @endif

                    <div class="text-muted small mt-3" id="openedAtText">
                        @if($announcement->opened_at)
                            Hasil ini sudah pernah Anda buka pada {{ $announcement->opened_at->format('d M Y H:i') }}.
                        @else
                            Riwayat pembukaan akan dicatat saat amplop dibuka.
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('openEnvelopeButton');
    const card = document.getElementById('announcementCard');
    const openedAtText = document.getElementById('openedAtText');

    if (!button || !card) {
        return;
    }

    const reveal = function () {
        button.classList.add('is-opened');
        card.classList.add('show');
    };

    if (button.dataset.opened === '1') {
        reveal();
    }

    button.addEventListener('click', function () {
        if (button.classList.contains('is-opening') || button.classList.contains('is-opened')) {
            if (!button.classList.contains('is-opened')) {
                reveal();
            }
            return;
        }

        button.classList.add('is-opening');

        fetch(@json(route('siswa.kelulusan-pengumuman.open-envelope')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Gagal membuka pengumuman.');
            }

            setTimeout(() => {
                reveal();
                if (payload.opened_at && openedAtText) {
                    openedAtText.textContent = 'Hasil ini dibuka pada ' + payload.opened_at + '.';
                }
            }, 950);
        })
        .catch(error => {
            button.classList.remove('is-opening');
            alert(error.message || 'Terjadi kendala saat membuka pengumuman.');
        });
    });
});
</script>
@stop
