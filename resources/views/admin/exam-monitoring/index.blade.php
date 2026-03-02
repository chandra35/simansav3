@extends('adminlte::page')

@section('title', 'Monitoring Ujian - ExaManmet')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .stats-card {
        border-radius: 10px;
        padding: 15px 20px;
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .stats-card .stats-number { font-size: 2rem; font-weight: 700; line-height: 1; }
    .stats-card .stats-label { font-size: 0.8rem; opacity: 0.9; margin-top: 4px; }
    .stats-online { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .stats-total { background: linear-gradient(135deg, #007bff, #0056b3); }
    .stats-locked { background: linear-gradient(135deg, #dc3545, #bd2130); }
    .stats-violations { background: linear-gradient(135deg, #ffc107, #d39e00); }

    .session-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        transition: all 0.3s;
        margin-bottom: 12px;
    }
    .session-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .session-card.locked { border-left: 4px solid #dc3545; background: #fff5f5; }
    .session-card.has-violations { border-left: 4px solid #ffc107; }

    .status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.online { background: #28a745; box-shadow: 0 0 6px rgba(40,167,69,0.5); animation: pulse 2s infinite; }
    .status-dot.idle { background: #ffc107; }
    .status-dot.offline { background: #dc3545; }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .violation-badge {
        background: linear-gradient(135deg, #dc3545, #bd2130);
        color: #fff;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .violation-badge.warning {
        background: linear-gradient(135deg, #ffc107, #d39e00);
        color: #000;
    }

    .btn-lock {
        border-radius: 20px;
        font-size: 0.78rem;
        padding: 4px 14px;
        font-weight: 600;
    }

    .auto-refresh-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6c757d;
        font-size: 0.8rem;
    }
    .auto-refresh-indicator .spinner-grow {
        width: 8px; height: 8px;
    }

    .siswa-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }
    .siswa-avatar-placeholder {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c757d, #495057);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .device-info { font-size: 0.75rem; color: #6c757d; }

    .violation-timeline { max-height: 400px; overflow-y: auto; }
    .violation-item {
        padding: 8px 12px;
        border-left: 3px solid #dee2e6;
        margin-bottom: 8px;
        background: #f8f9fa;
        border-radius: 0 6px 6px 0;
    }
    .violation-item.danger { border-left-color: #dc3545; }
    .violation-item.warning { border-left-color: #ffc107; }
    .violation-item.info { border-left-color: #17a2b8; }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #adb5bd;
    }
    .empty-state i { font-size: 4rem; margin-bottom: 15px; }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="fas fa-tv mr-2 text-primary"></i>Monitoring Ujian</h1>
        <small class="text-muted">Pantau aktivitas siswa secara real-time</small>
    </div>
    <div class="auto-refresh-indicator">
        <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
        Auto-refresh <span id="countdown">10</span>s
    </div>
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-2">
        <div class="stats-card stats-total">
            <div class="stats-number" id="stat-total">{{ $stats['total_active'] }}</div>
            <div class="stats-label"><i class="fas fa-users mr-1"></i>Total Aktif</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="stats-card stats-online">
            <div class="stats-number" id="stat-online">{{ $stats['online'] }}</div>
            <div class="stats-label"><i class="fas fa-circle mr-1"></i>Online</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="stats-card stats-locked">
            <div class="stats-number" id="stat-locked">{{ $stats['locked'] }}</div>
            <div class="stats-label"><i class="fas fa-lock mr-1"></i>Terkunci</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="stats-card stats-violations">
            <div class="stats-number" id="stat-violations">{{ $stats['with_violations'] }}</div>
            <div class="stats-label"><i class="fas fa-exclamation-triangle mr-1"></i>Bermasalah</div>
        </div>
    </div>
</div>

{{-- Session List --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Daftar Peserta Ujian</h5>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0" id="sessions-container">
        @forelse ($activeSessions as $session)
        <div class="session-card p-3 mx-3 mt-3 {{ $session->is_locked ? 'locked' : ($session->violation_count > 0 ? 'has-violations' : '') }}"
             id="session-{{ $session->id }}">
            <div class="d-flex align-items-center">
                {{-- Avatar --}}
                <div class="mr-3">
                    @if($session->siswa?->foto_profile)
                        <img src="{{ asset('storage/' . $session->siswa->foto_profile) }}" class="siswa-avatar" alt="">
                    @else
                        <div class="siswa-avatar-placeholder">
                            {{ strtoupper(substr($session->siswa?->nama_lengkap ?? $session->moodle_fullname ?? '?', 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <span class="status-dot {{ $session->status }}"></span>
                        <strong>{{ $session->siswa?->nama_lengkap ?? $session->moodle_fullname ?? $session->moodle_username ?? 'Unknown' }}</strong>
                        @if($session->is_locked)
                            <span class="badge badge-danger ml-2"><i class="fas fa-lock mr-1"></i>DIKUNCI</span>
                        @endif
                        @if($session->violation_count > 0)
                            <span class="violation-badge {{ $session->violation_count >= 3 ? '' : 'warning' }} ml-2">
                                {{ $session->violation_count }} pelanggaran
                            </span>
                        @endif
                    </div>
                    <div class="device-info mt-1">
                        <span class="mr-3"><i class="fas fa-id-card mr-1"></i>{{ $session->siswa?->nisn ?? $session->moodle_username ?? '-' }}</span>
                        <span class="mr-3"><i class="fas fa-school mr-1"></i>{{ $session->siswa?->kelasSaatIni?->nama_kelas ?? '-' }}</span>
                        <span class="mr-3"><i class="fas fa-mobile-alt mr-1"></i>{{ $session->device_model ?? '-' }}</span>
                        <span class="mr-3"><i class="fas fa-clock mr-1"></i>{{ $session->last_heartbeat?->diffForHumans() }}</span>
                        <span><i class="fas fa-globe mr-1"></i>{{ $session->ip_address ?? '-' }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="ml-3 d-flex gap-1">
                    @if($session->is_locked)
                        <button class="btn btn-success btn-lock btn-sm" onclick="unlockSession('{{ $session->id }}')">
                            <i class="fas fa-unlock mr-1"></i>Buka
                        </button>
                    @else
                        <button class="btn btn-danger btn-lock btn-sm" onclick="lockSession('{{ $session->id }}')">
                            <i class="fas fa-lock mr-1"></i>Kunci
                        </button>
                    @endif
                    <button class="btn btn-info btn-lock btn-sm ml-1" onclick="showViolations('{{ $session->id }}')">
                        <i class="fas fa-eye mr-1"></i>Detail
                    </button>
                </div>
            </div>

            @if($session->is_locked && $session->lock_reason)
                <div class="mt-2 small text-danger">
                    <i class="fas fa-info-circle mr-1"></i>{{ $session->lock_reason }}
                </div>
            @endif
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-desktop"></i>
            <h5>Belum Ada Siswa Ujian</h5>
            <p>Siswa yang sedang menggunakan aplikasi ExaManmet akan muncul di sini secara otomatis.</p>
        </div>
        @endforelse
        <div class="mb-3"></div>
    </div>
</div>

{{-- Violation Detail Modal --}}
<div class="modal fade" id="violationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Detail Pelanggaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 id="violation-siswa-name" class="mb-0"></h6>
                    <span id="violation-count-badge" class="badge badge-danger"></span>
                </div>
                <div class="violation-timeline" id="violation-list">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Memuat...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let refreshInterval;
let countdownInterval;
let countdown = 10;

// Auto-refresh every 10 seconds
function startAutoRefresh() {
    countdown = 10;
    countdownInterval = setInterval(() => {
        countdown--;
        document.getElementById('countdown').textContent = countdown;
        if (countdown <= 0) {
            countdown = 10;
            refreshData();
        }
    }, 1000);
}

function refreshData() {
    countdown = 10;
    document.getElementById('countdown').textContent = '...';

    fetch('{{ route("admin.exam-monitoring.api.sessions") }}')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateStats(data.stats);
                updateSessions(data.sessions);
            }
            document.getElementById('countdown').textContent = '10';
        })
        .catch(() => document.getElementById('countdown').textContent = '10');
}

function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total_active;
    document.getElementById('stat-online').textContent = stats.online;
    document.getElementById('stat-locked').textContent = stats.locked;
    document.getElementById('stat-violations').textContent = stats.with_violations;
}

function updateSessions(sessions) {
    const container = document.getElementById('sessions-container');

    if (sessions.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-desktop"></i>
                <h5>Belum Ada Siswa Ujian</h5>
                <p>Siswa yang sedang menggunakan aplikasi ExaManmet akan muncul di sini secara otomatis.</p>
            </div>`;
        return;
    }

    let html = '';
    sessions.forEach(s => {
        const cardClass = s.is_locked ? 'locked' : (s.violation_count > 0 ? 'has-violations' : '');
        const initial = (s.siswa_nama || '?')[0].toUpperCase();
        const avatar = s.foto
            ? `<img src="/storage/${s.foto}" class="siswa-avatar" alt="">`
            : `<div class="siswa-avatar-placeholder">${initial}</div>`;

        const lockBadge = s.is_locked ? '<span class="badge badge-danger ml-2"><i class="fas fa-lock mr-1"></i>DIKUNCI</span>' : '';
        const violBadge = s.violation_count > 0
            ? `<span class="violation-badge ${s.violation_count >= 3 ? '' : 'warning'} ml-2">${s.violation_count} pelanggaran</span>`
            : '';

        const lockBtn = s.is_locked
            ? `<button class="btn btn-success btn-lock btn-sm" onclick="unlockSession('${s.id}')"><i class="fas fa-unlock mr-1"></i>Buka</button>`
            : `<button class="btn btn-danger btn-lock btn-sm" onclick="lockSession('${s.id}')"><i class="fas fa-lock mr-1"></i>Kunci</button>`;

        const lockReason = s.is_locked && s.lock_reason
            ? `<div class="mt-2 small text-danger"><i class="fas fa-info-circle mr-1"></i>${s.lock_reason}</div>`
            : '';

        html += `
        <div class="session-card p-3 mx-3 mt-3 ${cardClass}" id="session-${s.id}">
            <div class="d-flex align-items-center">
                <div class="mr-3">${avatar}</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <span class="status-dot ${s.status}"></span>
                        <strong>${s.siswa_nama}</strong>
                        ${lockBadge}${violBadge}
                    </div>
                    <div class="device-info mt-1">
                        <span class="mr-3"><i class="fas fa-id-card mr-1"></i>${s.siswa_nisn || '-'}</span>
                        <span class="mr-3"><i class="fas fa-school mr-1"></i>${s.kelas || '-'}</span>
                        <span class="mr-3"><i class="fas fa-mobile-alt mr-1"></i>${s.device_model}</span>
                        <span class="mr-3"><i class="fas fa-clock mr-1"></i>${s.last_heartbeat}</span>
                        <span><i class="fas fa-globe mr-1"></i>${s.ip_address || '-'}</span>
                    </div>
                </div>
                <div class="ml-3 d-flex">
                    ${lockBtn}
                    <button class="btn btn-info btn-lock btn-sm ml-1" onclick="showViolations('${s.id}')">
                        <i class="fas fa-eye mr-1"></i>Detail
                    </button>
                </div>
            </div>
            ${lockReason}
        </div>`;
    });
    html += '<div class="mb-3"></div>';
    container.innerHTML = html;
}

function lockSession(sessionId) {
    Swal.fire({
        title: 'Kunci Ujian Siswa?',
        input: 'text',
        inputLabel: 'Alasan penguncian:',
        inputPlaceholder: 'Contoh: Terdeteksi keluar aplikasi 3x',
        inputValue: 'Dikunci oleh pengawas',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-lock mr-1"></i>Kunci',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) return 'Alasan wajib diisi!';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/exam-monitoring/${sessionId}/lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', 'Ujian siswa telah dikunci.', 'success');
                    refreshData();
                }
            });
        }
    });
}

function unlockSession(sessionId) {
    Swal.fire({
        title: 'Buka Kunci Ujian?',
        text: 'Siswa akan bisa melanjutkan ujian kembali.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-unlock mr-1"></i>Buka Kunci',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/exam-monitoring/${sessionId}/unlock`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', 'Ujian siswa telah dibuka.', 'success');
                    refreshData();
                }
            });
        }
    });
}

function showViolations(sessionId) {
    document.getElementById('violation-list').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</div>';
    $('#violationModal').modal('show');

    fetch(`/admin/exam-monitoring/${sessionId}/violations`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('violation-siswa-name').textContent = data.session.siswa_nama;
                document.getElementById('violation-count-badge').textContent = data.session.violation_count + ' pelanggaran';

                if (data.violations.length === 0) {
                    document.getElementById('violation-list').innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-check-circle mr-1 text-success"></i>Tidak ada pelanggaran tercatat.</div>';
                    return;
                }

                let html = '';
                data.violations.forEach(v => {
                    html += `
                    <div class="violation-item ${v.severity_color}">
                        <div class="d-flex justify-content-between">
                            <span><span class="badge badge-${v.severity_color}">${v.type_label}</span></span>
                            <small class="text-muted">${v.time} (${v.time_ago})</small>
                        </div>
                        ${v.detail ? `<div class="small text-muted mt-1">${v.detail}</div>` : ''}
                    </div>`;
                });
                document.getElementById('violation-list').innerHTML = html;
            }
        });
}

// Start auto-refresh on page load
document.addEventListener('DOMContentLoaded', startAutoRefresh);
</script>
@endsection
