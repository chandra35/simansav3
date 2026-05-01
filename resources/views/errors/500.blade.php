<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="15">
    <title>Kendala Sistem</title>
    <style>
        :root {
            --surface: rgba(255,255,255,.9);
            --surface-strong: rgba(255,255,255,.96);
            --line: rgba(37,99,235,.12);
            --text: #173462;
            --muted: #6b84aa;
            --primary: #2d6cdf;
            --danger: #dc5a74;
            --accent: #7c3aed;
            --shadow: 0 24px 72px rgba(28,64,121,.14);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(45,108,223,.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(124,58,237,.1), transparent 24%),
                linear-gradient(160deg, #f8fbff 0%, #eef5ff 54%, #fbfcff 100%);
            color: var(--text);
        }

        .shell {
            width: min(1060px,100%);
            background: var(--surface);
            border: 1px solid rgba(255,255,255,.82);
            border-radius: 28px;
            backdrop-filter: blur(18px);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0,1.08fr) minmax(320px,.92fr);
            min-height: 600px;
        }

        .content {
            padding: 54px 52px 44px;
        }

        .eyebrow {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(45,108,223,.1);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .eyebrow-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--danger);
            box-shadow: 0 0 0 0 rgba(220,90,116,.3);
            animation: pulse 2s infinite;
        }

        h1 {
            margin: 22px 0 14px;
            font-size: clamp(34px, 4vw, 54px);
            line-height: 1.05;
        }

        .lead {
            margin: 0 0 26px;
            max-width: 620px;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.8;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 14px;
        }

        .info-card {
            padding: 18px;
            min-height: 104px;
            border-radius: 18px;
            background: var(--surface-strong);
            border: 1px solid var(--line);
        }

        .info-card__label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .info-card__value {
            margin-top: 10px;
            font-size: 16px;
            line-height: 1.55;
            font-weight: 700;
        }

        .panel {
            margin-top: 18px;
            padding: 20px 22px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(45,108,223,.08), rgba(124,58,237,.08));
            border: 1px solid rgba(45,108,223,.1);
        }

        .panel__title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 12px;
            font-size: 15px;
            font-weight: 700;
        }

        .countdown {
            min-width: 118px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,.88);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(45,108,223,.12);
        }

        .panel__text {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.75;
        }

        .progress {
            position: relative;
            height: 11px;
            margin-top: 12px;
            border-radius: 999px;
            background: rgba(45,108,223,.1);
            overflow: hidden;
        }

        .progress::before {
            content: "";
            position: absolute;
            inset: 0;
            width: 42%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), var(--danger), var(--accent));
            animation: slide 2.8s ease-in-out infinite;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .btn-link--primary {
            background: linear-gradient(135deg, #2d6cdf, #3e7ff0);
            color: #fff;
            box-shadow: 0 16px 30px rgba(45,108,223,.22);
        }

        .btn-link--secondary {
            background: rgba(255,255,255,.86);
            color: var(--text);
            border: 1px solid var(--line);
        }

        .visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 36px;
        }

        .visual::before {
            content: "";
            position: absolute;
            inset: 32px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.76), rgba(255,255,255,.42)),
                radial-gradient(circle at top, rgba(45,108,223,.06), transparent 56%);
            border: 1px solid rgba(255,255,255,.72);
        }

        .scene {
            position: relative;
            z-index: 1;
            width: min(360px,100%);
            aspect-ratio: 1 / 1.04;
        }

        .scene-panel {
            position: absolute;
            inset: 34px 18px 18px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(25,66,138,.98), rgba(30,99,170,.88));
            box-shadow: 0 30px 70px rgba(19,49,94,.24);
            overflow: hidden;
            padding: 24px;
        }

        .scene-top {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,.92);
            font-size: 13px;
            font-weight: 700;
        }

        .scene-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255,255,255,.16) url('{{ asset('storage/settings/logo/logo-sekolah-1772827224.png') }}') center/28px no-repeat;
            border: 1px solid rgba(255,255,255,.18);
        }

        .diagnostic {
            margin-top: 20px;
            padding: 20px 18px 18px;
            border-radius: 22px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
        }

        .diagnostic__title {
            font-size: 27px;
            line-height: 1.16;
            font-weight: 800;
            color: rgba(255,255,255,.96);
        }

        .diagnostic__text {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255,255,255,.8);
        }

        .diag-board {
            margin-top: 18px;
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(10,28,62,.84), rgba(17,53,96,.68));
            border: 1px solid rgba(255,255,255,.12);
        }

        .diag-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid rgba(255,255,255,.08);
            font-size: 13px;
            color: rgba(255,255,255,.78);
        }

        .diag-row:last-child { border-bottom: 0; }

        .diag-badge {
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.1);
            color: rgba(255,255,255,.9);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220,90,116,.3); }
            70% { box-shadow: 0 0 0 14px rgba(220,90,116,0); }
            100% { box-shadow: 0 0 0 0 rgba(220,90,116,0); }
        }

        @keyframes slide {
            0% { transform: translateX(-28%); width: 36%; }
            50% { transform: translateX(72%); width: 42%; }
            100% { transform: translateX(-28%); width: 36%; }
        }

        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
            .content { padding: 32px 24px 18px; }
            .visual { padding: 0 22px 30px; }
            .info-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            body { padding: 14px; }
            .shell { border-radius: 22px; }
            .panel__title { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    @php
        $homeUrl = function_exists('getDashboardRoute') ? getDashboardRoute() : url('/');
    @endphp

    <main class="shell" role="main">
        <div class="grid">
            <section class="content">
                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    Status Kendala Sistem
                </div>

                <h1>Sistem sedang menangani kendala agar layanan kembali stabil.</h1>
                <p class="lead">
                    Kami mendeteksi gangguan internal saat memuat halaman ini. Biasanya kondisi ini bersifat sementara,
                    dan sistem akan mencoba pulih sambil menjaga layanan utama tetap aman.
                </p>

                <div class="info-grid">
                    <article class="info-card">
                        <div class="info-card__label">Status</div>
                        <div class="info-card__value">500 Internal Server Error</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Dampak</div>
                        <div class="info-card__value">Halaman belum dapat dimuat normal</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Saran</div>
                        <div class="info-card__value">Tunggu sebentar, lalu muat ulang halaman ini.</div>
                    </article>
                </div>

                <div class="panel">
                    <div class="panel__title">
                        <span>Proses pemulihan sedang berjalan</span>
                        <span class="countdown">Refresh <span id="refreshCountdown" style="margin-left:6px;">15</span>d</span>
                    </div>
                    <div class="progress"></div>
                    <p class="panel__text">
                        Jika gangguan hanya sementara, halaman akan mencoba dimuat ulang otomatis setelah hitung mundur selesai.
                    </p>
                </div>

                <div class="actions">
                    <a href="{{ $homeUrl }}" class="btn-link btn-link--primary">Kembali ke Dashboard</a>
                    <a href="{{ url()->current() }}" class="btn-link btn-link--secondary">Muat Ulang Halaman</a>
                </div>
            </section>

            <aside class="visual" aria-hidden="true">
                <div class="scene">
                    <div class="scene-panel">
                        <div class="scene-top">
                            <div class="scene-logo"></div>
                            <div>SIMANSA</div>
                        </div>
                        <div class="diagnostic">
                            <div class="diagnostic__title">Proses diagnosis dan pemulihan sedang aktif.</div>
                            <div class="diagnostic__text">Sistem memeriksa stabilitas layanan, status cache, dan respons aplikasi sebelum halaman dipulihkan.</div>
                            <div class="diag-board">
                                <div class="diag-row">
                                    <span>Status layanan inti</span>
                                    <span class="diag-badge">Memeriksa</span>
                                </div>
                                <div class="diag-row">
                                    <span>Pembaruan sesi aplikasi</span>
                                    <span class="diag-badge">Dipulihkan</span>
                                </div>
                                <div class="diag-row">
                                    <span>Sinkronisasi cache</span>
                                    <span class="diag-badge">Berjalan</span>
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
            let remaining = 15;
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
