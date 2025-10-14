@extends('adminlte::page')

@section('title', 'Permission Matrix - SIMANSA')

@section('content_header')
    <h1>Permission Matrix</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Role vs Permissions Matrix
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-info" id="expandAll">
                        <i class="fas fa-expand"></i> Expand All
                    </button>
                    <button class="btn btn-sm btn-secondary" id="collapseAll">
                        <i class="fas fa-compress"></i> Collapse All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-shield-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Roles</span>
                                <span class="info-box-number">{{ $roles->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-key"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Permissions</span>
                                <span class="info-box-number">{{ $permissions->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-cube"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Modules</span>
                                <span class="info-box-number">{{ $permissionsByModule->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Users</span>
                                <span class="info-box-number">{{ $totalUsers }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Matrix by Module -->
                <div class="accordion" id="permissionAccordion">
                    @foreach($permissionsByModule as $module => $perms)
                    <div class="card mb-2">
                        <div class="card-header p-2 bg-light" id="heading{{ $loop->index }}">
                            <h5 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" 
                                        data-toggle="collapse" data-target="#collapse{{ $loop->index }}">
                                    <i class="fas fa-cube mr-2"></i>
                                    <strong>{{ strtoupper($module) }}</strong>
                                    <span class="badge badge-secondary float-right">{{ $perms->count() }} permissions</span>
                                </button>
                            </h5>
                        </div>
                        <div id="collapse{{ $loop->index }}" class="collapse" data-parent="#permissionAccordion">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="bg-gradient-primary">
                                            <tr>
                                                <th style="width: 30%">Permission</th>
                                                @foreach($roles as $role)
                                                <th class="text-center" style="width: {{ 70 / $roles->count() }}%">
                                                    {{ $role->name }}
                                                </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($perms as $perm)
                                            <tr>
                                                <td>
                                                    <code class="text-sm">{{ $perm->name }}</code>
                                                </td>
                                                @foreach($roles as $role)
                                                <td class="text-center">
                                                    @if($role->hasPermissionTo($perm->name))
                                                        <i class="fas fa-check-circle text-success" title="Has Permission"></i>
                                                    @else
                                                        <i class="fas fa-times-circle text-danger" title="No Permission"></i>
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

                <!-- Role Details -->
                <div class="mt-4">
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Role Details</h5>
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header 
                                    @if($role->name == 'Super Admin') bg-danger
                                    @elseif($role->name == 'Admin') bg-primary
                                    @elseif($role->name == 'Operator') bg-info
                                    @elseif($role->name == 'Guru') bg-success
                                    @else bg-secondary
                                    @endif
                                ">
                                    <h3 class="widget-user-username">{{ $role->name }}</h3>
                                    <h5 class="widget-user-desc">{{ $role->permissions->count() }} Permissions</h5>
                                </div>
                                <div class="card-footer p-0">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Total Users <span class="float-right badge bg-info">{{ $role->users->count() }}</span>
                                            </span>
                                        </li>
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Guard <span class="float-right badge bg-success">{{ $role->guard_name }}</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .table-sm td, .table-sm th {
        padding: 0.5rem;
        vertical-align: middle;
    }
    code {
        font-size: 0.9rem;
    }
    .accordion .btn-link {
        text-decoration: none;
        color: #333;
    }
    .accordion .btn-link:hover {
        color: #007bff;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Expand All
    $('#expandAll').on('click', function() {
        $('.collapse').collapse('show');
    });

    // Collapse All
    $('#collapseAll').on('click', function() {
        $('.collapse').collapse('hide');
    });
});
</script>
@endpush
