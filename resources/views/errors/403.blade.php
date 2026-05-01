<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak</title>
    <style>
        :root {
            --bg: #f8fbff;
            --surface: rgba(255, 255, 255, 0.88);
            --surface-strong: rgba(255, 255, 255, 0.96);
            --line: rgba(30, 64, 175, 0.12);
            --text: #15315f;
            --muted: #6983ab;
            --primary: #265fd6;
            --danger: #dc5a74;
            --warning: #f3a53b;
            --shadow: 0 24px 72px rgba(28, 64, 121, 0.14);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(38, 95, 214, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(220, 90, 116, 0.11), transparent 24%),
                linear-gradient(160deg, #f8fbff 0%, #eef4ff 54%, #fdfcff 100%);
            color: var(--text);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }

        .page-shell {
            position: relative;
            width: min(1080px, 100%);
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.84);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(38, 95, 214, 0.06), transparent 32%),
                radial-gradient(circle at 84% 18%, rgba(220, 90, 116, 0.09), transparent 18%);
        }

        .page-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
            min-height: 610px;
        }

        .content-pane {
            padding: 54px 52px 44px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(38, 95, 214, 0.1);
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
            box-shadow: 0 0 0 0 rgba(220, 90, 116, 0.32);
            animation: pulse 2s infinite;
        }

        h1 {
            margin: 22px 0 14px;
            font-size: clamp(34px, 4vw, 54px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .lead {
            max-width: 620px;
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
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .info-card__value {
            margin-top: 10px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.55;
            color: var(--text);
        }

        .message-panel {
            margin-top: 18px;
            padding: 20px 22px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(38, 95, 214, 0.08), rgba(220, 90, 116, 0.08));
            border: 1px solid rgba(38, 95, 214, 0.12);
        }

        .message-panel__title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .message-panel__code {
            margin: 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(220, 90, 116, 0.16);
            color: #8d2942;
            font-size: 13px;
            line-height: 1.7;
            word-break: break-word;
        }

        .help-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .help-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 13px 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--line);
        }

        .help-item__icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(38, 95, 214, 0.1);
            color: var(--primary);
        }

        .help-item__icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .help-item__title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .help-item__text {
            font-size: 14px;
            line-height: 1.6;
            color: var(--muted);
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-link:hover {
            transform: translateY(-1px);
        }

        .btn-link--primary {
            background: linear-gradient(135deg, #2d6cdf, #3e7ff0);
            color: #fff;
            box-shadow: 0 16px 30px rgba(45, 108, 223, 0.22);
        }

        .btn-link--secondary {
            background: rgba(255, 255, 255, 0.86);
            color: var(--text);
            border: 1px solid var(--line);
        }

        .btn-link svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .visual-pane {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 36px;
        }

        .visual-pane::before {
            content: "";
            position: absolute;
            inset: 32px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(255, 255, 255, 0.42)),
                radial-gradient(circle at top, rgba(38, 95, 214, 0.06), transparent 56%);
            border: 1px solid rgba(255, 255, 255, 0.72);
        }

        .shield-scene {
            position: relative;
            z-index: 1;
            width: min(360px, 100%);
            aspect-ratio: 1 / 1.04;
        }

        .shield-orb {
            position: absolute;
            border-radius: 999px;
        }

        .shield-orb--one {
            width: 172px;
            height: 172px;
            top: 10px;
            right: 4px;
            background: radial-gradient(circle, rgba(38, 95, 214, 0.24), rgba(38, 95, 214, 0.03));
            animation: floatY 8s ease-in-out infinite;
        }

        .shield-orb--two {
            width: 132px;
            height: 132px;
            bottom: 26px;
            left: 14px;
            background: radial-gradient(circle, rgba(220, 90, 116, 0.2), rgba(220, 90, 116, 0.03));
            animation: floatY 8.8s ease-in-out infinite reverse;
        }

        .shield-panel {
            position: absolute;
            inset: 34px 18px 18px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(28, 67, 138, 0.98), rgba(54, 96, 181, 0.9));
            padding: 24px;
            box-shadow: 0 30px 70px rgba(19, 49, 94, 0.24);
            overflow: hidden;
        }

        .shield-panel::before {
            content: "";
            position: absolute;
            inset: auto -16% -18% 10%;
            height: 50%;
            background:
                radial-gradient(circle at 24% 24%, rgba(255, 255, 255, 0.26), transparent 24%),
                radial-gradient(circle at 80% 28%, rgba(255, 255, 255, 0.18), transparent 20%);
            animation: drift 14s ease-in-out infinite alternate;
        }

        .shield-top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            font-weight: 700;
        }

        .shield-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16) url('{{ asset('storage/settings/logo/logo-sekolah-1772827224.png') }}') center/28px no-repeat;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .shield-console {
            position: relative;
            z-index: 1;
            margin-top: 20px;
            padding: 20px 18px 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(8px);
        }

        .shield-title {
            font-size: 28px;
            line-height: 1.16;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.96);
        }

        .shield-text {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
        }

        .shield-diagram {
            margin-top: 18px;
            padding: 18px 14px;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(10, 28, 62, 0.84), rgba(17, 53, 96, 0.68));
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .shield-core {
            width: 132px;
            height: 132px;
            margin: 4px auto 14px;
            border-radius: 999px;
            position: relative;
            background:
                radial-gradient(circle at center, rgba(255,255,255,0.12), rgba(255,255,255,0.04) 62%, transparent 63%),
                radial-gradient(circle at center, rgba(255,255,255,0.08), transparent 70%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shield-core::before,
        .shield-core::after {
            content: "";
            position: absolute;
            inset: 12px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .shield-core::after {
            inset: 24px;
            border-color: rgba(255, 255, 255, 0.08);
        }

        .shield-badge {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 16px 28px rgba(0, 0, 0, 0.14);
        }

        .shield-badge svg {
            width: 34px;
            height: 34px;
            stroke: #fff;
            fill: none;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .shield-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .shield-stat {
            padding: 12px 10px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .shield-stat__label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.64);
        }

        .shield-stat__value {
            margin-top: 6px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.94);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 90, 116, 0.32); }
            70% { box-shadow: 0 0 0 14px rgba(220, 90, 116, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 90, 116, 0); }
        }

        @keyframes floatY {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes drift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(12px, -10px, 0); }
        }

        @media (max-width: 960px) {
            .page-grid {
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

            .page-shell {
                border-radius: 22px;
            }

            .shield-scene {
                width: min(320px, 100%);
            }
        }
    </style>
</head>
<body>
    @php
        $dashboardUrl = function_exists('getDashboardRoute') ? getDashboardRoute() : url('/');
        $backUrl = url()->previous() !== url()->current() ? url()->previous() : $dashboardUrl;
    @endphp

    <main class="page-shell" role="main">
        <div class="page-grid">
            <section class="content-pane">
                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    Status Otorisasi Sistem
                </div>

                <h1>Akses ke halaman ini dibatasi oleh sistem.</h1>
                <p class="lead">
                    Anda berhasil mencapai layanan yang benar, tetapi akun atau sesi Anda belum memiliki izin untuk membuka halaman ini.
                    Kami sarankan kembali ke dashboard atau menghubungi administrator jika akses ini memang dibutuhkan.
                </p>

                <div class="info-grid">
                    <article class="info-card">
                        <div class="info-card__label">Status</div>
                        <div class="info-card__value">403 Forbidden</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Akses</div>
                        <div class="info-card__value">Permission belum sesuai</div>
                    </article>
                    <article class="info-card">
                        <div class="info-card__label">Saran</div>
                        <div class="info-card__value">Kembali ke area yang tersedia untuk akun Anda.</div>
                    </article>
                </div>

                <div class="message-panel">
                    <div class="message-panel__title">Pesan sistem</div>
                    <p class="message-panel__code">{{ $exception->getMessage() ?: 'Unauthorized action.' }}</p>
                </div>

                <div class="help-list">
                    <div class="help-item">
                        <div class="help-item__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3.5 18.5 6v5.8c0 4-2.6 6.8-6.5 8.7-3.9-1.9-6.5-4.7-6.5-8.7V6L12 3.5Z"></path>
                                <path d="m9.6 12 1.7 1.8 3.2-3.5"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="help-item__title">Periksa izin akun</div>
                            <div class="help-item__text">Bisa jadi halaman ini memang dibatasi untuk role tertentu seperti admin, operator, atau wali kelas.</div>
                        </div>
                    </div>

                    <div class="help-item">
                        <div class="help-item__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 12h16"></path>
                                <path d="m10 6-6 6 6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="help-item__title">Gunakan jalur navigasi yang aman</div>
                            <div class="help-item__text">Lebih baik kembali ke dashboard, lalu buka menu yang memang tersedia agar alur kerja tetap stabil.</div>
                        </div>
                    </div>

                    <div class="help-item">
                        <div class="help-item__icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 8v4"></path>
                                <path d="M12 16h.01"></path>
                                <circle cx="12" cy="12" r="8"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="help-item__title">Hubungi admin bila perlu</div>
                            <div class="help-item__text">Jika Anda merasa seharusnya punya akses ke halaman ini, admin dapat memeriksa role dan permission akun Anda.</div>
                        </div>
                    </div>
                </div>

                <div class="action-row">
                    <a href="{{ $dashboardUrl }}" class="btn-link btn-link--primary">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 10.5 12 3l9 7.5"></path>
                            <path d="M5.5 9.5V20h13V9.5"></path>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ $backUrl }}" class="btn-link btn-link--secondary">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M19 12H5"></path>
                            <path d="m11 18-6-6 6-6"></path>
                        </svg>
                        Halaman Sebelumnya
                    </a>
                </div>
            </section>

            <aside class="visual-pane" aria-hidden="true">
                <div class="shield-scene">
                    <div class="shield-orb shield-orb--one"></div>
                    <div class="shield-orb shield-orb--two"></div>

                    <div class="shield-panel">
                        <div class="shield-top">
                            <div class="shield-logo"></div>
                            <div>SIMANSA</div>
                        </div>

                        <div class="shield-console">
                            <div class="shield-title">Area ini dijaga oleh kontrol izin sistem.</div>
                            <div class="shield-text">
                                Halaman tetap aman, dan akses hanya diberikan kepada akun yang memang memiliki peran yang sesuai.
                            </div>

                            <div class="shield-diagram">
                                <div class="shield-core">
                                    <div class="shield-badge">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 3.5 18.5 6v5.8c0 4-2.6 6.8-6.5 8.7-3.9-1.9-6.5-4.7-6.5-8.7V6L12 3.5Z"></path>
                                            <path d="m9.6 12 1.7 1.8 3.2-3.5"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div class="shield-stats">
                                    <div class="shield-stat">
                                        <div class="shield-stat__label">Status</div>
                                        <div class="shield-stat__value">Akses dibatasi</div>
                                    </div>
                                    <div class="shield-stat">
                                        <div class="shield-stat__label">Tindakan</div>
                                        <div class="shield-stat__value">Pilih menu yang tersedia</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
