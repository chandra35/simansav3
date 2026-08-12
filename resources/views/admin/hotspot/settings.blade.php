@extends('adminlte::page')

@section('title', 'Setting Hotspot')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-cogs text-primary mr-2"></i>Setting Hotspot</h1></div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.hotspot.index') }}">Hotspot</a></li>
            <li class="breadcrumb-item active">Setting</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="hotspot-settings">
    <div class="card bg-gradient-primary text-white mb-4 hs-setting-hero">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="text-uppercase small font-weight-bold mb-1"><i class="fas fa-network-wired mr-1"></i>Konfigurasi Terpusat</div>
                    <h3 class="mb-1">FreeRADIUS &amp; MikroTik</h3>
                    <p class="mb-0 text-white-75">Kelola koneksi server, profile akses, dan NAS tanpa mencampurkannya dengan data akun Hotspot.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <span class="badge badge-light text-{{ $radiusConnected ? 'success' : 'danger' }} border px-3 py-2">
                        <i class="fas fa-circle mr-1"></i>{{ $radiusConnected ? 'FreeRADIUS terhubung' : 'FreeRADIUS offline' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 col-xl-4 mb-3">
            <a href="{{ route('admin.hotspot.index') }}" class="info-box hs-setting-link mb-0">
                <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                <span class="info-box-content"><span class="info-box-text">Akun Hotspot</span><span class="info-box-number">Kelola akun</span></span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            <a href="{{ route('admin.hotspot.profiles.page') }}" class="info-box hs-setting-link mb-0">
                <span class="info-box-icon bg-info"><i class="fas fa-sliders-h"></i></span>
                <span class="info-box-content"><span class="info-box-text">Profile RADIUS</span><span class="info-box-number">{{ $profiles->count() }} profile</span></span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            <a href="#nasSection" class="info-box hs-setting-link mb-0">
                <span class="info-box-icon bg-primary"><i class="fas fa-router"></i></span>
                <span class="info-box-content"><span class="info-box-text">MikroTik / NAS</span><span class="info-box-number">{{ $nasList->count() }} perangkat</span></span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            <a href="{{ route('admin.hotspot.online') }}" class="info-box hs-setting-link mb-0">
                <span class="info-box-icon bg-success"><i class="fas fa-satellite-dish"></i></span>
                <span class="info-box-content"><span class="info-box-text">Monitoring</span><span class="info-box-number">Status pengguna</span></span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            <a href="{{ route('admin.hotspot.auth-logs') }}" class="info-box hs-setting-link mb-0">
                <span class="info-box-icon bg-warning"><i class="fas fa-clipboard-list"></i></span>
                <span class="info-box-content"><span class="info-box-text">Log Autentikasi</span><span class="info-box-number">Riwayat login</span></span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            @if($radiusDashboardUrl)
                <a href="{{ $radiusDashboardUrl }}" target="_blank" rel="noopener" class="info-box hs-setting-link mb-0">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-external-link-alt"></i></span>
                    <span class="info-box-content"><span class="info-box-text">Dashboard FreeRADIUS</span><span class="info-box-number">Buka dashboard</span></span>
                </a>
            @else
                <div class="info-box mb-0"><span class="info-box-icon bg-secondary"><i class="fas fa-external-link-alt"></i></span><span class="info-box-content"><span class="info-box-text">Dashboard FreeRADIUS</span><span class="text-muted small">URL belum diatur</span></span></div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-server text-primary mr-1"></i> Detail Server FreeRADIUS</h3>
                    <button class="btn btn-sm btn-primary ml-auto" id="btnCopyMikrotik"><i class="fas fa-copy mr-1"></i>Salin Script MikroTik</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach([
                            'RADIUS Host' => $serverInfo['host'],
                            'Auth / Accounting' => $serverInfo['auth_port'].' / '.$serverInfo['acct_port'],
                            'CoA' => $serverInfo['coa_port'],
                            'Database RADIUS' => $serverInfo['database'].':'.$serverInfo['database_port'],
                            'Shared Secret' => $serverInfo['shared_secret_hint'],
                            'Firewall' => 'UDP 1812/1813',
                        ] as $label => $value)
                            <div class="col-md-4 mb-3">
                                <div class="hs-setting-kv"><small>{{ $label }}</small><strong>{{ $value }}</strong></div>
                            </div>
                        @endforeach
                    </div>
                    <pre class="hs-setting-terminal mb-0" id="mikrotikScript">/radius add service=hotspot address={{ $serverInfo['host'] }} secret=&lt;SECRET&gt; authentication-port={{ $serverInfo['auth_port'] }} accounting-port={{ $serverInfo['acct_port'] }} timeout=1000ms
/ip hotspot profile set [find name=&lt;HOTSPOT_PROFILE&gt;] use-radius=yes accounting=yes
/radius incoming set accept=yes port={{ $serverInfo['coa_port'] }}</pre>
                </div>
            </div>

            <div class="card card-outline card-primary" id="nasSection">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-router text-primary mr-1"></i> MikroTik / NAS</h3>
                    <button class="btn btn-sm btn-primary ml-auto" id="btnAddNas"><i class="fas fa-plus mr-1"></i>Tambah NAS</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($nasList as $nas)
                            <div class="col-md-6 mb-3">
                                <div class="hs-nas-card h-100" data-id="{{ $nas->id }}" data-name="{{ e($nas->name) }}" data-nasname="{{ e($nas->nasname) }}" data-shortname="{{ e($nas->shortname) }}" data-type="{{ e($nas->type) }}" data-description="{{ e($nas->description) }}" data-active="{{ $nas->is_active ? 1 : 0 }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div><strong>{{ $nas->name }}</strong><div class="small text-muted">{{ $nas->nasname }} · {{ $nas->shortname ?: '-' }}</div></div>
                                        <span class="badge badge-{{ $nas->sync_status === 'synced' ? 'success' : 'warning' }}">{{ strtoupper($nas->type) }}</span>
                                    </div>
                                    <div class="small text-muted mt-2">Secret: {{ $nas->maskedSecret() }}{{ $nas->description ? ' · '.$nas->description : '' }}</div>
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-secondary btn-edit-nas"><i class="fas fa-edit mr-1"></i>Edit</button>
                                        <button class="btn btn-sm btn-info btn-sync-nas" data-id="{{ $nas->id }}"><i class="fas fa-sync mr-1"></i>Sync</button>
                                        <button class="btn btn-sm btn-danger btn-delete-nas" data-id="{{ $nas->id }}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">Belum ada MikroTik/NAS yang didaftarkan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-info">
                <div class="card-header d-flex align-items-center"><h3 class="card-title font-weight-bold"><i class="fas fa-heartbeat text-info mr-1"></i>Status RADIUS</h3><button class="btn btn-xs btn-secondary ml-auto" id="btnRefreshRadius"><i class="fas fa-sync"></i></button></div>
                <div class="card-body" id="radiusStatusPanel"><div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat status...</div></div>
            </div>
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h text-info mr-1"></i>Profile RADIUS</h3></div>
                <div class="card-body">
                    @forelse($profiles as $profile)
                        <div class="hs-profile-row"><div><strong>{{ $profile->name }}</strong><small>{{ $profile->code }} · {{ $profile->users_count }} akun</small></div><span class="badge badge-{{ $profile->sync_status === 'synced' ? 'success' : 'warning' }}">{{ $profile->sync_status }}</span></div>
                    @empty
                        <div class="text-muted small">Belum ada profile.</div>
                    @endforelse
                    <a href="{{ route('admin.hotspot.profiles.page') }}" class="btn btn-info btn-block mt-3"><i class="fas fa-sliders-h mr-1"></i>Kelola Profile RADIUS</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header bg-primary"><h5 class="modal-title"><i class="fas fa-router mr-2"></i>MikroTik / NAS</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <form id="formNas"><input type="hidden" id="nasId">
                <div class="form-group"><label>Nama Router</label><input id="nasName" class="form-control" maxlength="120" required></div>
                <div class="row"><div class="col-md-7 form-group"><label>IP / NAS Name</label><input id="nasNameIp" class="form-control" maxlength="120" required></div><div class="col-md-5 form-group"><label>Shortname</label><input id="nasShortname" class="form-control" maxlength="60"></div></div>
                <div class="row"><div class="col-md-5 form-group"><label>Type</label><input id="nasType" class="form-control" value="mikrotik" maxlength="40"></div><div class="col-md-7 form-group"><label>Shared Secret</label><input type="password" id="nasSecret" class="form-control" maxlength="255"><small class="text-muted">Wajib saat membuat NAS; kosongkan saat edit jika tidak diganti.</small></div></div>
                <div class="form-group"><label>Catatan</label><textarea id="nasDescription" class="form-control" rows="3" maxlength="1000"></textarea></div>
                <div class="form-check"><input type="checkbox" class="form-check-input" id="nasActive" checked><label class="form-check-label" for="nasActive">Aktif di RADIUS</label></div>
            </form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary" id="btnSaveNas"><i class="fas fa-save mr-1"></i>Simpan</button></div>
    </div></div>
</div>
@endsection

@section('css')
<style>
.hotspot-settings .hs-setting-hero { border-radius: 14px; box-shadow: 0 8px 22px rgba(37,99,235,.18); }
.hotspot-settings .text-white-75 { color: rgba(255,255,255,.8); }
.hotspot-settings .hs-setting-link { color: #1f2937; border: 1px solid #e2e8f0; }
.hotspot-settings .hs-setting-link:hover { color: #1f2937; box-shadow: 0 5px 15px rgba(15,23,42,.1); }
.hotspot-settings .hs-setting-kv { height: 100%; padding: .75rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
.hotspot-settings .hs-setting-kv small { display: block; color: #64748b; font-weight: 700; text-transform: uppercase; }
.hotspot-settings .hs-setting-kv strong { display: block; color: #0f172a; margin-top: .25rem; word-break: break-word; }
.hotspot-settings .hs-setting-terminal { padding: 1rem; border-radius: 8px; background: #0f172a; color: #bfdbfe; white-space: pre-wrap; font-size: .76rem; }
.hotspot-settings .hs-nas-card { padding: 1rem; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
.hotspot-settings .hs-profile-row { display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .65rem 0; border-bottom: 1px solid #e2e8f0; }
.hotspot-settings .hs-profile-row small { display: block; color: #64748b; }
</style>
@endsection

@section('js')
<script>
const HS_SETTING = {
    radiusStatus: @json(route('admin.hotspot.radius-status')),
    nasStore: @json(route('admin.hotspot.nas.store')),
    nasBase: @json(url('admin/hotspot/nas')),
    csrf: @json(csrf_token()),
};

function hsError(xhr, fallback) {
    const errors = xhr.responseJSON?.errors;
    return errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || fallback);
}

$('#btnCopyMikrotik').on('click', async function () {
    try { await navigator.clipboard.writeText($('#mikrotikScript').text().trim()); toastr.success('Script MikroTik disalin.'); }
    catch (e) { toastr.error('Browser tidak mengizinkan penyalinan otomatis.'); }
});

function resetNasForm() {
    $('#formNas')[0].reset(); $('#nasId').val(''); $('#nasType').val('mikrotik'); $('#nasActive').prop('checked', true); $('#nasSecret').prop('required', true);
}
$('#btnAddNas').on('click', function () { resetNasForm(); $('#modalNas').modal('show'); });
$(document).on('click', '.btn-edit-nas', function () {
    const card = $(this).closest('.hs-nas-card'); resetNasForm();
    $('#nasId').val(card.data('id')); $('#nasName').val(card.data('name')); $('#nasNameIp').val(card.data('nasname')); $('#nasShortname').val(card.data('shortname') || ''); $('#nasType').val(card.data('type') || 'mikrotik'); $('#nasDescription').val(card.data('description') || ''); $('#nasActive').prop('checked', String(card.data('active')) === '1'); $('#nasSecret').prop('required', false); $('#modalNas').modal('show');
});
$('#btnSaveNas').on('click', function () {
    const id = $('#nasId').val(); const button = $(this).prop('disabled', true);
    $.ajax({url: id ? `${HS_SETTING.nasBase}/${id}` : HS_SETTING.nasStore, method: id ? 'PUT' : 'POST', data: {name: $('#nasName').val(), nasname: $('#nasNameIp').val(), shortname: $('#nasShortname').val(), type: $('#nasType').val(), secret: $('#nasSecret').val(), description: $('#nasDescription').val(), is_active: $('#nasActive').is(':checked') ? 1 : 0, _token: HS_SETTING.csrf}})
        .done(r => { toastr.success(r.message); location.reload(); }).fail(xhr => toastr.error(hsError(xhr, 'NAS gagal disimpan.'))).always(() => button.prop('disabled', false));
});
$(document).on('click', '.btn-sync-nas', function () {
    const button = $(this).prop('disabled', true); $.post(`${HS_SETTING.nasBase}/${button.data('id')}/sync`, {_token: HS_SETTING.csrf}).done(r => { toastr.success(r.message); location.reload(); }).fail(xhr => toastr.error(hsError(xhr, 'Sync NAS gagal.'))).always(() => button.prop('disabled', false));
});
$(document).on('click', '.btn-delete-nas', function () {
    if (!confirm('Hapus NAS ini dari SIMANSA dan database RADIUS?')) return;
    $.ajax({url: `${HS_SETTING.nasBase}/${$(this).data('id')}`, method: 'DELETE', data: {_token: HS_SETTING.csrf}}).done(r => { toastr.success(r.message); location.reload(); }).fail(xhr => toastr.error(hsError(xhr, 'NAS gagal dihapus.')));
});

function loadRadiusStatus() {
    const panel = $('#radiusStatusPanel').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat status...</div>');
    $.get(HS_SETTING.radiusStatus).done(r => {
        if (!r.connected) return panel.html('<div class="alert alert-danger mb-0">FreeRADIUS tidak dapat dihubungi.</div>');
        const c = r.counts;
        panel.html(`<dl class="row small mb-0"><dt class="col-7">Akun RADIUS</dt><dd class="col-5 text-right">${c.radcheck}</dd><dt class="col-7">Group pengguna</dt><dd class="col-5 text-right">${c.radusergroup}</dd><dt class="col-7">NAS</dt><dd class="col-5 text-right">${c.nas ?? '-'}</dd><dt class="col-7">Online</dt><dd class="col-5 text-right text-success font-weight-bold">${c.radacct_active}</dd><dt class="col-7">Auth hari ini</dt><dd class="col-5 text-right">${c.radpostauth_today}</dd></dl>`);
    }).fail(() => panel.html('<div class="alert alert-warning mb-0">Status RADIUS belum dapat dimuat.</div>'));
}
$('#btnRefreshRadius').on('click', loadRadiusStatus); loadRadiusStatus();
</script>
@endsection
