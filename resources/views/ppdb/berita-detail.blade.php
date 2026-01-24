@extends('layouts.app')

@section('title', $berita->judul . ' - ' . ($settings->site_name ?? 'PPDB'))

@section('meta')
    <meta name="description" content="{{ $berita->excerpt ?? Str::limit(strip_tags($berita->konten), 160) }}">
    <meta name="keywords" content="{{ $berita->kategori }}, {{ $settings->meta_keywords ?? 'berita ppdb' }}">
    <meta name="author" content="{{ $berita->penulis ?? 'Admin' }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $berita->judul }}">
    <meta property="og:description" content="{{ $berita->excerpt ?? Str::limit(strip_tags($berita->konten), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($berita->gambar)
        <meta property="og:image" content="{{ asset('storage/' . $berita->gambar) }}">
    @endif
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $berita->judul }}">
    <meta name="twitter:description" content="{{ $berita->excerpt ?? Str::limit(strip_tags($berita->konten), 160) }}">
@endsection

@section('content')
<style>
    :root {
        --primary-color: {{ $settings->primary_color ?? '#2563eb' }};
    }

    .berita-detail-page {
        padding: 40px 0 80px;
        background: #f8fafc;
        min-height: 100vh;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .breadcrumb {
        padding: 15px 0;
        margin-bottom: 30px;
    }

    .breadcrumb a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb span {
        color: #64748b;
    }

    .berita-header {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .berita-image {
        width: 100%;
        height: 400px;
        background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
        overflow: hidden;
    }

    .berita-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .berita-meta {
        padding: 30px;
    }

    .berita-kategori {
        display: inline-block;
        padding: 6px 16px;
        background: var(--primary-color);
        color: white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .berita-title {
        font-size: 2rem;
        color: #1e293b;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 20px;
    }

    .berita-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        color: #64748b;
        font-size: 0.95rem;
    }

    .berita-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .berita-info-item i {
        color: var(--primary-color);
    }

    .berita-content {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .berita-content h1, .berita-content h2, .berita-content h3, 
    .berita-content h4, .berita-content h5, .berita-content h6 {
        color: #1e293b;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .berita-content p {
        color: #374151;
        line-height: 1.8;
        font-size: 1.05rem;
        margin-bottom: 20px;
    }

    .berita-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 20px 0;
    }

    .berita-content ul, .berita-content ol {
        margin: 20px 0;
        padding-left: 30px;
    }

    .berita-content li {
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .berita-content blockquote {
        border-left: 4px solid var(--primary-color);
        padding-left: 20px;
        margin: 25px 0;
        font-style: italic;
        color: #64748b;
    }

    .share-section {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .share-section h4 {
        color: #1e293b;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .share-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .share-btn.facebook {
        background: #1877f2;
        color: white;
    }

    .share-btn.twitter {
        background: #1da1f2;
        color: white;
    }

    .share-btn.whatsapp {
        background: #25d366;
        color: white;
    }

    .share-btn.copy {
        background: #64748b;
        color: white;
    }

    .related-section {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .related-section h3 {
        color: #1e293b;
        margin-bottom: 25px;
        font-size: 1.3rem;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .related-card {
        display: flex;
        gap: 15px;
        padding: 15px;
        border-radius: 10px;
        background: #f8fafc;
        transition: transform 0.2s;
    }

    .related-card:hover {
        transform: translateX(5px);
        background: #f1f5f9;
    }

    .related-card-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--primary-color);
    }

    .related-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-card-content h4 {
        font-size: 0.95rem;
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .related-card-content h4 a {
        color: inherit;
        text-decoration: none;
    }

    .related-card-content h4 a:hover {
        color: var(--primary-color);
    }

    .related-card-content span {
        font-size: 0.85rem;
        color: #64748b;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        margin-top: 20px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .berita-title {
            font-size: 1.5rem;
        }
        
        .berita-image {
            height: 250px;
        }
        
        .berita-content {
            padding: 25px;
        }
        
        .berita-info {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<div class="berita-detail-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ route('ppdb.landing') }}">Beranda</a>
            <span> / </span>
            <a href="{{ route('ppdb.landing') }}#berita">Berita</a>
            <span> / </span>
            <span>{{ Str::limit($berita->judul, 50) }}</span>
        </nav>

        <!-- Header -->
        <article class="berita-header">
            <div class="berita-image">
                @if($berita->gambar && file_exists(public_path('storage/' . $berita->gambar)))
                    <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}">
                @endif
            </div>
            <div class="berita-meta">
                @if($berita->kategori)
                    <span class="berita-kategori">{{ $berita->kategori }}</span>
                @endif
                <h1 class="berita-title">{{ $berita->judul }}</h1>
                <div class="berita-info">
                    <div class="berita-info-item">
                        <i class="fas fa-calendar"></i>
                        {{ $berita->created_at->format('d F Y') }}
                    </div>
                    @if($berita->penulis)
                        <div class="berita-info-item">
                            <i class="fas fa-user"></i>
                            {{ $berita->penulis }}
                        </div>
                    @endif
                    <div class="berita-info-item">
                        <i class="fas fa-eye"></i>
                        {{ number_format($berita->views) }} views
                    </div>
                    @if($berita->shared_to_facebook)
                        <div class="berita-info-item">
                            <i class="fab fa-facebook"></i>
                            Shared on Facebook
                        </div>
                    @endif
                </div>
            </div>
        </article>

        <!-- Content -->
        <div class="berita-content">
            {!! $berita->konten !!}
        </div>

        <!-- Share Section -->
        <div class="share-section">
            <h4>Bagikan Berita Ini:</h4>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                   target="_blank" class="share-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($berita->judul) }}" 
                   target="_blank" class="share-btn twitter">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://wa.me/?text={{ urlencode($berita->judul . ' - ' . url()->current()) }}" 
                   target="_blank" class="share-btn whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <button class="share-btn copy" onclick="copyToClipboard('{{ url()->current() }}')">
                    <i class="fas fa-link"></i> Salin Link
                </button>
            </div>
        </div>

        <!-- Related Articles -->
        @if($relatedBeritas->count() > 0)
            <div class="related-section">
                <h3>Berita Terkait</h3>
                <div class="related-grid">
                    @foreach($relatedBeritas as $related)
                        <div class="related-card">
                            <div class="related-card-img">
                                @if($related->gambar && file_exists(public_path('storage/' . $related->gambar)))
                                    <img src="{{ asset('storage/' . $related->gambar) }}" alt="{{ $related->judul }}">
                                @endif
                            </div>
                            <div class="related-card-content">
                                <h4><a href="{{ route('ppdb.berita.detail', $related->slug) }}">{{ Str::limit($related->judul, 60) }}</a></h4>
                                <span>{{ $related->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('ppdb.landing') }}#berita" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Berita
        </a>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link berhasil disalin!');
    }, function() {
        prompt('Salin link ini:', text);
    });
}
</script>
@endsection
