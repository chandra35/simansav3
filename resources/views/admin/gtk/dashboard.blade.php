@extends('adminlte::page')

@section('title', 'Dashboard GTK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-home text-primary"></i> Dashboard GTK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard Saya</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-account-dashboard">
    <div class="card bg-gradient-primary text-white mb-4 gtk-account-dashboard__hero">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-user-tie mr-1"></i> Selamat Datang, {{ $gtk->nama_lengkap }}</h3>
                    <p class="mb-2 text-white-50">Ruang kerja pribadi untuk mengelola identitas dan keamanan akun GTK.</p>
                    <p class="mb-0">Pantau informasi perwalian dan akses layanan utama dari satu halaman.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-center">
                    <div class="text-white-50 small text-uppercase font-weight-bold">Status Profil</div>
                    <h3 class="mb-0 text-white">{{ $needsCompletion ? 'Perlu Dilengkapi' : 'Lengkap' }}</h3>
                </div>
            </div>
        </div>
    </div>

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

    @if($teacherNotices->isNotEmpty())
        <div class="card card-outline card-warning gtk-account-dashboard__notices">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-bell text-warning mr-1"></i> Notice Pendampingan Siswa</h3><div class="card-tools"><span class="badge badge-warning">{{ $teacherNotices->count() }} notice</span></div></div>
            <div class="card-body"><div class="alert alert-light border py-2"><small><i class="fas fa-shield-alt text-primary mr-1"></i>Informasi internal untuk mendukung pembelajaran. Isi asesmen dan catatan rahasia BK tidak ditampilkan.</small></div><div class="row">@foreach($teacherNotices as $notice)<div class="col-lg-6 mb-3"><div class="gtk-account-dashboard__notice"><div class="d-flex"><img src="{{ $notice->siswa?->foto_profile_url }}" alt="Foto {{ $notice->siswa?->nama_lengkap }}"><div class="ml-2"><strong>{{ $notice->siswa?->nama_lengkap ?? '-' }}</strong><small>{{ $notice->siswa?->kelasTahunAktif->first()?->nama_kelas ?? '-' }} · {{ $notice->tanggal_konseling?->format('d/m/Y') }}</small></div></div><p>{{ $notice->teacher_notice }}</p></div></div>@endforeach</div></div>
        </div>
    @endif

    <div class="card card-outline card-primary gtk-account-dashboard__profile">
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
        <div class="card card-outline card-primary">
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

    <div class="card card-outline card-primary mb-3">
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
    .gtk-account-dashboard__hero { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-account-dashboard__hero > .card-body { padding:1.2rem 1.25rem; }
    .gtk-account-dashboard__hero h3 { font-size:1.35rem; font-weight:700; }
    .gtk-account-dashboard .card-outline { border-radius:12px; box-shadow:0 8px 20px rgba(15,23,42,.06); }
    .gtk-account-dashboard__avatar { width:96px; height:96px; object-fit:cover; border-radius:50%; border:3px solid #e2e8f0; box-shadow:0 6px 16px rgba(15,23,42,.12); }
    .gtk-account-dashboard__name { margin:0 0 .2rem; color:#0f172a; font-size:1.25rem; font-weight:800; }
    .gtk-account-dashboard__details { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.65rem; }
    .gtk-account-dashboard__detail, .gtk-account-dashboard__rombel-meta { min-width:0; padding:.7rem .8rem; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
    .gtk-account-dashboard__detail span, .gtk-account-dashboard__rombel-meta span { display:block; margin-bottom:.18rem; color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
    .gtk-account-dashboard__detail strong, .gtk-account-dashboard__rombel-meta strong { display:block; overflow-wrap:anywhere; color:#0f172a; font-size:.88rem; }
    .gtk-account-dashboard__actions { display:flex; flex-direction:column; gap:.6rem; min-width:172px; }
    .gtk-account-dashboard__rombel { padding:1rem; border:1px solid #e2e8f0; border-radius:12px; }
    .gtk-account-dashboard__notice { height:100%; padding:.75rem; border:1px solid #fde68a; border-radius:10px; background:#fffbeb; }
    .gtk-account-dashboard__notice img { width:42px; height:52px; object-fit:cover; border-radius:6px; }
    .gtk-account-dashboard__notice strong, .gtk-account-dashboard__notice small { display:block; }
    .gtk-account-dashboard__notice small { color:#64748b; font-size:.72rem; }
    .gtk-account-dashboard__notice p { margin:.6rem 0 0; color:#334155; font-size:.82rem; white-space:pre-line; }
    .gtk-account-dashboard__alert { border-radius:12px; }
    @media (max-width:991.98px) {
        .gtk-account-dashboard__details { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .gtk-account-dashboard__actions { flex-direction:row; min-width:0; }
    }
    @media (max-width:575.98px) {
        .gtk-account-dashboard__details { grid-template-columns:1fr; }
        .gtk-account-dashboard__actions { flex-direction:column; }
        .gtk-account-dashboard__actions .btn { width:100%; }
        .gtk-account-dashboard .card-outline > .card-header { align-items:flex-start !important; }
        .gtk-account-dashboard__hero > .card-body { padding:1rem; }
        .gtk-account-dashboard__hero h3 { font-size:1.15rem; }
    }
</style>
@stop
