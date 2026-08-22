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
                <span class="simansa-dashboard-chip__value"
                      data-count-target="{{ $persenAktif }}"
                      data-count-suffix="%"
                      aria-label="{{ $persenAktif }} persen">{{ $persenAktif }}%</span>
            </div>
        </div>
    </div>
    </div>
    </div>
@stop

@section('content')
    <nav class="simansa-dashboard-quick-nav d-md-none" aria-label="Akses cepat dashboard">
        <span class="simansa-dashboard-quick-nav__label">Akses cepat</span>
        <div class="simansa-dashboard-quick-nav__links">
            @can('view-siswa')
                <a href="{{ route('admin.siswa.index') }}" class="simansa-dashboard-quick-nav__link">
                    <i class="fas fa-user-graduate" aria-hidden="true"></i><span>Data Siswa</span>
                </a>
            @endcan
            @can('view-kelas')
                <a href="{{ route('admin.kelas.index') }}" class="simansa-dashboard-quick-nav__link">
                    <i class="fas fa-school" aria-hidden="true"></i><span>Kelas</span>
                </a>
            @endcan
            @can('view-gtk')
                <a href="{{ route('admin.gtk.index') }}" class="simansa-dashboard-quick-nav__link">
                    <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i><span>GTK</span>
                </a>
            @endcan
            @can('view-activity-log')
                <a href="{{ route('admin.activity-logs.index') }}" class="simansa-dashboard-quick-nav__link">
                    <i class="fas fa-history" aria-hidden="true"></i><span>Aktivitas</span>
                </a>
            @endcan
        </div>
    </nav>

    <div class="row">
        <div class="col-lg-3 col-6 mb-3">
            <div class="simansa-stat-card simansa-stat-card--indigo">
                <div class="simansa-stat-card__icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="simansa-stat-card__label">Total Siswa</div>
                <div class="simansa-stat-card__value"
                     data-count-target="{{ $stats['total_siswa'] }}"
                     aria-label="{{ number_format($stats['total_siswa'], 0, ',', '.') }} siswa">{{ number_format($stats['total_siswa']) }}</div>
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
                <div class="simansa-stat-card__value"
                     data-count-target="{{ $stats['siswa_aktif'] }}"
                     aria-label="{{ number_format($stats['siswa_aktif'], 0, ',', '.') }} siswa sudah aktivasi">{{ number_format($stats['siswa_aktif']) }}</div>
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
                <div class="simansa-stat-card__value"
                     data-count-target="{{ $stats['total_gtk'] }}"
                     aria-label="{{ number_format($stats['total_gtk'], 0, ',', '.') }} GTK">{{ number_format($stats['total_gtk']) }}</div>
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
                <div class="simansa-stat-card__value"
                     id="stat-online-count"
                     data-count-target="{{ $stats['online_count'] }}"
                     aria-label="{{ number_format($stats['online_count'], 0, ',', '.') }} pengguna sedang online">{{ number_format($stats['online_count']) }}</div>
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
                <div class="card-header border-0 simansa-online-card__header">
                    <div>
                        <h3 class="card-title mb-1">
                            <span class="simansa-pulse-dot"></span>
                            Sedang Online Sekarang
                        </h3>
                        <p class="simansa-online-card__subtitle mb-0">Pengguna aktif dalam lima menit terakhir.</p>
                    </div>
                    <div class="simansa-online-card__actions">
                        <span class="simansa-online-updated">Diperbarui <strong id="online-updated-at">—</strong></span>
                        <button class="btn simansa-online-icon-btn" id="btn-refresh-online" type="button" title="Perbarui data" aria-label="Perbarui data pengguna online">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn simansa-online-view-all" type="button" data-toggle="modal" data-target="#onlineUsersModal" aria-haspopup="dialog">
                            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="simansa-online-summary" aria-label="Ringkasan pengguna online">
                        <div class="simansa-online-summary__item is-all">
                            <span class="simansa-online-summary__icon"><i class="fas fa-users"></i></span>
                            <span><strong id="online-summary-all">0</strong><small>Semua</small></span>
                        </div>
                        <div class="simansa-online-summary__item is-student">
                            <span class="simansa-online-summary__icon"><i class="fas fa-user-graduate"></i></span>
                            <span><strong id="online-summary-siswa">0</strong><small>Siswa</small></span>
                        </div>
                        <div class="simansa-online-summary__item is-gtk">
                            <span class="simansa-online-summary__icon"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span><strong id="online-summary-gtk">0</strong><small>GTK</small></span>
                        </div>
                        <div class="simansa-online-summary__item is-staff">
                            <span class="simansa-online-summary__icon"><i class="fas fa-user-shield"></i></span>
                            <span><strong id="online-summary-staff">0</strong><small>Admin &amp; Staf</small></span>
                        </div>
                    </div>
                    <div class="simansa-online-table-wrap">
                        <table class="table simansa-online-table mb-0">
                            <thead>
                                <tr>
                                    <th>Pengguna</th>
                                    <th>Peran</th>
                                    <th>Perangkat</th>
                                    <th class="text-right">Terakhir Aktif</th>
                                </tr>
                            </thead>
                            <tbody id="online-users-table-body">
                                @for($i=0;$i<4;$i++)
                                    <tr class="simansa-online-skeleton-row">
                                        <td colspan="4"><span></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <div id="online-empty" class="simansa-online-empty d-none">
                        <i class="fas fa-moon"></i>
                        <p>Tidak ada pengguna yang sedang online.</p>
                    </div>
                    <div class="simansa-online-card__footer">
                        <span id="online-list-caption">Menampilkan pengguna yang paling baru aktif.</span>
                        <button type="button" class="btn btn-link p-0" data-toggle="modal" data-target="#onlineUsersModal">Buka daftar lengkap</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade simansa-online-modal" id="onlineUsersModal" tabindex="-1" role="dialog" aria-labelledby="onlineUsersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="simansa-online-modal__eyebrow"><span class="simansa-pulse-dot"></span>Monitoring Langsung</span>
                        <h4 class="modal-title" id="onlineUsersModalLabel">Pengguna Sedang Online</h4>
                        <p class="mb-0">Cari pengguna dan pantau perangkat serta aktivitas terakhirnya.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="simansa-online-toolbar">
                        <div class="simansa-online-search">
                            <i class="fas fa-search"></i>
                            <input type="search" id="online-search" class="form-control" placeholder="Cari nama, username, NISN, NIP..." aria-label="Cari pengguna online" autocomplete="off">
                        </div>
                        <select id="online-role-filter" class="form-control" aria-label="Filter peran">
                            <option value="">Semua Peran</option>
                            <option value="siswa">Siswa</option>
                            <option value="gtk">GTK</option>
                            <option value="staff">Admin &amp; Staf</option>
                        </select>
                        <button type="button" class="btn simansa-online-icon-btn" id="btn-refresh-online-modal" title="Perbarui daftar" aria-label="Perbarui daftar pengguna online">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="simansa-online-modal__result" id="online-modal-result">Memuat data pengguna...</div>
                    <div class="simansa-online-table-wrap is-modal">
                        <table class="table simansa-online-table mb-0">
                            <thead>
                                <tr>
                                    <th>Pengguna</th>
                                    <th>Peran</th>
                                    <th>Perangkat</th>
                                    <th class="text-right">Terakhir Aktif</th>
                                </tr>
                            </thead>
                            <tbody id="online-modal-table-body"></tbody>
                        </table>
                    </div>
                    <div id="online-modal-empty" class="simansa-online-empty d-none">
                        <i class="fas fa-search"></i>
                        <p>Pengguna tidak ditemukan.</p>
                    </div>
                </div>
                <div class="modal-footer simansa-online-pagination">
                    <span id="online-modal-page-info">Halaman 1</span>
                    <div>
                        <button class="btn btn-outline-primary" type="button" id="online-page-prev"><i class="fas fa-chevron-left mr-1"></i> Sebelumnya</button>
                        <button class="btn btn-outline-primary" type="button" id="online-page-next">Selanjutnya <i class="fas fa-chevron-right ml-1"></i></button>
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
                                        <td data-label="Waktu">
                                            <div class="simansa-activity-time">
                                                <strong>{{ $activity->created_at->format('d/m/Y') }}</strong>
                                                <span>{{ $activity->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td data-label="Pengguna">
                                            <div class="simansa-activity-user">
                                                <span class="simansa-activity-user__avatar">
                                                    {{ strtoupper(substr($activity->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                                <span>{{ $activity->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td data-label="Aktivitas">
                                            <span class="simansa-activity-badge">
                                                {{ $activity->activity_type }}
                                            </span>
                                        </td>
                                        <td data-label="Deskripsi">{{ $activity->description }}</td>
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

        .simansa-dashboard-quick-nav {
            margin: 0 0 1rem;
            padding: .8rem;
            border: 1px solid #dce7f5;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        }

        .simansa-dashboard-quick-nav__label {
            display: block;
            margin: 0 0 .55rem .1rem;
            color: #53637d;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .simansa-dashboard-quick-nav__links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
        }

        .simansa-dashboard-quick-nav__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 44px;
            padding: .55rem .65rem;
            border: 1px solid #d7e3f4;
            border-radius: .75rem;
            color: #2347c2;
            font-size: .8rem;
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
            text-decoration: none;
            background: #f8fbff;
        }

        .simansa-dashboard-quick-nav__link:hover {
            color: #1d3aa0;
            background: #eef4ff;
            text-decoration: none;
        }

        .simansa-dashboard-quick-nav__link:focus-visible,
        .simansa-stat-card__footer a:focus-visible,
        .simansa-online-icon-btn:focus-visible,
        .simansa-online-view-all:focus-visible {
            outline: 3px solid rgba(49, 91, 234, .35);
            outline-offset: 2px;
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
            font-variant-numeric: tabular-nums;
            transition: opacity .2s ease, transform .2s ease;
        }

        .simansa-stat-card__value.is-counting,
        .simansa-dashboard-chip__value.is-counting {
            opacity: .96;
            transform: translateY(-1px);
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

        @media (prefers-reduced-motion: reduce) {
            .simansa-stat-card,
            .simansa-online-table tbody tr,
            .simansa-pulse-dot,
            .simansa-online-skeleton-row span {
                animation: none !important;
                transition: none !important;
            }
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
            .simansa-dashboard-header__meta {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
                gap: .55rem;
            }

            .simansa-dashboard-chip {
                min-width: 0;
                padding: .7rem .75rem;
            }

            .simansa-dashboard-chip:last-child {
                grid-column: 1 / -1;
            }

            .simansa-stat-card__value { font-size: 1.55rem; }

            .simansa-activity-table-wrap {
                overflow: visible;
                border: 0;
                border-radius: 0;
            }

            .simansa-activity-table,
            .simansa-activity-table tbody,
            .simansa-activity-table tr,
            .simansa-activity-table td {
                display: block;
                width: 100%;
            }

            .simansa-activity-table thead {
                display: none;
            }

            .simansa-activity-table tbody {
                display: grid;
                gap: .65rem;
            }

            .simansa-activity-table tbody tr {
                padding: .75rem;
                border: 1px solid #dce7f5;
                border-radius: .85rem;
                background: #fff;
                box-shadow: 0 5px 14px rgba(15, 23, 42, .035);
            }

            .simansa-activity-table tbody td {
                padding: .32rem 0;
                border: 0;
            }

            .simansa-activity-table tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: .16rem;
                color: #71809a;
                font-size: .66rem;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .simansa-activity-table tbody td[colspan] {
                text-align: center;
            }
        }

        @media (max-width: 575.98px) {
            .simansa-dashboard-hero__title { font-size: 1.4rem !important; }
            .simansa-dashboard-hero__subtitle { font-size: .88rem; line-height: 1.55; }

            .simansa-stat-card {
                grid-template-columns: 38px 1fr;
                column-gap: 0.6rem;
                padding: 0.7rem 0.8rem;
                border-radius: 0.85rem;
            }
            .simansa-stat-card__icon { width: 38px; height: 38px; font-size: 0.9rem; border-radius: 0.65rem; }
            .simansa-stat-card__value { font-size: 1.35rem; }
            .simansa-stat-card__label { font-size: 0.6rem; }
            .simansa-stat-card__footer {
                align-items: flex-start;
                flex-direction: column;
                font-size: 0.68rem;
                margin-top: 0.45rem;
                padding-top: 0.45rem;
            }
            .simansa-stat-card__footer > span:first-child { white-space: normal; }
            .simansa-stat-card__footer a { min-height: 28px; display: inline-flex; align-items: center; }
        }

        /* Online users */
        .simansa-online-card {
            border: 1px solid #d8e3f4 !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06) !important;
            overflow: hidden;
        }

        .simansa-online-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem .8rem;
        }

        .simansa-online-card__subtitle,
        .simansa-online-updated {
            color: #71809a;
            font-size: .76rem;
        }

        .simansa-online-card__actions,
        .simansa-online-toolbar,
        .simansa-online-pagination,
        .simansa-online-pagination > div {
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .simansa-pulse-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            margin-right: 8px;
            border-radius: 50%;
            background: #22c55e;
            animation: simansa-pulse 2s infinite;
        }

        @keyframes simansa-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, .48); }
            70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .simansa-online-icon-btn,
        .simansa-online-view-all {
            height: 36px;
            border: 1px solid #d6e1f2;
            border-radius: 10px;
            background: #fff;
            color: #3154cf;
            font-weight: 700;
            font-size: .78rem;
        }

        .simansa-online-icon-btn {
            width: 36px;
            padding: 0;
        }

        .simansa-online-view-all { padding: 0 .85rem; }
        .simansa-online-icon-btn:hover,
        .simansa-online-view-all:hover { background: #eef4ff; color: #2347c2; }
        .simansa-online-icon-btn.spinning i,
        #btn-refresh-online.spinning i { animation: simansa-spin .7s linear infinite; }
        @keyframes simansa-spin { to { transform: rotate(360deg); } }

        .simansa-online-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .65rem;
            margin-bottom: .8rem;
        }

        .simansa-online-summary__item {
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 0;
            padding: .65rem .75rem;
            border: 1px solid #e1e9f5;
            border-radius: 12px;
            background: #f8faff;
        }

        .simansa-online-summary__icon {
            display: grid;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 10px;
            color: #315bea;
            background: #e9efff;
        }

        .simansa-online-summary__item.is-student .simansa-online-summary__icon { color: #c02663; background: #fff0f6; }
        .simansa-online-summary__item.is-gtk .simansa-online-summary__icon { color: #07875c; background: #eafaf4; }
        .simansa-online-summary__item.is-staff .simansa-online-summary__icon { color: #9a5d08; background: #fff7e5; }
        .simansa-online-summary__item strong { display: block; color: #172036; font-size: 1rem; line-height: 1.05; }
        .simansa-online-summary__item small { display: block; margin-top: 2px; color: #71809a; font-size: .68rem; font-weight: 600; }

        .simansa-online-table-wrap {
            overflow: hidden;
            border: 1px solid #e1e8f3;
            border-radius: 12px;
        }

        .simansa-online-table { table-layout: fixed; }
        .simansa-online-table thead th {
            padding: .62rem .8rem;
            border: 0;
            background: #f5f8fc;
            color: #64748b;
            font-size: .67rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .035em;
        }
        .simansa-online-table th:first-child { width: 38%; }
        .simansa-online-table th:nth-child(2) { width: 18%; }
        .simansa-online-table th:nth-child(3) { width: 26%; }
        .simansa-online-table th:last-child { width: 18%; }
        .simansa-online-table td {
            padding: .62rem .8rem;
            border-top: 1px solid #edf1f7;
            vertical-align: middle;
            color: #34425a;
            font-size: .76rem;
        }
        .simansa-online-table tbody tr { transition: background .2s ease; }
        .simansa-online-table tbody tr:hover { background: #f8fbff; }

        .simansa-online-identity {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: .65rem;
        }
        .simansa-online-avatar-wrap { position: relative; flex: 0 0 38px; }
        .simansa-online-avatar {
            width: 38px;
            height: 38px;
            border: 2px solid #e4eaf4;
            border-radius: 50%;
            object-fit: cover;
        }
        .simansa-online-dot {
            position: absolute;
            right: 0;
            bottom: 1px;
            width: 10px;
            height: 10px;
            border: 2px solid #fff;
            border-radius: 50%;
            background: #22c55e;
        }
        .simansa-online-name {
            overflow: hidden;
            color: #142037;
            font-size: .79rem;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .simansa-online-role {
            display: inline-flex;
            padding: .2rem .52rem;
            border-radius: 999px;
            background: #eef2f7;
            color: #516078;
            font-size: .67rem;
            font-weight: 800;
        }
        .simansa-online-role.is-siswa { color: #ae1f5a; background: #fff0f6; }
        .simansa-online-role.is-gtk { color: #087553; background: #eaf9f3; }
        .simansa-online-device {
            display: flex;
            align-items: center;
            gap: .55rem;
            min-width: 0;
        }
        .simansa-online-device__icons {
            display: inline-flex;
            gap: .25rem;
            color: #5270ca;
        }
        .simansa-online-device strong,
        .simansa-online-device small { display: block; }
        .simansa-online-device strong { color: #34425a; font-size: .73rem; }
        .simansa-online-device small,
        .simansa-online-time small { color: #8390a6; font-size: .66rem; }
        .simansa-online-time { text-align: right; }
        .simansa-online-time strong { display: block; color: #34425a; font-size: .73rem; }

        .simansa-online-card__footer {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding-top: .7rem;
            color: #75839a;
            font-size: .7rem;
        }
        .simansa-online-card__footer .btn-link { font-size: .7rem; font-weight: 700; }

        .simansa-online-skeleton-row td { padding: .68rem .8rem; }
        .simansa-online-skeleton-row span {
            display: block;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(90deg, #f1f5fa 25%, #e5edf7 50%, #f1f5fa 75%);
            background-size: 200% 100%;
            animation: simansa-shimmer 1.35s infinite;
        }
        @keyframes simansa-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .simansa-online-empty {
            padding: 2rem 1rem;
            text-align: center;
            color: #8b98ad;
        }
        .simansa-online-empty i { display: block; margin-bottom: .45rem; font-size: 1.8rem; }
        .simansa-online-empty p { margin: 0; font-size: .82rem; }

        .simansa-online-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 25px 70px rgba(15, 23, 42, .22);
        }
        .simansa-online-modal .modal-header {
            align-items: flex-start;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid #dce6f4;
            background: linear-gradient(135deg, #eff4ff, #f8fbff);
        }
        .simansa-online-modal .modal-header h4 { color: #172036; font-size: 1.25rem; font-weight: 800; }
        .simansa-online-modal .modal-header p { color: #71809a; font-size: .77rem; }
        .simansa-online-modal__eyebrow { color: #315bea; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        .simansa-online-modal .modal-body { padding: 1rem 1.25rem; }
        .simansa-online-toolbar { margin-bottom: .75rem; }
        .simansa-online-search { position: relative; flex: 1; }
        .simansa-online-search i { position: absolute; top: 50%; left: .85rem; z-index: 2; color: #8290a5; transform: translateY(-50%); }
        .simansa-online-search .form-control { height: 40px; padding-left: 2.35rem; border-color: #d7e1ef; border-radius: 10px; }
        .simansa-online-toolbar select { flex: 0 0 190px; height: 40px; border-color: #d7e1ef; border-radius: 10px; }
        .simansa-online-modal__result { margin-bottom: .55rem; color: #71809a; font-size: .72rem; }
        .simansa-online-table-wrap.is-modal { max-height: 52vh; overflow-y: auto; }
        .simansa-online-pagination { justify-content: space-between; padding: .8rem 1.25rem; }
        .simansa-online-pagination > span { color: #71809a; font-size: .74rem; }
        .simansa-online-pagination .btn { border-radius: 9px; font-size: .73rem; font-weight: 700; }

        @media (max-width: 767.98px) {
            .simansa-online-card__header { align-items: flex-start; }
            .simansa-online-updated { display: none; }
            .simansa-online-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .simansa-online-table-wrap { overflow: visible; border: 0; }
            .simansa-online-table,
            .simansa-online-table tbody,
            .simansa-online-table tr,
            .simansa-online-table td { display: block; width: 100%; }
            .simansa-online-table thead { display: none; }
            .simansa-online-table tbody { display: grid; gap: .6rem; }
            .simansa-online-table tbody tr {
                display: grid;
                grid-template-columns: 1fr 1fr;
                padding: .7rem;
                border: 1px solid #e1e8f3;
                border-radius: 12px;
                background: #fff;
            }
            .simansa-online-table td {
                padding: .25rem;
                border: 0;
                text-align: left !important;
            }
            .simansa-online-table td:first-child { grid-column: 1 / -1; padding-bottom: .55rem; border-bottom: 1px solid #edf1f7; }
            .simansa-online-table td:nth-child(2) { grid-column: 1; }
            .simansa-online-table td:nth-child(3) { grid-column: 1 / -1; }
            .simansa-online-table td:nth-child(4) { grid-column: 2; grid-row: 2; }
            .simansa-online-time { text-align: right; }
            .simansa-online-skeleton-row td { grid-column: 1 / -1 !important; }
            .simansa-online-toolbar { align-items: stretch; flex-wrap: wrap; }
            .simansa-online-search { flex: 1 1 100%; }
            .simansa-online-toolbar select { flex: 1; }
        }

        @media (max-width: 575.98px) {
            .simansa-online-card__header { flex-direction: column; }
            .simansa-online-card__actions { width: 100%; justify-content: space-between; }
            .simansa-online-icon-btn { width: 44px; height: 44px; }
            .simansa-online-view-all { min-height: 44px; padding: 0 .75rem; }
            .simansa-online-card__footer { flex-direction: column; }
            .simansa-online-modal .modal-dialog { margin: .5rem; }
            .simansa-online-modal .modal-header,
            .simansa-online-modal .modal-body { padding: 1rem; }
            .simansa-online-toolbar select { flex: 1 1 calc(100% - 3.25rem); }
            .simansa-online-pagination { align-items: stretch; flex-direction: column; }
            .simansa-online-pagination > div,
            .simansa-online-pagination .btn { flex: 1; }
        }
    </style>
@endsection

@section('js')
    <script>
        const onlineApiUrl = '{{ route('admin.dashboard.online-users') }}';
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const counterAnimations = new WeakMap();
        const counterFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

        function renderCounterValue(element, value) {
            const suffix = element.dataset.countSuffix || '';
            element.textContent = `${counterFormatter.format(Math.round(value))}${suffix}`;
        }

        function animateCounter(element, target, duration = 950) {
            if (!element) return;

            const normalizedTarget = Math.max(0, Number(target) || 0);
            const runningAnimation = counterAnimations.get(element);
            if (runningAnimation) cancelAnimationFrame(runningAnimation);

            const currentValue = Number(element.dataset.countCurrent ?? normalizedTarget);
            if (prefersReducedMotion.matches || currentValue === normalizedTarget) {
                renderCounterValue(element, normalizedTarget);
                element.dataset.countCurrent = String(normalizedTarget);
                element.classList.remove('is-counting');
                return;
            }

            const startedAt = performance.now();
            element.classList.add('is-counting');

            const frame = now => {
                const progress = Math.min((now - startedAt) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                const nextValue = currentValue + ((normalizedTarget - currentValue) * easedProgress);

                renderCounterValue(element, nextValue);
                element.dataset.countCurrent = String(nextValue);

                if (progress < 1) {
                    counterAnimations.set(element, requestAnimationFrame(frame));
                    return;
                }

                renderCounterValue(element, normalizedTarget);
                element.dataset.countCurrent = String(normalizedTarget);
                element.classList.remove('is-counting');
                counterAnimations.delete(element);
            };

            counterAnimations.set(element, requestAnimationFrame(frame));
        }

        function initializeDashboardCounters() {
            document.querySelectorAll('[data-count-target]').forEach((element, index) => {
                const target = Number(element.dataset.countTarget) || 0;

                if (prefersReducedMotion.matches) {
                    renderCounterValue(element, target);
                    element.dataset.countCurrent = String(target);
                    return;
                }

                element.dataset.countCurrent = '0';
                renderCounterValue(element, 0);
                requestAnimationFrame(() => animateCounter(element, target, 850 + (index * 55)));
            });
        }

        function esc(s) {
            return String(s ?? '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildOnlineRow(u) {
            const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=80&background=64748b&color=fff&bold=true`;
            return `
                <tr>
                    <td>
                        <div class="simansa-online-identity">
                            <span class="simansa-online-avatar-wrap">
                                <img src="${esc(u.photo)}" alt="${esc(u.name)}" class="simansa-online-avatar"
                                     onerror="this.onerror=null;this.src='${fallback}'">
                                <span class="simansa-online-dot"></span>
                            </span>
                            <span class="simansa-online-name" title="${esc(u.name)}">${esc(u.name)}</span>
                        </div>
                    </td>
                    <td><span class="simansa-online-role is-${esc(u.role_group)}">${esc(u.role)}</span></td>
                    <td>
                        <div class="simansa-online-device">
                            <span class="simansa-online-device__icons">
                                <i class="${esc(u.device_icon)}"></i>
                                <i class="${esc(u.browser_icon)}"></i>
                            </span>
                            <span>
                                <strong>${esc(u.device)}</strong>
                                <small>${esc(u.device_details)}</small>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="simansa-online-time">
                            <strong>${esc(u.last_activity)}</strong>
                            <small>${esc(u.last_activity_time)} WIB</small>
                        </div>
                    </td>
                </tr>`;
        }

        function renderOnlineTable(body, users, emptyElement) {
            if (!body) return;
            body.innerHTML = users.map(buildOnlineRow).join('');
            const wrap = body.closest('.simansa-online-table-wrap');

            if (users.length === 0) {
                wrap?.classList.add('d-none');
                emptyElement?.classList.remove('d-none');
            } else {
                wrap?.classList.remove('d-none');
                emptyElement?.classList.add('d-none');
            }
        }

        function renderOnlineSummary(summary = {}) {
            ['all', 'siswa', 'gtk', 'staff'].forEach(key => {
                const element = document.getElementById(`online-summary-${key}`);
                if (element) element.textContent = counterFormatter.format(Number(summary[key]) || 0);
            });
        }

        const onlineModalState = {
            page: 1,
            lastPage: 1,
            search: '',
            role: '',
            timer: null,
        };

        function onlineRequest(params = {}) {
            const url = new URL(onlineApiUrl, window.location.origin);
            Object.entries(params).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) {
                    url.searchParams.set(key, value);
                }
            });

            return fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            }).then(response => {
                if (!response.ok) throw new Error('Gagal memuat pengguna online.');
                return response.json();
            });
        }

        function setOnlineLoading(button, loading) {
            if (!button) return;
            button.classList.toggle('spinning', loading);
            button.disabled = loading;
        }

        function loadOnlineUsers() {
            const button = document.getElementById('btn-refresh-online');
            setOnlineLoading(button, true);

            return onlineRequest({ per_page: 8, page: 1 })
                .then(data => {
                    const users = data.users || [];
                    renderOnlineTable(
                        document.getElementById('online-users-table-body'),
                        users,
                        document.getElementById('online-empty')
                    );
                    renderOnlineSummary(data.summary);

                    const updated = document.getElementById('online-updated-at');
                    const statEl = document.getElementById('stat-online-count');
                    const caption = document.getElementById('online-list-caption');
                    if (updated) updated.textContent = data.updated_at || '—';
                    if (statEl) animateCounter(statEl, data.total || 0, 450);
                    if (caption) {
                        const shown = data.pagination?.to || 0;
                        caption.textContent = shown
                            ? `Menampilkan ${shown} dari ${counterFormatter.format(data.total || 0)} pengguna yang paling baru aktif.`
                            : 'Belum ada pengguna yang aktif dalam lima menit terakhir.';
                    }
                })
                .catch(() => {
                    const caption = document.getElementById('online-list-caption');
                    if (caption) caption.textContent = 'Data online belum dapat diperbarui. Silakan coba kembali.';
                })
                .finally(() => setOnlineLoading(button, false));
        }

        function loadOnlineModal(page = onlineModalState.page) {
            const button = document.getElementById('btn-refresh-online-modal');
            const body = document.getElementById('online-modal-table-body');
            const result = document.getElementById('online-modal-result');
            setOnlineLoading(button, true);
            onlineModalState.page = page;
            if (result) result.textContent = 'Memuat data pengguna...';

            return onlineRequest({
                per_page: 20,
                page,
                search: onlineModalState.search,
                role: onlineModalState.role,
            })
                .then(data => {
                    const users = data.users || [];
                    const pagination = data.pagination || {};
                    onlineModalState.page = pagination.current_page || 1;
                    onlineModalState.lastPage = pagination.last_page || 1;
                    renderOnlineTable(body, users, document.getElementById('online-modal-empty'));

                    if (result) {
                        const from = pagination.from || 0;
                        const to = pagination.to || 0;
                        result.textContent = pagination.filtered_total
                            ? `Menampilkan ${from}–${to} dari ${counterFormatter.format(pagination.filtered_total)} pengguna.`
                            : 'Tidak ada pengguna yang sesuai dengan pencarian.';
                    }

                    const pageInfo = document.getElementById('online-modal-page-info');
                    const previous = document.getElementById('online-page-prev');
                    const next = document.getElementById('online-page-next');
                    if (pageInfo) pageInfo.textContent = `Halaman ${onlineModalState.page} dari ${onlineModalState.lastPage}`;
                    if (previous) previous.disabled = onlineModalState.page <= 1;
                    if (next) next.disabled = onlineModalState.page >= onlineModalState.lastPage;
                })
                .catch(() => {
                    if (result) result.textContent = 'Data tidak dapat dimuat. Silakan coba kembali.';
                })
                .finally(() => setOnlineLoading(button, false));
        }

        function debounceOnlineSearch() {
            window.clearTimeout(onlineModalState.timer);
            onlineModalState.timer = window.setTimeout(() => {
                onlineModalState.search = document.getElementById('online-search')?.value.trim() || '';
                loadOnlineModal(1);
            }, 350);
        }

        document.addEventListener('DOMContentLoaded', function () {
            initializeDashboardCounters();
            loadOnlineUsers();
            window.setInterval(() => {
                loadOnlineUsers();
                if ($('#onlineUsersModal').hasClass('show')) loadOnlineModal();
            }, 15000);

            document.getElementById('btn-refresh-online')?.addEventListener('click', loadOnlineUsers);
            document.getElementById('btn-refresh-online-modal')?.addEventListener('click', () => loadOnlineModal());
            document.getElementById('online-search')?.addEventListener('input', debounceOnlineSearch);
            document.getElementById('online-role-filter')?.addEventListener('change', event => {
                onlineModalState.role = event.target.value;
                loadOnlineModal(1);
            });
            document.getElementById('online-page-prev')?.addEventListener('click', () => {
                if (onlineModalState.page > 1) loadOnlineModal(onlineModalState.page - 1);
            });
            document.getElementById('online-page-next')?.addEventListener('click', () => {
                if (onlineModalState.page < onlineModalState.lastPage) loadOnlineModal(onlineModalState.page + 1);
            });

            $('#onlineUsersModal').on('shown.bs.modal', () => {
                loadOnlineModal(1);
                document.getElementById('online-search')?.focus();
            });
            $('#onlineUsersModal').on('hidden.bs.modal', () => {
                onlineModalState.page = 1;
                onlineModalState.search = '';
                onlineModalState.role = '';
                const search = document.getElementById('online-search');
                const role = document.getElementById('online-role-filter');
                if (search) search.value = '';
                if (role) role.value = '';
            });
        });
    </script>
@endsection
