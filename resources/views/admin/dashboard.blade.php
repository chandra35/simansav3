@extends('adminlte::page')

@section('title', 'Dashboard - SIMANSA')

@php
    $siswaBelumAktif = $stats['total_siswa'] - $stats['siswa_aktif'];
    $persenAktif = $stats['total_siswa'] > 0 ? round(($stats['siswa_aktif'] / $stats['total_siswa']) * 100) : 0;
@endphp

@section('content_header')
    <div class="simansa-dashboard-hero">
        <div class="simansa-dashboard-hero__content">
            <div class="simansa-dashboard-hero__eyebrow">
                <i class="fas fa-chart-line mr-2"></i>Ringkasan Operasional SIMANSA
            </div>
            <h1 class="simansa-dashboard-hero__title">Dashboard Administrasi</h1>
            <p class="simansa-dashboard-hero__subtitle mb-0">
                Pantau aktivitas pengguna, status aktivasi siswa, dan gambaran umum sistem dari satu halaman yang lebih rapi.
            </p>
        </div>
        <div class="simansa-dashboard-hero__meta">
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Tahun Pelajaran Aktif</span>
                <span class="simansa-dashboard-chip__value">
                    {{ $tahunPelajaranAktif?->nama ?? 'Belum diatur' }}
                </span>
            </div>
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Semester</span>
                <span class="simansa-dashboard-chip__value">
                    {{ $tahunPelajaranAktif ? 'Semester '.$tahunPelajaranAktif->semester_aktif : '-' }}
                </span>
            </div>
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Aktivasi Siswa</span>
                <span class="simansa-dashboard-chip__value">{{ $persenAktif }}%</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--indigo">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="simansa-stat-card__label">Total Siswa</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total_siswa']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Seluruh siswa terdaftar</span>
                    <a href="{{ route('admin.siswa.index') }}">Lihat data</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--emerald">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="simansa-stat-card__label">Siswa Aktif</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['siswa_aktif']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Sudah login dan aktif</span>
                    <span>{{ $persenAktif }}% dari total siswa</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--amber">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="simansa-stat-card__label">Total Admin</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total_admin']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Admin, operator, dan GTK</span>
                    <a href="{{ route('admin.users.index') }}">Kelola akun</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--rose">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="simansa-stat-card__label">Belum Aktif</div>
                <div class="simansa-stat-card__value">{{ number_format($siswaBelumAktif) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Perlu pendampingan login awal</span>
                    <span>{{ max(0, 100 - $persenAktif) }}% dari total siswa</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card simansa-panel h-100">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i>
                        Sorotan Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="simansa-highlight-list">
                        <div class="simansa-highlight-item">
                            <div class="simansa-highlight-item__icon bg-primary">
                                <i class="fas fa-school"></i>
                            </div>
                            <div>
                                <div class="simansa-highlight-item__title">Tahun Pelajaran</div>
                                <div class="simansa-highlight-item__desc">
                                    {{ $tahunPelajaranAktif?->nama ?? 'Belum ada tahun pelajaran aktif.' }}
                                </div>
                            </div>
                        </div>
                        <div class="simansa-highlight-item">
                            <div class="simansa-highlight-item__icon bg-success">
                                <i class="fas fa-signal"></i>
                            </div>
                            <div>
                                <div class="simansa-highlight-item__title">Aktivasi Akun Siswa</div>
                                <div class="simansa-highlight-item__desc">
                                    {{ $stats['siswa_aktif'] }} siswa sudah aktif, {{ $siswaBelumAktif }} masih belum login pertama kali.
                                </div>
                            </div>
                        </div>
                        <div class="simansa-highlight-item">
                            <div class="simansa-highlight-item__icon bg-warning">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div>
                                <div class="simansa-highlight-item__title">Akun Pengelola</div>
                                <div class="simansa-highlight-item__desc">
                                    Saat ini ada {{ $stats['total_admin'] }} akun non-siswa yang aktif untuk operasional sistem.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-4">
            <div class="card simansa-panel h-100">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title mb-2 mb-md-0">
                            <i class="fas fa-history"></i>
                            Aktivitas Terbaru
                        </h3>
                        <span class="simansa-section-badge">
                            {{ $stats['recent_activities']->count() }} aktivitas terakhir
                        </span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive simansa-activity-table-wrap">
                        <table class="table simansa-activity-table mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aktivitas</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_activities'] as $activity)
                                    <tr>
                                        <td>
                                            <div class="simansa-activity-time">
                                                <strong>{{ $activity->created_at->format('d/m/Y') }}</strong>
                                                <span>{{ $activity->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="simansa-activity-user">
                                                <span class="simansa-activity-user__avatar">
                                                    {{ strtoupper(substr($activity->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                                <span>{{ $activity->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="simansa-activity-badge">
                                                {{ $activity->activity_type }}
                                            </span>
                                        </td>
                                        <td>{{ $activity->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Belum ada aktivitas yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .simansa-dashboard-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.9fr);
            gap: 1rem;
            align-items: center;
        }

        .simansa-dashboard-hero__eyebrow {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            opacity: 0.88;
            margin-bottom: 0.55rem;
        }

        .simansa-dashboard-hero__title {
            font-size: 2rem !important;
            font-weight: 800 !important;
            margin-bottom: 0.4rem;
        }

        .simansa-dashboard-hero__subtitle {
            font-size: 0.96rem;
            line-height: 1.7;
            max-width: 760px;
        }

        .simansa-dashboard-hero__meta {
            display: grid;
            gap: 0.85rem;
        }

        .simansa-dashboard-chip {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 1rem;
            padding: 0.9rem 1rem;
            backdrop-filter: blur(4px);
        }

        .simansa-dashboard-chip__label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            opacity: 0.75;
            margin-bottom: 0.2rem;
        }

        .simansa-dashboard-chip__value {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .simansa-stat-card {
            position: relative;
            overflow: hidden;
            min-height: 210px;
            border-radius: 1.5rem;
            padding: 1.3rem 1.3rem 1rem;
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        }

        .simansa-stat-card::before {
            content: "";
            position: absolute;
            inset: auto -45px -60px auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .simansa-stat-card--indigo { background: linear-gradient(135deg, #5b63f1, #6875f5); }
        .simansa-stat-card--emerald { background: linear-gradient(135deg, #2dc38b, #56d9a2); }
        .simansa-stat-card--amber { background: linear-gradient(135deg, #f0a700, #ffc233); color: #172554; }
        .simansa-stat-card--rose { background: linear-gradient(135deg, #f4767d, #f99195); }

        .simansa-stat-card--amber .simansa-stat-card__label,
        .simansa-stat-card--amber .simansa-stat-card__footer,
        .simansa-stat-card--amber .simansa-stat-card__footer a,
        .simansa-stat-card--amber .simansa-stat-card__icon {
            color: #172554;
        }

        .simansa-stat-card__icon {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.1rem;
            font-size: 1.35rem;
            background: rgba(255, 255, 255, 0.14);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .simansa-stat-card__label {
            position: relative;
            z-index: 1;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 700;
            opacity: 0.88;
            margin-bottom: 0.4rem;
        }

        .simansa-stat-card__value {
            position: relative;
            z-index: 1;
            font-size: 2.2rem;
            line-height: 1;
            font-weight: 800;
            margin-bottom: 1.6rem;
        }

        .simansa-stat-card__footer {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            font-size: 0.88rem;
            padding-top: 0.95rem;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        .simansa-stat-card__footer a {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .simansa-panel {
            border: 1px solid #d8e3f4 !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06) !important;
        }

        .simansa-highlight-list {
            display: grid;
            gap: 1rem;
        }

        .simansa-highlight-item {
            display: flex;
            gap: 0.9rem;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 1rem;
            background: linear-gradient(180deg, #f8fbff, #f1f6fc);
            border: 1px solid #dce7f5;
        }

        .simansa-highlight-item__icon {
            width: 46px;
            height: 46px;
            border-radius: 0.95rem;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
        }

        .simansa-highlight-item__title {
            font-weight: 800;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        .simansa-highlight-item__desc {
            color: #64748b;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .simansa-section-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #eef4ff;
            color: #3359d4;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .simansa-activity-table-wrap {
            border: 1px solid #dce7f5;
            border-radius: 1rem;
            overflow: hidden;
        }

        .simansa-activity-table thead th {
            background: #f8fbff !important;
        }

        .simansa-activity-time {
            display: grid;
            gap: 0.15rem;
        }

        .simansa-activity-time strong {
            font-size: 0.88rem;
            color: #0f172a;
        }

        .simansa-activity-time span {
            font-size: 0.78rem;
            color: #64748b;
        }

        .simansa-activity-user {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 700;
            color: #1e293b;
        }

        .simansa-activity-user__avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3d64e6, #2a8b93);
            color: #fff;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .simansa-activity-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: #edf2ff;
            color: #3459d3;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: lowercase;
        }

        @media (max-width: 991.98px) {
            .simansa-dashboard-hero {
                grid-template-columns: 1fr;
            }

            .simansa-dashboard-hero__title {
                font-size: 1.65rem !important;
            }
        }

        @media (max-width: 767.98px) {
            .simansa-stat-card {
                min-height: auto;
            }

            .simansa-stat-card__footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@stop

@section('js')
    <script>console.log('SIMANSA admin dashboard refreshed');</script>
@stop
