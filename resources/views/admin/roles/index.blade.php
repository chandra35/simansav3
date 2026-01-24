@extends('adminlte::page')

@section('title', 'Role Management')

@section('css')
@include('admin.partials.action-buttons-style')
<style>
    .role-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .role-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .role-card.system-role {
        border-left-color: #6c757d;
    }
    .role-card.custom-role {
        border-left-color: #007bff;
    }
    .permission-badge {
        font-size: 0.75rem;
        margin: 2px;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-primary"></i> Role Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Roles</li>
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
        <div class="col-md-4">
            <div class="card bg-gradient-primary">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-primary mr-3">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ $roles->count() }}</h3>
                        <span class="text-white-50">Total Roles</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-success">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-success mr-3">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                        <span class="text-white-50">Total Permissions</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-info">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-info mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 text-white">{{ $roles->sum('users_count') }}</h3>
                        <span class="text-white-50">Users with Roles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Role</h3>
            <div class="card-tools">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-key"></i> Kelola Permissions
                </a>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Role
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($roles as $role)
                @php
                    $systemRoles = ['Super Admin', 'Siswa', 'GTK', 'Admin', 'Kepala Madrasah', 'WAKA', 'Operator', 'BK', 'Wali Kelas', 'Bendahara'];
                    $isSystem = in_array($role->name, $systemRoles);
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card role-card {{ $isSystem ? 'system-role' : 'custom-role' }} h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        {{ $role->name }}
                                    </h5>
                                    @if($isSystem)
                                        <span class="badge badge-secondary"><i class="fas fa-lock"></i> System</span>
                                    @else
                                        <span class="badge badge-primary"><i class="fas fa-user-edit"></i> Custom</span>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ route('admin.roles.show', $role) }}">
                                            <i class="fas fa-eye text-info"></i> Lihat Detail
                                        </a>
                                        @if(!$isSystem || $role->name != 'Super Admin')
                                            <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                                <i class="fas fa-edit text-warning"></i> Edit
                                            </a>
                                        @endif
                                        @if(!$isSystem)
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Yakin hapus role ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-info mr-2">
                                    <i class="fas fa-users"></i> {{ $role->users_count }} user
                                </span>
                                <span class="badge badge-success">
                                    <i class="fas fa-key"></i> {{ $role->permissions->count() }} permission
                                </span>
                            </div>

                            @if($role->permissions->count() > 0)
                                <div class="permission-tags">
                                    @foreach($role->permissions->take(5) as $permission)
                                        <span class="badge badge-light permission-badge">{{ $permission->name }}</span>
                                    @endforeach
                                    @if($role->permissions->count() > 5)
                                        <span class="badge badge-secondary permission-badge">+{{ $role->permissions->count() - 5 }} lainnya</span>
                                    @endif
                                </div>
                            @else
                                <small class="text-muted">Tidak ada permission</small>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-primary btn-block">
                                <i class="fas fa-eye"></i> Lihat Detail & Users
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada role</p>
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Role Pertama
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
