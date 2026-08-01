@if(
    ($studentElectionNotice ?? null)
    && ! $studentElectionNotice['has_voted']
    && ! request()->routeIs('siswa.osis-election.*')
)
    <aside
        class="student-election-notice"
        id="studentElectionNotice"
        data-election="{{ $studentElectionNotice['id'] }}"
        data-phase="{{ $studentElectionNotice['phase'] }}"
        data-start="{{ $studentElectionNotice['starts_at'] }}"
        data-end="{{ $studentElectionNotice['ends_at'] }}"
    >
        <div class="student-election-overlay" id="studentElectionOverlay" hidden>
            <div class="student-election-card" role="dialog" aria-modal="true" aria-labelledby="studentElectionTitle">
                <button type="button" class="student-election-close" id="studentElectionClose" aria-label="Tutup pengumuman">
                    <i class="fas fa-times"></i>
                </button>

                <div class="student-election-visual" aria-hidden="true">
                    <span class="student-election-orbit orbit-one"></span>
                    <span class="student-election-orbit orbit-two"></span>
                    <div class="student-election-ballot">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                </div>

                <span class="student-election-eyebrow" id="studentElectionEyebrow">
                    PEMILIHAN OSIS
                </span>
                <h2 id="studentElectionTitle">{{ $studentElectionNotice['title'] }}</h2>
                <p id="studentElectionMessage">
                    {{ $studentElectionNotice['theme'] ?: 'Kenali kandidat dan gunakan hak suara Anda dengan bertanggung jawab.' }}
                </p>

                <div class="student-election-countdown" id="studentElectionCountdown" aria-live="polite">
                    <div><strong data-unit="days">00</strong><span>Hari</span></div>
                    <div><strong data-unit="hours">00</strong><span>Jam</span></div>
                    <div><strong data-unit="minutes">00</strong><span>Menit</span></div>
                    <div><strong data-unit="seconds">00</strong><span>Detik</span></div>
                </div>

                <div class="student-election-actions">
                    <button type="button" class="btn btn-light" id="studentElectionLater">Ingatkan Nanti</button>
                    <a href="{{ $studentElectionNotice['url'] }}" class="btn student-election-primary" id="studentElectionAction">
                        <i class="fas fa-users mr-1"></i>
                        <span>Lihat Kandidat</span>
                    </a>
                </div>
            </div>
        </div>

        <a href="{{ $studentElectionNotice['url'] }}" class="student-election-pill">
            <span><i class="fas fa-vote-yea"></i></span>
            <div>
                <small id="studentElectionPillLabel">PEMILIHAN OSIS</small>
                <strong id="studentElectionPillText">Lihat kandidat</strong>
            </div>
            <i class="fas fa-chevron-right"></i>
        </a>
    </aside>

    @once
        <style>
            .student-election-overlay {
                align-items: center;
                background: rgba(7, 15, 35, .78);
                backdrop-filter: blur(10px);
                display: flex;
                inset: 0;
                justify-content: center;
                padding: 1rem;
                position: fixed;
                z-index: 2050;
            }
            .student-election-overlay[hidden] { display: none; }
            .student-election-card {
                background: linear-gradient(155deg, #ffffff 0%, #eff6ff 100%);
                border: 1px solid rgba(255, 255, 255, .75);
                border-radius: 28px;
                box-shadow: 0 32px 90px rgba(2, 6, 23, .38);
                color: #0f172a;
                max-width: 620px;
                overflow: hidden;
                padding: 2rem;
                position: relative;
                text-align: center;
                width: 100%;
            }
            .student-election-card::before {
                background: linear-gradient(90deg, #2563eb, #14b8a6, #f59e0b);
                content: "";
                height: 5px;
                inset: 0 0 auto;
                position: absolute;
            }
            .student-election-close {
                align-items: center;
                background: #e2e8f0;
                border: 0;
                border-radius: 50%;
                color: #475569;
                display: flex;
                height: 38px;
                justify-content: center;
                position: absolute;
                right: 1rem;
                top: 1rem;
                width: 38px;
                z-index: 2;
            }
            .student-election-visual {
                height: 116px;
                margin: 0 auto .75rem;
                position: relative;
                width: 150px;
            }
            .student-election-ballot {
                align-items: center;
                animation: studentElectionFloat 2.6s ease-in-out infinite;
                background: linear-gradient(145deg, #2563eb, #0f766e);
                border: 7px solid #dbeafe;
                border-radius: 24px;
                box-shadow: 0 18px 35px rgba(37, 99, 235, .25);
                color: #fff;
                display: flex;
                font-size: 2.6rem;
                height: 94px;
                justify-content: center;
                left: 28px;
                position: absolute;
                top: 10px;
                transform: rotate(-4deg);
                width: 94px;
                z-index: 1;
            }
            .student-election-orbit {
                border: 2px dashed rgba(37, 99, 235, .2);
                border-radius: 50%;
                inset: 0;
                position: absolute;
            }
            .orbit-one { animation: studentElectionSpin 9s linear infinite; }
            .orbit-two {
                animation: studentElectionSpin 6s linear infinite reverse;
                inset: 15px -10px;
            }
            .student-election-eyebrow {
                color: #2563eb;
                display: block;
                font-size: .73rem;
                font-weight: 900;
                letter-spacing: .12em;
                margin-bottom: .35rem;
            }
            .student-election-card h2 {
                color: #0f172a;
                font-size: clamp(1.45rem, 4vw, 2rem);
                font-weight: 900;
                margin: 0;
            }
            .student-election-card > p {
                color: #64748b;
                margin: .65rem auto 1.15rem;
                max-width: 480px;
            }
            .student-election-countdown {
                display: grid;
                gap: .55rem;
                grid-template-columns: repeat(4, 1fr);
                margin: 0 auto 1.25rem;
                max-width: 430px;
            }
            .student-election-countdown > div {
                background: #fff;
                border: 1px solid #dbeafe;
                border-radius: 14px;
                padding: .65rem .3rem;
            }
            .student-election-countdown strong,
            .student-election-countdown span { display: block; }
            .student-election-countdown strong {
                color: #1d4ed8;
                font-size: 1.35rem;
                font-variant-numeric: tabular-nums;
                font-weight: 900;
            }
            .student-election-countdown span {
                color: #64748b;
                font-size: .65rem;
                font-weight: 700;
                text-transform: uppercase;
            }
            .student-election-actions {
                display: flex;
                gap: .7rem;
                justify-content: center;
            }
            .student-election-actions .btn {
                border-radius: 11px;
                font-weight: 800;
                padding: .65rem 1rem;
            }
            .student-election-primary {
                background: linear-gradient(135deg, #2563eb, #0f766e);
                color: #fff !important;
            }
            .student-election-pill {
                align-items: center;
                background: linear-gradient(135deg, #1d4ed8, #0f766e);
                border: 1px solid rgba(255, 255, 255, .25);
                border-radius: 18px;
                bottom: 1.1rem;
                box-shadow: 0 16px 38px rgba(15, 23, 42, .28);
                color: #fff !important;
                display: flex;
                gap: .7rem;
                max-width: calc(100vw - 2rem);
                padding: .65rem .8rem;
                position: fixed;
                right: 1.1rem;
                text-decoration: none !important;
                z-index: 1040;
            }
            .student-election-pill > span {
                align-items: center;
                background: rgba(255, 255, 255, .16);
                border-radius: 12px;
                display: flex;
                height: 42px;
                justify-content: center;
                width: 42px;
            }
            .student-election-pill div { min-width: 0; }
            .student-election-pill small,
            .student-election-pill strong { display: block; }
            .student-election-pill small {
                color: rgba(255, 255, 255, .72);
                font-size: .58rem;
                font-weight: 800;
                letter-spacing: .08em;
            }
            .student-election-pill strong {
                color: #fff;
                font-size: .78rem;
                max-width: 190px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            /* Fase dijeda: warna menyala + animasi khas */
            .student-election-pill.is-paused {
                animation: studentElectionGlow 1.8s ease-in-out infinite,
                           studentElectionShift 4s ease infinite;
                background: linear-gradient(270deg, #ff416c, #ff4b2b, #f7971e, #ff416c);
                background-size: 300% 300%;
                border: 1px solid rgba(255, 255, 255, .45);
            }
            .student-election-pill.is-paused > span {
                background: rgba(255, 255, 255, .25);
            }
            .student-election-pill.is-paused > span i {
                animation: studentElectionWiggle 2.4s ease-in-out infinite;
            }
            .student-election-pill.is-paused small {
                color: rgba(255, 255, 255, .9);
            }
            @keyframes studentElectionGlow {
                0%, 100% { box-shadow: 0 0 0 0 rgba(255, 65, 108, .55), 0 16px 38px rgba(15, 23, 42, .28); }
                50% { box-shadow: 0 0 0 12px rgba(255, 65, 108, 0), 0 16px 38px rgba(255, 75, 43, .45); }
            }
            @keyframes studentElectionShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            @keyframes studentElectionWiggle {
                0%, 60%, 100% { transform: rotate(0); }
                65% { transform: rotate(-14deg) scale(1.12); }
                72% { transform: rotate(12deg) scale(1.12); }
                80% { transform: rotate(-8deg); }
                88% { transform: rotate(6deg); }
            }
            @keyframes studentElectionFloat {
                0%, 100% { transform: translateY(0) rotate(-4deg); }
                50% { transform: translateY(-8px) rotate(2deg); }
            }
            @keyframes studentElectionSpin { to { transform: rotate(360deg); } }
            @media (max-width: 575.98px) {
                .student-election-card { border-radius: 22px; padding: 1.5rem 1rem; }
                .student-election-visual { height: 95px; transform: scale(.82); }
                .student-election-actions { flex-direction: column-reverse; }
                .student-election-actions .btn { width: 100%; }
                .student-election-pill { bottom: .75rem; right: .75rem; }
            }
            @media (prefers-reduced-motion: reduce) {
                .student-election-ballot,
                .student-election-orbit,
                .student-election-pill.is-paused,
                .student-election-pill.is-paused > span i { animation: none; }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const notice = document.getElementById('studentElectionNotice');
                if (!notice) return;

                const overlay = document.getElementById('studentElectionOverlay');
                const phase = notice.dataset.phase;
                const key = `simansa-osis-notice:${notice.dataset.election}:${phase}`;
                const start = new Date(notice.dataset.start).getTime();
                const end = new Date(notice.dataset.end).getTime();
                const countdown = document.getElementById('studentElectionCountdown');
                const eyebrow = document.getElementById('studentElectionEyebrow');
                const message = document.getElementById('studentElectionMessage');
                const actionText = document.querySelector('#studentElectionAction span');
                const pillLabel = document.getElementById('studentElectionPillLabel');
                const pillText = document.getElementById('studentElectionPillText');
                let storage;

                try { storage = window.sessionStorage; } catch (error) { storage = null; }

                if (phase !== 'paused' && !storage?.getItem(key)) {
                    overlay.hidden = false;
                    document.body.style.overflow = 'hidden';
                }

                const dismiss = () => {
                    overlay.hidden = true;
                    document.body.style.overflow = '';
                    try { storage?.setItem(key, 'seen'); } catch (error) {}
                };

                document.getElementById('studentElectionClose')?.addEventListener('click', dismiss);
                document.getElementById('studentElectionLater')?.addEventListener('click', dismiss);
                document.getElementById('studentElectionAction')?.addEventListener('click', () => {
                    try { storage?.setItem(key, 'seen'); } catch (error) {}
                });

                const pad = value => String(Math.max(0, value)).padStart(2, '0');
                const update = () => {
                    const now = Date.now();
                    const scheduled = now < start;
                    const target = scheduled ? start : end;
                    const remaining = Math.max(0, target - now);
                    const units = {
                        days: Math.floor(remaining / 86400000),
                        hours: Math.floor((remaining % 86400000) / 3600000),
                        minutes: Math.floor((remaining % 3600000) / 60000),
                        seconds: Math.floor((remaining % 60000) / 1000),
                    };

                    Object.entries(units).forEach(([unit, value]) => {
                        const element = countdown?.querySelector(`[data-unit="${unit}"]`);
                        if (element) element.textContent = pad(value);
                    });

                    if (phase === 'paused') {
                        eyebrow.textContent = 'PEMILIHAN SEDANG DIJEDA';
                        message.textContent = 'Panitia sedang menjeda pemungutan suara. Kandidat tetap dapat dipelajari sambil menunggu voting dilanjutkan.';
                        actionText.textContent = 'Lihat Kandidat';
                        pillLabel.textContent = 'PEMILIHAN DIJEDA';
                        pillText.textContent = 'Lihat informasi kandidat';
                        document.querySelector('.student-election-pill')?.classList.add('is-paused');
                        countdown.hidden = true;
                        return;
                    }

                    if (scheduled) {
                        eyebrow.textContent = 'PEMILIHAN SEGERA DIBUKA';
                        actionText.textContent = 'Kenali Kandidat';
                        pillLabel.textContent = 'DIBUKA DALAM';
                        pillText.textContent = `${units.days ? units.days + ' hari ' : ''}${pad(units.hours)}:${pad(units.minutes)}:${pad(units.seconds)}`;
                        return;
                    }

                    eyebrow.textContent = 'PEMILIHAN SEDANG BERLANGSUNG';
                    message.textContent = 'Waktunya menggunakan hak suara. Kenali paket kandidat, tentukan pilihan, lalu coblos dengan yakin.';
                    actionText.textContent = 'Coblos Sekarang';
                    pillLabel.textContent = 'VOTING BERLANGSUNG';
                    pillText.textContent = `Berakhir ${pad(units.hours)}:${pad(units.minutes)}:${pad(units.seconds)}`;

                    if (remaining <= 0) {
                        notice.hidden = true;
                        document.body.style.overflow = '';
                    }
                };

                update();
                window.setInterval(update, 1000);
            });
        </script>
    @endonce
@endif
