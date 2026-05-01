<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="20">
    <title>Updating Aplikasi</title>
    <style>
        :root {
            --bg: #f4f8ff;
            --surface: rgba(255, 255, 255, 0.82);
            --surface-strong: rgba(255, 255, 255, 0.94);
            --line: rgba(37, 99, 235, 0.12);
            --text: #16335f;
            --muted: #6781a8;
            --primary: #2d6cdf;
            --secondary: #13a388;
            --accent: #f59e0b;
            --shadow: 0 24px 80px rgba(28, 64, 121, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(45, 108, 223, 0.16), transparent 28%),
                radial-gradient(circle at bottom right, rgba(19, 163, 136, 0.14), transparent 24%),
                linear-gradient(160deg, #f7fbff 0%, #eef5ff 54%, #f8fcff 100%);
            color: var(--text);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            overflow: hidden;
        }

        .backdrop-pattern,
        .backdrop-pattern::before,
        .backdrop-pattern::after {
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        .backdrop-pattern::before,
        .backdrop-pattern::after {
            content: "";
            background-repeat: no-repeat;
            opacity: .5;
        }

        .backdrop-pattern::before {
            background-image:
                radial-gradient(circle at 20% 20%, rgba(45, 108, 223, 0.18) 0 7px, transparent 8px),
                radial-gradient(circle at 80% 18%, rgba(19, 163, 136, 0.16) 0 6px, transparent 7px),
                radial-gradient(circle at 70% 80%, rgba(245, 158, 11, 0.12) 0 9px, transparent 10px);
            animation: drift 16s ease-in-out infinite alternate;
        }

        .backdrop-pattern::after {
            background-image:
                linear-gradient(120deg, rgba(255, 255, 255, 0.55), transparent 36%),
                linear-gradient(300deg, rgba(255, 255, 255, 0.35), transparent 32%);
            animation: shimmer 12s linear infinite;
        }

        .maintenance-shell {
            position: relative;
            width: min(1100px, 100%);
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: var(--surface);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .maintenance-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(45, 108, 223, 0.08), transparent 28%),
                radial-gradient(circle at 85% 15%, rgba(19, 163, 136, 0.13), transparent 18%);
            pointer-events: none;
        }

        .maintenance-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
            gap: 0;
            min-height: 620px;
        }

        .content-pane {
            padding: 56px 54px 46px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(45, 108, 223, 0.1);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--secondary);
            box-shadow: 0 0 0 0 rgba(19, 163, 136, 0.35);
            animation: pulse 2s infinite;
        }

        h1 {
            margin: 22px 0 14px;
            font-size: clamp(34px, 4vw, 56px);
            line-height: 1.04;
            letter-spacing: 0;
        }

        .lead {
            max-width: 640px;
            margin: 0 0 24px;
            font-size: 17px;
            line-height: 1.8;
            color: var(--muted);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin: 26px 0 18px;
        }

        .info-card {
            background: var(--surface-strong);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px 18px 16px;
            min-height: 108px;
        }

        .info-card__label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .info-card__value {
            margin-top: 10px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.55;
            color: var(--text);
        }

        .status-panel {
            margin-top: 18px;
            padding: 20px 22px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(45, 108, 223, 0.1), rgba(19, 163, 136, 0.09));
            border: 1px solid rgba(45, 108, 223, 0.12);
        }

        .status-panel__title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 12px;
            font-size: 15px;
            font-weight: 700;
        }

        .refresh-note {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .progress-track {
            position: relative;
            height: 11px;
            border-radius: 999px;
            background: rgba(45, 108, 223, 0.1);
            overflow: hidden;
        }

        .progress-bar {
            position: absolute;
            inset: 0;
            width: 42%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), var(--secondary), #7c3aed);
            animation: progress 2.8s ease-in-out infinite;
        }

        .support-line {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            align-items: center;
            margin-top: 18px;
            color: var(--muted);
            font-size: 14px;
        }

        .support-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--line);
            color: var(--text);
            font-weight: 600;
        }

        .visual-pane {
            position: relative;
            padding: 42px 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 320px;
        }

        .visual-pane::before {
            content: "";
            position: absolute;
            inset: 32px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.45)),
                radial-gradient(circle at top, rgba(45, 108, 223, 0.08), transparent 55%);
            border: 1px solid rgba(255, 255, 255, 0.75);
        }

        .scene {
            position: relative;
            z-index: 1;
            width: min(372px, 100%);
            aspect-ratio: 1 / 1.08;
        }

        .scene-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(1px);
        }

        .scene-orb--one {
            width: 180px;
            height: 180px;
            top: 12px;
            right: 0;
            background: radial-gradient(circle, rgba(45, 108, 223, 0.28), rgba(45, 108, 223, 0.04));
            animation: floatY 7s ease-in-out infinite;
        }

        .scene-orb--two {
            width: 136px;
            height: 136px;
            bottom: 42px;
            left: 10px;
            background: radial-gradient(circle, rgba(19, 163, 136, 0.24), rgba(19, 163, 136, 0.04));
            animation: floatY 8s ease-in-out infinite reverse;
        }

        .scene-panel {
            position: absolute;
            inset: 34px 18px 18px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(25, 66, 138, 0.97), rgba(30, 99, 170, 0.88));
            box-shadow: 0 30px 70px rgba(19, 49, 94, 0.24);
            overflow: hidden;
            padding: 24px;
        }

        .scene-panel::before {
            content: "";
            position: absolute;
            inset: auto -15% -18% 8%;
            height: 52%;
            background:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.26), transparent 24%),
                radial-gradient(circle at 78% 34%, rgba(255, 255, 255, 0.18), transparent 20%);
            animation: drift 14s ease-in-out infinite alternate;
        }

        .scene-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .scene-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16) url('{{ asset('storage/settings/logo/logo-sekolah-1772827224.png') }}') center/28px no-repeat;
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(4px);
        }

        .scene-console {
            position: relative;
            z-index: 1;
            margin-top: 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            padding: 20px 18px 18px;
            backdrop-filter: blur(8px);
        }

        .scene-copy {
            color: rgba(255, 255, 255, 0.95);
        }

        .scene-copy__eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .scene-copy__title {
            margin-top: 10px;
            font-size: 27px;
            line-height: 1.16;
            font-weight: 800;
        }

        .scene-copy__text {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
        }

        .scene-monitor {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(11, 29, 63, 0.88), rgba(17, 53, 96, 0.7));
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 16px 16px 14px;
            overflow: hidden;
        }

        .scene-monitor__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.72);
        }

        .scene-monitor__dots {
            display: inline-flex;
            gap: 5px;
        }

        .scene-monitor__dots span {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.55);
        }

        .scene-wave {
            position: relative;
            height: 118px;
            border-radius: 16px;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)),
                repeating-linear-gradient(
                    to right,
                    rgba(255, 255, 255, 0.04) 0 1px,
                    transparent 1px 34px
                );
        }

        .scene-wave::before,
        .scene-wave::after {
            content: "";
            position: absolute;
            left: -8%;
            right: -8%;
            height: 60px;
            border-radius: 999px;
        }

        .scene-wave::before {
            top: 36px;
            background: linear-gradient(90deg, rgba(19, 163, 136, 0.12), rgba(19, 163, 136, 0.78), rgba(45, 108, 223, 0.2));
            animation: waveSlide 4.8s ease-in-out infinite;
        }

        .scene-wave::after {
            top: 54px;
            background: linear-gradient(90deg, rgba(45, 108, 223, 0.08), rgba(124, 58, 237, 0.58), rgba(255, 255, 255, 0.12));
            animation: waveSlide 5.8s ease-in-out infinite reverse;
        }

        .scene-cards {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .scene-mini-card {
            border-radius: 16px;
            padding: 14px 12px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
            animation: floatCard 6.2s ease-in-out infinite;
        }

        .scene-mini-card:nth-child(2) {
            animation-delay: .35s;
        }

        .scene-mini-card:nth-child(3) {
            animation-delay: .7s;
        }

        .scene-mini-card__icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            margin-bottom: 10px;
        }

        .scene-mini-card__icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.85;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .scene-mini-card__title {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.92);
            line-height: 1.4;
        }

        .scene-mini-card__meta {
            margin-top: 6px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.66);
            line-height: 1.5;
        }

        .countdown {
            min-width: 118px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.88);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(45, 108, 223, 0.12);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(19, 163, 136, 0.35); }
            70% { box-shadow: 0 0 0 14px rgba(19, 163, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(19, 163, 136, 0); }
        }

        @keyframes progress {
            0% { transform: translateX(-28%); width: 36%; }
            50% { transform: translateX(72%); width: 42%; }
            100% { transform: translateX(-28%); width: 36%; }
        }

        @keyframes drift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(12px, -10px, 0); }
        }

        @keyframes shimmer {
            from { transform: translateX(-3%); }
            to { transform: translateX(3%); }
        }

        @keyframes floatY {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes waveSlide {
            0%, 100% { transform: translateX(-4%) scaleX(.98); }
            50% { transform: translateX(6%) scaleX(1.02); }
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @media (max-width: 960px) {
            .maintenance-grid {
                grid-template-columns: 1fr;
            }

            .content-pane {
                padding: 32px 24px 18px;
            }

            .visual-pane {
                padding: 0 22px 30px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 14px;
            }

            .maintenance-shell {
                border-radius: 22px;
            }

            .scene {
                width: min(320px, 100%);
            }

            .status-panel__title {
                flex-direction: column;
                align-items: flex-start;
            }

            .support-line {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="backdrop-pattern" aria-hidden="true"></div>

    <main class="maintenance-shell" role="main">
        <div class="maintenance-grid">
            <section class="content-pane">
                <div class="badge">
                    <span class="pulse-dot"></span>
                    Status Pemeliharaan Sistem
                </div>

                <h1>SIMANSA sedang diperbarui agar kembali lebih stabil.</h1>
                <p class="lead">
                    Kami sedang menerapkan pembaruan aplikasi dan penyegaran layanan agar aktivitas belajar, presensi,
                    dan administrasi santri berjalan lebih nyaman. Halaman ini akan memuat ulang otomatis setelah proses selesai.
                </p>

                <div class="info-grid">
                    <article class="info-card">
                        <div class="info-card__label">Status</div>
                        <div class="info-card__value">Updating Aplikasi</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Kode Layanan</div>
                        <div class="info-card__value">503 Service Maintenance</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Saran</div>
                        <div class="info-card__value">Tunggu sebentar, lalu buka ulang halaman ini.</div>
                    </article>
                </div>

                <div class="status-panel">
                    <div class="status-panel__title">
                        <span>Proses pembaruan sedang berjalan</span>
                        <span class="countdown">Refresh <span id="refreshCountdown" style="margin-left:6px;">20</span>d</span>
                    </div>
                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-bar"></div>
                    </div>
                    <p class="refresh-note">
                        Jika Anda sedang menunggu akses kembali, tidak perlu menutup halaman ini.
                        Sistem akan mencoba memuat ulang otomatis setelah hitung mundur selesai.
                    </p>
                </div>

                <div class="support-line">
                    <span class="support-chip">Madrasah Digital</span>
                    <span class="support-chip">Santri Tertib Data</span>
                    <span class="support-chip">Layanan Akan Kembali Online</span>
                </div>
            </section>

            <aside class="visual-pane" aria-hidden="true">
                <div class="scene">
                    <div class="scene-orb scene-orb--one"></div>
                    <div class="scene-orb scene-orb--two"></div>
                    <div class="scene-panel">
                        <div class="scene-header">
                            <div class="scene-logo"></div>
                            <div>SIMANSA</div>
                        </div>

                        <div class="scene-console">
                            <div class="scene-copy">
                                <div class="scene-copy__eyebrow">Status Layanan</div>
                                <div class="scene-copy__title">Sistem sedang distabilkan dan akan kembali online.</div>
                                <div class="scene-copy__text">
                                    Pembaruan ini membantu layanan siswa, presensi, dan administrasi berjalan lebih rapi dan konsisten.
                                </div>
                            </div>

                            <div class="scene-monitor">
                                <div class="scene-monitor__top">
                                    <span>Monitoring maintenance</span>
                                    <span class="scene-monitor__dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                </div>
                                <div class="scene-wave"></div>
                            </div>

                                <div class="scene-cards">
                                <div class="scene-mini-card">
                                    <div class="scene-mini-card__icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <ellipse cx="12" cy="6" rx="6.5" ry="2.5"></ellipse>
                                            <path d="M5.5 6v5c0 1.4 2.9 2.5 6.5 2.5s6.5-1.1 6.5-2.5V6"></path>
                                            <path d="M5.5 11v5c0 1.4 2.9 2.5 6.5 2.5s6.5-1.1 6.5-2.5v-5"></path>
                                        </svg>
                                    </div>
                                    <div class="scene-mini-card__title">Sinkron data</div>
                                    <div class="scene-mini-card__meta">Menjaga data tetap rapi dan aman.</div>
                                </div>
                                <div class="scene-mini-card">
                                    <div class="scene-mini-card__icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 3.5 18.5 6v5.8c0 4-2.6 6.8-6.5 8.7-3.9-1.9-6.5-4.7-6.5-8.7V6L12 3.5Z"></path>
                                            <path d="m9.6 12 1.7 1.8 3.2-3.5"></path>
                                        </svg>
                                    </div>
                                    <div class="scene-mini-card__title">Stabilitas akses</div>
                                    <div class="scene-mini-card__meta">Memastikan layanan kembali lebih konsisten.</div>
                                </div>
                                <div class="scene-mini-card">
                                    <div class="scene-mini-card__icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M20 7.5V4.5h-3"></path>
                                            <path d="M4 16.5v3h3"></path>
                                            <path d="M6.2 9.4A7 7 0 0 1 17 7.5L20 7.5"></path>
                                            <path d="M17.8 14.6A7 7 0 0 1 7 16.5L4 16.5"></path>
                                        </svg>
                                    </div>
                                    <div class="scene-mini-card__title">Refresh otomatis</div>
                                    <div class="scene-mini-card__meta">Halaman akan mencoba memuat ulang sendiri.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        (function () {
            let remaining = 20;
            const countdown = document.getElementById('refreshCountdown');

            if (!countdown) {
                return;
            }

            const timer = window.setInterval(function () {
                remaining -= 1;
                countdown.textContent = remaining;

                if (remaining <= 0) {
                    window.clearInterval(timer);
                    window.location.reload();
                }
            }, 1000);
        })();
    </script>
</body>
</html>
