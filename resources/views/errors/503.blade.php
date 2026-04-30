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
            width: min(360px, 100%);
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

        .scene-card {
            position: absolute;
            inset: 52px 16px 16px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(18, 66, 136, 0.95), rgba(30, 123, 167, 0.86));
            box-shadow: 0 30px 70px rgba(19, 49, 94, 0.24);
            overflow: hidden;
        }

        .scene-card::before {
            content: "";
            position: absolute;
            inset: auto -20% -24% -12%;
            height: 52%;
            background:
                radial-gradient(circle at 35% 30%, rgba(255, 255, 255, 0.22), transparent 26%),
                radial-gradient(circle at 80% 40%, rgba(255, 255, 255, 0.18), transparent 24%);
            animation: drift 14s ease-in-out infinite alternate;
        }

        .scene-header {
            position: absolute;
            top: 22px;
            left: 24px;
            right: 24px;
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

        .students {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 12px;
        }

        .student {
            position: relative;
            width: 94px;
            height: 220px;
            animation: floatY 6s ease-in-out infinite;
        }

        .student:nth-child(2) {
            width: 112px;
            height: 252px;
            animation-delay: .3s;
        }

        .student:nth-child(3) {
            animation-delay: .6s;
        }

        .student-head {
            position: absolute;
            top: 0;
            left: 50%;
            width: 52px;
            height: 52px;
            margin-left: -26px;
            border-radius: 999px;
            background: #f3c9a6;
            box-shadow: inset 0 -8px 0 rgba(0, 0, 0, 0.04);
            z-index: 3;
        }

        .student--boy .student-head::before,
        .student--girl .student-head::before {
            content: "";
            position: absolute;
            inset: -8px 4px 20px;
            border-radius: 999px 999px 24px 24px;
        }

        .student--boy .student-head::before {
            background: #0f2247;
        }

        .student--girl .student-head::before {
            background: #ffffff;
            inset: -6px -5px 8px;
            border-radius: 999px 999px 30px 30px;
            box-shadow: 0 8px 18px rgba(8, 25, 58, 0.08);
        }

        .student-head::after {
            content: "";
            position: absolute;
            top: 13px;
            left: 50%;
            width: 16px;
            height: 7px;
            margin-left: -8px;
            border-bottom: 2px solid rgba(22, 51, 95, 0.65);
            border-radius: 0 0 12px 12px;
        }

        .student-cap {
            position: absolute;
            top: -10px;
            left: 50%;
            width: 66px;
            height: 18px;
            margin-left: -33px;
            border-radius: 999px 999px 10px 10px;
            background: #111f3f;
            z-index: 4;
        }

        .student-cap::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 6px;
            width: 10px;
            height: 36px;
            margin-left: -5px;
            border-radius: 8px;
            background: linear-gradient(180deg, #ffcf57, rgba(255, 207, 87, 0.18));
        }

        .student-body {
            position: absolute;
            left: 50%;
            top: 46px;
            width: 88px;
            height: 166px;
            margin-left: -44px;
            border-radius: 34px 34px 22px 22px;
            background: linear-gradient(180deg, #fffaf2, #efe1c3);
            z-index: 1;
        }

        .student-body::before {
            content: "";
            position: absolute;
            inset: 24px 18px 12px;
            border-radius: 24px;
            background: linear-gradient(180deg, #0f6d7f, #1a8ca0);
        }

        .student-body::after {
            content: "";
            position: absolute;
            top: 24px;
            left: 50%;
            width: 18px;
            height: 124px;
            margin-left: -9px;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffd568, #f0a91d);
            z-index: 2;
        }

        .student--girl .student-body {
            width: 96px;
            margin-left: -48px;
        }

        .student-book {
            position: absolute;
            bottom: 34px;
            left: 50%;
            width: 62px;
            height: 38px;
            margin-left: -31px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f7fbff, #d8ebff);
            border: 1px solid rgba(255, 255, 255, 0.62);
            box-shadow: 0 14px 24px rgba(8, 29, 64, 0.12);
            transform: rotate(-7deg);
            z-index: 5;
        }

        .student-book::after {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 50%;
            width: 2px;
            margin-left: -1px;
            background: rgba(45, 108, 223, 0.18);
        }

        .scene-copy {
            position: absolute;
            left: 28px;
            right: 28px;
            top: 84px;
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
            font-size: 28px;
            line-height: 1.15;
            font-weight: 800;
        }

        .scene-copy__text {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
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
                    <div class="scene-card">
                        <div class="scene-header">
                            <div class="scene-logo"></div>
                            <div>SIMANSA</div>
                        </div>
                        <div class="scene-copy">
                            <div class="scene-copy__eyebrow">Layanan Madrasah</div>
                            <div class="scene-copy__title">Belajar, tertib, dan terhubung kembali sebentar lagi.</div>
                            <div class="scene-copy__text">
                                Pembaruan sistem membantu layanan siswa, guru, dan wali kelas tetap berjalan rapi.
                            </div>
                        </div>

                        <div class="students">
                            <div class="student student--boy">
                                <div class="student-head"></div>
                                <div class="student-cap"></div>
                                <div class="student-body"></div>
                                <div class="student-book"></div>
                            </div>
                            <div class="student student--girl">
                                <div class="student-head"></div>
                                <div class="student-body"></div>
                                <div class="student-book"></div>
                            </div>
                            <div class="student student--boy">
                                <div class="student-head"></div>
                                <div class="student-cap"></div>
                                <div class="student-body"></div>
                                <div class="student-book"></div>
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
