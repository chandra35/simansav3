@extends('layouts.app')

@section('title', ($settings->meta_title ?? $settings->site_name ?? 'PPDB') . ' - Portal Resmi Pendaftaran')

@section('meta')
    <meta name="description" content="{{ $settings->meta_description ?? 'Portal Resmi Pendaftaran Peserta Didik Baru' }}">
    <meta name="keywords" content="{{ $settings->meta_keywords ?? 'ppdb, pendaftaran, siswa baru' }}">
    @if($settings->logo)
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon) }}" type="image/x-icon">
    @endif
@endsection

@section('content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary-color: {{ $settings->primary_color ?? '#2563eb' }};
        --secondary-color: {{ $settings->secondary_color ?? '#6c757d' }};
        --accent-color: {{ $settings->accent_color ?? '#28a745' }};
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        background: #fff;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* ===== SLIDER ===== */
    .hero-slider {
        position: relative;
        width: 100%;
        height: 500px;
        overflow: hidden;
        background: #000;
        margin-bottom: 60px;
    }

    .slide {
        display: none;
        position: absolute;
        width: 100%;
        height: 100%;
        animation: fadeIn 1s;
    }

    .slide.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6));
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        padding: 20px;
    }

    .slide-content h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .slide-content p {
        font-size: 1.3rem;
        margin-bottom: 30px;
        opacity: 0.95;
    }

    .slider-controls {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: all 0.3s;
    }

    .slider-dot.active {
        background: white;
        width: 30px;
        border-radius: 6px;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.3);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        transition: background 0.3s;
        z-index: 10;
    }

    .slider-btn:hover {
        background: rgba(255,255,255,0.5);
    }

    .slider-btn.prev { left: 20px; }
    .slider-btn.next { right: 20px; }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-block;
        padding: 12px 30px;
        background: #2563eb;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 16px;
    }

    .btn:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-outline {
        background: white;
        color: #2563eb;
        border: 2px solid #2563eb;
    }

    .btn-outline:hover {
        background: #2563eb;
        color: white;
    }

    /* ===== INFO CARDS ===== */
    .info-section {
        padding: 60px 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .info-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
        border-top: 4px solid var(--primary-color);
    }

    .info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    .info-card .icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: var(--primary-color);
        font-size: 1.4rem;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .info-card p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 20px;
    }

    /* ===== BERITA SECTION ===== */
    .berita-section {
        background: #f8f9fa;
        padding: 60px 0;
    }

    .section-title {
        text-align: center;
        font-size: 2.2rem;
        color: #1e293b;
        margin-bottom: 50px;
        font-weight: 700;
    }

    .berita-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
    }

    .berita-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .berita-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .berita-img {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .berita-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .berita-img .placeholder {
        font-size: 4rem;
        color: white;
    }

    .berita-body {
        padding: 25px;
    }

    .berita-date {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .berita-title {
        color: #1e293b;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .berita-excerpt {
        color: #64748b;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .berita-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .berita-link:hover {
        text-decoration: underline;
    }

    .berita-kategori {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary-color);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .berita-img {
        position: relative;
    }

    /* ===== JADWAL SECTION ===== */
    .jadwal-section {
        padding: 60px 0;
        background: #f8fafc;
    }

    .jadwal-timeline {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
        padding-left: 50px;
    }

    .jadwal-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #e2e8f0;
        border-radius: 2px;
    }

    .jadwal-item {
        position: relative;
        padding: 20px 0;
        padding-left: 40px;
    }

    .jadwal-item.active .jadwal-content {
        border-left: 4px solid var(--accent-color);
    }

    .jadwal-item.past {
        opacity: 0.6;
    }

    .jadwal-icon {
        position: absolute;
        left: -30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        z-index: 1;
    }

    .jadwal-content {
        background: white;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border-left: 4px solid transparent;
        transition: all 0.3s;
    }

    .jadwal-content:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .jadwal-content h4 {
        color: #1e293b;
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .jadwal-date {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 8px;
    }

    .jadwal-desc {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .jadwal-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    /* ===== AUTH SECTION ===== */
    .auth-section {
        padding: 60px 0;
    }

    .auth-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        max-width: 900px;
        margin: 0 auto;
    }

    .auth-card {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .auth-card h3 {
        color: #2563eb;
        font-size: 1.6rem;
        margin-bottom: 25px;
        text-align: center;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #334155;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .auth-card .btn {
        width: 100%;
        padding: 14px;
        font-size: 1.05rem;
    }

    .auth-text {
        text-align: center;
        color: #64748b;
        margin-bottom: 25px;
        line-height: 1.6;
    }

    /* ===== FOOTER ===== */
    footer {
        background: #1e293b;
        color: #cbd5e1;
        padding: 50px 0 30px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-col h4 {
        color: white;
        font-size: 1.2rem;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .footer-col p,
    .footer-col a {
        color: #cbd5e1;
        line-height: 2;
        text-decoration: none;
        display: block;
        font-size: 0.95rem;
    }

    .footer-col a:hover {
        color: #2563eb;
    }

    .footer-bottom {
        border-top: 1px solid #334155;
        padding-top: 30px;
        text-align: center;
        color: #94a3b8;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .hero-slider {
            height: 350px;
        }

        .slide-content h2 {
            font-size: 2rem;
        }

        .slide-content p {
            font-size: 1rem;
        }

        .auth-grid {
            grid-template-columns: 1fr;
        }

        .slider-btn {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .info-card,
        .auth-card {
            padding: 25px;
        }

        .section-title {
            font-size: 1.8rem;
        }
    }
</style>

<!-- Hero Slider -->
<div class="hero-slider">
    @if($sliders->count() > 0)
        @foreach($sliders as $index => $slider)
            <div class="slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                @if($slider->gambar && file_exists(public_path('storage/' . $slider->gambar)))
                    <img src="{{ asset('storage/' . $slider->gambar) }}" alt="{{ $slider->judul }}">
                @else
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2563eb, #1d4ed8);"></div>
                @endif
                <div class="slide-overlay">
                    <div class="slide-content">
                        <h2>{{ $slider->judul }}</h2>
                        @if($slider->deskripsi)
                            <p>{{ $slider->deskripsi }}</p>
                        @endif
                        @if($slider->link)
                            <a href="{{ $slider->link }}" class="btn">Selengkapnya</a>
                        @else
                            <a href="{{ route('ppdb.register.step1') }}" class="btn">Daftar Sekarang</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        <button class="slider-btn prev" onclick="moveSlide(-1)">‹</button>
        <button class="slider-btn next" onclick="moveSlide(1)">›</button>
        
        <div class="slider-controls">
            @foreach($sliders as $index => $slider)
                <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})"></div>
            @endforeach
        </div>
    @else
        <div class="slide active">
            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2563eb, #1d4ed8);"></div>
            <div class="slide-overlay">
                <div class="slide-content">
                    <h2>Portal Pendaftaran PPDB</h2>
                    <p>Penerimaan Peserta Didik Baru Tahun {{ now()->year }}/{{ now()->year + 1 }}</p>
                    <a href="{{ route('ppdb.register.step1') }}" class="btn">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Info Section -->
<section class="info-section">
    <div class="container">
        <div class="info-grid">
            <div class="info-card">
                <div class="icon">📝</div>
                <h3>Pendaftaran Baru</h3>
                <p>Belum memiliki akun? Daftar sekarang untuk memulai proses pendaftaran PPDB dengan mudah dan cepat.</p>
                <a href="{{ route('ppdb.register.step1') }}" class="btn">Daftar Sekarang</a>
            </div>

            <div class="info-card">
                <div class="icon">📅</div>
                <h3>Jadwal Penting</h3>
                <p>Pembukaan: 01 Jan 2025<br>Penutupan: 15 Jan 2025<br>Pengumuman: 01 Feb 2025</p>
                <a href="#berita" class="btn btn-outline">Lihat Detail</a>
            </div>

            <div class="info-card">
                <div class="icon">✅</div>
                <h3>Info & Persyaratan</h3>
                <p>Kuota: 200 Siswa<br>Verifikasi: 2-3 Hari<br>Gratis & Aman</p>
                <a href="#berita" class="btn btn-outline">Info Lengkap</a>
            </div>
        </div>
    </div>
</section>

<!-- Berita Section -->
<section class="berita-section" id="berita">
    <div class="container">
        <h2 class="section-title">📰 Berita & Informasi Terbaru</h2>
        
        @if($beritas->count() > 0)
            <div class="berita-grid">
                @foreach($beritas->take(6) as $berita)
                    <div class="berita-card">
                        <div class="berita-img">
                            @if($berita->gambar && file_exists(public_path('storage/' . $berita->gambar)))
                                <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}">
                            @else
                                <div class="placeholder">📰</div>
                            @endif
                            @if($berita->kategori)
                                <span class="berita-kategori">{{ $berita->kategori }}</span>
                            @endif
                        </div>
                        <div class="berita-body">
                            <div class="berita-date">{{ $berita->created_at->format('d M Y') }}</div>
                            <h4 class="berita-title">{{ $berita->judul }}</h4>
                            <p class="berita-excerpt">{{ Str::limit($berita->excerpt ?? strip_tags($berita->konten), 120) }}</p>
                            <a href="{{ route('ppdb.berita.detail', $berita->slug) }}" class="berita-link">Baca Selengkapnya →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <p>Belum ada berita terbaru.</p>
            </div>
        @endif
    </div>
</section>

<!-- Jadwal Section -->
@if($jadwals->count() > 0)
<section class="jadwal-section" id="jadwal">
    <div class="container">
        <h2 class="section-title">📅 Jadwal PPDB</h2>
        <div class="jadwal-timeline">
            @foreach($jadwals as $jadwal)
                <div class="jadwal-item {{ $jadwal->isOngoing() ? 'active' : ($jadwal->isPast() ? 'past' : '') }}">
                    <div class="jadwal-icon" style="background-color: {{ $jadwal->warna }};">
                        <i class="{{ $jadwal->icon }}"></i>
                    </div>
                    <div class="jadwal-content">
                        <span class="jadwal-badge badge-{{ $jadwal->status_color }}">{{ $jadwal->status_label }}</span>
                        <h4>{{ $jadwal->nama_kegiatan }}</h4>
                        <p class="jadwal-date">{{ $jadwal->date_range }}</p>
                        @if($jadwal->deskripsi)
                            <p class="jadwal-desc">{{ $jadwal->deskripsi }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Auth Section -->
<section class="auth-section">
    <div class="container">
        <div class="auth-grid">
            <!-- Login -->
            <div class="auth-card">
                <h3>🔐 Login</h3>
                <form method="POST" action="{{ route('ppdb.login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="Masukkan email Anda">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
                    </div>
                    <button type="submit" class="btn">Login Sekarang</button>
                </form>
            </div>

            <!-- Register -->
            <div class="auth-card">
                <h3>📝 Daftar Baru</h3>
                <p class="auth-text">Belum memiliki akun? Daftar sekarang untuk memulai proses pendaftaran PPDB. Gratis dan mudah!</p>
                <a href="{{ route('ppdb.register.step1') }}" class="btn">Mulai Pendaftaran</a>
                
                <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e2e8f0;">
                    <p style="color: #64748b; font-size: 0.9rem; text-align: center; margin-bottom: 15px;"><strong>Yang Anda Butuhkan:</strong></p>
                    <ul style="color: #64748b; font-size: 0.9rem; line-height: 2; list-style-position: inside;">
                        <li>✓ NISN yang valid</li>
                        <li>✓ Email aktif</li>
                        <li>✓ Dokumen pendukung</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Portal PPDB Online</h4>
                <p>Sistem Penerimaan Peserta Didik Baru yang modern, aman, dan terpercaya. Terintegrasi dengan database Kemendikbud untuk validasi NISN otomatis.</p>
            </div>
            <div class="footer-col">
                <h4>Kontak</h4>
                <p>📞 (021) 123-4567</p>
                <p>📧 ppdb@sekolah.sch.id</p>
                <p>📍 Jl. Pendidikan No. 123<br>   Kota, Indonesia</p>
            </div>
            <div class="footer-col">
                <h4>Jam Operasional</h4>
                <p>Senin - Kamis<br>08:00 - 15:00 WIB</p>
                <p>Jumat<br>08:00 - 16:30 WIB</p>
                <p>Sabtu - Minggu: Libur</p>
            </div>
            <div class="footer-col">
                <h4>Link Penting</h4>
                <a href="{{ route('ppdb.register.step1') }}">Pendaftaran</a>
                <a href="#berita">Berita</a>
                <a href="#berita">Informasi</a>
                <a href="{{ route('ppdb.landing') }}">Beranda</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ now()->year }} Portal PPDB Online. Terintegrasi dengan Kemendikbud. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
let currentIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');
const totalSlides = slides.length;

function showSlide(index) {
    if (index >= totalSlides) currentIndex = 0;
    if (index < 0) currentIndex = totalSlides - 1;
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (slides[currentIndex]) slides[currentIndex].classList.add('active');
    if (dots[currentIndex]) dots[currentIndex].classList.add('active');
}

function moveSlide(direction) {
    currentIndex += direction;
    showSlide(currentIndex);
}

function goToSlide(index) {
    currentIndex = index;
    showSlide(currentIndex);
}

// Auto slide
if (totalSlides > 1) {
    setInterval(() => {
        currentIndex++;
        showSlide(currentIndex);
    }, 5000);
}
</script>

@endsection
