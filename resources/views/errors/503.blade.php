<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#173a78">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="refresh" content="30">
    <title>Pemeliharaan Sistem | SIMANSA</title>
    <style>
        :root {
            color-scheme: light;
            --navy-950: #0a1d3b;
            --navy-900: #102b58;
            --navy-800: #173a78;
            --blue-600: #3268e8;
            --blue-500: #4f7df2;
            --cyan-400: #38c6d9;
            --green-500: #25ad7d;
            --amber-500: #f4a623;
            --ink: #12223e;
            --muted: #637491;
            --line: #dce5f3;
            --surface: #ffffff;
            --soft: #f4f7fc;
            --shadow: 0 28px 80px rgba(20, 49, 99, .16);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: #edf3fb;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
            background:
                radial-gradient(circle at 10% 8%, rgba(79, 125, 242, .16), transparent 27rem),
                radial-gradient(circle at 92% 92%, rgba(56, 198, 217, .14), transparent 25rem),
                #edf3fb;
        }

        button {
            font: inherit;
        }

        .maintenance-page {
            position: relative;
            isolation: isolate;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            padding: clamp(18px, 4vw, 56px);
            overflow: hidden;
        }

        .maintenance-page::before,
        .maintenance-page::after {
            content: "";
            position: fixed;
            z-index: -1;
            border-radius: 999px;
            pointer-events: none;
        }

        .maintenance-page::before {
            width: 260px;
            height: 260px;
            top: -110px;
            right: 10%;
            border: 44px solid rgba(50, 104, 232, .06);
        }

        .maintenance-page::after {
            width: 180px;
            height: 180px;
            left: -70px;
            bottom: -65px;
            border: 32px solid rgba(37, 173, 125, .06);
        }

        .maintenance-card {
            width: min(1160px, 100%);
            min-width: 0;
            max-width: 100%;
            margin: auto;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 30px;
            background: rgba(255, 255, 255, .94);
            box-shadow: var(--shadow);
        }

        .brand-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px clamp(22px, 4vw, 42px);
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .96);
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand__logo {
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid #d8e3f5;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(23, 58, 120, .1);
        }

        .brand__logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .brand__name {
            overflow: hidden;
        }

        .brand__name strong,
        .brand__name span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .brand__name strong {
            color: var(--navy-900);
            font-size: 18px;
            line-height: 1.3;
            letter-spacing: .01em;
        }

        .brand__name span {
            margin-top: 2px;
            color: var(--muted);
            font-size: 13px;
        }

        .service-code {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border: 1px solid #d9e4f6;
            border-radius: 999px;
            color: #4f6486;
            background: var(--soft);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(340px, .82fr);
            min-height: 540px;
            min-width: 0;
        }

        .content {
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(34px, 5.5vw, 72px);
        }

        .status-badge {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 8px 12px;
            border-radius: 999px;
            color: #176c53;
            background: #e9f8f2;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .status-badge__dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--green-500);
            box-shadow: 0 0 0 0 rgba(37, 173, 125, .3);
            animation: status-pulse 2.2s ease-out infinite;
        }

        h1 {
            max-width: 720px;
            margin: 22px 0 16px;
            color: var(--navy-950);
            font-size: clamp(38px, 5vw, 64px);
            line-height: 1.02;
            letter-spacing: -.045em;
            overflow-wrap: anywhere;
        }

        .intro {
            max-width: 660px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(15px, 1.7vw, 18px);
            line-height: 1.75;
        }

        .notice {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #d8e3f5;
            border-radius: 20px;
            background: var(--soft);
        }

        .notice__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .notice__title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--navy-900);
            font-size: 14px;
            font-weight: 800;
        }

        .notice__spinner {
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            border: 3px solid #ccd9ef;
            border-top-color: var(--blue-600);
            border-radius: 50%;
            animation: spin .9s linear infinite;
        }

        .countdown {
            flex: 0 0 auto;
            color: var(--blue-600);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .activity-track {
            position: relative;
            height: 7px;
            margin: 17px 0 13px;
            overflow: hidden;
            border-radius: 999px;
            background: #dfe8f7;
        }

        .activity-track::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -35%;
            width: 38%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--blue-600), var(--cyan-400));
            animation: activity 2.2s ease-in-out infinite;
        }

        .notice__text {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 22px;
        }

        .retry-button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 19px;
            border: 0;
            border-radius: 13px;
            color: #fff;
            background: var(--blue-600);
            box-shadow: 0 10px 22px rgba(50, 104, 232, .22);
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: transform .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .retry-button:hover {
            transform: translateY(-1px);
            background: #2458d6;
            box-shadow: 0 13px 28px rgba(50, 104, 232, .28);
        }

        .retry-button:focus-visible {
            outline: 3px solid rgba(50, 104, 232, .28);
            outline-offset: 3px;
        }

        .retry-button svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .actions__hint {
            color: var(--muted);
            font-size: 13px;
        }

        .visual {
            position: relative;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: clamp(30px, 4.5vw, 52px);
            color: #fff;
            background:
                radial-gradient(circle at 110% -10%, rgba(56, 198, 217, .38), transparent 40%),
                linear-gradient(155deg, var(--navy-800), var(--navy-950));
        }

        .visual::before {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            right: -110px;
            bottom: -110px;
            border: 42px solid rgba(255, 255, 255, .045);
            border-radius: 50%;
        }

        .visual__header,
        .visual__body,
        .visual__footer {
            position: relative;
            z-index: 1;
        }

        .visual__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .visual__label {
            color: rgba(255, 255, 255, .7);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .visual__live {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 999px;
            color: rgba(255, 255, 255, .82);
            background: rgba(255, 255, 255, .07);
            font-size: 11px;
            font-weight: 800;
        }

        .visual__live::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #67e7bd;
        }

        .maintenance-icon {
            width: 88px;
            height: 88px;
            display: grid;
            place-items: center;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 25px;
            background: rgba(255, 255, 255, .09);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
        }

        .maintenance-icon svg {
            width: 43px;
            height: 43px;
            fill: none;
            stroke: #8fe8f1;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.7;
        }

        .visual h2 {
            max-width: 380px;
            margin: 0;
            font-size: clamp(27px, 3vw, 38px);
            line-height: 1.12;
            letter-spacing: -.025em;
        }

        .visual__description {
            max-width: 380px;
            margin: 15px 0 0;
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
            line-height: 1.7;
        }

        .service-list {
            display: grid;
            gap: 10px;
            margin-top: 28px;
        }

        .service-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px 13px;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 13px;
            color: rgba(255, 255, 255, .82);
            background: rgba(255, 255, 255, .055);
            font-size: 12px;
            font-weight: 700;
        }

        .service-item__check {
            width: 21px;
            height: 21px;
            flex: 0 0 21px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #8fe8f1;
            background: rgba(143, 232, 241, .12);
        }

        .service-item__check svg {
            width: 12px;
            height: 12px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2.3;
        }

        .visual__footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .54);
            font-size: 11px;
            line-height: 1.6;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes activity {
            0% { left: -38%; width: 34%; }
            50% { width: 48%; }
            100% { left: 104%; width: 34%; }
        }

        @keyframes status-pulse {
            0% { box-shadow: 0 0 0 0 rgba(37, 173, 125, .34); }
            70% { box-shadow: 0 0 0 9px rgba(37, 173, 125, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 173, 125, 0); }
        }

        @media (max-width: 900px) {
            .maintenance-page {
                align-items: flex-start;
                overflow: visible;
            }

            .layout {
                grid-template-columns: minmax(0, 1fr);
                min-height: 0;
            }

            .content {
                padding: clamp(30px, 7vw, 52px);
            }

            .visual {
                min-height: 0;
                padding: 32px clamp(30px, 7vw, 52px);
            }

            .visual__body {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr);
                gap: 0 24px;
                align-items: start;
                margin-top: 32px;
            }

            .maintenance-icon {
                grid-row: span 3;
                margin: 0;
            }

            .service-list {
                grid-column: 1 / -1;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .maintenance-page {
                padding: 0;
                background: #fff;
            }

            .maintenance-card {
                min-height: 100vh;
                min-height: 100dvh;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .brand-bar {
                padding: 15px 18px;
            }

            .brand__logo {
                width: 44px;
                height: 44px;
                flex-basis: 44px;
                border-radius: 13px;
            }

            .brand__logo img {
                width: 35px;
                height: 35px;
            }

            .brand__name strong {
                font-size: 16px;
            }

            .brand__name span {
                font-size: 11px;
            }

            .service-code {
                display: none;
                padding: 8px 10px;
                font-size: 10px;
            }

            .content {
                padding: 30px 20px 34px;
            }

            h1 {
                margin-top: 19px;
                font-size: clamp(35px, 11vw, 48px);
            }

            .intro {
                font-size: 15px;
                line-height: 1.65;
            }

            .notice {
                margin-top: 24px;
                padding: 17px;
                border-radius: 17px;
            }

            .notice__top {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .actions {
                align-items: stretch;
                flex-direction: column;
            }

            .retry-button {
                width: 100%;
            }

            .actions__hint {
                text-align: center;
            }

            .visual {
                padding: 28px 20px 30px;
            }

            .visual__header {
                flex-wrap: wrap;
            }

            .visual__body {
                display: block;
                margin-top: 28px;
            }

            .maintenance-icon {
                width: 68px;
                height: 68px;
                margin-bottom: 20px;
                border-radius: 20px;
            }

            .maintenance-icon svg {
                width: 34px;
                height: 34px;
            }

            .service-list {
                display: grid;
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 380px) {
            .brand__name span {
                display: none;
            }

            .service-code {
                letter-spacing: 0;
            }
        }

        /*
         * Single viewport contract:
         * halaman 503 harus selalu muat tanpa scroll vertikal maupun horizontal.
         */
        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .maintenance-page {
            width: 100%;
            max-width: 100vw;
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            padding: clamp(12px, 2vw, 24px);
            overflow: hidden;
        }

        .maintenance-card {
            max-width: 100%;
            height: min(680px, calc(100dvh - clamp(24px, 4vw, 48px)));
            max-height: calc(100dvh - clamp(24px, 4vw, 48px));
            display: flex;
            flex-direction: column;
            border-radius: 24px;
        }

        .brand-bar {
            flex: 0 0 auto;
            padding: 12px clamp(18px, 3vw, 30px);
        }

        .brand__logo {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 12px;
        }

        .brand__logo img {
            width: 34px;
            height: 34px;
        }

        .layout {
            width: 100%;
            max-width: 100%;
            flex: 1 1 auto;
            min-height: 0;
            grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
        }

        .content {
            width: 100%;
            max-width: 100%;
            min-height: 0;
            overflow: hidden;
            padding: clamp(24px, 4vh, 42px) clamp(24px, 4vw, 48px);
        }

        .status-badge {
            padding: 7px 11px;
            font-size: 11px;
        }

        h1 {
            margin: clamp(14px, 2.5vh, 22px) 0 12px;
            font-size: clamp(34px, 4.1vw, 52px);
            line-height: 1.04;
        }

        .intro {
            font-size: clamp(14px, 1.35vw, 16px);
            line-height: 1.55;
        }

        .notice {
            margin-top: clamp(16px, 2.8vh, 24px);
            padding: 15px 17px;
            border-radius: 16px;
        }

        .activity-track {
            margin: 13px 0 10px;
        }

        .actions {
            gap: 12px;
            margin-top: clamp(14px, 2.5vh, 20px);
        }

        .retry-button {
            min-height: 42px;
        }

        .visual {
            min-height: 0;
            padding: clamp(24px, 4vh, 38px) clamp(24px, 3vw, 38px);
        }

        .maintenance-icon {
            width: 68px;
            height: 68px;
            margin-bottom: 18px;
            border-radius: 20px;
        }

        .maintenance-icon svg {
            width: 34px;
            height: 34px;
        }

        .visual h2 {
            font-size: clamp(24px, 2.4vw, 32px);
        }

        .visual__description {
            margin-top: 10px;
            line-height: 1.55;
        }

        .service-list {
            gap: 8px;
            margin-top: 18px;
        }

        .service-item {
            padding: 9px 11px;
        }

        .visual__footer {
            margin-top: 18px;
            padding-top: 14px;
        }

        @media (max-width: 900px) {
            .maintenance-page {
                align-items: center;
                padding: clamp(10px, 2.5vw, 20px);
                overflow: hidden;
            }

            .maintenance-card {
                width: min(680px, 100%);
                height: min(620px, calc(100dvh - clamp(20px, 5vw, 40px)));
                max-height: calc(100dvh - clamp(20px, 5vw, 40px));
            }

            .layout {
                display: block;
                min-height: 0;
            }

            .content {
                height: 100%;
                justify-content: center;
                padding: clamp(22px, 5vh, 40px) clamp(22px, 6vw, 42px);
            }

            .visual {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .maintenance-page {
                padding: 0;
            }

            .maintenance-card {
                width: 100%;
                height: 100dvh;
                max-height: 100dvh;
                min-height: 0;
            }

            .brand-bar {
                padding: 10px 16px;
            }

            .brand__logo {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
            }

            .brand__logo img {
                width: 31px;
                height: 31px;
            }

            .content {
                height: 100%;
                padding: clamp(18px, 4vh, 28px) 18px;
            }

            .content > *,
            .notice,
            .actions {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }

            h1 {
                margin: 13px 0 9px;
                font-size: clamp(29px, 9vw, 40px);
                overflow-wrap: anywhere;
            }

            .intro {
                font-size: 14px;
                line-height: 1.48;
            }

            .notice {
                margin-top: 15px;
                padding: 13px 14px;
            }

            .notice__top {
                gap: 6px;
            }

            .notice__title,
            .countdown {
                min-width: 0;
                white-space: normal;
            }

            .notice__text {
                font-size: 12px;
                line-height: 1.45;
            }

            .activity-track {
                margin: 10px 0 8px;
            }

            .actions {
                gap: 8px;
                margin-top: 13px;
            }

            .retry-button {
                max-width: 100%;
            }
        }

        @media (max-height: 560px) {
            .maintenance-card {
                width: min(760px, 100%);
            }

            .layout {
                display: block;
            }

            .visual {
                display: none;
            }

            .brand__name span,
            .notice__text,
            .actions__hint {
                display: none;
            }

            .content {
                padding-top: 14px;
                padding-bottom: 14px;
            }

            h1 {
                margin: 9px 0 7px;
                font-size: clamp(27px, 7vh, 36px);
            }

            .intro {
                font-size: 12px;
                line-height: 1.35;
            }

            .notice {
                margin-top: 10px;
                padding: 10px 12px;
            }

            .activity-track {
                margin: 8px 0 0;
            }

            .actions {
                margin-top: 10px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="maintenance-page">
        <section class="maintenance-card" aria-labelledby="maintenanceTitle">
            <header class="brand-bar">
                <div class="brand">
                    <span class="brand__logo">
                        <img src="/storage/settings/logo/logo-sekolah-1772827224.png"
                             alt="Logo MAN 1 Metro">
                    </span>
                    <span class="brand__name">
                        <strong>SIMANSA</strong>
                        <span>Sistem Informasi MAN 1 Metro</span>
                    </span>
                </div>
                <span class="service-code">HTTP 503</span>
            </header>

            <div class="layout">
                <section class="content">
                    <div class="status-badge">
                        <span class="status-badge__dot" aria-hidden="true"></span>
                        Pemeliharaan terjadwal
                    </div>

                    <h1 id="maintenanceTitle">Layanan sedang dalam pemeliharaan.</h1>
                    <p class="intro">
                        Tim kami sedang menerapkan pembaruan agar SIMANSA kembali lebih stabil, aman,
                        dan nyaman digunakan. Data Anda tetap tersimpan dengan aman selama proses berlangsung.
                    </p>

                    <div class="notice" aria-live="polite">
                        <div class="notice__top">
                            <div class="notice__title">
                                <span class="notice__spinner" aria-hidden="true"></span>
                                Sistem sedang menyiapkan layanan
                            </div>
                            <span class="countdown">
                                Cek ulang dalam <span id="refreshCountdown">30</span> detik
                            </span>
                        </div>
                        <div class="activity-track" aria-hidden="true"></div>
                        <p class="notice__text">
                            Halaman akan mencoba terhubung kembali secara otomatis. Anda juga dapat mencoba
                            sekarang tanpa perlu menutup halaman ini.
                        </p>
                    </div>

                    <div class="actions">
                        <button type="button" class="retry-button" id="retryButton">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20 11a8 8 0 1 0-2.3 5.7"></path>
                                <path d="M20 4v7h-7"></path>
                            </svg>
                            Coba akses kembali
                        </button>
                        <span class="actions__hint">Terima kasih atas pengertiannya.</span>
                    </div>
                </section>

                <aside class="visual" aria-label="Informasi pemeliharaan">
                    <div class="visual__header">
                        <span class="visual__label">Status operasional</span>
                        <span class="visual__live">Sedang diproses</span>
                    </div>

                    <div class="visual__body">
                        <div class="maintenance-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M14.7 6.3a4 4 0 0 0-5 5L3.5 17.5a2.1 2.1 0 0 0 3 3l6.2-6.2a4 4 0 0 0 5-5l-2.6 2.6-3-3 2.6-2.6Z"></path>
                                <path d="m16 16 4.5 4.5"></path>
                                <path d="m14.5 17.5 3-3"></path>
                            </svg>
                        </div>

                        <h2>Pembaruan dilakukan dengan aman dan terkendali.</h2>
                        <p class="visual__description">
                            Akses sementara dihentikan untuk mencegah perubahan data selama pembaruan sistem.
                        </p>

                        <div class="service-list">
                            <div class="service-item">
                                <span class="service-item__check">
                                    <svg viewBox="0 0 16 16"><path d="m3 8.5 3 3 7-7"></path></svg>
                                </span>
                                Integritas data dijaga
                            </div>
                            <div class="service-item">
                                <span class="service-item__check">
                                    <svg viewBox="0 0 16 16"><path d="m3 8.5 3 3 7-7"></path></svg>
                                </span>
                                Akses dipulihkan otomatis
                            </div>
                            <div class="service-item">
                                <span class="service-item__check">
                                    <svg viewBox="0 0 16 16"><path d="m3 8.5 3 3 7-7"></path></svg>
                                </span>
                                Tidak perlu login ulang
                            </div>
                        </div>
                    </div>

                    <footer class="visual__footer">
                        MAN 1 Metro · Layanan administrasi dan akademik digital
                    </footer>
                </aside>
            </div>
        </section>
    </main>

    <script>
        (function () {
            'use strict';

            let remaining = 30;
            const countdown = document.getElementById('refreshCountdown');
            const retryButton = document.getElementById('retryButton');

            const reloadPage = function () {
                if (retryButton) {
                    retryButton.disabled = true;
                    retryButton.textContent = 'Menghubungkan kembali...';
                }
                window.location.reload();
            };

            if (retryButton) {
                retryButton.addEventListener('click', reloadPage);
            }

            if (!countdown) {
                return;
            }

            window.setInterval(function () {
                remaining -= 1;
                countdown.textContent = Math.max(remaining, 0);

                if (remaining <= 0) {
                    reloadPage();
                }
            }, 1000);
        })();
    </script>
</body>
</html>
