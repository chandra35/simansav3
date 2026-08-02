@extends('adminlte::page')

@section('title', 'Dashboard GTK')

@section('content_header')
    <div class="simansa-hero gtk-account-dashboard__hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-user-tie"></i> Ruang Kerja GTK</div>
            <h1 class="simansa-hero__title">Selamat datang, {{ $gtk->nama_lengkap }}</h1>
            <p class="simansa-hero__subtitle">Kelola profil, keamanan akun, dan informasi perwalian Anda dari satu halaman.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Status Profil</span>
                <span class="simansa-hero-chip__value {{ $needsCompletion ? 'text-warning' : 'text-success' }}">
                    {{ $needsCompletion ? 'Perlu dilengkapi' : 'Lengkap' }}
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-account-dashboard">
    @if($needsCompletion)
        <div class="alert alert-warning alert-dismissible fade show gtk-account-dashboard__alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Profil belum lengkap</h5>
            <p class="mb-2">Lengkapi data berikut agar seluruh fitur akun dapat digunakan:</p>
            <ul class="mb-3 pl-4">
                @if(!$stats['data_diri_completed'])
                    <li>Data diri, tempat/tanggal lahir, dan alamat lengkap</li>
                @endif
                @if(!$stats['data_kepeg_completed'])
                    <li>Status kepegawaian dan jabatan</li>
                @endif
            </ul>
            <a href="{{ route('admin.gtk.profile') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit mr-1"></i> Lengkapi Profil
            </a>
        </div>
    @endif

    <div class="card simansa-management-card gtk-account-dashboard__profile">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-id-card-alt mr-1"></i> Ringkasan Akun</h3>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-12 col-lg-auto text-center mb-3 mb-lg-0">
                    <img class="gtk-account-dashboard__avatar" src="{{ $gtk->foto_profile_url }}" alt="Foto profil {{ $gtk->nama_lengkap }}">
                </div>
                <div class="col-12 col-lg">
                    <h2 class="gtk-account-dashboard__name">{{ $gtk->nama_lengkap }}</h2>
                    <p class="text-muted mb-3">{{ $gtk->jabatan ?: 'Jabatan belum diisi' }}{{ $gtk->status_kepegawaian ? ' · '.$gtk->status_kepegawaian : '' }}</p>
                    <div class="gtk-account-dashboard__details">
                        <div class="gtk-account-dashboard__detail"><span>NIK</span><strong>{{ $gtk->nik ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>NUPTK</span><strong>{{ $gtk->nuptk ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>NIP</span><strong>{{ $gtk->nip ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Status</span><strong>{{ $gtk->status_kepegawaian ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Jabatan</span><strong>{{ $gtk->jabatan ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Jenis PTK</span><strong>{{ $gtk->jenis_ptk ?: ($gtk->kategori_ptk ?: '-') }}</strong></div>
                    </div>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <div class="gtk-account-dashboard__actions">
                        <a href="{{ route('admin.gtk.profile') }}" class="btn btn-primary">
                            <i class="fas fa-user-edit mr-1"></i> Edit Profil
                        </a>
                        <a href="{{ route('admin.gtk.profile.password') }}" class="btn btn-secondary">
                            <i class="fas fa-key mr-1"></i> Ganti Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isWaliKelas)
        <div class="card simansa-management-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-1"></i> Rombel Perwalian Saya</h3>
                <span class="badge badge-light mt-2 mt-sm-0">{{ $tahunAktif?->nama ?? 'Tahun aktif belum tersedia' }}</span>
            </div>
            <div class="card-body">
                @forelse($waliKelasRombels as $rombel)
                    @php
                        $waliNama = $rombel->waliKelas?->gtk?->nama_lengkap ?? $rombel->waliKelas?->name;
                        $ketua = $rombel->ketuaKelasRecord?->siswa;
                    @endphp
                    <div class="gtk-account-dashboard__rombel {{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="font-weight-bold mb-1"><i class="fas fa-school text-primary mr-1"></i>{{ $rombel->nama_lengkap }}</h5>
                                <span class="text-muted small">{{ $rombel->siswa_aktif_count }} siswa aktif</span>
                            </div>
                            <span class="badge badge-success px-3 py-2 mt-2 mt-sm-0">Rombel Aktif</span>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                <div class="gtk-account-dashboard__rombel-meta">
                                    <span>Wali Kelas</span>
                                    <strong><i class="fas fa-user-tie text-primary mr-1"></i>{{ $waliNama ?? 'Belum ditugaskan' }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="gtk-account-dashboard__rombel-meta">
                                    <span>Ketua Kelas</span>
                                    <strong class="{{ $ketua ? '' : 'text-muted' }}"><i class="fas fa-crown text-warning mr-1"></i>{{ $ketua?->nama_lengkap ?? 'Belum ditetapkan' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0"><i class="fas fa-info-circle mr-1"></i> Akun Anda memiliki peran Wali Kelas, tetapi belum ditugaskan ke rombel aktif.</div>
                @endforelse
            </div>
        </div>
    @endif

    <div class="card simansa-management-card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-calendar-day mr-1"></i> Jadwal Mengajar Hari Ini</h3>
            <span class="badge badge-light mt-2 mt-sm-0">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</span>
        </div>
        <div class="card-body">
            <div class="callout callout-info mb-0">
                <h5><i class="fas fa-info-circle mr-1"></i> Informasi</h5>
                <p class="mb-0">Fitur jadwal mengajar akan segera tersedia. Hubungi admin untuk informasi jadwal.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .gtk-account-dashboard { color:#0f172a; }
    .gtk-account-dashboard__profile { border-top:3px solid #2563eb !important; }
    .gtk-account-dashboard__avatar { width:96px; height:96px; object-fit:cover; border-radius:50%; border:3px solid #e2e8f0; box-shadow:0 6px 16px rgba(15,23,42,.12); }
    .gtk-account-dashboard__name { margin:0 0 .2rem; color:#0f172a; font-size:1.25rem; font-weight:800; }
    .gtk-account-dashboard__details { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.65rem; }
    .gtk-account-dashboard__detail, .gtk-account-dashboard__rombel-meta { min-width:0; padding:.7rem .8rem; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
    .gtk-account-dashboard__detail span, .gtk-account-dashboard__rombel-meta span { display:block; margin-bottom:.18rem; color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
    .gtk-account-dashboard__detail strong, .gtk-account-dashboard__rombel-meta strong { display:block; overflow-wrap:anywhere; color:#0f172a; font-size:.88rem; }
    .gtk-account-dashboard__actions { display:flex; flex-direction:column; gap:.6rem; min-width:172px; }
    .gtk-account-dashboard__rombel { padding:1rem; border:1px solid #e2e8f0; border-radius:12px; }
    .gtk-account-dashboard__alert { border-radius:12px; }
    @media (max-width:991.98px) {
        .gtk-account-dashboard__details { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .gtk-account-dashboard__actions { flex-direction:row; min-width:0; }
    }
    @media (max-width:575.98px) {
        .gtk-account-dashboard__details { grid-template-columns:1fr; }
        .gtk-account-dashboard__actions { flex-direction:column; }
        .gtk-account-dashboard__actions .btn { width:100%; }
        .gtk-account-dashboard .simansa-management-card > .card-header { align-items:flex-start !important; }
    }
</style>
@stop
