<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $download->title }} - SIMANSA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --ink: #e7eefb; --muted: #9cacbf; --accent: #42d6c5; --accent-deep: #0b978d; --panel: rgba(16, 29, 49, .78); }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; overflow-x: hidden; font-family: 'Manrope', sans-serif; color: var(--ink); background: #07111f; }
        body::before, body::after { position: fixed; z-index: -1; width: 46rem; height: 46rem; border-radius: 50%; content: ''; filter: blur(36px); opacity: .35; }
        body::before { top: -22rem; left: -14rem; background: #0b978d; }
        body::after { right: -18rem; bottom: -25rem; background: #345bcd; }
        .container { width: min(1160px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        .back { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 22px; color: #cfe6ea; font-size: .9rem; font-weight: 700; text-decoration: none; transition: color .2s ease, transform .2s ease; }
        .back:hover { color: var(--accent); transform: translateX(-3px); }
        .player-card { overflow: hidden; border: 1px solid rgba(173, 204, 236, .16); border-radius: 24px; background: var(--panel); box-shadow: 0 28px 70px rgba(0, 0, 0, .35); backdrop-filter: blur(16px); }
        .video-wrap { position: relative; overflow: hidden; background: #010711; aspect-ratio: 16 / 9; }
        .video-wrap::after { position: absolute; top: 0; right: 0; left: 0; height: 34%; pointer-events: none; content: ''; background: linear-gradient(180deg, rgba(1, 7, 17, .48), transparent); }
        video, iframe { width: 100%; height: 100%; border: 0; display: block; }
        .video-badge { position: absolute; z-index: 1; top: 18px; left: 18px; display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; border: 1px solid rgba(232, 255, 252, .22); border-radius: 999px; color: #eafffb; background: rgba(1, 14, 26, .65); font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; backdrop-filter: blur(8px); }
        .content { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 22px; padding: 26px; }
        .eyebrow { display: block; margin-bottom: 8px; color: var(--accent); font-size: .76rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        h1 { margin: 0 0 10px; color: #fff; font-size: clamp(1.4rem, 3vw, 2.15rem); letter-spacing: -.035em; line-height: 1.15; }
        p { max-width: 720px; margin: 0; color: var(--muted); line-height: 1.7; white-space: pre-line; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 17px; color: #c1d0df; font-size: .8rem; }
        .meta span { padding: 6px 10px; border: 1px solid rgba(173, 204, 236, .14); border-radius: 999px; background: rgba(255, 255, 255, .055); }
        .actions { display: flex; align-items: center; }
        .download { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 45px; padding: 11px 16px; border: 1px solid rgba(153, 255, 244, .35); border-radius: 12px; color: #062421; background: linear-gradient(135deg, #67e5d7, #32baab); box-shadow: 0 12px 25px rgba(31, 202, 181, .2); font-size: .88rem; font-weight: 800; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease; }
        .download:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(31, 202, 181, .3); }
        .autoplay-note { margin-top: 18px; color: #8193a8; font-size: .75rem; }
        @media (max-width: 720px) { .container { width: min(100% - 22px, 1160px); padding-top: 18px; } .player-card { border-radius: 18px; } .content { grid-template-columns: 1fr; gap: 18px; padding: 20px; } .actions { align-items: flex-start; } .download { width: 100%; } .video-badge { top: 12px; left: 12px; } }
    </style>
</head>
<body>
    <main class="container">
        <a class="back" href="{{ route('downloads.index') }}"><i class="fas fa-arrow-left"></i> Kembali ke Download Center</a>
        <section class="player-card">
            <div class="video-wrap">
                <span class="video-badge"><i class="fas fa-circle-play"></i> Sedang diputar</span>
                @if($download->source === 'gdrive')
                    <iframe src="{{ $videoUrl }}" title="{{ $download->title }}" allow="autoplay; fullscreen" allowfullscreen></iframe>
                @else
                    <video id="video-player" controls autoplay muted playsinline preload="metadata">
                        <source src="{{ $videoUrl }}" type="{{ $download->mime_type }}">
                        Browser tidak mendukung pemutaran video ini.
                    </video>
                @endif
            </div>
            <div class="content">
                <div>
                    <span class="eyebrow">SIMANSA Video Center</span>
                    <h1>{{ $download->title }}</h1>
                    <p>{{ $download->description ?: 'Video dari SIMANSA Download Center.' }}</p>
                    <div class="meta">
                        <span><i class="fas fa-folder"></i> {{ $download->category->name ?? 'Umum' }}</span>
                        <span><i class="fas fa-file-video"></i> {{ strtoupper($download->file_extension) }}</span>
                        <span><i class="fas fa-weight-hanging"></i> {{ $download->formatted_size }}</span>
                    </div>
                    <div class="autoplay-note"><i class="fas fa-volume-xmark"></i> Video diputar otomatis tanpa suara agar kompatibel dengan browser.</div>
                </div>
                <div class="actions">
                    <a class="download" href="{{ route('downloads.download', ['download' => $download, 'filename' => $download->download_route_filename]) }}"><i class="fas fa-download"></i> Unduh Video</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
