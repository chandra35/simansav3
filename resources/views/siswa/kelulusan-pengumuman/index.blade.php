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

    .envelope-button.is-locked {
        cursor: not-allowed;
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

    .result-ribbon {
        position: absolute;
        left: 50%;
        top: 48%;
        z-index: 8;
        min-width: 220px;
        padding: 14px 22px;
        border-radius: 18px;
        color: #fff;
        font-weight: 900;
        letter-spacing: .08em;
        text-align: center;
        text-transform: uppercase;
        box-shadow: 0 20px 34px rgba(15, 23, 42, .28);
        opacity: 0;
        transform: translate(-50%, 34px) scale(.82) rotate(-2deg);
        transition:
            transform 1.05s cubic-bezier(.18,.89,.32,1.18),
            opacity .45s ease;
    }

    .result-ribbon::before,
    .result-ribbon::after {
        content: "";
        position: absolute;
        top: 50%;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: inherit;
        transform: translateY(-50%) rotate(45deg);
        opacity: .9;
        z-index: -1;
    }

    .result-ribbon::before { left: -9px; }
    .result-ribbon::after { right: -9px; }

    .result-ribbon.status-lulus {
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 56%, #0f766e 100%);
    }

    .result-ribbon.status-lulus_bersyarat {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 56%, #b45309 100%);
    }

    .result-ribbon.status-tidak_lulus {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 56%, #991b1b 100%);
    }

    .result-ribbon-label {
        display: block;
        font-size: 2.1rem;
        line-height: 1;
        text-shadow: 0 3px 10px rgba(15, 23, 42, .24);
    }

    .result-ribbon-subtitle {
        display: block;
        margin-top: 5px;
        font-size: .72rem;
        letter-spacing: .04em;
        opacity: .86;
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
        z-index: 9;
        box-shadow: 0 12px 24px rgba(245, 158, 11, .32);
        transition: opacity .25s ease;
    }

    .envelope-logo {
        width: 46px;
        height: 46px;
        object-fit: contain;
        border-radius: 50%;
        background: rgba(255,255,255,.92);
        padding: 6px;
    }

    .envelope-logo-fallback {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
        color: #0f766e;
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

    .envelope-button.is-opening .result-ribbon,
    .envelope-button.is-opened .result-ribbon {
        opacity: 1;
        transform: translate(-50%, -92px) scale(1) rotate(0);
        transition-delay: .22s;
    }

    .envelope-button.is-opening .envelope-seal,
    .envelope-button.is-opened .envelope-seal {
        opacity: 0;
    }

    .celebration-dot {
        position: absolute;
        left: 50%;
        top: 58%;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #fde68a;
        opacity: 0;
        z-index: 10;
    }

    .celebration-dot.d2 { background: #7dd3fc; }
    .celebration-dot.d3 { background: #86efac; }
    .celebration-dot.d4 { background: #fca5a5; }
    .celebration-dot.d5 { background: #c4b5fd; }
    .celebration-dot.d6 { background: #fef3c7; }

    .envelope-button.is-opening .celebration-dot,
    .envelope-button.is-opened .celebration-dot {
        animation: burstDot .9s ease-out .35s both;
    }

    .envelope-button.is-opening .celebration-dot.d1,
    .envelope-button.is-opened .celebration-dot.d1 { --x: -112px; --y: -132px; }
    .envelope-button.is-opening .celebration-dot.d2,
    .envelope-button.is-opened .celebration-dot.d2 { --x: -64px; --y: -168px; }
    .envelope-button.is-opening .celebration-dot.d3,
    .envelope-button.is-opened .celebration-dot.d3 { --x: 90px; --y: -150px; }
    .envelope-button.is-opening .celebration-dot.d4,
    .envelope-button.is-opened .celebration-dot.d4 { --x: 122px; --y: -96px; }
    .envelope-button.is-opening .celebration-dot.d5,
    .envelope-button.is-opened .celebration-dot.d5 { --x: -142px; --y: -72px; }
    .envelope-button.is-opening .celebration-dot.d6,
    .envelope-button.is-opened .celebration-dot.d6 { --x: 24px; --y: -188px; }

    @keyframes burstDot {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(.55); }
        20% { opacity: 1; }
        100% { opacity: 0; transform: translate(calc(-50% + var(--x)), calc(-50% + var(--y))) scale(1.35); }
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

    .countdown-panel {
        margin-top: 22px;
        border-radius: 22px;
        padding: 22px;
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.18);
        color: #fff;
    }

    .countdown-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(74px, 1fr));
        gap: 12px;
        margin-top: 16px;
        max-width: 520px;
    }

    .countdown-item {
        border-radius: 16px;
        padding: 12px 10px;
        text-align: center;
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.16);
    }

    .countdown-value {
        display: block;
        font-size: 1.75rem;
        line-height: 1.1;
        font-weight: 800;
    }

    .countdown-label {
        display: block;
        margin-top: 4px;
        font-size: .78rem;
        color: rgba(255,255,255,.75);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    @media (max-width: 576px) {
        .graduation-stage {
            border-radius: 18px;
            padding: 22px 14px;
        }

        .stage-copy h2 {
            font-size: 1.75rem;
        }

        .envelope-panel {
            padding: 20px 10px;
        }

        .envelope-wrapper {
            min-height: 250px;
        }

        .envelope-button {
            width: min(300px, 96%);
        }

        .result-ribbon {
            min-width: 180px;
            padding: 12px 18px;
        }

        .result-ribbon-label {
            font-size: 1.65rem;
        }

        .envelope-button.is-opening .result-ribbon,
        .envelope-button.is-opened .result-ribbon {
            transform: translate(-50%, -74px) scale(1) rotate(0);
        }

        .countdown-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .announcement-card {
            padding: 18px;
        }
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

        @if(!$isScheduledOpen && $startsAt)
            <div class="countdown-panel">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="text-uppercase small font-weight-bold mb-1" style="color: rgba(255,255,255,.72);">
                            Jadwal Pengumuman
                        </div>
                        <h4 class="mb-1">Amplop dapat dibuka pada {{ $startsAt->format('d M Y H:i') }} WIB</h4>
                        <div style="color: rgba(255,255,255,.78);">Hitung mundur akan berjalan otomatis di perangkat Anda.</div>
                    </div>
                    <i class="fas fa-hourglass-half" style="font-size: 2rem; opacity: .78;"></i>
                </div>
                <div class="countdown-grid" id="graduationCountdown" data-starts-at="{{ $startsAt->toIso8601String() }}">
                    <div class="countdown-item"><span class="countdown-value" data-part="days">0</span><span class="countdown-label">Hari</span></div>
                    <div class="countdown-item"><span class="countdown-value" data-part="hours">0</span><span class="countdown-label">Jam</span></div>
                    <div class="countdown-item"><span class="countdown-value" data-part="minutes">0</span><span class="countdown-label">Menit</span></div>
                    <div class="countdown-item"><span class="countdown-value" data-part="seconds">0</span><span class="countdown-label">Detik</span></div>
                </div>
            </div>
        @endif

        @if(!$announcement && $isScheduledOpen)
            <div class="pending-box">
                <h4 class="mb-2">Hasil Anda belum diinput admin</h4>
                <p class="mb-0">
                    Menu ini sudah dibuka, tetapi hasil kelulusan untuk akun Anda belum ditetapkan.
                    Silakan cek kembali beberapa saat lagi atau hubungi wali kelas/admin madrasah.
                </p>
            </div>
        @elseif($announcement && $isScheduledOpen)
            <div class="envelope-panel">
                <div class="envelope-wrapper">
                    <button
                        type="button"
                        class="envelope-button"
                        id="openEnvelopeButton"
                        data-opened="{{ $announcement->opened_at ? '1' : '0' }}"
                        data-ready="1"
                    >
                        <span class="spark s1"></span>
                        <span class="spark s2"></span>
                        <span class="spark s3"></span>
                        <span class="celebration-dot d1"></span>
                        <span class="celebration-dot d2"></span>
                        <span class="celebration-dot d3"></span>
                        <span class="celebration-dot d4"></span>
                        <span class="celebration-dot d5"></span>
                        <span class="celebration-dot d6"></span>
                        <div class="envelope-shell">
                            <div class="envelope-back"></div>
                            <div class="envelope-letter">
                                <div class="letter-line short"></div>
                                <div class="letter-line"></div>
                                <div class="letter-line"></div>
                                <div class="letter-line tiny"></div>
                            </div>
                            <div class="result-ribbon status-{{ $announcement->status }}">
                                <span class="result-ribbon-label">{{ strtoupper($announcement->status_label) }}</span>
                                <span class="result-ribbon-subtitle">Hasil Pengumuman</span>
                            </div>
                            <div class="envelope-front"></div>
                            <div class="envelope-flap"></div>
                            <div class="envelope-seal">
                                @if($setting->logo_sekolah_url)
                                    <img src="{{ $setting->logo_sekolah_url }}" alt="Logo {{ $setting->nama_sekolah }}" class="envelope-logo">
                                @else
                                    <span class="envelope-logo-fallback">SIMANSA</span>
                                @endif
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
        initCountdown();
        return;
    }

    const reveal = function (delayCard = 0) {
        button.classList.add('is-opened');
        window.setTimeout(() => {
            card.classList.add('show');
        }, delayCard);
    };

    if (button.dataset.opened === '1') {
        reveal(450);
    }

    button.addEventListener('click', function () {
        if (button.dataset.ready !== '1') {
            alert('Amplop pengumuman belum dapat dibuka sebelum jadwal tayang.');
            return;
        }

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
                reveal(650);
                if (payload.opened_at && openedAtText) {
                    openedAtText.textContent = 'Hasil ini dibuka pada ' + payload.opened_at + '.';
                }
            }, 900);
        })
        .catch(error => {
            button.classList.remove('is-opening');
            alert(error.message || 'Terjadi kendala saat membuka pengumuman.');
        });
    });

    initCountdown();

    function initCountdown() {
        const countdown = document.getElementById('graduationCountdown');
        if (!countdown || !countdown.dataset.startsAt) {
            return;
        }

        const target = new Date(countdown.dataset.startsAt).getTime();
        const parts = {
            days: countdown.querySelector('[data-part="days"]'),
            hours: countdown.querySelector('[data-part="hours"]'),
            minutes: countdown.querySelector('[data-part="minutes"]'),
            seconds: countdown.querySelector('[data-part="seconds"]')
        };

        const pad = value => String(value).padStart(2, '0');
        const tick = function () {
            const distance = Math.max(target - Date.now(), 0);
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((distance / (1000 * 60)) % 60);
            const seconds = Math.floor((distance / 1000) % 60);

            if (parts.days) parts.days.textContent = days;
            if (parts.hours) parts.hours.textContent = pad(hours);
            if (parts.minutes) parts.minutes.textContent = pad(minutes);
            if (parts.seconds) parts.seconds.textContent = pad(seconds);

            if (distance <= 0) {
                window.location.reload();
            }
        };

        tick();
        setInterval(tick, 1000);
    }
});
</script>
@stop
