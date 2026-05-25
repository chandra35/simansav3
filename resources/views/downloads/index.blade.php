<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Center - SIMANSA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #0f766e;
            --primary-soft: #ccfbf1;
            --accent: #ea580c;
            --line: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at 12% 8%, #dbeafe 0, #dbeafe 12%, transparent 45%),
                radial-gradient(circle at 88% 0%, #ccfbf1 0, #ccfbf1 10%, transparent 40%),
                var(--bg);
            color: var(--text);
        }
        .container {
            width: min(1120px, 92vw);
            margin: 0 auto;
            padding: 28px 0 36px;
        }
        .hero {
            background: linear-gradient(130deg, #0f766e, #155e75);
            color: #fff;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(2, 6, 23, .14);
            position: relative;
            overflow: hidden;
        }
        .hero:after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            right: -70px;
            top: -90px;
        }
        .hero h1 { margin: 0 0 8px; font-size: clamp(1.5rem, 2.8vw, 2.2rem); }
        .hero p { margin: 0; opacity: .94; }
        .stats {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 16px;
        }
        .stat {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 14px;
            padding: 12px 14px;
        }
        .stat b { display: block; font-size: 1.1rem; }
        .search-wrap {
            margin-top: 18px;
            display: grid;
            gap: 10px;
            grid-template-columns: 1.4fr .8fr .7fr .5fr;
        }
        .search-wrap input, .search-wrap select, .search-wrap button {
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: 11px 12px;
            font: inherit;
        }
        .search-wrap button {
            border: none;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .chips {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .chip {
            text-decoration: none;
            border: 1px solid var(--line);
            color: var(--text);
            background: #fff;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .86rem;
            font-weight: 600;
        }
        .chip.active { background: var(--primary-soft); border-color: #99f6e4; color: #115e59; }
        .grid {
            margin-top: 22px;
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .top { display: flex; justify-content: space-between; gap: 10px; align-items: center; }
        .ext {
            background: #e2e8f0;
            color: #0f172a;
            border-radius: 8px;
            padding: 5px 8px;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .04em;
        }
        .title { font-size: 1rem; font-weight: 800; margin: 0; line-height: 1.4; }
        .desc { color: var(--muted); margin: 0; font-size: .9rem; min-height: 38px; }
        .meta { display: flex; gap: 8px; flex-wrap: wrap; color: var(--muted); font-size: .8rem; }
        .meta span {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 3px 9px;
        }
        .actions { margin-top: auto; display: flex; justify-content: space-between; align-items: center; }
        .btn {
            text-decoration: none;
            background: var(--primary);
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: .86rem;
        }
        .empty {
            margin-top: 20px;
            text-align: center;
            color: var(--muted);
            background: #fff;
            border: 1px dashed var(--line);
            border-radius: 14px;
            padding: 28px;
        }
        .pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }
        @media (max-width: 960px) {
            .search-wrap { grid-template-columns: 1fr 1fr; }
            .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .stats { grid-template-columns: 1fr; }
            .search-wrap { grid-template-columns: 1fr; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="hero">
            <h1><i class="fas fa-cloud-arrow-down"></i> SIMANSA Download Center</h1>
            <p>Pusat file aplikasi, dokumen, modul, dan berkas resmi untuk diunduh kapan saja.</p>
            <div class="stats">
                <div class="stat"><small>Total File</small><b>{{ number_format($stats['total_files'], 0, ',', '.') }}</b></div>
                <div class="stat"><small>Total Download</small><b>{{ number_format($stats['total_downloads'], 0, ',', '.') }}</b></div>
                <div class="stat"><small>Total Kategori</small><b>{{ number_format($stats['total_categories'], 0, ',', '.') }}</b></div>
            </div>
        </section>

        <form class="search-wrap" method="GET" action="{{ route('downloads.index') }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari file, dokumen, atau tipe file...">
            <select name="category">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="sort">
                <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Terpopuler</option>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Cari</button>
        </form>

        <div class="chips">
            <a class="chip {{ request('category') ? '' : 'active' }}" href="{{ route('downloads.index', array_merge(request()->except('page', 'category'), ['sort' => $sort])) }}">Semua</a>
            @foreach($categories as $category)
                <a class="chip {{ request('category') === $category->slug ? 'active' : '' }}" href="{{ route('downloads.index', array_merge(request()->except('page'), ['category' => $category->slug, 'sort' => $sort])) }}">
                    <i class="{{ $category->icon }}"></i> {{ $category->name }}
                </a>
            @endforeach
        </div>

        @if($downloads->count() > 0)
            <div class="grid">
                @foreach($downloads as $item)
                    <article class="card">
                        <div class="top">
                            <span class="ext">{{ strtoupper($item->file_extension ?: 'FILE') }}</span>
                            <small style="color:#64748b;"><i class="fas fa-download"></i> {{ number_format($item->download_count, 0, ',', '.') }}</small>
                        </div>
                        <h2 class="title">{{ $item->title }}</h2>
                        <p class="desc">{{ $item->description ?: 'File resmi dari SIMANSA Download Center.' }}</p>
                        <div class="meta">
                            <span><i class="fas fa-folder"></i> {{ $item->category->name ?? 'Umum' }}</span>
                            <span><i class="fas fa-weight-hanging"></i> {{ $item->formatted_size }}</span>
                        </div>
                        <div class="actions">
                            <small style="color:#64748b;"><i class="far fa-clock"></i> {{ optional($item->published_at)->format('d M Y') }}</small>
                            <a class="btn" href="{{ route('downloads.download', ['download' => $item, 'filename' => $item->download_route_filename]) }}"><i class="fas fa-arrow-down"></i> Download</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">{{ $downloads->links() }}</div>
        @else
            <div class="empty">
                <i class="fas fa-folder-open" style="font-size:2rem;"></i>
                <p>Belum ada file pada filter ini.</p>
            </div>
        @endif
    </div>
</body>
</html>
