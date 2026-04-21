@extends('adminlte::page')

@section('title', 'Monitor Online - Hotspot SIMANSA')

@section('css')
<style>
/* ── Hero ──────────────────────────────────────────────────────────────── */
.hs-hero {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, rgba(22,163,74,.88), rgba(5,150,105,.82));
    border-radius: 20px;
    padding: 1.1rem 1.3rem;
    color: #fff;
    margin-bottom: .8rem;
    box-shadow: 0 8px 24px rgba(22,163,74,.25);
}
.hs-hero__eyebrow { font-size:.72rem; font-weight:700; letter-spacing:.07em; opacity:.85; text-transform:uppercase; margin-bottom:.3rem; }
.hs-hero__title   { font-size:1.35rem; font-weight:800; margin:0; }
.hs-hero__sub     { font-size:.8rem; opacity:.82; margin-top:.25rem; }

/* ── Summary bar ───────────────────────────────────────────────────────── */
.ol-summary {
    display: flex; gap: .55rem; flex-wrap: wrap; margin-bottom: .8rem;
}
.ol-sum-card {
    background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    padding: .6rem 1rem; display: flex; align-items: center; gap: .6rem;
    min-width: 120px;
}
.ol-sum-card__icon { font-size: 1.1rem; }
.ol-sum-card__val  { font-size: 1.4rem; font-weight: 800; line-height: 1; }
.ol-sum-card__label{ font-size: .66rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }

/* ── Panel ─────────────────────────────────────────────────────────────── */
.hs-panel {
    background: #fff; border-radius: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07); border: 1px solid #e2e8f0; overflow: hidden;
}
.hs-panel__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .8rem 1.1rem; border-bottom: 1px solid #e2e8f0;
    background: #f8fafc; flex-wrap: wrap; gap: .5rem;
}
.hs-panel__title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.hs-panel__body  { padding: .8rem 1rem; }

