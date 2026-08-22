@extends('adminlte::page')

@section('title', 'Role Management')

@section('css')
@include('admin.partials.action-buttons-style')
<style>
    .role-card {
        border: 1px solid rgba(191, 219, 254, 0.82);
        border-left: 4px solid transparent;
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 28%),
            linear-gradient(180deg, rgba(248, 251, 255, 0.98), rgba(239, 246, 255, 0.94));
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        transition: all 0.3s ease;
    }
    .role-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(37, 99, 235, 0.12);
    }
    .role-card.system-role {
        border-left-color: #6c757d;
    }
    .role-card.custom-role {
        border-left-color: #007bff;
    }
    .role-module-list {
        display: grid;
        gap: .65rem;
    }
    .role-module-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .7rem;
        padding: .75rem .8rem;
        border: 1px solid rgba(191, 219, 254, 0.78);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.66);
    }
    .role-module-item__main {
        min-width: 0;
        display: flex;
        gap: .7rem;
    }
    .role-module-item__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.16), rgba(13, 148, 136, 0.14));
        color: #1d4ed8;
        flex: 0 0 34px;
    }
    .role-module-item__title {
        margin: 0;
        font-size: .86rem;
        font-weight: 700;
        color: #0f172a;
    }
    .role-module-item__meta {
        margin-top: .15rem;
        font-size: .74rem;
        color: #64748b;
        line-height: 1.5;
    }
    .role-module-item__count {
        flex-shrink: 0;
        padding: .2rem .55rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
        font-size: .74rem;
        font-weight: 700;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-primary mr-1"></i> Role Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User &amp; Role</a></li>
                <li class="breadcrumb-item active">Role Management</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="text-uppercase small font-weight-bold mb-2"><i class="fas fa-shield-alt mr-1"></i> Users &amp; Role</p>
                    <h2 class="h4 mb-2">Role Management</h2>
                    <p class="mb-0">Kelola hak akses dan permission setiap role pengguna secara terstruktur dan mudah diaudit.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-6 border-right border-white-50"><div class="text-uppercase small font-weight-bold text-white-50">Total Role</div><div class="h3 mb-0">{{ $roles->count() }}</div></div>
                        <div class="col-6"><div class="text-uppercase small font-weight-bold text-white-50">Permission</div><div class="h3 mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--blue">
                <div class="simansa-stat-card__icon"><i class="fas fa-user-tag"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ $roles->count() }}</div>
                    <div class="simansa-stat-card__label">Total Role</div>
                    <div class="simansa-stat-card__desc">Role aktif di sistem</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--teal">
                <div class="simansa-stat-card__icon"><i class="fas fa-key"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ \Spatie\Permission\Models\Permission::count() }}</div>
                    <div class="simansa-stat-card__label">Total Permission</div>
                    <div class="simansa-stat-card__desc">Permission terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--indigo">
                <div class="simansa-stat-card__icon"><i class="fas fa-users"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ $roles->sum('users_count') }}</div>
                    <div class="simansa-stat-card__label">User Berrole</div>
                    <div class="simansa-stat-card__desc">User dengan role aktif</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role List -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Role</h3>
            <div class="card-tools d-flex">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm simansa-btn-header-soft mr-2">
                    <i class="fas fa-key"></i> Kelola Permissions
                </a>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-sm simansa-btn-strong">
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

                            @if(!empty($rolePermissionSummaries[$role->id]))
                                <div class="role-module-list">
                                    @foreach(collect($rolePermissionSummaries[$role->id])->take(3) as $module)
                                        <div class="role-module-item">
                                            <div class="role-module-item__main">
                                                <span class="role-module-item__icon">
                                                    <i class="fas fa-{{ $module['icon'] }}"></i>
                                                </span>
                                                <div>
                                                    <p class="role-module-item__title">{{ $module['label'] }}</p>
                                                    <div class="role-module-item__meta">
                                                        {{ implode(' | ', $module['preview']) ?: 'Akses fitur aktif' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="role-module-item__count">{{ $module['count'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <small class="text-muted">Tidak ada permission</small>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm simansa-btn-contrast btn-block">
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
