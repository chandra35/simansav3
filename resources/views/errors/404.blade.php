<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan</title>
    <style>
        :root {
            --surface: rgba(255, 255, 255, 0.9);
            --surface-strong: rgba(255, 255, 255, 0.96);
            --line: rgba(37, 99, 235, 0.12);
            --text: #173462;
            --muted: #6b84aa;
            --primary: #2d6cdf;
            --teal: #13a388;
            --warning: #f59e0b;
            --shadow: 0 24px 72px rgba(28, 64, 121, 0.14);
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
                radial-gradient(circle at top left, rgba(45, 108, 223, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(19, 163, 136, 0.12), transparent 24%),
                linear-gradient(160deg, #f8fbff 0%, #eef5ff 54%, #fbfcff 100%);
            color: var(--text);
        }

        .shell {
            width: min(1060px, 100%);
            background: var(--surface);
            border: 1px solid rgba(255,255,255,.82);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0,1.08fr) minmax(320px,.92fr);
            min-height: 590px;
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
            background: var(--warning);
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
            background: linear-gradient(135deg, rgba(45,108,223,.08), rgba(19,163,136,.08));
            border: 1px solid rgba(45,108,223,.1);
        }

        .panel__title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .panel__text {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.75;
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
            aspect-ratio: 1 / 1.02;
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

        .map-board {
            position: relative;
            z-index: 1;
            margin-top: 20px;
            border-radius: 22px;
            padding: 20px 18px 18px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
        }

        .map-title {
            font-size: 26px;
            line-height: 1.16;
            font-weight: 800;
            color: rgba(255,255,255,.96);
        }

        .map-text {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255,255,255,.8);
        }

        .map-grid {
            margin-top: 18px;
            height: 168px;
            border-radius: 18px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(10,28,62,.84), rgba(17,53,96,.68)),
                repeating-linear-gradient(to right, rgba(255,255,255,.04) 0 1px, transparent 1px 38px),
                repeating-linear-gradient(to bottom, rgba(255,255,255,.04) 0 1px, transparent 1px 38px);
            border: 1px solid rgba(255,255,255,.12);
        }

        .map-path,
        .map-pin {
            position: absolute;
        }

        .map-path {
            left: 20px;
            right: 24px;
            top: 84px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(255,255,255,.15), rgba(255,255,255,.78), rgba(19,163,136,.55));
        }

        .map-path::after {
            content: "";
            position: absolute;
            right: 0;
            top: -36px;
            width: 92px;
            height: 92px;
            border-radius: 999px;
            border: 2px dashed rgba(255,255,255,.16);
        }

        .map-pin {
            width: 24px;
            height: 24px;
            border-radius: 999px 999px 999px 0;
            transform: rotate(-45deg);
            background: #fff;
            box-shadow: 0 12px 24px rgba(0,0,0,.14);
        }

        .map-pin::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 999px;
            background: var(--warning);
        }

        .map-pin--start { left: 28px; top: 36px; }
        .map-pin--mid { left: 46%; top: 92px; }
        .map-pin--end { right: 30px; top: 40px; }

        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
            .content { padding: 32px 24px 18px; }
            .visual { padding: 0 22px 30px; }
            .info-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            body { padding: 14px; }
            .shell { border-radius: 22px; }
        }
    </style>
</head>
<body>
    @php
        $homeUrl = function_exists('getDashboardRoute') ? getDashboardRoute() : url('/');
        $backUrl = url()->previous() !== url()->current() ? url()->previous() : $homeUrl;
    @endphp

    <main class="shell" role="main">
        <div class="grid">
            <section class="content">
                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    Status Halaman Sistem
                </div>

                <h1>Halaman yang Anda cari belum tersedia di jalur ini.</h1>
                <p class="lead">
                    Tautan bisa saja sudah berubah, belum tersedia, atau Anda membuka alamat yang tidak lagi digunakan.
                    Tenang, aplikasi tetap berjalan normal dan Anda bisa kembali ke area utama.
                </p>

                <div class="info-grid">
                    <article class="info-card">
                        <div class="info-card__label">Status</div>
                        <div class="info-card__value">404 Not Found</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Kemungkinan</div>
                        <div class="info-card__value">URL berubah atau tidak valid</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Saran</div>
                        <div class="info-card__value">Kembali ke dashboard atau buka menu dari sidebar.</div>
                    </article>
                </div>

                <div class="panel">
                    <div class="panel__title">Langkah yang disarankan</div>
                    <div class="panel__text">
                        Gunakan navigasi utama aplikasi untuk membuka menu yang benar, atau ulangi dari dashboard agar alur akses tetap stabil.
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ $homeUrl }}" class="btn-link btn-link--primary">Kembali ke Dashboard</a>
                    <a href="{{ $backUrl }}" class="btn-link btn-link--secondary">Halaman Sebelumnya</a>
                </div>
            </section>

            <aside class="visual" aria-hidden="true">
                <div class="scene">
                    <div class="scene-panel">
                        <div class="scene-top">
                            <div class="scene-logo"></div>
                            <div>SIMANSA</div>
                        </div>
                        <div class="map-board">
                            <div class="map-title">Titik tujuan halaman ini belum ditemukan.</div>
                            <div class="map-text">Sistem membantu Anda kembali ke jalur yang benar tanpa mengganggu layanan utama.</div>
                            <div class="map-grid">
                                <div class="map-path"></div>
                                <div class="map-pin map-pin--start"></div>
                                <div class="map-pin map-pin--mid"></div>
                                <div class="map-pin map-pin--end"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
