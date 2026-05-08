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
        .simansa-stat-card--teal { background: linear-gradient(135deg, #0891b2, #22d3ee); }

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
        }

        /* ── Online Panel ── */
        .simansa-online-card {
            border: 1px solid #d8e3f4 !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06) !important;
        }

        .simansa-pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            margin-right: 8px;
            position: relative;
            top: -1px;
            box-shadow: 0 0 0 0 rgba(34,197,94,0.5);
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
            gap: 0.85rem;
        }

        .simansa-online-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.9rem;
            background: #f8fbff;
            border: 1px solid #dce7f5;
            border-radius: 1rem;
            min-width: 200px;
            max-width: 260px;
            flex: 1 1 200px;
            transition: box-shadow 0.18s, transform 0.18s;
            animation: simansa-fadein 0.3s ease both;
        }

        .simansa-online-user:hover {
            box-shadow: 0 6px 18px rgba(37,99,235,0.12);
            transform: translateY(-2px);
        }

        @keyframes simansa-fadein {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .simansa-online-user__photo {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #e2eaf5;
        }

        .simansa-online-user__name {
            font-weight: 700;
            font-size: 0.875rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        .simansa-online-user__meta {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.15rem;
        }

        .simansa-online-user__role {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.18rem 0.5rem;
            border-radius: 999px;
            background: #eef4ff;
            color: #3359d4;
        }

        .simansa-online-user__device {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .simansa-online-user__time {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 0.1rem;
        }

        .simansa-online-skeleton {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            width: 100%;
        }

        .simansa-online-skeleton__item {
            min-width: 200px;
            max-width: 260px;
            flex: 1 1 200px;
            height: 72px;
            border-radius: 1rem;
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

        .simansa-online-empty i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .simansa-online-empty p {
            margin: 0;
            font-size: 0.9rem;
        }

        #btn-refresh-online.spinning i {
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('js')
    <script>
        const onlineApiUrl = '{{ route('admin.dashboard.online-users') }}';

        function roleColor(role) {
            const map = {
                'Super Admin': 'background:#ede9fe;color:#5b21b6',
                'Admin':       'background:#dbeafe;color:#1e40af',
                'Operator':    'background:#e0f2fe;color:#0369a1',
                'GTK':         'background:#d1fae5;color:#065f46',
                'Siswa':       'background:#fce7f3;color:#9d174d',
            };
            return map[role] || 'background:#f1f5f9;color:#334155';
        }

        function buildCard(u, delay) {
            return `<div class="simansa-online-user" style="animation-delay:${delay}ms">
                <img src="${u.photo}" alt="${u.name}" class="simansa-online-user__photo"
                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=80&background=64748b&color=fff&bold=true'">
                <div style="min-width:0">
                    <div class="simansa-online-user__name" title="${u.name}">${u.name}</div>
                    <div class="simansa-online-user__meta">
                        <span class="simansa-online-user__role" style="${roleColor(u.role)}">${u.role}</span>
                        <span class="simansa-online-user__device"><i class="${u.device_icon}"></i> <i class="${u.browser_icon}"></i></span>
                    </div>
                    <div class="simansa-online-user__time"><i class="fas fa-clock" style="font-size:.65rem;margin-right:3px"></i>${u.last_activity}</div>
                </div>
            </div>`;
        }

        function loadOnlineUsers() {
            const grid   = document.getElementById('online-users-grid');
            const empty  = document.getElementById('online-empty');
            const loading = document.getElementById('online-loading');
            const btnRef  = document.getElementById('btn-refresh-online');
            const updEl   = document.getElementById('online-updated-at');
            const statEl  = document.getElementById('stat-online-count');

            if (btnRef) btnRef.classList.add('spinning');

            fetch(onlineApiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.remove();

                    const users = data.users || [];
                    if (updEl) updEl.textContent = data.updated_at || '';
                    if (statEl) statEl.textContent = data.total || '0';

                    // Fade out existing cards
                    const existingCards = grid.querySelectorAll('.simansa-online-user');
                    existingCards.forEach(c => c.style.opacity = '0');

                    setTimeout(() => {
                        existingCards.forEach(c => c.remove());

                        if (users.length === 0) {
                            empty.classList.remove('d-none');
                        } else {
                            empty.classList.add('d-none');
                            const html = users.map((u, i) => buildCard(u, i * 40)).join('');
                            grid.insertAdjacentHTML('beforeend', html);
                        }

                        if (btnRef) btnRef.classList.remove('spinning');
                    }, existingCards.length ? 200 : 0);
                })
                .catch(() => {
                    if (btnRef) btnRef.classList.remove('spinning');
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadOnlineUsers();
            setInterval(loadOnlineUsers, 30000);

            const btnRef = document.getElementById('btn-refresh-online');
            if (btnRef) btnRef.addEventListener('click', loadOnlineUsers);
        });
    </script>
@endsection
