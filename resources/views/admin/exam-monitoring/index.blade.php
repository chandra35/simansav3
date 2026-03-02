@extends('adminlte::page')

@section('title', 'Monitoring Ujian - ExaManmet')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Stats cards - compact */
    .stat-box { border-radius: 8px; padding: 12px 16px; color: #fff; }
    .stat-box .num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
    .stat-box .lbl { font-size: 0.72rem; opacity: 0.9; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
    .bg-stat-total { background: linear-gradient(135deg,#007bff,#0056b3); }
    .bg-stat-online { background: linear-gradient(135deg,#28a745,#1e7e34); }
    .bg-stat-idle { background: linear-gradient(135deg,#fd7e14,#e8590c); }
    .bg-stat-locked { background: linear-gradient(135deg,#dc3545,#bd2130); }
    .bg-stat-violation { background: linear-gradient(135deg,#ffc107,#d39e00); }
    .bg-stat-offline { background: linear-gradient(135deg,#6c757d,#495057); }

    /* Status dots */
    .sd { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .sd.online { background: #28a745; box-shadow: 0 0 5px rgba(40,167,69,.5); animation: pulse 2s infinite; }
    .sd.idle { background: #fd7e14; }
    .sd.offline { background: #dc3545; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

    /* Compact table */
    #tbl-sessions { font-size: 0.82rem; }
    #tbl-sessions th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .3px; background: #f8f9fa; border-bottom: 2px solid #dee2e6; white-space: nowrap; padding: 8px 10px; }
    #tbl-sessions td { padding: 6px 10px; vertical-align: middle; }
    #tbl-sessions tbody tr:hover { background: #f0f7ff; }
    #tbl-sessions tbody tr.row-locked { background: #fff5f5; }
    #tbl-sessions tbody tr.row-locked:hover { background: #ffe8e8; }

    /* Violation chip */
    .v-chip { display: inline-block; border-radius: 12px; padding: 1px 8px; font-size: .7rem; font-weight: 700; }
    .v-chip.danger { background: #dc3545; color: #fff; }
    .v-chip.warning { background: #ffc107; color: #000; }
    .v-chip.ok { background: #e9ecef; color: #6c757d; }

    /* Lock badge */
    .lock-badge { background: #dc3545; color: #fff; border-radius: 4px; padding: 1px 6px; font-size: .68rem; font-weight: 700; }

    /* Action buttons */
    .btn-xs { padding: 2px 8px; font-size: .72rem; border-radius: 4px; }

    /* Filter tabs */
    .filter-tabs .btn { border-radius: 20px; font-size: .78rem; padding: 4px 14px; font-weight: 600; }
    .filter-tabs .btn.active { box-shadow: 0 2px 8px rgba(0,0,0,.15); }

    /* Avatar */
    .av { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1.5px solid #dee2e6; }
    .av-ph { width: 28px; height: 28px; border-radius: 50%; background: #6c757d; color: #fff; display: flex; align-items: center; justify-content: center; font-size: .65rem; font-weight: 700; }

    /* Refresh indicator */
    .refresh-ind { display: inline-flex; align-items: center; gap: 5px; color: #6c757d; font-size: .75rem; }
    .refresh-ind .spinner-grow { width: 7px; height: 7px; }

    /* Violation modal */
    .viol-item { padding: 7px 10px; border-left: 3px solid #dee2e6; margin-bottom: 6px; background: #f8f9fa; border-radius: 0 4px 4px 0; font-size: .82rem; }
    .viol-item.danger { border-left-color: #dc3545; }
    .viol-item.warning { border-left-color: #ffc107; }
    .viol-item.info { border-left-color: #17a2b8; }
    .viol-list { max-height: 400px; overflow-y: auto; }

    /* Scrollable table container */
    .table-wrap { max-height: calc(100vh - 340px); overflow-y: auto; }
    .table-wrap thead th { position: sticky; top: 0; z-index: 2; }

    /* Search box */
    .search-box input { border-radius: 20px; font-size: .82rem; padding: 5px 14px; width: 220px; }

    /* Sortable headers */
    .sortable { cursor: pointer; user-select: none; position: relative; }
    .sortable:hover { background: #e9ecef !important; }
    .sort-icon { font-size: .6rem; margin-left: 3px; opacity: .35; }
    .sortable.asc .sort-icon, .sortable.desc .sort-icon { opacity: 1; color: #007bff; }

    /* Checkbox */
    #tbl-sessions .chk-cell { width: 32px; text-align: center; }
    #tbl-sessions .chk-cell input { cursor: pointer; width: 15px; height: 15px; }
    #tbl-sessions tbody tr.row-selected { background: #e3f0ff !important; }
    #tbl-sessions tbody tr.row-selected.row-locked { background: #ffe0e0 !important; }

    /* Floating bulk action bar */
    .bulk-bar { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1050;
      background: #1e1e2f; color: #fff; border-radius: 12px; padding: 10px 20px;
      display: none; align-items: center; gap: 12px; box-shadow: 0 6px 24px rgba(0,0,0,.35);
      font-size: .85rem; animation: slideUp .25s ease; }
    .bulk-bar.show { display: flex; }
    .bulk-bar .count { font-weight: 700; font-size: 1rem; min-width: 28px; text-align: center;
      background: #007bff; border-radius: 6px; padding: 2px 8px; }
    .bulk-bar .btn { border-radius: 8px; font-size: .78rem; font-weight: 600; padding: 5px 14px; }
    @keyframes slideUp { from { opacity:0; transform: translateX(-50%) translateY(20px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-tv mr-2 text-primary"></i>Monitoring Ujian</h1>
        <small class="text-muted">Real-time monitoring peserta ujian ExaManmet</small>
    </div>
    <div class="d-flex align-items-center flex-wrap" style="gap:8px">
        {{-- Date Filter --}}
        <div class="input-group input-group-sm" style="width:auto">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
            </div>
            <select class="form-control form-control-sm" id="date-filter" onchange="changeDateFilter(this.value)" style="width:auto;min-width:140px">
                <option value="{{ now()->format('Y-m-d') }}" {{ $dateFilter === now()->format('Y-m-d') ? 'selected' : '' }}>Hari Ini ({{ now()->format('d M') }})</option>
                @foreach($availableDates as $d)
                    @if($d !== now()->format('Y-m-d'))
                        <option value="{{ $d }}" {{ $dateFilter === $d ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($d)->format('d M Y') }}</option>
                    @endif
                @endforeach
                <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>Semua Hari</option>
            </select>
        </div>
        <div class="refresh-ind">
            <span class="spinner-grow spinner-grow-sm text-success"></span>
            Auto <span id="countdown">5</span>s
        </div>
        <button class="btn btn-sm btn-outline-primary" onclick="refreshData()"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
    </div>
</div>
@endsection

@section('content')
{{-- Stats Row --}}
<div class="row mb-3">
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-total">
            <div class="num" id="s-total">{{ $stats['total_active'] }}</div>
            <div class="lbl"><i class="fas fa-users mr-1"></i>Total Aktif</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-online">
            <div class="num" id="s-online">{{ $stats['online'] }}</div>
            <div class="lbl"><i class="fas fa-wifi mr-1"></i>Online</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-idle">
            <div class="num" id="s-idle">{{ $stats['idle'] ?? 0 }}</div>
            <div class="lbl"><i class="fas fa-clock mr-1"></i>Idle</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-offline">
            <div class="num" id="s-offline">{{ $stats['offline'] ?? 0 }}</div>
            <div class="lbl"><i class="fas fa-plug mr-1"></i>Offline</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-locked">
            <div class="num" id="s-locked">{{ $stats['locked'] }}</div>
            <div class="lbl"><i class="fas fa-lock mr-1"></i>Terkunci</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-2">
        <div class="stat-box bg-stat-violation">
            <div class="num" id="s-violations">{{ $stats['with_violations'] }}</div>
            <div class="lbl"><i class="fas fa-exclamation-triangle mr-1"></i>Bermasalah</div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card card-outline card-primary mb-0">
    <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            {{-- Filter Tabs --}}
            <div class="filter-tabs d-flex flex-wrap" id="filters" style="gap:4px">
                <button class="btn btn-primary btn-sm active" data-filter="all" onclick="setFilter('all',this)">
                    Semua <span class="badge badge-light ml-1" id="f-all">{{ $stats['total_active'] }}</span>
                </button>
                <button class="btn btn-outline-success btn-sm" data-filter="online" onclick="setFilter('online',this)">
                    <i class="fas fa-wifi mr-1"></i>Online <span class="badge badge-success ml-1" id="f-online">{{ $stats['online'] }}</span>
                </button>
                <button class="btn btn-outline-danger btn-sm" data-filter="locked" onclick="setFilter('locked',this)">
                    <i class="fas fa-lock mr-1"></i>Terkunci <span class="badge badge-danger ml-1" id="f-locked">{{ $stats['locked'] }}</span>
                </button>
                <button class="btn btn-outline-warning btn-sm" data-filter="violations" onclick="setFilter('violations',this)">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Bermasalah <span class="badge badge-warning ml-1" id="f-violations">{{ $stats['with_violations'] }}</span>
                </button>
                <button class="btn btn-outline-secondary btn-sm" data-filter="offline" onclick="setFilter('offline',this)">
                    <i class="fas fa-plug mr-1"></i>Offline <span class="badge badge-secondary ml-1" id="f-offline">{{ $stats['offline'] ?? 0 }}</span>
                </button>
            </div>
            <div class="d-flex align-items-center mt-1 mt-md-0" style="gap:6px">
                {{-- Search --}}
                <div class="search-box">
                    <input type="text" class="form-control form-control-sm" id="search-input" placeholder="Cari nama / NISN / kelas..." oninput="filterTable()">
                </div>
                {{-- Bulk actions --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" title="Aksi Massal"><i class="fas fa-ellipsis-v"></i></button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item text-danger" href="#" onclick="bulkLock(); return false;"><i class="fas fa-lock mr-2"></i>Kunci Semua Bermasalah</a>
                        <a class="dropdown-item text-success" href="#" onclick="bulkUnlock(); return false;"><i class="fas fa-unlock mr-2"></i>Buka Semua Kunci</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-muted" href="#" onclick="endAllOffline(); return false;"><i class="fas fa-power-off mr-2"></i>Akhiri Semua Offline</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-wrap">
            <table class="table table-hover mb-0" id="tbl-sessions">
                <thead>
                    <tr>
                        <th class="chk-cell"><input type="checkbox" id="chk-all" title="Pilih Semua" onchange="toggleAllCheckboxes(this)"></th>
                        <th class="sortable" data-sort="status" onclick="sortBy('status',this)"># Status <i class="fas fa-sort sort-icon"></i></th>
                        <th class="sortable" data-sort="siswa_nama" onclick="sortBy('siswa_nama',this)">Siswa <i class="fas fa-sort sort-icon"></i></th>
                        <th>NISN</th>
                        <th class="sortable" data-sort="kelas" onclick="sortBy('kelas',this)">Kelas <i class="fas fa-sort sort-icon"></i></th>
                        <th>Device</th>
                        <th>IP</th>
                        <th class="sortable" data-sort="started_at_raw" onclick="sortBy('started_at_raw',this)">Mulai <i class="fas fa-sort sort-icon"></i></th>
                        <th class="sortable" data-sort="last_heartbeat_raw" onclick="sortBy('last_heartbeat_raw',this)">Heartbeat <i class="fas fa-sort sort-icon"></i></th>
                        <th class="sortable" data-sort="violation_count" onclick="sortBy('violation_count',this)">Pelanggaran <i class="fas fa-sort sort-icon"></i></th>
                        <th style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-sessions">
                    @forelse ($activeSessions as $i => $s)
                    <tr class="{{ $s->is_locked ? 'row-locked' : '' }}" id="row-{{ $s->id }}">
                        <td class="chk-cell"><input type="checkbox" onchange="toggleRow('{{ $s->id }}',this)"></td>
                        <td>
                            <span class="sd {{ $s->status }}"></span>
                            <small class="text-{{ $s->status_color }}">{{ $s->status_label }}</small>
                            @if($s->is_locked)<span class="lock-badge ml-1"><i class="fas fa-lock"></i></span>@endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($s->siswa?->foto_profile)
                                    <img src="{{ asset('storage/' . $s->siswa->foto_profile) }}" class="av mr-2">
                                @else
                                    <div class="av-ph mr-2">{{ strtoupper(substr($s->siswa?->nama_lengkap ?? $s->moodle_fullname ?? '?', 0, 1)) }}</div>
                                @endif
                                <div>
                                    <div style="line-height:1.2"><strong>{{ $s->siswa?->nama_lengkap ?? $s->moodle_fullname ?? $s->moodle_username ?? '-' }}</strong></div>
                                    @if($s->is_locked && $s->lock_reason)<div class="text-danger" style="font-size:.68rem"><i class="fas fa-info-circle"></i> {{ Str::limit($s->lock_reason, 35) }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td><code style="font-size:.75rem">{{ $s->siswa?->nisn ?? $s->moodle_username ?? '-' }}</code></td>
                        <td>{{ $s->siswa?->kelasSaatIni?->nama_kelas ?? '-' }}</td>
                        <td><small>{{ $s->device_model ?? '-' }}</small></td>
                        <td><small>{{ $s->ip_address ?? '-' }}</small></td>
                        <td><small>{{ $s->started_at?->format('H:i') }}</small></td>
                        <td><small>{{ $s->last_heartbeat?->diffForHumans(short: true) }}</small></td>
                        <td>
                            @if($s->violation_count >= 3)
                                <span class="v-chip danger">{{ $s->violation_count }}x</span>
                            @elseif($s->violation_count > 0)
                                <span class="v-chip warning">{{ $s->violation_count }}x</span>
                            @else
                                <span class="v-chip ok">0</span>
                            @endif
                        </td>
                        <td>
                            @if($s->is_locked)
                                <button class="btn btn-success btn-xs" onclick="unlockSession('{{ $s->id }}')" title="Buka Kunci"><i class="fas fa-unlock"></i></button>
                            @else
                                <button class="btn btn-danger btn-xs" onclick="lockSession('{{ $s->id }}')" title="Kunci"><i class="fas fa-lock"></i></button>
                            @endif
                            <button class="btn btn-info btn-xs" onclick="showViolations('{{ $s->id }}')" title="Detail Pelanggaran"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-secondary btn-xs" onclick="endSession('{{ $s->id }}')" title="Akhiri Session"><i class="fas fa-power-off"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-row"><td colspan="12" class="text-center text-muted py-5">
                        <i class="fas fa-desktop fa-3x d-block mb-2"></i>
                        Belum ada siswa yang menggunakan ExaManmet
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-2 bg-white d-flex justify-content-between">
        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Heartbeat 10s &bull; Online &le;60s &bull; Idle 60-120s &bull; Offline &gt;120s &bull; Auto-lock &ge;3 pelanggaran</small>
        <small class="text-muted" id="showing-count"></small>
    </div>
</div>

{{-- Floating Bulk Action Bar --}}
<div class="bulk-bar" id="bulk-bar">
    <span class="count" id="sel-count">0</span>
    <span>dipilih</span>
    <button class="btn btn-danger btn-sm" onclick="bulkLockSelected()"><i class="fas fa-lock mr-1"></i>Kunci</button>
    <button class="btn btn-success btn-sm" onclick="bulkUnlockSelected()"><i class="fas fa-unlock mr-1"></i>Buka Kunci</button>
    <button class="btn btn-secondary btn-sm" onclick="bulkEndSelected()"><i class="fas fa-power-off mr-1"></i>Akhiri</button>
    <button class="btn btn-outline-light btn-sm" onclick="clearSelection()" title="Batal"><i class="fas fa-times"></i></button>
</div>

{{-- Violation Detail Modal --}}
<div class="modal fade" id="violModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-danger text-white">
                <h6 class="modal-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Detail Pelanggaran &mdash; <span id="viol-name"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge badge-dark" id="viol-device"></span>
                        <span class="badge badge-info" id="viol-ip"></span>
                        <span class="badge badge-secondary" id="viol-started"></span>
                    </div>
                    <span class="badge badge-danger" id="viol-count"></span>
                </div>
                <div class="viol-list" id="viol-list">
                    <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const csrf = '{{ csrf_token() }}';
let activeFilter = 'all';
let allSessions = [];
let cd = 5;
let selectedIds = new Set();
let sortCol = '';
let sortDir = 'asc';

// ===== Auto Refresh =====
setInterval(() => {
    cd--;
    document.getElementById('countdown').textContent = cd;
    if (cd <= 0) { cd = 5; refreshData(); }
}, 1000);

function refreshData() {
    cd = 5;
    document.getElementById('countdown').textContent = '...';
    fetch('{{ route("admin.exam-monitoring.api.sessions") }}?date=' + currentDateFilter)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                allSessions = d.sessions;
                updateStats(d.stats);
                renderTable();
                updateFilterCounts();
            }
            document.getElementById('countdown').textContent = '5';
        })
        .catch(() => { document.getElementById('countdown').textContent = '5'; });
}

function updateStats(st) {
    document.getElementById('s-total').textContent = st.total_active;
    document.getElementById('s-online').textContent = st.online;
    document.getElementById('s-idle').textContent = st.idle || 0;
    document.getElementById('s-offline').textContent = st.offline || 0;
    document.getElementById('s-locked').textContent = st.locked;
    document.getElementById('s-violations').textContent = st.with_violations;
}

function updateFilterCounts() {
    document.getElementById('f-all').textContent = allSessions.length;
    document.getElementById('f-online').textContent = allSessions.filter(s => s.status === 'online').length;
    document.getElementById('f-locked').textContent = allSessions.filter(s => s.is_locked).length;
    document.getElementById('f-violations').textContent = allSessions.filter(s => s.violation_count > 0).length;
    document.getElementById('f-offline').textContent = allSessions.filter(s => s.status === 'offline').length;
}

// ===== Filtered + Sorted data =====
function getFiltered() {
    let data = allSessions;
    if (activeFilter === 'online') data = data.filter(s => s.status === 'online');
    else if (activeFilter === 'locked') data = data.filter(s => s.is_locked);
    else if (activeFilter === 'violations') data = data.filter(s => s.violation_count > 0);
    else if (activeFilter === 'offline') data = data.filter(s => s.status === 'offline');

    const q = document.getElementById('search-input').value.toLowerCase().trim();
    if (q) {
        data = data.filter(s =>
            (s.siswa_nama || '').toLowerCase().includes(q) ||
            (s.siswa_nisn || '').toLowerCase().includes(q) ||
            (s.kelas || '').toLowerCase().includes(q) ||
            (s.device_model || '').toLowerCase().includes(q) ||
            (s.ip_address || '').toLowerCase().includes(q)
        );
    }

    // Sort
    if (sortCol) {
        const statusOrder = {online: 0, idle: 1, offline: 2};
        data = [...data].sort((a, b) => {
            let va, vb;
            if (sortCol === 'status') {
                va = (a.is_locked ? 10 : 0) + (statusOrder[a.status] ?? 9);
                vb = (b.is_locked ? 10 : 0) + (statusOrder[b.status] ?? 9);
            } else if (sortCol === 'violation_count') {
                va = a.violation_count || 0;
                vb = b.violation_count || 0;
            } else if (sortCol === 'started_at_raw' || sortCol === 'last_heartbeat_raw') {
                va = a[sortCol] || '';
                vb = b[sortCol] || '';
            } else {
                va = (a[sortCol] || '').toString().toLowerCase();
                vb = (b[sortCol] || '').toString().toLowerCase();
            }
            if (va < vb) return sortDir === 'asc' ? -1 : 1;
            if (va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });
    }
    return data;
}

// ===== Render =====
function renderTable() {
    const tbody = document.getElementById('tbody-sessions');
    const filtered = getFiltered();
    const showCount = document.getElementById('showing-count');
    showCount.textContent = `Menampilkan ${filtered.length} dari ${allSessions.length} session`;

    if (!allSessions.length) {
        tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-5"><i class="fas fa-desktop fa-3x d-block mb-2"></i>Belum ada siswa yang menggunakan ExaManmet</td></tr>';
        updateBulkBar();
        return;
    }
    if (!filtered.length) {
        tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4"><i class="fas fa-search mr-1"></i>Tidak ditemukan</td></tr>';
        updateBulkBar();
        return;
    }

    let html = '';
    filtered.forEach((s, i) => {
        const init = (s.siswa_nama || '?')[0].toUpperCase();
        const av = s.foto ? `<img src="/storage/${s.foto}" class="av mr-2">` : `<div class="av-ph mr-2">${init}</div>`;
        const stColor = s.status === 'online' ? 'success' : s.status === 'idle' ? 'warning' : 'danger';
        const stLabel = s.status_label || s.status.charAt(0).toUpperCase() + s.status.slice(1);
        const lockIcon = s.is_locked ? ' <span class="lock-badge"><i class="fas fa-lock"></i></span>' : '';
        let rowClass = s.is_locked ? 'row-locked' : '';
        if (selectedIds.has(s.id)) rowClass += ' row-selected';
        const checked = selectedIds.has(s.id) ? 'checked' : '';

        const lockReason = s.is_locked && s.lock_reason
            ? `<div class="text-danger" style="font-size:.68rem"><i class="fas fa-info-circle"></i> ${s.lock_reason.substring(0,35)}${s.lock_reason.length>35?'...':''}</div>` : '';

        let vChip;
        if (s.violation_count >= 3) vChip = `<span class="v-chip danger">${s.violation_count}x</span>`;
        else if (s.violation_count > 0) vChip = `<span class="v-chip warning">${s.violation_count}x</span>`;
        else vChip = '<span class="v-chip ok">0</span>';

        const lockBtn = s.is_locked
            ? `<button class="btn btn-success btn-xs" onclick="unlockSession('${s.id}')" title="Buka Kunci"><i class="fas fa-unlock"></i></button>`
            : `<button class="btn btn-danger btn-xs" onclick="lockSession('${s.id}')" title="Kunci"><i class="fas fa-lock"></i></button>`;

        html += `<tr class="${rowClass}" id="row-${s.id}">
            <td class="chk-cell"><input type="checkbox" ${checked} onchange="toggleRow('${s.id}',this)"></td>
            <td><span class="sd ${s.status}"></span> <small class="text-${stColor}">${stLabel}</small>${lockIcon}</td>
            <td><div class="d-flex align-items-center">${av}<div><div style="line-height:1.2"><strong>${s.siswa_nama}</strong></div>${lockReason}</div></div></td>
            <td><code style="font-size:.75rem">${s.siswa_nisn || '-'}</code></td>
            <td>${s.kelas || '-'}</td>
            <td><small>${s.device_model}</small></td>
            <td><small>${s.ip_address || '-'}</small></td>
            <td><small>${s.started_at || '-'}</small></td>
            <td><small>${s.last_heartbeat || '-'}</small></td>
            <td>${vChip}</td>
            <td>${lockBtn} <button class="btn btn-info btn-xs" onclick="showViolations('${s.id}')" title="Detail"><i class="fas fa-eye"></i></button> <button class="btn btn-secondary btn-xs" onclick="endSession('${s.id}')" title="Akhiri"><i class="fas fa-power-off"></i></button></td>
        </tr>`;
    });
    tbody.innerHTML = html;
    // Update header checkbox state
    const chkAll = document.getElementById('chk-all');
    if (chkAll) {
        const visibleIds = filtered.map(s => s.id);
        const allChecked = visibleIds.length > 0 && visibleIds.every(id => selectedIds.has(id));
        const someChecked = visibleIds.some(id => selectedIds.has(id));
        chkAll.checked = allChecked;
        chkAll.indeterminate = someChecked && !allChecked;
    }
    updateBulkBar();
}

// ===== Filters =====
function setFilter(f, btn) {
    activeFilter = f;
    document.querySelectorAll('#filters .btn').forEach(b => {
        const c = {all:'primary',online:'success',locked:'danger',violations:'warning',offline:'secondary'}[b.dataset.filter];
        b.className = `btn btn-outline-${c} btn-sm`;
    });
    const c = {all:'primary',online:'success',locked:'danger',violations:'warning',offline:'secondary'}[f];
    btn.className = `btn btn-${c} btn-sm active`;
    renderTable();
}

function filterTable() { renderTable(); }

// ===== Sort =====
function sortBy(col, th) {
    if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        sortCol = col;
        sortDir = 'asc';
    }
    // Update header styles
    document.querySelectorAll('#tbl-sessions thead .sortable').forEach(h => {
        h.classList.remove('asc','desc');
        h.querySelector('.sort-icon').className = 'fas fa-sort sort-icon';
    });
    th.classList.add(sortDir);
    th.querySelector('.sort-icon').className = `fas fa-sort-${sortDir === 'asc' ? 'up' : 'down'} sort-icon`;
    renderTable();
}

// ===== Checkbox Selection =====
function toggleAllCheckboxes(chk) {
    const filtered = getFiltered();
    if (chk.checked) {
        filtered.forEach(s => selectedIds.add(s.id));
    } else {
        filtered.forEach(s => selectedIds.delete(s.id));
    }
    renderTable();
}

function toggleRow(id, chk) {
    if (chk.checked) {
        selectedIds.add(id);
    } else {
        selectedIds.delete(id);
    }
    // Update row highlight without full re-render
    const row = document.getElementById('row-' + id);
    if (row) row.classList.toggle('row-selected', chk.checked);
    // Update header checkbox
    const filtered = getFiltered();
    const chkAll = document.getElementById('chk-all');
    const visibleIds = filtered.map(s => s.id);
    const allChecked = visibleIds.length > 0 && visibleIds.every(i => selectedIds.has(i));
    const someChecked = visibleIds.some(i => selectedIds.has(i));
    chkAll.checked = allChecked;
    chkAll.indeterminate = someChecked && !allChecked;
    updateBulkBar();
}

function clearSelection() {
    selectedIds.clear();
    renderTable();
}

function updateBulkBar() {
    const bar = document.getElementById('bulk-bar');
    const count = selectedIds.size;
    document.getElementById('sel-count').textContent = count;
    if (count > 0) {
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

// ===== Bulk Actions for Selected =====
function getSelectedSessions() {
    return allSessions.filter(s => selectedIds.has(s.id));
}

function bulkLockSelected() {
    const sel = getSelectedSessions().filter(s => !s.is_locked);
    if (!sel.length) { toast('Tidak ada yang bisa dikunci dari pilihan','info'); return; }
    Swal.fire({
        title: `Kunci ${sel.length} siswa terpilih?`,
        input: 'text',
        inputLabel: 'Alasan:',
        inputPlaceholder: 'Contoh: Terdeteksi keluar aplikasi',
        inputValue: 'Dikunci oleh pengawas',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: `<i class="fas fa-lock mr-1"></i>Kunci ${sel.length}`,
        inputValidator: v => { if (!v) return 'Alasan wajib diisi!'; }
    }).then(r => {
        if (r.isConfirmed) {
            sel.forEach(s => { s.is_locked = true; s.lock_reason = r.value; });
            renderTable();
            Promise.all(sel.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/lock`, {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                    body: JSON.stringify({reason: r.value})
                })
            )).then(() => { toast(`${sel.length} siswa dikunci (FCM terkirim)`,'success'); selectedIds.clear(); refreshData(); });
        }
    });
}

function bulkUnlockSelected() {
    const sel = getSelectedSessions().filter(s => s.is_locked);
    if (!sel.length) { toast('Tidak ada yang terkunci dari pilihan','info'); return; }
    Swal.fire({
        title: `Buka kunci ${sel.length} siswa terpilih?`,
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#28a745', confirmButtonText: `<i class="fas fa-unlock mr-1"></i>Buka ${sel.length}`
    }).then(r => {
        if (r.isConfirmed) {
            sel.forEach(s => { s.is_locked = false; s.lock_reason = null; });
            renderTable();
            Promise.all(sel.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/unlock`, {method:'POST',headers:{'X-CSRF-TOKEN':csrf}})
            )).then(() => { toast(`${sel.length} siswa dibuka (FCM terkirim)`,'success'); selectedIds.clear(); refreshData(); });
        }
    });
}

function bulkEndSelected() {
    const sel = getSelectedSessions();
    if (!sel.length) { toast('Tidak ada yang dipilih','info'); return; }
    Swal.fire({
        title: `Akhiri ${sel.length} session terpilih?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: `Akhiri ${sel.length}`
    }).then(r => {
        if (r.isConfirmed) {
            Promise.all(sel.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/end`, {method:'POST',headers:{'X-CSRF-TOKEN':csrf}})
            )).then(() => { toast(`${sel.length} session diakhiri`,'info'); selectedIds.clear(); refreshData(); });
        }
    });
}

// ===== Actions =====
function lockSession(id) {
    Swal.fire({
        title: 'Kunci Ujian Siswa?',
        input: 'text',
        inputLabel: 'Alasan:',
        inputPlaceholder: 'Contoh: Terdeteksi keluar aplikasi',
        inputValue: 'Dikunci oleh pengawas',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-lock mr-1"></i>Kunci',
        cancelButtonText: 'Batal',
        inputValidator: v => { if (!v) return 'Alasan wajib diisi!'; }
    }).then(r => {
        if (r.isConfirmed) {
            // Optimistic UI — instant visual feedback
            const s = allSessions.find(x => x.id == id);
            if (s) { s.is_locked = true; s.lock_reason = r.value; renderTable(); }
            fetch(`/admin/exam-monitoring/${id}/lock`, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body: JSON.stringify({reason: r.value})
            }).then(r=>r.json()).then(d => { if(d.success) { toast('Ujian dikunci! (FCM terkirim)','success'); refreshData(); } else { refreshData(); }}).catch(() => refreshData());
        }
    });
}

function unlockSession(id) {
    Swal.fire({
        title: 'Buka Kunci?',
        text: 'Siswa akan melanjutkan ujian.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-unlock mr-1"></i>Buka',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (r.isConfirmed) {
            // Optimistic UI — instant visual feedback
            const s = allSessions.find(x => x.id == id);
            if (s) { s.is_locked = false; s.lock_reason = null; renderTable(); }
            fetch(`/admin/exam-monitoring/${id}/unlock`, {
                method: 'POST', headers: {'X-CSRF-TOKEN':csrf}
            }).then(r=>r.json()).then(d => { if(d.success) { toast('Kunci dibuka! (FCM terkirim)','success'); refreshData(); } else { refreshData(); }}).catch(() => refreshData());
        }
    });
}

function endSession(id) {
    Swal.fire({
        title: 'Akhiri Session?',
        text: 'Session ditutup paksa.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        confirmButtonText: 'Akhiri',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (r.isConfirmed) {
            fetch(`/admin/exam-monitoring/${id}/end`, {
                method: 'POST', headers: {'X-CSRF-TOKEN':csrf}
            }).then(r=>r.json()).then(d => { if(d.success) { toast('Session diakhiri','info'); refreshData(); }});
        }
    });
}

function showViolations(id) {
    document.getElementById('viol-list').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    $('#violModal').modal('show');
    fetch(`/admin/exam-monitoring/${id}/violations`)
        .then(r=>r.json())
        .then(d => {
            if (!d.success) return;
            document.getElementById('viol-name').textContent = d.session.siswa_nama;
            document.getElementById('viol-count').textContent = d.session.violation_count + ' pelanggaran';
            const sess = allSessions.find(s => s.id === id);
            document.getElementById('viol-device').textContent = sess ? sess.device_model : '-';
            document.getElementById('viol-ip').textContent = sess ? (sess.ip_address || '-') : '-';
            document.getElementById('viol-started').textContent = sess ? ('Mulai: ' + (sess.started_at || '-')) : '';

            if (!d.violations.length) {
                document.getElementById('viol-list').innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-check-circle text-success mr-1"></i>Tidak ada pelanggaran</div>';
                return;
            }
            let html = '';
            d.violations.forEach(v => {
                html += `<div class="viol-item ${v.severity_color}">
                    <div class="d-flex justify-content-between">
                        <span class="badge badge-${v.severity_color}">${v.type_label}</span>
                        <small class="text-muted">${v.time} (${v.time_ago})</small>
                    </div>
                    ${v.detail ? `<div class="small text-muted mt-1">${v.detail}</div>` : ''}
                </div>`;
            });
            document.getElementById('viol-list').innerHTML = html;
        });
}

// ===== Bulk Actions =====
function bulkLock() {
    const t = allSessions.filter(s => s.violation_count > 0 && !s.is_locked);
    if (!t.length) { toast('Tidak ada siswa bermasalah','info'); return; }
    Swal.fire({
        title: `Kunci ${t.length} siswa bermasalah?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Kunci Semua'
    }).then(r => {
        if (r.isConfirmed) {
            // Optimistic UI — update local state immediately
            t.forEach(s => { s.is_locked = true; s.lock_reason = 'Kunci massal oleh pengawas'; });
            renderTable();
            Promise.all(t.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/lock`, {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                    body: JSON.stringify({reason:'Kunci massal oleh pengawas'})
                })
            )).then(() => { toast(`${t.length} siswa dikunci (FCM terkirim)`,'success'); refreshData(); });
        }
    });
}

function bulkUnlock() {
    const t = allSessions.filter(s => s.is_locked);
    if (!t.length) { toast('Tidak ada siswa terkunci','info'); return; }
    Swal.fire({
        title: `Buka kunci ${t.length} siswa?`,
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#28a745', confirmButtonText: 'Buka Semua'
    }).then(r => {
        if (r.isConfirmed) {
            // Optimistic UI — update local state immediately
            t.forEach(s => { s.is_locked = false; s.lock_reason = null; });
            renderTable();
            Promise.all(t.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/unlock`, {method:'POST',headers:{'X-CSRF-TOKEN':csrf}})
            )).then(() => { toast(`${t.length} siswa dibuka (FCM terkirim)`,'success'); refreshData(); });
        }
    });
}

function endAllOffline() {
    const t = allSessions.filter(s => s.status === 'offline');
    if (!t.length) { toast('Tidak ada session offline','info'); return; }
    Swal.fire({
        title: `Akhiri ${t.length} session offline?`,
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Akhiri Semua'
    }).then(r => {
        if (r.isConfirmed) {
            Promise.all(t.map(s =>
                fetch(`/admin/exam-monitoring/${s.id}/end`, {method:'POST',headers:{'X-CSRF-TOKEN':csrf}})
            )).then(() => { toast(`${t.length} session diakhiri`,'info'); refreshData(); });
        }
    });
}

function toast(msg, icon) {
    Swal.fire({toast:true, position:'top-end', icon:icon, title:msg, showConfirmButton:false, timer:2000});
}

// ===== Date Filter =====
let currentDateFilter = '{{ $dateFilter }}';

function changeDateFilter(val) {
    currentDateFilter = val;
    // Reload page with date param for server-side initial data
    window.location.href = '{{ route("admin.exam-monitoring.index") }}?date=' + val;
}

// ===== Init =====
document.addEventListener('DOMContentLoaded', () => {
    allSessions = @json($sessionsJson);
    document.getElementById('showing-count').textContent = `Menampilkan ${allSessions.length} session`;
});
</script>
@endsection