/* ── Session table ─────────────────────────────────────────────────────── */
.ol-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .82rem; }
.ol-table thead th {
    background: #f1f5f9; padding: .6rem .75rem; font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: #64748b;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.ol-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .12s; }
.ol-table tbody tr:hover { background: #f0fdf4; }
.ol-table tbody td { padding: .6rem .75rem; vertical-align: middle; }
.ol-table tbody tr:last-child { border-bottom: none; }

/* ── Duration / bytes ──────────────────────────────────────────────────── */
.dur-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    background: #dcfce7; color: #15803d; border-radius: 20px;
    padding: .2rem .6rem; font-size: .72rem; font-weight: 700;
}
.bytes-row { font-size: .72rem; color: #64748b; }

/* ── Refresh bar ───────────────────────────────────────────────────────── */
.refresh-bar {
    display: flex; align-items: center; gap: .5rem;
    padding: .4rem .75rem; background: #f0fdf4;
    border-bottom: 1px solid #bbf7d0; font-size: .75rem; color: #15803d;
}
.refresh-bar .countdown { font-weight: 700; min-width: 1.5rem; display: inline-block; }

/* ── Empty / error state ───────────────────────────────────────────────── */
.ol-empty { text-align: center; padding: 3rem 1rem; }
.ol-empty__icon { font-size: 3rem; margin-bottom: .5rem; }
.ol-empty__title { font-weight: 700; color: #1e293b; margin: .25rem 0; }
.ol-empty__sub   { font-size: .8rem; color: #64748b; }

/* ── Search filter ─────────────────────────────────────────────────────── */
.ol-filter { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }

/* ── Pulse ─────────────────────────────────────────────────────────────── */
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.pulse { animation: pulse 1.5s infinite; }
</style>
@endsection

@section('content_header')
<div class="hs-hero">
    <div>
        <div class="hs-hero__eyebrow">
            <i class="fas fa-wifi mr-1"></i>Hotspot Manager
        </div>
        <h1 class="hs-hero__title">
            <span class="pulse" style="margin-right:.35rem">🟢</span>Monitor User Online
        </h1>
        <p class="hs-hero__sub mb-0">
            Sesi aktif di FreeRADIUS &mdash; data langsung dari tabel <code style="font-size:.72rem;opacity:.85">radacct</code>
        </p>
    </div>
    <div class="d-flex flex-column gap-2 align-items-end">
        @if($radiusConnected)
            <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .7rem;border-radius:20px;font-size:.75rem;font-weight:700;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.4)">
                <span style="width:8px;height:8px;border-radius:50%;background:#4ade80;box-shadow:0 0 6px #4ade80"></span>
                FreeRADIUS Terhubung
            </span>
        @else
            <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .7rem;border-radius:20px;font-size:.75rem;font-weight:700;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4)">
                <span style="width:8px;height:8px;border-radius:50%;background:#ef4444"></span>
                FreeRADIUS Offline
            </span>
        @endif
        <a href="{{ route('admin.hotspot.index') }}" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>
@endsection

@section('content')

{{-- Summary mini cards --------------------------------------------------- --}}
<div class="ol-summary">
    <div class="ol-sum-card">
        <div class="ol-sum-card__icon"><span class="pulse">🟢</span></div>
        <div>
            <div class="ol-sum-card__val text-success" id="sumTotal">–</div>
            <div class="ol-sum-card__label">Total Online</div>
        </div>
    </div>
    <div class="ol-sum-card">
        <div class="ol-sum-card__icon">👨‍🏫</div>
        <div>
            <div class="ol-sum-card__val text-primary" id="sumGuru">–</div>
            <div class="ol-sum-card__label">Guru/GTK</div>
        </div>
    </div>
    <div class="ol-sum-card">
        <div class="ol-sum-card__icon">👨‍🎓</div>
        <div>
            <div class="ol-sum-card__val" style="color:#0891b2" id="sumSiswa">–</div>
            <div class="ol-sum-card__label">Siswa</div>
        </div>
    </div>
    <div class="ol-sum-card">
        <div class="ol-sum-card__icon">🧑‍💼</div>
        <div>
            <div class="ol-sum-card__val text-warning" id="sumTamu">–</div>
            <div class="ol-sum-card__label">Tamu</div>
        </div>
    </div>
    <div class="ol-sum-card ml-auto" style="background:#0f172a;border-color:#1e293b">
        <div class="ol-sum-card__icon">🖥️</div>
        <div>
            <div class="ol-sum-card__val text-info" id="sumNas">–</div>
            <div class="ol-sum-card__label" style="color:#94a3b8">Access Point</div>
        </div>
    </div>
</div>

{{-- Main Table ----------------------------------------------------------- --}}
<div class="hs-panel">

    {{-- Refresh bar --}}
    <div class="refresh-bar" id="refreshBar">
        <span class="pulse">🟢</span>
        <span>Memuat data...</span>
        <span class="ml-auto text-muted" id="lastRefreshText" style="font-size:.7rem"></span>
    </div>

    {{-- Toolbar --}}
    <div class="hs-panel__header">
        <span class="hs-panel__title">
            <i class="fas fa-satellite-dish mr-1 text-success"></i>
            Sesi Aktif &mdash; <span id="sessionCount" class="text-success">0</span> user
        </span>
        <div class="ol-filter">
            <input type="text" id="searchOnline" class="form-control form-control-sm"
                   placeholder="Cari nama / username / IP..." style="width:220px">
            <select id="filterRoleOnline" class="form-control form-control-sm" style="width:120px">
                <option value="">Semua Role</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
                <option value="tamu">Tamu</option>
            </select>
            <button class="btn btn-outline-success btn-sm" id="btnRefreshNow" title="Refresh sekarang">
                <i class="fas fa-sync mr-1"></i>Refresh
            </button>
            <a href="{{ route('admin.hotspot.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="hs-panel__body" style="padding:0;overflow-x:auto">
        <div id="onlineTableWrap">
            <div class="ol-empty">
                <div class="ol-empty__icon">📡</div>
                <div class="ol-empty__title">Memuat data...</div>
                <div class="ol-empty__sub">Mengambil sesi aktif dari FreeRADIUS</div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
<script>
const ROUTES = {
    onlineUsers:  '{{ route("admin.hotspot.online-users") }}',
    toggleActive: (id) => `{{ url("admin/hotspot") }}/${id}/toggle-active`,
    hotspotIndex: '{{ route("admin.hotspot.index") }}',
};

const CSRF = '{{ csrf_token() }}';

let _allSessions  = [];
let _countdown    = 30;
let _cdTimer      = null;
let _refreshTimer = null;

// ── Format helpers ────────────────────────────────────────────────────────
function fmtBytes(b) {
    if (!b || b <= 0) return '<span class="text-muted">0 B</span>';
    const u = ['B','KB','MB','GB']; let i = 0;
    while (b >= 1024 && i < u.length - 1) { b /= 1024; i++; }
    return b.toFixed(i > 0 ? 1 : 0) + '&thinsp;' + u[i];
}

function fmtDuration(s) {
    if (!s || s <= 0) return '<span class="text-muted">-</span>';
    const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
    let str = '';
    if (h > 0) str += h + ' j ';
    if (m > 0 || h > 0) str += m + ' m ';
    str += sec + ' d';
    return str.trim();
}

function fmtDate(str) {
    if (!str) return '-';
    return new Date(str).toLocaleString('id-ID', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
}

// ── Load sessions ─────────────────────────────────────────────────────────
function loadSessions() {
    $('#btnRefreshNow').html('<i class="fas fa-spin fa-sync mr-1"></i>Memuat...').prop('disabled', true);

    $.get(ROUTES.onlineUsers)
        .done(r => {
            const now = new Date().toLocaleTimeString('id-ID');
            $('#lastRefreshText').text('Diperbarui: ' + now);
            $('#refreshBar').html(`<span class="pulse">🟢</span><span>Live &mdash; auto-refresh tiap <strong class="countdown" id="cdNum">${_countdown}</strong> detik</span><span class="ml-auto text-muted" style="font-size:.7rem">Diperbarui: ${now}</span>`);

            if (!r.success) {
                $('#onlineTableWrap').html(`<div class="ol-empty"><div class="ol-empty__icon">⚠️</div><div class="ol-empty__title text-danger">Gagal Mengambil Data</div><div class="ol-empty__sub">${r.error || 'Tidak dapat terhubung ke FreeRADIUS'}</div></div>`);
                updateSummary([]);
                return;
            }

            _allSessions = r.sessions || [];
            updateSummary(_allSessions);
            renderTable();
        })
        .fail(() => {
            $('#onlineTableWrap').html('<div class="ol-empty"><div class="ol-empty__icon">❌</div><div class="ol-empty__title text-danger">Error</div><div class="ol-empty__sub">Gagal menghubungi server. Cek koneksi.</div></div>');
            updateSummary([]);
        })
        .always(() => {
            $('#btnRefreshNow').html('<i class="fas fa-sync mr-1"></i>Refresh').prop('disabled', false);
        });
}

function updateSummary(sessions) {
    const guru  = sessions.filter(s => s.role === 'guru').length;
    const siswa = sessions.filter(s => s.role === 'siswa').length;
    const tamu  = sessions.filter(s => s.role === 'tamu').length;
    const nas   = new Set(sessions.map(s => s.nas_ip).filter(Boolean)).size;
    $('#sumTotal').text(sessions.length);
    $('#sumGuru').text(guru);
    $('#sumSiswa').text(siswa);
    $('#sumTamu').text(tamu);
    $('#sumNas').text(nas);
    $('#sessionCount').text(sessions.length);
}

function renderTable() {
    const search   = ($('#searchOnline').val() || '').toLowerCase().trim();
    const roleF    = $('#filterRoleOnline').val();

    const filtered = _allSessions.filter(s => {
        if (roleF && s.role !== roleF) return false;
        if (search) {
            const hay = [s.username, s.display_name, s.framed_ip, s.mac, s.kelas].join(' ').toLowerCase();
            if (!hay.includes(search)) return false;
        }
        return true;
    });

    if (filtered.length === 0) {
        $('#onlineTableWrap').html(`
            <div class="ol-empty">
                <div class="ol-empty__icon">📡</div>
                <div class="ol-empty__title">Tidak ada sesi aktif</div>
                <div class="ol-empty__sub">${_allSessions.length > 0 ? 'Tidak ada hasil yang cocok dengan filter.' : 'Belum ada user yang terhubung ke WiFi saat ini.'}</div>
            </div>`);
        return;
    }

    const avMap = {
        guru:  { bg: '#dbeafe', ic: '👨‍🏫', badge: 'badge-primary' },
        siswa: { bg: '#e0f2fe', ic: '👨‍🎓', badge: 'badge-info' },
        tamu:  { bg: '#fef3c7', ic: '🧑‍💼', badge: 'badge-warning' },
    };

    const rows = filtered.map((s, i) => {
        const av  = avMap[s.role] || { bg: '#f1f5f9', ic: '👤', badge: 'badge-secondary' };
        const kb  = s.kelas ? `<span class="badge badge-light border ml-1" style="font-size:.6rem">${escHtml(s.kelas)}</span>` : '';
        const blk = s.hotspot_id
            ? `<button class="btn btn-xs btn-outline-danger btn-blk"
                       data-id="${s.hotspot_id}" data-uname="${escHtml(s.username)}"
                       title="Blokir &amp; Disconnect saat reconnect">
                    <i class="fas fa-ban"></i>
               </button>`
            : '';

        return `<tr>
            <td style="text-align:center;width:36px">
                <span style="font-size:1.2rem;display:inline-block;width:36px;height:36px;line-height:36px;border-radius:50%;background:${av.bg};text-align:center">${av.ic}</span>
            </td>
            <td>
                <div style="font-weight:700;color:#1e293b">${escHtml(s.display_name || s.username)}${kb}</div>
                <div style="font-size:.72rem;color:#64748b;font-family:monospace">${escHtml(s.username)}</div>
            </td>
            <td><span class="badge ${av.badge}">${escHtml(s.role)}</span></td>
            <td>
                <div style="font-family:monospace;font-size:.8rem;font-weight:600;color:#2563eb">${escHtml(s.framed_ip || '-')}</div>
                <div class="bytes-row"><i class="fas fa-ethernet mr-1"></i>${escHtml(s.mac || '-')}</div>
            </td>
            <td>
                <div style="font-size:.78rem;color:#64748b">${escHtml(s.nas_ip || '-')}</div>
            </td>
            <td>
                <span class="dur-pill"><i class="fas fa-clock"></i>${fmtDuration(s.session_time)}</span>
                <div class="bytes-row mt-1" style="font-size:.68rem">${fmtDate(s.started_at)}</div>
            </td>
            <td style="text-align:right">
                <div class="bytes-row"><i class="fas fa-arrow-down text-success mr-1"></i>${fmtBytes(s.bytes_out)}</div>
                <div class="bytes-row"><i class="fas fa-arrow-up text-primary mr-1"></i>${fmtBytes(s.bytes_in)}</div>
            </td>
            <td style="text-align:center">${blk}</td>
        </tr>`;
    }).join('');

    $('#onlineTableWrap').html(`
        <table class="ol-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nama / Username</th>
                    <th>Role</th>
                    <th>IP &amp; MAC</th>
                    <th>Access Point</th>
                    <th>Durasi / Mulai</th>
                    <th style="text-align:right">Data Usage</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`);
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Countdown ─────────────────────────────────────────────────────────────
function startCountdown() {
    if (_cdTimer) clearInterval(_cdTimer);
    _countdown = 30;
    _cdTimer = setInterval(() => {
        _countdown--;
        const el = document.getElementById('cdNum');
        if (el) el.textContent = _countdown;
        if (_countdown <= 0) {
            _countdown = 30;
            loadSessions();
        }
    }, 1000);
}

// ── Events ────────────────────────────────────────────────────────────────
$('#btnRefreshNow').on('click', () => {
    _countdown = 30;
    loadSessions();
    startCountdown();
});

$('#searchOnline').on('keyup', debounce(renderTable, 250));
$('#filterRoleOnline').on('change', renderTable);

$(document).on('click', '.btn-blk', function () {
    const id   = $(this).data('id');
    const uname = $(this).data('uname');
    if (!confirm(`Blokir akun "${uname}"?\nUser akan terputus saat reconnect ke WiFi.`)) return;
    const $btn = $(this).html('<i class="fas fa-spin fa-sync"></i>').prop('disabled', true);
    $.post(ROUTES.toggleActive(id), { _token: CSRF })
        .done(r => {
            if (!r.is_active) {
                toastr.warning(`Akun ${uname} berhasil diblokir.`);
                // Remove from sessions and re-render
                _allSessions = _allSessions.filter(s => s.hotspot_id != id);
                updateSummary(_allSessions);
                renderTable();
            } else {
                toastr.success(r.message);
            }
        })
        .fail(() => toastr.error('Gagal memblokir akun.'))
        .always(() => $btn.html('<i class="fas fa-ban"></i>').prop('disabled', false));
});

// ── Utility ───────────────────────────────────────────────────────────────
function debounce(fn, delay) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

// ── Init ──────────────────────────────────────────────────────────────────
loadSessions();
startCountdown();
</script>
@endsection
