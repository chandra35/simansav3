@extends('adminlte::page')

@section('title', 'Dashboard GTK')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow">
                <i class="fas fa-home"></i>
                Portal GTK
            </div>
            <h1 class="simansa-hero__title">Selamat datang, {{ $gtk->nama_lengkap }}</h1>
            <p class="simansa-hero__subtitle">
                {{ $gtk->jabatan ?? 'Guru & Tenaga Kependidikan' }}{{ $gtk->status_kepegawaian ? ' · ' . $gtk->status_kepegawaian : '' }}
                — Tahun Pelajaran {{ $tahunAktif?->nama ?? 'belum ditetapkan' }}
            </p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Kelengkapan Profil</span>
                <span class="simansa-hero-chip__value">{{ $stats['completion_percentage'] }}%</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Rombel Perwalian</span>
                <span class="simansa-hero-chip__value">{{ $isWaliKelas ? $waliKelasRombels->count() : '—' }}</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- Peringatan kelengkapan profil --}}
    @if($needsCompletion)
        <div class="card mb-3" style="border-left: 4px solid #d97706;">
            <div class="card-body py-3 d-flex flex-wrap align-items-center" style="gap: .75rem;">
                <div class="flex-grow-1">
                    <div class="font-weight-bold mb-1">
                        <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Profil Anda belum lengkap
                    </div>
                    <div class="text-muted small">
                        Lengkapi
                        @if(!$stats['data_diri_completed'])
                            <strong>Data Diri</strong> (NIK, tempat/tanggal lahir, alamat)@if(!$stats['data_kepeg_completed']) dan @endif
                        @endif
                        @if(!$stats['data_kepeg_completed'])
                            <strong>Data Kepegawaian</strong> (status kepegawaian, jabatan)
                        @endif
                        untuk mengakses semua fitur sistem.
                    </div>
                </div>
                <a href="{{ route('admin.gtk.profile') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1"></i> Lengkapi Sekarang
                </a>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Ringkasan kepegawaian --}}
        <div class="col-lg-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3" style="gap: 1rem;">
                        @if($gtk->foto_profile_url)
                            <img src="{{ $gtk->foto_profile_url }}" alt="Foto {{ $gtk->nama_lengkap }}" class="gtk-dash-avatar">
                        @else
                            <div class="gtk-dash-avatar gtk-dash-avatar--initial">{{ strtoupper(mb_substr($gtk->nama_lengkap, 0, 1)) }}</div>
                        @endif
                        <div>
                            <div class="h5 font-weight-bold mb-1">{{ $gtk->nama_lengkap }}</div>
                            @if($gtk->kategori_ptk || $gtk->jenis_ptk)
                                @if($gtk->kategori_ptk)<span class="badge badge-info">{{ $gtk->kategori_ptk }}</span>@endif
                                @if($gtk->jenis_ptk)<span class="badge badge-success">{{ $gtk->jenis_ptk }}</span>@endif
                            @else
                                <span class="text-muted small">Kategori PTK belum diisi</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-4 mb-3">
                            <div class="gtk-dash-label">NIK</div>
                            <div class="gtk-dash-value">{{ $gtk->nik ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4 mb-3">
                            <div class="gtk-dash-label">NUPTK</div>
                            <div class="gtk-dash-value">{{ $gtk->nuptk ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4 mb-3">
                            <div class="gtk-dash-label">NIP</div>
                            <div class="gtk-dash-value">{{ $gtk->nip ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4 mb-3 mb-md-0">
                            <div class="gtk-dash-label">Status Kepegawaian</div>
                            <div class="gtk-dash-value">{{ $gtk->status_kepegawaian ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-4 mb-0">
                            <div class="gtk-dash-label">Jabatan</div>
                            <div class="gtk-dash-value">{{ $gtk->jabatan ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aksi cepat --}}
        <div class="col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-body p-2">
                    <div class="gtk-dash-label px-3 pt-2 pb-1">Aksi Cepat</div>
                    <a href="{{ route('admin.gtk.profile') }}" class="gtk-qa">
                        <span class="gtk-qa__icon"><i class="fas fa-user-edit"></i></span>
                        <span class="gtk-qa__body">
                            <span class="gtk-qa__title">Perbarui Profil</span>
                            <span class="gtk-qa__desc">Data diri &amp; kepegawaian</span>
                        </span>
                        <i class="fas fa-chevron-right gtk-qa__arrow"></i>
                    </a>
                    <a href="{{ route('admin.gtk.profile.password') }}" class="gtk-qa">
                        <span class="gtk-qa__icon"><i class="fas fa-key"></i></span>
                        <span class="gtk-qa__body">
                            <span class="gtk-qa__title">Ganti Password</span>
                            <span class="gtk-qa__desc">Keamanan akun Anda</span>
                        </span>
                        <i class="fas fa-chevron-right gtk-qa__arrow"></i>
                    </a>
                    <a href="{{ route('admin.gtk.osis-election.index') }}" class="gtk-qa">
                        <span class="gtk-qa__icon"><i class="fas fa-vote-yea"></i></span>
                        <span class="gtk-qa__body">
                            <span class="gtk-qa__title">Pemilihan OSIS</span>
                            <span class="gtk-qa__desc">Berikan suara Anda</span>
                        </span>
                        <i class="fas fa-chevron-right gtk-qa__arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Rombel perwalian --}}
    @if($isWaliKelas)
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Rombel Perwalian Saya
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary">{{ $tahunAktif?->nama ?? 'Tahun aktif belum tersedia' }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                @forelse($waliKelasRombels as $rombel)
                    @php
                        $waliNama = $rombel->waliKelas?->gtk?->nama_lengkap ?? $rombel->waliKelas?->name;
                        $ketua = $rombel->ketuaKelasRecord?->siswa;
                    @endphp
                    <div class="{{ !$loop->last ? 'pb-3 mb-3 border-bottom' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap: .5rem;">
                            <div>
                                <div class="h5 font-weight-bold mb-1">
                                    {{ $rombel->nama_lengkap }} {!! $rombel->asrama_badge !!}
                                </div>
                                <span class="text-muted small">
                                    <i class="fas fa-user-graduate mr-1"></i>{{ $rombel->siswa_aktif_count }} siswa aktif
                                </span>
                            </div>
                            <span class="badge badge-success">Rombel Aktif</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="gtk-dash-label mb-1">Wali Kelas</div>
                                    <div class="font-weight-bold">
                                        <i class="fas fa-user-tie text-primary mr-1"></i>
                                        {{ $waliNama ?? 'Belum ditugaskan' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="gtk-dash-label mb-1">Ketua Kelas</div>
                                    <div class="font-weight-bold {{ $ketua ? '' : 'text-muted' }}">
                                        <i class="fas fa-crown text-warning mr-1"></i>
                                        {{ $ketua?->nama_lengkap ?? 'Belum ditetapkan' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Akun Anda memiliki peran Wali Kelas, tetapi belum ditugaskan ke rombel aktif.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
@endsection

@section('css')
    <style>
        .gtk-dash-avatar {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            border-radius: 16px;
            object-fit: cover;
        }
        .gtk-dash-avatar--initial {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4338ca;
            font-size: 1.5rem;
            font-weight: 800;
        }
        .gtk-dash-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #64748b;
        }
        .gtk-dash-value {
            font-weight: 600;
            color: #0f172a;
        }
        .gtk-qa {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .8rem 1rem;
            border-radius: 12px;
            color: #0f172a;
        }
        .gtk-qa:hover {
            background: #eef2ff;
            color: #0f172a;
            text-decoration: none;
        }
        .gtk-qa__icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4f46e5;
        }
        .gtk-qa__body {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .gtk-qa__title { font-weight: 700; font-size: .9rem; }
        .gtk-qa__desc { font-size: .78rem; color: #64748b; }
        .gtk-qa__arrow { color: #cbd5e1; font-size: .75rem; }
    </style>
@endsection
