@extends('adminlte::page')

@section('title', 'Permission Matrix - SIMANSA')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-shield-alt"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Permission Matrix</h1>
            <p class="simansa-hero__subtitle">Atur izin per role secara menyeluruh dari satu layar. Cocok untuk audit cepat, sinkronisasi permission baru, dan koreksi akses lintas modul.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-toolbar__group">
                <button type="button" class="btn simansa-btn-contrast" id="btnScan" title="Scan Permission">
                    <i class="fas fa-search mr-1"></i> Scan
                </button>
                <button type="button" class="btn simansa-btn-strong" id="btnAddRole" title="Tambah Role">
                    <i class="fas fa-plus mr-1"></i> Tambah Role
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="simansa-stat-card simansa-stat-card--blue">
            <div class="simansa-stat-card__icon"><i class="fas fa-user-tag"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__value">{{ $roles->count() }}</div>
                <div class="simansa-stat-card__label">Total Role</div>
                <div class="simansa-stat-card__desc">Role aktif yang bisa diatur dari matrix</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="simansa-stat-card simansa-stat-card--teal">
            <div class="simansa-stat-card__icon"><i class="fas fa-key"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__value">{{ $totalPermissions }}</div>
                <div class="simansa-stat-card__label">Permission Terdaftar</div>
                <div class="simansa-stat-card__desc">Permission yang sudah aktif di database</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="simansa-stat-card simansa-stat-card--indigo">
            <div class="simansa-stat-card__icon"><i class="fas fa-cubes"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__value">{{ count($moduleDefinitions) }}</div>
                <div class="simansa-stat-card__label">Modul Fitur</div>
                <div class="simansa-stat-card__desc">Kelompok fitur yang dipakai untuk audit akses</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Sidebar: Roles -->
    <div class="col-md-3">
        <div class="card simansa-management-card sticky-top permission-role-panel" style="top: 10px; z-index: 100;">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <h3 class="card-title mb-0"><i class="fas fa-user-tag mr-1"></i> Roles</h3>
                    <span class="badge badge-light">{{ $roles->count() }}</span>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="role-list-stack" id="roleList">
                    @foreach($roles as $role)
                    <div class="role-item {{ $role->name === 'Super Admin' ? 'bg-warning-subtle role-item--super' : '' }}" 
                         data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="role-item__content">
                                <strong class="role-item__title">{{ $role->name }}</strong>
                                @if($role->name === 'Super Admin')
                                <i class="fas fa-crown text-warning ml-1" title="Protected"></i>
                                @endif
                                <div class="role-item__meta">
                                    <span><i class="fas fa-users mr-1"></i>{{ $role->users_count }} users</span>
                                    <span><i class="fas fa-key mr-1"></i>{{ $role->permissions_count ?? (isset($permissionMatrix[$role->id]) ? count(array_filter($permissionMatrix[$role->id])) : 0) }}</span>
                                </div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                @if($role->name !== 'Super Admin')
                                <button type="button" class="btn btn-outline-success btn-xs btn-role-grant-all" 
                                        data-role-id="{{ $role->id }}" title="Grant All">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs btn-role-revoke-all" 
                                        data-role-id="{{ $role->id }}" title="Revoke All">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Super Admin memiliki semua akses
                </small>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="card simansa-surface-card mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Statistik</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Roles:</span>
                    <strong>{{ $roles->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Permissions:</span>
                    <strong>{{ $totalPermissions }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Users:</span>
                    <strong>{{ $totalUsers }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Modules:</span>
                    <strong>{{ count($moduleDefinitions) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Permission Matrix -->
    <div class="col-md-9">
        <div class="card simansa-management-card">
            <div class="card-header">
                <div class="simansa-toolbar permission-matrix-toolbar">
                    <div class="permission-matrix-toolbar__intro">
                        <h3 class="card-title mb-0"><i class="fas fa-th mr-1"></i> Permission per Module</h3>
                        <div class="simansa-card-subtitle">Audit akses per fitur, lengkap dengan status registration permission di database.</div>
                    </div>
                    <div class="simansa-toolbar__group permission-matrix-toolbar__actions">
                    <button class="btn btn-sm simansa-btn-contrast" id="expandAll">
                        <i class="fas fa-expand-alt"></i> Expand All
                    </button>
                    <button class="btn btn-sm simansa-btn-muted" id="collapseAll">
                        <i class="fas fa-compress-alt"></i> Collapse All
                    </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="accordion" id="permissionAccordion">
                    @foreach($moduleDefinitions as $moduleKey => $module)
                    @php
                        $modulePermissions = $module['permissions'];
                        $registeredCount = 0;
                        foreach($modulePermissions as $perm) {
                            if(in_array($perm, $allPermissions)) $registeredCount++;
                        }
                    @endphp
                    <div class="card mb-1 module-card" data-module="{{ $moduleKey }}">
                        <div class="card-header module-card__header" id="heading{{ $moduleKey }}">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <button class="btn btn-link text-left p-0 module-toggle" type="button" 
                                        data-toggle="collapse" data-target="#collapse{{ $moduleKey }}"
                                        aria-expanded="false" aria-controls="collapse{{ $moduleKey }}">
                                    <span class="module-toggle__content">
                                        <span class="module-toggle__icon module-toggle__icon--{{ $module['color'] ?? 'primary' }}">
                                            <i class="fas fa-{{ $module['icon'] ?? 'cube' }}"></i>
                                        </span>
                                        <span>
                                            <strong>{{ $module['label'] }}</strong>
                                            <span class="module-toggle__meta">
                                                {{ $registeredCount }}/{{ count($modulePermissions) }} permission terdaftar
                                                @if(!empty($module['description']))
                                                    | {{ $module['description'] }}
                                                @endif
                                            </span>
                                        </span>
                                    </span>
                                </button>
                                <div class="module-card__tools">
                                    <span class="module-card__badge badge badge-{{ $registeredCount == count($modulePermissions) ? 'success' : 'warning' }}">
                                        {{ $registeredCount == count($modulePermissions) ? 'Lengkap' : 'Perlu Sync' }}
                                    </span>
                                    <div class="btn-group module-card__action-group">
                                    <button type="button" class="btn btn-module-action btn-module-action--grant btn-module-grant-all" 
                                            data-module="{{ $moduleKey }}" title="Grant All for this Module">
                                        <i class="fas fa-check"></i><span class="ml-1">Grant</span>
                                    </button>
                                    <button type="button" class="btn btn-module-action btn-module-action--revoke btn-module-revoke-all" 
                                            data-module="{{ $moduleKey }}" title="Revoke All for this Module">
                                        <i class="fas fa-times"></i><span class="ml-1">Revoke</span>
                                    </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="collapse{{ $moduleKey }}" class="collapse" data-parent="#permissionAccordion">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover table-striped mb-0 permission-table">
                                        <thead class="bg-gradient-secondary">
                                            <tr>
                                                <th style="min-width: 200px;">Permission</th>
                                                @foreach($roles as $role)
                                                <th class="text-center" style="min-width: 80px;">
                                                    <small>{{ Str::limit($role->name, 10) }}</small>
                                                </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modulePermissions as $permName)
                                            @php
                                                $isRegistered = in_array($permName, $allPermissions);
                                            @endphp
                                            <tr class="{{ !$isRegistered ? 'table-warning' : '' }}">
                                                <td>
                                                    <code class="text-xs {{ !$isRegistered ? 'text-danger' : '' }}">{{ $permName }}</code>
                                                    @if(!$isRegistered)
                                                    <span class="badge badge-warning badge-sm ml-1" title="Not registered">!</span>
                                                    @endif
                                                </td>
                                                @foreach($roles as $role)
                                                <td class="text-center p-1">
                                                    @if($role->name === 'Super Admin')
                                                    <span class="super-admin-cell" title="Super Admin has all permissions">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                    @elseif($isRegistered)
                                                    @php
                                                        $hasPermission = isset($permissionMatrix[$role->id][$permName]) && $permissionMatrix[$role->id][$permName];
                                                    @endphp
                                                    <div class="custom-control custom-checkbox d-inline">
                                                        <input type="checkbox" 
                                                               class="custom-control-input permission-checkbox" 
                                                               id="perm_{{ $role->id }}_{{ md5($permName) }}"
                                                               data-role-id="{{ $role->id }}"
                                                               data-permission="{{ $permName }}"
                                                               {{ $hasPermission ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $role->id }}_{{ md5($permName) }}"></label>
                                                    </div>
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div id="floatingSaveBtn" class="floating-save-btn d-none">
    <button type="button" class="btn btn-lg btn-success shadow-lg" id="btnSaveChanges">
        <i class="fas fa-save mr-2"></i> Simpan Perubahan
        <span class="badge badge-light ml-2" id="changeCount">0</span>
    </button>
</div>

<!-- Scan Modal -->
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-search mr-2"></i> Scan Permissions</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body scan-modal-body">
                <div id="scanLoading" class="scan-loading-state">
                    <div class="scan-loading-state__spinner">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="scan-loading-state__content">
                        <h6>Memindai permission sistem</h6>
                        <p>Menelusuri route, menu, dan katalog fitur untuk menemukan permission yang belum sinkron.</p>
                        <div class="scan-progress">
                            <div class="scan-progress__bar"></div>
                        </div>
                        <div class="scan-loading-state__steps">
                            <span><i class="fas fa-route"></i> Route</span>
                            <span><i class="fas fa-stream"></i> Menu</span>
                            <span><i class="fas fa-layer-group"></i> Katalog</span>
                        </div>
                    </div>
                </div>
                <div id="scanResults" class="d-none">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Ditemukan <strong id="totalUnregistered">0</strong> permission yang belum terdaftar
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <div class="simansa-mini-stat">
                                <span class="simansa-mini-stat__label">Terdaftar</span>
                                <span class="simansa-mini-stat__value" id="scanRegisteredTotal">0</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="simansa-mini-stat">
                                <span class="simansa-mini-stat__label">Katalog Modul</span>
                                <span class="simansa-mini-stat__value" id="scanCatalogTotal">0</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="simansa-mini-stat">
                                <span class="simansa-mini-stat__label">Shared Menu</span>
                                <span class="simansa-mini-stat__value" id="scanSharedMenuTotal">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="list-group" id="unregisteredList">
                        <!-- Populated by JS -->
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-warning mb-2">
                            <i class="fas fa-layer-group mr-2"></i>
                            Permission yang sudah terdaftar tetapi belum masuk katalog fitur
                        </div>
                        <div class="list-group" id="uncataloguedList"></div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-secondary mb-2">
                            <i class="fas fa-project-diagram mr-2"></i>
                            Menu yang masih berbagi permission umum
                        </div>
                        <div class="list-group" id="sharedPermissionList"></div>
                    </div>
                </div>
                <div id="syncLoading" class="scan-loading-state d-none">
                    <div class="scan-loading-state__spinner scan-loading-state__spinner--sync">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="scan-loading-state__content">
                        <h6>Menyinkronkan permission</h6>
                        <p>Menambahkan permission dari katalog modul ke database dan menyegarkan cache akses sistem.</p>
                        <div class="scan-progress scan-progress--sync">
                            <div class="scan-progress__bar"></div>
                        </div>
                        <div class="scan-loading-state__steps">
                            <span><i class="fas fa-layer-group"></i> Katalog Modul</span>
                            <span><i class="fas fa-database"></i> Database</span>
                            <span><i class="fas fa-bolt"></i> Cache Permission</span>
                        </div>
                    </div>
                </div>
                <div id="syncSuccess" class="scan-loading-state scan-loading-state--success d-none">
                    <div class="scan-loading-state__spinner scan-loading-state__spinner--success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="scan-loading-state__content">
                        <h6>Sinkronisasi berhasil</h6>
                        <p id="syncSuccessMessage">Permission berhasil disinkronkan dan cache akses sudah diperbarui.</p>
                        <div class="row mb-2">
                            <div class="col-sm-6 mb-2">
                                <div class="simansa-mini-stat">
                                    <span class="simansa-mini-stat__label">Permission Baru</span>
                                    <span class="simansa-mini-stat__value" id="syncCreatedCount">0</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="simansa-mini-stat">
                                    <span class="simansa-mini-stat__label">Sudah Ada</span>
                                    <span class="simansa-mini-stat__value" id="syncExistingCount">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="scan-loading-state__steps">
                            <span><i class="fas fa-check-circle text-success"></i> Database sinkron</span>
                            <span><i class="fas fa-bolt text-success"></i> Cache diperbarui</span>
                            <span><i class="fas fa-redo text-success"></i> Halaman akan dimuat ulang</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn simansa-btn-contrast d-none" id="btnReloadAfterSync">
                    <i class="fas fa-redo mr-1"></i> Muat Ulang Sekarang
                </button>
                <button type="button" class="btn btn-success" id="btnSyncPermissions">
                    <i class="fas fa-sync mr-1"></i> Sync All Permissions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Tambah Role Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAddRole">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="roleName">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required
                               placeholder="Contoh: Koordinator BK">
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">Deskripsi</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="2"
                                  placeholder="Deskripsi role (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .floating-save-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1050;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .permission-checkbox {
        cursor: pointer;
    }

    .simansa-card-subtitle {
        margin-top: .2rem;
        font-size: .82rem;
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.45;
    }

    .permission-checkbox:checked + .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }

    .permission-role-panel .card-body {
        max-height: 72vh;
        overflow: auto;
    }

    .role-list-stack {
        display: grid;
        gap: .65rem;
    }

    .role-item {
        transition: all 0.2s ease;
        border: 1px solid rgba(191, 219, 254, 0.82);
        border-left: 4px solid transparent;
        border-radius: 16px;
        padding: .9rem 1rem;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.98));
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
    }

    .role-item:hover {
        background-color: #f8fbff;
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.09);
    }

    .role-item.active {
        background: linear-gradient(180deg, rgba(219, 234, 254, 0.85), rgba(239, 246, 255, 0.96));
        border-left-color: #2563eb;
        box-shadow: 0 16px 28px rgba(37, 99, 235, 0.12);
    }

    .role-item--super {
        border-left-color: #f59e0b;
    }

    .role-item__content {
        min-width: 0;
    }

    .role-item__title {
        font-size: .94rem;
        color: #0f172a;
        font-weight: 800;
    }

    .role-item__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
        margin-top: .3rem;
        font-size: .76rem;
        color: #64748b;
    }

    .permission-matrix-toolbar {
        gap: 1rem;
    }

    .permission-matrix-toolbar__intro {
        min-width: 0;
        flex: 1 1 auto;
    }

    .permission-matrix-toolbar__actions {
        flex-shrink: 0;
    }

    .module-card {
        border: 1px solid rgba(191, 219, 254, 0.84);
        border-left: 3px solid transparent;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,255,0.98));
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .module-card:hover {
        border-left-color: #007bff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        transform: translateY(-1px);
    }

    .module-card__header {
        padding: .85rem 1rem !important;
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.92), rgba(248, 250, 252, 0.94));
    }

    .module-toggle {
        text-decoration: none !important;
        color: #0f172a !important;
        width: 100%;
    }

    .module-toggle__content {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 0;
        padding-right: 1rem;
    }

    .module-toggle__content > span:last-child {
        min-width: 0;
        flex: 1 1 auto;
    }

    .module-toggle__content strong {
        display: block;
        font-size: .96rem;
        line-height: 1.2;
        font-weight: 800;
        color: #0f172a !important;
        margin-bottom: .2rem;
    }

    .module-toggle__icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .module-toggle__icon--success { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .module-toggle__icon--warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .module-toggle__icon--danger { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
    .module-toggle__icon--info { background: rgba(6, 182, 212, 0.12); color: #0891b2; }
    .module-toggle__icon--secondary { background: rgba(100, 116, 139, 0.12); color: #475569; }
    .module-toggle__icon--dark { background: rgba(15, 23, 42, 0.1); color: #1e293b; }

    .module-toggle__meta {
        display: block;
        margin-top: .18rem;
        font-size: .8rem;
        font-weight: 500;
        color: #64748b;
        line-height: 1.45;
    }

    .module-card__tools {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .module-card__badge {
        padding: .38rem .7rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }

    .module-card__action-group {
        gap: .45rem;
    }

    .module-card__action-group > .btn {
        border-radius: 12px !important;
    }

    .btn-module-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .2rem;
        min-width: 92px;
        padding: .42rem .75rem;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1;
        border: 1px solid transparent;
        background: #fff;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        transition: all .2s ease;
        text-decoration: none !important;
    }

    .btn-module-action span,
    .btn-module-action i {
        color: inherit !important;
    }

    .btn-module-action--grant {
        border-color: rgba(34, 197, 94, 0.5);
        color: #15803d !important;
        background: linear-gradient(180deg, rgba(240, 253, 244, 0.98), rgba(255, 255, 255, 0.98));
    }

    .btn-module-action--grant:hover,
    .btn-module-action--grant:focus {
        border-color: rgba(21, 128, 61, 0.72);
        color: #166534 !important;
        background: linear-gradient(180deg, rgba(220, 252, 231, 0.98), rgba(240, 253, 244, 0.98));
    }

    .btn-module-action--grant,
    .btn-module-action--grant span,
    .btn-module-action--grant i,
    .btn-module-action--grant:visited,
    .btn-module-action--grant:visited span,
    .btn-module-action--grant:visited i {
        color: #15803d !important;
    }

    .btn-module-action--grant:hover,
    .btn-module-action--grant:hover span,
    .btn-module-action--grant:hover i,
    .btn-module-action--grant:focus,
    .btn-module-action--grant:focus span,
    .btn-module-action--grant:focus i {
        color: #166534 !important;
    }

    .btn-module-action--revoke {
        border-color: rgba(248, 113, 113, 0.55);
        color: #dc2626 !important;
        background: linear-gradient(180deg, rgba(254, 242, 242, 0.98), rgba(255, 255, 255, 0.98));
    }

    .btn-module-action--revoke:hover,
    .btn-module-action--revoke:focus {
        border-color: rgba(220, 38, 38, 0.72);
        color: #b91c1c !important;
        background: linear-gradient(180deg, rgba(254, 226, 226, 0.98), rgba(254, 242, 242, 0.98));
    }

    .btn-module-action--revoke,
    .btn-module-action--revoke span,
    .btn-module-action--revoke i,
    .btn-module-action--revoke:visited,
    .btn-module-action--revoke:visited span,
    .btn-module-action--revoke:visited i {
        color: #dc2626 !important;
    }

    .btn-module-action--revoke:hover,
    .btn-module-action--revoke:hover span,
    .btn-module-action--revoke:hover i,
    .btn-module-action--revoke:focus,
    .btn-module-action--revoke:focus span,
    .btn-module-action--revoke:focus i {
        color: #b91c1c !important;
    }

    .module-toggle:hover {
        color: #0056b3 !important;
    }

    .permission-table th {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .permission-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: .75rem;
        font-weight: 700;
    }

    .permission-table td:first-child {
        background: rgba(248, 250, 252, 0.85);
        font-weight: 600;
        min-width: 230px;
    }

    .permission-table code {
        display: inline-block;
        font-size: .76rem;
        padding: .32rem .58rem;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
        white-space: normal;
        line-height: 1.35;
    }

    .super-admin-cell {
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }

    .bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .badge-sm {
        font-size: 0.65rem;
        padding: 2px 5px;
    }

    /* Changed indicator */
    .permission-checkbox.changed + .custom-control-label::after {
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.5);
    }

    /* Checkbox size adjustment */
    .custom-checkbox .custom-control-label::before,
    .custom-checkbox .custom-control-label::after {
        width: 1.2rem;
        height: 1.2rem;
    }

    .custom-control {
        padding-left: 1.8rem;
        min-height: 1.2rem;
    }

    #floatingSaveBtn .btn {
        border-radius: 999px;
    }

    .scan-modal-body {
        position: relative;
        min-height: 240px;
    }

    .scan-loading-state {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        padding: 1.4rem;
        border: 1px solid rgba(191, 219, 254, 0.78);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(239,246,255,0.96), rgba(248,250,252,0.98));
    }

    .scan-loading-state__spinner {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(37,99,235,0.16), rgba(13,148,136,0.16));
        color: #1d4ed8;
        font-size: 1.8rem;
        animation: pulse 1.8s infinite;
        flex: 0 0 72px;
    }

    .scan-loading-state__spinner--sync {
        background: linear-gradient(135deg, rgba(16,185,129,0.16), rgba(37,99,235,0.16));
        color: #0f766e;
    }

    .scan-loading-state__spinner--sync i {
        animation: spin 1.15s linear infinite;
    }

    .scan-loading-state--success {
        border-color: rgba(167, 243, 208, 0.9);
        background: linear-gradient(135deg, rgba(236,253,245,0.96), rgba(248,250,252,0.98));
    }

    .scan-loading-state__spinner--success {
        background: linear-gradient(135deg, rgba(34,197,94,0.16), rgba(16,185,129,0.16));
        color: #15803d;
    }

    .scan-loading-state__content h6 {
        margin: 0 0 .35rem;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .scan-loading-state__content p {
        margin: 0 0 .8rem;
        color: #64748b;
        font-size: .86rem;
    }

    .scan-progress {
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(191, 219, 254, 0.5);
        margin-bottom: .8rem;
    }

    .scan-progress__bar {
        width: 45%;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #14b8a6);
        animation: scanProgress 1.5s ease-in-out infinite;
    }

    .scan-progress--sync .scan-progress__bar {
        background: linear-gradient(90deg, #10b981, #2563eb, #06b6d4);
        animation-duration: 1.2s;
    }

    .scan-loading-state__steps {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        font-size: .75rem;
        color: #475569;
    }

    .scan-loading-state__steps span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .55rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.72);
        border: 1px solid rgba(191, 219, 254, 0.82);
    }

    @keyframes scanProgress {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(320%); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (max-width: 767.98px) {
        .scan-loading-state {
            flex-direction: column;
            text-align: center;
        }

        .module-toggle__meta {
            max-width: 100%;
        }

        .permission-role-panel .card-body {
            max-height: none;
            overflow: visible;
        }
    }

    @media (max-width: 991.98px) {
        .permission-matrix-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .permission-matrix-toolbar__actions {
            width: 100%;
        }

        .module-card__tools {
            width: 100%;
            justify-content: flex-start;
            margin-top: .7rem;
        }

        .btn-module-action {
            min-width: 86px;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let pendingChanges = [];

    function notifySuccess(message) {
        if (window.toastr && typeof window.toastr.success === 'function') {
            window.toastr.success(message);
        } else {
            console.log(message);
        }
    }

    function notifyError(message) {
        if (window.toastr && typeof window.toastr.error === 'function') {
            window.toastr.error(message);
        } else {
            console.error(message);
        }
    }

    function resetScanSummaries() {
        $('#totalUnregistered').text('0');
        $('#scanRegisteredTotal').text('0');
        $('#scanCatalogTotal').text('0');
        $('#scanSharedMenuTotal').text('0');
        $('#unregisteredList').empty();
        $('#uncataloguedList').empty();
        $('#sharedPermissionList').empty();
    }

    function setScanState(isLoading) {
        $('#scanLoading').toggleClass('d-none', !isLoading);
        $('#syncLoading').addClass('d-none');
        $('#syncSuccess').addClass('d-none');
        $('#scanResults').toggleClass('d-none', isLoading);
        $('#btnSyncPermissions').prop('disabled', isLoading);
    }

    function setSyncState(isLoading) {
        $('#scanLoading').addClass('d-none');
        $('#scanResults').toggleClass('d-none', isLoading);
        $('#syncLoading').toggleClass('d-none', !isLoading);
        $('#syncSuccess').addClass('d-none');
        $('#btnSyncPermissions').prop('disabled', isLoading);
    }

    function resetSyncButton() {
        $('#btnSyncPermissions')
            .prop('disabled', false)
            .html('<i class="fas fa-sync mr-1"></i> Sync All Permissions');
    }

    function showSyncSuccess(response) {
        $('#scanLoading, #scanResults, #syncLoading').addClass('d-none');
        $('#syncCreatedCount').text(response.created ?? 0);
        $('#syncExistingCount').text(response.existing ?? 0);
        $('#syncSuccessMessage').text(response.message || 'Permission berhasil disinkronkan dan cache akses sudah diperbarui.');
        $('#syncSuccess').removeClass('d-none');
        $('#btnReloadAfterSync').removeClass('d-none');
    }

    $('#scanModal').on('hidden.bs.modal', function() {
        resetScanSummaries();
        setScanState(false);
        resetSyncButton();
        $('#btnReloadAfterSync').addClass('d-none');
    });

    $('#btnReloadAfterSync').on('click', function() {
        location.reload();
    });
    
    // Track permission checkbox changes
    $(document).on('change', '.permission-checkbox', function() {
        const $checkbox = $(this);
        const roleId = $checkbox.data('role-id');
        const permission = $checkbox.data('permission');
        const isChecked = $checkbox.is(':checked');
        
        // Mark as changed
        $checkbox.addClass('changed');
        
        // Add to pending changes
        const changeIndex = pendingChanges.findIndex(c => 
            c.role_id === roleId && c.permission === permission
        );
        
        const change = {
            role_id: roleId,
            permission: permission,
            action: isChecked ? 'grant' : 'revoke'
        };
        
        if (changeIndex >= 0) {
            pendingChanges[changeIndex] = change;
        } else {
            pendingChanges.push(change);
        }
        
        updateSaveButton();
    });
    
    // Update floating save button
    function updateSaveButton() {
        const $btn = $('#floatingSaveBtn');
        const $count = $('#changeCount');
        
        if (pendingChanges.length > 0) {
            $btn.removeClass('d-none');
            $count.text(pendingChanges.length);
        } else {
            $btn.addClass('d-none');
        }
    }
    
    // Save changes
    $('#btnSaveChanges').on('click', function() {
        if (pendingChanges.length === 0) return;
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...');
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                changes: pendingChanges
            },
            success: function(response) {
                if (response.success) {
                    notifySuccess(response.message);
                    pendingChanges = [];
                    $('.permission-checkbox.changed').removeClass('changed');
                    updateSaveButton();
                } else {
                    notifyError(response.message || 'Gagal menyimpan perubahan');
                }
            },
            error: function(xhr) {
                notifyError('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Perubahan <span class="badge badge-light ml-2" id="changeCount">0</span>');
            }
        });
    });
    
    // Expand/Collapse all
    $('#expandAll').on('click', function() {
        $('.collapse').collapse('show');
    });
    
    $('#collapseAll').on('click', function() {
        $('.collapse').collapse('hide');
    });
    
    // Scan permissions
    $('#btnScan').on('click', function() {
        $('#scanModal').modal('show');
        resetScanSummaries();
        resetSyncButton();
        setScanState(true);
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.scan") }}',
            method: 'GET',
            dataType: 'json'
        }).done(function(response) {
            setScanState(false);
            
            if (response.success) {
                $('#totalUnregistered').text(response.total);
                $('#scanRegisteredTotal').text(response.registered_total || 0);
                $('#scanCatalogTotal').text(response.catalog_total || 0);
                $('#scanSharedMenuTotal').text((response.shared_menu_permissions || []).length);
                
                const $list = $('#unregisteredList');
                const $uncatalogued = $('#uncataloguedList');
                const $shared = $('#sharedPermissionList');
                $list.empty();
                $uncatalogued.empty();
                $shared.empty();
                
                if (response.unregistered.length === 0) {
                    $list.html('<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i> Semua permission sudah terdaftar!</div>');
                } else {
                    response.unregistered.forEach(function(perm) {
                        $list.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <code>${perm}</code>
                                <span class="badge badge-warning">Belum terdaftar</span>
                            </div>
                        `);
                    });
                }

                if (!response.uncatalogued || response.uncatalogued.length === 0) {
                    $uncatalogued.html('<div class="alert alert-success mb-0"><i class="fas fa-check-circle mr-2"></i> Semua permission terdaftar sudah masuk katalog fitur.</div>');
                } else {
                    response.uncatalogued.forEach(function(perm) {
                        $uncatalogued.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <code>${perm}</code>
                                <span class="badge badge-warning">Belum dipetakan</span>
                            </div>
                        `);
                    });
                }

                if (!response.shared_menu_permissions || response.shared_menu_permissions.length === 0) {
                    $shared.html('<div class="alert alert-success mb-0"><i class="fas fa-check-circle mr-2"></i> Semua menu sudah memakai permission yang spesifik.</div>');
                } else {
                    response.shared_menu_permissions.forEach(function(entry) {
                        $shared.append(`
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <code>${entry.permission}</code>
                                        <div class="small text-muted mt-1">${entry.menus.join(' | ')}</div>
                                    </div>
                                    <span class="badge badge-secondary">${entry.count} menu</span>
                                </div>
                            </div>
                        `);
                    });
                }
            } else {
                $('#unregisteredList').html(`
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>${response.message || 'Scan gagal. Coba lagi sebentar.'}
                    </div>
                `);
            }
        }).fail(function(xhr) {
            setScanState(false);
            const rawMessage = xhr.responseJSON?.message
                || xhr.statusText
                || 'Scan gagal. Coba lagi sebentar.';
            $('#unregisteredList').html(`<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>${rawMessage}</div>`);
            $('#uncataloguedList').empty();
            $('#sharedPermissionList').empty();
            notifyError('Gagal scan permissions: ' + rawMessage);
        });
    });
    
    // Sync permissions
    $('#btnSyncPermissions').on('click', function() {
        const $btn = $(this);
        setSyncState(true);
        $btn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin mr-1"></i> Menyinkronkan...');
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.sync") }}',
            method: 'POST',
            dataType: 'json',
            timeout: 15000,
            data: {
                _token: '{{ csrf_token() }}'
            }
        }).done(function(response) {
            if (response.success) {
                notifySuccess(response.message);
                showSyncSuccess(response);
                setTimeout(function() {
                    location.reload();
                }, 1800);
            } else {
                setSyncState(false);
                notifyError(response.message || 'Sinkronisasi gagal.');
            }
        }).fail(function(xhr, textStatus) {
            setSyncState(false);

            let message = xhr.responseJSON?.message || xhr.statusText || 'Unknown error';

            if (textStatus === 'timeout') {
                message = 'Permintaan sync melebihi batas waktu. Coba lagi atau muat ulang halaman.';
            } else if (!xhr.responseJSON && xhr.responseText && xhr.responseText.includes('<!DOCTYPE')) {
                message = 'Request sync dialihkan oleh middleware. Pastikan sesi login dan lokasi perangkat valid.';
            }

            notifyError('Gagal sync permissions: ' + message);
            $('#unregisteredList').html(`<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>${message}</div>`);
            $('#scanResults').removeClass('d-none');
        }).always(function() {
            resetSyncButton();
            if (!$('#scanResults').hasClass('d-none') || !$('#syncSuccess').hasClass('d-none')) {
                $('#syncLoading').addClass('d-none');
            }
        });
    });
    
    // Add role
    $('#btnAddRole').on('click', function() {
        $('#addRoleModal').modal('show');
    });
    
    $('#formAddRole').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        
        $.post('{{ route("admin.permission-matrix.role.store") }}', $form.serialize(), function(response) {
            if (response.success) {
                notifySuccess(response.message);
                $('#addRoleModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                notifyError(response.message || 'Gagal membuat role.');
            }
        }).fail(function(xhr) {
            notifyError('Gagal membuat role: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Role');
        });
    });
    
    // Grant all for role
    $(document).on('click', '.btn-role-grant-all', function() {
        const roleId = $(this).data('role-id');
        
        if (!confirm('Berikan semua permission ke role ini?')) return;
        
        bulkUpdateRole(roleId, 'grant_all');
    });
    
    // Revoke all for role
    $(document).on('click', '.btn-role-revoke-all', function() {
        const roleId = $(this).data('role-id');
        
        if (!confirm('Cabut semua permission dari role ini?')) return;
        
        bulkUpdateRole(roleId, 'revoke_all');
    });
    
    function bulkUpdateRole(roleId, action) {
        $.post('{{ route("admin.permission-matrix.role.bulk") }}', {
            _token: '{{ csrf_token() }}',
            role_id: roleId,
            action: action
        }, function(response) {
            if (response.success) {
                notifySuccess(response.message);
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                notifyError(response.message || 'Gagal update permission.');
            }
        }).fail(function(xhr) {
            notifyError('Gagal update permission');
        });
    }
    
    // Module-level grant/revoke
    $(document).on('click', '.btn-module-grant-all', function() {
        const module = $(this).data('module');
        // Check all checkboxes in this module
        $(`#collapse${module} .permission-checkbox`).prop('checked', true).trigger('change');
    });
    
    $(document).on('click', '.btn-module-revoke-all', function() {
        const module = $(this).data('module');
        // Uncheck all checkboxes in this module
        $(`#collapse${module} .permission-checkbox`).prop('checked', false).trigger('change');
    });
    
    // Keyboard shortcut: Ctrl+S to save
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (pendingChanges.length > 0) {
                $('#btnSaveChanges').click();
            }
        }
    });
});
</script>
@stop
