@extends('adminlte::page')

@section('title', 'Hotspot Manager - SIMANSA')

@section('css')
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
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9999; align-items: center; justify-content: center;
}
#syncOverlay.show { display: flex; }
.sync-modal {
    background: #fff; border-radius: 18px; padding: 2rem 2.5rem;
    max-width: 520px; width: 90%; text-align: center;
    box-shadow: 0 24px 60px rgba(0,0,0,.2);
}
.sync-spinner { font-size: 2.5rem; color: #2563eb; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.sync-output {
    text-align: left; background: #0f172a; color: #94a3b8;
    border-radius: 10px; padding: .8rem 1rem; font-family: monospace;
    font-size: .75rem; max-height: 240px; overflow-y: auto; margin-top: 1rem;
    display: none;
}

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
        @if($radiusConnected)
            <span class="hs-radius-badge ok">
                <span class="dot"></span> FreeRADIUS Terhubung
            </span>
        @else
            <span class="hs-radius-badge err">
                <span class="dot"></span> FreeRADIUS Offline
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
    <div class="hs-stat hs-stat--guru">
        <div class="hs-stat__icon">👨‍🏫</div>
        <div class="hs-stat__val">{{ $stats['guru'] }}</div>
        <div class="hs-stat__label">Guru/GTK</div>
    </div>
    <div class="hs-stat hs-stat--siswa">
        <div class="hs-stat__icon">👨‍🎓</div>
        <div class="hs-stat__val">{{ $stats['siswa'] }}</div>
        <div class="hs-stat__label">Siswa</div>
    </div>
    <div class="hs-stat hs-stat--tamu">
        <div class="hs-stat__icon">🧑‍💼</div>
        <div class="hs-stat__val">{{ $stats['tamu'] }}</div>
        <div class="hs-stat__label">Tamu</div>
    </div>
    <div class="hs-stat hs-stat--online">
        <div class="hs-stat__icon"><span class="pulse">🟢</span></div>
        <div class="hs-stat__val" id="statOnline">{{ $stats['online'] }}</div>
        <div class="hs-stat__label">Online Sekarang</div>
    </div>
    <div class="hs-stat hs-stat--err">
        <div class="hs-stat__icon">⚠️</div>
        <div class="hs-stat__val">{{ $stats['error_sync'] }}</div>
        <div class="hs-stat__label">Error Sync</div>
    </div>
    <div class="hs-stat hs-stat--pend">
        <div class="hs-stat__icon">⏳</div>
        <div class="hs-stat__val">{{ $stats['pending_sync'] }}</div>
        <div class="hs-stat__label">Pending Sync</div>
    </div>
    <div class="hs-stat">
        <div class="hs-stat__icon">✅</div>
        <div class="hs-stat__val">{{ $stats['aktif'] }}</div>
        <div class="hs-stat__label">Akun Aktif</div>
    </div>
    <div class="hs-stat">
        <div class="hs-stat__icon">🚫</div>
        <div class="hs-stat__val">{{ $stats['nonaktif'] }}</div>
        <div class="hs-stat__label">Nonaktif</div>
    </div>
</div>

{{-- Main Panel ----------------------------------------------------------- --}}
<div class="row">
    <div class="col-lg-8">
        <div class="hs-panel">
            <div class="hs-panel__header">
                <span class="hs-panel__title"><i class="fas fa-users mr-1 text-primary"></i>Daftar Akun Hotspot</span>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="searchBox" class="form-control form-control-sm" placeholder="Cari username / nama..." style="width:200px">
                    <select id="filterRole" class="form-control form-control-sm" style="width:110px">
                        <option value="">Semua Role</option>
                        <option value="guru">Guru</option>
                        <option value="siswa">Siswa</option>
                        <option value="tamu">Tamu</option>
                    </select>
                    <select id="filterSync" class="form-control form-control-sm" style="width:110px">
                        <option value="">Semua Status</option>
                        <option value="synced">Synced</option>
                        <option value="pending">Pending</option>
                        <option value="error">Error</option>
                    </select>
                </div>
            </div>
            <div class="hs-panel__body" style="padding:.5rem">
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
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Role</th>
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
                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sync Overlay --------------------------------------------------------- --}}
