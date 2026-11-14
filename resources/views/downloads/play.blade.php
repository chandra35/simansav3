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
        :root { --surface: #fff; --text: #0f172a; --muted: #64748b; --primary: #0f766e; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Manrope', sans-serif; background: #f5f7fb; color: var(--text); }
        .container { width: min(1040px, 92vw); margin: 0 auto; padding: 28px 0 36px; }
        .back { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 700; margin-bottom: 16px; }
        .player-card { overflow: hidden; background: var(--surface); border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 14px 36px rgba(15, 23, 42, .08); }
        .video-wrap { position: relative; background: #020617; aspect-ratio: 16 / 9; }
        video, iframe { width: 100%; height: 100%; border: 0; display: block; }
        .content { padding: 20px; }
        h1 { margin: 0 0 8px; font-size: clamp(1.25rem, 2.8vw, 1.85rem); }
        p { margin: 0; color: var(--muted); white-space: pre-line; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; color: var(--muted); font-size: .86rem; }
        .meta span { padding: 5px 10px; border: 1px solid #e2e8f0; border-radius: 999px; background: #f8fafc; }
        .actions { margin-top: 18px; }
        .download { display: inline-flex; align-items: center; gap: 8px; border-radius: 10px; padding: 10px 14px; color: #fff; background: var(--primary); text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <main class="container">
        <a class="back" href="{{ route('downloads.index') }}"><i class="fas fa-arrow-left"></i> Kembali ke Download Center</a>
        <section class="player-card">
            <div class="video-wrap">
                @if($download->source === 'gdrive')
                    <iframe src="{{ $videoUrl }}" title="{{ $download->title }}" allow="autoplay; fullscreen" allowfullscreen></iframe>
                @else
                    <video controls preload="metadata">
                        <source src="{{ $videoUrl }}" type="{{ $download->mime_type }}">
                        Browser tidak mendukung pemutaran video ini.
                    </video>
                @endif
            </div>
            <div class="content">
                <h1>{{ $download->title }}</h1>
                <p>{{ $download->description ?: 'Video dari SIMANSA Download Center.' }}</p>
                <div class="meta">
                    <span><i class="fas fa-folder"></i> {{ $download->category->name ?? 'Umum' }}</span>
                    <span><i class="fas fa-file-video"></i> {{ strtoupper($download->file_extension) }}</span>
                    <span><i class="fas fa-weight-hanging"></i> {{ $download->formatted_size }}</span>
                </div>
                <div class="actions">
                    <a class="download" href="{{ route('downloads.download', ['download' => $download, 'filename' => $download->download_route_filename]) }}"><i class="fas fa-download"></i> Unduh Video</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
