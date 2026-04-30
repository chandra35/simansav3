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
<div class="row">
    <!-- Left Sidebar: Roles -->
    <div class="col-md-3">
        <div class="card simansa-management-card sticky-top" style="top: 10px; z-index: 100;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tag mr-1"></i> Roles</h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="roleList">
                    @foreach($roles as $role)
                    <div class="list-group-item d-flex justify-content-between align-items-center role-item {{ $role->name === 'Super Admin' ? 'bg-warning-subtle' : '' }}" 
                         data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                        <div>
                            <strong>{{ $role->name }}</strong>
                            @if($role->name === 'Super Admin')
                            <i class="fas fa-crown text-warning ml-1" title="Protected"></i>
                            @endif
                            <br>
                            <small class="text-muted">{{ $role->users_count }} users</small>
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
                <div class="simansa-toolbar">
                    <h3 class="card-title mb-0"><i class="fas fa-th mr-1"></i> Permission per Module</h3>
                    <div class="simansa-toolbar__group">
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
                        <div class="card-header p-2 bg-light" id="heading{{ $moduleKey }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-link text-left p-0 module-toggle" type="button" 
                                        data-toggle="collapse" data-target="#collapse{{ $moduleKey }}"
                                        aria-expanded="false" aria-controls="collapse{{ $moduleKey }}">
                                    <i class="fas fa-{{ $module['icon'] ?? 'cube' }} mr-2 text-{{ $module['color'] ?? 'primary' }}"></i>
                                    <strong>{{ $module['label'] }}</strong>
                                    <span class="badge badge-{{ $registeredCount == count($modulePermissions) ? 'success' : 'warning' }} ml-2">
                                        {{ $registeredCount }}/{{ count($modulePermissions) }}
                                    </span>
                                </button>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-success btn-xs btn-module-grant-all" 
                                            data-module="{{ $moduleKey }}" title="Grant All for this Module">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-xs btn-module-revoke-all" 
                                            data-module="{{ $moduleKey }}" title="Revoke All for this Module">
                                        <i class="fas fa-times"></i>
                                    </button>
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
                                                    <i class="fas fa-check-circle text-success" title="Super Admin has all permissions"></i>
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
            <div class="modal-body">
                <div id="scanLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Scanning permissions...</p>
                </div>
                <div id="scanResults" class="d-none">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Ditemukan <strong id="totalUnregistered">0</strong> permission yang belum terdaftar
                    </div>
                    <div class="list-group" id="unregisteredList">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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

    .permission-checkbox:checked + .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }

    .role-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .role-item:hover {
        background-color: #f8fbff;
    }

    .role-item.active {
        background-color: #e0f2fe;
        border-left-color: #2563eb;
    }

    .module-card {
        border: 1px solid rgba(203, 213, 225, 0.8);
        border-left: 3px solid transparent;
        border-radius: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .module-card:hover {
        border-left-color: #007bff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
    }

    .module-toggle {
        text-decoration: none !important;
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
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let pendingChanges = [];
    
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
                    toastr.success(response.message);
                    pendingChanges = [];
                    $('.permission-checkbox.changed').removeClass('changed');
                    updateSaveButton();
                } else {
                    toastr.error(response.message || 'Gagal menyimpan perubahan');
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
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
        $('#scanLoading').removeClass('d-none');
        $('#scanResults').addClass('d-none');
        
        $.get('{{ route("admin.permission-matrix.scan") }}', function(response) {
            $('#scanLoading').addClass('d-none');
            $('#scanResults').removeClass('d-none');
            
            if (response.success) {
                $('#totalUnregistered').text(response.total);
                
                const $list = $('#unregisteredList');
                $list.empty();
                
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
            }
        }).fail(function(xhr) {
            $('#scanLoading').addClass('d-none');
            toastr.error('Gagal scan permissions');
        });
    });
    
    // Sync permissions
    $('#btnSyncPermissions').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Syncing...');
        
        $.post('{{ route("admin.permission-matrix.sync") }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(response.message);
            }
        }).fail(function(xhr) {
            toastr.error('Gagal sync permissions');
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-sync mr-1"></i> Sync All Permissions');
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
                toastr.success(response.message);
                $('#addRoleModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.message);
            }
        }).fail(function(xhr) {
            toastr.error('Gagal membuat role: ' + (xhr.responseJSON?.message || 'Unknown error'));
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
                toastr.success(response.message);
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.message);
            }
        }).fail(function(xhr) {
            toastr.error('Gagal update permission');
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