<div id="syncOverlay">
    <div class="sync-modal">
        <div class="sync-spinner"><i class="fas fa-sync fa-spin"></i></div>
        <h5 class="mt-3 mb-1 font-weight-bold">Sinkronisasi Berjalan...</h5>
        <p class="text-muted small mb-2">Harap tunggu, jangan tutup halaman ini.</p>
        <div class="sync-output" id="syncOutput"></div>
        <button class="btn btn-secondary btn-sm mt-3" id="btnCloseSyncOverlay" style="display:none">Tutup</button>
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
<script>
const ROUTES = {
    data:        '{{ route("admin.hotspot.data") }}',
    sync:        '{{ route("admin.hotspot.sync") }}',
    radiusStatus:'{{ route("admin.hotspot.radius-status") }}',
    tamuStore:   '{{ route("admin.hotspot.tamu.store") }}',
    tamuUpdate:  (id) => `{{ url("admin/hotspot/tamu") }}/${id}`,
    tamuDestroy: (id) => `{{ url("admin/hotspot/tamu") }}/${id}`,
    syncSingle:  (id) => `{{ url("admin/hotspot/sync") }}/${id}`,
    toggleActive:(id) => `{{ url("admin/hotspot") }}/${id}/toggle-active`,
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
            }
        },
        columns: [
            { data: 'username',     name: 'username' },
            { data: 'display_name', name: 'display_name' },
            { data: 'role_badge',   name: 'role', orderable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'sync_badge',   name: 'sync_status', orderable: false },
            { data: 'actions',      name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        pageLength: 20,
        order: [[2, 'asc']],
        dom: 'tip',
    });

    // Filter bindings
    $('#filterRole, #filterSync').on('change', () => table.ajax.reload());
    $('#searchBox').on('keyup', debounce(() => table.ajax.reload(), 400));
});

// ── Active filter buttons ─────────────────────────────────────────────────
let activeFilter = '';
$('.filter-active').on('click', function () {
    $('.filter-active').removeClass('active');
    $(this).addClass('active');
    activeFilter = $(this).data('active');
    table.ajax.reload();
});

// ── Sync overlay ──────────────────────────────────────────────────────────
function doSync(role = '', force = false) {
    $('#syncOverlay').addClass('show');
    $('#syncOutput').hide().html('');
    $('#btnCloseSyncOverlay').hide();

    $.post(ROUTES.sync, { role, force: force ? 1 : 0, _token: '{{ csrf_token() }}' })
        .done(r => {
            $('#syncOutput').html(r.output).show();
            toastr.success(r.message);
            table.ajax.reload();
            loadRadiusStatus();
        })
        .fail(() => toastr.error('Sync gagal.'))
        .always(() => {
            $('#btnCloseSyncOverlay').show();
            $('.sync-spinner').css('display', 'none');
        });
}

$('#btnSyncAll').on('click', () => doSync());
$('.btn-sync-role').on('click', function () {
    doSync($(this).data('role'), $(this).data('force') == 1);
});
$('#btnSyncErrors').on('click', () => doSync('', true));
$('#btnCloseSyncOverlay').on('click', () => {
    $('#syncOverlay').removeClass('show');
    $('.sync-spinner').css('display', '');
});

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
function loadRadiusStatus() {
    $.get(ROUTES.radiusStatus).done(r => {
        if (!r.connected) {
            $('#radiusStatusPanel').html(`
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="small mb-0">Tidak dapat terhubung ke RADIUS</p>
                    <small class="text-muted">${r.error || ''}</small>
                </div>`);
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

        $('#radiusStatusPanel').html(`
            <div class="radius-live mb-2">
                <div class="radius-live__row"><span>Akun di RADIUS</span><span class="radius-live__val">${c.radcheck}</span></div>
                <div class="radius-live__row"><span>User groups</span><span class="radius-live__val">${c.radusergroup}</span></div>
                <div class="radius-live__row"><span>Online saat ini</span><span class="radius-live__val" style="color:#4ade80">${c.radacct_active}</span></div>
                <div class="radius-live__row"><span>Auth hari ini</span><span class="radius-live__val">${c.radpostauth_today}</span></div>
            </div>
            <div class="mb-1" style="font-size:.72rem;font-weight:700;color:#64748b;letter-spacing:.05em;text-transform:uppercase">AUTH TERBARU</div>
            <div class="radius-live" style="padding:.5rem">${authRows || '<div class="text-muted text-center py-2" style="font-size:.75rem">Belum ada data</div>'}</div>
        `);
    }).fail(() => {
        $('#radiusStatusPanel').html('<div class="text-danger small text-center py-2">Gagal load.</div>');
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
