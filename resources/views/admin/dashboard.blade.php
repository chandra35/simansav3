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
        <div class="col-lg-3 col-6 mb-3">
            <div class="simansa-stat-card simansa-stat-card--indigo">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="simansa-stat-card__label">Total Siswa</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total_siswa']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Siswa pada tahun aktif</span>
                    <a href="{{ route('admin.siswa.statistics') }}">Lihat statistik</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="simansa-stat-card simansa-stat-card--emerald">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="simansa-stat-card__label">Sudah Aktivasi</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['siswa_aktif']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Sudah login dan aktif</span>
                    <span>{{ $persenAktif }}% dari total siswa</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="simansa-stat-card simansa-stat-card--amber">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="simansa-stat-card__label">Jumlah GTK</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total_gtk']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Guru dan tenaga kependidikan</span>
                    @can('view-gtk')
                        <a href="{{ route('admin.gtk.index') }}">Lihat GTK</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="simansa-stat-card simansa-stat-card--teal">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-circle" style="font-size:0.85rem; color:#4ade80;"></i>
                </div>
                <div class="simansa-stat-card__label">Sedang Online</div>
                <div class="simansa-stat-card__value" id="stat-online-count">{{ number_format($stats['online_count']) }}</div>
                <div class="simansa-stat-card__footer">
                    <span>Aktif dalam 5 menit</span>
                    <a href="#online-panel" onclick="document.getElementById('online-panel').scrollIntoView({behavior:'smooth'}); return false;">Lihat siapa</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Online Users Panel --}}
    <div class="row" id="online-panel">
        <div class="col-12 mb-4">
            <div class="card simansa-panel simansa-online-card">
                <div class="card-header border-0 pb-0 d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem">
                    <h3 class="card-title mb-0">
                        <span class="simansa-pulse-dot"></span>
                        Sedang Online Sekarang
                    </h3>
                    <div class="d-flex align-items-center" style="gap:.75rem">
                        <span class="text-muted" style="font-size:.8rem">Diperbarui: <span id="online-updated-at">—</span></span>
                        <button class="btn btn-sm btn-light" id="btn-refresh-online" title="Refresh">
                            <i class="fas fa-sync-alt" id="refresh-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="online-users-grid" class="simansa-online-grid">
                        <div class="simansa-online-skeleton" id="online-loading">
                            @for($i=0;$i<6;$i++)
                            <div class="simansa-online-skeleton__item"></div>
                            @endfor
                        </div>
                    </div>
                    <div id="online-empty" class="simansa-online-empty d-none">
                        <i class="fas fa-moon"></i>
                        <p>Tidak ada pengguna yang sedang online.</p>
                    </div>
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
                                <div class="simansa-highlight-item__title">Guru dan Tenaga Kependidikan</div>
                                <div class="simansa-highlight-item__desc">
                                    Terdapat {{ $stats['total_gtk'] }} GTK yang tercatat di SIMANSA.
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

        /* ── Stat Cards ── */
        .simansa-stat-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 46px 1fr;
            grid-template-rows: auto auto auto;
            grid-template-areas:
                "icon label"
                "icon value"
                "footer footer";
            column-gap: 0.8rem;
            border-radius: 1rem;
            padding: 0.85rem 1rem;
            color: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.13);
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }

        .simansa-stat-card:hover {
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
            transform: translateY(-2px);
        }

        .simansa-stat-card::before {
            content: "";
            position: absolute;
            right: -30px; bottom: -36px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
        }

        .simansa-stat-card--indigo  { background: linear-gradient(135deg, #5b63f1, #6875f5); }
        .simansa-stat-card--emerald { background: linear-gradient(135deg, #2dc38b, #56d9a2); }
        .simansa-stat-card--amber   { background: linear-gradient(135deg, #f0a700, #ffc233); color: #172554; }
        .simansa-stat-card--rose    { background: linear-gradient(135deg, #f4767d, #f99195); }
        .simansa-stat-card--teal    { background: linear-gradient(135deg, #0891b2, #22d3ee); }

        .simansa-stat-card--amber .simansa-stat-card__label,
        .simansa-stat-card--amber .simansa-stat-card__footer,
        .simansa-stat-card--amber .simansa-stat-card__footer a,
        .simansa-stat-card--amber .simansa-stat-card__icon { color: #172554; }

        .simansa-stat-card__icon {
            grid-area: icon;
            align-self: center;
            width: 46px; height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.15);
            position: relative; z-index: 1;
            flex-shrink: 0;
        }

        .simansa-stat-card__label {
            grid-area: label;
            align-self: end;
            position: relative; z-index: 1;
            font-size: 0.67rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 700;
            opacity: 0.82;
            line-height: 1;
            padding-bottom: 0.12rem;
        }

        .simansa-stat-card__value {
            grid-area: value;
            align-self: start;
            position: relative; z-index: 1;
            font-size: 1.8rem;
            line-height: 1.05;
            font-weight: 800;
        }

        .simansa-stat-card__footer {
            grid-area: footer;
            position: relative; z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.76rem;
            margin-top: 0.6rem;
            padding-top: 0.6rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .simansa-stat-card__footer > span:first-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1 1 0;
            min-width: 0;
            opacity: 0.88;
        }

        .simansa-stat-card__footer a {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            flex-shrink: 0;
            white-space: nowrap;
            opacity: 0.95;
        }

        .simansa-stat-card__footer a:hover { opacity: 1; text-decoration: underline; }

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
            .simansa-stat-card__value { font-size: 1.55rem; }
        }

        @media (max-width: 575.98px) {
            .simansa-stat-card {
                grid-template-columns: 38px 1fr;
                column-gap: 0.6rem;
                padding: 0.7rem 0.8rem;
                border-radius: 0.85rem;
            }
            .simansa-stat-card__icon { width: 38px; height: 38px; font-size: 0.9rem; border-radius: 0.65rem; }
            .simansa-stat-card__value { font-size: 1.35rem; }
            .simansa-stat-card__label { font-size: 0.6rem; }
            .simansa-stat-card__footer { font-size: 0.68rem; margin-top: 0.45rem; padding-top: 0.45rem; }
        }

        /* ── Online Panel ── */
        .simansa-online-card {
            border: 1px solid #d8e3f4 !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06) !important;
            overflow: visible !important;
        }

        .simansa-online-card .card-body { overflow: visible !important; }

        .simansa-pulse-dot {
            display: inline-block;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #22c55e;
            margin-right: 8px;
            position: relative; top: -1px;
            animation: simansa-pulse 2s infinite;
        }

        @keyframes simansa-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
            70%  { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }

        .simansa-online-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        /* Card base */
        .simansa-online-user {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.62rem;
            padding: 0.52rem 0.78rem;
            background: #f8fbff;
            border: 1px solid #dce7f5;
            border-radius: 0.85rem;
            min-width: 170px;
            max-width: 225px;
            flex: 1 1 170px;
            cursor: default;
            /* Base transition — hover + exit */
            transition: box-shadow 0.22s ease, transform 0.22s ease, opacity 0.32s ease;
        }

        /* Enter: start hidden so transition can play */
        .simansa-online-user.entering {
            opacity: 0;
            transform: scale(0.88) translateY(10px);
            transition: none !important;
        }

        /* Exit: fade + shrink up */
        .simansa-online-user.exiting {
            opacity: 0 !important;
            transform: scale(0.84) translateY(-8px) !important;
            pointer-events: none;
        }

        .simansa-online-user:hover:not(.exiting) {
            box-shadow: 0 6px 20px rgba(37,99,235,0.14);
            transform: translateY(-2px);
            z-index: 200;
        }

        /* photo wrapper for green dot */
        .simansa-online-user__photo-wrap {
            position: relative;
            flex-shrink: 0;
            width: 40px; height: 40px;
        }

        .simansa-online-user__photo {
            width: 40px; height: 40px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            border: 2px solid #e2eaf5;
            transition: border-color 0.2s;
        }

        .simansa-online-user:hover .simansa-online-user__photo { border-color: #93c5fd; }

        .simansa-online-dot {
            position: absolute;
            bottom: 0; right: 0;
            width: 11px; height: 11px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid #fff;
            animation: simansa-pulse 2.2s ease infinite;
        }

        .simansa-online-user__name {
            font-weight: 700;
            font-size: 0.82rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 115px;
        }

        .simansa-online-user__meta {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.08rem;
        }

        .simansa-online-user__role {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.12rem 0.4rem;
            border-radius: 999px;
        }

        .simansa-online-user__device {
            font-size: 0.66rem;
            color: #94a3b8;
        }

        .simansa-online-user__time {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.06rem;
            transition: opacity 0.25s;
        }

        /* ── Tooltip ── */
        .simansa-online-tt {
            position: absolute;
            bottom: calc(100% + 11px);
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            background: #1e293b;
            border-radius: 14px;
            padding: 12px 14px 10px;
            min-width: 200px;
            opacity: 0;
            pointer-events: none;
            z-index: 1050;
            box-shadow: 0 14px 38px rgba(0,0,0,0.28);
            transition: opacity 0.22s ease, transform 0.22s ease;
            white-space: nowrap;
        }

        .simansa-online-tt::after {
            content: '';
            position: absolute;
            top: 100%; left: 50%;
            transform: translateX(-50%);
            border: 7px solid transparent;
            border-top-color: #1e293b;
        }

        .simansa-online-user:hover .simansa-online-tt {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .simansa-online-tt__head {
            display: flex;
            align-items: center;
            gap: 9px;
            padding-bottom: 8px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .simansa-online-tt__photo {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.22);
            flex-shrink: 0;
        }

        .simansa-online-tt__name {
            font-weight: 700;
            font-size: 0.83rem;
            color: #f1f5f9;
            line-height: 1.25;
        }

        .simansa-online-tt__role {
            font-size: 0.7rem;
            color: #7dd3fc;
            margin-top: 1px;
        }

        .simansa-online-tt__row {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.72rem;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .simansa-online-tt__row i {
            width: 13px;
            text-align: center;
            color: #7dd3fc;
            flex-shrink: 0;
        }

        .simansa-online-tt__row + .simansa-online-tt__row { margin-top: 2px; }

        /* Skeleton */
        .simansa-online-skeleton {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            width: 100%;
        }

        .simansa-online-skeleton__item {
            min-width: 170px;
            max-width: 225px;
            flex: 1 1 170px;
            height: 60px;
            border-radius: 0.85rem;
            background: linear-gradient(90deg, #f0f5fc 25%, #e2ecf9 50%, #f0f5fc 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .simansa-online-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #94a3b8;
        }

        .simansa-online-empty i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
        .simansa-online-empty p { margin: 0; font-size: 0.9rem; }

        #btn-refresh-online.spinning i { animation: spin 0.7s linear infinite; }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
@endsection

@section('js')
    <script>
        const onlineApiUrl = '{{ route('admin.dashboard.online-users') }}';

        // Role badge hidden in card for these roles (still visible in tooltip)
        const HIDE_BADGE = ['Super Admin', 'Admin'];

        // Persistent state: userId (string) → card DOM element
        const onlineState = new Map();

        function roleColor(role) {
            const map = {
                'Operator': 'background:#e0f2fe;color:#0369a1',
                'GTK':      'background:#d1fae5;color:#065f46',
                'Siswa':    'background:#fce7f3;color:#9d174d',
                'WAKA':     'background:#fef9c3;color:#854d0e',
            };
            return map[role] || 'background:#f1f5f9;color:#334155';
        }

        function esc(s) {
            return String(s ?? '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function buildCard(u) {
            const el = document.createElement('div');
            el.className = 'simansa-online-user entering';
            el.dataset.uid = String(u.id);

            const showBadge = !HIDE_BADGE.includes(u.role);
            const badge = showBadge
                ? `<span class="simansa-online-user__role" style="${esc(roleColor(u.role))}">${esc(u.role)}</span>`
                : '';
            const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=80&background=64748b&color=fff&bold=true`;

            el.innerHTML = `
                <div class="simansa-online-user__photo-wrap">
                    <img src="${esc(u.photo)}" alt="${esc(u.name)}" class="simansa-online-user__photo"
                         onerror="this.onerror=null;this.src='${fallback}'">
                    <span class="simansa-online-dot"></span>
                </div>
                <div style="min-width:0;flex:1">
                    <div class="simansa-online-user__name">${esc(u.name)}</div>
                    <div class="simansa-online-user__meta">
                        ${badge}
                        <span class="simansa-online-user__device">
                            <i class="${esc(u.device_icon)}"></i>
                            <i class="${esc(u.browser_icon)}"></i>
                        </span>
                    </div>
                    <div class="simansa-online-user__time">
                        <i class="fas fa-clock" style="font-size:.58rem;margin-right:2px"></i>
                        <span class="ou-time">${esc(u.last_activity)}</span>
                    </div>
                </div>
                <div class="simansa-online-tt">
                    <div class="simansa-online-tt__head">
                        <img src="${esc(u.photo)}" class="simansa-online-tt__photo"
                             onerror="this.onerror=null;this.src='${fallback}'">
                        <div>
                            <div class="simansa-online-tt__name">${esc(u.name)}</div>
                            <div class="simansa-online-tt__role">${esc(u.role)}</div>
                        </div>
                    </div>
                    <div class="simansa-online-tt__row">
                        <i class="${esc(u.device_icon)}"></i>
                        <i class="${esc(u.browser_icon)}"></i>
                        <span>Perangkat &amp; Browser</span>
                    </div>
                    <div class="simansa-online-tt__row">
                        <i class="fas fa-clock"></i>
                        <span class="tt-time">${esc(u.last_activity)}</span>
                    </div>
                </div>`;
            return el;
        }

        function updateCard(el, u) {
            // Smoothly update last_activity text
            const timeEl = el.querySelector('.ou-time');
            if (timeEl && timeEl.textContent !== u.last_activity) {
                timeEl.style.opacity = '0';
                setTimeout(() => {
                    timeEl.textContent = u.last_activity;
                    timeEl.style.opacity = '1';
                }, 220);
            }
            const ttTime = el.querySelector('.tt-time');
            if (ttTime) ttTime.textContent = u.last_activity;
        }

        function removeCard(el) {
            el.classList.add('exiting');
            setTimeout(() => el.remove(), 380);
        }

        function animateIn(el) {
            // Double rAF: paint 'entering' state first, then remove it so CSS transition plays
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { el.classList.remove('entering'); });
            });
        }

        function loadOnlineUsers() {
            const grid    = document.getElementById('online-users-grid');
            const empty   = document.getElementById('online-empty');
            const loading = document.getElementById('online-loading');
            const btnRef  = document.getElementById('btn-refresh-online');
            const updEl   = document.getElementById('online-updated-at');
            const statEl  = document.getElementById('stat-online-count');

            if (btnRef) btnRef.classList.add('spinning');

            fetch(onlineApiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.remove();

                    const users   = data.users || [];
                    const newIdSet = new Set(users.map(u => String(u.id)));

                    if (updEl)  updEl.textContent  = data.updated_at || '';
                    if (statEl) statEl.textContent = String(data.total || 0);

                    // Animate out users that went offline
                    for (const [uid, el] of onlineState) {
                        if (!newIdSet.has(uid)) {
                            removeCard(el);
                            onlineState.delete(uid);
                        }
                    }

                    // Add new users / update existing ones
                    users.forEach(u => {
                        const uid = String(u.id);
                        if (onlineState.has(uid)) {
                            updateCard(onlineState.get(uid), u);
                        } else {
                            const el = buildCard(u);
                            grid.appendChild(el);
                            onlineState.set(uid, el);
                            animateIn(el);
                        }
                    });

                    // Empty state
                    (onlineState.size === 0)
                        ? empty.classList.remove('d-none')
                        : empty.classList.add('d-none');

                    if (btnRef) btnRef.classList.remove('spinning');
                })
                .catch(() => {
                    if (btnRef) btnRef.classList.remove('spinning');
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadOnlineUsers();
            setInterval(loadOnlineUsers, 15000);
            const btnRef = document.getElementById('btn-refresh-online');
            if (btnRef) btnRef.addEventListener('click', loadOnlineUsers);
        });
    </script>
@endsection
