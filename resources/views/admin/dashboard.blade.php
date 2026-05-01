@extends('adminlte::page')

@section('title', 'Dashboard - SIMANSA')

@php
    $siswaBelumAktif = $stats['total_siswa'] - $stats['siswa_aktif'];
    $persenAktif = $stats['total_siswa'] > 0 ? round(($stats['siswa_aktif'] / $stats['total_siswa']) * 100) : 0;
@endphp

@section('content_header')
    <div class="simansa-page-hero">
        <div class="simansa-page-hero__body">
    <div class="simansa-dashboard-header">
        <div class="simansa-dashboard-header__content">
            <div class="simansa-dashboard-hero__eyebrow">
                <i class="fas fa-chart-line mr-2"></i>Ringkasan Operasional SIMANSA
            </div>
            <h1 class="simansa-dashboard-hero__title">Dashboard Administrasi</h1>
            <p class="simansa-dashboard-hero__subtitle mb-0">
                Pantau aktivitas pengguna, status aktivasi siswa, dan gambaran umum sistem dari satu halaman yang lebih rapi.
            </p>
        </div>
        <div class="simansa-dashboard-header__meta">
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Tahun Pelajaran Aktif</span>
                <span class="simansa-dashboard-chip__value">{{ $tahunPelajaranAktif?->nama ?? 'Belum diatur' }}</span>
            </div>
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Semester</span>
                <span class="simansa-dashboard-chip__value">{{ $tahunPelajaranAktif ? 'Semester '.$tahunPelajaranAktif->semester_aktif : '-' }}</span>
            </div>
            <div class="simansa-dashboard-chip">
                <span class="simansa-dashboard-chip__label">Aktivasi Siswa</span>
                <span class="simansa-dashboard-chip__value">{{ $persenAktif }}%</span>
            </div>
        </div>
    </div>
    </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--indigo">
                <div class="simansa-stat-card__top">
                    <div class="simansa-stat-card__icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="simansa-stat-card__label">Total Siswa</div>
                </div>
                <div class="simansa-stat-card__metric">
                    <div class="simansa-stat-card__value">{{ number_format($stats['total_siswa']) }}</div>
                    <div class="simansa-stat-card__meta">Seluruh siswa terdaftar di SIMANSA</div>
                </div>
                <div class="simansa-stat-card__footer">
                    <span class="simansa-stat-card__note">Data induk siswa aktif dan arsip berjalan</span>
                    <a href="{{ route('admin.siswa.index') }}" class="simansa-stat-card__action">Lihat Data</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--emerald">
                <div class="simansa-stat-card__top">
                    <div class="simansa-stat-card__icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="simansa-stat-card__label">Siswa Aktif</div>
                </div>
                <div class="simansa-stat-card__metric">
                    <div class="simansa-stat-card__value">{{ number_format($stats['siswa_aktif']) }}</div>
                    <div class="simansa-stat-card__meta">Sudah login dan menyelesaikan aktivasi akun</div>
                </div>
                <div class="simansa-stat-card__footer">
                    <span class="simansa-stat-card__note">Cakupan aktivasi saat ini {{ $persenAktif }}% dari total siswa</span>
                    <span class="simansa-stat-card__pill">{{ $persenAktif }}%</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--amber">
                <div class="simansa-stat-card__top">
                    <div class="simansa-stat-card__icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="simansa-stat-card__label">Total Admin</div>
                </div>
                <div class="simansa-stat-card__metric">
                    <div class="simansa-stat-card__value">{{ number_format($stats['total_admin']) }}</div>
                    <div class="simansa-stat-card__meta">Admin, operator, dan akun GTK non-siswa</div>
                </div>
                <div class="simansa-stat-card__footer">
                    <span class="simansa-stat-card__note">Kontrol akses dan akun operasional sistem</span>
                    <a href="{{ route('admin.users.index') }}" class="simansa-stat-card__action">Kelola Akun</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="simansa-stat-card simansa-stat-card--rose">
                <div class="simansa-stat-card__top">
                    <div class="simansa-stat-card__icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="simansa-stat-card__label">Belum Aktif</div>
                </div>
                <div class="simansa-stat-card__metric">
                    <div class="simansa-stat-card__value">{{ number_format($siswaBelumAktif) }}</div>
                    <div class="simansa-stat-card__meta">Masih perlu pendampingan untuk login awal</div>
                </div>
                <div class="simansa-stat-card__footer">
                    <span class="simansa-stat-card__note">Proporsi siswa belum aktif {{ max(0, 100 - $persenAktif) }}%</span>
                    <span class="simansa-stat-card__pill">{{ number_format($siswaBelumAktif) }}</span>
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
        .simansa-dashboard-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem 1.25rem;
        }

        .simansa-dashboard-hero__eyebrow {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.86);
            opacity: 1;
            margin-bottom: 0.55rem;
        }

        .simansa-dashboard-hero__title {
            font-size: 2rem !important;
            font-weight: 800 !important;
            color: #ffffff;
            margin-bottom: 0.4rem;
        }

        .simansa-dashboard-hero__subtitle {
            font-size: 0.96rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.9);
            max-width: 720px;
        }

        .simansa-dashboard-header__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            justify-content: flex-end;
            max-width: 560px;
        }

        .simansa-dashboard-chip {
            min-width: 165px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(216, 227, 244, 0.95);
            border-radius: 1rem;
            padding: 0.8rem 0.95rem;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.08);
        }

        .simansa-dashboard-chip__label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #64748b;
            margin-bottom: 0.2rem;
        }

        .simansa-dashboard-chip__value {
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .simansa-stat-card {
            position: relative;
            overflow: hidden;
            min-height: 228px;
            border-radius: 1.35rem;
            padding: 1.25rem 1.25rem 1.1rem;
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
            display: flex;
            flex-direction: column;
            gap: 1rem;
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
        .simansa-stat-card--amber .simansa-stat-card__meta,
        .simansa-stat-card--amber .simansa-stat-card__note,
        .simansa-stat-card--amber .simansa-stat-card__footer,
        .simansa-stat-card--amber .simansa-stat-card__footer a,
        .simansa-stat-card--amber .simansa-stat-card__action,
        .simansa-stat-card--amber .simansa-stat-card__pill,
        .simansa-stat-card--amber .simansa-stat-card__icon {
            color: #172554;
        }

        .simansa-stat-card--amber .simansa-stat-card__action,
        .simansa-stat-card--amber .simansa-stat-card__pill {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(23, 37, 84, 0.14);
        }

        .simansa-stat-card__icon {
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, 0.14);
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .simansa-stat-card__top {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 54px minmax(0, 1fr);
            align-items: center;
            gap: 0.9rem;
        }

        .simansa-stat-card__label {
            position: relative;
            z-index: 1;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 700;
            opacity: 0.9;
            margin-bottom: 0;
            line-height: 1.45;
        }

        .simansa-stat-card__metric {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 0.55rem;
        }

        .simansa-stat-card__value {
            font-size: clamp(2rem, 2.8vw, 2.5rem);
            line-height: 0.95;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .simansa-stat-card__meta {
            font-size: 0.92rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.9);
            max-width: 24ch;
        }

        .simansa-stat-card__footer {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            gap: 0.9rem;
            align-items: flex-end;
            margin-top: auto;
            font-size: 0.84rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        .simansa-stat-card__note {
            max-width: 26ch;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.88);
        }

        .simansa-stat-card__footer a,
        .simansa-stat-card__action {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(4px);
            white-space: nowrap;
        }

        .simansa-stat-card__pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-weight: 800;
            letter-spacing: 0.01em;
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
            .simansa-dashboard-header {
                flex-direction: column;
            }

            .simansa-dashboard-hero__title {
                font-size: 1.65rem !important;
            }

            .simansa-dashboard-header__meta {
                justify-content: flex-start;
                max-width: 100%;
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

            .simansa-stat-card__meta,
            .simansa-stat-card__note {
                max-width: none;
            }
        }
    </style>
@stop
