@extends('adminlte::page')

@section('title', 'Hotspot Manager - SIMANSA')

@section('css')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
/* ── Hero Card ─────────────────────────────────── */
.hs-hero {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, rgba(14,165,233,.9), rgba(99,102,241,.85));
    border-radius: 20px;
    padding: 1.1rem 1.3rem;
    color: #fff;
    margin-bottom: .8rem;
    box-shadow: 0 8px 24px rgba(14,165,233,.25);
}
.hs-hero__eyebrow {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .07em;
    opacity: .85;
    text-transform: uppercase;
    margin-bottom: .3rem;
}
.hs-hero__title { font-size: 1.35rem; font-weight: 800; margin: 0; }
.hs-hero__sub { font-size: .8rem; opacity: .82; margin-top: .25rem; }
.hs-hero__actions { display: flex; flex-direction: column; gap: .45rem; }
.hs-radius-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .7rem; border-radius: 20px; font-size: .75rem; font-weight: 700;
}
.hs-radius-badge.ok  { background: rgba(34,197,94,.2); border: 1px solid rgba(34,197,94,.5); }
.hs-radius-badge.err { background: rgba(239,68,68,.2); border: 1px solid rgba(239,68,68,.5); }
.hs-radius-badge .dot { width: 8px; height: 8px; border-radius: 50%; }
.hs-radius-badge.ok  .dot { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
.hs-radius-badge.err .dot { background: #ef4444; box-shadow: 0 0 6px #ef4444; }

/* ── Stat Cards ────────────────────────────────── */
.hs-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: .55rem;
    margin-bottom: .8rem;
}
.hs-stat {
    background: #fff;
    border-radius: 14px;
    padding: .75rem 1rem;
    display: flex; flex-direction: column; gap: .2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    border: 1px solid #e2e8f0;
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.hs-stat:hover { box-shadow: 0 6px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
.hs-stat__icon { font-size: 1.2rem; margin-bottom: .1rem; }
.hs-stat__val { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.hs-stat__label { font-size: .68rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
.hs-stat--guru .hs-stat__val   { color: #2563eb; }
.hs-stat--siswa .hs-stat__val  { color: #0891b2; }
.hs-stat--tamu .hs-stat__val   { color: #d97706; }
.hs-stat--online .hs-stat__val { color: #16a34a; }
.hs-stat--err .hs-stat__val    { color: #dc2626; }
.hs-stat--pend .hs-stat__val   { color: #9333ea; }

/* ── Panel ─────────────────────────────────────── */
.hs-panel {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.hs-panel__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .8rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    flex-wrap: wrap; gap: .5rem;
}
.hs-panel__title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.hs-panel__body { padding: .8rem 1rem; }

/* ── Filter Bar ────────────────────────────────── */
.hs-filter-bar {
    display: flex; gap: .5rem; flex-wrap: wrap; align-items: center;
    margin-bottom: .7rem;
}
.hs-filter-bar .btn { font-size: .75rem; padding: .3rem .75rem; border-radius: 20px; }
.hs-filter-bar .btn.active { font-weight: 700; }

/* ── Online pulse ──────────────────────────────── */
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.pulse { animation: pulse 1.5s infinite; }

/* ── Sync progress overlay ─────────────────────── */
#syncOverlay {
    display: none;
    position: fixed; inset: 0; background: rgba(15,23,42,.6);
    z-index: 9999; align-items: center; justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(3px);
}
#syncOverlay.show { display: flex; }
.sync-modal {
    background: #fff; border-radius: 20px;
    max-width: 500px; width: 100%;
    box-shadow: 0 32px 80px rgba(0,0,0,.3);
    display: flex; flex-direction: column;
    max-height: 90vh; overflow: hidden;
}
.sync-modal__header {
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.sync-modal__header-title {
    font-size: .88rem; font-weight: 700; color: #1e293b;
    display: flex; align-items: center; gap: .5rem;
}
.sync-modal__body {
    padding: 1.75rem 2rem;
    text-align: center;
    overflow-y: auto;
    flex: 1;
}
.sync-modal__footer {
    padding: 1rem 1.4rem;
    border-top: 1px solid #e2e8f0;
    display: flex; justify-content: flex-end; gap: .5rem;
    flex-shrink: 0;
    background: #f8fafc;
    border-radius: 0 0 20px 20px;
}
/* Running state */
.sync-spinner-wrap {
    width: 64px; height: 64px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 8px 24px rgba(37,99,235,.35);
}
.sync-spinner-wrap i { font-size: 1.6rem; color: #fff; }
@keyframes spin { to { transform: rotate(360deg); } }
/* Done state */
.sync-result-icon {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem; line-height: 1;
}
.sync-result-icon.ok  { background: #dcfce7; box-shadow: 0 8px 24px rgba(34,197,94,.25); }
.sync-result-icon.err { background: #fef9c3; box-shadow: 0 8px 24px rgba(234,179,8,.25); }
.sync-result-icon.fail{ background: #fee2e2; box-shadow: 0 8px 24px rgba(239,68,68,.25); }
.sync-counts {
    display: flex; justify-content: center; gap: 0;
    border: 1px solid #e2e8f0; border-radius: 12px;
    overflow: hidden; margin: 1.25rem 0 0;
}
.sync-count-item {
    flex: 1; padding: .6rem .25rem; text-align: center;
    border-right: 1px solid #e2e8f0;
}
.sync-count-item:last-child { border-right: none; }
.sync-count-item__val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.sync-count-item__label { font-size: .62rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #64748b; margin-top: .15rem; }
.sync-output {
    text-align: left; background: #0f172a; color: #94a3b8;
    border-radius: 10px; padding: .7rem 1rem; font-family: monospace;
    font-size: .72rem; max-height: 180px; overflow-y: auto; margin-top: 1rem;
    display: none;
}
.sync-output-toggle {
    font-size: .75rem; color: #64748b; cursor: pointer; margin-top: .6rem;
    display: inline-flex; align-items: center; gap: .3rem;
    border: none; background: none; padding: 0;
}
.sync-output-toggle:hover { color: #2563eb; }

/* ── RADIUS live panel ─────────────────────────── */
.radius-live {
    background: #0f172a; border-radius: 14px; padding: 1rem;
    color: #94a3b8; font-size: .78rem; font-family: monospace;
}
.radius-live__row { display: flex; justify-content: space-between; border-bottom: 1px solid #1e293b; padding: .3rem 0; }
.radius-live__row:last-child { border-bottom: none; }
.radius-live__val { color: #38bdf8; font-weight: 700; }
.radius-auth-row { padding: .25rem 0; border-bottom: 1px solid #1e293b; }
.radius-auth-row .accept { color: #4ade80; }
.radius-auth-row .reject { color: #f87171; }

/* ── Tamu form ─────────────────────────────────── */
.tamu-password-toggle { cursor: pointer; }

.net-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
    gap: 1rem;
    margin-bottom: 1rem;
}
.net-card {
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    overflow: hidden;
}
.net-card__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding: .95rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #f8fbff, #eef6ff);
}
.net-card__title {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 800;
}
.net-card__sub {
    margin: .15rem 0 0;
    color: #64748b;
    font-size: .78rem;
}
.net-card__body { padding: 1rem 1.1rem; }
.net-kv {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .65rem;
}
.net-kv__item {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .7rem .8rem;
    background: #f8fafc;
}
.net-kv__label {
    color: #64748b;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.net-kv__value {
    color: #0f172a;
    font-size: .95rem;
    font-weight: 800;
    word-break: break-word;
    margin-top: .2rem;
}
.net-terminal {
    background: #07111f;
    color: #b7c9dd;
    border-radius: 14px;
    padding: .85rem 1rem;
    font-family: Consolas, Monaco, monospace;
    font-size: .74rem;
    line-height: 1.55;
    overflow-x: auto;
    border: 1px solid #1e3a5f;
}
.net-terminal .comment { color: #5eead4; }
.profile-list, .nas-list { display: grid; gap: .6rem; }
.net-mini-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: .75rem;
    background: #fff;
}
.net-mini-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
}
.net-chip {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    border-radius: 999px;
    padding: .18rem .5rem;
    font-size: .68rem;
    font-weight: 800;
    background: #e0f2fe;
    color: #0369a1;
}
.net-chip.green { background: #dcfce7; color: #166534; }
.net-chip.warn { background: #fef3c7; color: #92400e; }
.net-action-row { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .55rem; }
.nas-sync-log {
    text-align: left;
    background: #07111f;
    color: #b7c9dd;
    border-radius: 12px;
    padding: .85rem 1rem;
    font-family: Consolas, Monaco, monospace;
    font-size: .78rem;
    line-height: 1.55;
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #1e3a5f;
}
.nas-sync-log__row {
    display: flex;
    gap: .55rem;
    padding: .2rem 0;
    border-bottom: 1px solid rgba(148, 163, 184, .12);
}
.nas-sync-log__row:last-child { border-bottom: none; }
.nas-sync-log__icon { width: 18px; flex: 0 0 18px; }
.nas-sync-log__row.running .nas-sync-log__icon { color: #38bdf8; }
.nas-sync-log__row.success .nas-sync-log__icon { color: #4ade80; }
.nas-sync-log__row.error .nas-sync-log__icon { color: #f87171; }
@media (max-width: 991.98px) {
    .net-grid { grid-template-columns: 1fr; }
    .net-kv { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 575.98px) {
    .net-kv { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content_header')
<div class="hs-hero">
    <div>
        <div class="hs-hero__eyebrow"><i class="fas fa-wifi mr-1"></i>Manajemen Hotspot Sekolah</div>
        <h1 class="hs-hero__title">Hotspot Manager</h1>
        <p class="hs-hero__sub mb-0">Kelola akun WiFi siswa, guru &amp; tamu terintegrasi dengan FreeRADIUS</p>
    </div>
    <div class="hs-hero__actions">
        @if($radiusConnected === true)
            <span class="hs-radius-badge ok">
                <span class="dot"></span> FreeRADIUS Terhubung
            </span>
        @elseif($radiusConnected === false)
            <span class="hs-radius-badge err">
                <span class="dot"></span> FreeRADIUS Offline
            </span>
        @else
            <span class="hs-radius-badge" style="background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);">
                <span class="dot" style="background:#fbbf24;box-shadow:0 0 6px #fbbf24;"></span> Status RADIUS dicek otomatis
            </span>
        @endif
        <div class="d-flex gap-2">
            <button class="btn btn-light btn-sm" id="btnSyncAll">
                <i class="fas fa-sync mr-1"></i> Sync Semua
            </button>
            <button class="btn btn-warning btn-sm" id="btnTambahTamu">
                <i class="fas fa-user-plus mr-1"></i> + Tamu
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')

{{-- Stats ---------------------------------------------------------------- --}}
<div class="hs-stats">
    <a href="{{ route('admin.hotspot.index') }}?role=guru" class="hs-stat hs-stat--guru">
        <div class="hs-stat__icon">👨‍🏫</div>
        <div class="hs-stat__val" id="statGuru">{{ $stats['guru'] }}</div>
        <div class="hs-stat__label">Guru/GTK</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?role=siswa" class="hs-stat hs-stat--siswa">
        <div class="hs-stat__icon">👨‍🎓</div>
        <div class="hs-stat__val" id="statSiswa">{{ $stats['siswa'] }}</div>
        <div class="hs-stat__label">Siswa</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?role=tamu" class="hs-stat hs-stat--tamu">
        <div class="hs-stat__icon">🧑‍💼</div>
        <div class="hs-stat__val" id="statTamu">{{ $stats['tamu'] }}</div>
        <div class="hs-stat__label">Tamu</div>
    </a>
    <a href="{{ route('admin.hotspot.online') }}" class="hs-stat hs-stat--online">
        <div class="hs-stat__icon"><span class="pulse">🟢</span></div>
        <div class="hs-stat__val" id="statOnline">{{ $stats['online'] ?? '--' }}</div>
        <div class="hs-stat__label">Online Sekarang</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?sync_status=error" class="hs-stat hs-stat--err">
        <div class="hs-stat__icon">⚠️</div>
        <div class="hs-stat__val" id="statError">{{ $stats['error_sync'] }}</div>
        <div class="hs-stat__label">Error Sync</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?sync_status=pending" class="hs-stat hs-stat--pend">
        <div class="hs-stat__icon">⏳</div>
        <div class="hs-stat__val" id="statPending">{{ $stats['pending_sync'] }}</div>
        <div class="hs-stat__label">Pending Sync</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?is_active=1" class="hs-stat">
        <div class="hs-stat__icon">✅</div>
        <div class="hs-stat__val" id="statAktif">{{ $stats['aktif'] }}</div>
        <div class="hs-stat__label">Akun Aktif</div>
    </a>
    <a href="{{ route('admin.hotspot.index') }}?is_active=0" class="hs-stat">
        <div class="hs-stat__icon">🚫</div>
        <div class="hs-stat__val" id="statNonaktif">{{ $stats['nonaktif'] }}</div>
        <div class="hs-stat__label">Nonaktif</div>
    </a>
</div>

{{-- Network Operations --------------------------------------------------- --}}
<div class="net-grid">
    <div class="net-card">
        <div class="net-card__head">
            <div>
                <h2 class="net-card__title"><i class="fas fa-network-wired text-primary mr-1"></i>Detail Server FreeRADIUS</h2>
                <p class="net-card__sub">Informasi inti untuk menghubungkan MikroTik Hotspot ke RADIUS SIMANSA.</p>
            </div>
            <button class="btn btn-outline-primary btn-sm" id="btnCopyMikrotik">
                <i class="fas fa-copy mr-1"></i>Copy Script
            </button>
        </div>
        <div class="net-card__body">
            <div class="net-kv mb-3">
                <div class="net-kv__item">
                    <div class="net-kv__label">RADIUS Host</div>
                    <div class="net-kv__value">{{ $serverInfo['host'] }}</div>
                </div>
                <div class="net-kv__item">
                    <div class="net-kv__label">Auth / Accounting</div>
                    <div class="net-kv__value">{{ $serverInfo['auth_port'] }} / {{ $serverInfo['acct_port'] }}</div>
                </div>
                <div class="net-kv__item">
                    <div class="net-kv__label">CoA</div>
                    <div class="net-kv__value">{{ $serverInfo['coa_port'] }}</div>
                </div>
                <div class="net-kv__item">
                    <div class="net-kv__label">DB Radius</div>
                    <div class="net-kv__value">{{ $serverInfo['database'] }}:{{ $serverInfo['database_port'] }}</div>
                </div>
                <div class="net-kv__item">
                    <div class="net-kv__label">Shared Secret</div>
                    <div class="net-kv__value">{{ $serverInfo['shared_secret_hint'] }}</div>
                </div>
                <div class="net-kv__item">
                    <div class="net-kv__label">Firewall</div>
                    <div class="net-kv__value">UDP 1812/1813</div>
                </div>
            </div>
            <div class="net-terminal" id="mikrotikScript">/radius add service=hotspot address={{ $serverInfo['host'] }} secret=&lt;SECRET&gt; authentication-port={{ $serverInfo['auth_port'] }} accounting-port={{ $serverInfo['acct_port'] }} timeout=1000ms
/ip hotspot profile set [find name=&lt;HOTSPOT_PROFILE&gt;] use-radius=yes accounting=yes
/radius incoming set accept=yes port={{ $serverInfo['coa_port'] }}
<span class="comment"># Pastikan MikroTik/NAS sudah didaftarkan di FreeRADIUS dan firewall mengizinkan UDP 1812/1813.</span></div>
        </div>
    </div>

    <div class="net-card">
        <div class="net-card__head">
            <div>
                <h2 class="net-card__title"><i class="fas fa-sliders-h text-info mr-1"></i>Profile RADIUS</h2>
                <p class="net-card__sub">Bandwidth, timeout, pool, dan batas login dikirim sebagai group FreeRADIUS.</p>
            </div>
            <button class="btn btn-primary btn-sm" id="btnAddProfile">
                <i class="fas fa-plus mr-1"></i>Profile
            </button>
        </div>
        <div class="net-card__body profile-list" id="profileList">
            @forelse($profiles as $profile)
                <div class="net-mini-card" data-profile='@json($profile)'>
                    <div class="net-mini-card__top">
                        <div>
                            <strong>{{ $profile->name }}</strong>
                            <div class="small text-muted">{{ $profile->code }}{{ $profile->rate_limit ? ' | '.$profile->rate_limit : '' }}</div>
                        </div>
                        <span class="net-chip {{ $profile->sync_status === 'synced' ? 'green' : 'warn' }}">
                            {{ $profile->is_default ? 'Default ' : '' }}{{ strtoupper($profile->role ?? 'CUSTOM') }}
                        </span>
                    </div>
                    <div class="small text-muted mt-2">{{ $profile->description ?: 'Belum ada catatan profile.' }}</div>
                    <div class="net-action-row">
                        <button class="btn btn-xs btn-outline-primary btn-edit-profile"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-xs btn-outline-info btn-sync-profile" data-id="{{ $profile->id }}"><i class="fas fa-sync"></i> Sync</button>
                        <button class="btn btn-xs btn-outline-danger btn-delete-profile" data-id="{{ $profile->id }}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            @empty
                <div class="text-muted small">Belum ada profile RADIUS.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Main Panel ----------------------------------------------------------- --}}
<div class="row">
    <div class="col-lg-8">
        <div class="hs-panel">
            <div class="hs-panel__header">
                <span class="hs-panel__title"><i class="fas fa-users mr-1 text-primary"></i>Daftar Akun Hotspot</span>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="text" id="searchBox" class="form-control form-control-sm" placeholder="Cari username / nama..." style="width:200px">
                    <select id="filterRole" class="form-control form-control-sm" style="width:110px">
                        <option value="">Semua Role</option>
                        <option value="guru">Guru</option>
                        <option value="siswa">Siswa</option>
                        <option value="tamu">Tamu</option>
                    </select>
                    {{-- Filter kelas: tampil saat role=siswa --}}
                    <div id="kelasFilterWrap" style="display:none" class="d-flex gap-2 align-items-center">
                        <select id="filterTingkat" class="form-control form-control-sm" style="width:82px">
                            <option value="">Kelas</option>
                            <option value="10">X</option>
                            <option value="11">XI</option>
                            <option value="12">XII</option>
                        </select>
                        <select id="filterRombel" class="form-control form-control-sm" style="width:145px">
                            <option value="">Semua Rombel</option>
                        </select>
                    </div>
                    <select id="filterSync" class="form-control form-control-sm" style="width:110px">
                        <option value="">Semua Status</option>
                        <option value="synced">Synced</option>
                        <option value="pending">Pending</option>
                        <option value="error">Error</option>
                    </select>
                </div>
            </div>
            <div class="hs-panel__body" style="padding:.5rem">

                {{-- Bulk action bar --}}
                <div id="bulkBar" style="display:none;background:#eff6ff;border-bottom:1px solid #bfdbfe"
                     class="d-flex align-items-center flex-wrap gap-2 px-3 py-2">
                    <i class="fas fa-check-square text-primary"></i>
                    <span class="font-weight-bold text-primary small" id="bulkCount">0 dipilih</span>
                    <div class="d-flex gap-1 ml-2">
                        <button class="btn btn-success btn-sm px-3" id="btnBulkAktif">
                            <i class="fas fa-check mr-1"></i>Aktifkan
                        </button>
                        <button class="btn btn-danger btn-sm px-3" id="btnBulkNonaktif">
                            <i class="fas fa-ban mr-1"></i>Nonaktifkan
                        </button>
                        <select id="bulkProfileId" class="form-control form-control-sm" style="width:190px">
                            <option value="">Profile default role</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-info btn-sm px-3" id="btnBulkProfile">
                            <i class="fas fa-layer-group mr-1"></i>Set Profile
                        </button>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm ml-auto" id="btnBulkClear" title="Batal pilih semua">
                        <i class="fas fa-times mr-1"></i>Batal Pilih
                    </button>
                </div>

                <div class="hs-filter-bar" style="padding:.3rem .5rem 0">
                    <button class="btn btn-outline-secondary filter-active active" data-active="">Semua</button>
                    <button class="btn btn-outline-success filter-active" data-active="1">Aktif</button>
                    <button class="btn btn-outline-danger filter-active" data-active="0">Nonaktif</button>
                    <span class="ml-auto">
                        <button class="btn btn-outline-primary btn-sm" id="btnSyncErrors">
                            <i class="fas fa-redo mr-1"></i>Retry Error
                        </button>
                    </span>
                </div>
                <table id="hotspotTable" class="table table-sm table-hover" style="width:100%;font-size:.82rem">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:38px;text-align:center">
                                <input type="checkbox" id="checkAll" title="Pilih semua di halaman ini" style="cursor:pointer">
                            </th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Role</th>
                            <th>Profile</th>
                            <th>Status</th>
                            <th>Sync</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar: RADIUS live + sync actions ─────────────────────────────── --}}
    <div class="col-lg-4">
        {{-- Sync Actions --}}
        <div class="hs-panel mb-3">
            <div class="hs-panel__header">
                <span class="hs-panel__title"><i class="fas fa-sync mr-1 text-info"></i>Sync Control</span>
            </div>
            <div class="hs-panel__body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-block btn-sync-role" data-role="guru">
                        <i class="fas fa-chalkboard-teacher mr-1"></i>Sync Guru/GTK
                    </button>
                    <button class="btn btn-info btn-block btn-sync-role" data-role="siswa">
                        <i class="fas fa-user-graduate mr-1"></i>Sync Siswa
                    </button>
                    <button class="btn btn-outline-secondary btn-block btn-sync-role" data-role="" data-force="1">
                        <i class="fas fa-sync-alt mr-1"></i>Force Sync Semua
                    </button>
                </div>
                <hr class="my-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sync otomatis berjalan setiap malam jam 02:00.<br>
                    Password ikut password Simansa secara real-time.
                </small>
            </div>
        </div>

        {{-- RADIUS Live Status --}}
        <div class="hs-panel">
            <div class="hs-panel__header">
                <span class="hs-panel__title"><i class="fas fa-server mr-1 text-success"></i>RADIUS Live</span>
                <button class="btn btn-xs btn-outline-secondary" id="btnRefreshRadius">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
            <div class="hs-panel__body">
                <div id="radiusStatusPanel">
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa koneksi RADIUS...
                        <div class="small mt-2 text-muted">Data hotspot lokal tetap bisa dipakai walau layanan eksternal lambat atau offline.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hs-panel mt-3">
            <div class="hs-panel__header">
                <span class="hs-panel__title"><i class="fas fa-router mr-1 text-primary"></i>MikroTik / NAS</span>
                <button class="btn btn-xs btn-primary" id="btnAddNas"><i class="fas fa-plus"></i></button>
            </div>
            <div class="hs-panel__body nas-list">
                @forelse($nasList as $nas)
                    <div class="net-mini-card"
                         data-id="{{ $nas->id }}"
                         data-name="{{ e($nas->name) }}"
                         data-nasname="{{ e($nas->nasname) }}"
                         data-shortname="{{ e($nas->shortname) }}"
                         data-type="{{ e($nas->type) }}"
                         data-description="{{ e($nas->description) }}"
                         data-active="{{ $nas->is_active ? 1 : 0 }}">
                        <div class="net-mini-card__top">
                            <div>
                                <strong>{{ $nas->name }}</strong>
                                <div class="small text-muted">{{ $nas->nasname }} | {{ $nas->shortname ?: '-' }}</div>
                            </div>
                            <span class="net-chip {{ $nas->sync_status === 'synced' ? 'green' : 'warn' }}">{{ strtoupper($nas->type) }}</span>
                        </div>
                        <div class="small text-muted mt-2">Secret: {{ $nas->maskedSecret() }}{{ $nas->description ? ' | '.$nas->description : '' }}</div>
                        <div class="net-action-row">
                            <button class="btn btn-xs btn-outline-primary btn-edit-nas"><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn btn-xs btn-outline-info btn-sync-nas" data-id="{{ $nas->id }}"><i class="fas fa-sync"></i> Sync</button>
                            <button class="btn btn-xs btn-outline-danger btn-delete-nas" data-id="{{ $nas->id }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada MikroTik/NAS yang didaftarkan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Sync Overlay --------------------------------------------------------- --}}
<div id="syncOverlay">
    <div class="sync-modal">

        {{-- Header --}}
        <div class="sync-modal__header">
            <div class="sync-modal__header-title">
                <i class="fas fa-wifi text-primary"></i>
                <span id="syncHeaderTitle">Hotspot Sync</span>
            </div>
            <button type="button" id="btnCloseSyncOverlay"
                class="btn btn-sm btn-light"
                style="display:none;border-radius:8px;padding:.25rem .6rem"
                title="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="sync-modal__body">

            {{-- Running state --}}
            <div id="syncStateRunning">
                <div class="sync-spinner-wrap">
                    <i class="fas fa-sync fa-spin"></i>
                </div>
                <h5 class="font-weight-bold mb-1">Sinkronisasi Berjalan...</h5>
                <p class="text-muted small mb-0">Harap tunggu, jangan tutup halaman ini.</p>
            </div>

            {{-- Done state --}}
            <div id="syncStateDone" style="display:none">
                <div id="syncResultIconWrap" class="sync-result-icon">
                    <span id="syncResultEmoji"></span>
                </div>
                <h5 class="font-weight-bold mb-1" id="syncResultTitle"></h5>
                <p class="text-muted small mb-0" id="syncResultSub"></p>

                {{-- Count grid --}}
                <div class="sync-counts" id="syncResultCounts"></div>

                {{-- Log toggle --}}
                <button class="sync-output-toggle" id="btnToggleSyncLog" style="display:none">
                    <i class="fas fa-chevron-down" id="syncLogIcon"></i>
                    <span id="syncLogLabel">Lihat detail log</span>
                </button>
                <div class="sync-output" id="syncOutputDone"></div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="sync-modal__footer" id="syncModalFooter" style="display:none">
            <button type="button" class="btn btn-secondary btn-sm px-3" id="btnCloseSyncOverlay2">
                <i class="fas fa-times mr-1"></i>Tutup
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3" id="btnSyncAgain">
                <i class="fas fa-redo mr-1"></i>Sync Lagi
            </button>
        </div>

    </div>
</div>

{{-- NAS Sync Progress ---------------------------------------------------- --}}
<div class="modal fade" id="modalNasSync" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#0f766e);color:#fff;border-bottom:none">
                <h5 class="modal-title"><i class="fas fa-network-wired mr-2"></i>Sync NAS ke FreeRADIUS</h5>
                <button type="button" class="close text-white nas-sync-close" data-dismiss="modal" style="display:none">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="sync-spinner-wrap mr-3 mb-0" id="nasSyncSpinner" style="width:48px;height:48px">
                        <i class="fas fa-sync fa-spin" style="font-size:1.2rem"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1" id="nasSyncTitle">Menyiapkan sinkronisasi...</h6>
                        <div class="small text-muted" id="nasSyncSub">Log proses akan tampil di bawah.</div>
                    </div>
                </div>
                <div class="nas-sync-log" id="nasSyncLog"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary nas-sync-close" data-dismiss="modal" style="display:none">
                    Tutup
                </button>
                <button type="button" class="btn btn-primary nas-sync-refresh" style="display:none">
                    <i class="fas fa-redo mr-1"></i>Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Profile RADIUS ------------------------------------------------- --}}
<div class="modal fade" id="modalProfile" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#2563eb,#0f766e);color:#fff;border-bottom:none">
                <h5 class="modal-title"><i class="fas fa-sliders-h mr-2"></i>Profile RADIUS</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="formProfile">
                    <input type="hidden" id="profileId">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold small">Nama Profile</label>
                                <input type="text" id="profileName" class="form-control" placeholder="Siswa 5M, Guru, Tamu">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Kode Group</label>
                                <input type="text" id="profileCode" class="form-control" placeholder="siswa-5m">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Role</label>
                                <select id="profileRole" class="form-control">
                                    <option value="">Custom</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                    <option value="tamu">Tamu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Prioritas</label>
                                <input type="number" id="profilePriority" class="form-control" value="1" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small">Mikrotik-Rate-Limit</label>
                                <input type="text" id="profileRateLimit" class="form-control" placeholder="5M/5M atau 2M/2M 5M/5M...">
                                <small class="text-muted">Format mengikuti atribut MikroTik.</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Session</label>
                                <input type="number" id="profileSession" class="form-control" placeholder="detik">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Idle</label>
                                <input type="number" id="profileIdle" class="form-control" placeholder="detik">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Login</label>
                                <input type="number" id="profileSimUse" class="form-control" placeholder="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="font-weight-bold small">Aktif</label>
                                <select id="profileActive" class="form-control">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small">Framed-Pool</label>
                                <input type="text" id="profilePool" class="form-control" placeholder="pool-siswa">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small">Address List</label>
                                <input type="text" id="profileAddressList" class="form-control" placeholder="siswa-hotspot">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" class="form-check-input" id="profileDefault">
                                <label class="form-check-label font-weight-bold small" for="profileDefault">Jadikan default untuk role</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Catatan</label>
                        <textarea id="profileDescription" class="form-control" rows="2" placeholder="Keterangan penggunaan profile"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary font-weight-bold" id="btnSaveProfile">
                    <i class="fas fa-save mr-1"></i>Simpan & Sync
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal NAS ------------------------------------------------------------ --}}
<div class="modal fade" id="modalNas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#0f172a);color:#fff;border-bottom:none">
                <h5 class="modal-title"><i class="fas fa-router mr-2"></i>MikroTik / NAS</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="formNas">
                    <input type="hidden" id="nasId">
                    <div class="form-group">
                        <label class="font-weight-bold small">Nama Router</label>
                        <input type="text" id="nasName" class="form-control" placeholder="MikroTik Lab / Gateway Utama">
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="font-weight-bold small">IP / NAS Name</label>
                                <input type="text" id="nasNameIp" class="form-control" placeholder="172.16.250.1">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold small">Shortname</label>
                                <input type="text" id="nasShortname" class="form-control" placeholder="gw-hotspot">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold small">Type</label>
                                <input type="text" id="nasType" class="form-control" value="mikrotik">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="font-weight-bold small">Shared Secret</label>
                                <input type="password" id="nasSecret" class="form-control" placeholder="Isi ulang jika ingin mengganti">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Catatan</label>
                        <textarea id="nasDescription" class="form-control" rows="2" placeholder="Lokasi router, interface, atau catatan firewall"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="nasActive" checked>
                        <label class="form-check-label font-weight-bold small" for="nasActive">Aktif di RADIUS</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary font-weight-bold" id="btnSaveNas">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Tamu ---------------------------------------------------- --}}
<div class="modal fade" id="modalTamu" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border-bottom:none">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Akun Tamu</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="formTamu">
                    <input type="hidden" id="tamuId">
                    <div class="form-group">
                        <label class="font-weight-bold small">Nama Tamu <span class="text-danger">*</span></label>
                        <input type="text" id="tamuNama" class="form-control" placeholder="Nama lengkap tamu" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Keterangan / Keperluan</label>
                        <input type="text" id="tamuKeterangan" class="form-control" placeholder="Misal: Rapat komite, Kunjungan dinas...">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="tamuPassword" class="form-control" placeholder="Min. 4 karakter">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnGenPassword">
                                    <i class="fas fa-random"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Berlaku Hingga</label>
                        <input type="date" id="tamuExpired" class="form-control">
                        <small class="text-muted">Kosongkan = tidak ada batas waktu</small>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="tamuIsActive" checked>
                        <label class="form-check-label font-weight-bold small" for="tamuIsActive">Akun Aktif</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning font-weight-bold" id="btnSaveTamu">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hasil Tamu ----------------------------------------------------- --}}
<div class="modal fade" id="modalTamuResult" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius:18px;overflow:hidden">
            <div class="modal-body p-4">
                <div style="font-size:3rem">🎉</div>
                <h5 class="font-weight-bold mt-2">Akun Tamu Dibuat!</h5>
                <div class="bg-light rounded p-3 mt-3 text-left">
                    <div class="mb-1"><small class="text-muted">Username</small></div>
                    <div class="font-weight-bold text-primary" id="resultUsername" style="font-family:monospace;word-break:break-all"></div>
                    <div class="mb-1 mt-2"><small class="text-muted">Password</small></div>
                    <div class="font-weight-bold text-success" id="resultPassword" style="font-family:monospace"></div>
                </div>
                <small class="text-muted d-block mt-2">Catat password ini, tidak bisa dilihat lagi.</small>
                <button class="btn btn-primary mt-3 btn-block" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
const ROUTES = {
    data:         '{{ route("admin.hotspot.data") }}',
    sync:         '{{ route("admin.hotspot.sync") }}',
    stats:        '{{ route("admin.hotspot.stats") }}',
    radiusStatus: '{{ route("admin.hotspot.radius-status") }}',
    filterOptions:'{{ route("admin.hotspot.filter-options") }}',
    bulkToggle:   '{{ route("admin.hotspot.bulk-toggle") }}',
    assignProfile:'{{ route("admin.hotspot.assign-profile") }}',
    profileStore: '{{ route("admin.hotspot.profiles.store") }}',
    profileUpdate:(id) => `{{ url("admin/hotspot/profiles") }}/${id}`,
    profileSync:  (id) => `{{ url("admin/hotspot/profiles") }}/${id}/sync`,
    profileDestroy:(id) => `{{ url("admin/hotspot/profiles") }}/${id}`,
    nasStore:     '{{ route("admin.hotspot.nas.store") }}',
    nasUpdate:    (id) => `{{ url("admin/hotspot/nas") }}/${id}`,
    nasSync:      (id) => `{{ url("admin/hotspot/nas") }}/${id}/sync`,
    nasDestroy:   (id) => `{{ url("admin/hotspot/nas") }}/${id}`,
    tamuStore:    '{{ route("admin.hotspot.tamu.store") }}',
    tamuUpdate:   (id) => `{{ url("admin/hotspot/tamu") }}/${id}`,
    tamuDestroy:  (id) => `{{ url("admin/hotspot/tamu") }}/${id}`,
    syncSingle:   (id) => `{{ url("admin/hotspot/sync") }}/${id}`,
    toggleActive: (id) => `{{ url("admin/hotspot") }}/${id}/toggle-active`,
};

// ── DataTable ─────────────────────────────────────────────────────────────
let table;
$(function () {
    table = $('#hotspotTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ROUTES.data,
            data: d => {
                d.role        = $('#filterRole').val();
                d.sync_status = $('#filterSync').val();
                d.is_active   = activeFilter;
                d.search      = $('#searchBox').val();
                d.tingkat     = $('#filterTingkat').val();
                d.kelas_id    = $('#filterRombel').val();
            }
        },
        columns: [
            {
                data: 'id',
                render: (data) =>
                    `<div style="text-align:center"><input type="checkbox" class="row-check" data-id="${data}" style="cursor:pointer"></div>`,
                orderable: false, searchable: false, width: 38, name: 'id'
            },
            { data: 'username',     name: 'username' },
            { data: 'display_name', name: 'display_name' },
            { data: 'kelas_info',   name: 'kelas_info', orderable: false, searchable: false },
            { data: 'role_badge',   name: 'role', orderable: false },
            { data: 'profile_badge', name: 'profile_badge', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'sync_badge',   name: 'sync_status', orderable: false },
            { data: 'actions',      name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        pageLength: 20,
        order: [[1, 'asc']],
        dom: 'tip',
    });

    // Restore checkbox state setelah setiap draw
    table.on('draw.dt', function () {
        $('.row-check').each(function () {
            $(this).prop('checked', selectedIds.has(parseInt($(this).data('id'))));
        });
        const total   = $('.row-check').length;
        const checked = $('.row-check:checked').length;
        $('#checkAll')
            .prop('checked', total > 0 && checked === total)
            .prop('indeterminate', checked > 0 && checked < total);
        updateBulkBar();
    });

    // Filter bindings
    $('#filterSync').on('change', () => table.ajax.reload());
    $('#filterRombel').on('change', () => table.ajax.reload());
    $('#filterTingkat').on('change', function () {
        populateRombel($(this).val());
        $('#filterRombel').val('');
        table.ajax.reload();
    });
    $('#filterRole').on('change', function () {
        const isSiswa = $(this).val() === 'siswa';
        if (isSiswa) {
            $('#kelasFilterWrap').show();
        } else {
            $('#kelasFilterWrap').hide();
            $('#filterTingkat').val('');
            $('#filterRombel').html('<option value="">Semua Rombel</option>').val('');
        }
        table.ajax.reload();
    });
    $('#searchBox').on('keyup', debounce(() => table.ajax.reload(), 400));

    // Load kelas options
    loadFilterOptions();

    // ── Pre-apply filters from URL params (clicked from stat cards) ────────
    const _p = new URLSearchParams(window.location.search);
    if (_p.get('role')) {
        $('#filterRole').val(_p.get('role'));
        if (_p.get('role') === 'siswa') $('#kelasFilterWrap').show();
    }
    if (_p.get('sync_status')) {
        $('#filterSync').val(_p.get('sync_status'));
    }
    if (_p.get('is_active') !== null && _p.get('is_active') !== '') {
        activeFilter = _p.get('is_active');
        $('.filter-active').removeClass('active');
        $(`.filter-active[data-active="${activeFilter}"]`).addClass('active');
    }
    if (_p.has('role') || _p.has('sync_status') || _p.has('is_active')) {
        table.ajax.reload();
    }
});

// ── Active filter buttons ─────────────────────────────────────────────────
let activeFilter = '';
$('.filter-active').on('click', function () {
    $('.filter-active').removeClass('active');
    $(this).addClass('active');
    activeFilter = $(this).data('active');
    table.ajax.reload();
});

// ── Filter options (tingkat / rombel) ─────────────────────────────────────
let _allKelas = [];

function loadFilterOptions() {
    $.get(ROUTES.filterOptions).done(data => {
        _allKelas = data.kelas || [];
        populateRombel('');
    });
}

function populateRombel(tingkat) {
    const $s = $('#filterRombel');
    $s.html('<option value="">Semua Rombel</option>');
    const list = tingkat ? _allKelas.filter(k => String(k.tingkat) === String(tingkat)) : _allKelas;
    list.forEach(k => $s.append(`<option value="${k.id}">${k.nama}</option>`));
}

// ── Bulk select ───────────────────────────────────────────────────────────
let selectedIds = new Set();

function updateBulkBar() {
    const n = selectedIds.size;
    $('#bulkCount').text(n + ' akun dipilih');
    if (n > 0) { $('#bulkBar').slideDown(150); }
    else       { $('#bulkBar').slideUp(150); }
}

// Header checkAll
$('#hotspotTable thead').on('change', '#checkAll', function () {
    const chk = this.checked;
    $('.row-check').each(function () {
        this.checked = chk;
        const id = parseInt($(this).data('id'));
        if (chk) selectedIds.add(id); else selectedIds.delete(id);
    });
    updateBulkBar();
});

// Row checkbox
$(document).on('change', '.row-check', function () {
    const id = parseInt($(this).data('id'));
    if (this.checked) selectedIds.add(id); else selectedIds.delete(id);
    const total   = $('.row-check').length;
    const checked = $('.row-check:checked').length;
    $('#checkAll')
        .prop('checked', total > 0 && checked === total)
        .prop('indeterminate', checked > 0 && checked < total);
    updateBulkBar();
});

$('#btnBulkClear').on('click', () => {
    selectedIds.clear();
    $('.row-check').prop('checked', false);
    $('#checkAll').prop('checked', false).prop('indeterminate', false);
    updateBulkBar();
});

function doBulkToggle(action) {
    const ids   = Array.from(selectedIds);
    const label = action === 'aktif' ? 'aktifkan' : 'nonaktifkan';
    if (!confirm(`${ids.length} akun akan di-${label}. Lanjutkan?`)) return;

    $.post(ROUTES.bulkToggle, { ids, action, _token: '{{ csrf_token() }}' })
        .done(r => {
            toastr[r.success ? 'success' : 'error'](r.message);
            if (r.success) {
                selectedIds.clear();
                $('#checkAll').prop('checked', false).prop('indeterminate', false);
                updateBulkBar();
                table.ajax.reload();
                if (r.stats) updateStatCards(r.stats);
            }
        })
        .fail(() => toastr.error('Gagal. Coba lagi.'));
}

$('#btnBulkAktif').on('click', () => doBulkToggle('aktif'));
$('#btnBulkNonaktif').on('click', () => doBulkToggle('nonaktif'));

$('#btnBulkProfile').on('click', () => {
    const ids = Array.from(selectedIds);
    if (!ids.length) {
        toastr.warning('Pilih akun terlebih dahulu.');
        return;
    }

    const profileId = $('#bulkProfileId').val();
    if (!confirm(`${ids.length} akun akan dipindahkan ke profile yang dipilih. Lanjutkan?`)) return;

    $.post(ROUTES.assignProfile, {
        ids,
        profile_id: profileId,
        _token: '{{ csrf_token() }}'
    }).done(r => {
        toastr[r.success ? 'success' : 'error'](r.message);
        if (r.success) {
            selectedIds.clear();
            $('#checkAll').prop('checked', false).prop('indeterminate', false);
            updateBulkBar();
            table.ajax.reload();
            loadRadiusStatus();
        }
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal mengubah profile.'));
});

$('#btnCopyMikrotik').on('click', () => {
    const text = $('#mikrotikScript').text().trim();
    navigator.clipboard?.writeText(text);
    toastr.success('Script MikroTik disalin.');
});

// ── Sync overlay ──────────────────────────────────────────────────────────
let lastSyncRole = '', lastSyncForce = false;

function closeSyncOverlay() {
    $('#syncOverlay').removeClass('show');
}

function doSync(role = '', force = false) {
    lastSyncRole  = role;
    lastSyncForce = force;

    // Reset ke running state
    $('#syncHeaderTitle').text('Hotspot Sync');
    $('#syncStateRunning').show();
    $('#syncStateDone').hide();
    $('#syncModalFooter').hide();
    $('#btnCloseSyncOverlay').hide();
    $('#syncOutputDone').hide().html('');
    $('#syncResultCounts').html('');
    $('#btnToggleSyncLog').hide();
    $('#syncOverlay').addClass('show');

    $.post(ROUTES.sync, { role, force: force ? 1 : 0, _token: '{{ csrf_token() }}' })
        .done(r => {
            const c = r.counts || {};
            const hasError = (c.errors || 0) > 0;
            const total = (c.created || 0) + (c.updated || 0);

            // Icon + warna
            const iconWrap = $('#syncResultIconWrap');
            iconWrap.removeClass('ok err fail');
            if (hasError) {
                iconWrap.addClass('err');
                $('#syncResultEmoji').text('⚠️');
            } else {
                iconWrap.addClass('ok');
                $('#syncResultEmoji').text('✅');
            }

            $('#syncHeaderTitle').text(hasError ? 'Selesai dengan Error' : 'Sync Berhasil');
            $('#syncResultTitle').text(hasError ? 'Sync Selesai dengan Error' : 'Sinkronisasi Berhasil!');
            $('#syncResultSub').text(hasError
                ? `${c.errors} akun gagal disync ke RADIUS.`
                : total > 0
                    ? `${total} akun berhasil disinkronkan ke FreeRADIUS.`
                    : `Semua akun sudah up-to-date di FreeRADIUS.`);

            // Count grid
            const items = [
                { label: 'Dibuat',      val: c.created     || 0, color: '#16a34a' },
                { label: 'Diperbarui',  val: c.updated     || 0, color: '#0891b2' },
                { label: 'Nonaktifkan', val: c.deactivated || 0, color: '#d97706' },
                { label: 'Error',       val: c.errors      || 0, color: hasError ? '#dc2626' : '#94a3b8' },
            ];
            $('#syncResultCounts').html(
                items.map(i => `
                    <div class="sync-count-item">
                        <div class="sync-count-item__val" style="color:${i.color}">${i.val}</div>
                        <div class="sync-count-item__label">${i.label}</div>
                    </div>`).join('')
            );

            // Log toggle
            if (r.output) {
                $('#syncOutputDone').html(r.output);
                $('#btnToggleSyncLog').show();
                $('#syncLogIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('#syncLogLabel').text('Lihat detail log');
                $('#syncOutputDone').hide();
            }

            $('#syncStateRunning').hide();
            $('#syncStateDone').show();

            // Update stat cards
            if (r.stats) updateStatCards(r.stats);
            table.ajax.reload();
            loadRadiusStatus();
        })
        .fail(() => {
            $('#syncResultIconWrap').removeClass('ok err').addClass('fail');
            $('#syncResultEmoji').text('❌');
            $('#syncHeaderTitle').text('Sync Gagal');
            $('#syncResultTitle').text('Terjadi Kesalahan!');
            $('#syncResultSub').text('Gagal menghubungi server. Cek koneksi dan coba lagi.');
            $('#syncResultCounts').html('');
            $('#btnToggleSyncLog').hide();
            $('#syncStateRunning').hide();
            $('#syncStateDone').show();
        })
        .always(() => {
            $('#btnCloseSyncOverlay').show();
            $('#syncModalFooter').show();
        });
}

// Log toggle
$('#btnToggleSyncLog').on('click', function () {
    const out  = $('#syncOutputDone');
    const show = !out.is(':visible');
    out.toggle(show);
    $('#syncLogIcon').toggleClass('fa-chevron-down', !show).toggleClass('fa-chevron-up', show);
    $('#syncLogLabel').text(show ? 'Sembunyikan log' : 'Lihat detail log');
});

// Close buttons
$('#btnCloseSyncOverlay, #btnCloseSyncOverlay2').on('click', closeSyncOverlay);
$('#btnSyncAgain').on('click', () => doSync(lastSyncRole, lastSyncForce));

// Klik backdrop untuk tutup (hanya saat done)
$('#syncOverlay').on('click', function (e) {
    if (e.target === this && $('#syncStateDone').is(':visible')) closeSyncOverlay();
});

// Binding tombol sync
$('#btnSyncAll').on('click', () => doSync());
$('.btn-sync-role').on('click', function () {
    doSync($(this).data('role'), $(this).data('force') == 1);
});
$('#btnSyncErrors').on('click', () => doSync('', true));

function updateStatCards(s) {
    $('#statGuru').text(s.guru ?? '-');
    $('#statSiswa').text(s.siswa ?? '-');
    $('#statTamu').text(s.tamu ?? '-');
    $('#statAktif').text(s.aktif ?? '-');
    $('#statNonaktif').text(s.nonaktif ?? '-');
    $('#statError').text(s.error_sync ?? '-');
    $('#statPending').text(s.pending_sync ?? '-');
}

// ── Sync single ───────────────────────────────────────────────────────────
$(document).on('click', '.btn-sync-single', function () {
    const id = $(this).data('id');
    $(this).html('<i class="fas fa-spin fa-sync"></i>');
    $.post(ROUTES.syncSingle(id), { _token: '{{ csrf_token() }}' })
        .done(r => {
            toastr[r.success ? 'success' : 'error'](r.message);
            table.ajax.reload();
        })
        .fail(() => toastr.error('Gagal.'))
        .always(() => $(this).html('<i class="fas fa-sync"></i>'));
});

// ── Toggle active ─────────────────────────────────────────────────────────
$(document).on('click', '.btn-toggle-active', function () {
    const id = $(this).data('id');
    if (!confirm('Ubah status akun ini?')) return;
    $.post(ROUTES.toggleActive(id), { _token: '{{ csrf_token() }}' })
        .done(r => {
            toastr[r.success ? 'success' : 'error'](r.message);
            table.ajax.reload();
        });
});

// ── Tamu modal ────────────────────────────────────────────────────────────
$('#btnTambahTamu').on('click', () => {
    $('#tamuId').val('');
    $('#formTamu')[0].reset();
    $('#tamuIsActive').prop('checked', true);
    $('#modalTamu .modal-title').html('<i class="fas fa-user-plus mr-2"></i>Akun Tamu Baru');
    $('#btnSaveTamu').text('Simpan');
    $('#modalTamu').modal('show');
});

$(document).on('click', '.btn-edit-tamu', function () {
    const el = $(this);
    $('#tamuId').val(el.data('id'));
    $('#tamuNama').val(el.data('displayname'));
    $('#tamuKeterangan').val(el.data('keterangan'));
    $('#tamuExpired').val(el.data('expired'));
    $('#tamuPassword').val('');
    $('#tamuIsActive').prop('checked', true);
    $('#modalTamu .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Akun Tamu');
    $('#btnSaveTamu').text('Update');
    $('#modalTamu').modal('show');
});

$('#btnGenPassword').on('click', () => {
    const chars = 'abcdefghijkmnpqrstuvwxyz23456789';
    let pw = '';
    for (let i = 0; i < 8; i++) pw += chars[Math.floor(Math.random() * chars.length)];
    $('#tamuPassword').val(pw);
});

$('#btnSaveTamu').on('click', () => {
    const id = $('#tamuId').val();
    const data = {
        display_name: $('#tamuNama').val(),
        keterangan:   $('#tamuKeterangan').val(),
        password:     $('#tamuPassword').val(),
        expired_at:   $('#tamuExpired').val(),
        is_active:    $('#tamuIsActive').is(':checked') ? 1 : 0,
        _token:       '{{ csrf_token() }}',
    };

    const isEdit = !!id;
    const url    = isEdit ? ROUTES.tamuUpdate(id) : ROUTES.tamuStore;
    const method = isEdit ? 'PUT' : 'POST';

    $.ajax({ url, method, data })
        .done(r => {
            if (r.success) {
                $('#modalTamu').modal('hide');
                table.ajax.reload();
                toastr.success(r.message);
                if (!isEdit) {
                    $('#resultUsername').text(r.username);
                    $('#resultPassword').text(r.password);
                    $('#modalTamuResult').modal('show');
                }
            } else {
                toastr.error(r.message || 'Gagal menyimpan.');
            }
        })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error.'));
});

$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    if (!confirm('Hapus akun tamu ini dari sistem?')) return;
    $.ajax({ url: ROUTES.tamuDestroy(id), method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
        .done(r => {
            toastr[r.success ? 'success' : 'error'](r.message);
            table.ajax.reload();
        });
});

// ── RADIUS Live Status ────────────────────────────────────────────────────
function resetProfileForm() {
    $('#formProfile')[0].reset();
    $('#profileId').val('');
    $('#profilePriority').val(1);
    $('#profileActive').val('1');
    $('#profileDefault').prop('checked', false);
}

$('#btnAddProfile').on('click', () => {
    resetProfileForm();
    $('#modalProfile .modal-title').html('<i class="fas fa-sliders-h mr-2"></i>Profile RADIUS Baru');
    $('#modalProfile').modal('show');
});

$(document).on('click', '.btn-edit-profile', function () {
    const p = $(this).closest('[data-profile]').data('profile');
    resetProfileForm();
    $('#profileId').val(p.id);
    $('#profileName').val(p.name);
    $('#profileCode').val(p.code);
    $('#profileRole').val(p.role || '');
    $('#profilePriority').val(p.priority || 1);
    $('#profileRateLimit').val(p.rate_limit || '');
    $('#profileSession').val(p.session_timeout || '');
    $('#profileIdle').val(p.idle_timeout || '');
    $('#profileSimUse').val(p.simultaneous_use || '');
    $('#profilePool').val(p.framed_pool || '');
    $('#profileAddressList').val(p.address_list || '');
    $('#profileDescription').val(p.description || '');
    $('#profileDefault').prop('checked', !!p.is_default);
    $('#profileActive').val(p.is_active ? '1' : '0');
    $('#modalProfile .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Profile RADIUS');
    $('#modalProfile').modal('show');
});

$('#btnSaveProfile').on('click', () => {
    const id = $('#profileId').val();
    $.ajax({
        url: id ? ROUTES.profileUpdate(id) : ROUTES.profileStore,
        method: id ? 'PUT' : 'POST',
        data: {
            name: $('#profileName').val(),
            code: $('#profileCode').val(),
            role: $('#profileRole').val(),
            priority: $('#profilePriority').val(),
            rate_limit: $('#profileRateLimit').val(),
            session_timeout: $('#profileSession').val(),
            idle_timeout: $('#profileIdle').val(),
            simultaneous_use: $('#profileSimUse').val(),
            framed_pool: $('#profilePool').val(),
            address_list: $('#profileAddressList').val(),
            description: $('#profileDescription').val(),
            is_default: $('#profileDefault').is(':checked') ? 1 : 0,
            is_active: $('#profileActive').val(),
            _token: '{{ csrf_token() }}',
        }
    }).done(r => {
        toastr.success(r.message);
        location.reload();
    }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan profile.'));
});

$(document).on('click', '.btn-sync-profile', function () {
    $.post(ROUTES.profileSync($(this).data('id')), { _token: '{{ csrf_token() }}' })
        .done(r => toastr.success(r.message))
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Sync profile gagal.'));
});

$(document).on('click', '.btn-delete-profile', function () {
    if (!confirm('Hapus profile ini dari SIMANSA dan group RADIUS?')) return;
    $.ajax({ url: ROUTES.profileDestroy($(this).data('id')), method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
        .done(r => { toastr.success(r.message); location.reload(); })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Profile tidak bisa dihapus.'));
});

function resetNasForm() {
    $('#formNas')[0].reset();
    $('#nasId').val('');
    $('#nasType').val('mikrotik');
    $('#nasActive').prop('checked', true);
}

$('#btnAddNas').on('click', () => {
    resetNasForm();
    $('#modalNas .modal-title').html('<i class="fas fa-router mr-2"></i>Tambah MikroTik / NAS');
    $('#modalNas').modal('show');
});

$(document).on('click', '.btn-edit-nas', function () {
    const card = $(this).closest('.net-mini-card');
    resetNasForm();
    $('#nasId').val(card.data('id'));
    $('#nasName').val(card.data('name'));
    $('#nasNameIp').val(card.data('nasname'));
    $('#nasShortname').val(card.data('shortname') || '');
    $('#nasType').val(card.data('type') || 'mikrotik');
    $('#nasDescription').val(card.data('description') || '');
    $('#nasSecret').val('');
    $('#nasActive').prop('checked', String(card.data('active')) === '1');
    $('#modalNas .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit MikroTik / NAS');
    $('#modalNas').modal('show');
});

$('#btnSaveNas').on('click', () => {
    const id = $('#nasId').val();
    const btn = $('#btnSaveNas');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
    $.ajax({
        url: id ? ROUTES.nasUpdate(id) : ROUTES.nasStore,
        method: id ? 'PUT' : 'POST',
        timeout: 15000,
        data: {
            name: $('#nasName').val(),
            nasname: $('#nasNameIp').val(),
            shortname: $('#nasShortname').val(),
            type: $('#nasType').val(),
            secret: $('#nasSecret').val(),
            description: $('#nasDescription').val(),
            is_active: $('#nasActive').is(':checked') ? 1 : 0,
            _token: '{{ csrf_token() }}',
        }
    }).done(r => {
        toastr.success(r.message);
        $('#modalNas').modal('hide');
        location.reload();
    }).fail(xhr => {
        const errors = xhr.responseJSON?.errors;
        const firstError = errors ? Object.values(errors).flat()[0] : null;
        toastr.error(firstError || xhr.responseJSON?.message || 'Gagal menyimpan NAS.');
    }).always(() => {
        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan');
    });
});

$(document).on('click', '.btn-sync-nas', function () {
    const btn = $(this);
    const original = btn.html();
    const card = btn.closest('.net-mini-card');
    const nasName = card.data('nasname') || 'NAS';
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sync');

    resetNasSyncModal();
    $('#nasSyncTitle').text('Sync ' + nasName);
    $('#modalNasSync').modal('show');
    appendNasSyncLog('running', 'Menyiapkan request sinkronisasi.');
    setTimeout(() => appendNasSyncLog('running', 'Mengirim request ke server SIMANSA.'), 250);

    $.ajax({
        url: ROUTES.nasSync(btn.data('id')),
        method: 'POST',
        timeout: 30000,
        data: { _token: '{{ csrf_token() }}' }
    }).done(r => {
        appendNasSyncLog('success', 'Response server diterima.');
        renderNasSyncSteps(r.steps || []);
        finishNasSync(true, r.message || 'NAS berhasil disinkronkan.');
        toastr.success(r.message);
    }).fail(xhr => {
        const message = xhr.responseJSON?.message || 'Sync NAS gagal.';
        appendNasSyncLog('error', 'Response server gagal atau timeout.');
        renderNasSyncSteps(xhr.responseJSON?.steps || []);
        finishNasSync(false, message);
        toastr.error(message);
    }).always(() => {
        btn.prop('disabled', false).html(original);
    });
});

function resetNasSyncModal() {
    $('#nasSyncLog').html('');
    $('#nasSyncSpinner').show();
    $('#nasSyncTitle').text('Sync NAS ke FreeRADIUS');
    $('#nasSyncSub').text('Log proses akan tampil di bawah.');
    $('.nas-sync-close, .nas-sync-refresh').hide();
}

function appendNasSyncLog(status, message) {
    const icon = status === 'success'
        ? '<i class="fas fa-check"></i>'
        : status === 'error'
            ? '<i class="fas fa-times"></i>'
            : '<i class="fas fa-circle-notch fa-spin"></i>';
    const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
    $('#nasSyncLog').append(`
        <div class="nas-sync-log__row ${status}">
            <span class="nas-sync-log__icon">${icon}</span>
            <span>[${time}] ${escapeHtml(message)}</span>
        </div>
    `);
    const log = document.getElementById('nasSyncLog');
    log.scrollTop = log.scrollHeight;
}

function renderNasSyncSteps(steps) {
    steps.forEach(step => appendNasSyncLog(step.status || 'running', step.message || '-'));
}

function finishNasSync(success, message) {
    $('#nasSyncSpinner').hide();
    $('#nasSyncTitle').text(success ? 'Sync NAS selesai' : 'Sync NAS gagal');
    $('#nasSyncSub').text(message);
    appendNasSyncLog(success ? 'success' : 'error', message);
    $('.nas-sync-close, .nas-sync-refresh').show();
}

$('.nas-sync-refresh').on('click', () => location.reload());

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

$(document).on('click', '.btn-delete-nas', function () {
    if (!confirm('Hapus NAS ini dari SIMANSA dan database RADIUS?')) return;
    $.ajax({ url: ROUTES.nasDestroy($(this).data('id')), method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
        .done(r => { toastr.success(r.message); location.reload(); })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'NAS tidak bisa dihapus.'));
});

function loadRadiusStatus() {
    const $panel = $('#radiusStatusPanel');
    const $btn = $('#btnRefreshRadius');

    $btn.prop('disabled', true).addClass('disabled');
    $panel.html(`
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa koneksi RADIUS...
            <div class="small mt-2 text-muted">Halaman hotspot tetap berjalan memakai data lokal selama pengecekan berlangsung.</div>
        </div>
    `);

    $.ajax({
        url: ROUTES.radiusStatus,
        method: 'GET',
        timeout: 8000
    }).done(r => {
        if (!r.connected) {
            $panel.html(`
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="small mb-1 font-weight-bold">RADIUS sedang tidak dapat dihubungi</p>
                    <small class="text-muted d-block">${r.error || 'Koneksi ke layanan eksternal sedang bermasalah.'}</small>
                    <small class="text-muted d-block mt-2">Data akun hotspot lokal tetap tersedia. Coba refresh beberapa saat lagi.</small>
                </div>`);
            $('#statOnline').text('--');
            return;
        }

        const c = r.counts;
        let authRows = '';
        (r.recent_auth || []).forEach(a => {
            const cls = a.reply === 'Access-Accept' ? 'accept' : 'reject';
            const icon = cls === 'accept' ? '✓' : '✗';
            authRows += `<div class="radius-auth-row"><span class="${cls}">${icon} ${a.username}</span> <span class="float-right text-muted" style="font-size:.7rem">${a.reply === 'Access-Accept' ? 'OK' : 'REJECTED'}</span></div>`;
        });

        $('#statOnline').text(c.radacct_active);

        $panel.html(`
            <div class="radius-live mb-2">
                <div class="radius-live__row"><span>Akun di RADIUS</span><span class="radius-live__val">${c.radcheck}</span></div>
                <div class="radius-live__row"><span>User groups</span><span class="radius-live__val">${c.radusergroup}</span></div>
                <div class="radius-live__row"><span>Group reply</span><span class="radius-live__val">${c.radgroupreply ?? '-'}</span></div>
                <div class="radius-live__row"><span>NAS/MikroTik</span><span class="radius-live__val">${c.nas ?? '-'}</span></div>
                <div class="radius-live__row"><span>Online saat ini</span><span class="radius-live__val" style="color:#4ade80">${c.radacct_active}</span></div>
                <div class="radius-live__row"><span>Auth hari ini</span><span class="radius-live__val">${c.radpostauth_today}</span></div>
            </div>
            <div class="mb-1" style="font-size:.72rem;font-weight:700;color:#64748b;letter-spacing:.05em;text-transform:uppercase">AUTH TERBARU</div>
            <div class="radius-live" style="padding:.5rem">${authRows || '<div class="text-muted text-center py-2" style="font-size:.75rem">Belum ada data</div>'}</div>
        `);
    }).fail((xhr, textStatus) => {
        const isTimeout = textStatus === 'timeout';
        $panel.html(`
            <div class="text-center py-3 text-warning">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <p class="small mb-1 font-weight-bold">${isTimeout ? 'Pengecekan RADIUS melebihi batas waktu' : 'Status RADIUS belum bisa dimuat'}</p>
                <small class="text-muted d-block">${isTimeout ? 'Koneksi ke layanan luar terlalu lama merespons.' : 'Terjadi kendala saat memuat status layanan eksternal.'}</small>
                <small class="text-muted d-block mt-2">Anda tetap bisa mengelola data hotspot lokal dan mencoba cek ulang nanti.</small>
            </div>
        `);
        $('#statOnline').text('--');
    }).always(() => {
        $btn.prop('disabled', false).removeClass('disabled');
    });
}

$('#btnRefreshRadius').on('click', loadRadiusStatus);
loadRadiusStatus();
setInterval(loadRadiusStatus, 30000); // refresh tiap 30 detik

// ── Utils ─────────────────────────────────────────────────────────────────
function debounce(fn, delay) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}
</script>
@endsection
