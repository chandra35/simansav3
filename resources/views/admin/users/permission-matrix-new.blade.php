@extends('adminlte::page')

@section('title', 'Permission Matrix - SIMANSA')

@section('css')
<style>
    /* Matrix Table Styling */
    .matrix-container {
        overflow-x: auto;
    }
    
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        min-width: 100%;
    }
    
    .matrix-table th,
    .matrix-table td {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
        text-align: center;
        vertical-align: middle;
    }
    
    .matrix-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .matrix-table thead th.role-header {
        min-width: 120px;
        font-size: 0.85rem;
    }
    
    .matrix-table thead th.role-header small {
        display: block;
        font-weight: normal;
        opacity: 0.8;
    }
    
    .matrix-table .module-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .matrix-table .module-header:hover {
        background: linear-gradient(135deg, #0f8a7e 0%, #30d96c 100%);
    }
    
    .matrix-table .module-header i {
        margin-right: 8px;
        width: 20px;
        text-align: center;
    }
    
    .matrix-table .permission-row {
        background: #fff;
    }
    
    .matrix-table .permission-row:hover {
        background: #f8f9fa;
    }
    
    .matrix-table .permission-name {
        text-align: left;
        padding-left: 30px;
        font-size: 0.85rem;
    }
    
    .matrix-table .permission-name code {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    /* Checkbox Styling */
    .perm-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #28a745;
    }
    
    .perm-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .perm-checkbox.changed {
        outline: 2px solid #ffc107;
        outline-offset: 2px;
    }
    
    /* Module Toggle */
    .module-toggle {
        cursor: pointer;
        user-select: none;
    }
    
    .module-toggle .toggle-icon {
        transition: transform 0.3s;
    }
    
    .module-toggle.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
    
    /* Role Cards */
    .role-card {
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .role-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .role-card .card-header {
        padding: 15px;
    }
    
    .role-card.super-admin .card-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .role-card.admin .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }
    
    .role-card.gtk .card-header {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: white;
    }
    
    .role-card.default .card-header {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: white;
    }
    
    /* Stats Cards */
    .stat-card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    /* Save Button */
    .save-changes-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        padding: 15px 30px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        display: none;
    }
    
    .save-changes-btn.show {
        display: flex;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        50% { box-shadow: 0 4px 30px rgba(40, 167, 69, 0.5); }
    }
    
    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 5px;
    }
    
    .quick-actions .btn {
        padding: 2px 8px;
        font-size: 0.7rem;
    }
    
    /* Protected Role Badge */
    .protected-badge {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
    }
    
    /* Scan Results */
    .scan-result-item {
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f8f9fa;
        border-left: 4px solid #17a2b8;
    }
    
    .scan-result-item code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-th text-primary"></i> Permission Matrix</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Permission Matrix</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-primary">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-primary mr-3">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ count($roles) }}</h3>
                        <span class="text-white-50">Total Roles</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-success">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-success mr-3">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ $totalPermissions }}</h3>
                        <span class="text-white-50">Total Permissions</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-info">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-info mr-3">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ count($modules) }}</h3>
                        <span class="text-white-50">Modules</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-warning">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-warning mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ $totalUsers }}</h3>
                        <span class="text-white-50">Total Users</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Toolbar -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary btn-sm" id="expandAll">
                        <i class="fas fa-expand-alt"></i> Expand All
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="collapseAll">
                        <i class="fas fa-compress-alt"></i> Collapse All
                    </button>
                    <button class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#scanModal">
                        <i class="fas fa-sync-alt"></i> Scan Permissions
                    </button>
                    <button class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#addRoleModal">
                        <i class="fas fa-plus"></i> Add Role
                    </button>
                </div>
                <div>
                    <span class="badge badge-light" id="changeCounter">0 changes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Matrix Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Role vs Permission Matrix</h3>
            <div class="card-tools">
                <span class="text-muted small">
                    <i class="fas fa-info-circle"></i> Centang checkbox untuk mengaktifkan permission
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <form id="matrixForm" action="{{ route('admin.permission-matrix.update') }}" method="POST">
                @csrf
                <div class="matrix-container">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th style="min-width: 250px; text-align: left;">
                                    <i class="fas fa-cubes"></i> Module / Permission
                                </th>
                                @foreach($roles as $roleId => $role)
                                <th class="role-header">
                                    {{ $role['name'] }}
                                    <small>{{ $role['users_count'] }} users</small>
                                    @if($role['is_system'])
                                        <span class="protected-badge"><i class="fas fa-lock"></i></span>
                                    @endif
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $moduleKey => $module)
                            <!-- Module Header -->
                            <tr class="module-header module-toggle" data-target=".module-{{ $moduleKey }}">
                                <td colspan="{{ count($roles) + 1 }}">
                                    <i class="fas {{ $module['icon'] }}"></i>
                                    <strong>{{ $module['name'] }}</strong>
                                    <span class="badge badge-light ml-2">{{ count($module['permissions']) }} permissions</span>
                                    <i class="fas fa-chevron-down toggle-icon float-right"></i>
                                </td>
                            </tr>
                            <!-- Permission Rows -->
                            @foreach($module['permissions'] as $perm)
                            <tr class="permission-row module-{{ $moduleKey }}">
                                <td class="permission-name">
                                    {{ $perm['label'] }}<br>
                                    <code>{{ $perm['name'] }}</code>
                                </td>
                                @foreach($roles as $roleId => $role)
                                <td>
                                    @php
                                        $hasPermission = isset($role['modules'][$moduleKey]['permissions'][$perm['name']]) 
                                            ? $role['modules'][$moduleKey]['permissions'][$perm['name']] 
                                            : false;
                                        $isProtected = $role['name'] === 'Super Admin';
                                    @endphp
                                    <input type="checkbox" 
                                           class="perm-checkbox" 
                                           name="permissions[{{ $roleId }}][]" 
                                           value="{{ $perm['name'] }}"
                                           data-role="{{ $roleId }}"
                                           data-permission="{{ $perm['name'] }}"
                                           data-original="{{ $hasPermission ? '1' : '0' }}"
                                           {{ $hasPermission ? 'checked' : '' }}
                                           {{ $isProtected ? 'disabled' : '' }}
                                           title="{{ $isProtected ? 'Super Admin memiliki semua permission' : '' }}">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Cards Summary -->
    <div class="row mt-4">
        @foreach($roles as $roleId => $role)
        @php
            $cardClass = 'default';
            if ($role['name'] === 'Super Admin') $cardClass = 'super-admin';
            elseif ($role['name'] === 'Admin') $cardClass = 'admin';
            elseif ($role['name'] === 'GTK') $cardClass = 'gtk';
        @endphp
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card role-card {{ $cardClass }}">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tag"></i> {{ $role['name'] }}
                        @if($role['is_system'])
                            <span class="badge badge-light float-right"><i class="fas fa-lock"></i></span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Users:</span>
                        <strong>{{ $role['users_count'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Permissions:</span>
                        <strong id="perm-count-{{ $roleId }}">
                            {{ collect($role['modules'])->sum(function($m) { return collect($m['permissions'])->filter()->count(); }) }}
                        </strong>
                    </div>
                    @if(!$role['is_system'])
                    <div class="quick-actions mt-3">
                        <button type="button" class="btn btn-sm btn-success select-all-role" data-role="{{ $roleId }}">
                            <i class="fas fa-check-double"></i> All
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary clear-all-role" data-role="{{ $roleId }}">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Floating Save Button -->
    <button type="button" class="btn btn-success save-changes-btn" id="saveChangesBtn">
        <i class="fas fa-save mr-2"></i> 
        <span>Save Changes</span>
        <span class="badge badge-light ml-2" id="saveChangeCount">0</span>
    </button>

    <!-- Scan Modal -->
    <div class="modal fade" id="scanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-sync-alt"></i> Scan & Sync Permissions</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Scan akan mencari permission yang digunakan di routes dan menu tapi belum terdaftar di database.
                    </div>
                    
                    <div id="scanResults" class="d-none">
                        <h6><i class="fas fa-search"></i> Unregistered Permissions Found:</h6>
                        <div id="scanResultsList"></div>
                    </div>
                    
                    <div id="scanLoading" class="text-center py-4 d-none">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Scanning...</p>
                    </div>
                    
                    <div id="scanEmpty" class="text-center py-4 d-none">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                        <p class="mt-2">All permissions are already registered!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="startScan">
                        <i class="fas fa-search"></i> Start Scan
                    </button>
                    <button type="button" class="btn btn-success d-none" id="syncPermissions">
                        <i class="fas fa-sync"></i> Sync All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Role</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="roleName">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="roleName" class="form-control" 
                                   placeholder="e.g., Kepala TU, Staff Keuangan" required>
                            <small class="text-muted">Nama role harus unik</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let changes = {};
    
    // Toggle module rows
    $('.module-toggle').on('click', function() {
        const target = $(this).data('target');
        $(target).toggle();
        $(this).find('.toggle-icon').toggleClass('fa-chevron-down fa-chevron-right');
    });
    
    // Expand/Collapse All
    $('#expandAll').on('click', function() {
        $('.permission-row').show();
        $('.toggle-icon').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    });
    
    $('#collapseAll').on('click', function() {
        $('.permission-row').hide();
        $('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-right');
    });
    
    // Track checkbox changes
    $('.perm-checkbox').on('change', function() {
        const $this = $(this);
        const roleId = $this.data('role');
        const permission = $this.data('permission');
        const original = $this.data('original') === 1;
        const current = $this.is(':checked');
        
        const key = roleId + '_' + permission;
        
        if (original !== current) {
            changes[key] = {
                role_id: roleId,
                permission: permission,
                value: current
            };
            $this.addClass('changed');
        } else {
            delete changes[key];
            $this.removeClass('changed');
        }
        
        updateChangeCounter();
        updatePermissionCount(roleId);
    });
    
    // Update change counter
    function updateChangeCounter() {
        const count = Object.keys(changes).length;
        $('#changeCounter').text(count + ' changes');
        $('#saveChangeCount').text(count);
        
        if (count > 0) {
            $('#saveChangesBtn').addClass('show');
            $('#changeCounter').removeClass('badge-light').addClass('badge-warning');
        } else {
            $('#saveChangesBtn').removeClass('show');
            $('#changeCounter').removeClass('badge-warning').addClass('badge-light');
        }
    }
    
    // Update permission count for role
    function updatePermissionCount(roleId) {
        const count = $('input.perm-checkbox[data-role="' + roleId + '"]:checked').length;
        $('#perm-count-' + roleId).text(count);
    }
    
    // Select all permissions for role
    $('.select-all-role').on('click', function() {
        const roleId = $(this).data('role');
        $('input.perm-checkbox[data-role="' + roleId + '"]:not(:disabled)').each(function() {
            if (!$(this).is(':checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });
    });
    
    // Clear all permissions for role
    $('.clear-all-role').on('click', function() {
        const roleId = $(this).data('role');
        $('input.perm-checkbox[data-role="' + roleId + '"]:not(:disabled)').each(function() {
            if ($(this).is(':checked')) {
                $(this).prop('checked', false).trigger('change');
            }
        });
    });
    
    // Save changes
    $('#saveChangesBtn').on('click', function() {
        if (Object.keys(changes).length === 0) return;
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                changes: Object.values(changes)
            },
            success: function(response) {
                if (response.success) {
                    // Reset changes tracking
                    changes = {};
                    $('.perm-checkbox').removeClass('changed');
                    
                    // Update original values
                    $('.perm-checkbox').each(function() {
                        $(this).data('original', $(this).is(':checked') ? 1 : 0);
                    });
                    
                    updateChangeCounter();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to save changes'
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i><span>Save Changes</span><span class="badge badge-light ml-2" id="saveChangeCount">0</span>');
            }
        });
    });
    
    // Scan permissions
    $('#startScan').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true);
        $('#scanLoading').removeClass('d-none');
        $('#scanResults, #scanEmpty').addClass('d-none');
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.scan") }}',
            method: 'GET',
            success: function(response) {
                if (response.unregistered && Object.keys(response.unregistered).length > 0) {
                    let html = '';
                    $.each(response.unregistered, function(name, info) {
                        html += '<div class="scan-result-item">';
                        html += '<code>' + name + '</code>';
                        html += '<span class="badge badge-info ml-2">' + info.source + '</span>';
                        if (info.menu) html += '<span class="text-muted ml-2">Menu: ' + info.menu + '</span>';
                        if (info.route) html += '<span class="text-muted ml-2">Route: ' + info.route + '</span>';
                        html += '</div>';
                    });
                    $('#scanResultsList').html(html);
                    $('#scanResults').removeClass('d-none');
                    $('#syncPermissions').removeClass('d-none');
                } else {
                    $('#scanEmpty').removeClass('d-none');
                    $('#syncPermissions').addClass('d-none');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to scan permissions', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $('#scanLoading').addClass('d-none');
            }
        });
    });
    
    // Sync permissions
    $('#syncPermissions').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
        
        $.ajax({
            url: '{{ route("admin.permission-matrix.sync") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Synced!',
                    text: response.message,
                    showConfirmButton: true
                }).then(function() {
                    location.reload();
                });
            },
            error: function() {
                Swal.fire('Error', 'Failed to sync permissions', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Sync All');
            }
        });
    });
    
    // Initialize - collapse all by default for cleaner view
    // $('.permission-row').hide();
    // $('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-right');
});
</script>
@stop
